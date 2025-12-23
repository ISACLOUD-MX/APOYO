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
$proveedores = [];

$sql_proveedores = "SELECT id, nombre_proveedor, contacto_nombre, contacto_telefono, contacto_email, direccion, rfc, notas FROM proveedores ORDER BY nombre_proveedor ASC";
$result_proveedores = $conn->query($sql_proveedores);
if ($result_proveedores && $result_proveedores->num_rows > 0) {
    while ($row = $result_proveedores->fetch_assoc()) {
        $proveedores[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_proveedor'])) {
    $nombre_proveedor = $_POST['nombre_proveedor'] ?? '';
    $contacto_nombre = $_POST['contacto_nombre'] ?? '';
    $contacto_telefono = $_POST['contacto_telefono'] ?? '';
    $contacto_email = $_POST['contacto_email'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $rfc = $_POST['rfc'] ?? '';
    $notas = $_POST['notas'] ?? '';

    if (!empty($nombre_proveedor)) {
        $stmt_check = $conn->prepare("SELECT id FROM proveedores WHERE nombre_proveedor = ?");
        $stmt_check->bind_param("s", $nombre_proveedor);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $mensaje = "<div class='alert alert-danger'>El proveedor ya existe.</div>";
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO proveedores (nombre_proveedor, contacto_nombre, contacto_telefono, contacto_email, direccion, rfc, notas) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssssss", $nombre_proveedor, $contacto_nombre, $contacto_telefono, $contacto_email, $direccion, $rfc, $notas);

            if ($stmt_insert->execute()) {
                $mensaje = "<div class='alert alert-success'>Proveedor creado exitosamente.</div>";
                
                $proveedores = [];
                $result_proveedores = $conn->query($sql_proveedores);
                if ($result_proveedores && $result_proveedores->num_rows > 0) {
                    while ($row = $result_proveedores->fetch_assoc()) {
                        $proveedores[] = $row;
                    }
                }
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al crear el proveedor. Por favor, inténtelo de nuevo.</div>";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>El nombre del proveedor es obligatorio.</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_proveedor'])) {
    $id_proveedor_editar = $_POST['id_proveedor_editar'] ?? 0;
    $nombre_proveedor_editar = $_POST['nombre_proveedor_editar'] ?? '';
    $contacto_nombre_editar = $_POST['contacto_nombre_editar'] ?? '';
    $contacto_telefono_editar = $_POST['contacto_telefono_editar'] ?? '';
    $contacto_email_editar = $_POST['contacto_email_editar'] ?? '';
    $direccion_editar = $_POST['direccion_editar'] ?? '';
    $rfc_editar = $_POST['rfc_editar'] ?? '';
    $notas_editar = $_POST['notas_editar'] ?? '';

    if ($id_proveedor_editar > 0 && !empty($nombre_proveedor_editar)) {
        $stmt_update = $conn->prepare("UPDATE proveedores SET nombre_proveedor = ?, contacto_nombre = ?, contacto_telefono = ?, contacto_email = ?, direccion = ?, rfc = ?, notas = ? WHERE id = ?");
        $stmt_update->bind_param("sssssssi", $nombre_proveedor_editar, $contacto_nombre_editar, $contacto_telefono_editar, $contacto_email_editar, $direccion_editar, $rfc_editar, $notas_editar, $id_proveedor_editar);

        if ($stmt_update->execute()) {
            $mensaje = "<div class='alert alert-success'>Proveedor actualizado exitosamente.</div>";
           
            $proveedores = [];
            $result_proveedores = $conn->query($sql_proveedores);
            if ($result_proveedores && $result_proveedores->num_rows > 0) {
                while ($row = $result_proveedores->fetch_assoc()) {
                    $proveedores[] = $row;
                }
            }
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al actualizar el proveedor. Por favor, inténtelo de nuevo.</div>";
        }
        $stmt_update->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>El nombre del proveedor es obligatorio para actualizar.</div>";
    }
}

if (isset($_GET['eliminar'])) {
    $id_proveedor_eliminar = $_GET['eliminar'];
    if (is_numeric($id_proveedor_eliminar) && $id_proveedor_eliminar > 0) {
        
        $stmt_delete = $conn->prepare("DELETE FROM proveedores WHERE id = ?");
        $stmt_delete->bind_param("i", $id_proveedor_eliminar);

        if ($stmt_delete->execute()) {
            $mensaje = "<div class='alert alert-success'>Proveedor eliminado exitosamente.</div>";
            
            $proveedores = [];
            $result_proveedores = $conn->query($sql_proveedores);
            if ($result_proveedores && $result_proveedores->num_rows > 0) {
                while ($row = $result_proveedores->fetch_assoc()) {
                    $proveedores[] = $row;
                }
            }
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al eliminar el proveedor. Por favor, inténtelo de nuevo.</div>";
        }
        $stmt_delete->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>ID de proveedor inválido para eliminar.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Proveedores</title>
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
                            <a class="nav-link" href="admin_productos.php">
                                <i class="fas fa-box-open"></i> Productos
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="admin_proveedores.php">
                                <i class="fas fa-truck"></i> Proveedores
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
                    <h1 class="h2"><i class="fas fa-truck mr-2"></i> Administrar Proveedores</h1>
                </div>

                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-container">
                                <h2 class="text-center"><i class="fas fa-plus-circle mr-2"></i> Crear Nuevo Proveedor</h2>
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="nombre_proveedor"><i class="fas fa-building mr-2"></i> Nombre del Proveedor:</label>
                                        <input type="text" class="form-control" id="nombre_proveedor" name="nombre_proveedor" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="contacto_nombre"><i class="fas fa-user mr-2"></i> Nombre de Contacto (opcional):</label>
                                        <input type="text" class="form-control" id="contacto_nombre" name="contacto_nombre">
                                    </div>
                                    <div class="form-group">
                                        <label for="contacto_telefono"><i class="fas fa-phone mr-2"></i> Teléfono de Contacto (opcional):</label>
                                        <input type="tel" class="form-control" id="contacto_telefono" name="contacto_telefono">
                                    </div>
                                    <div class="form-group">
                                        <label for="contacto_email"><i class="fas fa-envelope mr-2"></i> Email de Contacto (opcional):</label>
                                        <input type="email" class="form-control" id="contacto_email" name="contacto_email">
                                    </div>
                                    <div class="form-group">
                                        <label for="direccion"><i class="fas fa-map-marker-alt mr-2"></i> Dirección (opcional):</label>
                                        <textarea class="form-control" id="direccion" name="direccion" rows="3"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="rfc"><i class="fas fa-id-card mr-2"></i> RFC (opcional, único):</label>
                                        <input type="text" class="form-control" id="rfc" name="rfc">
                                    </div>
                                    <div class="form-group">
                                        <label for="notas"><i class="fas fa-sticky-note mr-2"></i> Notas (opcional):</label>
                                        <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block" name="crear_proveedor"><i class="fas fa-save mr-2"></i> Crear Proveedor</button>
                                </form>
                                <?php echo $mensaje; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-container">
                                <h2 class="text-center"><i class="fas fa-list-alt mr-2"></i> Proveedores Existentes</h2>
                                <?php if (empty($proveedores)): ?>
                                    <p>No hay proveedores registrados aún.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Contacto</th>
                                                    <th>Teléfono</th>
                                                    <th>Acciones</th>
                                                </tr>
												</thead>
                                            <tbody>
                                                <?php foreach ($proveedores as $proveedor): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($proveedor['nombre_proveedor']); ?></td>
                                                        <td><?php echo htmlspecialchars($proveedor['contacto_nombre'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($proveedor['contacto_telefono'] ?? 'N/A'); ?></td>
                                                        <td class="text-center">
                                                            <button class="btn btn-sm btn-success btn-editar-proveedor"
                                                                    data-toggle="modal"
                                                                    data-target="#editarProveedorModal"
                                                                    data-id="<?php echo htmlspecialchars($proveedor['id']); ?>"
                                                                    data-nombre="<?php echo htmlspecialchars($proveedor['nombre_proveedor']); ?>"
                                                                    data-contacto_nombre="<?php echo htmlspecialchars($proveedor['contacto_nombre']); ?>"
                                                                    data-contacto_telefono="<?php echo htmlspecialchars($proveedor['contacto_telefono']); ?>"
                                                                    data-contacto_email="<?php echo htmlspecialchars($proveedor['contacto_email']); ?>"
                                                                    data-direccion="<?php echo htmlspecialchars($proveedor['direccion']); ?>"
                                                                    data-rfc="<?php echo htmlspecialchars($proveedor['rfc']); ?>"
                                                                    data-notas="<?php echo htmlspecialchars($proveedor['notas']); ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <a href="?eliminar=<?php echo htmlspecialchars($proveedor['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este proveedor?')">
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

                <div class="modal fade" id="editarProveedorModal" tabindex="-1" aria-labelledby="editarProveedorModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editarProveedorModalLabel"><i class="fas fa-edit mr-2"></i> Editar Proveedor</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="post" action="">
                                <div class="modal-body">
                                    <input type="hidden" id="id_proveedor_editar" name="id_proveedor_editar">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nombre_proveedor_editar"><i class="fas fa-building mr-2"></i> Nombre del Proveedor:</label>
                                                <input type="text" class="form-control" id="nombre_proveedor_editar" name="nombre_proveedor_editar" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="contacto_nombre_editar"><i class="fas fa-user mr-2"></i> Nombre de Contacto (opcional):</label>
                                                <input type="text" class="form-control" id="contacto_nombre_editar" name="contacto_nombre_editar">
                                            </div>
                                            <div class="form-group">
                                                <label for="contacto_telefono_editar"><i class="fas fa-phone mr-2"></i> Teléfono de Contacto (opcional):</label>
                                                <input type="tel" class="form-control" id="contacto_telefono_editar" name="contacto_telefono_editar">
                                            </div>
                                            <div class="form-group">
                                                <label for="contacto_email_editar"><i class="fas fa-envelope mr-2"></i> Email de Contacto (opcional):</label>
                                                <input type="email" class="form-control" id="contacto_email_editar" name="contacto_email_editar">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="direccion_editar"><i class="fas fa-map-marker-alt mr-2"></i> Dirección (opcional):</label>
                                                <textarea class="form-control" id="direccion_editar" name="direccion_editar" rows="3"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="rfc_editar"><i class="fas fa-id-card mr-2"></i> RFC (opcional, único):</label>
                                                <input type="text" class="form-control" id="rfc_editar" name="rfc_editar">
                                            </div>
                                            <div class="form-group">
                                                <label for="notas_editar"><i class="fas fa-sticky-note mr-2"></i> Notas (opcional):</label>
                                                <textarea class="form-control" id="notas_editar" name="notas_editar" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-2"></i> Cancelar</button>
                                    <button type="submit" class="btn btn-success" name="actualizar_proveedor"><i class="fas fa-save mr-2"></i> Guardar Cambios</button>
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
            $('#editarProveedorModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var nombre = button.data('nombre');
                var contacto_nombre = button.data('contacto_nombre');
                var contacto_telefono = button.data('contacto_telefono');
                var contacto_email = button.data('contacto_email');
                var direccion = button.data('direccion');
                var rfc = button.data('rfc');
                var notas = button.data('notas');

                $('#id_proveedor_editar').val(id);
                $('#nombre_proveedor_editar').val(nombre);
                $('#contacto_nombre_editar').val(contacto_nombre);
                $('#contacto_telefono_editar').val(contacto_telefono);
                $('#contacto_email_editar').val(contacto_email);
                $('#direccion_editar').val(direccion);
                $('#rfc_editar').val(rfc);
                $('#notas_editar').val(notas);
            });
        });
    </script>
</body>
</html>