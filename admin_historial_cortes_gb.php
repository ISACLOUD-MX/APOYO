<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();


$historial_cortes = [];
$sql_historial_cortes = "SELECT cc.id, cc.fecha_corte, cc.total_sistema, cc.total_efectivo_contado, cc.diferencia, u.nombre_usuario
                        FROM cortes_caja cc
                        INNER JOIN usuarios u ON cc.id_usuario = u.id
                        ORDER BY cc.fecha_corte DESC";
$result_historial_cortes = $conn->query($sql_historial_cortes);

if ($result_historial_cortes && $result_historial_cortes->num_rows > 0) {
    while ($row = $result_historial_cortes->fetch_assoc()) {
        $historial_cortes[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Cortes de Caja</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .content-wrapper {
            padding: 20px;
        }
        .historial-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .historial-section h2 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .table th, .table td {
            text-align: center;
        }
        .btn-imprimir-corte {
            margin-right: 5px;
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
                            <a class="nav-link active" href="admon_panel_central.php">
                                <i class="fas fa-tachometer-alt"></i> Panel
                            </a>
                        </li>
						
						 <li class="nav-item">
                            <a class="nav-link active" href="admin_ventas.php">
                                <i class="fas fa-cash-register"></i> Punto de Venta
                            </a>
                        </li>
						
						  <li class="nav-item">
                            <a class="nav-link active" href="admin_ventas_history.php">
                                <i class="fas fa-history"></i> Historial de Ventas
                            </a>
                        </li>
						                       					
						 <li class="nav-item">
                            <a class="nav-link active" href="admon_cortes_cj.php">
                                <i class="fas fa-cut"></i> Corte de Caja
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
                            <a class="nav-link" href="admin_inventario.php">
                                <i class="fas fa-cubes"></i> Inventario
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="admin_reportes.php">
                                <i class="fas fa-chart-bar"></i> Reportes
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
                    <h1 class="h2">Historial de Cortes de Caja</h1>
                </div>

                <div class="content-wrapper">
                    <div class="historial-section">
                        <h2>Historial de Cortes Realizados</h2>
                        <?php if (empty($historial_cortes)): ?>
                            <p>No se han realizado cortes de caja aún.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>N° Corte</th>
                                            <th>Fecha y Hora</th>
                                            <th>Usuario</th>
                                            <th>Total Sistema</th>
                                            <th>Total Contado</th>
                                            <th>Diferencia</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historial_cortes as $corte): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($corte['id']); ?></td>
                                                <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($corte['fecha_corte']))); ?></td>
                                                <td><?php echo htmlspecialchars($corte['nombre_usuario']); ?></td>
                                                <td>$<?php echo htmlspecialchars(number_format($corte['total_sistema'], 2)); ?></td>
                                                <td>$<?php echo htmlspecialchars(number_format($corte['total_efectivo_contado'], 2)); ?></td>
                                                <td class="<?php echo ($corte['diferencia'] != 0) ? 'text-danger' : 'text-success'; ?>">
                                                    $<?php echo htmlspecialchars(number_format($corte['diferencia'], 2)); ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-secondary btn-imprimir-corte" data-corte-id="<?php echo htmlspecialchars($corte['id']); ?>">
                                                        <i class="fas fa-print"></i> Imprimir Corte
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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
            $('.btn-imprimir-corte').on('click', function() {
                var corteId = $(this).data('corte-id');
                window.open('admin_impresion_decortes.php?corte_id=' + corteId, '_blank');
            });
        });
    </script>
</body>
</html>