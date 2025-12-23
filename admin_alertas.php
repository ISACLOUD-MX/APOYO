<?php

//CloudMR 2025

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();

$alertas = [];

$sql_config_global = "SELECT nivel_minimo_global FROM configuracion LIMIT 1";
$result_config_global = $conn->query($sql_config_global);
$nivel_minimo_global = null;
if ($result_config_global && $result_config_global->num_rows > 0) {
    $row_config_global = $result_config_global->fetch_assoc();
    $nivel_minimo_global = $row_config_global['nivel_minimo_global'];
}

if ($nivel_minimo_global !== null) {
    $sql_stock_bajo = "SELECT nombre_producto, stock
                      FROM productos
                      WHERE stock <= ?";
    $stmt_stock_bajo = $conn->prepare($sql_stock_bajo);
    $stmt_stock_bajo->bind_param("i", $nivel_minimo_global);
    $stmt_stock_bajo->execute();
    $result_stock_bajo = $stmt_stock_bajo->get_result();

    if ($result_stock_bajo && $result_stock_bajo->num_rows > 0) {
        while ($row = $result_stock_bajo->fetch_assoc()) {
            $alertas[] = [
                'tipo' => 'stock_bajo',
                'producto' => $row['nombre_producto'],
                'mensaje' => 'Stock bajo: solo quedan ' . $row['stock'] . ' unidades. Nivel mínimo global: ' . $nivel_minimo_global . ' unidades.'
            ];
        }
    }
    $stmt_stock_bajo->close();
}

$conn->close();
?>

<div class="content-wrapper">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-bell mr-2"></i> Alertas del Sistema</h1>
    </div>

    <?php if (!empty($alertas)): ?>
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle mr-2"></i> ¡Atención! Se encontraron las siguientes alertas:</h4>
            <hr>
            <ul class="mb-0">
                <?php foreach ($alertas as $alerta): ?>
                    <li><strong><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $alerta['tipo']))); ?>:</strong> <?php echo htmlspecialchars($alerta['producto']); ?> - <?php echo htmlspecialchars($alerta['mensaje']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle mr-2"></i> No hay alertas del sistema en este momento. ¡Todo está en orden!
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