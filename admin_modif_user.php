<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();

$mensaje = '';
$usuarios = [];
$modo_edicion = false;
$usuario_editar = null;


if (isset($_GET['editar_id']) && is_numeric($_GET['editar_id'])) {
    $modo_edicion = true;
    $id_editar = $_GET['editar_id'];
    $sql_select_usuario = "SELECT id, nombre_usuario, contrasena, rol FROM usuarios WHERE id = ?";
    $stmt_select_usuario = $conn->prepare($sql_select_usuario);
    $stmt_select_usuario->bind_param("i", $id_editar);
    $stmt_select_usuario->execute();
    $result_usuario = $stmt_select_usuario->get_result();
    if ($result_usuario && $result_usuario->num_rows === 1) {
        $usuario_editar = $result_usuario->fetch_assoc();
    } else {
        $mensaje = '<div class="alert alert-warning mt-3">Usuario no encontrado para editar.</div>';
    }
    $stmt_select_usuario->close();
}


if (isset($_POST['actualizar_usuario'])) {
    $id_actualizar = $_POST['id_actualizar'];
    $nombre = $_POST['nombre_usuario'] ?? '';
    $rol = $_POST['rol'] ?? 'punto_venta';
    $contrasena = $_POST['contrasena'] ?? '';

    if (!empty($nombre)) {
        $sql_update = "UPDATE usuarios SET nombre_usuario=?, rol=?";
        if (!empty($contrasena)) {
            $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
            $sql_update .= ", contrasena=?";
        }
        $sql_update .= " WHERE id=?";
        $stmt_update = $conn->prepare($sql_update);
        if (!empty($contrasena)) {
            $stmt_update->bind_param("sssi", $nombre, $rol, $hashed_password, $id_actualizar);
        } else {
            $stmt_update->bind_param("sssi", $nombre, $rol, $id_actualizar);
        }

        if ($stmt_update->execute()) {
            $mensaje = '<div class="alert alert-success mt-3">Usuario actualizado exitosamente.</div>';
            $modo_edicion = false;
            $usuario_editar = null;
        } else {
            $mensaje = '<div class="alert alert-danger mt-3">Error al actualizar el usuario.</div>';
        }
        $stmt_update->close();
    } else {
        $mensaje = '<div class="alert alert-warning mt-3">Por favor, complete el campo Nombre.</div>';
    }
}


if (isset($_POST['eliminar_usuario']) && isset($_POST['id_eliminar'])) {
    $id_eliminar = $_POST['id_eliminar'];
    $sql_delete = "DELETE FROM usuarios WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id_eliminar);
    if ($stmt_delete->execute()) {
        $mensaje = '<div class="alert alert-success mt-3">Usuario eliminado exitosamente.</div>';
    } else {
        $mensaje = '<div class="alert alert-danger mt-3">Error al eliminar el usuario.</div>';
    }
    $stmt_delete->close();
}


$sql_select = "SELECT id, nombre_usuario, contrasena, rol FROM usuarios ORDER BY nombre_usuario ASC";
$result_usuarios = $conn->query($sql_select);
if ($result_usuarios && $result_usuarios->num_rows > 0) {
    while ($row = $result_usuarios->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuarios - Administración</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .content-wrapper {
            padding: 20px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .btn-editar, .btn-eliminar {
            margin-right: 5px;
        }
        .form-editar-usuario {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .form-editar-usuario .form-group {
            margin-bottom: 15px;
        }
        .form-editar-usuario label {
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
                        
                        <li class="nav-item">
                            <a class="nav-link" href="admin_reportes.php">
                                <i class="fas fa-chart-bar"></i> Reporte de Vendedores
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
                    <h1 class="h2"><i class="fas fa-users-cog mr-2"></i> *Modificar Usuarios*</h1>
                </div>

                <div class="content-wrapper">
                    <?php echo $mensaje; ?>

                    <h2><i class="fas fa-users mr-2"></i> Lista de Usuarios</h2>
                    <?php if (!empty($usuarios)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        
                                        <th>Nombre De Usuario</th>
                                        <th>Rol Asignado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            
                                            <td><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></td>
                                            <td><?php echo htmlspecialchars(ucfirst($usuario['rol'])); ?></td>
                                            <td>
                                                <a href="editar_usuario_admin.php?editar_id=<?php echo htmlspecialchars($usuario['id']); ?>" class="btn btn-sm btn-primary btn-editar"><i class="fas fa-edit"></i> Editar</a>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="id_eliminar" value="<?php echo htmlspecialchars($usuario['id']); ?>">
                                                    <button type="submit" name="eliminar_usuario" class="btn btn-sm btn-danger btn-eliminar" onclick="return confirm('¿Estás seguro de eliminar este usuario?')"><i class="fas fa-trash-alt"></i> Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No hay usuarios registrados.</div>
                    <?php endif; ?>

                    <?php if ($modo_edicion && $usuario_editar): ?>
                        <div class="form-editar-usuario">
                            <h3><i class="fas fa-edit mr-2"></i> Editar Usuario ID: <?php echo htmlspecialchars($usuario_editar['id']); ?></h3>
                            <form method="post" action="">
                                <input type="hidden" name="id_actualizar" value="<?php echo htmlspecialchars($usuario_editar['id']); ?>">
                                <div class="form-group">
                                    <label for="nombre_usuario"><i class="fas fa-user mr-2"></i> Nombre:</label>
                                    <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" value="<?php echo htmlspecialchars($usuario_editar['nombre_usuario']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="contrasena"><i class="fas fa-key mr-2"></i> Nueva Contraseña (dejar en blanco para no cambiar):</label>
                                    <input type="password" class="form-control" id="contrasena" name="contrasena">
                                </div>
                                <div class="form-group">
                                    <label for="rol"><i class="fas fa-user-tag mr-2"></i> Rol:</label>
                                    <select class="form-control" id="rol" name="rol">
                                        <option value="administrador" <?php echo ($usuario_editar['rol'] === 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                                        <option value="punto_venta" <?php echo ($usuario_editar['rol'] === 'punto_venta') ? 'selected' : ''; ?>>Punto de Venta</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary" name="actualizar_usuario"><i class="fas fa-save mr-2"></i> Guardar Cambios</button>
                                <a href="editar_usuario_admin.php" class="btn btn-secondary ml-2"><i class="fas fa-ban mr-2"></i> Cancelar Edición</a>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>