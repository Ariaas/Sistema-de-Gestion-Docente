-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-09-2025 a las 01:03:48
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db_bitacora`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permisos`
--

CREATE TABLE `rol_permisos` (
  `rol_id` int(10) NOT NULL,
  `per_id` int(10) NOT NULL,
  `per_accion` enum('registrar','modificar','eliminar') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol_permisos`
--

INSERT INTO `rol_permisos` (`rol_id`, `per_id`, `per_accion`) VALUES
(1, 5, 'registrar'),
(1, 13, 'registrar'),
(1, 13, 'modificar'),
(1, 13, 'eliminar'),
(1, 17, 'registrar'),
(1, 17, 'modificar'),
(1, 17, 'eliminar'),
(1, 11, 'registrar'),
(1, 11, 'modificar'),
(1, 11, 'eliminar'),
(1, 14, 'registrar'),
(1, 14, 'modificar'),
(1, 14, 'eliminar'),
(1, 12, 'registrar'),
(1, 12, 'modificar'),
(1, 12, 'eliminar'),
(1, 10, 'registrar'),
(1, 10, 'modificar'),
(1, 10, 'eliminar'),
(1, 3, 'registrar'),
(1, 3, 'modificar'),
(1, 3, 'eliminar'),
(1, 8, 'registrar'),
(1, 8, 'modificar'),
(1, 8, 'eliminar'),
(1, 7, 'registrar'),
(1, 7, 'modificar'),
(1, 7, 'eliminar'),
(1, 16, 'registrar'),
(1, 16, 'modificar'),
(1, 16, 'eliminar'),
(1, 9, 'registrar'),
(1, 9, 'modificar'),
(1, 9, 'eliminar'),
(1, 1, 'registrar'),
(1, 1, 'modificar'),
(1, 1, 'eliminar'),
(1, 15, 'registrar'),
(1, 15, 'modificar'),
(1, 15, 'eliminar'),
(1, 18, 'registrar'),
(1, 18, 'modificar'),
(1, 18, 'eliminar'),
(1, 2, 'registrar'),
(1, 2, 'modificar'),
(1, 2, 'eliminar'),
(1, 4, 'registrar'),
(1, 4, 'modificar'),
(1, 4, 'eliminar'),
(1, 5, 'registrar'),
(1, 13, 'registrar'),
(1, 13, 'modificar'),
(1, 13, 'eliminar'),
(1, 17, 'registrar'),
(1, 17, 'modificar'),
(1, 17, 'eliminar'),
(1, 11, 'registrar'),
(1, 11, 'modificar'),
(1, 11, 'eliminar'),
(1, 14, 'registrar'),
(1, 14, 'modificar'),
(1, 14, 'eliminar'),
(1, 12, 'registrar'),
(1, 12, 'modificar'),
(1, 12, 'eliminar'),
(1, 10, 'registrar'),
(1, 10, 'modificar'),
(1, 10, 'eliminar'),
(1, 3, 'registrar'),
(1, 3, 'modificar'),
(1, 3, 'eliminar'),
(1, 8, 'registrar'),
(1, 8, 'modificar'),
(1, 8, 'eliminar'),
(1, 7, 'registrar'),
(1, 7, 'modificar'),
(1, 7, 'eliminar'),
(1, 16, 'registrar'),
(1, 16, 'modificar'),
(1, 16, 'eliminar'),
(1, 9, 'registrar'),
(1, 9, 'modificar'),
(1, 9, 'eliminar'),
(1, 1, 'registrar'),
(1, 1, 'modificar'),
(1, 1, 'eliminar'),
(1, 15, 'registrar'),
(1, 15, 'modificar'),
(1, 15, 'eliminar'),
(1, 18, 'registrar'),
(1, 18, 'modificar'),
(1, 18, 'eliminar'),
(1, 2, 'registrar'),
(1, 2, 'modificar'),
(1, 2, 'eliminar'),
(1, 4, 'registrar'),
(1, 4, 'modificar'),
(1, 4, 'eliminar'),
(1, 5, 'registrar'),
(1, 13, 'registrar'),
(1, 13, 'modificar'),
(1, 13, 'eliminar'),
(1, 17, 'registrar'),
(1, 17, 'modificar'),
(1, 17, 'eliminar'),
(1, 11, 'registrar'),
(1, 11, 'modificar'),
(1, 11, 'eliminar'),
(1, 14, 'registrar'),
(1, 14, 'modificar'),
(1, 14, 'eliminar'),
(1, 12, 'registrar'),
(1, 12, 'modificar'),
(1, 12, 'eliminar'),
(1, 10, 'registrar'),
(1, 10, 'modificar'),
(1, 10, 'eliminar'),
(1, 3, 'registrar'),
(1, 3, 'modificar'),
(1, 3, 'eliminar'),
(1, 8, 'registrar'),
(1, 8, 'modificar'),
(1, 8, 'eliminar'),
(1, 7, 'registrar'),
(1, 7, 'modificar'),
(1, 7, 'eliminar'),
(1, 16, 'registrar'),
(1, 16, 'modificar'),
(1, 16, 'eliminar'),
(1, 9, 'registrar'),
(1, 9, 'modificar'),
(1, 9, 'eliminar'),
(1, 1, 'registrar'),
(1, 1, 'modificar'),
(1, 1, 'eliminar'),
(1, 15, 'registrar'),
(1, 15, 'modificar'),
(1, 15, 'eliminar'),
(1, 18, 'registrar'),
(1, 18, 'modificar'),
(1, 18, 'eliminar'),
(1, 2, 'registrar'),
(1, 2, 'modificar'),
(1, 2, 'eliminar'),
(1, 4, 'registrar'),
(1, 4, 'modificar'),
(1, 4, 'eliminar'),
(1, 5, 'registrar'),
(1, 13, 'registrar'),
(1, 13, 'modificar'),
(1, 13, 'eliminar'),
(1, 17, 'registrar'),
(1, 17, 'modificar'),
(1, 17, 'eliminar'),
(1, 11, 'registrar'),
(1, 11, 'modificar'),
(1, 11, 'eliminar'),
(1, 14, 'registrar'),
(1, 14, 'modificar'),
(1, 14, 'eliminar'),
(1, 12, 'registrar'),
(1, 12, 'modificar'),
(1, 12, 'eliminar'),
(1, 10, 'registrar'),
(1, 10, 'modificar'),
(1, 10, 'eliminar'),
(1, 3, 'registrar'),
(1, 3, 'modificar'),
(1, 3, 'eliminar'),
(1, 8, 'registrar'),
(1, 8, 'modificar'),
(1, 8, 'eliminar'),
(1, 7, 'registrar'),
(1, 7, 'modificar'),
(1, 7, 'eliminar'),
(1, 16, 'registrar'),
(1, 16, 'modificar'),
(1, 16, 'eliminar'),
(1, 9, 'registrar'),
(1, 9, 'modificar'),
(1, 9, 'eliminar'),
(1, 1, 'registrar'),
(1, 1, 'modificar'),
(1, 1, 'eliminar'),
(1, 15, 'registrar'),
(1, 15, 'modificar'),
(1, 15, 'eliminar'),
(1, 18, 'registrar'),
(1, 18, 'modificar'),
(1, 18, 'eliminar'),
(1, 2, 'registrar'),
(1, 2, 'modificar'),
(1, 2, 'eliminar'),
(1, 4, 'registrar'),
(1, 4, 'modificar'),
(1, 4, 'eliminar'),
(1, 5, 'registrar'),
(1, 13, 'registrar'),
(1, 13, 'modificar'),
(1, 13, 'eliminar'),
(1, 17, 'registrar'),
(1, 17, 'modificar'),
(1, 17, 'eliminar'),
(1, 11, 'registrar'),
(1, 11, 'modificar'),
(1, 11, 'eliminar'),
(1, 14, 'registrar'),
(1, 14, 'modificar'),
(1, 14, 'eliminar'),
(1, 12, 'registrar'),
(1, 12, 'modificar'),
(1, 12, 'eliminar'),
(1, 10, 'registrar'),
(1, 10, 'modificar'),
(1, 10, 'eliminar'),
(1, 3, 'registrar'),
(1, 3, 'modificar'),
(1, 3, 'eliminar'),
(1, 8, 'registrar'),
(1, 8, 'modificar'),
(1, 8, 'eliminar'),
(1, 7, 'registrar'),
(1, 7, 'modificar'),
(1, 7, 'eliminar'),
(1, 16, 'registrar'),
(1, 16, 'modificar'),
(1, 16, 'eliminar'),
(1, 9, 'registrar'),
(1, 9, 'modificar'),
(1, 9, 'eliminar'),
(1, 1, 'registrar'),
(1, 1, 'modificar'),
(1, 1, 'eliminar'),
(1, 15, 'registrar'),
(1, 15, 'modificar'),
(1, 15, 'eliminar'),
(1, 18, 'registrar'),
(1, 18, 'modificar'),
(1, 18, 'eliminar'),
(1, 2, 'registrar'),
(1, 2, 'modificar'),
(1, 2, 'eliminar'),
(1, 4, 'registrar'),
(1, 4, 'modificar'),
(1, 4, 'eliminar'),
(1, 5, 'registrar'),
(1, 13, 'registrar'),
(1, 13, 'modificar'),
(1, 13, 'eliminar'),
(1, 17, 'registrar'),
(1, 17, 'modificar'),
(1, 17, 'eliminar'),
(1, 11, 'registrar'),
(1, 11, 'modificar'),
(1, 11, 'eliminar'),
(1, 14, 'registrar'),
(1, 14, 'modificar'),
(1, 14, 'eliminar'),
(1, 12, 'registrar'),
(1, 12, 'modificar'),
(1, 12, 'eliminar'),
(1, 10, 'registrar'),
(1, 10, 'modificar'),
(1, 10, 'eliminar'),
(1, 3, 'registrar'),
(1, 3, 'modificar'),
(1, 3, 'eliminar'),
(1, 8, 'registrar'),
(1, 8, 'modificar'),
(1, 8, 'eliminar'),
(1, 7, 'registrar'),
(1, 7, 'modificar'),
(1, 7, 'eliminar'),
(1, 16, 'registrar'),
(1, 16, 'modificar'),
(1, 16, 'eliminar'),
(1, 9, 'registrar'),
(1, 9, 'modificar'),
(1, 9, 'eliminar'),
(1, 1, 'registrar'),
(1, 1, 'modificar'),
(1, 1, 'eliminar'),
(1, 15, 'registrar'),
(1, 15, 'modificar'),
(1, 15, 'eliminar'),
(1, 18, 'registrar'),
(1, 18, 'modificar'),
(1, 18, 'eliminar'),
(1, 2, 'registrar'),
(1, 2, 'modificar'),
(1, 2, 'eliminar'),
(1, 4, 'registrar'),
(1, 4, 'modificar'),
(1, 4, 'eliminar'),
(1, 5, 'registrar'),
(1, 13, 'registrar'),
(1, 13, 'modificar'),
(1, 13, 'eliminar'),
(1, 17, 'registrar'),
(1, 17, 'modificar'),
(1, 17, 'eliminar'),
(1, 11, 'registrar'),
(1, 11, 'modificar'),
(1, 11, 'eliminar'),
(1, 14, 'registrar'),
(1, 14, 'modificar'),
(1, 14, 'eliminar'),
(1, 12, 'registrar'),
(1, 12, 'modificar'),
(1, 12, 'eliminar'),
(1, 10, 'registrar'),
(1, 10, 'modificar'),
(1, 10, 'eliminar'),
(1, 3, 'registrar'),
(1, 3, 'modificar'),
(1, 3, 'eliminar'),
(1, 8, 'registrar'),
(1, 8, 'modificar'),
(1, 8, 'eliminar'),
(1, 7, 'registrar'),
(1, 7, 'modificar'),
(1, 7, 'eliminar'),
(1, 16, 'registrar'),
(1, 16, 'modificar'),
(1, 16, 'eliminar'),
(1, 9, 'registrar'),
(1, 9, 'modificar'),
(1, 9, 'eliminar'),
(1, 1, 'registrar'),
(1, 1, 'modificar'),
(1, 1, 'eliminar'),
(1, 15, 'registrar'),
(1, 15, 'modificar'),
(1, 15, 'eliminar'),
(1, 18, 'registrar'),
(1, 18, 'modificar'),
(1, 18, 'eliminar'),
(1, 2, 'registrar'),
(1, 2, 'modificar'),
(1, 2, 'eliminar'),
(1, 4, 'registrar'),
(1, 4, 'modificar'),
(1, 4, 'eliminar'),
(1, 5, 'registrar'),
(1, 13, 'registrar'),
(1, 13, 'modificar'),
(1, 13, 'eliminar'),
(1, 17, 'registrar'),
(1, 17, 'modificar'),
(1, 17, 'eliminar'),
(1, 11, 'registrar'),
(1, 11, 'modificar'),
(1, 11, 'eliminar'),
(1, 14, 'registrar'),
(1, 14, 'modificar'),
(1, 14, 'eliminar'),
(1, 12, 'registrar'),
(1, 12, 'modificar'),
(1, 12, 'eliminar'),
(1, 10, 'registrar'),
(1, 10, 'modificar'),
(1, 10, 'eliminar'),
(1, 3, 'registrar'),
(1, 3, 'modificar'),
(1, 3, 'eliminar'),
(1, 8, 'registrar'),
(1, 8, 'modificar'),
(1, 8, 'eliminar'),
(1, 7, 'registrar'),
(1, 7, 'modificar'),
(1, 7, 'eliminar'),
(1, 16, 'registrar'),
(1, 16, 'modificar'),
(1, 16, 'eliminar'),
(1, 9, 'registrar'),
(1, 9, 'modificar'),
(1, 9, 'eliminar'),
(1, 1, 'registrar'),
(1, 1, 'modificar'),
(1, 1, 'eliminar'),
(1, 15, 'registrar'),
(1, 15, 'modificar'),
(1, 15, 'eliminar'),
(1, 18, 'registrar'),
(1, 18, 'modificar'),
(1, 18, 'eliminar'),
(1, 2, 'registrar'),
(1, 2, 'modificar'),
(1, 2, 'eliminar'),
(1, 4, 'registrar'),
(1, 4, 'modificar'),
(1, 4, 'eliminar'),
(1, 5, 'registrar'),
(1, 13, 'registrar'),
(1, 13, 'modificar'),
(1, 13, 'eliminar'),
(1, 17, 'registrar'),
(1, 17, 'modificar'),
(1, 17, 'eliminar'),
(1, 11, 'registrar'),
(1, 11, 'modificar'),
(1, 11, 'eliminar'),
(1, 14, 'registrar'),
(1, 14, 'modificar'),
(1, 14, 'eliminar'),
(1, 12, 'registrar'),
(1, 12, 'modificar'),
(1, 12, 'eliminar'),
(1, 10, 'registrar'),
(1, 10, 'modificar'),
(1, 10, 'eliminar'),
(1, 3, 'registrar'),
(1, 3, 'modificar'),
(1, 3, 'eliminar'),
(1, 8, 'registrar'),
(1, 8, 'modificar'),
(1, 8, 'eliminar'),
(1, 7, 'registrar'),
(1, 7, 'modificar'),
(1, 7, 'eliminar'),
(1, 16, 'registrar'),
(1, 16, 'modificar'),
(1, 16, 'eliminar'),
(1, 9, 'registrar'),
(1, 9, 'modificar'),
(1, 9, 'eliminar'),
(1, 1, 'registrar'),
(1, 1, 'modificar'),
(1, 1, 'eliminar'),
(1, 15, 'registrar'),
(1, 15, 'modificar'),
(1, 15, 'eliminar'),
(1, 18, 'registrar'),
(1, 18, 'modificar'),
(1, 18, 'eliminar'),
(1, 2, 'registrar'),
(1, 2, 'modificar'),
(1, 2, 'eliminar'),
(1, 4, 'registrar'),
(1, 4, 'modificar'),
(1, 4, 'eliminar'),
(1, 5, 'registrar'),
(1, 13, 'registrar'),
(1, 13, 'modificar'),
(1, 13, 'eliminar'),
(1, 17, 'registrar'),
(1, 17, 'modificar'),
(1, 17, 'eliminar'),
(1, 11, 'registrar'),
(1, 11, 'modificar'),
(1, 11, 'eliminar'),
(1, 14, 'registrar'),
(1, 14, 'modificar'),
(1, 14, 'eliminar'),
(1, 12, 'registrar'),
(1, 12, 'modificar'),
(1, 12, 'eliminar'),
(1, 10, 'registrar'),
(1, 10, 'modificar'),
(1, 10, 'eliminar'),
(1, 3, 'registrar'),
(1, 3, 'modificar'),
(1, 3, 'eliminar'),
(1, 8, 'registrar'),
(1, 8, 'modificar'),
(1, 8, 'eliminar'),
(1, 7, 'registrar'),
(1, 7, 'modificar'),
(1, 7, 'eliminar'),
(1, 16, 'registrar'),
(1, 16, 'modificar'),
(1, 16, 'eliminar'),
(1, 9, 'registrar'),
(1, 9, 'modificar'),
(1, 9, 'eliminar'),
(1, 1, 'registrar'),
(1, 1, 'modificar'),
(1, 1, 'eliminar'),
(1, 15, 'registrar'),
(1, 15, 'modificar'),
(1, 15, 'eliminar'),
(1, 18, 'registrar'),
(1, 18, 'modificar'),
(1, 18, 'eliminar'),
(1, 2, 'registrar'),
(1, 2, 'modificar'),
(1, 2, 'eliminar'),
(1, 4, 'registrar'),
(1, 4, 'modificar'),
(1, 4, 'eliminar');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_bitacora`
--

CREATE TABLE `tbl_bitacora` (
  `usu_id` int(10) NOT NULL,
  `bit_modulo` varchar(30) NOT NULL,
  `bit_accion` varchar(30) NOT NULL,
  `bit_fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bit_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_notificacion`
--

CREATE TABLE `tbl_notificacion` (
  `not_id` int(10) NOT NULL,
  `not_notificacion` varchar(255) NOT NULL,
  `not_fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `not_fin` date NOT NULL,
  `not_estado` tinyint(1) DEFAULT 1,
  `not_activo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_permisos`
--

CREATE TABLE `tbl_permisos` (
  `per_id` int(10) NOT NULL,
  `per_modulo` varchar(30) NOT NULL,
  `per_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_permisos`
--

INSERT INTO `tbl_permisos` (`per_id`, `per_modulo`, `per_estado`) VALUES
(1, 'seccion', 1),
(2, 'unidad curricular', 1),
(3, 'espacio', 1),
(4, 'usuario', 1),
(5, 'reportes', 1),
(7, 'malla curricular', 1),
(8, 'horario docente', 1),
(9, 'reporte estadístico', 1),
(10, 'eje', 1),
(11, 'categoría', 1),
(12, 'docentes', 1),
(13, 'año', 1),
(14, 'coordinacion', 1),
(15, 'titulo', 1),
(16, 'notas', 1),
(17, 'area', 1),
(18, 'turno', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_rol`
--

CREATE TABLE `tbl_rol` (
  `rol_id` int(10) NOT NULL,
  `rol_nombre` varchar(30) NOT NULL,
  `rol_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_rol`
--

INSERT INTO `tbl_rol` (`rol_id`, `rol_nombre`, `rol_estado`) VALUES
(1, 'Administrador', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_usuario`
--

CREATE TABLE `tbl_usuario` (
  `usu_id` int(10) NOT NULL,
  `usu_nombre` varchar(30) NOT NULL,
  `usu_correo` varchar(30) NOT NULL,
  `usu_contrasenia` varchar(70) NOT NULL,
  `usu_foto` varchar(255) DEFAULT NULL,
  `usu_cedula` int(8) DEFAULT NULL,
  `usu_docente` varchar(30) NOT NULL,
  `usu_estado` tinyint(1) NOT NULL,
  `reset_token` varchar(20) DEFAULT NULL,
  `reset_token_expira` datetime DEFAULT NULL,
  `rol_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_usuario`
--

INSERT INTO `tbl_usuario` (`usu_id`, `usu_nombre`, `usu_correo`, `usu_contrasenia`, `usu_foto`, `usu_cedula`, `usu_docente`, `usu_estado`, `reset_token`, `reset_token_expira`, `rol_id`) VALUES
(1, 'LigiaDuran', 'duranligia.pnfi@gmail.com', '$2y$10$lsIkF/Cq2.qSEX2LDH0fle/34.o8dazmlI9vRmWXFwcuDGFajzgJe', 'public/assets/profile/ligiaDuran.png?v=1754037180097', 0, '', 1, NULL, NULL, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD KEY `per_id` (`per_id`),
  ADD KEY `rol_id` (`rol_id`);

--
-- Indices de la tabla `tbl_bitacora`
--
ALTER TABLE `tbl_bitacora`
  ADD PRIMARY KEY (`usu_id`,`bit_modulo`,`bit_accion`,`bit_fecha`),
  ADD KEY `bit_usu` (`usu_id`);

--
-- Indices de la tabla `tbl_notificacion`
--
ALTER TABLE `tbl_notificacion`
  ADD PRIMARY KEY (`not_id`);

--
-- Indices de la tabla `tbl_permisos`
--
ALTER TABLE `tbl_permisos`
  ADD PRIMARY KEY (`per_id`);

--
-- Indices de la tabla `tbl_rol`
--
ALTER TABLE `tbl_rol`
  ADD PRIMARY KEY (`rol_id`);

--
-- Indices de la tabla `tbl_usuario`
--
ALTER TABLE `tbl_usuario`
  ADD PRIMARY KEY (`usu_id`),
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tbl_notificacion`
--
ALTER TABLE `tbl_notificacion`
  MODIFY `not_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_permisos`
--
ALTER TABLE `tbl_permisos`
  MODIFY `per_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `tbl_rol`
--
ALTER TABLE `tbl_rol`
  MODIFY `rol_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tbl_usuario`
--
ALTER TABLE `tbl_usuario`
  MODIFY `usu_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
