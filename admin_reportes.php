<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();

$reporte_vendedores = [];
$filtro_fecha_inicio = $_POST['filtro_fecha_inicio'] ?? '';
$filtro_fecha_fin = $_POST['filtro_fecha_fin'] ?? '';
$filtro_usuario = $_POST['filtro_usuario'] ?? '';

$usuarios = [];
$sql_usuarios = "SELECT id, nombre_usuario FROM usuarios WHERE rol = 'punto_venta' OR rol = 'administrador' ORDER BY nombre_usuario ASC";
$result_usuarios = $conn->query($sql_usuarios);
if ($result_usuarios && $result_usuarios->num_rows > 0) {
    while ($row = $result_usuarios->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar_reporte_vendedores'])) {
    $where_clause = " WHERE 1=1 ";
    $params = [];
    $types = "";

    if (!empty($filtro_fecha_inicio) && !empty($filtro_fecha_fin)) {
        $where_clause .= " AND v.fecha_venta BETWEEN ? AND ? ";
        $params[] = $filtro_fecha_inicio . " 00:00:00";
        $params[] = $filtro_fecha_fin . " 23:59:59";
        $types .= "ss";
    }

    if (!empty($filtro_usuario)) {
        $where_clause .= " AND v.id_usuario= ? ";
        $params[] = $filtro_usuario;
        $types .= "i";
    }

    $sql_reporte_vendedores = "SELECT v.id AS venta_id, v.fecha_venta, u.nombre_usuario AS vendedor, v.total_venta
                                FROM ventas v
                                LEFT JOIN usuarios u ON v.id_usuario= u.id
                                $where_clause
                                ORDER BY v.fecha_venta DESC";
    $stmt_reporte_vendedores = $conn->prepare($sql_reporte_vendedores);

    if ($stmt_reporte_vendedores) {
        if (!empty($params)) {
            $stmt_reporte_vendedores->bind_param($types, ...$params);
        }
        $stmt_reporte_vendedores->execute();
        $result_reporte_vendedores = $stmt_reporte_vendedores->get_result();
        if ($result_reporte_vendedores && $result_reporte_vendedores->num_rows > 0) {
            while ($row = $result_reporte_vendedores->fetch_assoc()) {
                $reporte_vendedores[] = $row;
            }
        }
        $stmt_reporte_vendedores->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Vendedores </title>
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
        .report-results {
            margin-top: 20px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
        }
        .report-results h3 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
            color: #343a40;
        }
        .print-button {
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <div class="text-center mt-3">
                        <img src="imagenes/logo_abarrotes.png" alt="Logo " class="img-fluid mb-3" style="max-width: 150px;">
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
                    <h1 class="h2"><i class="fas fa-chart-bar mr-2"></i> Reporte De Ventas</h1>
                </div>

                <div class="content-wrapper">
                    <div class="form-container">
                        <h2 class="text-center"><i class="fas fa-filter mr-2"></i> Filtrar Reporte de Vendedores</h2>
                        <form method="post" action="">
                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="filtro_fecha_inicio"><i class="fas fa-calendar-alt mr-2"></i> Fecha Inicio:</label>
                                    <input type="date" class="form-control" id="filtro_fecha_inicio" name="filtro_fecha_inicio" value="<?php echo htmlspecialchars($filtro_fecha_inicio); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="filtro_fecha_fin"><i class="fas fa-calendar-alt mr-2"></i> Fecha Fin:</label>
                                    <input type="date" class="form-control" id="filtro_fecha_fin" name="filtro_fecha_fin" value="<?php echo htmlspecialchars($filtro_fecha_fin); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="filtro_usuario"><i class="fas fa-user-tie mr-2"></i> Vendedor:</label>
                                <select class="form-control" id="filtro_usuario" name="filtro_usuario">
                                    <option value="">-- Seleccionar Vendedor --</option>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?php echo htmlspecialchars($usuario['id']); ?>" <?php echo ($filtro_usuario == $usuario['id'] ? 'selected' : ''); ?>><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" name="generar_reporte_vendedores"><i class="fas fa-chart-bar mr-2"></i> Generar Reporte de Vendedores</button>
                        </form>
                    </div>

                    <?php if (!empty($reporte_vendedores)): ?>
                        <div class="report-results">
                            <h3><i class="fas fa-file-alt mr-2"></i> Resultados del Reporte de Vendedores</h3>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>N° Venta</th>
                                            <th>Fecha Venta</th>
                                            <th>Vendedor</th>
                                            <th>Total Venta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reporte_vendedores as $venta): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($venta['venta_id']); ?></td>
                                                <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($venta['fecha_venta']))); ?></td>
                                                <td><?php echo htmlspecialchars($venta['vendedor'] ?? 'N/A'); ?></td>
                                                <td>$<?php echo htmlspecialchars(number_format($venta['total_venta'], 2)); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button onclick="window.print();" class="btn btn-info print-button"><i class="fas fa-print mr-2"></i> Imprimir Reporte</button>
                        </div>
                    <?php elseif (isset($_POST['generar_reporte_vendedores'])): ?>
                        <div class="alert alert-info mt-3">No se encontraron resultados para los filtros de vendedores seleccionados.</div>
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