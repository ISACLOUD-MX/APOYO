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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $rol = $_POST['rol'] ?? 'punto_venta'; 

    if (!empty($nombre_usuario) && !empty($contrasena)) {
      
        $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
        $stmt_check->bind_param("s", $nombre_usuario);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $mensaje = "<div class='alert alert-danger'>El nombre de usuario ya existe.</div>";
        } else {
            
            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
            
            $stmt_insert = $conn->prepare("INSERT INTO usuarios (nombre_usuario, contrasena, rol) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $nombre_usuario, $contrasena_hash, $rol);

            if ($stmt_insert->execute()) {
                $mensaje = "<div class='alert alert-success'>Usuario creado exitosamente.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al crear el usuario. Por favor, inténtelo de nuevo.</div>";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>Por favor, complete todos los campos.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Usuario</title>
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
                            <a class="nav-link active" href="admin_modif_user.php">
                                <i class="fas fa-users"></i> Usuarios
                            </a>
                        </li>
																		
                        <li class="nav-item">
                            <a class="nav-link" href="admin_productos.php">
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
                    <h1 class="h2"><i class="fas fa-user-plus mr-2"></i> Crear Nuevo Usuario</h1>
                </div>

                <div class="content-wrapper">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="form-container">
                                <h2 class="text-center"><i class="fas fa-user-plus mr-2"></i> Información del Nuevo Usuario</h2>
                                <form method="post">
                                    <div class="form-group">
                                        <label for="nombre_usuario"><i class="fas fa-user mr-2"></i> Nombre de Usuario:</label>
                                        <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="contrasena"><i class="fas fa-key mr-2"></i> Contraseña:</label>
                                        <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                                        <small class="form-text text-muted">La contraseña se encriptará de forma segura.</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="rol"><i class="fas fa-briefcase mr-2"></i> Rol:</label>
                                        <select class="form-control" id="rol" name="rol">
                                            <option value="administrador">Administrador</option>
                                            <option value="punto_venta" selected>Punto de Venta</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save mr-2"></i> Crear Usuario</button>
                                </form>
                                <?php echo $mensaje; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>