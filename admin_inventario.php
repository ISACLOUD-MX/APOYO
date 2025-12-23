<?php

//CloudMR

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();

$mensaje = "";
$productos = [];
$movimientos = [];
$usuarios = [];

$sql_productos = "SELECT id, nombre_producto, stock FROM productos ORDER BY nombre_producto ASC";
$result_productos = $conn->query($sql_productos);
if ($result_productos && $result_productos->num_rows > 0) {
    while ($row = $result_productos->fetch_assoc()) {
        $productos[] = $row;
    }
}

$sql_movimientos = "SELECT mi.id, p.nombre_producto, mi.tipo_movimiento, mi.cantidad, mi.fecha_movimiento, u.nombre_usuario AS usuario_responsable, mi.descripcion, v.id AS id_venta
                    FROM movimientos_inventario mi
                    INNER JOIN productos p ON mi.id_producto = p.id
                    LEFT JOIN usuarios u ON mi.id_usuario_responsable = u.id
                    LEFT JOIN ventas v ON mi.id_venta = v.id
                    ORDER BY mi.fecha_movimiento DESC";
$result_movimientos = $conn->query($sql_movimientos);
if ($result_movimientos && $result_movimientos->num_rows > 0) {
    while ($row = $result_movimientos->fetch_assoc()) {
        $movimientos[] = $row;
    }
}

