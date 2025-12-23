<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'punto_venta' && $_SESSION['rol'] !== 'administrador')) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();

$resultados = [];
$termino_busqueda = '';

if (isset($_GET['buscar'])) {
    $termino_busqueda = trim($_GET['termino']);
    if (!empty($termino_busqueda)) {
        $termino_busqueda_like = "%" . $termino_busqueda . "%";
        $sql_buscar_productos = "SELECT id, nombre_producto, precio_venta, stock, codigo_barras
                                 FROM productos
                                 WHERE nombre_producto LIKE ? OR codigo_barras LIKE ?";
        $stmt = $conn->prepare($sql_buscar_productos);
        $stmt->bind_param("ss", $termino_busqueda_like, $termino_busqueda_like);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $resultados[] = $row;
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Productos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .content-wrapper-consulta {
            padding: 20px;
        }
        .consulta-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .consulta-section h2 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .input-group {
            margin-bottom: 15px;
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
                            <a class="nav-link" href="punto_venta_user.php">
                                <i class="fas fa-cash-register"></i> Home
                            </a>
                        </li>
						
						  <li class="nav-item">
                            <a class="nav-link active" href="historial_ventas.php">
                                <i class="fas fa-history"></i> Historial de Ventas
                            </a>
                        </li>
						
                        <li class="nav-item">
                            <a class="nav-link active" href="consultar_productos.php">
                                <i class="fas fa-search"></i> Consultar Productos
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
                </div>
            </nav>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Consultar Productos</h1>
                </div>

                <div class="content-wrapper-consulta">
                    <div class="consulta-section">
                        <h2>Buscar Productos (Precio y Existencias)</h2>
                        <form method="get">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="termino" name="termino" placeholder="Buscar producto por nombre o código" value="<?php echo htmlspecialchars($termino_busqueda); ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit" name="buscar">Buscar</button>
                                </div>
                            </div>
							
							
							
							
                        </form>
                        <div id="resultados_consulta">
                            <?php if (!empty($termino_busqueda)): ?>
                                <?php if (!empty($resultados)): ?>
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Precio</th>
                                                <th>Stock</th>
                                                <th>Código de Barras</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($resultados as $producto): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                                    <td>$<?php echo htmlspecialchars(number_format($producto['precio_venta'], 2)); ?></td>
                                                    <td><?php echo htmlspecialchars($producto['stock']); ?></td>
                                                    <td><?php echo htmlspecialchars($producto['codigo_barras'] ?: '-'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p class='alert alert-warning'>No se encontraron productos con ese nombre o código.</p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class='alert alert-info'>Ingrese el nombre o código del producto para consultar.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>