-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-06-2025 a las 19:03:06
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.0.28

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
-- Estructura de tabla para la tabla `coordinacion_docente`
--

CREATE TABLE `coordinacion_docente` (
  `cor_id` int(10) NOT NULL,
  `doc_id` int(10) NOT NULL,
  `cor_doc_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `coordinacion_docente`
--

INSERT INTO `coordinacion_docente` (`cor_id`, `doc_id`, `cor_doc_estado`) VALUES
(2, 4, 1),
(2, 3, 1),
(2, 5, 1),
(2, 6, 1),
(2, 2, 1),
(2, 7, 1);

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

--
-- Volcado de datos para la tabla `docente_horario`
--

INSERT INTO `docente_horario` (`doc_id`, `hor_id`) VALUES
(1, 3),
(2, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pensum_certificado`
--

CREATE TABLE `pensum_certificado` (
  `mal_id` int(10) NOT NULL,
  `cert_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pensum_certificado`
--

INSERT INTO `pensum_certificado` (`mal_id`, `cert_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seccion_horario`
--

CREATE TABLE `seccion_horario` (
  `sec_id` int(10) NOT NULL,
  `hor_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `seccion_horario`
--

INSERT INTO `seccion_horario` (`sec_id`, `hor_id`) VALUES
(3, 1),
(3, 2),
(3, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_anio`
--

CREATE TABLE `tbl_anio` (
  `ani_id` int(10) NOT NULL,
  `ani_anio` year(4) NOT NULL,
  `ani_activo` tinyint(1) NOT NULL,
  `ani_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_anio`
--

INSERT INTO `tbl_anio` (`ani_id`, `ani_anio`, `ani_activo`, `ani_estado`) VALUES
(1, '2025', 1, 1),
(2, '2024', 0, 1),
(3, '2026', 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_area`
--

CREATE TABLE `tbl_area` (
  `area_id` int(10) NOT NULL,
  `area_nombre` varchar(30) NOT NULL,
  `area_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_area`
--

INSERT INTO `tbl_area` (`area_id`, `area_nombre`, `area_estado`) VALUES
(1, 'Diseño', 1),
(2, 'Programacion', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_categoria`
--

CREATE TABLE `tbl_categoria` (
  `cat_id` int(10) NOT NULL,
  `cat_nombre` varchar(30) NOT NULL,
  `cat_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_categoria`
--

INSERT INTO `tbl_categoria` (`cat_id`, `cat_nombre`, `cat_estado`) VALUES
(1, 'Instructor', 1),
(2, 'Titular', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_certificacion`
--

CREATE TABLE `tbl_certificacion` (
  `cert_id` int(10) NOT NULL,
  `tra_id` int(10) NOT NULL,
  `cert_nombre` varchar(20) NOT NULL,
  `cert_tipo` varchar(30) NOT NULL,
  `cert_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_certificacion`
--

INSERT INTO `tbl_certificacion` (`cert_id`, `tra_id`, `cert_nombre`, `cert_tipo`, `cert_estado`) VALUES
(1, 2, 'certificado  1', 'anual', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_cohorte`
--

CREATE TABLE `tbl_cohorte` (
  `coh_id` int(10) NOT NULL,
  `coh_numero` int(10) NOT NULL,
  `coh_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_cohorte`
--

INSERT INTO `tbl_cohorte` (`coh_id`, `coh_numero`, `coh_estado`) VALUES
(1, 2, 1),
(2, 4, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_convenio`
--

CREATE TABLE `tbl_convenio` (
  `con_id` int(10) NOT NULL,
  `tra_id` int(10) NOT NULL,
  `con_nombre` varchar(30) NOT NULL,
  `con_inicio` date NOT NULL,
  `con_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_convenio`
--

INSERT INTO `tbl_convenio` (`con_id`, `tra_id`, `con_nombre`, `con_inicio`, `con_estado`) VALUES
(1, 1, 'iujo', '2025-06-20', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_coordinacion`
--

CREATE TABLE `tbl_coordinacion` (
  `cor_id` int(10) NOT NULL,
  `cor_nombre` varchar(30) NOT NULL,
  `cor_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_coordinacion`
--

INSERT INTO `tbl_coordinacion` (`cor_id`, `cor_nombre`, `cor_estado`) VALUES
(1, 'holaa', 0),
(2, 'Ingeniería de software', 1);

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
  `doc_dedicacion` enum('Exclusiva','Medio tiempo','Tiempo completo') NOT NULL,
  `doc_condicion` enum('Ordinario','Contratado') NOT NULL,
  `doc_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_docente`
--

INSERT INTO `tbl_docente` (`doc_id`, `cat_id`, `doc_prefijo`, `doc_cedula`, `doc_nombre`, `doc_apellido`, `doc_correo`, `doc_dedicacion`, `doc_condicion`, `doc_estado`) VALUES
(1, 1, 'V', 56778453, 'Alberto', 'Velasquez', 'velasques@gmail.com', 'Medio tiempo', 'Contratado', 0),
(2, 1, 'V', 34556298, 'Engels Moises', 'Morillo', 'morillo@gmail.com', 'Exclusiva', 'Ordinario', 1),
(3, 2, 'V', 30894865, 'Liam', 'Morillo perez', 'liam@gmail.com', 'Medio tiempo', 'Ordinario', 0),
(4, 1, 'V', 2323323, 'adsadad', 'dasdasd', 'dsadasdasd@gmail.com', 'Exclusiva', 'Ordinario', 0),
(5, 1, 'V', 56778234, 'Juan ', 'Gonzalez', 'gonzalez@gmail.com', 'Medio tiempo', 'Contratado', 1),
(6, 1, 'V', 43242323, 'fdsfdsf', 'sdfdsf', 'sdfsdfgq@gmail.com', 'Exclusiva', 'Ordinario', 0),
(7, 1, 'V', 3343443, 'asdsadsad', 'asdasd', 'dasadsasd@gmail.com', 'Medio tiempo', 'Ordinario', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_eje`
--

CREATE TABLE `tbl_eje` (
  `eje_id` int(10) NOT NULL,
  `eje_nombre` varchar(30) NOT NULL,
  `eje_descripcion` varchar(50) NOT NULL,
  `eje_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_eje`
--

INSERT INTO `tbl_eje` (`eje_id`, `eje_nombre`, `eje_descripcion`, `eje_estado`) VALUES
(1, 'Ludico', '', 1),
(2, 'Didactico', '', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_espacio`
--

CREATE TABLE `tbl_espacio` (
  `esp_id` int(10) NOT NULL,
  `esp_codigo` varchar(20) NOT NULL,
  `esp_tipo` enum('aula','laboratorio') NOT NULL,
  `esp_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_espacio`
--

INSERT INTO `tbl_espacio` (`esp_id`, `esp_codigo`, `esp_tipo`, `esp_estado`) VALUES
(1, 'G-27', 'aula', 1),
(2, 'L-6', 'laboratorio', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_fase`
--

CREATE TABLE `tbl_fase` (
  `fase_id` int(10) NOT NULL,
  `tra_id` int(10) NOT NULL,
  `fase_numero` enum('1','2') NOT NULL,
  `fase_apertura` date NOT NULL,
  `fase_cierre` date NOT NULL,
  `fase_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_fase`
--

INSERT INTO `tbl_fase` (`fase_id`, `tra_id`, `fase_numero`, `fase_apertura`, `fase_cierre`, `fase_estado`) VALUES
(1, 1, '2', '2005-02-04', '2005-02-05', 0),
(2, 1, '1', '2025-01-01', '2025-06-30', 0),
(3, 2, '2', '2025-01-01', '2025-07-31', 0),
(4, 1, '1', '2025-06-20', '2025-12-31', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_horario`
--

CREATE TABLE `tbl_horario` (
  `hor_id` int(10) NOT NULL,
  `fase_id` int(10) NOT NULL,
  `esp_id` int(10) NOT NULL,
  `tur_id` int(10) NOT NULL,
  `hor_modalidad` enum('presencial','semipresencial') NOT NULL,
  `hor_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_horario`
--

INSERT INTO `tbl_horario` (`hor_id`, `fase_id`, `esp_id`, `tur_id`, `hor_modalidad`, `hor_estado`) VALUES
(1, 1, 1, 1, 'presencial', 0),
(2, 1, 1, 1, 'presencial', 1),
(3, 1, 2, 1, 'presencial', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_horariodocente`
--

CREATE TABLE `tbl_horariodocente` (
  `hdo_id` int(10) NOT NULL,
  `doc_id` int(10) NOT NULL,
  `hdo_lapso` varchar(10) NOT NULL,
  `hdo_tipoactividad` varchar(30) NOT NULL,
  `hdo_descripcion` varchar(20) NOT NULL,
  `hdo_dependencia` varchar(30) NOT NULL,
  `hdo_observacion` varchar(30) NOT NULL,
  `hdo_horas` varchar(30) NOT NULL,
  `hdo_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_malla`
--

CREATE TABLE `tbl_malla` (
  `mal_id` int(10) NOT NULL,
  `coh_id` int(10) NOT NULL,
  `ani_id` int(10) NOT NULL,
  `mal_codigo` varchar(10) NOT NULL,
  `mal_nombre` varchar(30) NOT NULL,
  `mal_descripcion` varchar(30) NOT NULL,
  `mal_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_malla`
--

INSERT INTO `tbl_malla` (`mal_id`, `coh_id`, `ani_id`, `mal_codigo`, `mal_nombre`, `mal_descripcion`, `mal_estado`) VALUES
(1, 1, 1, '1232', 'Malla 23', 'Nuevo pensum', 1),
(2, 2, 2, '3456', 'Malla 2024', 'Pensum24', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_prosecucion`
--

CREATE TABLE `tbl_prosecucion` (
  `pro_id` int(10) NOT NULL,
  `sec_id_origen` int(10) NOT NULL,
  `sec_id_promocion` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_seccion`
--

CREATE TABLE `tbl_seccion` (
  `sec_id` int(10) NOT NULL,
  `tra_id` int(10) NOT NULL,
  `coh_id` int(10) NOT NULL,
  `sec_nombre` varchar(5) NOT NULL,
  `sec_codigo` varchar(10) NOT NULL,
  `sec_cantidad` int(10) NOT NULL,
  `sec_grupo` int(4) NOT NULL,
  `sec_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_seccion`
--

INSERT INTO `tbl_seccion` (`sec_id`, `tra_id`, `coh_id`, `sec_nombre`, `sec_codigo`, `sec_cantidad`, `sec_grupo`, `sec_estado`) VALUES
(3, 1, 1, '3104', 'IN', 25, 1, 1),
(4, 2, 1, '2014', 'IN', 25, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_titulo`
--

CREATE TABLE `tbl_titulo` (
  `tit_id` int(10) NOT NULL,
  `tit_prefijo` varchar(20) NOT NULL,
  `tit_nombre` varchar(20) NOT NULL,
  `tit_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_titulo`
--

INSERT INTO `tbl_titulo` (`tit_id`, `tit_prefijo`, `tit_nombre`, `tit_estado`) VALUES
(1, 'Ingeniero', 'Informatica', 1),
(2, 'Master', 'Base de datos', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_trayecto`
--

CREATE TABLE `tbl_trayecto` (
  `tra_id` int(10) NOT NULL,
  `tra_numero` enum('inicial','1','2','3','4') NOT NULL,
  `tra_tipo` enum('regular','intensivo') NOT NULL,
  `tra_estado` tinyint(1) NOT NULL,
  `ani_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_trayecto`
--

INSERT INTO `tbl_trayecto` (`tra_id`, `tra_numero`, `tra_tipo`, `tra_estado`, `ani_id`) VALUES
(1, '1', 'regular', 1, 1),
(2, '2', 'intensivo', 1, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_turno`
--

CREATE TABLE `tbl_turno` (
  `tur_id` int(10) NOT NULL,
  `tur_horainicio` time NOT NULL,
  `tur_horafin` time NOT NULL,
  `tur_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_turno`
--

INSERT INTO `tbl_turno` (`tur_id`, `tur_horainicio`, `tur_horafin`, `tur_estado`) VALUES
(1, '02:07:00', '03:10:00', 1),
(2, '05:10:00', '06:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_uc`
--

CREATE TABLE `tbl_uc` (
  `uc_id` int(10) NOT NULL,
  `eje_id` int(10) NOT NULL,
  `tra_id` int(10) NOT NULL,
  `area_id` int(10) NOT NULL,
  `uc_codigo` varchar(30) NOT NULL,
  `uc_nombre` varchar(30) NOT NULL,
  `uc_hora_independiente` int(3) NOT NULL,
  `uc_hora_asistida` int(3) NOT NULL,
  `uc_hora_academica` int(3) NOT NULL,
  `uc_creditos` int(2) NOT NULL,
  `uc_periodo` enum('anual','0','1','2') NOT NULL,
  `uc_electiva` tinyint(1) NOT NULL,
  `uc_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_uc`
--

INSERT INTO `tbl_uc` (`uc_id`, `eje_id`, `tra_id`, `area_id`, `uc_codigo`, `uc_nombre`, `uc_hora_independiente`, `uc_hora_asistida`, `uc_hora_academica`, `uc_creditos`, `uc_periodo`, `uc_electiva`, `uc_estado`) VALUES
(1, 1, 1, 1, 'BD34', 'Base de datos', 23, 34, 56, 4, '1', 1, 1),
(2, 1, 1, 1, 'hola', 'comovas', 3, 3, 3, 3, '1', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `titulo_docente`
--

CREATE TABLE `titulo_docente` (
  `doc_id` int(10) NOT NULL,
  `tit_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `titulo_docente`
--

INSERT INTO `titulo_docente` (`doc_id`, `tit_id`) VALUES
(1, 1),
(2, 1),
(2, 2),
(3, 2),
(4, 1),
(4, 2),
(5, 2),
(6, 1),
(7, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uc_docente`
--

CREATE TABLE `uc_docente` (
  `doc_id` int(10) NOT NULL,
  `uc_id` int(10) NOT NULL,
  `uc_doc_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `uc_docente`
--

INSERT INTO `uc_docente` (`doc_id`, `uc_id`, `uc_doc_estado`) VALUES
(1, 1, 1),
(2, 1, 1),
(2, 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uc_horario`
--

CREATE TABLE `uc_horario` (
  `hor_id` int(10) NOT NULL,
  `uc_id` int(10) NOT NULL,
  `hor_dia` enum('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado') NOT NULL,
  `hor_horainicio` varchar(5) NOT NULL,
  `hor_horafin` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `uc_horario`
--

INSERT INTO `uc_horario` (`hor_id`, `uc_id`, `hor_dia`, `hor_horainicio`, `hor_horafin`) VALUES
(1, 1, 'Lunes', '02:07', '03:10'),
(2, 1, 'Lunes', '02:07', '03:10'),
(3, 1, 'Miércoles', '02:07', '03:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uc_pensum`
--

CREATE TABLE `uc_pensum` (
  `uc_id` int(10) NOT NULL,
  `mal_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `uc_pensum`
--

INSERT INTO `uc_pensum` (`uc_id`, `mal_id`) VALUES
(1, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `coordinacion_docente`
--
ALTER TABLE `coordinacion_docente`
  ADD KEY `cor_id` (`cor_id`),
  ADD KEY `doc_id` (`doc_id`);

--
-- Indices de la tabla `crear_horariodocente`
--
ALTER TABLE `crear_horariodocente`
  ADD PRIMARY KEY (`hor_id`,`hdo_id`),
  ADD KEY `hdo_id` (`hdo_id`);

--
-- Indices de la tabla `docente_horario`
--
ALTER TABLE `docente_horario`
  ADD PRIMARY KEY (`doc_id`,`hor_id`),
  ADD KEY `hor_id` (`hor_id`);

--
-- Indices de la tabla `pensum_certificado`
--
ALTER TABLE `pensum_certificado`
  ADD PRIMARY KEY (`mal_id`,`cert_id`),
  ADD KEY `cert_id` (`cert_id`);

--
-- Indices de la tabla `seccion_horario`
--
ALTER TABLE `seccion_horario`
  ADD PRIMARY KEY (`sec_id`,`hor_id`),
  ADD KEY `hor_id` (`hor_id`);

--
-- Indices de la tabla `tbl_anio`
--
ALTER TABLE `tbl_anio`
  ADD PRIMARY KEY (`ani_id`);

--
-- Indices de la tabla `tbl_area`
--
ALTER TABLE `tbl_area`
  ADD PRIMARY KEY (`area_id`);

--
-- Indices de la tabla `tbl_categoria`
--
ALTER TABLE `tbl_categoria`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indices de la tabla `tbl_certificacion`
--
ALTER TABLE `tbl_certificacion`
  ADD PRIMARY KEY (`cert_id`),
  ADD KEY `tra_id` (`tra_id`);

--
-- Indices de la tabla `tbl_cohorte`
--
ALTER TABLE `tbl_cohorte`
  ADD PRIMARY KEY (`coh_id`);

--
-- Indices de la tabla `tbl_convenio`
--
ALTER TABLE `tbl_convenio`
  ADD PRIMARY KEY (`con_id`),
  ADD KEY `tra_id` (`tra_id`);

--
-- Indices de la tabla `tbl_coordinacion`
--
ALTER TABLE `tbl_coordinacion`
  ADD PRIMARY KEY (`cor_id`);

--
-- Indices de la tabla `tbl_docente`
--
ALTER TABLE `tbl_docente`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `cat_id` (`cat_id`);

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
-- Indices de la tabla `tbl_fase`
--
ALTER TABLE `tbl_fase`
  ADD PRIMARY KEY (`fase_id`),
  ADD KEY `tra_id` (`tra_id`);

--
-- Indices de la tabla `tbl_horario`
--
ALTER TABLE `tbl_horario`
  ADD PRIMARY KEY (`hor_id`),
  ADD KEY `fase_id` (`fase_id`),
  ADD KEY `tur_id` (`tur_id`),
  ADD KEY `esp_id` (`esp_id`);

--
-- Indices de la tabla `tbl_horariodocente`
--
ALTER TABLE `tbl_horariodocente`
  ADD PRIMARY KEY (`hdo_id`),
  ADD KEY `doc_id` (`doc_id`);

--
-- Indices de la tabla `tbl_malla`
--
ALTER TABLE `tbl_malla`
  ADD PRIMARY KEY (`mal_id`),
  ADD KEY `coh_id` (`coh_id`),
  ADD KEY `ani_id` (`ani_id`);

--
-- Indices de la tabla `tbl_prosecucion`
--
ALTER TABLE `tbl_prosecucion`
  ADD PRIMARY KEY (`pro_id`),
  ADD KEY `sec_id_origen` (`sec_id_origen`),
  ADD KEY `sec_id_promocion` (`sec_id_promocion`);

--
-- Indices de la tabla `tbl_seccion`
--
ALTER TABLE `tbl_seccion`
  ADD PRIMARY KEY (`sec_id`),
  ADD KEY `tra_id` (`tra_id`),
  ADD KEY `coh_id` (`coh_id`);

--
-- Indices de la tabla `tbl_titulo`
--
ALTER TABLE `tbl_titulo`
  ADD PRIMARY KEY (`tit_id`);

--
-- Indices de la tabla `tbl_trayecto`
--
ALTER TABLE `tbl_trayecto`
  ADD PRIMARY KEY (`tra_id`),
  ADD KEY `anio_trayecto` (`ani_id`);

--
-- Indices de la tabla `tbl_turno`
--
ALTER TABLE `tbl_turno`
  ADD PRIMARY KEY (`tur_id`);

--
-- Indices de la tabla `tbl_uc`
--
ALTER TABLE `tbl_uc`
  ADD PRIMARY KEY (`uc_id`),
  ADD KEY `eje_id` (`eje_id`),
  ADD KEY `tra_id` (`tra_id`),
  ADD KEY `area_id` (`area_id`);

--
-- Indices de la tabla `titulo_docente`
--
ALTER TABLE `titulo_docente`
  ADD PRIMARY KEY (`doc_id`,`tit_id`),
  ADD KEY `tit_id` (`tit_id`);

--
-- Indices de la tabla `uc_docente`
--
ALTER TABLE `uc_docente`
  ADD PRIMARY KEY (`doc_id`,`uc_id`),
  ADD KEY `uc_id` (`uc_id`);

--
-- Indices de la tabla `uc_horario`
--
ALTER TABLE `uc_horario`
  ADD PRIMARY KEY (`hor_id`,`uc_id`),
  ADD KEY `uc_id` (`uc_id`);

--
-- Indices de la tabla `uc_pensum`
--
ALTER TABLE `uc_pensum`
  ADD PRIMARY KEY (`uc_id`,`mal_id`),
  ADD KEY `mal_id` (`mal_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tbl_anio`
--
ALTER TABLE `tbl_anio`
  MODIFY `ani_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tbl_area`
--
ALTER TABLE `tbl_area`
  MODIFY `area_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_categoria`
--
ALTER TABLE `tbl_categoria`
  MODIFY `cat_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_certificacion`
--
ALTER TABLE `tbl_certificacion`
  MODIFY `cert_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tbl_cohorte`
--
ALTER TABLE `tbl_cohorte`
  MODIFY `coh_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_convenio`
--
ALTER TABLE `tbl_convenio`
  MODIFY `con_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tbl_coordinacion`
--
ALTER TABLE `tbl_coordinacion`
  MODIFY `cor_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_docente`
--
ALTER TABLE `tbl_docente`
  MODIFY `doc_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tbl_eje`
--
ALTER TABLE `tbl_eje`
  MODIFY `eje_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_espacio`
--
ALTER TABLE `tbl_espacio`
  MODIFY `esp_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_fase`
--
ALTER TABLE `tbl_fase`
  MODIFY `fase_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tbl_horario`
--
ALTER TABLE `tbl_horario`
  MODIFY `hor_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tbl_horariodocente`
--
ALTER TABLE `tbl_horariodocente`
  MODIFY `hdo_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_malla`
--
ALTER TABLE `tbl_malla`
  MODIFY `mal_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_prosecucion`
--
ALTER TABLE `tbl_prosecucion`
  MODIFY `pro_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_seccion`
--
ALTER TABLE `tbl_seccion`
  MODIFY `sec_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tbl_titulo`
--
ALTER TABLE `tbl_titulo`
  MODIFY `tit_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_trayecto`
--
ALTER TABLE `tbl_trayecto`
  MODIFY `tra_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_turno`
--
ALTER TABLE `tbl_turno`
  MODIFY `tur_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_uc`
--
ALTER TABLE `tbl_uc`
  MODIFY `uc_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `coordinacion_docente`
--
ALTER TABLE `coordinacion_docente`
  ADD CONSTRAINT `coordinacion_docente_ibfk_1` FOREIGN KEY (`cor_id`) REFERENCES `tbl_coordinacion` (`cor_id`),
  ADD CONSTRAINT `coordinacion_docente_ibfk_2` FOREIGN KEY (`doc_id`) REFERENCES `tbl_docente` (`doc_id`);

--
-- Filtros para la tabla `crear_horariodocente`
--
ALTER TABLE `crear_horariodocente`
  ADD CONSTRAINT `crear_horariodocente_ibfk_1` FOREIGN KEY (`hor_id`) REFERENCES `tbl_horario` (`hor_id`),
  ADD CONSTRAINT `crear_horariodocente_ibfk_2` FOREIGN KEY (`hdo_id`) REFERENCES `tbl_horariodocente` (`hdo_id`);

--
-- Filtros para la tabla `docente_horario`
--
ALTER TABLE `docente_horario`
  ADD CONSTRAINT `docente_horario_ibfk_1` FOREIGN KEY (`doc_id`) REFERENCES `tbl_docente` (`doc_id`),
  ADD CONSTRAINT `docente_horario_ibfk_2` FOREIGN KEY (`hor_id`) REFERENCES `tbl_horario` (`hor_id`);

--
-- Filtros para la tabla `pensum_certificado`
--
ALTER TABLE `pensum_certificado`
  ADD CONSTRAINT `pensum_certificado_ibfk_1` FOREIGN KEY (`mal_id`) REFERENCES `tbl_malla` (`mal_id`),
  ADD CONSTRAINT `pensum_certificado_ibfk_2` FOREIGN KEY (`cert_id`) REFERENCES `tbl_certificacion` (`cert_id`);

--
-- Filtros para la tabla `seccion_horario`
--
ALTER TABLE `seccion_horario`
  ADD CONSTRAINT `seccion_horario_ibfk_1` FOREIGN KEY (`sec_id`) REFERENCES `tbl_seccion` (`sec_id`),
  ADD CONSTRAINT `seccion_horario_ibfk_2` FOREIGN KEY (`hor_id`) REFERENCES `tbl_horario` (`hor_id`);

--
-- Filtros para la tabla `tbl_certificacion`
--
ALTER TABLE `tbl_certificacion`
  ADD CONSTRAINT `tbl_certificacion_ibfk_1` FOREIGN KEY (`tra_id`) REFERENCES `tbl_trayecto` (`tra_id`);

--
-- Filtros para la tabla `tbl_convenio`
--
ALTER TABLE `tbl_convenio`
  ADD CONSTRAINT `tbl_convenio_ibfk_1` FOREIGN KEY (`tra_id`) REFERENCES `tbl_trayecto` (`tra_id`);

--
-- Filtros para la tabla `tbl_docente`
--
ALTER TABLE `tbl_docente`
  ADD CONSTRAINT `tbl_docente_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `tbl_categoria` (`cat_id`);

--
-- Filtros para la tabla `tbl_fase`
--
ALTER TABLE `tbl_fase`
  ADD CONSTRAINT `tbl_fase_ibfk_1` FOREIGN KEY (`tra_id`) REFERENCES `tbl_trayecto` (`tra_id`);

--
-- Filtros para la tabla `tbl_horario`
--
ALTER TABLE `tbl_horario`
  ADD CONSTRAINT `tbl_horario_ibfk_1` FOREIGN KEY (`fase_id`) REFERENCES `tbl_fase` (`fase_id`),
  ADD CONSTRAINT `tbl_horario_ibfk_2` FOREIGN KEY (`tur_id`) REFERENCES `tbl_turno` (`tur_id`),
  ADD CONSTRAINT `tbl_horario_ibfk_3` FOREIGN KEY (`esp_id`) REFERENCES `tbl_espacio` (`esp_id`);

--
-- Filtros para la tabla `tbl_horariodocente`
--
ALTER TABLE `tbl_horariodocente`
  ADD CONSTRAINT `tbl_horariodocente_ibfk_1` FOREIGN KEY (`doc_id`) REFERENCES `tbl_docente` (`doc_id`);

--
-- Filtros para la tabla `tbl_malla`
--
ALTER TABLE `tbl_malla`
  ADD CONSTRAINT `tbl_malla_ibfk_1` FOREIGN KEY (`coh_id`) REFERENCES `tbl_cohorte` (`coh_id`),
  ADD CONSTRAINT `tbl_malla_ibfk_2` FOREIGN KEY (`ani_id`) REFERENCES `tbl_anio` (`ani_id`);

--
-- Filtros para la tabla `tbl_prosecucion`
--
ALTER TABLE `tbl_prosecucion`
  ADD CONSTRAINT `tbl_prosecucion_ibfk_1` FOREIGN KEY (`sec_id_origen`) REFERENCES `tbl_seccion` (`sec_id`),
  ADD CONSTRAINT `tbl_prosecucion_ibfk_2` FOREIGN KEY (`sec_id_promocion`) REFERENCES `tbl_seccion` (`sec_id`);

--
-- Filtros para la tabla `tbl_seccion`
--
ALTER TABLE `tbl_seccion`
  ADD CONSTRAINT `tbl_seccion_ibfk_1` FOREIGN KEY (`tra_id`) REFERENCES `tbl_trayecto` (`tra_id`),
  ADD CONSTRAINT `tbl_seccion_ibfk_2` FOREIGN KEY (`coh_id`) REFERENCES `tbl_cohorte` (`coh_id`);

--
-- Filtros para la tabla `tbl_trayecto`
--
ALTER TABLE `tbl_trayecto`
  ADD CONSTRAINT `anio_trayecto` FOREIGN KEY (`ani_id`) REFERENCES `tbl_anio` (`ani_id`);

--
-- Filtros para la tabla `tbl_uc`
--
ALTER TABLE `tbl_uc`
  ADD CONSTRAINT `tbl_uc_ibfk_1` FOREIGN KEY (`eje_id`) REFERENCES `tbl_eje` (`eje_id`),
  ADD CONSTRAINT `tbl_uc_ibfk_2` FOREIGN KEY (`tra_id`) REFERENCES `tbl_trayecto` (`tra_id`),
  ADD CONSTRAINT `tbl_uc_ibfk_3` FOREIGN KEY (`area_id`) REFERENCES `tbl_area` (`area_id`);

--
-- Filtros para la tabla `titulo_docente`
--
ALTER TABLE `titulo_docente`
  ADD CONSTRAINT `titulo_docente_ibfk_1` FOREIGN KEY (`doc_id`) REFERENCES `tbl_docente` (`doc_id`),
  ADD CONSTRAINT `titulo_docente_ibfk_2` FOREIGN KEY (`tit_id`) REFERENCES `tbl_titulo` (`tit_id`);

--
-- Filtros para la tabla `uc_docente`
--
ALTER TABLE `uc_docente`
  ADD CONSTRAINT `uc_docente_ibfk_1` FOREIGN KEY (`doc_id`) REFERENCES `tbl_docente` (`doc_id`),
  ADD CONSTRAINT `uc_docente_ibfk_2` FOREIGN KEY (`uc_id`) REFERENCES `tbl_uc` (`uc_id`);

--
-- Filtros para la tabla `uc_horario`
--
ALTER TABLE `uc_horario`
  ADD CONSTRAINT `uc_horario_ibfk_1` FOREIGN KEY (`hor_id`) REFERENCES `tbl_horario` (`hor_id`),
  ADD CONSTRAINT `uc_horario_ibfk_2` FOREIGN KEY (`uc_id`) REFERENCES `tbl_uc` (`uc_id`);

--
-- Filtros para la tabla `uc_pensum`
--
ALTER TABLE `uc_pensum`
  ADD CONSTRAINT `uc_pensum_ibfk_1` FOREIGN KEY (`uc_id`) REFERENCES `tbl_uc` (`uc_id`),
  ADD CONSTRAINT `uc_pensum_ibfk_2` FOREIGN KEY (`mal_id`) REFERENCES `tbl_malla` (`mal_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
