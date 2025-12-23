<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'punto_venta') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();

$id_usuario_sesion = null;
if (isset($_SESSION['usuario'])) {
    $stmt_usuario = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
    $stmt_usuario->bind_param("s", $_SESSION['usuario']);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();
    if ($result_usuario->num_rows === 1) {
        $usuario_data = $result_usuario->fetch_assoc();
        $id_usuario_sesion = $usuario_data['id'];
    }
    $stmt_usuario->close();
}

$fecha_ultimo_corte = null;
if ($id_usuario_sesion) {
    $sql_ultimo_corte = "SELECT fecha_corte FROM cortes_caja WHERE id_usuario = ? ORDER BY fecha_corte DESC LIMIT 1";
    $stmt_ultimo_corte = $conn->prepare($sql_ultimo_corte);
    $stmt_ultimo_corte->bind_param("i", $id_usuario_sesion);
    $stmt_ultimo_corte->execute();
    $result_ultimo_corte = $stmt_ultimo_corte->get_result();
    if ($result_ultimo_corte->num_rows === 1) {
        $row_ultimo_corte = $result_ultimo_corte->fetch_assoc();
        $fecha_ultimo_corte = $row_ultimo_corte['fecha_corte'];
    }
    $stmt_ultimo_corte->close();
}

$total_sistema = 0;
if ($id_usuario_sesion) {
    $fecha_inicio_turno = $fecha_ultimo_corte ?? date('Y-m-d 00:00:00');
    $sql_total_sistema = "SELECT SUM(total_venta) AS total
                          FROM ventas
                          WHERE id_usuario = ?
                            AND metodo_pago = 'efectivo'
                            AND fecha_venta >= ?";
    $stmt_total_sistema = $conn->prepare($sql_total_sistema);
    $stmt_total_sistema->bind_param("is", $id_usuario_sesion, $fecha_inicio_turno);
    $stmt_total_sistema->execute();
    $result_total_sistema = $stmt_total_sistema->get_result();
    if ($result_total_sistema->num_rows === 1) {
        $row_total_sistema = $result_total_sistema->fetch_assoc();
        $total_sistema = $row_total_sistema['total'] ?? 0;
    }
    $stmt_total_sistema->close();
}

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total_efectivo_contado = $_POST['total_efectivo_contado'] ?? 0;
    $notas = $_POST['notas'] ?? '';
    $diferencia = round($total_efectivo_contado - $total_sistema, 2);

    $stmt_corte = $conn->prepare("INSERT INTO cortes_caja (id_usuario, total_sistema, total_efectivo_contado, diferencia, notas) VALUES (?, ?, ?, ?, ?)");
    $stmt_corte->bind_param("iddss", $id_usuario_sesion, $total_sistema, $total_efectivo_contado, $diferencia, $notas);

    if ($stmt_corte->execute()) {
        $mensaje = "<div class='alert alert-success'>Corte de caja realizado exitosamente. Diferencia: $" . number_format($diferencia, 2) . "</div>";
    } else {
        $mensaje = "<div class='alert alert-danger'>Error al realizar el corte de caja. Por favor, inténtelo de nuevo.</div>";
    }
    $stmt_corte->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corte de Caja</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .content-wrapper {
            padding: 20px;
        }
        .corte-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .corte-section h2 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
        }
        #total_sistema {
            font-size: 1.2em;
            font-weight: bold;
        }
        .alert {
            margin-top: 15px;
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
                            <a class="nav-link" href="punto_venta_dashboard.php">
                                <i class="fas fa-cash-register"></i> Punto de Venta
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="historial_ventas.php">
                                <i class="fas fa-history"></i> Historial de Ventas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="cortes_caja.php">
                                <i class="fas fa-cut"></i> Corte de Caja
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="history_global_cortes.php">
                                <i class="fas fa-list-alt"></i> Historial de Cortes
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
                    <h1 class="h2">Corte de Caja</h1>
                </div>

                <div class="content-wrapper">
                    <div class="corte-section">
                        <h2>Realizar Corte de Caja</h2>
                        <p>Total de ventas en efectivo registradas desde el último corte: <span id="total_sistema">$<?php echo number_format($total_sistema, 2); ?></span></p>
                        <form method="post">
                            <div class="form-group">
                                <label for="total_efectivo_contado"><i class="fas fa-money-bill-wave"></i> Total de efectivo contado:</label>
                                <input type="number" class="form-control" id="total_efectivo_contado" name="total_efectivo_contado" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="notas"><i class="fas fa-sticky-note"></i> Notas (opcional):</label>
                                <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-block"><i class="fas fa-check-double"></i> Realizar Corte de Caja</button>
                        </form>
                        <?php echo $mensaje; ?>
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