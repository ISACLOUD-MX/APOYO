<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();

$configuracion = [];
$alertas_stock = [];

$sql_config = "SELECT nombre_negocio, direccion_negocio, telefono_negocio, email_negocio, moneda_predeterminada, formato_fecha, logo_negocio, nivel_minimo_global, nivel_minimo_ventas_global FROM configuracion LIMIT 1";
$result_config = $conn->query($sql_config);
if ($result_config && $result_config->num_rows > 0) {
    $configuracion = $result_config->fetch_assoc();
}

$sql_alertas = "SELECT id, nivel_minimo FROM alertas_stock ORDER BY nivel_minimo ASC";
$result_alertas = $conn->query($sql_alertas);
if ($result_alertas && $result_alertas->num_rows > 0) {
    while ($row = $result_alertas->fetch_assoc()) {
        $alertas_stock[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['guardar_configuracion'])) {
        $nombre_negocio = $_POST['nombre_negocio'] ?? '';
        $direccion_negocio = $_POST['direccion_negocio'] ?? '';
        $telefono_negocio = $_POST['telefono_negocio'] ?? '';
        $email_negocio = $_POST['email_negocio'] ?? '';
        $moneda_predeterminada = $_POST['moneda_predeterminada'] ?? 'MXN';
        $formato_fecha = $_POST['formato_fecha'] ?? 'd/m/Y';
        $nivel_minimo_global = $_POST['nivel_minimo_global'] ?? null;
		$nivel_minimo_ventas_global = $_POST['nivel_minimo_ventas_global'] ?? null;
       
        $logo_negocio = $configuracion['logo_negocio'] ?? ''; 
     
        $check_config = $conn->query("SELECT COUNT(*) FROM configuracion");
        $config_exists = $check_config->fetch_row()[0] > 0;

        if ($config_exists) {
            $sql_update_config = "UPDATE configuracion SET nombre_negocio=?, direccion_negocio=?, telefono_negocio=?, email_negocio=?, moneda_predeterminada=?, formato_fecha=?, logo_negocio=?, nivel_minimo_global=?, nivel_minimo_ventas_global=?";
            $stmt_update_config = $conn->prepare($sql_update_config);
            $stmt_update_config->bind_param("sssssssii", $nombre_negocio, $direccion_negocio, $telefono_negocio, $email_negocio, $moneda_predeterminada, $formato_fecha, $logo_negocio, $nivel_minimo_global, $nivel_minimo_ventas_global);
            if ($stmt_update_config->execute()) {
                $mensaje_config = '<div class="alert alert-success mt-3">Configuración general guardada exitosamente.</div>';
            } else {
                $mensaje_config = '<div class="alert alert-danger mt-3">Error al guardar la configuración general.</div>';
            }
            $stmt_update_config->close();
        } else {
            $sql_insert_config = "INSERT INTO configuracion (nombre_negocio, direccion_negocio, telefono_negocio, email_negocio, moneda_predeterminada, formato_fecha, logo_negocio, nivel_minimo_global, nivel_minimo_ventas_global) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert_config = $conn->prepare($sql_insert_config);
            $stmt_insert_config->bind_param("sssssssii", $nombre_negocio, $direccion_negocio, $telefono_negocio, $email_negocio, $moneda_predeterminada, $formato_fecha, $logo_negocio, $nivel_minimo_global, $nivel_minimo_ventas_global);
            if ($stmt_insert_config->execute()) {
                $mensaje_config = '<div class="alert alert-success mt-3">Configuración general guardada exitosamente.</div>';
            } else {
                $mensaje_config = '<div class="alert alert-danger mt-3">Error al guardar la configuración general.</div>';
            }
            $stmt_insert_config->close();
        }
       
        $result_config = $conn->query($sql_config);
        if ($result_config && $result_config->num_rows > 0) {
            $configuracion = $result_config->fetch_assoc();
        }
    }
  
    if (isset($_POST['agregar_alerta'])) {
        $nuevo_nivel = $_POST['nuevo_nivel'] ?? '';
        if (!empty($nuevo_nivel) && is_numeric($nuevo_nivel) && $nuevo_nivel >= 0) {
            $sql_insert_alerta = "INSERT INTO alertas_stock (nivel_minimo) VALUES (?)";
            $stmt_insert_alerta = $conn->prepare($sql_insert_alerta);
            $stmt_insert_alerta->bind_param("i", $nuevo_nivel);
            if ($stmt_insert_alerta->execute()) {
                $mensaje_alerta = '<div class="alert alert-success mt-3">Nivel de alerta agregado exitosamente.</div>';
            } else {
                $mensaje_alerta = '<div class="alert alert-danger mt-3">Error al agregar el nivel de alerta.</div>';
            }
            $stmt_insert_alerta->close();
            
            $result_alertas = $conn->query($sql_alertas);
            if ($result_alertas && $result_alertas->num_rows > 0) {
                $alertas_stock = [];
                while ($row = $result_alertas->fetch_assoc()) {
                    $alertas_stock[] = $row;
                }
            }
        } else {
            $mensaje_alerta = '<div class="alert alert-warning mt-3">Por favor, ingrese un nivel mínimo válido.</div>';
        }
    }
    
    if (isset($_POST['eliminar_alerta']) && isset($_POST['id_alerta_eliminar'])) {
        $id_eliminar = $_POST['id_alerta_eliminar'];
        $sql_delete_alerta = "DELETE FROM alertas_stock WHERE id = ?";
        $stmt_delete_alerta = $conn->prepare($sql_delete_alerta);
        $stmt_delete_alerta->bind_param("i", $id_eliminar);
        if ($stmt_delete_alerta->execute()) {
            $mensaje_alerta = '<div class="alert alert-success mt-3">Nivel de alerta eliminado exitosamente.</div>';
        } else {
            $mensaje_alerta = '<div class="alert alert-danger mt-3">Error al eliminar el nivel de alerta.</div>';
        }
        $stmt_delete_alerta->close();
        
        $result_alertas = $conn->query($sql_alertas);
        if ($result_alertas && $result_alertas->num_rows > 0) {
            $alertas_stock = [];
            while ($row = $result_alertas->fetch_assoc()) {
                $alertas_stock[] = $row;
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración</title>
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
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .alert {
            margin-top: 20px;
        }
        .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
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

                        <li class="nav-item">
                            <a class="nav-link" href="admin_reportes_vendedores.php">
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
                    <h1 class="h2"><i class="fas fa-cog mr-2"></i> Configuración</h1>
                </div>

                <div class="content-wrapper">
                    <div class="form-container">
                        <h2 class="text-center"><i class="fas fa-wrench mr-2"></i> Configuración General del Sistema</h2>
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="nombre_negocio"><i class="fas fa-store mr-2"></i> Nombre del Negocio:</label>
                                <input type="text" class="form-control" id="nombre_negocio" name="nombre_negocio" value="<?php echo htmlspecialchars($configuracion['nombre_negocio'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="direccion_negocio"><i class="fas fa-map-marker-alt mr-2"></i> Dirección del Negocio:</label>
                                <textarea class="form-control" id="direccion_negocio" name="direccion_negocio"><?php echo htmlspecialchars($configuracion['direccion_negocio'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefono_negocio"><i class="fas fa-phone mr-2"></i> Teléfono:</label>
                                    <input type="tel" class="form-control" id="telefono_negocio" name="telefono_negocio" value="<?php echo htmlspecialchars($configuracion['telefono_negocio'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email_negocio"><i class="fas fa-envelope mr-2"></i> Email:</label>
                                    <input type="email" class="form-control" id="email_negocio" name="email_negocio" value="<?php echo htmlspecialchars($configuracion['email_negocio'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="moneda_predeterminada"><i class="fas fa-coins mr-2"></i> Moneda Predeterminada:</label>
                                    <select class="form-control" id="moneda_predeterminada" name="moneda_predeterminada">
                                        <option value="MXN" <?php echo (isset($configuracion['moneda_predeterminada']) && $configuracion['moneda_predeterminada'] === 'MXN') ? 'selected' : ''; ?>>Peso Mexicano (MXN)</option>
                                        <option value="USD" <?php echo (isset($configuracion['moneda_predeterminada']) && $configuracion['moneda_predeterminada'] === 'USD') ? 'selected' : ''; ?>>Dólar Estadounidense (USD)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="formato_fecha"><i class="fas fa-calendar-alt mr-2"></i> Formato de Fecha:</label>
                                    <select class="form-control" id="formato_fecha" name="formato_fecha">
                                        <option value="d/m/Y" <?php echo (isset($configuracion['formato_fecha']) && $configuracion['formato_fecha'] === 'd/m/Y') ? 'selected' : ''; ?>>DD/MM/AAAA</option>
                                        <option value="Y-m-d" <?php echo (isset($configuracion['formato_fecha']) && $configuracion['formato_fecha'] === 'Y-m-d') ? 'selected' : ''; ?>>AAAA-MM-DD</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nivel_minimo_global"><i class="fas fa-sort-numeric-down mr-2"></i> Nivel Mínimo de Stock Global:</label>
                                <input type="number" class="form-control" id="nivel_minimo_global" name="nivel_minimo_global" value="<?php echo htmlspecialchars($configuracion['nivel_minimo_global'] ?? ''); ?>" min="0">
                                <small class="form-text text-muted">Este valor se utilizará para generar alertas de stock bajo en todos los productos.</small>
                            </div>
							 
							  <div class="form-group">
								<label for="nivel_minimo_ventas_global"><i class="fas fa-chart-line mr-2"></i> Nivel Mínimo de Ventas Mensuales Global:</label>
								<input type="number" class="form-control" id="nivel_minimo_ventas_global" name="nivel_minimo_ventas_global" value="<?php echo htmlspecialchars($configuracion['nivel_minimo_ventas_global'] ?? ''); ?>" min="0">
								<small class="form-text text-muted">Generar alerta si las ventas de un producto en el mes actual son inferiores a este valor.</small>
								</div>

							
                            <div class="form-group">
                                <label for="logo_negocio"><i class="fas fa-image mr-2"></i> Logo del Negocio:</label>
                                <input type="file" class="form-control-file" id="logo_negocio" name="logo_negocio">
                                <small class="form-text text-muted">Seleccione un archivo de imagen para el logo.</small>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" name="guardar_configuracion"><i class="fas fa-save mr-2"></i> Guardar Configuración General</button>
                            <?php echo $mensaje_config ?? ''; ?>
                        </form>
                    </div>

                    <div class="form-container">
                        <h2 class="text-center"><i class="fas fa-bell mr-2"></i> Niveles de Alerta de Stock (Individual - Opcional)</h2>
                        <p class="text-muted">Si deseas configurar niveles de alerta específicos para ciertos productos, podrás hacerlo en la sección de edición de productos. La configuración global anterior se aplicará por defecto a todos los productos que no tengan un nivel individual establecido.</p>
                        <form method="post" action="" class="mb-3">
                            <div class="form-row align-items-center">
                                <div class="col-auto">
                                    <label class="sr-only" for="nuevo_nivel">Nuevo Nivel Mínimo</label>
                                    <input type="number" class="form-control mb-2" id="nuevo_nivel" name="nuevo_nivel" placeholder="Nuevo Nivel Mínimo (Opcional)" min="0">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-success mb-2" name="agregar_alerta"><i class="fas fa-plus mr-2"></i> Agregar Nivel a Lista</button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Esta lista es opcional y podría usarse en futuras funcionalidades.</small>
                            <?php echo $mensaje_alerta ?? ''; ?>
                        </form>

                        <?php if (!empty($alertas_stock)): ?>
                            <h3>Niveles de Alerta en Lista (Opcional)</h3>
                            <ul class="list-group">
                                <?php foreach ($alertas_stock as $alerta): ?>
                                    <li class="list-group-item">
                                        <span>Nivel Mínimo: <?php echo htmlspecialchars($alerta['nivel_minimo']); ?></span>
                                        <form method="post" action="" class="d-inline">
                                            <input type="hidden" name="id_alerta_eliminar" value="<?php echo htmlspecialchars($alerta['id']); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" name="eliminar_alerta"><i class="fas fa-trash-alt"></i> Eliminar</button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-info mt-3">No hay niveles de alerta en la lista (opcional).</div>
                        <?php endif; ?>
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