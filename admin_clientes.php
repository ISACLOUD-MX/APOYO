<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();

$mensaje = "";
$clientes = [];

$sql_clientes = "SELECT id, nombre_cliente, direccion, telefono, email FROM clientes ORDER BY nombre_cliente ASC";
$result_clientes = $conn->query($sql_clientes);
if ($result_clientes && $result_clientes->num_rows > 0) {
    while ($row = $result_clientes->fetch_assoc()) {
        $clientes[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_cliente'])) {
    $nombre_cliente = $_POST['nombre_cliente'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';

    if (!empty($nombre_cliente)) {
        $stmt_insert = $conn->prepare("INSERT INTO clientes (nombre_cliente, direccion, telefono, email) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("ssss", $nombre_cliente, $direccion, $telefono, $email);

        if ($stmt_insert->execute()) {
            $mensaje = "<div class='alert alert-success'>Cliente creado exitosamente.</div>";
           
            $clientes = [];
            $result_clientes = $conn->query($sql_clientes);
            if ($result_clientes && $result_clientes->num_rows > 0) {
                while ($row = $result_clientes->fetch_assoc()) {
                    $clientes[] = $row;
                }
            }
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al crear el cliente. Por favor, inténtelo de nuevo.</div>";
        }
        $stmt_insert->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>El nombre del cliente es obligatorio.</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_cliente'])) {
    $id_cliente_editar = $_POST['id_cliente_editar'] ?? 0;
    $nombre_cliente_editar = $_POST['nombre_cliente_editar'] ?? '';
    $direccion_editar = $_POST['direccion_editar'] ?? '';
    $telefono_editar = $_POST['telefono_editar'] ?? '';
    $email_editar = $_POST['email_editar'] ?? '';

    if ($id_cliente_editar > 0 && !empty($nombre_cliente_editar)) {
        $stmt_update = $conn->prepare("UPDATE clientes SET nombre_cliente = ?, direccion = ?, telefono = ?, email = ? WHERE id = ?");
        $stmt_update->bind_param("ssssi", $nombre_cliente_editar, $direccion_editar, $telefono_editar, $email_editar, $id_cliente_editar);

        if ($stmt_update->execute()) {
            $mensaje = "<div class='alert alert-success'>Cliente actualizado exitosamente.</div>";

            $clientes = [];
            $result_clientes = $conn->query($sql_clientes);
            if ($result_clientes && $result_clientes->num_rows > 0) {
                while ($row = $result_clientes->fetch_assoc()) {
                    $clientes[] = $row;
                }
            }
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al actualizar el cliente. Por favor, inténtelo de nuevo.</div>";
        }
        $stmt_update->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>El nombre del cliente es obligatorio para actualizar.</div>";
    }
}

if (isset($_GET['eliminar'])) {
    $id_cliente_eliminar = $_GET['eliminar'];
    if (is_numeric($id_cliente_eliminar) && $id_cliente_eliminar > 0) {
        
        $stmt_delete = $conn->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt_delete->bind_param("i", $id_cliente_eliminar);

        if ($stmt_delete->execute()) {
            $mensaje = "<div class='alert alert-success'>Cliente eliminado exitosamente.</div>";        
            $clientes = [];
            $result_clientes = $conn->query($sql_clientes);
            if ($result_clientes && $result_clientes->num_rows > 0) {
                while ($row = $result_clientes->fetch_assoc()) {
                    $clientes[] = $row;
                }
            }
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al eliminar el cliente. Por favor, inténtelo de nuevo.</div>";
        }
        $stmt_delete->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>ID de cliente inválido para eliminar.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Clientes</title>
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
                        <img src="imagenes/logo_abarrotes.png" alt="Logo Abarrotes" class="img-fluid mb-3" style="max-width: 150px;">
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
                            <a class="nav-link" href="admin_productos.php">
                                <i class="fas fa-box-open"></i> Productos
                            </a>
                        </li>
                        
                        <li class="nav-item active">
                            <a class="nav-link" href="admin_clientes.php">
                                <i class="fas fa-users"></i> Clientes
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
                    <h1 class="h2"><i class="fas fa-users mr-2"></i> Administrar Clientes</h1>
                </div>

                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-container">
                                <h2 class="text-center"><i class="fas fa-user-plus mr-2"></i> Crear Nuevo Cliente</h2>
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="nombre_cliente"><i class="fas fa-user mr-2"></i> Nombre del Cliente:</label>
                                        <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="direccion"><i class="fas fa-map-marker-alt mr-2"></i> Dirección (opcional):</label>
                                        <input type="text" class="form-control" id="direccion" name="direccion">
                                    </div>
                                    <div class="form-group">
                                        <label for="telefono"><i class="fas fa-phone mr-2"></i> Teléfono (opcional):</label>
                                        <input type="tel" class="form-control" id="telefono" name="telefono">
                                    </div>
                                    <div class="form-group">
                                        <label for="email"><i class="fas fa-envelope mr-2"></i> Email (opcional):</label>
                                        <input type="email" class="form-control" id="email" name="email">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block" name="crear_cliente"><i class="fas fa-save mr-2"></i> Crear Cliente</button>
                                </form>
                                <?php echo $mensaje; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-container">
                                <h2 class="text-center"><i class="fas fa-list-alt mr-2"></i> Clientes Existentes</h2>
                                <?php if (empty($clientes)): ?>
                                    <p>No hay clientes registrados aún.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Teléfono</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($clientes as $cliente): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($cliente['nombre_cliente']); ?></td>
                                                        <td><?php echo htmlspecialchars($cliente['telefono'] ?? 'N/A'); ?></td>
                                                        <td class="text-center">
                                                            <button class="btn btn-sm btn-success btn-editar-cliente"
                                                                    data-toggle="modal"
                                                                    data-target="#editarClienteModal"
                                                                    data-id="<?php echo htmlspecialchars($cliente['id']); ?>"
                                                                    data-nombre="<?php echo htmlspecialchars($cliente['nombre_cliente']); ?>"
                                                                    data-direccion="<?php echo htmlspecialchars($cliente['direccion']); ?>"
                                                                    data-telefono="<?php echo htmlspecialchars($cliente['telefono']); ?>"
                                                                    data-email="<?php echo htmlspecialchars($cliente['email']); ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <a href="?eliminar=<?php echo htmlspecialchars($cliente['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este cliente?')">
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

                <div class="modal fade" id="editarClienteModal" tabindex="-1" aria-labelledby="editarClienteModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editarClienteModalLabel"><i class="fas fa-edit mr-2"></i> Editar Cliente</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="post" action="">
                                <div class="modal-body">
                                    <input type="hidden" id="id_cliente_editar" name="id_cliente_editar">
                                    <div class="form-group">
                                        <label for="nombre_cliente_editar"><i class="fas fa-user mr-2"></i> Nombre del Cliente:</label>
                                        <input type="text" class="form-control" id="nombre_cliente_editar" name="nombre_cliente_editar" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="direccion_editar"><i class="fas fa-map-marker-alt mr-2"></i> Dirección (opcional):</label>
										<input type="text" class="form-control" id="direccion_editar" name="direccion_editar">
                                    </div>
                                    <div class="form-group">
                                        <label for="telefono_editar"><i class="fas fa-phone mr-2"></i> Teléfono (opcional):</label>
                                        <input type="tel" class="form-control" id="telefono_editar" name="telefono_editar">
                                    </div>
                                    <div class="form-group">
                                        <label for="email_editar"><i class="fas fa-envelope mr-2"></i> Email (opcional):</label>
                                        <input type="email" class="form-control" id="email_editar" name="email_editar">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-2"></i> Cancelar</button>
                                    <button type="submit" class="btn btn-success" name="actualizar_cliente"><i class="fas fa-save mr-2"></i> Guardar Cambios</button>
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
            $('#editarClienteModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var nombre = button.data('nombre');
                var direccion = button.data('direccion');
                var telefono = button.data('telefono');
                var email = button.data('email');

                $('#id_cliente_editar').val(id);
                $('#nombre_cliente_editar').val(nombre);
                $('#direccion_editar').val(direccion);
                $('#telefono_editar').val(telefono);
                $('#email_editar').val(email);
            });
        });
    </script>
</body>
</html>