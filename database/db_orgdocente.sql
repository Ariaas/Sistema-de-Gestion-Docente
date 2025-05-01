-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-04-2025 a las 07:26:04
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
-- Base de datos: `db_orgdocente`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `crear_horariodocente`
--

CREATE TABLE `crear_horariodocente` (
  `hor_id` int(10) NOT NULL,
  `hdo_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docente_horario`
--

CREATE TABLE `docente_horario` (
  `doc_id` int(10) NOT NULL,
  `hor_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seccion_horario`
--

CREATE TABLE `seccion_horario` (
  `sec_id` int(10) NOT NULL,
  `hor_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_categoria`
--

CREATE TABLE `tbl_categoria` (
  `cat_id` int(10) NOT NULL,
  `cat_nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_docente`
--

CREATE TABLE `tbl_docente` (
  `doc_id` int(10) NOT NULL,
  `cat_id` int(10) NOT NULL,
  `doc_prefijo` char(1) NOT NULL,
  `doc_cedula` int(10) NOT NULL,
  `doc_nombre` varchar(30) NOT NULL,
  `doc_apellido` varchar(30) NOT NULL,
  `doc_correo` varchar(30) NOT NULL,
  `doc_dedicacion` enum('Dedicación exclusiva','Dedicación') NOT NULL,
  `doc_condicion` enum('Ordinario','Desordinario') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_eje`
--

CREATE TABLE `tbl_eje` (
  `eje_id` int(10) NOT NULL,
  `eje_nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_espacio`
--

CREATE TABLE `tbl_espacio` (
  `esp_id` int(10) NOT NULL,
  `esp_codigo` varchar(20) NOT NULL,
  `esp_tipo` enum('Aula','Laboratorio','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_horario`
--

CREATE TABLE `tbl_horario` (
  `hor_id` int(10) NOT NULL,
  `esp_id` int(10) NOT NULL,
  `hor_fase` enum('1','2') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_horariodocente`
--

CREATE TABLE `tbl_horariodocente` (
  `hdo_id` int(10) NOT NULL,
  `hdo_lapso` varchar(10) NOT NULL,
  `hdo_tipoactividad` varchar(30) NOT NULL,
  `hdo_descripcion` varchar(20) NOT NULL,
  `hdo_dependencia` varchar(30) NOT NULL,
  `hdo_observacion` varchar(30) NOT NULL,
  `hdo_hora` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_seccion`
--

CREATE TABLE `tbl_seccion` (
  `sec_id` int(10) NOT NULL,
  `tra_id` int(10) NOT NULL,
  `sec_codigo` varchar(20) NOT NULL,
  `sec_cantidad` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_titulo`
--

CREATE TABLE `tbl_titulo` (
  `tit_id` int(10) NOT NULL,
  `tit_prefijo` enum('Ingeniero','Doctorado','Master','') NOT NULL,
  `tit_nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_trayecto`
--

CREATE TABLE `tbl_trayecto` (
  `tra_id` int(10) NOT NULL,
  `tra_numero` enum('1','2','3','4') NOT NULL,
  `tra_año` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_uc`
--

CREATE TABLE `tbl_uc` (
  `uc_id` int(10) NOT NULL,
  `eje_id` int(10) NOT NULL,
  `tra_id` int(10) NOT NULL,
  `uc_codigo` varchar(20) NOT NULL,
  `uc_nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `titulo_docente`
--

CREATE TABLE `titulo_docente` (
  `tit_id` int(10) NOT NULL,
  `doc_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uc_docente`
--

CREATE TABLE `uc_docente` (
  `doc_id` int(11) NOT NULL,
  `uc_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uc_horario`
--

CREATE TABLE `uc_horario` (
  `uc_id` int(10) NOT NULL,
  `hor_id` int(10) NOT NULL,
  `hor_dia` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sábado','Domingo') NOT NULL,
  `hor_inicio` varchar(5) NOT NULL,
  `hor_fin` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `crear_horariodocente`
--
ALTER TABLE `crear_horariodocente`
  ADD KEY `horario_horariodocente` (`hor_id`),
  ADD KEY `horariodocente_horario` (`hdo_id`);

--
-- Indices de la tabla `docente_horario`
--
ALTER TABLE `docente_horario`
  ADD KEY `docente_horario` (`doc_id`),
  ADD KEY `horario_docente` (`hor_id`);

--
-- Indices de la tabla `seccion_horario`
--
ALTER TABLE `seccion_horario`
  ADD KEY `seccion_horario` (`sec_id`),
  ADD KEY `horario_seccion` (`hor_id`);

--
-- Indices de la tabla `tbl_categoria`
--
ALTER TABLE `tbl_categoria`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indices de la tabla `tbl_docente`
--
ALTER TABLE `tbl_docente`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `categoria_docente` (`cat_id`);

--
-- Indices de la tabla `tbl_eje`
--
ALTER TABLE `tbl_eje`
  ADD PRIMARY KEY (`eje_id`);

--
-- Indices de la tabla `tbl_espacio`
--
ALTER TABLE `tbl_espacio`
  ADD PRIMARY KEY (`esp_id`);

--
-- Indices de la tabla `tbl_horario`
--
ALTER TABLE `tbl_horario`
  ADD PRIMARY KEY (`hor_id`),
  ADD KEY `espacio_horario` (`esp_id`);

--
-- Indices de la tabla `tbl_horariodocente`
--
ALTER TABLE `tbl_horariodocente`
  ADD PRIMARY KEY (`hdo_id`);

--
-- Indices de la tabla `tbl_seccion`
--
ALTER TABLE `tbl_seccion`
  ADD PRIMARY KEY (`sec_id`),
  ADD KEY `trayecto_seccion` (`tra_id`);

--
-- Indices de la tabla `tbl_titulo`
--
ALTER TABLE `tbl_titulo`
  ADD PRIMARY KEY (`tit_id`);

--
-- Indices de la tabla `tbl_trayecto`
--
ALTER TABLE `tbl_trayecto`
  ADD PRIMARY KEY (`tra_id`);

--
-- Indices de la tabla `tbl_uc`
--
ALTER TABLE `tbl_uc`
  ADD PRIMARY KEY (`uc_id`),
  ADD KEY `trayecto_uc` (`tra_id`),
  ADD KEY `eje_uc` (`eje_id`);

--
-- Indices de la tabla `titulo_docente`
--
ALTER TABLE `titulo_docente`
  ADD KEY `docente_titulo` (`doc_id`),
  ADD KEY `titulo_docente` (`tit_id`);

--
-- Indices de la tabla `uc_docente`
--
ALTER TABLE `uc_docente`
  ADD KEY `docente_uc` (`doc_id`),
  ADD KEY `uc_docente` (`uc_id`);

--
-- Indices de la tabla `uc_horario`
--
ALTER TABLE `uc_horario`
  ADD KEY `uc_horario` (`uc_id`),
  ADD KEY `horario_uc` (`hor_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tbl_categoria`
--
ALTER TABLE `tbl_categoria`
  MODIFY `cat_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_docente`
--
ALTER TABLE `tbl_docente`
  MODIFY `doc_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_eje`
--
ALTER TABLE `tbl_eje`
  MODIFY `eje_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_espacio`
--
ALTER TABLE `tbl_espacio`
  MODIFY `esp_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_horario`
--
ALTER TABLE `tbl_horario`
  MODIFY `hor_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_horariodocente`
--
ALTER TABLE `tbl_horariodocente`
  MODIFY `hdo_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_seccion`
--
ALTER TABLE `tbl_seccion`
  MODIFY `sec_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_titulo`
--
ALTER TABLE `tbl_titulo`
  MODIFY `tit_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_trayecto`
--
ALTER TABLE `tbl_trayecto`
  MODIFY `tra_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_uc`
--
ALTER TABLE `tbl_uc`
  MODIFY `uc_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `crear_horariodocente`
--
ALTER TABLE `crear_horariodocente`
  ADD CONSTRAINT `horario_horariodocente` FOREIGN KEY (`hor_id`) REFERENCES `tbl_horario` (`hor_id`),
  ADD CONSTRAINT `horariodocente_horario` FOREIGN KEY (`hdo_id`) REFERENCES `tbl_horariodocente` (`hdo_id`);

--
-- Filtros para la tabla `docente_horario`
--
ALTER TABLE `docente_horario`
  ADD CONSTRAINT `docente_horario` FOREIGN KEY (`doc_id`) REFERENCES `tbl_docente` (`doc_id`),
  ADD CONSTRAINT `horario_docente` FOREIGN KEY (`hor_id`) REFERENCES `tbl_horario` (`hor_id`);

--
-- Filtros para la tabla `seccion_horario`
--
ALTER TABLE `seccion_horario`
  ADD CONSTRAINT `horario_seccion` FOREIGN KEY (`hor_id`) REFERENCES `tbl_horario` (`hor_id`),
  ADD CONSTRAINT `seccion_horario` FOREIGN KEY (`sec_id`) REFERENCES `tbl_seccion` (`sec_id`);

--
-- Filtros para la tabla `tbl_docente`
--
ALTER TABLE `tbl_docente`
  ADD CONSTRAINT `categoria_docente` FOREIGN KEY (`cat_id`) REFERENCES `tbl_categoria` (`cat_id`);

--
-- Filtros para la tabla `tbl_horario`
--
ALTER TABLE `tbl_horario`
  ADD CONSTRAINT `espacio_horario` FOREIGN KEY (`esp_id`) REFERENCES `tbl_espacio` (`esp_id`);

--
-- Filtros para la tabla `tbl_seccion`
--
ALTER TABLE `tbl_seccion`
  ADD CONSTRAINT `trayecto_seccion` FOREIGN KEY (`tra_id`) REFERENCES `tbl_trayecto` (`tra_id`);

--
-- Filtros para la tabla `tbl_uc`
--
ALTER TABLE `tbl_uc`
  ADD CONSTRAINT `eje_uc` FOREIGN KEY (`eje_id`) REFERENCES `tbl_eje` (`eje_id`),
  ADD CONSTRAINT `trayecto_uc` FOREIGN KEY (`tra_id`) REFERENCES `tbl_trayecto` (`tra_id`);

--
-- Filtros para la tabla `titulo_docente`
--
ALTER TABLE `titulo_docente`
  ADD CONSTRAINT `docente_titulo` FOREIGN KEY (`doc_id`) REFERENCES `tbl_docente` (`doc_id`),
  ADD CONSTRAINT `titulo_docente` FOREIGN KEY (`tit_id`) REFERENCES `tbl_titulo` (`tit_id`);

--
-- Filtros para la tabla `uc_docente`
--
ALTER TABLE `uc_docente`
  ADD CONSTRAINT `docente_uc` FOREIGN KEY (`doc_id`) REFERENCES `tbl_docente` (`doc_id`),
  ADD CONSTRAINT `uc_docente` FOREIGN KEY (`uc_id`) REFERENCES `tbl_uc` (`uc_id`);

--
-- Filtros para la tabla `uc_horario`
--
ALTER TABLE `uc_horario`
  ADD CONSTRAINT `horario_uc` FOREIGN KEY (`hor_id`) REFERENCES `tbl_horario` (`hor_id`),
  ADD CONSTRAINT `uc_horario` FOREIGN KEY (`uc_id`) REFERENCES `tbl_uc` (`uc_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
