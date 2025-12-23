<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'punto_venta') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();


$hoy = date('Y-m-d');
$sql_ventas_hoy = "SELECT COUNT(id) AS total_transacciones, SUM(total_venta) AS total_ventas FROM ventas WHERE DATE(fecha_venta) = ?";
$stmt_ventas_hoy = $conn->prepare($sql_ventas_hoy);
$stmt_ventas_hoy->bind_param("s", $hoy);
$stmt_ventas_hoy->execute();
$result_ventas_hoy = $stmt_ventas_hoy->get_result();
$resumen_hoy = $result_ventas_hoy->fetch_assoc();
$total_transacciones_hoy = $resumen_hoy['total_transacciones'] ?? 0;
$total_ventas_hoy = $resumen_hoy['total_ventas'] ?? 0;
$promedio_venta_hoy = ($total_transacciones_hoy > 0) ? ($total_ventas_hoy / $total_transacciones_hoy) : 0;
$stmt_ventas_hoy->close();


$sql_productos_hoy = "SELECT dv.id_producto, p.nombre_producto, SUM(dv.cantidad) AS cantidad_vendida
FROM detalles_venta dv
JOIN ventas v ON dv.id_venta = v.id
JOIN productos p ON dv.id_producto = p.id
WHERE DATE(v.fecha_venta) = ?
GROUP BY dv.id_producto
ORDER BY cantidad_vendida DESC
LIMIT 5;";
$stmt_productos_hoy = $conn->prepare($sql_productos_hoy);
if ($stmt_productos_hoy === false) {
    die("Error al preparar la consulta de productos más vendidos: " . $conn->error);
}
$stmt_productos_hoy->bind_param("s", $hoy);
$stmt_productos_hoy->execute();

$result_productos_hoy = $stmt_productos_hoy->get_result();
$productos_mas_vendidos_hoy = [];
while ($row = $result_productos_hoy->fetch_assoc()) {
    $productos_mas_vendidos_hoy[] = $row;
}
$stmt_productos_hoy->close();


$conn->close();


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Punto de Venta</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .dashboard-container {
            padding: 20px;
        }
        .dashboard-item {
            background-color: #f8f9fa;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .dashboard-item h3 {
            margin-top: 0;
            color: #007bff;
        }
        .venta-hoy-info {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        .productos-vendidos-lista {
            list-style: none;
            padding-left: 0;
        }
        .productos-vendidos-lista li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .productos-vendidos-lista li:last-child {
            border-bottom: none;
        }
        .botones-acciones {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
            gap: 20px; /* Espacio entre los botones */
        }
        .nueva-venta-btn,
        .buscar-producto-btn {
            background-color: #28a745; /* Verde para Nueva Venta */
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1em;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
            animation: pulse-green 1.5s infinite alternate;
            text-decoration: none; /* Para los enlaces */
        }
        .buscar-producto-btn {
            background-color: #007bff; /* Azul para Buscar Producto */
            animation: pulse-blue 1.5s infinite alternate;
        }
        .nueva-venta-btn:hover,
        .buscar-producto-btn:hover {
            transform: scale(1.05);
        }
        @keyframes pulse-green {
            0% {
                transform: scale(1);
                opacity: 0.9;
            }
            100% {
                transform: scale(1.05);
                opacity: 1;
            }
        }
        @keyframes pulse-blue {
            0% {
                transform: scale(1);
                opacity: 0.9;
            }
            100% {
                transform: scale(1.05);
                opacity: 1;
            }
        }
        .columna-info {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .columna-info > div {
            flex: 1;
        }

        /* Estilos Responsive */
        @media (max-width: 768px) {
            .columna-info {
                flex-direction: column; /* Apilar en pantallas pequeñas */
            }
            .botones-acciones {
                flex-direction: column; /* Apilar botones en pantallas pequeñas */
                gap: 10px;
            }
            .nueva-venta-btn,
            .buscar-producto-btn {
                width: 100%; /* Ocupar todo el ancho en pantallas pequeñas */
            }
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row">
		
		<ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="punto_venta_user.php">
                                <i class="fas fa-cash-register"></i> Home
                            </a>
                        </li>
						
						  <li class="nav-item">
                            <a class="nav-link active" href="historial_ventas.php">
                                <i class="fas fa-history"></i> Historial de Ventas
                            </a>
                        </li>
						                        					
						 <li class="nav-item">
                            <a class="nav-link active" href="cortes_caja.php">
                                <i class="fas fa-cut"></i> Corte de Caja
                            </a>
                        </li>
						
                        <li class="nav-item mt-5">
                            <a class="nav-link btn btn-danger btn-sm" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
		            
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-tachometer-alt mr-2"></i> Panel Punto De Venta</h1>
                </div>

              <div class="dashboard-container"> 
                    
                    <div class="botones-acciones">
                        <a href="punto_venta_dashboard.php" class="nueva-venta-btn"><i class="fas fa-cash-register mr-2"></i> Nueva Venta</a>
                        <a href="cortes_caja.php" class="buscar-producto-btn"><i class="fas fa-search mr-2"></i> Realizar Corte De Caja</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	
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