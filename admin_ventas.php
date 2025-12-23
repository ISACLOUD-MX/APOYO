<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();


$sql_productos = "SELECT id, nombre_producto, precio_venta, stock, codigo_barras FROM productos WHERE stock > 0";
$result_productos = $conn->query($sql_productos);
$productos_array = [];
if ($result_productos->num_rows > 0) {
    while ($row = $result_productos->fetch_assoc()) {
        $productos_array[] = $row;
    }
}
$productos_json = json_encode($productos_array);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Estilos adicionales para el punto de venta */
        .content-wrapper-pv {
            padding: 20px;
        }
        .venta-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .venta-section h2 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        #carrito-venta {
            margin-top: 15px;
        }
        #carrito-venta .producto-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        #carrito-venta .producto-item .nombre {
            flex-grow: 1;
        }
        #carrito-venta .producto-item .cantidad-input {
            width: 80px;
            margin-left: 10px;
            margin-right: 10px;
        }
        #carrito-venta .producto-item .precio {
            width: 80px;
            text-align: right;
            margin-right: 10px;
        }
        #carrito-venta .producto-item .subtotal {
            width: 100px;
            text-align: right;
            font-weight: bold;
        }
        #carrito-venta .producto-item .eliminar-producto {
            background: none;
            border: none;
            color: red;
            cursor: pointer;
            margin-left: 10px;
        }
        #total-venta {
            font-size: 1.5em;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }
        #metodo-pago-group {
            margin-top: 20px;
        }
        .mensaje-venta {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
            text-align: center;
        }
        .mensaje-venta.exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .mensaje-venta.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .buscar-producto-container {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .buscar-producto-container .form-control {
            flex-grow: 1;
            margin-right: 10px;
        }
        .lista-productos-busqueda {
            position: absolute;
            z-index: 1000;
            width: 100%;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            display: none; /* Oculto inicialmente */
        }
        .lista-productos-busqueda a {
            display: block;
            padding: 8px;
            text-decoration: none;
            color: #333;
            border-bottom: 1px solid #eee;
        }
        .lista-productos-busqueda a:hover {
            background-color: #eee;
        }
        .form-pago {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .form-pago .form-group {
            margin-bottom: 10px;
        }
        #cambio-cliente {
            font-size: 1.2em;
            margin-top: 10px;
            text-align: right;
            font-weight: bold;
        }
        #registrar_venta_btn:disabled {
            cursor: not-allowed;
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <div class="text-center mt-3">
                        <img src="imagenes/logo_papeleria.png" alt="Logo" class="img-fluid mb-3" style="max-width: 150px;">
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
                    <h1 class="h2">Panel De Administración</h1>
                </div>

                <div class="content-wrapper-pv">
                    <div class="venta-section">
                        <h2>Registrar Venta</h2>
                        <div class="buscar-producto-container">
                            <label for="termino_busqueda"><i class="fas fa-barcode"></i> Buscar Producto (Nombre o Código):</label>
                            <input type="text" class="form-control" id="termino_busqueda" placeholder="Ingrese nombre o código">
                            <button type="button" class="btn btn-outline-primary ml-2" id="btn_agregar_producto">Agregar</button>
                            <div class="lista-productos-busqueda" id="lista-productos-busqueda">
                                </div>
                        </div>

                        <div id="carrito-venta">
                            <ul class="list-unstyled">
                                </ul>
                            <div id="total-venta">Total: $0.00</div>
                        </div>

                        <form id="form-venta">
                            <input type="hidden" name="total_venta" id="total_venta_input" value="0">
                            <input type="hidden" name="productos_venta_json" id="productos_venta_json" value="">

                            <div class="form-group form-pago">
                                <label for="metodo_pago"><i class="fas fa-money-bill-alt"></i> Método de Pago:</label>
                                <select class="form-control" id="metodo_pago" name="metodo_pago" required>
                                    <option value="">Seleccionar</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                                    <option value="transferencia">Transferencia Bancaria</option>
                                </select>
                            </div>

                            <div id="pago-efectivo" style="display: none;">
                                <div class="form-group">
                                    <label for="efectivo_recibido"><i class="fas fa-hand-holding-usd"></i> Efectivo Recibido:</label>
                                    <input type="number" class="form-control" id="efectivo_recibido" name="efectivo_recibido" step="0.01" min="0">
                                </div>
                                <div id="cambio-cliente">Cambio: $0.00</div>
                            </div>

                            <div id="pago-tarjeta" style="display: none;">
                                <div class="form-group">
                                    <label for="tarjeta_monto"><i class="fas fa-credit-card"></i> Monto Cobrado con Tarjeta:</label>
                                    <input type="number" class="form-control" id="tarjeta_monto" name="tarjeta_monto" step="0.01" min="0">
                                </div>
                            </div>

                            <button type="button" class="btn btn-success btn-block mt-3" id="registrar_venta_btn" disabled>
                                <i class="fas fa-check"></i> Registrar Venta
                            </button>
                        </form>

                        <div id="mensaje-venta" class="mt-3">
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
            var productosDisponibles = <?php echo $productos_json; ?>;
            var carrito = {}; 
           
            function actualizarCarritoUI() {
                var carritoVentaDiv = $('#carrito-venta ul');
                carritoVentaDiv.empty();
                var total = 0;

                for (const id in carrito) {
                    const item = carrito[id];
                    const subtotal = item.precio * item.cantidad;
                    total += subtotal;

                    const listItem = $('<li class="producto-item" data-id="' + id + '">');
                    listItem.append('<span class="nombre">' + item.nombre + '</span>');
                    listItem.append('<input type="number" class="form-control cantidad-input" value="' + item.cantidad + '" min="1" max="' + item.stock + '" style="width: 80px;">');
                    listItem.append('<span class="precio">$' + parseFloat(item.precio).toFixed(2) + '</span>');
                    listItem.append('<span class="subtotal">$' + parseFloat(subtotal).toFixed(2) + '</span>');
                    listItem.append('<button class="eliminar-producto"><i class="fas fa-trash"></i></button>');
                    carritoVentaDiv.append(listItem);
                }

                $('#total-venta').text('Total: $' + parseFloat(total).toFixed(2));
                $('#total_venta_input').val(parseFloat(total).toFixed(2));
                $('#productos_venta_json').val(JSON.stringify(carrito));

                
                if (Object.keys(carrito).length > 0) {
                    $('#registrar_venta_btn').prop('disabled', false);
                } else {
                    $('#registrar_venta_btn').prop('disabled', true);
                }
            }
            
            $('#termino_busqueda').on('input', function() {
                const termino = $(this).val().toLowerCase().trim();
                const resultados = productosDisponibles.filter(producto =>
                    producto.nombre_producto.toLowerCase().includes(termino) || (producto.codigo_barras && producto.codigo_barras.toLowerCase().includes(termino))
                );

                const listaResultados = $('#lista-productos-busqueda');
                listaResultados.empty();

                if (termino.length >= 1 && resultados.length > 0) {
                    resultados.forEach(producto => {
                        const link = $('<a href="#" data-id="' + producto.id + '" data-nombre="' + producto.nombre_producto + '" data-precio="' + producto.precio_venta + '" data-stock="' + producto.stock + '">');
                        link.text(producto.nombre_producto + ' ($' + parseFloat(producto.precio_venta).toFixed(2) + ')');
                        listaResultados.append(link);
                    });
                    listaResultados.slideDown('fast');
                } else {
                    listaResultados.slideUp('fast');
                }
            });

            
            $('#lista-productos-busqueda').on('click', 'a', function(e) {
                e.preventDefault();
                agregarProductoAlCarrito($(this).data('id'), $(this).data('nombre'), $(this).data('precio'), $(this).data('stock'));
                $('#termino_busqueda').val('');
                $('#lista-productos-busqueda').slideUp('fast');
            });
           
            $('#btn_agregar_producto').on('click', function() {
                const primerResultado = $('#lista-productos-busqueda a:first-child');
                if (primerResultado.length > 0 && $('#termino_busqueda').val().trim() !== '') {
                    agregarProductoAlCarrito(primerResultado.data('id'), primerResultado.data('nombre'), primerResultado.data('precio'), primerResultado.data('stock'));
                    $('#termino_busqueda').val('');
                    $('#lista-productos-busqueda').slideUp('fast');
                } else if ($('#termino_busqueda').val().trim() === '') {
                    alert('Por favor, ingrese el nombre o código del producto.');
                } else {
                    alert('No se encontraron productos para agregar.');
                }
            });
            
            function agregarProductoAlCarrito(id, nombre, precio, stock) {
                if (carrito[id]) {
                    carrito[id].cantidad++;
                } else {
                    carrito[id] = { nombre: nombre, precio: precio, cantidad: 1, stock: stock, id: id }; 
                }
                actualizarCarritoUI();
            }
            
            $('#carrito-venta').on('change', '.cantidad-input', function() {
                const id = $(this).closest('.producto-item').data('id');
                const cantidad = parseInt($(this).val());
                if (carrito[id]) {
                    if (cantidad >= 1 && cantidad <= carrito[id].stock) {
                        carrito[id].cantidad = cantidad;
                        actualizarCarritoUI();
                    } else {
                        alert('Cantidad inválida o excede el stock disponible (' + carrito[id].stock + ').');
                        $(this).val(carrito[id].cantidad); 
                    }
                }
            });
            
            $('#carrito-venta').on('click', '.eliminar-producto', function() {
                const id = $(this).closest('.producto-item').data('id');
                delete carrito[id];
                actualizarCarritoUI();
            });
            
            $('#metodo_pago').change(function() {
                const metodo = $(this).val();
                $('#pago-efectivo').hide();
                $('#pago-tarjeta').hide();
                if (metodo === 'efectivo') {
                    $('#pago-efectivo').show();
                } else if (metodo === 'tarjeta') {
                    $('#pago-tarjeta').show();
                }
            });
           
            $('#efectivo_recibido').on('input', function() {
                const efectivoRecibido = parseFloat($(this).val()) || 0;
                const totalVenta = parseFloat($('#total_venta_input').val()) || 0;
                const cambio = efectivoRecibido - totalVenta;
                $('#cambio-cliente').text('Cambio: $' + Math.max(0, cambio).toFixed(2));
            });
            
            $('#registrar_venta_btn').click(function() {
                const metodoPago = $('#metodo_pago').val();
                const totalVenta = parseFloat($('#total_venta_input').val());
                const productosVentaArray = Object.values(carrito).map(item => ({
                    id: item.id,
                    cantidad: item.cantidad,
                    precio: item.precio
                }));
                const productosVentaJson = JSON.stringify(productosVentaArray);
                let efectivoRecibido = 0;
                let tarjetaMonto = 0;

                if (metodoPago === 'efectivo') {
                    efectivoRecibido = parseFloat($('#efectivo_recibido').val()) || 0;
                    if (efectivoRecibido < totalVenta) {
                        $('#mensaje-venta').removeClass('alert-success').addClass('alert-danger').text('El efectivo recibido es insuficiente.');
                        return;
                    }
                } else if (metodoPago === 'tarjeta') {
                    tarjetaMonto = parseFloat($('#tarjeta_monto').val()) || totalVenta; 
                } else if (metodoPago === 'transferencia') {
                    
                }

                if (Object.keys(carrito).length === 0 || !metodoPago) {
                    $('#mensaje-venta').removeClass('alert-success').addClass('alert-danger').text('No hay productos en el carrito o no se seleccionó el método de pago.');
                    return;
                }

                $.ajax({
                    url: 'admin_proceso_venta.php', 
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        metodo_pago: metodoPago,
                        total_venta: totalVenta,
                        efectivo_recibido: efectivoRecibido,
                        tarjeta_monto: tarjetaMonto,
                        productos_venta: productosVentaJson
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#mensaje-venta').removeClass('alert-danger').addClass('alert-success').text(response.message);
                            carrito = {}; 
                            actualizarCarritoUI();
                            $('#form-venta')[0].reset(); 
                            $('#pago-efectivo').hide();
                            $('#pago-tarjeta').hide();
                            if (response.id_venta) {
                                
                                alert('Venta registrada con ID: ' + response.id_venta);
                            }
                        } else {
                            $('#mensaje-venta').removeClass('alert-success').addClass('alert-danger').text(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al registrar la venta:', error);
                        $('#mensaje-venta').removeClass('alert-success').addClass('alert-danger').text('Error al registrar la venta. Por favor, inténtelo de nuevo.');
                    }
                });
            });
        });
    </script>
</body>
</html>