$sql_usuarios = "SELECT id, nombre_usuario FROM usuarios ORDER BY nombre_usuario ASC";
$result_usuarios = $conn->query($sql_usuarios);
if ($result_usuarios && $result_usuarios->num_rows > 0) {
    while ($row = $result_usuarios->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajustar_stock'])) {
    $id_producto_ajuste = $_POST['id_producto_ajuste'] ?? 0;
    $nuevo_stock = $_POST['nuevo_stock'] ?? 0;

    if ($id_producto_ajuste > 0 && is_numeric($nuevo_stock)) {
        $stmt_update = $conn->prepare("UPDATE productos SET stock = ? WHERE id = ?");
        $stmt_update->bind_param("ii", $nuevo_stock, $id_producto_ajuste);

        if ($stmt_update->execute()) {
            $mensaje = "<div class='alert alert-success'>Stock del producto ajustado.</div>";
            
            $productos = [];
            $result_productos = $conn->query($sql_productos);
            if ($result_productos && $result_productos->num_rows > 0) {
                while ($row = $result_productos->fetch_assoc()) {
                    $productos[] = $row;
                }
            }
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al ajustar el stock.</div>";
        }
        $stmt_update->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>Seleccione un producto e ingrese un stock válido.</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_movimiento'])) {
    $id_producto_movimiento = $_POST['id_producto_movimiento'] ?? 0;
    $tipo_movimiento = $_POST['tipo_movimiento'] ?? '';
    $cantidad_movimiento = $_POST['cantidad_movimiento'] ?? 0;
    $id_usuario_responsable_movimiento = $_POST['id_usuario_responsable_movimiento'] ?? null;
    $descripcion_movimiento = $_POST['descripcion_movimiento'] ?? '';

    if ($id_producto_movimiento > 0 && in_array($tipo_movimiento, ['entrada', 'salida', 'ajuste']) && is_numeric($cantidad_movimiento)) {
        $stmt_insert_movimiento = $conn->prepare("INSERT INTO movimientos_inventario (id_producto, tipo_movimiento, cantidad, id_usuario_responsable, descripcion) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert_movimiento->bind_param("isiss", $id_producto_movimiento, $tipo_movimiento, $cantidad_movimiento, $id_usuario_responsable_movimiento, $descripcion_movimiento);

        if ($stmt_insert_movimiento->execute()) {
            $mensaje = "<div class='alert alert-success'>Movimiento de inventario registrado.</div>";
            $stmt_insert_movimiento->close(); 
          
            if ($tipo_movimiento === 'entrada') {
                $stmt_update_stock = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
                $stmt_update_stock->bind_param("ii", $cantidad_movimiento, $id_producto_movimiento);
                $stmt_update_stock->execute();
                $stmt_update_stock->close();
            } elseif ($tipo_movimiento === 'salida') {
                $stmt_update_stock = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
                $stmt_update_stock->bind_param("ii", $cantidad_movimiento, $id_producto_movimiento);
                $stmt_update_stock->execute();
                $stmt_update_stock->close();
            } elseif ($tipo_movimiento === 'ajuste') {
                
                $stmt_update_stock = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
                $stmt_update_stock->bind_param("ii", $cantidad_movimiento, $id_producto_movimiento);
                $stmt_update_stock->execute();
                $stmt_update_stock->close();
            }
            
            $movimientos = [];
            $result_movimientos = $conn->query($sql_movimientos);
            if ($result_movimientos && $result_movimientos->num_rows > 0) {
                while ($row = $result_movimientos->fetch_assoc()) {
                    $movimientos[] = $row;
                }
            }
            $productos = [];
            $result_productos = $conn->query($sql_productos);
            if ($result_productos && $result_productos->num_rows > 0) {
                while ($row = $result_productos->fetch_assoc()) {
                    $productos[] = $row;
                }
            }

        } else {
            $mensaje = "<div class='alert alert-danger'>Error al registrar el movimiento.</div>";
            $stmt_insert_movimiento->close(); 
        }
    } else {
        $mensaje = "<div class='alert alert-warning'>Por favor, complete todos los campos del movimiento correctamente.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Inventario</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .content-wrapper {
            padding: 20px;
        }
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        .form-container h2 {
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 25px;
            color: #343a40;
        }
        .form-group label {
            font-weight: bold;
            color: #495057;
        }
        .alert {
            margin-top: 20px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-sm {
            margin-right: 5px;
        }
        .modal-title {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <div class="text-center mt-3">
                        <img src="imagenes/logo_abarrotes.png" alt="Logo" class="img-fluid mb-3" style="max-width: 150px;">
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="admon_panel_central.php">
                                <i class="fas fa-tachometer-alt"></i> Panel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_modif_user.php">
                                <i class="fas fa-users"></i> Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_categorias.php">
                                <i class="fas fa-tags"></i> Categorías
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_inventario.php">
                                <i class="fas fa-cubes"></i> Inventario
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link btn btn-danger btn-sm" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-cubes mr-2"></i> Administrar Inventario</h1>
                </div>

                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-md-6">
                            <div class=" form-container">
                                <h2 class="text-center"><i class="fas fa-box-open mr-2"></i> Inventario Actual</h2>
                                <?php if (empty($productos)): ?>
                                    <p>No hay productos en el inventario.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Stock</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($productos as $producto): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                                        <td><?php echo htmlspecialchars($producto['stock']); ?></td>
                                                        <td class="text-center">
                                                            <button class="btn btn-sm btn-info btn-ajustar-stock"
                                                                    data-toggle="modal"
                                                                    data-target="#ajustarStockModal"
                                                                    data-id="<?php echo htmlspecialchars($producto['id']); ?>"
                                                                    data-nombre="<?php echo htmlspecialchars($producto['nombre_producto']); ?>"
                                                                    data-stock="<?php echo htmlspecialchars($producto['stock']); ?>">
                                                                <i class="fas fa-edit"></i> Ajustar Stock
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-container">
                                <h2 class="text-center"><i class="fas fa-exchange-alt mr-2"></i> Registrar Movimiento</h2>
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="id_producto_movimiento"><i class="fas fa-shopping-bag mr-2"></i> Producto:</label>
                                        <select class="form-control" id="id_producto_movimiento" name="id_producto_movimiento" required>
                                            <option value="">Seleccionar Producto</option>
                                            <?php foreach ($productos as $prod): ?>
                                                <option value="<?php echo htmlspecialchars($prod['id']); ?>"><?php echo htmlspecialchars($prod['nombre_producto']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="tipo_movimiento"><i class="fas fa-arrow-right mr-2"></i> Tipo de Movimiento:</label>
                                        <select class="form-control" id="tipo_movimiento" name="tipo_movimiento" required>
                                            <option value="">Seleccionar Tipo</option>
                                            <option value="entrada">Entrada</option>
                                            <option value="salida">Salida</option>
                                            <option value="ajuste">Ajuste</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="cantidad_movimiento"><i class="fas fa-sort-numeric-up mr-2"></i> Cantidad:</label>
                                        <input type="number" class="form-control" id="cantidad_movimiento" name="cantidad_movimiento" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="id_usuario_responsable_movimiento"><i class="fas fa-user mr-2"></i> Responsable (opcional):</label>
                                        <select class="form-control" id="id_usuario_responsable_movimiento" name="id_usuario_responsable_movimiento">
                                            <option value="">Seleccionar Usuario</option>
                                            <?php foreach ($usuarios as $user): ?>
                                                <option value="<?php echo htmlspecialchars($user['id']); ?>"><?php echo htmlspecialchars($user['nombre_usuario']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="descripcion_movimiento"><i class="fas fa-file-alt mr-2"></i> Descripción (opcional):</label>
                                        <textarea class="form-control" id="descripcion_movimiento" name="descripcion_movimiento" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block" name="registrar_movimiento"><i class="fas fa-plus-circle mr-2"></i> Registrar Movimiento</button>
                                </form>
                                <?php echo $mensaje; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-container">
                                <h2 class="text-center"><i class="fas fa-history mr-2"></i> Historial de Movimientos</h2>
                                <?php if (empty($movimientos)): ?>
                                    <p>No hay movimientos de inventario registrados.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Tipo</th>
                                                    <th>Cantidad</th>
                                                    <th>Fecha</th>
                                                    <th>Responsable</th>
                                                    <th>Descripción</th>
                                                    <th>Venta</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($movimientos as $movimiento): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($movimiento['nombre_producto']); ?></td>
                                                        <td><?php echo htmlspecialchars($movimiento['tipo_movimiento']); ?></td>
                                                        <td><?php echo htmlspecialchars($movimiento['cantidad']); ?></td>
                                                        <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($movimiento['fecha_movimiento']))); ?></td>
                                                        <td><?php echo htmlspecialchars($movimiento['usuario_responsable'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($movimiento['descripcion'] ?? 'N/A'); ?></td>
                                                        <td><?php echo ($movimiento['id_venta']) ? '<span class="badge badge-success">#' . htmlspecialchars($movimiento['id_venta']) . '</span>' : 'N/A'; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="ajustarStockModal" tabindex="-1" aria-labelledby="ajustarStockModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="ajustarStockModalLabel"><i class="fas fa-edit mr-2"></i> Ajustar Stock</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="post" action="">
                                <div class="modal-body">
                                    <input type="hidden" id="id_producto_ajuste" name="id_producto_ajuste">
                                    <p><strong id="nombre_producto_ajuste"></strong> - Stock Actual: <span id="stock_actual"></span></p>
                                    <div class="form-group">
                                        <label for="nuevo_stock"><i class="fas fa-cubes mr-2"></i> Nuevo Stock:</label>
                                        <input type="number" class="form-control" id="nuevo_stock" name="nuevo_stock" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-2"></i> Cancelar</button>
                                    <button type="submit" class="btn btn-primary" name="ajustar_stock"><i class="fas fa-check mr-2"></i> Guardar Ajuste</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#ajustarStockModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var nombre = button.data('nombre');
                var stock = button.data('stock');

                $('#id_producto_ajuste').val(id);
                $('#nombre_producto_ajuste').text(nombre);
                $('#stock_actual').text(stock);
                $('#nuevo_stock').val(stock); 
            });
        });
    </script>
</body>
</html>