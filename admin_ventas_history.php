<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
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


$historial_ventas = [];
if ($id_usuario_sesion) {
    $sql_historial = "SELECT v.id, v.fecha_venta, v.total_venta, v.metodo_pago
                      FROM ventas v
                      WHERE v.id_usuario = ?
                      ORDER BY v.fecha_venta DESC";
    $stmt_historial = $conn->prepare($sql_historial);
    $stmt_historial->bind_param("i", $id_usuario_sesion);
    $stmt_historial->execute();
    $result_historial = $stmt_historial->get_result();
    if ($result_historial->num_rows > 0) {
        while ($row = $result_historial->fetch_assoc()) {
            $historial_ventas[] = $row;
        }
    }
    $stmt_historial->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas </title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .content-wrapper {
            padding: 20px;
        }
        .historial-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .historial-section h2 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .table th, .table td {
            text-align: center;
        }
        .btn-ver-detalle {
            margin-right: 5px;
        }
        .modal-title {
            font-weight: bold;
        }
        #detalle-venta-productos {
            margin-top: 15px;
        }
        #detalle-venta-productos ul {
            list-style: none;
            padding-left: 0;
        }
        #detalle-venta-productos li {
            margin-bottom: 8px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        #detalle-venta-info p {
            margin-bottom: 5px;
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
                    <h1 class="h2">Historial de Ventas</h1>
                </div>

                <div class="content-wrapper">
                    <div class="historial-section">
                        <h2>Historial de Ventas Globales</h2>
                        <?php if (empty($historial_ventas)): ?>
                            <p>No has realizado ninguna venta aún.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>N° Venta</th>
                                            <th>Fecha</th>
                                            <th>Total</th>
                                            <th>Método de Pago</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historial_ventas as $venta): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($venta['id']); ?></td>
                                                <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($venta['fecha_venta']))); ?></td>
                                                <td>$<?php echo htmlspecialchars(number_format($venta['total_venta'], 2)); ?></td>
                                                <td><?php echo htmlspecialchars(ucfirst($venta['metodo_pago'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info btn-ver-detalle" data-toggle="modal" data-target="#modalDetalleVenta" data-venta-id="<?php echo htmlspecialchars($venta['id']); ?>">
                                                        <i class="fas fa-eye"></i> Ver Detalle
                                                    </button>
                                                    <button class="btn btn-sm btn-secondary btn-imprimir-ticket" data-venta-id="<?php echo htmlspecialchars($venta['id']); ?>">
                                                        <i class="fas fa-print"></i> Imprimir Ticket
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modal fade" id="modalDetalleVenta" tabindex="-1" aria-labelledby="modalDetalleVentaLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalDetalleVentaLabel">Detalle de Venta #<span id="detalle-venta-id"></span></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div id="detalle-venta-info">
                                    <p><strong>Fecha:</strong> <span id="detalle-venta-fecha"></span></p>
                                    <p><strong>Total:</strong> $<span id="detalle-venta-total"></span></p>
                                    <p><strong>Método de Pago:</strong> <span id="detalle-venta-metodo-pago"></span></p>
                                    <p><strong>Atendido por:</strong> <span id="detalle-venta-usuario"></span></p>
                                </div>
                               
                                <div id="detalle-venta-productos">
                                    <ul id="lista-productos-vendidos">
                                        </ul>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#modalDetalleVenta').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); 
                var ventaId = button.data('venta-id'); 

                $.ajax({
                    url: 'admin_detallado_deventas.php', 
                    method: 'POST',
                    dataType: 'json',
                    data: { venta_id: ventaId },
                    success: function(response) {
                        if (response.success) {
                            $('#detalle-venta-id').text(response.venta.id);
                            $('#detalle-venta-fecha').text(new Date(response.venta.fecha_venta).toLocaleString());
                            $('#detalle-venta-total').text(parseFloat(response.venta.total_venta).toFixed(2));
                            $('#detalle-venta-metodo-pago').text(response.venta.metodo_pago);
                            $('#detalle-venta-usuario').text(response.usuario_nombre);

                            var productosList = $('#lista-productos-vendidos');
                            productosList.empty();
                            if (response.productos && response.productos.length > 0) {
                                $.each(response.productos, function(i, producto) {
                                   productosList.append('<li>' +
    '<strong>' + htmlspecialchars(producto.nombre_producto) + '</strong> - Cantidad: ' + htmlspecialchars(producto.cantidad) +
    ' - Precio Unitario: $' + parseFloat(producto.precio_unitario).toFixed(2) +
    ' - Subtotal: $' + parseFloat(producto.subtotal).toFixed(2) +
    '</li>');
                                });
                            } else {
                                productosList.append('<li>No se encontraron productos para esta venta.</li>');
                            }
                        } else {
                            alert('Error al cargar el detalle de la venta: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la petición AJAX:', error);
                        alert('Error al cargar el detalle de la venta.');
                    }
                });
            });

            $('.btn-imprimir-ticket').on('click', function() {
                var ventaId = $(this).data('venta-id');
                window.open('admin_tickets_generados.php?venta_id=' + ventaId, '_blank');
            });
        });
    </script>
</body>
</html>