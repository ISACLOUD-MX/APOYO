<?php

//CloudMR 2025

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();

$alertas = [];

$sql_bajas_ventas = "SELECT p.nombre_producto
                     FROM productos p
                     LEFT JOIN detalles_venta dv ON p.id = dv.id_producto
                     WHERE p.stock_actual > 0
                     GROUP BY p.id
                     HAVING SUM(CASE WHEN dv.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN dv.cantidad ELSE 0 END) < 2";

$result_bajas_ventas = $conn->query($sql_bajas_ventas);
if ($result_bajas_ventas && $result_bajas_ventas->num_rows > 0) {
    while ($row = $result_bajas_ventas->fetch_assoc()) {
        $alertas[] = [
            'tipo' => 'ventas_bajas',
            'producto' => $row['nombre_producto'],
            'mensaje' => 'Bajas ventas en el último mes.'
        ];
    }
}

$conn->close();
?>

<div class="content-wrapper">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-chart-line mr-2"></i> Alertas de Bajas Ventas</h1>
    </div>

    <?php if (!empty($alertas)): ?>
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle mr-2"></i> ¡Atención! Se encontraron los siguientes productos con bajas ventas:</h4>
            <hr>
            <ul class="mb-0">
                <?php foreach ($alertas as $alerta): ?>
                    <li><i class="fas fa-box-open mr-2"></i> <?php echo htmlspecialchars($alerta['producto']); ?> - <?php echo htmlspecialchars($alerta['mensaje']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle mr-2"></i> No hay productos con bajas ventas en el último mes.
        </div>
    <?php endif; ?>
</div>

<style>
    .content-wrapper {
        padding: 20px;
    }
    .alert-warning {
        color: #856404;
        background-color: #fff3cd;
        border-color: #ffeeba;
    }
    .alert-warning hr {
        border-top-color: #ffe8a1;
    }
    .alert-warning .alert-link {
        color: #533403;
    }
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
    .alert-success .alert-link {
        color: #0b2e13;
    }
    .alert-heading {
        color: inherit;
    }
</style>

