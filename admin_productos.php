<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();

$mensaje = "";
$productos = [];
$categorias = [];
$unidades_medidas = []; 

$sql_unidades_medidas = "SELECT id, nombre_unidad FROM unidades_medidas ORDER BY nombre_unidad ASC";
$result_unidades_medidas = $conn->query($sql_unidades_medidas);
if ($result_unidades_medidas && $result_unidades_medidas->num_rows > 0) {
    while ($row = $result_unidades_medidas->fetch_assoc()) {
        $unidades_medidas[] = $row;
    }
}


$sql_categorias = "SELECT id, nombre_categoria FROM categorias ORDER BY nombre_categoria ASC";
$result_categorias = $conn->query($sql_categorias);
if ($result_categorias && $result_categorias->num_rows > 0) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}


$sql_productos = "SELECT p.id, p.nombre_producto, p.descripcion, p.id_categoria, c.nombre_categoria AS nombre_categoria_fk, 
                         p.precio_compra, p.precio_venta, p.stock, p.codigo_barras, 
                         p.id_medida, um.nombre_unidad AS nombre_unidad_fk -- Añadir id_medida y nombre de la unidad
                  FROM productos p
                  INNER JOIN categorias c ON p.id_categoria = c.id
                  LEFT JOIN unidades_medidas um ON p.id_medida = um.id -- Unir con unidades_medidas
                  ORDER BY p.nombre_producto ASC";

