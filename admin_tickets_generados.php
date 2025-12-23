<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();

if (isset($_GET['venta_id'])) {
    $venta_id = $_GET['venta_id'];

    
    $sql_venta = "SELECT v.id, v.fecha_venta, v.total_venta, v.metodo_pago, u.nombre_usuario
                  FROM ventas v
                  INNER JOIN usuarios u ON v.id_usuario = u.id
                  WHERE v.id = ?";
    $stmt_venta = $conn->prepare($sql_venta);
    $stmt_venta->bind_param("i", $venta_id);
    $stmt_venta->execute();
    $result_venta = $stmt_venta->get_result();

    if ($result_venta->num_rows === 1) {
        $venta = $result_venta->fetch_assoc();
       
        $sql_productos = "SELECT dv.cantidad, dv.precio_unitario, dv.subtotal, p.nombre_producto
                          FROM detalles_venta dv
                          INNER JOIN productos p ON dv.id_producto = p.id
                          WHERE dv.id_venta = ?";
        $stmt_productos = $conn->prepare($sql_productos);
        $stmt_productos->bind_param("i", $venta_id);
        $stmt_productos->execute();
        $result_productos = $stmt_productos->get_result();
        $productos = [];
        if ($result_productos->num_rows > 0) {
            while ($row_producto = $result_productos->fetch_assoc()) {
                $productos[] = $row_producto;
            }
        }
        $stmt_productos->close();

        // --- Generación del Ticket ---
        $nombre_papeleria = "ABAPOST MX"; 
		$direccion_papeleria = "Calle Principal #123, Colonia Centro"; 
        $telefono_papeleria = "555-XXXX-XXXX"; 

        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ticket de Venta #<?php echo htmlspecialchars($venta['id']); ?></title>
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
                .text-right {
                    text-align: right;
                }
                .item {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 5px;
                    border-bottom: 1px dotted #000;
                    padding-bottom: 5px;
                }
                .total {
                    font-weight: bold;
                    margin-top: 10px;
                    padding-top: 10px;
                    border-top: 1px solid #000;
                    display: flex;
                    justify-content: space-between;
                }
                .info {
                    margin-bottom: 10px;
                }
            </style>
        </head>
        <body>
            <div class="ticket">
                <div class="info text-center">
                    <h2><?php echo htmlspecialchars($nombre_papeleria); ?></h2>
                    <p><?php echo htmlspecialchars($direccion_papeleria); ?></p>
                    <p>Teléfono: <?php echo htmlspecialchars($telefono_papeleria); ?></p>
                    <p>Ticket de Venta #<?php echo htmlspecialchars($venta['id']); ?></p>
                    <p>Fecha: <?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($venta['fecha_venta']))); ?></p>
                    <p>Atendido por: <?php echo htmlspecialchars($venta['nombre_usuario']); ?></p>
                    <p>Método de Pago: <?php echo htmlspecialchars(ucfirst($venta['metodo_pago'])); ?></p>
                </div>
                <div>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $producto): ?>
                            <div class="item">
                                <span><?php echo htmlspecialchars($producto['nombre_producto']); ?> x <?php echo htmlspecialchars($producto['cantidad']); ?></span>
                                <span class="text-right">$<?php echo htmlspecialchars(number_format($producto['subtotal'], 2)); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No se encontraron productos en esta venta.</p>
                    <?php endif; ?>
                </div>
                <div class="total">
                    <span>Total:</span>
                    <span class="text-right">$<?php echo htmlspecialchars(number_format($venta['total_venta'], 2)); ?></span>
                </div>
                <div class="text-center" style="margin-top: 20px;">
                    <p>¡Gracias por su compra!</p>
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
        echo "No se encontró la venta.";
    }
    $stmt_venta->close();
} else {
    echo "ID de venta no proporcionado.";
}

$conn->close();
?>