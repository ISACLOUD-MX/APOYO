<?php

//CloudMR 2025

session_start();
if (isset($_SESSION['usuario'])) {
    if ($_SESSION['rol'] === 'administrador') {
        header("Location: admon_panel_central.php");
    } elseif ($_SESSION['rol'] === 'punto_venta') {
        header("Location: punto_venta_user.php");
    }
    exit();
}

require_once 'config.php';

$error_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];

    $conn = conectarDB();

    $sql = "SELECT id, nombre_usuario, contrasena, rol, intentos_fallidos, bloqueo_temporal, bloqueo_permanente FROM usuarios WHERE nombre_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nombre_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();

      
        if ($usuario['bloqueo_permanente']) {
            $error_mensaje = "Su cuenta ha sido bloqueada permanentemente. Contacte al administrador.";
        } elseif ($usuario['bloqueo_temporal'] !== null && strtotime($usuario['bloqueo_temporal']) > time()) {
            $tiempo_restante = ceil((strtotime($usuario['bloqueo_temporal']) - time()) / 60);
            $error_mensaje = "Su cuenta está bloqueada temporalmente. Intente nuevamente en " . $tiempo_restante . " minutos.";
        } else {
            if (password_verify($contrasena, $usuario['contrasena'])) {
                
                $_SESSION['usuario'] = $usuario['nombre_usuario'];
                $_SESSION['rol'] = $usuario['rol'];

               
                $reset_sql = "UPDATE usuarios SET intentos_fallidos = 0, bloqueo_temporal = NULL WHERE id = ?";
                $reset_stmt = $conn->prepare($reset_sql);
                $reset_stmt->bind_param("i", $usuario['id']);
                $reset_stmt->execute();

             
                if ($usuario['rol'] === 'administrador') {
                    header("Location: admon_panel_central.php");
                } elseif ($usuario['rol'] === 'punto_venta') {
                    header("Location: punto_venta_user.php");
                }
                exit();
            } else {
               
                $intentos = $usuario['intentos_fallidos'] + 1;
                $update_sql = "UPDATE usuarios SET intentos_fallidos = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $intentos, $usuario['id']);
                $update_stmt->execute();

                if ($intentos === 3) {
                    $bloqueo_temporal = date('Y-m-d H:i:s', time() + (60 * 60)); // Bloquear por 1 hora
                    $bloqueo_sql = "UPDATE usuarios SET bloqueo_temporal = ? WHERE id = ?";
                    $bloqueo_stmt = $conn->prepare($bloqueo_sql);
                    $bloqueo_stmt->bind_param("si", $bloqueo_temporal, $usuario['id']);
                    $bloqueo_stmt->execute();
                    $error_mensaje = "Contraseña incorrecta. Su cuenta ha sido bloqueada por 1 hora.";
                } elseif ($intentos >= 5) {
                    $bloqueo_permanente = 1;
                    $bloqueo_sql = "UPDATE usuarios SET bloqueo_permanente = ? WHERE id = ?";
                    $bloqueo_stmt = $conn->prepare($bloqueo_sql);
                    $bloqueo_stmt->bind_param("ii", $bloqueo_permanente, $usuario['id']);
                    $bloqueo_stmt->execute();
                    $error_mensaje = "Demasiados intentos fallidos. Su cuenta ha sido bloqueada permanentemente.";
                } else {
                    $error_mensaje = "Contraseña incorrecta. Le quedan " . (3 - $intentos) . " intentos antes de un bloqueo temporal.";
                }
            }
        }
    } else {
        $error_mensaje = "Nombre de usuario incorrecto.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABAPOST</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/login_styles.css">
    <link rel="stylesheet" href="style.css"> <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-form .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .login-form .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        .login-form .form-control {
            padding-left: 40px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .login-form .btn-primary {
            width: 100%;
            border: none;
            border-radius: 5px;
            padding: 12px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
            position: relative; /* Para la barra de carga */
            overflow: hidden; /* Para ocultar la barra de carga fuera del botón */
        }

        .login-form .btn-primary:hover {
            background-color: #0056b3;
        }

        .login-form .progress-bar-container {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background-color: #eee;
            border-radius: 0 0 5px 5px;
            overflow: hidden;
            margin-top: 5px; /* Espacio entre el botón y la barra */
        }

        .login-form .progress-bar {
            background-color: #28a745;
            height: 100%;
            width: 0%;
            border-radius: 0 0 5px 5px;
            transition: width 0.5s ease-in-out;
        }

        .error-message {
            color: red;
            margin-top: 10px;
            text-align: center;
        }

        .logo-container {
            position: absolute;
            top: 20px;
            left: 20px;
            max-width: 150px;
            height: auto;
        }

        @media (max-width: 576px) {
            .login-container {
                margin: 20px;
            }

            .logo-container {
                position: static;
                text-align: center;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="logo-container">
        <img src="imagenes/logo_abarrotes.png" alt="Logotipo" class="img-fluid">
    </div>

    <div class="login-container">
        <div class="login-header">
            <h2>ABAPOST</h2>
            <p class="text-muted">Ingrese Sus Datos</p>
        </div>
        <form class="login-form" method="post">
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" placeholder="Usuario" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary">
                Iniciar Sesión
                <div class="progress-bar-container">
                    <div class="progress-bar" id="login-progress"></div>
                </div>
            </button>
            <?php if (!empty($error_mensaje)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_mensaje); ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        document.querySelector('.btn-primary').addEventListener('click', function() {
            const progressBar = document.getElementById('login-progress');
            let width = 0;
            const interval = setInterval(function() {
                if (width >= 100) {
                    clearInterval(interval);
                    
                } else {
                    width++;
                    progressBar.style.width = width + '%';
                }
            }, 30); 
        });
    </script>
	
    <?php include 'inferior_menu.php'; ?>
    <div class="header-marquee-container">
        <div class="abstract-bg"></div>
        <div class="marquee-content-wrapper">
            <div class="marquee-text marquee-center">
                <span><?php echo COPYRIGHT_TEXT; ?></span>
            </div>
            <div class="version-text">
                <span><?php echo VERSION_TEXT; ?></span>
            </div>
        </div>
    </div>



</body>
</html>