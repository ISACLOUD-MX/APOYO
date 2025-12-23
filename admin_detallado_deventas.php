<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit();
}

require_once 'config.php';
$conn = conectarDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['venta_id'])) {
    $venta_id = $_POST['venta_id'];

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

        echo json_encode(['success' => true, 'venta' => $venta, 'productos' => $productos, 'usuario_nombre' => $venta['nombre_usuario']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró la venta.']);
    }
    $stmt_venta->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}

$conn->close();
?>