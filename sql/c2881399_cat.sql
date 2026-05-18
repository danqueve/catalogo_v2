-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 18-05-2026 a las 20:23:43
-- Versión del servidor: 8.4.7
-- Versión de PHP: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `c2881399_cat`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulos`
--

DROP TABLE IF EXISTS `articulos`;
CREATE TABLE IF NOT EXISTS `articulos` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoria_id` int UNSIGNED NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio_contado` decimal(12,2) DEFAULT NULL,
  `cuotas_sem_cant` smallint UNSIGNED DEFAULT NULL,
  `cuotas_sem_monto` decimal(12,2) DEFAULT NULL,
  `cuotas_mes_cant` smallint UNSIGNED DEFAULT NULL,
  `cuotas_mes_monto` decimal(12,2) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_art_cat` (`categoria_id`),
  KEY `idx_art_creado` (`creado_en` DESC)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `articulos`
--

INSERT INTO `articulos` (`id`, `categoria_id`, `nombre`, `descripcion`, `imagen`, `precio_contado`, `cuotas_sem_cant`, `cuotas_sem_monto`, `cuotas_mes_cant`, `cuotas_mes_monto`, `activo`, `creado_en`) VALUES
(1, 1, 'Combo Lavarropas Drean', 'Secarropas Drean QV 6.5K y Lavarropa Drean', 'c4afe1107c0ffd225e3b08159ec32473.png', 580000.00, 22, 42000.00, 10, 108700.00, 1, '2026-05-18 14:39:01'),
(2, 1, 'Lavarropas Drean 5Kg', NULL, 'd5c5cd138b4d527e82593d9532db89ff.jpg', 240000.00, 12, 35000.00, NULL, NULL, 1, '2026-05-18 15:33:19'),
(3, 1, 'Lavarropas Drean 5KG Semi', NULL, '5fb952a9e6d4ab43898e4294bcd1dda0.jpg', 250000.00, 12, 36500.00, NULL, NULL, 1, '2026-05-18 15:34:00'),
(4, 1, 'Secarropas Drean 5.5KG', NULL, '9230d4fbfeafbc128490d496727c89c7.jpg', 292000.00, 14, 36500.00, NULL, NULL, 1, '2026-05-18 15:34:40'),
(5, 1, 'Secarropas Drean QV 6.5', NULL, 'f705e7d11385a2260d90ef9bd4667c8b.jpg', 312000.00, 14, 39000.00, NULL, NULL, 1, '2026-05-18 15:35:12'),
(6, 1, 'Lavarropa Drean Concep Neo Fuzzy 6.5 Kg', NULL, '494666205939d24118596585f7781563.jpg', 678400.00, 22, 54000.00, NULL, NULL, 1, '2026-05-18 15:35:51'),
(7, 1, 'Eslabon de Lujo 7Kg', NULL, 'd82cde9d603c48b7466d7988b631bf00.jpg', 846400.00, 22, 67500.00, NULL, NULL, 1, '2026-05-18 15:37:41'),
(8, 1, 'Drean Next Inverter 7 Kg', NULL, 'bc84f8c8e31178a09e07313825543cb8.jpg', 888000.00, 24, 65000.00, NULL, NULL, 1, '2026-05-18 15:52:16'),
(9, 1, 'Eslabon de Lujo 9Kg', NULL, 'fe827ab7f6845abc0fc7813c32aaa299.jpg', 1086400.00, 24, 79000.00, NULL, NULL, 1, '2026-05-18 15:52:53'),
(10, 1, 'Drean Next  P Eco 8Kg', NULL, 'e9bf9edc78481b373e2cdcfc6385d431.jpg', NULL, 24, 93000.00, NULL, NULL, 1, '2026-05-18 15:55:41'),
(11, 1, 'Drean 7 Kg Semi mas Secarropas 6.5 KG', NULL, 'b781e220edf415c77ea58207be06a176.jpg', NULL, 24, 54300.00, NULL, NULL, 1, '2026-05-18 15:57:11'),
(12, 3, 'Atma 2.5 Litros', NULL, '476bea90c5fb26375da01e770243ec6f.jpg', 115000.00, 6, 32000.00, NULL, NULL, 1, '2026-05-18 16:01:12'),
(13, 3, 'Peabody Digital 6.87 Litros', NULL, '7d0eb7ca320a64ca962eaa562ce8da2f.jpg', NULL, 8, 32000.00, NULL, NULL, 1, '2026-05-18 16:08:20'),
(14, 3, 'Atma 6.5 Litros', NULL, '3c35b70f5e21af9937f585f0218257e4.jpg', NULL, 12, 33000.00, NULL, NULL, 1, '2026-05-18 16:08:50'),
(15, 3, 'Black and Decker Tactil 4.2 Litros', NULL, '901fabdee9d5b3bf7823ed228bb5444a.jpg', NULL, 12, 35000.00, NULL, NULL, 1, '2026-05-18 16:09:51'),
(16, 3, 'Black and Decker 5.5 Litros', NULL, '5f34d6e6afade595b3878759bdba122d.jpg', NULL, 14, 36000.00, NULL, NULL, 1, '2026-05-18 16:10:44'),
(17, 2, 'Smartv 50\"', NULL, '838d5ae99aec51a504c2a74028cf9f5b.jpg', NULL, 24, 62000.00, 10, 160000.00, 1, '2026-05-18 16:19:49'),
(18, 2, 'Sillon Eco + Smartv 40 + Caloventor', 'Sillon Economico con Camastro mas SmarTv 40\" + Caloventor 2200w Liliana', '12ea6b48b4a7f0d456c8237e8f864b22.jpg', NULL, 24, 60000.00, 10, 160000.00, 1, '2026-05-18 16:30:12'),
(19, 4, 'Cortadoras de Pelo', 'Cortadoras de Pelo Wahl', '9fa310d7fad575196df4dac500b5ea9e.jpg', 80000.00, NULL, NULL, NULL, NULL, 1, '2026-05-18 16:55:04'),
(20, 4, 'Gama', NULL, '877283ca624588cbd59e6adede3dfcd3.jpg', 80000.00, NULL, NULL, NULL, NULL, 1, '2026-05-18 16:55:26'),
(21, 4, 'Remington', NULL, 'f692c4f90a42fc096510aa586502fabb.jpg', 80000.00, NULL, NULL, NULL, NULL, 1, '2026-05-18 16:55:51'),
(22, 2, 'Herramientas', 'Soldador, Rotomartillo y Aspiradora', 'fbcd1d8aafee00a5662ab33119f7274a.jpg', 170000.00, NULL, NULL, NULL, NULL, 1, '2026-05-18 16:56:28'),
(23, 2, 'Compresor y Amoladora', 'Compresor 50 Litros', 'e26cb8bd357d89b00a9188c8f20a448d.jpg', 100000.00, NULL, NULL, NULL, NULL, 1, '2026-05-18 16:57:12'),
(24, 2, 'Talador Inalambrico mas Set de Herramientas', NULL, '24f552b7b81e3cdeabf6b105db97268a.jpg', 144000.00, 7, 33000.00, NULL, NULL, 1, '2026-05-18 16:57:44'),
(25, 2, 'Set Herramientas', NULL, 'c0634ad6734740eae21182f3e2b28a92.jpg', 144000.00, 7, 33000.00, NULL, NULL, 1, '2026-05-18 16:58:19'),
(26, 2, 'Taladors', NULL, '923d44b0ba38bfc9948fd9418e4a8c7e.jpg', 72800.00, NULL, NULL, NULL, NULL, 1, '2026-05-18 16:58:47'),
(27, 2, 'Taladro Inalambrico y Minitorno', NULL, '1f935e6518cfaf841bbb922aacf68cab.jpg', 116500.00, NULL, NULL, NULL, NULL, 1, '2026-05-18 16:59:21'),
(28, 2, 'Smart 65 y 75', NULL, '339d03874816619f81698a44547c0793.jpg', NULL, 24, 120000.00, 12, 383000.00, 1, '2026-05-18 17:00:08'),
(29, 2, 'SmarTV 32\"', NULL, 'd7058207f8e19d269184a42131f135e3.jpg', NULL, 20, 35000.00, 8, 93000.00, 1, '2026-05-18 17:00:41'),
(30, 2, 'Smartv 40', NULL, 'ee728bf324ee3c96787dc97d822cf67c.jpg', NULL, 22, 42000.00, 10, 100000.00, 1, '2026-05-18 17:01:07'),
(31, 2, 'Smartv + Rack y Panel Milan', NULL, 'a02f2241c811db818f3a5eca4808cff7.jpg', NULL, 24, 57000.00, 10, 150000.00, 1, '2026-05-18 17:01:56'),
(32, 2, 'Sillon + Freidora', NULL, '64b8cd682ef8187cbdfe7373e4873a39.jpg', NULL, 10, 65000.00, 10, 100000.00, 1, '2026-05-18 17:03:10'),
(33, 2, 'Sillon Eco + Freidora Digital', NULL, '47c91f039af732f924bb9597f1097898.jpg', NULL, 20, 35000.00, 8, 93000.00, 1, '2026-05-18 17:03:47'),
(34, 2, 'SmarTV 43\" Roku TV', 'Smartv 43 son sistema operativo ROKU (no android, no googletv)', '581e2082bb5b4d3c29e73523156ab6dc.jpg', NULL, 20, 45000.00, 10, 90000.00, 1, '2026-05-18 17:04:50'),
(35, 4, 'Reloj', NULL, '57553aaf6149ed29d3c08fef4692d85c.jpg', 96000.00, 8, 33500.00, NULL, NULL, 1, '2026-05-18 17:13:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_cat_creado` (`creado_en` DESC)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `slug`, `imagen`, `activo`, `creado_en`) VALUES
(1, 'Lavarropas y Secarropas', 'lavarropas-y-secarropas', 'fb108840b1ac48653e6cbf2fce4b3e35.jpg', 1, '2026-05-18 14:36:42'),
(2, 'Promos Del Mes', 'promos-del-mes', '9ef7e78e050829ca736efbe24162bea5.jpg', 1, '2026-05-18 15:15:00'),
(3, 'Freidoras', 'freidoras', '73263f3fbe64a984badd5c594372810b.jpg', 1, '2026-05-18 15:59:27'),
(4, 'Dia del Padre', 'dia-del-padre', '8fa6d4397a5ea685147d1564e2c23ff5.jpg', 1, '2026-05-18 17:08:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `password_hash`, `nombre`, `creado_en`) VALUES
(1, 'admin', '$2y$10$FWC7GDIwvy24H4Flrv8UxOQDlSHI9eAKvoZK.x9WcV/Lm14q0vL4e', 'Administrador', '2026-05-18 13:44:23');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD CONSTRAINT `fk_art_cat` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
