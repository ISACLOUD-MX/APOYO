<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'punto_venta') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();

if (isset($_GET['corte_id'])) {
    $corte_id = $_GET['corte_id'];

    $sql_corte = "SELECT cc.id, cc.fecha_corte, cc.total_sistema, cc.total_efectivo_contado, cc.diferencia, u.nombre_usuario, cc.notas
                  FROM cortes_caja cc
                  INNER JOIN usuarios u ON cc.id_usuario = u.id
                  WHERE cc.id = ?";
    $stmt_corte = $conn->prepare($sql_corte);
    $stmt_corte->bind_param("i", $corte_id);
    $stmt_corte->execute();
    $result_corte = $stmt_corte->get_result();

    if ($result_corte->num_rows === 1) {
        $corte = $result_corte->fetch_assoc();

        // --- Generación del Formulario de Impresión del Corte ---
        $nombre_papeleria = "ABAPOST"; 
        $direccion_papeleria = "Calle Principal #123, Colonia Centro"; 
        $telefono_papeleria = "555-XXXX-XXXX"; 

        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Imprimir Corte de Caja #<?php echo htmlspecialchars($corte['id']); ?></title>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    font-size: 12px;
                }
                .ticket {
                    width: 280px;
                    max-width: 280px;
                    margin: 0 auto;
                    padding: 15px;
                    border: 1px dashed #000;
                }
                .text-center {
                    text-align: center;
                }
                .info {
                    margin-bottom: 10px;
                }
                .total {
                    font-weight: bold;
                    margin-top: 10px;
                    padding-top: 10px;
                    border-top: 1px solid #000;
                    display: flex;
                    justify-content: space-between;
                }
                .notes {
                    margin-top: 15px;
                    border-top: 1px dotted #000;
                    padding-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class="ticket">
                <div class="info text-center">
                    <h2><?php echo htmlspecialchars($nombre_papeleria); ?></h2>
                    <p><?php echo htmlspecialchars($direccion_papeleria); ?></p>
                    <p>Teléfono: <?php echo htmlspecialchars($telefono_papeleria); ?></p>
                    <h2>Corte de Caja</h2>
                    <p>ID Corte: #<?php echo htmlspecialchars($corte['id']); ?></p>
                    <p>Fecha y Hora: <?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($corte['fecha_corte']))); ?></p>
                    <p>Usuario: <?php echo htmlspecialchars($corte['nombre_usuario']); ?></p>
                </div>
                <div>
                    <div class="total">
                        <span>Total Sistema:</span>
                        <span class="text-right">$<?php echo htmlspecialchars(number_format($corte['total_sistema'], 2)); ?></span>
                    </div>
                    <div class="total">
                        <span>Total Contado:</span>
                        <span class="text-right">$<?php echo htmlspecialchars(number_format($corte['total_efectivo_contado'], 2)); ?></span>
                    </div>
                    <div class="total">
                        <span>Diferencia:</span>
                        <span class="text-right <?php echo ($corte['diferencia'] != 0) ? 'text-danger' : 'text-success'; ?>">
                            $<?php echo htmlspecialchars(number_format($corte['diferencia'], 2)); ?>
                        </span>
                    </div>
                </div>
                <?php if (!empty($corte['notas'])): ?>
                    <div class="notes">
                        <strong>Notas:</strong>
                        <p><?php echo nl2br(htmlspecialchars($corte['notas'])); ?></p>
                    </div>
                <?php endif; ?>
                <div class="text-center" style="margin-top: 20px;">
                    <p>Fin del Corte de Caja</p>
                </div>
            </div>
            <script>
                window.onload = function() {
                    window.print();                    
                };
            </script>
        </body>
        </html>
        <?php
    } else {
        echo "No se encontró el corte de caja.";
    }
    $stmt_corte->close();
} else {
    echo "ID de corte no proporcionado.";
}

$conn->close();
?>