$result_productos = $conn->query($sql_productos);
if ($result_productos && $result_productos->num_rows > 0) {
    while ($row = $result_productos->fetch_assoc()) {
        $productos[] = $row;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_producto'])) {
    $nombre_producto = $_POST['nombre_producto'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $id_categoria = $_POST['id_categoria'] ?? 0;
    $precio_compra = $_POST['precio_compra'] ?? 0;
    $precio_venta = $_POST['precio_venta'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $codigo_barras = $_POST['codigo_barras'] ?? '';
    $id_medida = $_POST['id_medida'] ?? 0; 

    if (!empty($nombre_producto) && $id_categoria > 0 && is_numeric($precio_compra) && is_numeric($precio_venta) && is_numeric($stock) && !empty($codigo_barras) && $id_medida > 0) { // Validar id_medida
        $stmt_check = $conn->prepare("SELECT id FROM productos WHERE codigo_barras = ?");
        $stmt_check->bind_param("s", $codigo_barras);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $mensaje = "<div class='alert alert-danger'>El código de barras ya existe.</div>";
        } else {
            
            $stmt_insert = $conn->prepare("INSERT INTO productos (nombre_producto, descripcion, id_categoria, precio_compra, precio_venta, stock, codigo_barras, id_medida) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("ssiddssi", $nombre_producto, $descripcion, $id_categoria, $precio_compra, $precio_venta, $stock, $codigo_barras, $id_medida);

            if ($stmt_insert->execute()) {
                $mensaje = "<div class='alert alert-success'>Producto creado exitosamente.</div>";
             
                $productos = [];
                $result_productos = $conn->query($sql_productos);
                if ($result_productos && $result_productos->num_rows > 0) {
                    while ($row = $result_productos->fetch_assoc()) {
                        $productos[] = $row;
                    }
                }
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al crear el producto: " . $stmt_insert->error . "</div>";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>Por favor, complete todos los campos obligatorios.</div>";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_producto'])) {
    $id_producto_editar = $_POST['id_producto_editar'] ?? 0;
    $nombre_producto_editar = $_POST['nombre_producto_editar'] ?? '';
    $descripcion_editar = $_POST['descripcion_editar'] ?? '';
    $id_categoria_editar = $_POST['id_categoria_editar'] ?? 0;
    $precio_compra_editar = $_POST['precio_compra_editar'] ?? 0;
    $precio_venta_editar = $_POST['precio_venta_editar'] ?? 0;
    $stock_editar = $_POST['stock_editar'] ?? 0;
    $codigo_barras_editar = $_POST['codigo_barras_editar'] ?? '';
    $id_medida_editar = $_POST['id_medida_editar'] ?? 0; 

    if ($id_producto_editar > 0 && !empty($nombre_producto_editar) && $id_categoria_editar > 0 && is_numeric($precio_compra_editar) && is_numeric($precio_venta_editar) && is_numeric($stock_editar) && !empty($codigo_barras_editar) && $id_medida_editar > 0) { // Validar id_medida_editar
        
        $stmt_check_barcode = $conn->prepare("SELECT id FROM productos WHERE codigo_barras = ? AND id != ?");
        $stmt_check_barcode->bind_param("si", $codigo_barras_editar, $id_producto_editar);
        $stmt_check_barcode->execute();
        $result_check_barcode = $stmt_check_barcode->get_result();

        if ($result_check_barcode->num_rows > 0) {
            $mensaje = "<div class='alert alert-danger'>El código de barras ya existe para otro producto.</div>";
        } else {
           
            $stmt_update = $conn->prepare("UPDATE productos SET nombre_producto = ?, descripcion = ?, id_categoria = ?, precio_compra = ?, precio_venta = ?, stock = ?, codigo_barras = ?, id_medida = ? WHERE id = ?");
            $stmt_update->bind_param("ssiddssii", $nombre_producto_editar, $descripcion_editar, $id_categoria_editar, $precio_compra_editar, $precio_venta_editar, $stock_editar, $codigo_barras_editar, $id_medida_editar, $id_producto_editar);

            if ($stmt_update->execute()) {
                $mensaje = "<div class='alert alert-success'>Producto actualizado exitosamente.</div>";
                
                $productos = [];
                $result_productos = $conn->query($sql_productos);
                if ($result_productos && $result_productos->num_rows > 0) {
                    while ($row = $result_productos->fetch_assoc()) {
                        $productos[] = $row;
                    }
                }
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al actualizar el producto: " . $stmt_update->error . "</div>";
            }
            $stmt_update->close();
        }
        $stmt_check_barcode->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>Por favor, complete todos los campos obligatorios para actualizar el producto.</div>";
    }
}


if (isset($_GET['eliminar'])) {
    $id_producto_eliminar = $_GET['eliminar'];
    if (is_numeric($id_producto_eliminar) && $id_producto_eliminar > 0) {
        $stmt_delete = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt_delete->bind_param("i", $id_producto_eliminar);

        if ($stmt_delete->execute()) {
            $mensaje = "<div class='alert alert-success'>Producto eliminado exitosamente.</div>";
           
            $productos = [];
            $result_productos = $conn->query($sql_productos);
            if ($result_productos && $result_productos->num_rows > 0) {
                while ($row = $result_productos->fetch_assoc()) {
                    $productos[] = $row;
                }
            }
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al eliminar el producto: " . $stmt_delete->error . "</div>";
        }
        $stmt_delete->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>ID de producto inválido para eliminar.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Productos</title>
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
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
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
                            <a class="nav-link active" href="admin_productos.php">
                                <i class="fas fa-box-open"></i> Productos
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
                    <h1 class="h2"><i class="fas fa-box-open mr-2"></i> Administrar Productos</h1>
                </div>

                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-container">
                                <h2 class="text-center"><i class="fas fa-plus-circle mr-2"></i> Crear Nuevo Producto</h2>
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="nombre_producto"><i class="fas fa-shopping-bag mr-2"></i> Nombre del Producto:</label>
                                        <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="descripcion"><i class="fas fa-file-alt mr-2"></i> Descripción (opcional):</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="id_categoria"><i class="fas fa-tag mr-2"></i> Departamento:</label>
                                        <select class="form-control" id="id_categoria" name="id_categoria" required>
                                            <option value="">Seleccionar Departamento</option>
                                            <?php foreach ($categorias as $cat): ?>
                                                <option value="<?php echo htmlspecialchars($cat['id']); ?>"><?php echo htmlspecialchars($cat['nombre_categoria']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="id_medida"><i class="fas fa-ruler-combined mr-2"></i> Unidad Del Producto:</label>
                                        <select class="form-control" id="id_medida" name="id_medida" required>
                                            <option value="">Seleccionar Unidad</option>
                                            <?php foreach ($unidades_medidas as $um): ?>
                                                <option value="<?php echo htmlspecialchars($um['id']); ?>"><?php echo htmlspecialchars($um['nombre_unidad']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="precio_compra"><i class="fas fa-dollar-sign mr-2"></i> Precio de Compra:</label>
                                        <input type="number" class="form-control" id="precio_compra" name="precio_compra" step="0.01" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="precio_venta"><i class="fas fa-hand-holding-usd mr-2"></i> Precio de Venta:</label>
                                        <input type="number" class="form-control" id="precio_venta" name="precio_venta" step="0.01" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="stock"><i class="fas fa-cubes mr-2"></i> Stock:</label>
                                        <input type="number" class="form-control" id="stock" name="stock" value="0" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="codigo_barras"><i class="fas fa-barcode mr-2"></i> Código de Barras:</label>
                                        <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block" name="crear_producto"><i class="fas fa-save mr-2"></i> Crear Producto</button>
                                </form>
                                <?php echo $mensaje; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-container">
                                <h2 class="text-center"><i class="fas fa-list-alt mr-2"></i> Productos Existentes</h2>
                                <?php if (empty($productos)): ?>
                                    <p>No hay productos creados aún.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Departamento</th>
                                                    <th>Unidad</th> <th>Precio Venta</th>
                                                    <th>Stock</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($productos as $producto): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                                        <td><?php echo htmlspecialchars($producto['nombre_categoria_fk']); ?></td>
                                                        <td><?php echo htmlspecialchars($producto['nombre_unidad_fk'] ?? 'N/A'); ?></td> <td>$<?php echo htmlspecialchars(number_format($producto['precio_venta'], 2)); ?></td>
                                                        <td><?php echo htmlspecialchars($producto['stock']); ?></td>
                                                        <td class="text-center">
                                                            <button class="btn btn-sm btn-success btn-editar-producto"
                                                                    data-toggle="modal"
                                                                    data-target="#editarProductoModal"
                                                                    data-id="<?php echo htmlspecialchars($producto['id']); ?>"
                                                                    data-nombre="<?php echo htmlspecialchars($producto['nombre_producto']); ?>"
                                                                    data-descripcion="<?php echo htmlspecialchars($producto['descripcion']); ?>"
                                                                    data-id_categoria="<?php echo htmlspecialchars($producto['id_categoria']); ?>"
                                                                    data-precio_compra="<?php echo htmlspecialchars($producto['precio_compra']); ?>"
                                                                    data-precio_venta="<?php echo htmlspecialchars($producto['precio_venta']); ?>"
                                                                    data-stock="<?php echo htmlspecialchars($producto['stock']); ?>"
                                                                    data-codigo_barras="<?php echo htmlspecialchars($producto['codigo_barras']); ?>"
                                                                    data-id_medida="<?php echo htmlspecialchars($producto['id_medida']); ?>"> <i class="fas fa-edit"></i>
                                                            </button>
                                                            <a href="?eliminar=<?php echo htmlspecialchars($producto['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?')">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </a>
                                                        </td>
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

                <div class="modal fade" id="editarProductoModal" tabindex="-1" aria-labelledby="editarProductoModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editarProductoModalLabel"><i class="fas fa-edit mr-2"></i> Editar Producto</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="post" action="">
                                <div class="modal-body">
                                    <input type="hidden" id="id_producto_editar" name="id_producto_editar">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nombre_producto_editar"><i class="fas fa-shopping-bag mr-2"></i> Nombre del Producto:</label>
                                                <input type="text" class="form-control" id="nombre_producto_editar" name="nombre_producto_editar" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="descripcion_editar"><i class="fas fa-file-alt mr-2"></i> Descripción (opcional):</label>
                                                <textarea class="form-control" id="descripcion_editar" name="descripcion_editar" rows="3"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="id_categoria_editar"><i class="fas fa-tag mr-2"></i> Departamento:</label>
                                                <select class="form-control" id="id_categoria_editar" name="id_categoria_editar" required>
                                                    <option value="">Seleccionar Departamento</option>
                                                    <?php foreach ($categorias as $cat): ?>
                                                        <option value="<?php echo htmlspecialchars($cat['id']); ?>"><?php echo htmlspecialchars($cat['nombre_categoria']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="id_medida_editar"><i class="fas fa-ruler-combined mr-2"></i> Unidad Del Producto:</label>
                                                <select class="form-control" id="id_medida_editar" name="id_medida_editar" required>
                                                    <option value="">Seleccionar Unidad</option>
                                                    <?php foreach ($unidades_medidas as $um): ?>
                                                        <option value="<?php echo htmlspecialchars($um['id']); ?>"><?php echo htmlspecialchars($um['nombre_unidad']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="precio_compra_editar"><i class="fas fa-dollar-sign mr-2"></i> Precio de Compra:</label>
                                                <input type="number" class="form-control" id="precio_compra_editar" name="precio_compra_editar" step="0.01" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="precio_venta_editar"><i class="fas fa-hand-holding-usd mr-2"></i> Precio de Venta:</label>
                                                <input type="number" class="form-control" id="precio_venta_editar" name="precio_venta_editar" step="0.01" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="stock_editar"><i class="fas fa-cubes mr-2"></i> Stock:</label>
                                                <input type="number" class="form-control" id="stock_editar" name="stock_editar" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="codigo_barras_editar"><i class="fas fa-barcode mr-2"></i> Código de Barras:</label>
                                                <input type="text" class="form-control" id="codigo_barras_editar" name="codigo_barras_editar" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-2"></i> Cancelar</button>
                                    <button type="submit" class="btn btn-success" name="actualizar_producto"><i class="fas fa-save mr-2"></i> Guardar Cambios</button>
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
            $('#editarProductoModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var nombre = button.data('nombre');
                var descripcion = button.data('descripcion');
                var id_categoria = button.data('id_categoria');
                var precio_compra = button.data('precio_compra');
                var precio_venta = button.data('precio_venta');
                var stock = button.data('stock');
                var codigo_barras = button.data('codigo_barras');
                var id_medida = button.data('id_medida'); 

                $('#id_producto_editar').val(id);
                $('#nombre_producto_editar').val(nombre);
                $('#descripcion_editar').val(descripcion);
                $('#id_categoria_editar').val(id_categoria);
                $('#precio_compra_editar').val(precio_compra);
                $('#precio_venta_editar').val(precio_venta);
                $('#stock_editar').val(stock);
                $('#codigo_barras_editar').val(codigo_barras);
                $('#id_medida_editar').val(id_medida); 
            });
        });
    </script>
</body>
</html>