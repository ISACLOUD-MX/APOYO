-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-12-2025 a las 20:12:57
-- Versión del servidor: 10.4.6-MariaDB
-- Versión de PHP: 7.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `aba_post1.1.0316`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas_stock`
--

CREATE TABLE `alertas_stock` (
  `id` int(11) NOT NULL,
  `nivel_minimo` int(10) UNSIGNED NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre_categoria`, `descripcion`) VALUES
(1, 'DEMO', 'VARIOS'),
(6, 'Jugos Varios', 'Varios Jugos De Diferentes TamaÃ±os');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre_cliente` varchar(255) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre_cliente`, `direccion`, `telefono`, `email`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Publico General', 'SN', 'SN', 'isacloudmx@cloud.COM', '2025-07-11 00:04:44', '2025-12-23 19:09:21'),
(2, 'Cliente 1', 'Av Sur ', '5555555565', 'isacloudmx@cloud.online', '2025-07-11 00:13:37', '2025-12-23 19:09:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `nombre_negocio` varchar(255) DEFAULT NULL,
  `direccion_negocio` text DEFAULT NULL,
  `telefono_negocio` varchar(20) DEFAULT NULL,
  `email_negocio` varchar(255) DEFAULT NULL,
  `moneda_predeterminada` varchar(10) DEFAULT 'MXN',
  `formato_fecha` varchar(10) DEFAULT 'd/m/Y',
  `nivel_minimo_global` int(10) UNSIGNED DEFAULT NULL,
  `nivel_minimo_ventas_global` int(10) UNSIGNED DEFAULT NULL,
  `logo_negocio` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nombre_negocio`, `direccion_negocio`, `telefono_negocio`, `email_negocio`, `moneda_predeterminada`, `formato_fecha`, `nivel_minimo_global`, `nivel_minimo_ventas_global`, `logo_negocio`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Ferreventas Del sur', 'Chiapas', '5555555555', 'Farmaposmx@cloud.online', 'MXN', 'd/m/Y', 10, 2, 'imagenes/logo_ferreteria.png', '2025-05-22 18:49:50', '2025-07-31 01:11:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cortes_caja`
--

CREATE TABLE `cortes_caja` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_corte` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_sistema` decimal(10,2) NOT NULL,
  `total_efectivo_contado` decimal(10,2) NOT NULL,
  `diferencia` decimal(10,2) NOT NULL,
  `notas` text DEFAULT NULL,
  `detalle_metodos_pago` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `cortes_caja`
--

INSERT INTO `cortes_caja` (`id`, `id_usuario`, `fecha_corte`, `total_sistema`, `total_efectivo_contado`, `diferencia`, `notas`, `detalle_metodos_pago`) VALUES
(5, 4, '2025-07-31 00:31:49', '100.00', '100.00', '0.00', '', '{\"fecha_inicio_turno\":\"2025-07-30 18:25:42\",\"fecha_corte_actual\":\"2025-07-31 02:31:49\",\"total_sistema_global\":100,\"totales_sistema_por_metodo\":{\"efectivo\":\"100.00\"},\"totales_contados_por_metodo\":{\"credito\":0,\"deposito\":0,\"efectivo\":100,\"tarjeta_tc\":0,\"tarjeta_td\":0,\"transferencia\":0},\"diferencias_por_metodo\":{\"credito\":0,\"deposito\":0,\"efectivo\":0,\"tarjeta_tc\":0,\"tarjeta_td\":0,\"transferencia\":0},\"total_diferencia_global\":0}'),
(6, 2, '2025-08-01 06:00:00', '12.00', '12.00', '0.00', '', '{\"fecha_inicio_turno\":\"2025-08-01 10:25:42\",\"fecha_corte_actual\":\"2025-08-01 19:31:49\",\"total_sistema_global\":12,\"totales_sistema_por_metodo\":{\"efectivo\":\"12.00\"},\"totales_contados_por_metodo\":{\"credito\":0,\"deposito\":0,\"efectivo\":12,\"tarjeta_tc\":0,\"tarjeta_td\":0,\"transferencia\":0},\"diferencias_por_metodo\":{\"credito\":0,\"deposito\":0,\"efectivo\":0,\"tarjeta_tc\":0,\"tarjeta_td\":0,\"transferencia\":0},\"total_diferencia_global\":0}'),
(7, 2, '2025-08-03 02:15:41', '24.00', '24.00', '0.00', 'Todo Correcto', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_venta`
--

CREATE TABLE `detalles_venta` (
  `id` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `detalles_venta`
--

INSERT INTO `detalles_venta` (`id`, `id_venta`, `id_producto`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(58, 1, 15, 1, '150.00', '150.00'),
(59, 2, 16, 1, '12.00', '12.00'),
(60, 3, 16, 1, '12.00', '12.00'),
(61, 4, 16, 2, '12.00', '24.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `tipo_movimiento` enum('entrada','salida','ajuste') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_usuario_responsable` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `id_venta` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `monto_pago` decimal(10,2) NOT NULL,
  `referencia_pago` varchar(255) DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre_producto` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_categoria` int(11) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `codigo_barras` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `descripcion2` text DEFAULT NULL,
  `id_medida` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre_producto`, `descripcion`, `id_categoria`, `precio_compra`, `precio_venta`, `stock`, `codigo_barras`, `fecha_creacion`, `fecha_actualizacion`, `descripcion2`, `id_medida`) VALUES
(15, 'Demos Abarrotes', 'Prueba De producto Exitosa.', 1, '100.00', '150.00', 39, '1111', '2025-08-01 16:21:17', '2025-08-01 21:03:57', NULL, 1),
(16, 'Jugo Del Valle Sabor Durazno 413ML', 'Jugo Del Valle Vidrio', 6, '8.00', '12.00', 6, '32239052017', '2025-08-01 21:06:38', '2025-08-03 02:11:14', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre_proveedor` varchar(255) NOT NULL,
  `contacto_nombre` varchar(255) DEFAULT NULL,
  `contacto_telefono` varchar(20) DEFAULT NULL,
  `contacto_email` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `rfc` varchar(20) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidades_medidas`
--

CREATE TABLE `unidades_medidas` (
  `id` int(11) NOT NULL,
  `nombre_unidad` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `unidades_medidas`
--

INSERT INTO `unidades_medidas` (`id`, `nombre_unidad`, `descripcion`) VALUES
(1, 'KG', 'KILOGRAMO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('administrador','punto_venta') NOT NULL,
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `intentos_fallidos` int(11) DEFAULT 0,
  `bloqueo_temporal` timestamp NULL DEFAULT NULL,
  `bloqueo_permanente` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `contrasena`, `rol`, `ultimo_login`, `intentos_fallidos`, `bloqueo_temporal`, `bloqueo_permanente`, `fecha_creacion`) VALUES
(2, 'cajero1', '$2y$10$SVYThjWOCinodroG5NkBe.hXrrXCtq3LwdtI0.Da9l0R68NlHoqXu', 'punto_venta', NULL, 0, NULL, 0, '2025-05-21 15:10:44'),
(4, 'admin2.1', '$2y$10$4h1ovxTadE6eA7UJ133dqeypmzW0bHqxcfL78XCTIbN44O3ZozbEG', 'administrador', NULL, 0, NULL, 0, '2025-05-22 20:31:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_venta` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `efectivo_recibido` decimal(10,2) DEFAULT NULL,
  `tarjeta_monto` decimal(10,2) DEFAULT NULL,
  `cambio` decimal(10,2) DEFAULT NULL,
  `id_cliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `id_usuario`, `fecha_venta`, `total_venta`, `metodo_pago`, `efectivo_recibido`, `tarjeta_monto`, `cambio`, `id_cliente`) VALUES
(1, 4, '2025-08-01 16:22:12', '150.00', 'efectivo', '200.00', NULL, '50.00', 0),
(2, 4, '2025-08-01 21:09:20', '12.00', 'efectivo', '20.00', NULL, '8.00', 0),
(3, 2, '2025-08-01 21:11:15', '12.00', 'tarjeta', NULL, '12.00', '0.00', 0),
(4, 2, '2025-08-03 02:11:14', '24.00', 'efectivo', '200.00', NULL, '176.00', 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alertas_stock`
--
ALTER TABLE `alertas_stock`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cortes_caja`
--
ALTER TABLE `cortes_caja`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indices de la tabla `detalles_venta`
--
ALTER TABLE `detalles_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_venta` (`id_venta`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `id_usuario_responsable` (`id_usuario_responsable`),
  ADD KEY `id_venta` (`id_venta`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_venta` (`id_venta`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_barras` (`codigo_barras`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `id_medida` (`id_medida`) USING BTREE;

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfc` (`rfc`);

--
-- Indices de la tabla `unidades_medidas`
--
ALTER TABLE `unidades_medidas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_unidad` (`nombre_unidad`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fk_cliente_venta` (`id_cliente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alertas_stock`
--
ALTER TABLE `alertas_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cortes_caja`
--
ALTER TABLE `cortes_caja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `detalles_venta`
--
ALTER TABLE `detalles_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `unidades_medidas`
--
ALTER TABLE `unidades_medidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
