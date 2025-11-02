-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-11-2025 a las 03:18:26
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
-- Estructura de tabla para la tabla `coordinacion_docente`
--

CREATE TABLE `coordinacion_docente` (
  `cor_nombre` varchar(30) NOT NULL,
  `doc_cedula` int(11) NOT NULL,
  `cor_doc_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `coordinacion_docente`
--

INSERT INTO `coordinacion_docente` (`cor_nombre`, `doc_cedula`, `cor_doc_estado`) VALUES
('Académica', 7439117, 1),
('Actividades Acreditables I', 12701387, 1),
('Actividades Acreditables I', 16385182, 1),
('Actividades Acreditables I', 18103232, 1),
('Actividades Acreditables I', 20351422, 1),
('Administración de Base de Dato', 5260810, 1),
('Algorítmica y Programación', 7391773, 1),
('Algorítmica y Programación', 7439117, 1),
('Algorítmica y Programación', 9629702, 1),
('Algorítmica y Programación', 10723015, 1),
('Algorítmica y Programación', 10846157, 1),
('Algorítmica y Programación', 15693145, 1),
('Arquitectura del Computador', 7424546, 1),
('Arquitectura del Computador', 10778236, 1),
('Arquitectura del Computador', 10844463, 1),
('Arquitectura del Computador', 11898335, 1),
('Auditoria de Sistemas', 16403903, 1),
('Base de Datos', 5260810, 1),
('Base de Datos', 9540060, 1),
('Base de Datos', 9555514, 1),
('Base de Datos', 13991250, 1),
('Centro de Estudio Investigació', 9619518, 1),
('Currículo', 25471240, 1),
('Deporte', 18103232, 1),
('Doctorados', 7404027, 1),
('Educación Municipalizada', 9619518, 1),
('Eje Epistemológico', 3759671, 1),
('Eje Estético Lúdico', 18103232, 1),
('Eje Etico Político', 3759671, 1),
('Electiva 1', 26197135, 1),
('Electiva 1', 29880797, 1),
('Electiva 1', 30395804, 1),
('Electiva II', 5260810, 1),
('Electiva II', 9555514, 1),
('Electiva II', 29517943, 1),
('Electiva II', 30088284, 1),
('Electiva II', 30395804, 1),
('Electiva III', 25471240, 1),
('Electiva III', 30088284, 1),
('Electiva IV', 7391773, 1),
('EMTICL', 7439117, 1),
('EMTICL', 9619518, 1),
('EMTICL', 10723015, 1),
('Formación Crítica TI, TII, TII', 9629702, 1),
('Formación Crítica TI, TII, TII', 13527711, 1),
('Formación Crítica TI, TII, TII', 14159756, 1),
('Formación Crítica TI, TII, TII', 23316126, 1),
('Formación Crítica TI, TII, TII', 24418577, 1),
('Gestión de Proyectos', 16403903, 1),
('Gestión TIC', 7404027, 1),
('Gestión TIC', 10775753, 1),
('Idiomas', 13188691, 1),
('Idiomas I', 13695847, 1),
('Idiomas I', 18356682, 1),
('Idiomas II', 18356682, 1),
('Ingeniería del Software', 9555514, 1),
('Ingeniería del Software', 13991971, 1),
('Ingeniería del Software', 30395804, 1),
('Ingeniería del Software II', 13991971, 1),
('Ingeniería del Software II', 30395804, 1),
('Ingenierías', 10723015, 1),
('Investigación', 10775753, 1),
('Investigación de Operaciones', 7392496, 1),
('Laboratorios', 9619518, 1),
('Laboratorios', 10778236, 1),
('Laboratorios', 10956121, 1),
('Laboratorios', 13188691, 1),
('Matemática Aplicada', 14677589, 1),
('Matemática Aplicada', 18912216, 1),
('Matemática I, II, III, IV', 9627295, 1),
('Matemática I, II, III, IV', 10775753, 1),
('Matemática I, II, III, IV', 14677589, 1),
('Matemática I, II, III, IV', 15351688, 1),
('Matemática I, II, III, IV', 17354607, 1),
('Modelado de Base de Datos', 5260810, 1),
('Modelado de Base de Datos', 9540060, 1),
('Modelado de Base de Datos', 10844463, 1),
('Nocturna y Fin de Semana', 10956121, 1),
('PNFA-PNFI', 7404027, 1),
('PNFA-PNFI', 25471240, 1),
('Preparaduría', 14677589, 1),
('Programación', 10846157, 1),
('Programación', 13188691, 1),
('Programación', 25471240, 1),
('Programación', 29517943, 1),
('Proyecto Sociotecnológico I', 7423485, 1),
('Proyecto Sociotecnológico I', 10723015, 1),
('Proyecto Sociotecnológico I', 10848316, 1),
('Proyecto Sociotecnológico I', 13527711, 1),
('Proyecto Sociotecnológico I', 15170003, 1),
('Proyecto Sociotecnológico I', 26197135, 1),
('Proyecto Sociotecnológico II', 5260810, 1),
('Proyecto Sociotecnológico II', 7439117, 1),
('Proyecto Sociotecnológico II', 9540060, 1),
('Proyecto Sociotecnológico II', 9555514, 1),
('Proyecto Sociotecnológico II', 13991250, 1),
('Proyecto Sociotecnológico II', 16403903, 1),
('Proyecto Sociotecnológico II', 25471240, 1),
('Proyecto Sociotecnológico III', 7391773, 1),
('Proyecto Sociotecnológico III', 7392496, 1),
('Proyecto Sociotecnológico III', 10844463, 1),
('Proyecto Sociotecnológico III', 25471240, 1),
('Proyecto Sociotecnológico IV', 10723015, 1),
('Proyecto Sociotecnológico IV', 16403903, 1),
('Redes Avanzadas', 5260810, 1),
('Redes del Computador', 11898335, 1),
('Redes del Computador', 13188691, 1),
('Redes del Computador', 14159756, 1),
('Seguridad', 25471240, 1),
('Sistemas Operativos', 7392496, 1),
('Tutorías Académicas', 3759671, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docente_horario`
--

CREATE TABLE `docente_horario` (
  `doc_cedula` int(11) DEFAULT NULL,
  `sec_codigo` varchar(30) DEFAULT NULL,
  `ani_anio` int(11) DEFAULT NULL,
  `ani_tipo` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `docente_horario`
--

INSERT INTO `docente_horario` (`doc_cedula`, `sec_codigo`, `ani_anio`, `ani_tipo`) VALUES
(10846157, 'IN1202', 2025, 'regular'),
(18912216, 'IN1202', 2025, 'regular'),
(10846157, 'IN1204', 2025, 'regular'),
(18912216, 'IN1204', 2025, 'regular'),
(10846157, 'IN1214', 2025, 'regular'),
(18912216, 'IN1214', 2025, 'regular'),
(16385182, 'IN2101', 2025, 'regular'),
(24418577, 'IN2101', 2025, 'regular'),
(17354607, 'IN2101', 2025, 'regular'),
(13188691, 'IN2101', 2025, 'regular'),
(13991250, 'IN2101', 2025, 'regular'),
(15693145, 'IN2101', 2025, 'regular'),
(30088284, 'IN2101', 2025, 'regular'),
(16403903, 'IN2102', 2025, 'regular'),
(10846157, 'IN2102', 2025, 'regular'),
(24418577, 'IN2102', 2025, 'regular'),
(18103232, 'IN2102', 2025, 'regular'),
(14677589, 'IN2102', 2025, 'regular'),
(13188691, 'IN2102', 2025, 'regular'),
(9555514, 'IN2102', 2025, 'regular'),
(29517943, 'IN2104', 2025, 'regular'),
(7439117, 'IN2104', 2025, 'regular'),
(17354607, 'IN2104', 2025, 'regular'),
(9629702, 'IN2104', 2025, 'regular'),
(12701387, 'IN2104', 2025, 'regular'),
(11898335, 'IN2104', 2025, 'regular'),
(5260810, 'IN2104', 2025, 'regular'),
(16385182, 'IIN3101', 2025, 'regular'),
(25471240, 'IIN3101', 2025, 'regular'),
(15170003, 'IIN3101', 2025, 'regular'),
(7392496, 'IIN3101', 2025, 'regular'),
(13991971, 'IIN3101', 2025, 'regular'),
(18912216, 'IIN3101', 2025, 'regular'),
(7391773, 'IIN3101', 2025, 'regular'),
(16385182, 'IIN3102', 2025, 'regular'),
(25471240, 'IIN3102', 2025, 'regular'),
(15170003, 'IIN3102', 2025, 'regular'),
(7392496, 'IIN3102', 2025, 'regular'),
(13991971, 'IIN3102', 2025, 'regular'),
(18912216, 'IIN3102', 2025, 'regular'),
(7391773, 'IIN3102', 2025, 'regular'),
(9629702, 'IN1101', 2025, 'regular'),
(10848316, 'IN1101', 2025, 'regular'),
(16385182, 'IN1101', 2025, 'regular'),
(30395804, 'IN1101', 2025, 'regular'),
(15351688, 'IN1101', 2025, 'regular'),
(10844463, 'IN1101', 2025, 'regular'),
(9629702, 'IN1102', 2025, 'regular'),
(10848316, 'IN1102', 2025, 'regular'),
(16385182, 'IN1102', 2025, 'regular'),
(30395804, 'IN1102', 2025, 'regular'),
(15351688, 'IN1102', 2025, 'regular'),
(10844463, 'IN1102', 2025, 'regular'),
(9629702, 'IN1104', 2025, 'regular'),
(10848316, 'IN1104', 2025, 'regular'),
(16385182, 'IN1104', 2025, 'regular'),
(30395804, 'IN1104', 2025, 'regular'),
(15351688, 'IN1104', 2025, 'regular'),
(10844463, 'IN1104', 2025, 'regular'),
(29517943, 'IN2114', 2025, 'regular'),
(9540060, 'IN2114', 2025, 'regular'),
(18103232, 'IN2114', 2025, 'regular'),
(30088284, 'IN2114', 2025, 'regular'),
(14159756, 'IN2114', 2025, 'regular'),
(10775753, 'IN2114', 2025, 'regular'),
(5260810, 'IIN4401', 2025, 'regular'),
(16403903, 'IIN4401', 2025, 'regular'),
(16385182, 'IIN4401', 2025, 'regular'),
(13527711, 'IIN4401', 2025, 'regular'),
(7391773, 'IIN4401', 2025, 'regular'),
(18356682, 'IIN4401', 2025, 'regular'),
(5260810, 'IIN4403', 2025, 'regular'),
(16403903, 'IIN4403', 2025, 'regular'),
(16385182, 'IIN4403', 2025, 'regular'),
(13527711, 'IIN4403', 2025, 'regular'),
(7391773, 'IIN4403', 2025, 'regular'),
(18356682, 'IIN4403', 2025, 'regular'),
(5260810, 'IIN4404', 2025, 'regular'),
(16403903, 'IIN4404', 2025, 'regular'),
(16385182, 'IIN4404', 2025, 'regular'),
(13527711, 'IIN4404', 2025, 'regular'),
(7391773, 'IIN4404', 2025, 'regular'),
(18356682, 'IIN4404', 2025, 'regular'),
(14677589, 'IN1133', 2025, 'regular'),
(12701387, 'IN1133', 2025, 'regular'),
(24418577, 'IN1133', 2025, 'regular'),
(7423486, 'IN1133', 2025, 'regular'),
(13527711, 'IN1133', 2025, 'regular'),
(10846157, 'IN1133', 2025, 'regular'),
(7424546, 'IN1133', 2025, 'regular'),
(12701387, 'IN1403', 2025, 'regular'),
(7423486, 'IN1403', 2025, 'regular'),
(13527711, 'IN1403', 2025, 'regular'),
(17354607, 'IN1403', 2025, 'regular'),
(7423485, 'IN1403', 2025, 'regular'),
(15693145, 'IN1403', 2025, 'regular'),
(7424546, 'IN1403', 2025, 'regular');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `per_aprobados`
--

CREATE TABLE `per_aprobados` (
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `fase_numero` tinyint(1) NOT NULL,
  `uc_codigo` varchar(30) NOT NULL,
  `sec_codigo` varchar(30) NOT NULL,
  `per_cantidad` int(11) NOT NULL DEFAULT 0,
  `per_aprobados` int(11) NOT NULL DEFAULT 0,
  `pa_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_actividad`
--

CREATE TABLE `tbl_actividad` (
  `doc_cedula` int(11) NOT NULL,
  `act_academicas` int(11) NOT NULL DEFAULT 0,
  `act_creacion_intelectual` int(11) NOT NULL,
  `act_integracion_comunidad` int(11) NOT NULL,
  `act_gestion_academica` int(11) NOT NULL,
  `act_otras` int(11) NOT NULL,
  `act_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_anio`
--

CREATE TABLE `tbl_anio` (
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `ani_activo` tinyint(1) NOT NULL DEFAULT 0,
  `ani_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_anio`
--

INSERT INTO `tbl_anio` (`ani_anio`, `ani_tipo`, `ani_activo`, `ani_estado`) VALUES
(2025, 'regular', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_aprobados`
--

CREATE TABLE `tbl_aprobados` (
  `apro_estado` tinyint(1) NOT NULL,
  `apro_cantidad` int(11) NOT NULL DEFAULT 0,
  `uc_codigo` varchar(30) NOT NULL,
  `sec_codigo` varchar(30) NOT NULL,
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `fase_numero` tinyint(1) NOT NULL,
  `doc_cedula` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_area`
--

CREATE TABLE `tbl_area` (
  `area_nombre` varchar(30) NOT NULL,
  `area_estado` tinyint(1) NOT NULL DEFAULT 1,
  `area_descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_area`
--

INSERT INTO `tbl_area` (`area_nombre`, `area_estado`, `area_descripcion`) VALUES
('Actividades', 1, 'Área de actividades acreditables y desarrollo integral'),
('Arquitectura', 1, 'Área de arquitectura de computadores y sistemas'),
('Bases de Datos', 1, 'Área de diseño, modelado y administración de bases de datos'),
('Electivas', 1, 'Área de materias electivas complementarias'),
('Formación Crítica', 1, 'Área enfocada en el desarrollo del pensamiento crítico y la formación ciudadana'),
('Gestión', 1, 'Área de gestión de proyectos y administración informática'),
('Idiomas', 1, 'Área de idiomas extranjeros aplicados a la informática'),
('Ingeniería Software', 1, 'Área de ingeniería y desarrollo de software'),
('Introducción', 1, 'Área de introducción a la universidad y programas de formación'),
('Investigación', 1, 'Área de investigación de operaciones y métodos cuantitativos'),
('Matemáticas', 1, 'Área de ciencias exactas y matemáticas aplicadas a la informática'),
('Politica', 0, 'educativa'),
('Programación', 1, 'Área dedicada a la programación, algorítmica y desarrollo de software'),
('Proyectos', 1, 'Área dedicada a proyectos socio-tecnológicos y desarrollo de soluciones integrales'),
('Redes', 1, 'Área de redes de computadoras y comunicaciones'),
('Seguridad', 1, 'Área de seguridad informática y auditoría'),
('Sistemas Operativos', 1, 'Área de sistemas operativos y administración de sistemas'),
('Tecnologías', 1, 'Área de tecnologías de la información y comunicación');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_bloque_eliminado`
--

CREATE TABLE `tbl_bloque_eliminado` (
  `bloque_eliminado_id` int(11) NOT NULL,
  `sec_codigo` varchar(30) NOT NULL,
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `tur_horainicio` time NOT NULL,
  `tur_horafin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_bloque_personalizado`
--

CREATE TABLE `tbl_bloque_personalizado` (
  `bloque_id` int(11) NOT NULL,
  `sec_codigo` varchar(30) NOT NULL,
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `tur_horainicio` varchar(5) NOT NULL,
  `tur_horafin` varchar(5) NOT NULL,
  `bloque_sintetico` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_bloque_personalizado`
--

INSERT INTO `tbl_bloque_personalizado` (`bloque_id`, `sec_codigo`, `ani_anio`, `ani_tipo`, `tur_horainicio`, `tur_horafin`, `bloque_sintetico`) VALUES
(1, 'IN1133', 2025, 'regular', '07:20', '08:00', 0),
(2, 'IN1133', 2025, 'regular', '08:00', '08:40', 0),
(3, 'IN1133', 2025, 'regular', '08:40', '09:20', 0),
(4, 'IN1133', 2025, 'regular', '09:20', '10:00', 0),
(5, 'IN1133', 2025, 'regular', '10:00', '10:40', 0),
(6, 'IN1133', 2025, 'regular', '10:40', '11:20', 0),
(7, 'IN1133', 2025, 'regular', '11:20', '12:00', 0),
(8, 'IN1403', 2025, 'regular', '07:20', '08:00', 0),
(9, 'IN1403', 2025, 'regular', '08:00', '08:40', 0),
(10, 'IN1403', 2025, 'regular', '08:40', '09:20', 0),
(11, 'IN1403', 2025, 'regular', '09:20', '10:00', 0),
(12, 'IN1403', 2025, 'regular', '10:00', '10:40', 0),
(13, 'IN1403', 2025, 'regular', '10:40', '11:20', 0),
(14, 'IN1403', 2025, 'regular', '11:20', '12:00', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_categoria`
--

CREATE TABLE `tbl_categoria` (
  `cat_nombre` varchar(30) NOT NULL,
  `cat_estado` tinyint(1) NOT NULL DEFAULT 1,
  `cat_descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_categoria`
--

INSERT INTO `tbl_categoria` (`cat_nombre`, `cat_estado`, `cat_descripcion`) VALUES
('Agregado', 1, 'Agregado'),
('Asistente', 1, 'Asistente'),
('Asociado', 1, 'Asociado'),
('Instructor', 1, 'Instructor'),
('Pasante', 0, 'En prueba de trabajo'),
('Titular', 1, 'Titular');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_coordinacion`
--

CREATE TABLE `tbl_coordinacion` (
  `cor_nombre` varchar(30) NOT NULL,
  `cor_estado` tinyint(1) NOT NULL DEFAULT 1,
  `coor_hora_descarga` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_coordinacion`
--

INSERT INTO `tbl_coordinacion` (`cor_nombre`, `cor_estado`, `coor_hora_descarga`) VALUES
('Académica', 1, 0),
('Actividades Acreditables I', 1, 0),
('Actividades Acreditables II', 1, 0),
('Administración de Base de Dato', 1, 0),
('Algorítmica y Programación', 1, 0),
('Arquitectura del Computador', 1, 0),
('Auditoria de Sistemas', 1, 0),
('Base de Datos', 1, 0),
('Centro de Estudio Investigació', 1, 0),
('Currículo', 1, 0),
('Deporte', 1, 0),
('Doctorados', 1, 0),
('Educación Municipalizada', 1, 0),
('Eje Epistemológico', 1, 0),
('Eje Estético Lúdico', 1, 0),
('Eje Etico Político', 1, 0),
('Electiva 1', 1, 0),
('Electiva II', 1, 0),
('Electiva III', 1, 0),
('Electiva IV', 1, 0),
('EMTICL', 1, 0),
('Formación Crítica TI, TII, TII', 1, 0),
('Gestión de Proyectos', 1, 0),
('Gestión TIC', 1, 0),
('Idiomas', 1, 0),
('Idiomas I', 1, 0),
('Idiomas II', 1, 0),
('Ingeniería del Software', 1, 0),
('Ingeniería del Software II', 1, 0),
('Ingenierías', 1, 0),
('Investigación', 1, 0),
('Investigación de Operaciones', 1, 0),
('Laboratorios', 1, 0),
('Matemática Aplicada', 1, 0),
('Matemática I, II, III, IV', 1, 0),
('Modelado de Base de Datos', 1, 0),
('Nocturna y Fin de Semana', 1, 0),
('PNFA-PNFI', 1, 0),
('Preparaduría', 1, 0),
('Programación', 1, 0),
('Proyecto Sociotecnológico I', 1, 0),
('Proyecto Sociotecnológico II', 1, 0),
('Proyecto Sociotecnológico III', 1, 0),
('Proyecto Sociotecnológico IV', 1, 0),
('Redes Avanzadas', 1, 0),
('Redes del Computador', 1, 0),
('Seguridad', 1, 0),
('Sistemas Operativos', 1, 0),
('Tutorías Académicas', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_docente`
--

CREATE TABLE `tbl_docente` (
  `doc_cedula` int(11) NOT NULL,
  `cat_nombre` varchar(30) NOT NULL,
  `doc_prefijo` char(1) NOT NULL DEFAULT '',
  `doc_nombre` varchar(30) NOT NULL DEFAULT '',
  `doc_apellido` varchar(30) NOT NULL DEFAULT '',
  `doc_correo` varchar(30) NOT NULL DEFAULT '',
  `doc_dedicacion` varchar(30) NOT NULL,
  `doc_condicion` varchar(50) NOT NULL,
  `doc_estado` tinyint(1) NOT NULL DEFAULT 1,
  `doc_observacion` varchar(250) DEFAULT NULL,
  `doc_ingreso` date NOT NULL,
  `doc_anio_concurso` date DEFAULT NULL,
  `doc_tipo_concurso` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_docente`
--

INSERT INTO `tbl_docente` (`doc_cedula`, `cat_nombre`, `doc_prefijo`, `doc_nombre`, `doc_apellido`, `doc_correo`, `doc_dedicacion`, `doc_condicion`, `doc_estado`, `doc_observacion`, `doc_ingreso`, `doc_anio_concurso`, `doc_tipo_concurso`) VALUES
(3759671, 'Instructor', 'V', 'Norma', 'Barreto', 'prueba@pnfi.edu.ve', 'Exclusiva', 'Ordinario', 1, '- Integra Comisión Organización docente - Integrante Comisión Currículo - Integra Com. Seguimiento Egresado', '1998-03-10', '2003-06-01', 'Oposicion'),
(4374529, 'Instructor', 'V', 'Jackob', 'Jiménez', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '', '2000-03-25', '2000-03-01', 'Credenciales'),
(5260810, 'Instructor', 'V', 'José', 'Tillero', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2016-07-19', NULL, NULL),
(6269299, 'Instructor', 'V', 'Francys', 'Barreto', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2006-01-01', NULL, NULL),
(7391773, 'Instructor', 'V', 'Edecio', 'Freitez', 'edeciofreitez@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(7392496, 'Instructor', 'V', 'Pura', 'Castillo', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2009-10-05', '2019-11-01', 'Oposición'),
(7404027, 'Instructor', 'V', 'Oswaldo', 'Aparicio', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, 'Solicitará Permiso por viaje', '2004-01-19', '2003-04-01', 'Oposicion'),
(7415067, 'Instructor', 'V', 'Lisbeth', 'Oropeza', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, 'Docente con descarga académico-administrativa del 50% por Estudios de Doctorado', '2004-01-19', '2003-06-01', 'Oposicion'),
(7423485, 'Instructor', 'V', 'Ingrid', 'Figueroa', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '- Docente Enlace Comisión Seguimiento al Egresado - Vicerrectorado Académico - Integra Comisión Grupo de Trabajo de Estudiantes - Adscrito al PNFI desde 15-01-2020', '2008-01-18', '2007-01-01', 'Credenciales'),
(7423486, 'Instructor', 'V', 'Lérida', 'Figueroa', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2022-10-25', NULL, NULL),
(7424546, 'Instructor', 'V', 'Paola', 'Ruggero', 'paolarugsg@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(7439117, 'Instructor', 'V', 'Sullín', 'Santaella', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '- Descarga Completa por (Dpto EMTICL) - Docente Diplomado EMTICL - Facilita Cursos de Posgrado - - Desarrolladora Aulas Virtuales en DEA', '2006-01-09', '2013-04-01', 'Oposicion'),
(9118178, 'Instructor', 'V', 'Iris', 'Daza', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '', '2010-07-26', '2010-06-01', 'Credenciales'),
(9540060, 'Instructor', 'V', 'Maribel', 'Durán', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2019-09-24', NULL, NULL),
(9541953, 'Instructor', 'V', 'Nelson', 'Montilla', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado', 1, 'Carga administrativa contratado por tiempo determinado. Cumplira sus horas en la unidad de sistemas de la uptaeb. 25/10/2021 Hasta el -.......', '2020-01-27', NULL, NULL),
(9555514, 'Instructor', 'V', 'Lisbeth', 'Flores', 'florbeth08@yahoo.es', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(9602562, 'Instructor', 'V', 'Samary', 'Páez', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '- Solicito Permiso x Contrato Colectivo para elaboración de trabajo de Tesis Doctoral EN ESPERA DE DEFINICION DE ESTADO', '2006-05-28', '2013-04-01', 'Oposicion'),
(9619518, 'Instructor', 'V', 'Sonia', 'Córdoba', 'prueba@pnfi.edu.ve', 'Exclusiva', 'Ordinario', 1, '- Integra la Comisión de EMTICL del PNFI - Colabora con la Educación Municipalizada PNFI (Misión Sucre) Directora de PNFI', '2003-06-16', '2003-06-01', 'Oposicion'),
(9627295, 'Instructor', 'V', 'Douglas', 'Nelo', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2016-06-20', '2019-11-01', 'Oposicion'),
(9629702, 'Instructor', 'V', 'Ruben', 'Godoy', 'prueba@pnfi.edu.ve', 'Exclusiva', 'Suplente', 1, '', '2022-10-25', NULL, NULL),
(10723015, 'Instructor', 'V', 'Sol', 'Hernández', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '- Docente Enlace Revista Digital del PNFI - Integrante de Comisión de Trayecto I. - Jefe de Despacho de la UPTAEB - Subcoordinadora Línea Institucional Gestión TIC ( Pasa a Agregado a partir 31-10-18) - Representa al PNFI ante Sistema Nacional de For', '2013-01-15', '2014-06-01', 'Credenciales'),
(10775753, 'Instructor', 'V', 'Wilmar', 'Marrufo', 'prueba@pnfi.edu.ve', 'Exclusiva', 'Ordinario', 1, '', '2008-01-17', '2013-04-01', 'Oposicion'),
(10778236, 'Instructor', 'V', 'Alexis', 'Dorante', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2016-06-20', '2019-11-01', 'Oposicion'),
(10844463, 'Instructor', 'V', 'Edith', 'Urdaneta', 'edyiurav@hotmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(10846157, 'Instructor', 'V', 'Juan', 'Jiménez', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2016-09-23', '2019-11-01', 'Oposicion'),
(10847351, 'Instructor', 'V', 'Leany', 'González', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '1998-03-10', '2003-04-01', 'Oposicion'),
(10848316, 'Instructor', 'V', 'Enrique', 'Ramos', 'uptaebenrique@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(10956121, 'Instructor', 'V', 'Lissette', 'Torrealba', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '- de la UPTAEB - del PNFI -Integra de PNFI.', '2013-06-15', '2014-06-01', 'Credenciales'),
(11898335, 'Instructor', 'V', 'Eduardo', 'Venegas', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2015-02-04', '2019-11-01', 'Oposicion'),
(12045627, 'Instructor', 'V', 'Pedro', 'Castro', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2016-07-19', NULL, NULL),
(12701387, 'Instructor', 'V', 'Fidel', 'Aguilar', 'fidelaguilar3000@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(12849928, 'Instructor', 'V', 'Darwin', 'Velásquez', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '', '2013-01-15', '2014-06-01', 'Credenciales'),
(13188691, 'Instructor', 'V', 'Ellery', 'López', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '- Integra Comisión del PNFI', '2017-01-30', '2019-11-01', 'Oposicion'),
(13527711, 'Instructor', 'V', 'Marling', 'Brito', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2017-10-02', '2019-11-01', 'Oposicion'),
(13695847, 'Instructor', 'V', 'Angelica', 'Rojas', 'angelicarojas@iujo.edu.ve', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(13991250, 'Instructor', 'V', 'Ligia', 'Durán', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2017-02-06', '2019-11-01', 'Oposicion'),
(13991971, 'Instructor', 'V', 'Angelismar', 'Terán', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2016-01-01', NULL, NULL),
(14091124, 'Instructor', 'V', 'Jehamar', 'Lovera', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '', '2013-01-15', '2014-06-01', 'Credenciales'),
(14159756, 'Instructor', 'V', 'Aracelys', 'Terán', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '', '2012-06-15', '2014-06-01', 'Credenciales'),
(14292469, 'Instructor', 'V', 'María', 'Mendoza', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2016-06-20', '2019-11-01', 'Oposicion'),
(14677589, 'Instructor', 'V', 'Miguel', 'Rodríguez', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '- Integrante de la Comisión de Economía Comunal', '2015-06-29', '2019-11-01', 'Oposicion'),
(15170003, 'Instructor', 'V', 'Aida', 'Sivira', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2016-07-06', '2019-11-01', 'Oposición'),
(15351688, 'Instructor', 'V', 'Francis', 'Rodriguez', 'francisyrm2601@hotmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(15693145, 'Instructor', 'V', 'Indira', 'González', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2019-10-25', NULL, NULL),
(16385182, 'Instructor', 'V', 'Hermes', 'Gordillo', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '', '2013-03-02', '2014-06-01', 'Credenciales'),
(16403903, 'Instructor', 'V', 'Orlando', 'Guerra', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2012-01-04', '2019-11-01', 'Oposicion'),
(17354607, 'Instructor', 'V', 'María', 'Linares', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2012-07-12', '2019-11-01', 'Oposicion'),
(18103232, 'Instructor', 'V', 'Carlos', 'Moreno', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, 'Docente del PNF Deporte, atiende TC horas en PNFI', '2017-04-20', '2019-11-01', 'Oposicion'),
(18356682, 'Instructor', 'V', 'Judith', 'Gomez', 'Rebecagomez1808@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(18912216, 'Instructor', 'V', 'Cesar', 'Perez', 'cesarupf72@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(20351422, 'Instructor', 'V', 'Kenlimar', 'Alvarado', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(23316126, 'Instructor', 'V', 'Nangelys', 'Oviedo', 'nangelisg@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(24418577, 'Instructor', 'V', 'Douglas', 'Ramos', 'douglasramos0210@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(25471240, 'Instructor', 'V', 'José', 'Sequera', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2021-10-25', NULL, NULL),
(26197135, 'Instructor', 'V', 'Maria', 'Diaz', 'mjcazorla1997@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(29517943, 'Instructor', 'V', 'Sabrina', 'Colmenarez', 'sabrinacolmenarez16@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(29880797, 'Instructor', 'V', 'Davianys', 'Guerrero', 'davianystra@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(30088284, 'Instructor', 'V', 'Jose', 'Escalona', 'joseescalona1505@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
(30395804, 'Instructor', 'V', 'Jhoanly', 'Hernandez', 'duranjhoa16.pnfi@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_eje`
--

CREATE TABLE `tbl_eje` (
  `eje_nombre` varchar(30) NOT NULL,
  `eje_descripcion` varchar(255) NOT NULL,
  `eje_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_eje`
--

INSERT INTO `tbl_eje` (`eje_nombre`, `eje_descripcion`, `eje_estado`) VALUES
('Epistemológico', 'Eje integrador centrado en la construcción del conocimiento científico y tecnológico', 1),
('Estético Lúdico', 'Eje integrador dedicado a actividades culturales, deportivas y de desarrollo personal', 1),
('Ético Político-Socio Ambiental', 'Eje integrador que combina aspectos éticos, políticos y ambientales', 1),
('Ético-Político', 'Eje integrador enfocado en la formación ciudadana, valores éticos y participación política', 1),
('Politico ', 'Eje politico', 0),
('Trabajo-Productivo', 'Eje integrador orientado a la vinculación con el sector productivo y el desarrollo de proyectos', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_espacio`
--

CREATE TABLE `tbl_espacio` (
  `esp_numero` varchar(30) NOT NULL,
  `esp_tipo` varchar(30) NOT NULL,
  `esp_edificio` varchar(30) NOT NULL,
  `esp_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_espacio`
--

INSERT INTO `tbl_espacio` (`esp_numero`, `esp_tipo`, `esp_edificio`, `esp_estado`) VALUES
('1', 'Aula', 'Giraluna', 1),
('1', 'Aula', 'Hilandera', 1),
('1', 'Aula', 'Orinoco', 1),
('1', 'Aula', 'Rio 7 Estrellas', 1),
('10', 'Aula', 'Giraluna', 1),
('10', 'Aula', 'Hilandera', 1),
('10', 'Aula', 'Rio 7 Estrellas', 1),
('11', 'Aula', 'Giraluna', 1),
('11', 'Aula', 'Hilandera', 1),
('11', 'Aula', 'Rio 7 Estrellas', 1),
('12', 'Aula', 'Giraluna', 1),
('12', 'Aula', 'Hilandera', 1),
('12', 'Aula', 'Rio 7 Estrellas', 1),
('13', 'Aula', 'Giraluna', 1),
('13', 'Aula', 'Hilandera', 1),
('13', 'Aula', 'Rio 7 Estrellas', 1),
('14', 'Aula', 'Giraluna', 1),
('14', 'Aula', 'Hilandera', 1),
('14', 'Aula', 'Rio 7 Estrellas', 1),
('15', 'Aula', 'Giraluna', 1),
('15', 'Aula', 'Hilandera', 1),
('15', 'Aula', 'Rio 7 Estrellas', 1),
('16', 'Aula', 'Giraluna', 1),
('16', 'Aula', 'Rio 7 Estrellas', 1),
('17', 'Aula', 'Giraluna', 1),
('18', 'Aula', 'Giraluna', 1),
('19', 'Aula', 'Giraluna', 1),
('2', 'Aula', 'Hilandera', 1),
('2', 'Aula', 'Orinoco', 1),
('2', 'Aula', 'Rio 7 Estrellas', 1),
('20', 'Aula', 'Giraluna', 1),
('21', 'Aula', 'Giraluna', 1),
('22', 'Aula', 'Giraluna', 1),
('23', 'Aula', 'Giraluna', 1),
('24', 'Aula', 'Giraluna', 1),
('25', 'Aula', 'Giraluna', 1),
('26', 'Aula', 'Giraluna', 1),
('27', 'Aula', 'Giraluna', 1),
('3', 'Aula', 'Hilandera', 1),
('3', 'Aula', 'Orinoco', 1),
('3', 'Aula', 'Rio 7 Estrellas', 1),
('3', 'Laboratorio', 'Giraluna', 1),
('4', 'Aula', 'Hilandera', 1),
('4', 'Aula', 'Orinoco', 1),
('4', 'Aula', 'Rio 7 Estrellas', 1),
('4', 'Laboratorio', 'Rio 7 Estrellas', 1),
('5', 'Aula', 'Hilandera', 1),
('5', 'Aula', 'Rio 7 Estrellas', 1),
('5', 'Laboratorio', 'Giraluna', 1),
('6', 'Aula', 'Hilandera', 1),
('6', 'Aula', 'Orinoco', 1),
('6', 'Aula', 'Rio 7 Estrellas', 1),
('6', 'Laboratorio', 'Hilandera', 1),
('7', 'Aula', 'Hilandera', 1),
('7', 'Aula', 'Orinoco', 1),
('7', 'Aula', 'Rio 7 Estrellas', 1),
('8', 'Aula', 'Hilandera', 1),
('8', 'Aula', 'Orinoco', 1),
('9', 'Aula', 'Giraluna', 1),
('9', 'Aula', 'Hilandera', 1),
('9', 'Aula', 'Orinoco', 1),
('9', 'Aula', 'Rio 7 Estrellas', 1),
('Hardware', 'Laboratorio', 'Hilandera', 1),
('Software', 'Laboratorio', 'Hilandera', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_fase`
--

CREATE TABLE `tbl_fase` (
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `fase_numero` tinyint(1) NOT NULL,
  `fase_apertura` date NOT NULL,
  `fase_cierre` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_fase`
--

INSERT INTO `tbl_fase` (`ani_anio`, `ani_tipo`, `fase_numero`, `fase_apertura`, `fase_cierre`) VALUES
(2025, 'regular', 1, '2025-02-17', '2025-06-20'),
(2025, 'regular', 2, '2025-06-23', '2025-11-27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_horario`
--

CREATE TABLE `tbl_horario` (
  `sec_codigo` varchar(30) NOT NULL,
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `tur_nombre` varchar(30) NOT NULL,
  `hor_estado` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_horario`
--

INSERT INTO `tbl_horario` (`sec_codigo`, `ani_anio`, `ani_tipo`, `tur_nombre`, `hor_estado`) VALUES
('IN0403', 2025, 'regular', 'Mañana', 1),
('IN0423', 2025, 'regular', 'Mañana', 1),
('IN1103', 2025, 'regular', 'Mañana', 1),
('IN1123', 2025, 'regular', 'Mañana', 1),
('IN1203', 2025, 'regular', 'Tarde', 1),
('IN1143', 2025, 'regular', 'Mañana', 1),
('IN1213', 2025, 'regular', 'Tarde', 1),
('IN2103', 2025, 'regular', 'Mañana', 1),
('IN2113', 2025, 'regular', 'Mañana', 1),
('IN2403', 2025, 'regular', 'Mañana', 1),
('IIN3103', 2025, 'regular', 'Mañana', 1),
('IIN3104', 2025, 'regular', 'Mañana', 1),
('IIN3113', 2025, 'regular', 'Mañana', 1),
('IN1202', 2025, 'regular', 'Tarde', 1),
('IN1204', 2025, 'regular', 'Tarde', 1),
('IN1214', 2025, 'regular', 'Tarde', 1),
('IN2101', 2025, 'regular', 'Mañana', 1),
('IN2102', 2025, 'regular', 'Mañana', 1),
('IN2104', 2025, 'regular', 'Mañana', 1),
('IIN3101', 2025, 'regular', 'Mañana', 1),
('IIN3102', 2025, 'regular', 'Mañana', 1),
('IN1101', 2025, 'regular', 'Mañana', 1),
('IN1102', 2025, 'regular', 'Mañana', 1),
('IN1104', 2025, 'regular', 'Mañana', 1),
('IN0413', 2025, 'regular', 'Mañana', 1),
('IN0103', 2025, 'regular', 'Mañana', 1),
('IN0113', 2025, 'regular', 'Mañana', 1),
('IN0123', 2025, 'regular', 'Mañana', 1),
('IN1113', 2025, 'regular', 'Mañana', 1),
('IN2123', 2025, 'regular', 'Mañana', 1),
('IN2133', 2025, 'regular', 'Mañana', 1),
('IN2114', 2025, 'regular', 'Mañana', 1),
('IIN4402', 2025, 'regular', 'Mañana', 1),
('IIN4401', 2025, 'regular', 'Mañana', 1),
('IIN4403', 2025, 'regular', 'Mañana', 1),
('IIN4404', 2025, 'regular', 'Mañana', 1),
('IN1133', 2025, 'regular', 'Mañana', 1),
('IN1403', 2025, 'regular', 'Mañana', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_malla`
--

CREATE TABLE `tbl_malla` (
  `mal_codigo` varchar(30) NOT NULL,
  `mal_nombre` varchar(30) NOT NULL DEFAULT '',
  `mal_descripcion` varchar(255) NOT NULL,
  `mal_cohorte` tinyint(1) NOT NULL DEFAULT 0,
  `mal_activa` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_malla`
--

INSERT INTO `tbl_malla` (`mal_codigo`, `mal_nombre`, `mal_descripcion`, `mal_cohorte`, `mal_activa`) VALUES
('05033', 'PLAN DE ESTUDIO COHORTE III', 'Malla 2025', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_per`
--

CREATE TABLE `tbl_per` (
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `per_apertura` date NOT NULL,
  `per_fase` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_per`
--

INSERT INTO `tbl_per` (`ani_anio`, `ani_tipo`, `per_apertura`, `per_fase`) VALUES
(2025, 'regular', '2025-06-23', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_prosecusion`
--

CREATE TABLE `tbl_prosecusion` (
  `sec_origen` varchar(30) NOT NULL,
  `ani_origen` int(11) NOT NULL,
  `ani_tipo_origen` varchar(10) NOT NULL,
  `sec_promocion` varchar(30) NOT NULL,
  `ani_destino` int(11) NOT NULL,
  `ani_tipo_destino` varchar(10) NOT NULL,
  `pro_cantidad` int(11) NOT NULL DEFAULT 0,
  `pro_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_seccion`
--

CREATE TABLE `tbl_seccion` (
  `sec_codigo` varchar(30) NOT NULL,
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `sec_cantidad` int(11) NOT NULL DEFAULT 0,
  `sec_estado` tinyint(1) NOT NULL,
  `grupo_union_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_seccion`
--

INSERT INTO `tbl_seccion` (`sec_codigo`, `ani_anio`, `ani_tipo`, `sec_cantidad`, `sec_estado`, `grupo_union_id`) VALUES
('IIN3101', 2025, 'regular', 0, 1, NULL),
('IIN3102', 2025, 'regular', 0, 1, NULL),
('IIN3103', 2025, 'regular', 0, 1, NULL),
('IIN3104', 2025, 'regular', 0, 1, NULL),
('IIN3113', 2025, 'regular', 0, 1, NULL),
('IIN4103', 2025, 'regular', 0, 1, NULL),
('IIN4401', 2025, 'regular', 0, 1, 'grupo_6900d879070a11.92867672'),
('IIN4402', 2025, 'regular', 0, 1, 'grupo_6900d879070a11.92867672'),
('IIN4403', 2025, 'regular', 0, 1, 'grupo_6900d879070a11.92867672'),
('IIN4404', 2025, 'regular', 0, 1, 'grupo_6900d879070a11.92867672'),
('IN0103', 2025, 'regular', 0, 1, NULL),
('IN0106', 2025, 'regular', 0, 1, NULL),
('IN0113', 2025, 'regular', 0, 1, NULL),
('IN0123', 2025, 'regular', 0, 1, 'grupo_68f1e6eff04e21.73853247'),
('IN0403', 2025, 'regular', 0, 1, 'grupo_68f2b5460a8c62.29071220'),
('IN0413', 2025, 'regular', 0, 1, 'grupo_68f2b5460a8c62.29071220'),
('IN0423', 2025, 'regular', 0, 1, NULL),
('IN1101', 2025, 'regular', 0, 1, 'grupo_68dcbe74e9fbe0.04656473'),
('IN1102', 2025, 'regular', 0, 1, 'grupo_68dcbe74e9fbe0.04656473'),
('IN1103', 2025, 'regular', 0, 1, 'grupo_68f1eb5b82b6f9.74113922'),
('IN1104', 2025, 'regular', 0, 1, 'grupo_68dcbe74e9fbe0.04656473'),
('IN1113', 2025, 'regular', 0, 1, 'grupo_68f1eb5b82b6f9.74113922'),
('IN1123', 2025, 'regular', 0, 1, NULL),
('IN1133', 2025, 'regular', 0, 1, NULL),
('IN1143', 2025, 'regular', 0, 1, 'grupo_68dcbe74e9fbe0.04656473'),
('IN1202', 2025, 'regular', 0, 1, NULL),
('IN1203', 2025, 'regular', 0, 1, NULL),
('IN1204', 2025, 'regular', 0, 1, NULL),
('IN1213', 2025, 'regular', 0, 1, NULL),
('IN1214', 2025, 'regular', 0, 1, NULL),
('IN1403', 2025, 'regular', 0, 1, NULL),
('IN2101', 2025, 'regular', 0, 1, NULL),
('IN2102', 2025, 'regular', 0, 1, NULL),
('IN2103', 2025, 'regular', 0, 1, NULL),
('IN2104', 2025, 'regular', 0, 1, NULL),
('IN2113', 2025, 'regular', 0, 1, 'grupo_68f2b8b5dcca70.68942892'),
('IN2114', 2025, 'regular', 0, 1, 'grupo_6900d74d165227.86474264'),
('IN2123', 2025, 'regular', 0, 1, 'grupo_68f2b8b5dcca70.68942892'),
('IN2133', 2025, 'regular', 0, 1, 'grupo_6900d74d165227.86474264'),
('IN2403', 2025, 'regular', 0, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_titulo`
--

CREATE TABLE `tbl_titulo` (
  `tit_prefijo` varchar(30) NOT NULL,
  `tit_nombre` varchar(80) NOT NULL,
  `tit_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_titulo`
--

INSERT INTO `tbl_titulo` (`tit_prefijo`, `tit_nombre`, `tit_estado`) VALUES
('Dr.', 'Cs Educación ', 1),
('Esp.', 'Experto Elearning', 1),
('Esp.', 'Organización Sit. Información', 1),
('Esp.', 'Sist. Información', 1),
('Esp.', 'Telematica Informática', 1),
('Ing.', 'Computación', 1),
('Ing.', 'Informática', 1),
('Ing.', 'rijals', 0),
('Ing.', 'rijas', 0),
('Ing.', 'Sistemas', 1),
('Lic.', 'Administración Mención Informática', 1),
('Lic.', 'Cs Información', 1),
('Lic.', 'Cs Matemáticas', 1),
('Lic.', 'Cultura y Física y deporte', 1),
('Lic.', 'Educación Mención Matemática', 1),
('Lic.', 'Matemática', 1),
('Msc.', 'Ciencias de la Computación Mención Inteligencia Ar', 1),
('Msc.', 'Cs Gerencia Educaccional', 1),
('Msc.', 'Cs Información', 1),
('Msc.', 'Cs Orientación', 1),
('Msc.', 'Educación Superior', 1),
('Msc.', 'Educación Superior Mención Docencia Universitaria', 1),
('Msc.', 'Educación Superior Mención Gerencia Educacional', 1),
('Msc.', 'Gerenc. Emp.', 1),
('Msc.', 'Ing. Industrial', 1),
('Msc.', 'Matemática Pura', 1),
('Prof.', 'Educación Física y deporte', 1),
('Prof.', 'Geografia e Historia', 1),
('Prof.', 'Inglés', 1),
('TSU.', 'Analista de Sistemas', 1),
('TSU.', 'Politicaa', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_turno`
--

CREATE TABLE `tbl_turno` (
  `tur_nombre` varchar(30) NOT NULL,
  `tur_horaInicio` time NOT NULL,
  `tur_horaFin` time NOT NULL,
  `tur_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_turno`
--

INSERT INTO `tbl_turno` (`tur_nombre`, `tur_horaInicio`, `tur_horaFin`, `tur_estado`) VALUES
('Mañana', '07:20:00', '12:00:00', 1),
('Noche', '17:00:00', '21:00:00', 1),
('Tarde', '13:00:00', '17:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_uc`
--

CREATE TABLE `tbl_uc` (
  `uc_codigo` varchar(30) NOT NULL,
  `eje_nombre` varchar(30) NOT NULL,
  `area_nombre` varchar(30) NOT NULL,
  `uc_nombre` varchar(100) NOT NULL,
  `uc_creditos` int(11) NOT NULL DEFAULT 0,
  `uc_periodo` varchar(10) NOT NULL,
  `uc_estado` tinyint(1) NOT NULL,
  `uc_trayecto` varchar(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_uc`
--

INSERT INTO `tbl_uc` (`uc_codigo`, `eje_nombre`, `area_nombre`, `uc_nombre`, `uc_creditos`, `uc_periodo`, `uc_estado`, `uc_trayecto`) VALUES
('PIABD090403', 'Epistemológico', 'Bases de Datos', 'Administración de bases de datos', 3, 'Fase I', 1, '4'),
('PIACA090103', 'Estético Lúdico', 'Actividades', 'Actividades Acreditable I', 3, 'Anual', 1, '1'),
('PIACA090203', 'Estético Lúdico', 'Actividades', 'Actividades Acreditables II', 3, 'Anual', 1, '2'),
('PIACA090303', 'Estético Lúdico', 'Actividades', 'Actividades Acreditables III', 3, 'Anual', 1, '3'),
('PIACA090403', 'Estético Lúdico', 'Actividades', 'Actividades Acreditables IV', 3, 'Anual', 1, '4'),
('PIALP306112', 'Epistemológico', 'Programación', 'Algorítmica y Programación', 12, 'Anual', 1, '1'),
('PIARC234109', 'Epistemológico', 'Arquitectura', 'Arquitectura del Computador', 9, 'Anual', 1, '1'),
('PIAUI120404', 'Epistemológico', 'Seguridad', 'Auditoria de sistemas', 4, 'Fase II', 1, '4'),
('PIBAD090203', 'Epistemológico', 'Bases de Datos', 'Base de Datos', 3, 'Fase I', 1, '2'),
('PIELE072103', 'Epistemológico', 'Electivas', 'Electiva I', 3, 'Fase II', 1, '1'),
('PIELE072203', 'Epistemológico', 'Electivas', 'Electiva II', 3, 'Fase II', 1, '2'),
('PIELE072403', 'Epistemológico', 'Electivas', 'Electiva IV', 3, 'Fase II', 1, '4'),
('PIELE078303', 'Epistemológico', 'Electivas', 'Electiva III', 3, 'Fase II', 1, '3'),
('PIFOC090103', 'Ético-Político', 'Formación Crítica', 'Formación Crítica I', 3, 'Anual', 1, '1'),
('PIFOC090203', 'Ético-Político', 'Formación Crítica', 'Formación Crítica II', 3, 'Anual', 1, '2'),
('PIFOC090303', 'Ético Político-Socio Ambiental', 'Formación Crítica', 'Formación Crítica III', 3, 'Anual', 1, '3'),
('PIFOC090403', 'Ético Político-Socio Ambiental', 'Formación Crítica', 'Formación Crítica IV', 3, 'Anual', 1, '4'),
('PIGPI120404', 'Epistemológico', 'Gestión', 'Gestión de proyecto Informático', 4, 'Fase I', 1, '4'),
('PIIDI090103', 'Epistemológico', 'Idiomas', 'Idiomas I', 3, 'Fase I', 1, '1'),
('PIIDI090403', 'Epistemológico', 'Idiomas', 'Idiomas II', 3, 'Anual', 1, '4'),
('PIINO078303', 'Epistemológico', 'Investigación', 'Investigación de operaciones', 3, 'Fase II', 1, '3'),
('PIINS090203', 'Epistemológico', 'Ingeniería Software', 'Ingeniería del Software I', 3, 'Fase I', 1, '2'),
('PIINS252309', 'Epistemológico', 'Ingeniería Software', 'Ingeniería de Software II', 9, 'Anual', 1, '3'),
('PIIUP052002', 'Ético-Político', 'Introducción', 'Introducción a la universidad y a los programas nacionales de formacion', 2, 'Anual', 1, '0'),
('PIMAT090003', 'Epistemológico', 'Matemáticas', 'Matemática', 3, 'Anual', 1, '0'),
('PIMAT156206', 'Epistemológico', 'Matemáticas', 'Matemática II', 6, 'Anual', 1, '2'),
('PIMAT156306', 'Epistemológico', 'Matemáticas', 'Matemática Aplicada', 6, 'Anual', 1, '3'),
('PIMAT234109', 'Epistemológico', 'Matemáticas', 'Matemática I', 9, 'Anual', 1, '1'),
('PIMOB078303', 'Epistemológico', 'Bases de Datos', 'Modelado de bases de datos', 3, 'Fase I', 1, '3'),
('PIPNN078003', 'Ético-Político', 'Proyectos', 'Proyecto nacional y nueva ciudadanía', 3, 'Anual', 1, '0'),
('PIPRO306212', 'Epistemológico', 'Programación', 'Programación II', 12, 'Anual', 1, '2'),
('PIPST234109', 'Trabajo-Productivo', 'Proyectos', 'Proyecto Socio Tecnológico I', 9, 'Anual', 1, '1'),
('PIPST234209', 'Trabajo-Productivo', 'Proyectos', 'Proyecto Socio Tecnológico II', 9, 'Anual', 1, '2'),
('PIPST234309', 'Trabajo-Productivo', 'Proyectos', 'Proyecto Socio Tecnológico III', 9, 'Anual', 1, '3'),
('PIPST360412', 'Trabajo-Productivo', 'Proyectos', 'Proyecto Socio Tecnológico IV', 12, 'Anual', 1, '4'),
('PIREA084403', 'Epistemológico', 'Redes', 'Redes Avanzadas', 3, 'Fase II', 1, '4'),
('PIREC156206', 'Epistemológico', 'Redes', 'Redes de Computadoras', 6, 'Anual', 1, '2'),
('PISEI120404', 'Epistemológico', 'Seguridad', 'Seguridad Informática', 4, 'Fase I', 1, '4'),
('PISIO078303', 'Epistemológico', 'Sistemas Operativos', 'Sistemas Operativos', 3, 'Fase I', 1, '3'),
('PITIC032002', 'Epistemológico', 'Tecnologías', 'Tecnologías de la información y comunicación', 2, 'Anual', 1, '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `titulo_docente`
--

CREATE TABLE `titulo_docente` (
  `doc_cedula` int(11) NOT NULL,
  `tit_prefijo` varchar(30) NOT NULL,
  `tit_nombre` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `titulo_docente`
--

INSERT INTO `titulo_docente` (`doc_cedula`, `tit_prefijo`, `tit_nombre`) VALUES
(7404027, 'Ing.', 'Informática'),
(7404027, 'Msc.', 'Cs Información'),
(6269299, 'Ing.', 'Informática'),
(6269299, 'Msc.', 'Educación Superior'),
(3759671, 'Ing.', 'Informática'),
(3759671, 'Msc.', 'Gerenc. Emp.'),
(3759671, 'Dr.', 'Cs Educación'),
(13527711, 'Prof.', 'Geografia e Historia'),
(12045627, 'Ing.', 'Informática'),
(9619518, 'Ing.', 'Informática'),
(9619518, 'Msc.', 'Ciencias de la Computación Mención Inteligencia Ar'),
(9118178, 'Ing.', 'Informática'),
(9118178, 'Msc.', 'Ing. Industrial'),
(10778236, 'Ing.', 'Informática'),
(13991250, 'Ing.', 'Sistemas'),
(9540060, 'Ing.', 'Informática'),
(9540060, 'Msc.', 'Cs Gerencia Educaccional'),
(7423485, 'Ing.', 'Informática'),
(7423485, 'Msc.', 'Educación Superior'),
(7423485, 'Ing.', 'Informática'),
(9629702, 'Ing.', 'Informática'),
(15693145, 'Ing.', 'Informática'),
(10847351, 'Ing.', 'Informática'),
(10847351, 'Msc.', 'Educación Superior Mención Gerencia Educacional'),
(16385182, 'Lic.', 'Cultura y Física y deporte'),
(16385182, 'Msc.', 'Cs Orientación'),
(16403903, 'Ing.', 'Sistemas'),
(10723015, 'Ing.', 'Informática'),
(10723015, 'Msc.', 'Educación Superior Mención Docencia Universitaria'),
(4374529, 'Prof.', 'Inglés'),
(10846157, 'Ing.', 'Computación'),
(17354607, 'Lic.', 'Cs Matemáticas'),
(17354607, 'Msc.', 'Matemática Pura'),
(13188691, 'Ing.', 'Informática'),
(14091124, 'Ing.', 'Informática'),
(14091124, 'Esp.', 'Experto Elearning'),
(10775753, 'Lic.', 'Educación Mención Matemática'),
(10775753, 'Lic.', 'Cs Información'),
(10775753, 'Esp.', 'Organización Sit. Información'),
(14292469, 'Lic.', 'Cs Matemáticas'),
(18103232, 'Prof.', 'Educación Física y deporte'),
(9627295, 'Lic.', 'Cs Matemáticas'),
(7415067, 'Ing.', 'Informática'),
(7415067, 'Esp.', 'Telematica Informática'),
(7415067, 'Msc.', 'Cs Información'),
(9602562, 'Ing.', 'Informática'),
(9602562, 'Msc.', 'Gerenc. Emp.'),
(14677589, 'Lic.', 'Matemática'),
(7439117, 'Ing.', 'Informática'),
(7439117, 'Esp.', 'Sist. Información'),
(7439117, 'Msc.', 'Educación Superior'),
(25471240, 'Ing.', 'Informática'),
(13991971, 'Ing.', 'Computación'),
(13991971, 'Msc.', 'Educación Superior'),
(14159756, 'Ing.', 'Computación'),
(5260810, 'Ing.', 'Informática'),
(10956121, 'Ing.', 'Informática'),
(12849928, 'Ing.', 'Informática'),
(12849928, 'Msc.', 'Educación Superior'),
(11898335, 'Ing.', 'Computación'),
(9541953, 'TSU.', 'Analista de Sistemas'),
(9541953, 'Lic.', 'Administración Mención Informática'),
(7392496, 'Ing.', 'Informática'),
(7392496, 'Msc.', 'Cs Información'),
(15170003, 'Ing.', 'Computación');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uc_docente`
--

CREATE TABLE `uc_docente` (
  `uc_codigo` varchar(30) NOT NULL,
  `doc_cedula` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `uc_docente`
--

INSERT INTO `uc_docente` (`uc_codigo`, `doc_cedula`) VALUES
('PIPNN078003', 14159756),
('PIPNN078003', 13527711),
('PIMAT090003', 10775753),
('PIMAT090003', 9627295),
('PIMAT090003', 17354607),
('PIIUP052002', 9118178),
('PIPST234109', 7423485),
('PIPST234109', 15170003),
('PIPST234109', 13527711),
('PIFOC090103', 15170003),
('PIFOC090103', 13527711),
('PIFOC090103', 9629702),
('PIMAT090003', 18912216),
('PIMAT156306', 18912216),
('PIPST234109', 10848316),
('PIMAT234109', 15351688),
('PIELE072103', 30395804),
('PIACA090103', 16385182),
('PIACA090203', 16385182),
('PIFOC090203', 24418577),
('PIMAT156206', 26197135),
('PIPRO306212', 13188691),
('PIREC156206', 15693145),
('PIELE072203', 30088284),
('PIPST234209', 13991250),
('PIPST234209', 16403903),
('PIPST234209', 9555514),
('PIACA090203', 18103232),
('PIMAT156206', 10775753),
('PIREC156206', 14159756),
('PIFOC090203', 14159756),
('PIBAD090203', 9540060),
('PIACA090203', 20351422),
('PIELE072203', 29517943),
('PIMAT156206', 15351688),
('PIALP306112', 9118178),
('PIALP306112', 7439117),
('PIALP306112', 9629702),
('PIACA090103', 18103232),
('PIMAT234109', 9627295),
('PIPST234109', 26197135),
('PIALP306112', 10723015),
('PIACA090103', 11898335),
('PIPST234109', 14677589),
('PIFOC090103', 23316126),
('PIELE072103', 29880797),
('PISIO078303', 7392496),
('PIFOC090303', 15170003),
('PIINS252309', 13991971),
('PIPST234309', 7391773),
('PIELE072203', 25471240),
('PIELE078303', 25471240),
('PIFOC090303', 24418577),
('PIPST234309', 10844463),
('PIREA084403', 5260810),
('PIAUI120404', 16403903),
('PIACA090403', 16403903),
('PIELE072403', 7391773),
('PIFOC090403', 13527711),
('PIMAT156206', 17354607),
('PIPRO306212', 10846157),
('PIPRO306212', 29517943),
('PIPST234209', 9540060),
('PIINO078303', 7392496),
('PIACA090303', 16385182),
('PIELE078303', 30088284),
('PIACA090303', 12701387),
('PIPST234309', 7392496),
('PIINS252309', 30395804),
('PIACA090403', 16385182),
('PIPST360412', 16403903),
('PIMAT156206', 14677589),
('PIREC156206', 13188691),
('PIELE072203', 9555514),
('PIFOC090203', 9629702),
('PIPST234209', 7439117),
('PIACA090203', 12701387),
('PIREC156206', 11898335),
('PIELE072203', 5260810),
('PIPRO306212', 25471240),
('PIARC234109', 10844463),
('PIALP306112', 10846157),
('PIMAT234109', 18912216),
('PIMAT234109', 14677589),
('PIACA090103', 12701387),
('PIFOC090103', 24418577),
('PIELE072103', 7423486),
('PIARC234109', 7424546),
('PIMAT234109', 17354607),
('PIALP306112', 15693145),
('PIELE072103', 26197135),
('PIALP306112', 7391773),
('PIARC234109', 10778236),
('PIARC234109', 7423486),
('PIARC234109', 11898335),
('PIIUP052002', 13695847),
('PIPNN078003', 10848316),
('PIIUP052002', 18356682);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uc_horario`
--

CREATE TABLE `uc_horario` (
  `uc_codigo` varchar(30) DEFAULT NULL,
  `doc_cedula` int(11) DEFAULT NULL,
  `subgrupo` varchar(10) DEFAULT NULL,
  `sec_codigo` varchar(30) DEFAULT NULL,
  `ani_anio` int(11) DEFAULT NULL,
  `ani_tipo` varchar(10) DEFAULT NULL,
  `esp_numero` varchar(30) DEFAULT NULL,
  `hor_dia` varchar(10) DEFAULT NULL,
  `hor_horainicio` varchar(5) DEFAULT '',
  `hor_horafin` varchar(5) DEFAULT '',
  `esp_tipo` varchar(30) DEFAULT NULL,
  `esp_edificio` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `uc_horario`
--

INSERT INTO `uc_horario` (`uc_codigo`, `doc_cedula`, `subgrupo`, `sec_codigo`, `ani_anio`, `ani_tipo`, `esp_numero`, `hor_dia`, `hor_horainicio`, `hor_horafin`, `esp_tipo`, `esp_edificio`) VALUES
('PIIUP052002', 9118178, NULL, 'IN0403', 2025, 'regular', '14', 'Miércoles', '08:00', '10:00', 'Aula', 'Hilandera'),
('PIMAT090003', 10775753, NULL, 'IN0403', 2025, 'regular', '8', 'Sábado', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPNN078003', 14159756, NULL, 'IN0403', 2025, 'regular', '8', 'Sábado', '10:40', '12:00', 'Aula', 'Hilandera'),
('PITIC032002', NULL, NULL, 'IN0403', 2025, 'regular', '8', 'Sábado', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIIUP052002', 18356682, NULL, 'IN0423', 2025, 'regular', '15', 'Miércoles', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIMAT090003', 10775753, NULL, 'IN0423', 2025, 'regular', '21', 'Sábado', '09:20', '10:40', 'Aula', 'Giraluna'),
('PITIC032002', NULL, NULL, 'IN0423', 2025, 'regular', '21', 'Sábado', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIPNN078003', 14159756, NULL, 'IN0423', 2025, 'regular', '21', 'Sábado', '13:00', '14:20', 'Aula', 'Giraluna'),
('PIACA090103', 18103232, NULL, 'IN1103', 2025, 'regular', '12', 'Miércoles', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090103', 23316126, NULL, 'IN1103', 2025, 'regular', '12', 'Miércoles', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072103', 29880797, NULL, 'IN1103', 2025, 'regular', '3', 'Miércoles', '10:40', '12:00', 'Laboratorio', 'Giraluna'),
('PIPST234109', 15170003, NULL, 'IN1103', 2025, 'regular', '9', 'Viernes', '08:00', '10:00', 'Aula', 'Giraluna'),
('PIMAT234109', 9627295, NULL, 'IN1103', 2025, 'regular', '9', 'Viernes', '10:00', '12:00', 'Aula', 'Giraluna'),
('PIALP306112', 9118178, 'A', 'IN1103', 2025, 'regular', '3', 'Martes', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10778236, 'B', 'IN1103', 2025, 'regular', 'Hardware', 'Martes', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10778236, 'A', 'IN1103', 2025, 'regular', 'Hardware', 'Martes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9118178, 'B', 'IN1103', 2025, 'regular', '3', 'Martes', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIALP306112', 10723015, 'A', 'IN1123', 2025, 'regular', '5', 'Lunes', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 11898335, 'B', 'IN1123', 2025, 'regular', 'Hardware', 'Lunes', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 11898335, 'A', 'IN1123', 2025, 'regular', 'Hardware', 'Lunes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 10723015, 'B', 'IN1123', 2025, 'regular', '5', 'Lunes', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIPST234109', 10848316, NULL, 'IN1123', 2025, 'regular', '7', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIMAT234109', 14677589, NULL, 'IN1123', 2025, 'regular', '7', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072103', 29880797, NULL, 'IN1123', 2025, 'regular', '3', 'Miércoles', '08:00', '09:20', 'Laboratorio', 'Giraluna'),
('PIACA090103', 18103232, NULL, 'IN1123', 2025, 'regular', '14', 'Miércoles', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIFOC090103', 23316126, NULL, 'IN1123', 2025, 'regular', '14', 'Miércoles', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIFOC090103', 23316126, NULL, 'IN1203', 2025, 'regular', '12', 'Miércoles', '13:00', '14:20', 'Aula', 'Hilandera'),
('PIACA090103', 18103232, NULL, 'IN1203', 2025, 'regular', '12', 'Miércoles', '14:20', '15:40', 'Aula', 'Hilandera'),
('PIELE072103', 26197135, NULL, 'IN1203', 2025, 'regular', '12', 'Miércoles', '15:40', '17:00', 'Aula', 'Hilandera'),
('PIMAT234109', 15351688, NULL, 'IN1203', 2025, 'regular', '12', 'Jueves', '13:00', '15:00', 'Aula', 'Hilandera'),
('PIPST234109', 26197135, NULL, 'IN1203', 2025, 'regular', '12', 'Jueves', '15:00', '17:00', 'Aula', 'Hilandera'),
('PIALP306112', 7391773, 'A', 'IN1203', 2025, 'regular', '3', 'Viernes', '13:00', '15:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10844463, 'B', 'IN1203', 2025, 'regular', 'Hardware', 'Viernes', '13:00', '15:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10844463, 'A', 'IN1203', 2025, 'regular', 'Hardware', 'Viernes', '15:00', '17:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 7391773, 'B', 'IN1203', 2025, 'regular', '3', 'Viernes', '15:00', '17:00', 'Laboratorio', 'Giraluna'),
('PIFOC090103', 9629702, NULL, 'IN1143', 2025, 'regular', '14', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST234109', 10848316, NULL, 'IN1143', 2025, 'regular', '14', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090103', 16385182, NULL, 'IN1143', 2025, 'regular', '14', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 30395804, NULL, 'IN1143', 2025, 'regular', '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 15351688, NULL, 'IN1143', 2025, 'regular', '14', 'Jueves', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIALP306112', 9629702, 'A', 'IN1143', 2025, 'regular', '3', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10844463, 'B', 'IN1143', 2025, 'regular', 'Hardware', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10844463, 'A', 'IN1143', 2025, 'regular', 'Hardware', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9629702, 'B', 'IN1143', 2025, 'regular', '3', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIALP306112', 10846157, NULL, 'IN1213', 2025, 'regular', '3', 'Lunes', '13:00', '15:00', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 18912216, NULL, 'IN1213', 2025, 'regular', '12', 'Martes', '13:00', '15:00', 'Aula', 'Hilandera'),
('PIACA090203', 16385182, NULL, 'IN2103', 2025, 'regular', '10', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090203', 24418577, NULL, 'IN2103', 2025, 'regular', '10', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 17354607, NULL, 'IN2103', 2025, 'regular', '10', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIPRO306212', 13188691, NULL, 'IN2103', 2025, 'regular', '6', 'Jueves', '09:20', '12:00', 'Laboratorio', 'Hilandera'),
('PIPST234209', 13991250, NULL, 'IN2103', 2025, 'regular', '10', 'Viernes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIREC156206', 15693145, NULL, 'IN2103', 2025, 'regular', '10', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIELE072203', 30088284, NULL, 'IN2103', 2025, 'regular', '6', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIPST234209', 16403903, NULL, 'IN2113', 2025, 'regular', '4', 'Lunes', '08:00', '10:00', 'Aula', 'Hilandera'),
('PIPRO306212', 10846157, NULL, 'IN2113', 2025, 'regular', '6', 'Lunes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090203', 24418577, NULL, 'IN2113', 2025, 'regular', '11', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIACA090203', 18103232, NULL, 'IN2113', 2025, 'regular', '11', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 14677589, NULL, 'IN2113', 2025, 'regular', '11', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREC156206', 13188691, NULL, 'IN2113', 2025, 'regular', '12', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072203', 9555514, NULL, 'IN2113', 2025, 'regular', '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIREC156206', 13188691, NULL, 'IN2403', 2025, 'regular', '11', 'Martes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIACA090203', 20351422, NULL, 'IN2403', 2025, 'regular', '11', 'Martes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIELE072203', 29517943, NULL, 'IN2403', 2025, 'regular', '6', 'Viernes', '08:00', '09:20', 'Laboratorio', 'Hilandera'),
('PIPST234209', 9555514, NULL, 'IN2403', 2025, 'regular', '12', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIMAT156206', 15351688, NULL, 'IN2403', 2025, 'regular', '12', 'Viernes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIPRO306212', 25471240, NULL, 'IN2403', 2025, 'regular', '6', 'Sábado', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIFOC090203', 14159756, NULL, 'IN2403', 2025, 'regular', '21', 'Sábado', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIACA090303', 16385182, NULL, 'IIN3103', 2025, 'regular', '6', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIELE078303', 25471240, NULL, 'IIN3103', 2025, 'regular', '6', 'Sábado', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090303', 15170003, NULL, 'IIN3103', 2025, 'regular', '12', 'Viernes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIINO078303', 7392496, NULL, 'IIN3103', 2025, 'regular', '6', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIINS252309', 13991971, NULL, 'IIN3103', 2025, 'regular', '12', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156306', 18912216, NULL, 'IIN3103', 2025, 'regular', '6', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIPST234309', 7391773, NULL, 'IIN3103', 2025, 'regular', '12', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIACA090303', 12701387, NULL, 'IIN3104', 2025, 'regular', '12', 'Lunes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE078303', 30088284, NULL, 'IIN3104', 2025, 'regular', '3', 'Lunes', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIMAT156306', 18912216, NULL, 'IIN3104', 2025, 'regular', '26', 'Martes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIINO078303', 7392496, NULL, 'IIN3104', 2025, 'regular', '26', 'Martes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIINS252309', 13991971, NULL, 'IIN3104', 2025, 'regular', '26', 'Viernes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIFOC090303', 24418577, NULL, 'IIN3104', 2025, 'regular', '26', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIPST234309', 10844463, NULL, 'IIN3104', 2025, 'regular', '26', 'Viernes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIELE078303', 30088284, NULL, 'IIN3113', 2025, 'regular', '3', 'Lunes', '08:00', '09:20', 'Laboratorio', 'Giraluna'),
('PIACA090303', 12701387, NULL, 'IIN3113', 2025, 'regular', '13', 'Lunes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIINO078303', 7392496, NULL, 'IIN3113', 2025, 'regular', '10', 'Martes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIMAT156306', 18912216, NULL, 'IIN3113', 2025, 'regular', '10', 'Martes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIPST234309', 7392496, NULL, 'IIN3113', 2025, 'regular', '13', 'Viernes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIINS252309', 30395804, NULL, 'IIN3113', 2025, 'regular', '13', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIFOC090303', 24418577, NULL, 'IIN3113', 2025, 'regular', '13', 'Viernes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIALP306112', 10846157, NULL, 'IN1202', 2025, 'regular', '3', 'Lunes', '13:00', '15:00', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 18912216, NULL, 'IN1202', 2025, 'regular', '12', 'Martes', '13:00', '15:00', 'Aula', 'Hilandera'),
('PIALP306112', 10846157, NULL, 'IN1204', 2025, 'regular', '3', 'Lunes', '13:00', '15:00', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 18912216, NULL, 'IN1204', 2025, 'regular', '12', 'Martes', '13:00', '15:00', 'Aula', 'Hilandera'),
('PIALP306112', 10846157, NULL, 'IN1214', 2025, 'regular', '3', 'Lunes', '13:00', '15:00', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 18912216, NULL, 'IN1214', 2025, 'regular', '12', 'Martes', '13:00', '15:00', 'Aula', 'Hilandera'),
('PIACA090203', 16385182, NULL, 'IN2101', 2025, 'regular', '10', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090203', 24418577, NULL, 'IN2101', 2025, 'regular', '10', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 17354607, NULL, 'IN2101', 2025, 'regular', '10', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIPRO306212', 13188691, NULL, 'IN2101', 2025, 'regular', '6', 'Jueves', '09:20', '12:00', 'Laboratorio', 'Hilandera'),
('PIPST234209', 13991250, NULL, 'IN2101', 2025, 'regular', '10', 'Viernes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIREC156206', 15693145, NULL, 'IN2101', 2025, 'regular', '10', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIELE072203', 30088284, NULL, 'IN2101', 2025, 'regular', '6', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIPST234209', 16403903, NULL, 'IN2102', 2025, 'regular', '4', 'Lunes', '08:00', '10:00', 'Aula', 'Hilandera'),
('PIPRO306212', 10846157, NULL, 'IN2102', 2025, 'regular', '6', 'Lunes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090203', 24418577, NULL, 'IN2102', 2025, 'regular', '11', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIACA090203', 18103232, NULL, 'IN2102', 2025, 'regular', '11', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 14677589, NULL, 'IN2102', 2025, 'regular', '11', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREC156206', 13188691, NULL, 'IN2102', 2025, 'regular', '12', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072203', 9555514, NULL, 'IN2102', 2025, 'regular', '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIPRO306212', 29517943, NULL, 'IN2104', 2025, 'regular', '6', 'Lunes', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIPST234209', 7439117, NULL, 'IN2104', 2025, 'regular', '4', 'Lunes', '10:00', '12:00', 'Aula', 'Hilandera'),
('PIMAT156206', 17354607, NULL, 'IN2104', 2025, 'regular', '8', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090203', 9629702, NULL, 'IN2104', 2025, 'regular', '8', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIACA090203', 12701387, NULL, 'IN2104', 2025, 'regular', '8', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREC156206', 11898335, NULL, 'IN2104', 2025, 'regular', '13', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072203', 5260810, NULL, 'IN2104', 2025, 'regular', 'Software', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Hilandera'),
('PIACA090303', 16385182, NULL, 'IIN3101', 2025, 'regular', '6', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIELE078303', 25471240, NULL, 'IIN3101', 2025, 'regular', '6', 'Sábado', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090303', 15170003, NULL, 'IIN3101', 2025, 'regular', '12', 'Viernes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIINO078303', 7392496, NULL, 'IIN3101', 2025, 'regular', '6', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIINS252309', 13991971, NULL, 'IIN3101', 2025, 'regular', '12', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156306', 18912216, NULL, 'IIN3101', 2025, 'regular', '6', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIPST234309', 7391773, NULL, 'IIN3101', 2025, 'regular', '12', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIACA090303', 16385182, NULL, 'IIN3102', 2025, 'regular', '6', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIELE078303', 25471240, NULL, 'IIN3102', 2025, 'regular', '6', 'Sábado', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090303', 15170003, NULL, 'IIN3102', 2025, 'regular', '12', 'Viernes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIINO078303', 7392496, NULL, 'IIN3102', 2025, 'regular', '6', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIINS252309', 13991971, NULL, 'IIN3102', 2025, 'regular', '12', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156306', 18912216, NULL, 'IIN3102', 2025, 'regular', '6', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIPST234309', 7391773, NULL, 'IIN3102', 2025, 'regular', '12', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090103', 9629702, NULL, 'IN1101', 2025, 'regular', '14', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST234109', 10848316, NULL, 'IN1101', 2025, 'regular', '14', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090103', 16385182, NULL, 'IN1101', 2025, 'regular', '14', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 30395804, NULL, 'IN1101', 2025, 'regular', '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 15351688, NULL, 'IN1101', 2025, 'regular', '14', 'Jueves', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIALP306112', 9629702, NULL, 'IN1101', 2025, 'regular', '3', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10844463, NULL, 'IN1101', 2025, 'regular', 'Hardware', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10844463, NULL, 'IN1101', 2025, 'regular', 'Hardware', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9629702, NULL, 'IN1101', 2025, 'regular', '3', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIFOC090103', 9629702, NULL, 'IN1102', 2025, 'regular', '14', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST234109', 10848316, NULL, 'IN1102', 2025, 'regular', '14', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090103', 16385182, NULL, 'IN1102', 2025, 'regular', '14', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 30395804, NULL, 'IN1102', 2025, 'regular', '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 15351688, NULL, 'IN1102', 2025, 'regular', '14', 'Jueves', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIALP306112', 9629702, NULL, 'IN1102', 2025, 'regular', '3', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10844463, NULL, 'IN1102', 2025, 'regular', 'Hardware', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10844463, NULL, 'IN1102', 2025, 'regular', 'Hardware', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9629702, NULL, 'IN1102', 2025, 'regular', '3', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIFOC090103', 9629702, NULL, 'IN1104', 2025, 'regular', '14', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST234109', 10848316, NULL, 'IN1104', 2025, 'regular', '14', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090103', 16385182, NULL, 'IN1104', 2025, 'regular', '14', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 30395804, NULL, 'IN1104', 2025, 'regular', '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 15351688, NULL, 'IN1104', 2025, 'regular', '14', 'Jueves', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIALP306112', 9629702, NULL, 'IN1104', 2025, 'regular', '3', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10844463, NULL, 'IN1104', 2025, 'regular', 'Hardware', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10844463, NULL, 'IN1104', 2025, 'regular', 'Hardware', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9629702, NULL, 'IN1104', 2025, 'regular', '3', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIPNN078003', 13527711, NULL, 'IN0123', 2025, 'regular', '13', 'Jueves', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIIUP052002', 13695847, NULL, 'IN0123', 2025, 'regular', '9', 'Viernes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIMAT090003', 9627295, NULL, 'IN0123', 2025, 'regular', '9', 'Viernes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PITIC032002', NULL, NULL, 'IN0123', 2025, 'regular', '9', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIACA090103', 18103232, NULL, 'IN1113', 2025, 'regular', '12', 'Miércoles', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090103', 23316126, NULL, 'IN1113', 2025, 'regular', '12', 'Miércoles', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072103', 29880797, NULL, 'IN1113', 2025, 'regular', '3', 'Miércoles', '10:40', '12:00', 'Laboratorio', 'Giraluna'),
('PIPST234109', 15170003, NULL, 'IN1113', 2025, 'regular', '9', 'Viernes', '08:00', '10:00', 'Aula', 'Giraluna'),
('PIMAT234109', 9627295, NULL, 'IN1113', 2025, 'regular', '9', 'Viernes', '10:00', '12:00', 'Aula', 'Giraluna'),
('PIALP306112', 9118178, NULL, 'IN1113', 2025, 'regular', '3', 'Martes', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10778236, NULL, 'IN1113', 2025, 'regular', 'Hardware', 'Martes', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10778236, NULL, 'IN1113', 2025, 'regular', 'Hardware', 'Martes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9118178, NULL, 'IN1113', 2025, 'regular', '3', 'Martes', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIACA090103', 18103232, NULL, 'IN1113', 2025, 'regular', '12', 'Miércoles', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090103', 23316126, NULL, 'IN1113', 2025, 'regular', '12', 'Miércoles', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072103', 29880797, NULL, 'IN1113', 2025, 'regular', '3', 'Miércoles', '10:40', '12:00', 'Laboratorio', 'Giraluna'),
('PIPST234109', 15170003, NULL, 'IN1113', 2025, 'regular', '9', 'Viernes', '08:00', '10:00', 'Aula', 'Giraluna'),
('PIMAT234109', 9627295, NULL, 'IN1113', 2025, 'regular', '9', 'Viernes', '10:00', '12:00', 'Aula', 'Giraluna'),
('PIALP306112', 9118178, NULL, 'IN1113', 2025, 'regular', '3', 'Martes', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10778236, NULL, 'IN1113', 2025, 'regular', 'Hardware', 'Martes', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10778236, NULL, 'IN1113', 2025, 'regular', 'Hardware', 'Martes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9118178, NULL, 'IN1113', 2025, 'regular', '3', 'Martes', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIIUP052002', 18356682, NULL, 'IN0413', 2025, 'regular', '15', 'Miércoles', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIPNN078003', 14159756, NULL, 'IN0413', 2025, 'regular', '21', 'Sábado', '13:00', '14:00', 'Aula', 'Giraluna'),
('PITIC032002', NULL, NULL, 'IN0413', 2025, 'regular', '21', 'Sábado', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIMAT090003', 10775753, NULL, 'IN0413', 2025, 'regular', '21', 'Sábado', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIIUP052002', 13695847, NULL, 'IN0103', 2025, 'regular', '9', 'Viernes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PITIC032002', NULL, NULL, 'IN0103', 2025, 'regular', '9', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIMAT090003', 9627295, NULL, 'IN0103', 2025, 'regular', '9', 'Viernes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIPNN078003', 13527711, NULL, 'IN0103', 2025, 'regular', NULL, 'Jueves', '10:40', '12:00', NULL, NULL),
('PIMAT090003', 17354607, NULL, 'IN0113', 2025, 'regular', '12', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPNN078003', 13527711, NULL, 'IN0113', 2025, 'regular', '12', 'Jueves', '09:20', '10:40', 'Aula', 'Hilandera'),
('PITIC032002', NULL, NULL, 'IN0113', 2025, 'regular', '26', 'Viernes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIIUP052002', 13695847, NULL, 'IN0113', 2025, 'regular', '26', 'Viernes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIMAT090003', 9627295, NULL, 'IN0123', 2025, 'regular', '26', 'Martes', '07:00', '08:40', 'Aula', 'Giraluna'),
('PIPNN078003', 10848316, NULL, 'IN0123', 2025, 'regular', '26', 'Martes', '08:40', '10:00', 'Aula', 'Giraluna'),
('PIIUP052002', 18356682, NULL, 'IN0123', 2025, 'regular', '26', 'Miércoles', '08:00', '09:20', 'Aula', 'Giraluna'),
('PITIC032002', NULL, NULL, 'IN0123', 2025, 'regular', NULL, 'Miércoles', '09:20', '10:40', NULL, NULL),
('PIFOC090103', 15170003, NULL, 'IN1113', 2025, 'regular', '13', 'Miércoles', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 29880797, NULL, 'IN1113', 2025, 'regular', '13', 'Miércoles', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIACA090103', 18103232, NULL, 'IN1113', 2025, 'regular', '13', 'Miércoles', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIALP306112', 7439117, 'A', 'IN1113', 2025, 'regular', '5', 'Jueves', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 7423486, 'B', 'IN1113', 2025, 'regular', 'Hardware', 'Jueves', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 7439117, 'B', 'IN1113', 2025, 'regular', '5', 'Jueves', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIALP306112', 7439117, 'B', 'IN1113', 2025, 'regular', '5', 'Jueves', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 9627295, NULL, 'IN1113', 2025, 'regular', '13', 'Viernes', '08:00', '10:00', 'Aula', 'Hilandera'),
('PIPST234109', 26197135, NULL, 'IN1113', 2025, 'regular', '13', 'Viernes', '10:00', '12:00', 'Aula', 'Hilandera'),
('PIPRO306212', 29517943, NULL, 'IN2123', 2025, 'regular', '6', 'Lunes', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIPST234209', 7439117, NULL, 'IN2123', 2025, 'regular', '4', 'Lunes', '10:00', '12:00', 'Aula', 'Hilandera'),
('PIMAT156206', 17354607, NULL, 'IN2123', 2025, 'regular', '8', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090203', 9629702, NULL, 'IN2123', 2025, 'regular', '8', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIACA090203', 12701387, NULL, 'IN2123', 2025, 'regular', '8', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREC156206', 11898335, NULL, 'IN2123', 2025, 'regular', '13', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072203', 5260810, NULL, 'IN2123', 2025, 'regular', 'Software', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Hilandera'),
('PIPRO306212', 29517943, NULL, 'IN2133', 2025, 'regular', '6', 'Martes', '08:00', '09:20', 'Laboratorio', 'Hilandera'),
('PIPST234209', 9540060, NULL, 'IN2133', 2025, 'regular', '13', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIACA090203', 18103232, NULL, 'IN2133', 2025, 'regular', '13', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIELE072203', 30088284, NULL, 'IN2133', 2025, 'regular', '6', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIREC156206', 14159756, NULL, 'IN2133', 2025, 'regular', '9', 'Sábado', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090203', 14159756, NULL, 'IN2133', 2025, 'regular', '9', 'Sábado', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 10775753, NULL, 'IN2133', 2025, 'regular', '9', 'Sábado', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIPRO306212', 29517943, NULL, 'IN2114', 2025, 'regular', '6', 'Martes', '08:00', '09:20', 'Laboratorio', 'Hilandera'),
('PIPST234209', 9540060, NULL, 'IN2114', 2025, 'regular', '13', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIACA090203', 18103232, NULL, 'IN2114', 2025, 'regular', '13', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIELE072203', 30088284, NULL, 'IN2114', 2025, 'regular', '6', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIREC156206', 14159756, NULL, 'IN2114', 2025, 'regular', '9', 'Sábado', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090203', 14159756, NULL, 'IN2114', 2025, 'regular', '9', 'Sábado', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 10775753, NULL, 'IN2114', 2025, 'regular', '9', 'Sábado', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREA084403', 5260810, NULL, 'IIN4402', 2025, 'regular', '5', 'Lunes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIAUI120404', 16403903, NULL, 'IIN4402', 2025, 'regular', '5', 'Lunes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090403', 16385182, NULL, 'IIN4402', 2025, 'regular', '15', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST360412', 16403903, NULL, 'IIN4402', 2025, 'regular', '15', 'Martes', '09:20', '11:20', 'Aula', 'Hilandera'),
('PIFOC090403', 13527711, NULL, 'IIN4402', 2025, 'regular', '15', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072403', 7391773, NULL, 'IIN4402', 2025, 'regular', 'Software', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIIDI090403', 18356682, NULL, 'IIN4402', 2025, 'regular', '15', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIREA084403', 5260810, NULL, 'IIN4401', 2025, 'regular', '5', 'Lunes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIAUI120404', 16403903, NULL, 'IIN4401', 2025, 'regular', '5', 'Lunes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090403', 16385182, NULL, 'IIN4401', 2025, 'regular', '15', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST360412', 16403903, NULL, 'IIN4401', 2025, 'regular', '15', 'Martes', '09:20', '11:20', 'Aula', 'Hilandera'),
('PIFOC090403', 13527711, NULL, 'IIN4401', 2025, 'regular', '15', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072403', 7391773, NULL, 'IIN4401', 2025, 'regular', 'Software', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIIDI090403', 18356682, NULL, 'IIN4401', 2025, 'regular', '15', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIREA084403', 5260810, NULL, 'IIN4403', 2025, 'regular', '5', 'Lunes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIAUI120404', 16403903, NULL, 'IIN4403', 2025, 'regular', '5', 'Lunes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090403', 16385182, NULL, 'IIN4403', 2025, 'regular', '15', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST360412', 16403903, NULL, 'IIN4403', 2025, 'regular', '15', 'Martes', '09:20', '11:20', 'Aula', 'Hilandera'),
('PIFOC090403', 13527711, NULL, 'IIN4403', 2025, 'regular', '15', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072403', 7391773, NULL, 'IIN4403', 2025, 'regular', 'Software', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIIDI090403', 18356682, NULL, 'IIN4403', 2025, 'regular', '15', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIREA084403', 5260810, NULL, 'IIN4404', 2025, 'regular', '5', 'Lunes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIAUI120404', 16403903, NULL, 'IIN4404', 2025, 'regular', '5', 'Lunes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090403', 16385182, NULL, 'IIN4404', 2025, 'regular', '15', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST360412', 16403903, NULL, 'IIN4404', 2025, 'regular', '15', 'Martes', '09:20', '11:20', 'Aula', 'Hilandera'),
('PIFOC090403', 13527711, NULL, 'IIN4404', 2025, 'regular', '15', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072403', 7391773, NULL, 'IIN4404', 2025, 'regular', 'Software', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIIDI090403', 18356682, NULL, 'IIN4404', 2025, 'regular', '15', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT234109', 14677589, NULL, 'IN1133', 2025, 'regular', '7', 'Martes', '08:00', '09:20', 'Aula', 'Rio 7 Estrellas'),
('PIACA090103', 12701387, NULL, 'IN1133', 2025, 'regular', '7', 'Martes', '09:20', '10:40', 'Aula', 'Rio 7 Estrellas'),
('PIFOC090103', 24418577, NULL, 'IN1133', 2025, 'regular', '10', 'Viernes', '08:00', '09:20', 'Aula', 'Rio 7 Estrellas'),
('PIELE072103', 7423486, NULL, 'IN1133', 2025, 'regular', '6', 'Viernes', '09:20', '10:40', 'Laboratorio', 'Hilandera'),
('PIPST234109', 13527711, NULL, 'IN1133', 2025, 'regular', '10', 'Viernes', '10:40', '12:00', 'Aula', 'Rio 7 Estrellas'),
('PIALP306112', 10846157, 'A', 'IN1133', 2025, 'regular', '5', 'Sábado', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 7424546, 'B', 'IN1133', 2025, 'regular', 'Hardware', 'Sábado', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 7424546, 'B', 'IN1133', 2025, 'regular', 'Hardware', 'Sábado', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 10846157, 'A', 'IN1133', 2025, 'regular', '5', 'Sábado', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIACA090103', 12701387, NULL, 'IN1403', 2025, 'regular', '9', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 7423486, NULL, 'IN1403', 2025, 'regular', '6', 'Viernes', '08:00', '09:20', 'Laboratorio', 'Hilandera'),
('PIFOC090103', 13527711, NULL, 'IN1403', 2025, 'regular', '11', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT234109', 17354607, NULL, 'IN1403', 2025, 'regular', '9', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIPST234109', 7423485, NULL, 'IN1403', 2025, 'regular', '11', 'Viernes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIALP306112', 15693145, 'A', 'IN1403', 2025, 'regular', '3', 'Sábado', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 7424546, 'B', 'IN1403', 2025, 'regular', 'Hardware', 'Sábado', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 7424546, 'A', 'IN1403', 2025, 'regular', 'Hardware', 'Sábado', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 15693145, 'B', 'IN1403', 2025, 'regular', '3', 'Sábado', '10:00', '12:00', 'Laboratorio', 'Giraluna');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uc_malla`
--

CREATE TABLE `uc_malla` (
  `mal_codigo` varchar(30) NOT NULL,
  `uc_codigo` varchar(30) NOT NULL,
  `mal_hora_independiente` int(11) NOT NULL DEFAULT 0,
  `mal_hora_asistida` int(11) NOT NULL DEFAULT 0,
  `mal_hora_academica` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `uc_malla`
--

INSERT INTO `uc_malla` (`mal_codigo`, `uc_codigo`, `mal_hora_independiente`, `mal_hora_asistida`, `mal_hora_academica`) VALUES
('05033', 'PIIUP052002', 16, 36, 3),
('05033', 'PIMAT090003', 18, 72, 8),
('05033', 'PIPNN078003', 30, 48, 4),
('05033', 'PITIC032002', 8, 24, 2),
('05033', 'PIACA090103', 18, 72, 2),
('05033', 'PIALP306112', 126, 180, 5),
('05033', 'PIARC234109', 54, 180, 5),
('05033', 'PIELE072103', 18, 54, 3),
('05033', 'PIFOC090103', 18, 72, 2),
('05033', 'PIIDI090103', 18, 72, 4),
('05033', 'PIMAT234109', 54, 180, 5),
('05033', 'PIPST234109', 18, 216, 6),
('05033', 'PIACA090203', 18, 72, 2),
('05033', 'PIBAD090203', 36, 54, 3),
('05033', 'PIELE072203', 18, 54, 3),
('05033', 'PIFOC090203', 18, 72, 2),
('05033', 'PIINS090203', 36, 54, 3),
('05033', 'PIMAT156206', 36, 120, 3),
('05033', 'PIPRO306212', 126, 180, 5),
('05033', 'PIPST234209', 18, 216, 6),
('05033', 'PIREC156206', 36, 120, 3),
('05033', 'PIACA090303', 18, 72, 2),
('05033', 'PIELE078303', 18, 54, 3),
('05033', 'PIFOC090303', 18, 72, 2),
('05033', 'PIINS252309', 72, 180, 5),
('05033', 'PIINO078303', 6, 72, 4),
('05033', 'PIMAT156306', 36, 120, 3),
('05033', 'PIMOB078303', 6, 72, 4),
('05033', 'PIPST234309', 18, 216, 6),
('05033', 'PISIO078303', 6, 72, 4),
('05033', 'PIACA090403', 18, 72, 2),
('05033', 'PIABD090403', 26, 64, 4),
('05033', 'PIAUI120404', 48, 72, 4),
('05033', 'PIELE072403', 18, 54, 3),
('05033', 'PIFOC090403', 18, 72, 2),
('05033', 'PIGPI120404', 48, 72, 4),
('05033', 'PIIDI090403', 18, 72, 2),
('05033', 'PIPST360412', 144, 216, 6),
('05033', 'PIREA084403', 18, 66, 4),
('05033', 'PISEI120404', 48, 72, 4);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `coordinacion_docente`
--
ALTER TABLE `coordinacion_docente`
  ADD KEY `doc_cedula` (`doc_cedula`),
  ADD KEY `cor_nombre` (`cor_nombre`);

--
-- Indices de la tabla `docente_horario`
--
ALTER TABLE `docente_horario`
  ADD KEY `doc_cedula` (`doc_cedula`),
  ADD KEY `sec_codigo` (`sec_codigo`,`ani_anio`,`ani_tipo`);

--
-- Indices de la tabla `per_aprobados`
--
ALTER TABLE `per_aprobados`
  ADD KEY `ani_anio` (`ani_anio`,`ani_tipo`),
  ADD KEY `sec_codigo` (`sec_codigo`,`ani_anio`,`ani_tipo`),
  ADD KEY `uc_codigo` (`uc_codigo`);

--
-- Indices de la tabla `tbl_actividad`
--
ALTER TABLE `tbl_actividad`
  ADD KEY `doc_cedula` (`doc_cedula`);

--
-- Indices de la tabla `tbl_anio`
--
ALTER TABLE `tbl_anio`
  ADD PRIMARY KEY (`ani_anio`,`ani_tipo`);

--
-- Indices de la tabla `tbl_aprobados`
--
ALTER TABLE `tbl_aprobados`
  ADD KEY `ani_anio` (`ani_anio`,`ani_tipo`),
  ADD KEY `doc_cedula` (`doc_cedula`),
  ADD KEY `sec_codigo` (`sec_codigo`,`ani_anio`,`ani_tipo`),
  ADD KEY `uc_codigo` (`uc_codigo`);

--
-- Indices de la tabla `tbl_area`
--
ALTER TABLE `tbl_area`
  ADD PRIMARY KEY (`area_nombre`);

--
-- Indices de la tabla `tbl_bloque_eliminado`
--
ALTER TABLE `tbl_bloque_eliminado`
  ADD PRIMARY KEY (`bloque_eliminado_id`),
  ADD UNIQUE KEY `uk_bloque_eliminado` (`sec_codigo`,`ani_anio`,`ani_tipo`,`tur_horainicio`),
  ADD KEY `idx_seccion_anio` (`sec_codigo`,`ani_anio`,`ani_tipo`);

--
-- Indices de la tabla `tbl_bloque_personalizado`
--
ALTER TABLE `tbl_bloque_personalizado`
  ADD PRIMARY KEY (`bloque_id`),
  ADD UNIQUE KEY `uk_bloque` (`sec_codigo`,`ani_anio`,`ani_tipo`,`tur_horainicio`);

--
-- Indices de la tabla `tbl_categoria`
--
ALTER TABLE `tbl_categoria`
  ADD PRIMARY KEY (`cat_nombre`);

--
-- Indices de la tabla `tbl_coordinacion`
--
ALTER TABLE `tbl_coordinacion`
  ADD PRIMARY KEY (`cor_nombre`);

--
-- Indices de la tabla `tbl_docente`
--
ALTER TABLE `tbl_docente`
  ADD PRIMARY KEY (`doc_cedula`),
  ADD KEY `cat_nombre` (`cat_nombre`);

--
-- Indices de la tabla `tbl_eje`
--
ALTER TABLE `tbl_eje`
  ADD PRIMARY KEY (`eje_nombre`);

--
-- Indices de la tabla `tbl_espacio`
--
ALTER TABLE `tbl_espacio`
  ADD PRIMARY KEY (`esp_numero`,`esp_tipo`,`esp_edificio`);

--
-- Indices de la tabla `tbl_fase`
--
ALTER TABLE `tbl_fase`
  ADD KEY `ani_anio` (`ani_anio`,`ani_tipo`);

--
-- Indices de la tabla `tbl_horario`
--
ALTER TABLE `tbl_horario`
  ADD KEY `sec_codigo` (`sec_codigo`,`ani_anio`,`ani_tipo`),
  ADD KEY `tur_nombre` (`tur_nombre`);

--
-- Indices de la tabla `tbl_malla`
--
ALTER TABLE `tbl_malla`
  ADD PRIMARY KEY (`mal_codigo`);

--
-- Indices de la tabla `tbl_per`
--
ALTER TABLE `tbl_per`
  ADD KEY `ani_anio` (`ani_anio`,`ani_tipo`);

--
-- Indices de la tabla `tbl_prosecusion`
--
ALTER TABLE `tbl_prosecusion`
  ADD PRIMARY KEY (`sec_origen`,`ani_origen`,`ani_tipo_origen`,`sec_promocion`,`ani_destino`,`ani_tipo_destino`),
  ADD KEY `sec_promocion` (`sec_promocion`,`ani_destino`,`ani_tipo_destino`);

--
-- Indices de la tabla `tbl_seccion`
--
ALTER TABLE `tbl_seccion`
  ADD PRIMARY KEY (`sec_codigo`,`ani_anio`,`ani_tipo`),
  ADD KEY `ani_anio` (`ani_anio`,`ani_tipo`);

--
-- Indices de la tabla `tbl_titulo`
--
ALTER TABLE `tbl_titulo`
  ADD PRIMARY KEY (`tit_prefijo`,`tit_nombre`);

--
-- Indices de la tabla `tbl_turno`
--
ALTER TABLE `tbl_turno`
  ADD PRIMARY KEY (`tur_nombre`);

--
-- Indices de la tabla `tbl_uc`
--
ALTER TABLE `tbl_uc`
  ADD PRIMARY KEY (`uc_codigo`),
  ADD KEY `eje_nombre` (`eje_nombre`),
  ADD KEY `area_nombre` (`area_nombre`);

--
-- Indices de la tabla `titulo_docente`
--
ALTER TABLE `titulo_docente`
  ADD KEY `doc_cedula` (`doc_cedula`),
  ADD KEY `tit_prefijo` (`tit_prefijo`,`tit_nombre`);

--
-- Indices de la tabla `uc_docente`
--
ALTER TABLE `uc_docente`
  ADD KEY `doc_cedula` (`doc_cedula`),
  ADD KEY `uc_codigo` (`uc_codigo`);

--
-- Indices de la tabla `uc_horario`
--
ALTER TABLE `uc_horario`
  ADD KEY `uc_codigo` (`uc_codigo`),
  ADD KEY `esp_numero` (`esp_numero`,`esp_tipo`,`esp_edificio`),
  ADD KEY `sec_codigo` (`sec_codigo`,`ani_anio`,`ani_tipo`);

--
-- Indices de la tabla `uc_malla`
--
ALTER TABLE `uc_malla`
  ADD KEY `uc_codigo` (`uc_codigo`),
  ADD KEY `mal_codigo` (`mal_codigo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tbl_bloque_eliminado`
--
ALTER TABLE `tbl_bloque_eliminado`
  MODIFY `bloque_eliminado_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_bloque_personalizado`
--
ALTER TABLE `tbl_bloque_personalizado`
  MODIFY `bloque_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `coordinacion_docente`
--
ALTER TABLE `coordinacion_docente`
  ADD CONSTRAINT `coordinacion_docente_ibfk_1` FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `coordinacion_docente_ibfk_2` FOREIGN KEY (`cor_nombre`) REFERENCES `tbl_coordinacion` (`cor_nombre`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `docente_horario`
--
ALTER TABLE `docente_horario`
  ADD CONSTRAINT `docente_horario_ibfk_1` FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `docente_horario_ibfk_2` FOREIGN KEY (`sec_codigo`,`ani_anio`,`ani_tipo`) REFERENCES `tbl_horario` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `per_aprobados`
--
ALTER TABLE `per_aprobados`
  ADD CONSTRAINT `per_aprobados_ibfk_1` FOREIGN KEY (`ani_anio`,`ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `per_aprobados_ibfk_2` FOREIGN KEY (`sec_codigo`,`ani_anio`,`ani_tipo`) REFERENCES `tbl_seccion` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `per_aprobados_ibfk_3` FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_actividad`
--
ALTER TABLE `tbl_actividad`
  ADD CONSTRAINT `tbl_actividad_ibfk_1` FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_aprobados`
--
ALTER TABLE `tbl_aprobados`
  ADD CONSTRAINT `tbl_aprobados_ibfk_1` FOREIGN KEY (`ani_anio`,`ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_aprobados_ibfk_2` FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_aprobados_ibfk_3` FOREIGN KEY (`sec_codigo`,`ani_anio`,`ani_tipo`) REFERENCES `tbl_seccion` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_aprobados_ibfk_4` FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_bloque_personalizado`
--
ALTER TABLE `tbl_bloque_personalizado`
  ADD CONSTRAINT `fk_bloque_seccion` FOREIGN KEY (`sec_codigo`,`ani_anio`,`ani_tipo`) REFERENCES `tbl_horario` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_docente`
--
ALTER TABLE `tbl_docente`
  ADD CONSTRAINT `tbl_docente_ibfk_1` FOREIGN KEY (`cat_nombre`) REFERENCES `tbl_categoria` (`cat_nombre`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_fase`
--
ALTER TABLE `tbl_fase`
  ADD CONSTRAINT `tbl_fase_ibfk_1` FOREIGN KEY (`ani_anio`,`ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_horario`
--
ALTER TABLE `tbl_horario`
  ADD CONSTRAINT `tbl_horario_ibfk_1` FOREIGN KEY (`sec_codigo`,`ani_anio`,`ani_tipo`) REFERENCES `tbl_seccion` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_horario_ibfk_2` FOREIGN KEY (`tur_nombre`) REFERENCES `tbl_turno` (`tur_nombre`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_per`
--
ALTER TABLE `tbl_per`
  ADD CONSTRAINT `tbl_per_ibfk_1` FOREIGN KEY (`ani_anio`,`ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_prosecusion`
--
ALTER TABLE `tbl_prosecusion`
  ADD CONSTRAINT `tbl_prosecusion_ibfk_1` FOREIGN KEY (`sec_origen`,`ani_origen`,`ani_tipo_origen`) REFERENCES `tbl_seccion` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_prosecusion_ibfk_2` FOREIGN KEY (`sec_promocion`,`ani_destino`,`ani_tipo_destino`) REFERENCES `tbl_seccion` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_seccion`
--
ALTER TABLE `tbl_seccion`
  ADD CONSTRAINT `tbl_seccion_ibfk_1` FOREIGN KEY (`ani_anio`,`ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_uc`
--
ALTER TABLE `tbl_uc`
  ADD CONSTRAINT `tbl_uc_ibfk_1` FOREIGN KEY (`eje_nombre`) REFERENCES `tbl_eje` (`eje_nombre`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_uc_ibfk_2` FOREIGN KEY (`area_nombre`) REFERENCES `tbl_area` (`area_nombre`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `titulo_docente`
--
ALTER TABLE `titulo_docente`
  ADD CONSTRAINT `titulo_docente_ibfk_1` FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `titulo_docente_ibfk_2` FOREIGN KEY (`tit_prefijo`,`tit_nombre`) REFERENCES `tbl_titulo` (`tit_prefijo`, `tit_nombre`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `uc_docente`
--
ALTER TABLE `uc_docente`
  ADD CONSTRAINT `uc_docente_ibfk_1` FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `uc_docente_ibfk_2` FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `uc_horario`
--
ALTER TABLE `uc_horario`
  ADD CONSTRAINT `uc_horario_ibfk_1` FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `uc_horario_ibfk_2` FOREIGN KEY (`esp_numero`,`esp_tipo`,`esp_edificio`) REFERENCES `tbl_espacio` (`esp_numero`, `esp_tipo`, `esp_edificio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `uc_horario_ibfk_3` FOREIGN KEY (`sec_codigo`,`ani_anio`,`ani_tipo`) REFERENCES `tbl_seccion` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `uc_malla`
--
ALTER TABLE `uc_malla`
  ADD CONSTRAINT `uc_malla_ibfk_1` FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `uc_malla_ibfk_2` FOREIGN KEY (`mal_codigo`) REFERENCES `tbl_malla` (`mal_codigo`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
