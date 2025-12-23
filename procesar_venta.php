<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'punto_venta') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit();
}

require_once 'config.php';
$conn = conectarDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    $total_venta = $_POST['total_venta'] ?? 0;
    $efectivo_recibido = $_POST['efectivo_recibido'] ?? 0;
    $tarjeta_monto = $_POST['tarjeta_monto'] ?? 0;
    $productos_venta_json = $_POST['productos_venta'] ?? '[]';
    $productos_venta = json_decode($productos_venta_json, true);
    $id_usuario = null;

    if (isset($_SESSION['usuario'])) {
        $stmt_usuario = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
        $stmt_usuario->bind_param("s", $_SESSION['usuario']);
        $stmt_usuario->execute();
        $result_usuario = $stmt_usuario->get_result();
        if ($result_usuario->num_rows === 1) {
            $usuario_data = $result_usuario->fetch_assoc();
            $id_usuario = $usuario_data['id'];
        }
        $stmt_usuario->close();
    }

    if ($id_usuario && !empty($productos_venta) && $total_venta > 0 && !empty($metodo_pago)) {
        $conn->begin_transaction();
        $venta_exitosa = true;
        $id_venta = null;
        $cambio = 0;

        $stmt_venta = $conn->prepare("INSERT INTO ventas (id_usuario, total_venta, metodo_pago, efectivo_recibido, tarjeta_monto, cambio) VALUES (?, ?, ?, ?, ?, ?)");
        $efectivo_recibido_db = ($metodo_pago === 'efectivo') ? $efectivo_recibido : null;
        $tarjeta_monto_db = ($metodo_pago === 'tarjeta' || $metodo_pago === 'transferencia') ? $total_venta : null;
        $cambio = ($metodo_pago === 'efectivo') ? round($efectivo_recibido - $total_venta, 2) : 0;
        $stmt_venta->bind_param("idsddd", $id_usuario, $total_venta, $metodo_pago, $efectivo_recibido_db, $tarjeta_monto_db, $cambio);

        if ($stmt_venta->execute()) {
            $id_venta = $conn->insert_id;
            $stmt_detalle = $conn->prepare("INSERT INTO detalles_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt_stock = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?");

            foreach ($productos_venta as $item) {
                $id_producto = $item['id'];
                $cantidad = $item['cantidad'];
                $precio_unitario = $item['precio'];
                $subtotal = $cantidad * $precio_unitario;

                $stmt_detalle->bind_param("iiidd", $id_venta, $id_producto, $cantidad, $precio_unitario, $subtotal);
                $stmt_detalle->execute();

                $stmt_stock->bind_param("iii", $cantidad, $id_producto, $cantidad);
                $stmt_stock->execute();
                if ($conn->affected_rows < 1) {
                    $venta_exitosa = false;
                    break;
                }
            }
            $stmt_detalle->close();
            $stmt_stock->close();

            if ($venta_exitosa) {
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Venta registrada exitosamente.', 'id_venta' => $id_venta]);
            } else {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Error al registrar la venta: No hay suficiente stock para algunos productos.']);
            }
            $stmt_venta->close();
        } else {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Error al registrar la venta.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Datos de venta inválidos.']);
    }
} else {
    http_response_code(405); // Método no permitido
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}

$conn->close();
?>