<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}


require_once 'config.php';


$conn = conectarDB();

$alertas = [];


$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Estilos adicionales para las alertas */
        #product-alerts .alert {
            margin-bottom: 10px;
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
                            <a class="nav-link" href="admin_historial_cortes_gb.php">
                                <i class="fas fa-list-alt"></i> Historial de Cortes
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
                            <a class="nav-link" href="admin_unidades.php">
                                <i class="fas fa-tags"></i> Unidades Medidas
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
                        
                    </ul>
                </div>
            </nav>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Panel De Administración</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        </div>
                </div>

                <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    </div>
            </nav>

            
        </div>
    </div>
				
                <div class="content-wrapper">
                    
                    <p class="lead">Control Global Del Sistema, Análisis Detallados.</p>
					
					<li class="nav-item mt-5">
                            <a class="nav-link btn btn-danger btn-sm" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </li>
					
                    </div>
            </main>
        </div>
    </div>
	
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
	

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	
	
    </body>
	
	 
	
</html>