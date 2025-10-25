-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-10-2025 a las 05:45:49
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
('Actividades Acreditables I, II', 12701387, 1),
('Actividades Acreditables I, II', 16385182, 1),
('Actividades Acreditables I, II', 18103232, 1),
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
('Arquitectura del Computador', 11264888, 1),
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
('Eje Estético Lúdico', 18103232, 1),
('Electiva 1', 11264888, 1),
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
('Ingeniería del Software', 11264888, 1),
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
('Proyecto Sociotecnológico I', 15170003, 1),
('Actividades Acreditables I, II', 2134123, 1),
('Algorítmica y Programación', 2134123, 1),
('Arquitectura del Computador', 2134123, 1),
('Administración de Base de Dato', 1123345, 1),
('Eje Epistemológico', 3759671, 1),
('Eje Etico Político', 3759671, 1),
('Tutorías Académicas', 3759671, 1),
('Académica', 23232323, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docente_horario`
--

CREATE TABLE `docente_horario` (
  `doc_cedula` int(11) DEFAULT NULL,
  `sec_codigo` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `docente_horario`
--

INSERT INTO `docente_horario` (`doc_cedula`, `sec_codigo`) VALUES
(10846157, 'IN1202'),
(18912216, 'IN1202'),
(10846157, 'IN1204'),
(18912216, 'IN1204'),
(10846157, 'IN1214'),
(18912216, 'IN1214'),
(16385182, 'IN2101'),
(24418577, 'IN2101'),
(17354607, 'IN2101'),
(13188691, 'IN2101'),
(13991250, 'IN2101'),
(15693145, 'IN2101'),
(30088284, 'IN2101'),
(16403903, 'IN2102'),
(10846157, 'IN2102'),
(24418577, 'IN2102'),
(18103232, 'IN2102'),
(14677589, 'IN2102'),
(13188691, 'IN2102'),
(9555514, 'IN2102'),
(29517943, 'IN2104'),
(7439117, 'IN2104'),
(17354607, 'IN2104'),
(9629702, 'IN2104'),
(12701387, 'IN2104'),
(11898335, 'IN2104'),
(5260810, 'IN2104'),
(16385182, 'IN3101'),
(25471240, 'IN3101'),
(15170003, 'IN3101'),
(7392496, 'IN3101'),
(13991971, 'IN3101'),
(18912216, 'IN3101'),
(7391773, 'IN3101'),
(16385182, 'IN3102'),
(25471240, 'IN3102'),
(15170003, 'IN3102'),
(7392496, 'IN3102'),
(13991971, 'IN3102'),
(18912216, 'IN3102'),
(7391773, 'IN3102'),
(5260810, 'IN4402'),
(16403903, 'IN4402'),
(16385182, 'IN4402'),
(13527711, 'IN4402'),
(18356682, 'IN4402'),
(7391773, 'IN4402'),
(5260810, 'IN4404'),
(16403903, 'IN4404'),
(16385182, 'IN4404'),
(13527711, 'IN4404'),
(18356682, 'IN4404'),
(7391773, 'IN4404'),
(9629702, 'IN1101'),
(10848316, 'IN1101'),
(16385182, 'IN1101'),
(30395804, 'IN1101'),
(15351688, 'IN1101'),
(10844463, 'IN1101'),
(9629702, 'IN1102'),
(10848316, 'IN1102'),
(16385182, 'IN1102'),
(30395804, 'IN1102'),
(15351688, 'IN1102'),
(10844463, 'IN1102'),
(9629702, 'IN1104'),
(10848316, 'IN1104'),
(16385182, 'IN1104'),
(30395804, 'IN1104'),
(15351688, 'IN1104'),
(10844463, 'IN1104'),
(18103232, 'IN1113'),
(23316126, 'IN1113'),
(29880797, 'IN1113'),
(15170003, 'IN1113'),
(9627295, 'IN1113'),
(9118178, 'IN1113'),
(10778236, 'IN1113'),
(16403903, 'IN2114'),
(10846157, 'IN2114'),
(24418577, 'IN2114'),
(18103232, 'IN2114'),
(14677589, 'IN2114'),
(13188691, 'IN2114'),
(9555514, 'IN2114'),
(16403903, 'IN2123'),
(10846157, 'IN2123'),
(24418577, 'IN2123'),
(18103232, 'IN2123'),
(14677589, 'IN2123'),
(13188691, 'IN2123'),
(9555514, 'IN2123');

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

--
-- Volcado de datos para la tabla `tbl_actividad`
--

INSERT INTO `tbl_actividad` (`doc_cedula`, `act_academicas`, `act_creacion_intelectual`, `act_integracion_comunidad`, `act_gestion_academica`, `act_otras`, `act_estado`) VALUES
(3759671, 10, 5, 0, 5, 5, 1),
(4374529, 10, 5, 0, 5, 5, 1),
(5260810, 10, 5, 0, 5, 5, 1),
(6269299, 10, 5, 0, 5, 5, 1),
(7391773, 10, 5, 0, 5, 5, 1),
(7392496, 20, 0, 0, 0, 0, 1),
(7404027, 10, 5, 0, 5, 5, 1),
(7415067, 10, 5, 0, 5, 5, 1),
(7423485, 10, 5, 0, 5, 5, 1),
(7423486, 10, 5, 0, 5, 5, 1),
(7424546, 10, 5, 0, 5, 5, 1),
(7439117, 10, 5, 0, 5, 5, 1),
(9118178, 10, 5, 0, 5, 5, 1),
(9540060, 10, 5, 0, 5, 5, 1),
(9541953, 10, 5, 0, 5, 5, 1),
(9555514, 10, 5, 0, 5, 5, 1),
(9602562, 10, 5, 0, 5, 5, 1),
(9619518, 10, 5, 0, 5, 5, 1),
(9627295, 10, 5, 0, 5, 5, 1),
(9629702, 10, 5, 0, 5, 5, 1),
(10723015, 10, 5, 0, 5, 5, 1),
(10775753, 10, 5, 0, 5, 5, 1),
(10778236, 10, 5, 0, 5, 5, 1),
(10844463, 10, 5, 0, 5, 5, 1),
(10846157, 10, 5, 0, 5, 5, 1),
(10847351, 10, 5, 0, 5, 5, 1),
(10848316, 10, 5, 0, 5, 5, 1),
(10956121, 10, 5, 0, 5, 5, 1),
(11264888, 10, 5, 0, 5, 5, 1),
(11898335, 10, 5, 0, 5, 5, 1),
(12045627, 10, 5, 0, 5, 5, 1),
(12701387, 10, 5, 0, 5, 5, 1),
(12849928, 10, 5, 0, 5, 5, 1),
(13188691, 10, 5, 0, 5, 5, 1),
(13527711, 10, 5, 0, 5, 5, 1),
(13695847, 10, 5, 0, 5, 5, 1),
(13991250, 10, 5, 0, 5, 5, 1),
(13991971, 10, 5, 0, 5, 5, 1),
(14091124, 10, 5, 0, 5, 5, 1),
(14159756, 10, 5, 0, 5, 5, 1),
(14292469, 10, 5, 0, 5, 5, 1),
(14677589, 10, 5, 0, 5, 5, 1),
(15170003, 10, 5, 0, 5, 5, 1),
(15351688, 10, 5, 0, 5, 5, 1),
(15693145, 10, 5, 0, 5, 5, 1),
(16385182, 10, 5, 0, 5, 5, 1),
(16403903, 10, 5, 0, 5, 5, 1),
(17354607, 10, 5, 0, 5, 5, 1),
(18103232, 10, 5, 0, 5, 5, 1),
(18356682, 10, 5, 0, 5, 5, 1),
(18912216, 10, 5, 0, 5, 5, 1),
(20351422, 10, 5, 0, 5, 5, 1),
(23316126, 10, 5, 0, 5, 5, 1),
(24418577, 10, 5, 0, 5, 5, 1),
(25471240, 10, 5, 0, 5, 5, 1),
(26197135, 10, 5, 0, 5, 5, 1),
(29517943, 10, 5, 0, 5, 5, 1),
(29880797, 10, 5, 0, 5, 5, 1),
(30088284, 10, 5, 0, 5, 5, 1),
(30395804, 10, 5, 0, 5, 5, 1),
(3759671, 10, 5, 0, 5, 5, 1),
(4374529, 10, 5, 0, 5, 5, 1),
(5260810, 10, 5, 0, 5, 5, 1),
(6269299, 10, 5, 0, 5, 5, 1),
(7391773, 10, 5, 0, 5, 5, 1),
(7392496, 20, 0, 0, 0, 0, 1),
(7404027, 10, 5, 0, 5, 5, 1),
(7415067, 10, 5, 0, 5, 5, 1),
(7423485, 10, 5, 0, 5, 5, 1),
(7423486, 10, 5, 0, 5, 5, 1),
(7424546, 10, 5, 0, 5, 5, 1),
(7439117, 10, 5, 0, 5, 5, 1),
(9118178, 10, 5, 0, 5, 5, 1),
(9540060, 10, 5, 0, 5, 5, 1),
(9541953, 10, 5, 0, 5, 5, 1),
(9555514, 10, 5, 0, 5, 5, 1),
(9602562, 10, 5, 0, 5, 5, 1),
(9619518, 10, 5, 0, 5, 5, 1),
(9627295, 10, 5, 0, 5, 5, 1),
(9629702, 10, 5, 0, 5, 5, 1),
(10723015, 10, 5, 0, 5, 5, 1),
(10775753, 10, 5, 0, 5, 5, 1),
(10778236, 10, 5, 0, 5, 5, 1),
(10844463, 10, 5, 0, 5, 5, 1),
(10846157, 10, 5, 0, 5, 5, 1),
(10847351, 10, 5, 0, 5, 5, 1),
(10848316, 10, 5, 0, 5, 5, 1),
(10956121, 10, 5, 0, 5, 5, 1),
(11264888, 10, 5, 0, 5, 5, 1),
(11898335, 10, 5, 0, 5, 5, 1),
(12045627, 10, 5, 0, 5, 5, 1),
(12701387, 10, 5, 0, 5, 5, 1),
(12849928, 10, 5, 0, 5, 5, 1),
(13188691, 10, 5, 0, 5, 5, 1),
(13527711, 10, 5, 0, 5, 5, 1),
(13695847, 10, 5, 0, 5, 5, 1),
(13991250, 10, 5, 0, 5, 5, 1),
(13991971, 10, 5, 0, 5, 5, 1),
(14091124, 10, 5, 0, 5, 5, 1),
(14159756, 10, 5, 0, 5, 5, 1),
(14292469, 10, 5, 0, 5, 5, 1),
(14677589, 10, 5, 0, 5, 5, 1),
(15170003, 10, 5, 0, 5, 5, 1),
(15351688, 10, 5, 0, 5, 5, 1),
(15693145, 10, 5, 0, 5, 5, 1),
(16385182, 10, 5, 0, 5, 5, 1),
(16403903, 10, 5, 0, 5, 5, 1),
(17354607, 10, 5, 0, 5, 5, 1),
(18103232, 10, 5, 0, 5, 5, 1),
(18356682, 10, 5, 0, 5, 5, 1),
(18912216, 10, 5, 0, 5, 5, 1),
(20351422, 10, 5, 0, 5, 5, 1),
(23316126, 10, 5, 0, 5, 5, 1),
(24418577, 10, 5, 0, 5, 5, 1),
(25471240, 10, 5, 0, 5, 5, 1),
(26197135, 10, 5, 0, 5, 5, 1),
(29517943, 10, 5, 0, 5, 5, 1),
(29880797, 10, 5, 0, 5, 5, 1),
(30088284, 10, 5, 0, 5, 5, 1),
(30395804, 10, 5, 0, 5, 5, 1),
(3759671, 10, 5, 0, 5, 5, 1),
(4374529, 10, 5, 0, 5, 5, 1),
(5260810, 10, 5, 0, 5, 5, 1),
(6269299, 10, 5, 0, 5, 5, 1),
(7391773, 10, 5, 0, 5, 5, 1),
(7392496, 20, 0, 0, 0, 0, 1),
(7404027, 10, 5, 0, 5, 5, 1),
(7415067, 10, 5, 0, 5, 5, 1),
(7423485, 10, 5, 0, 5, 5, 1),
(7423486, 10, 5, 0, 5, 5, 1),
(7424546, 10, 5, 0, 5, 5, 1),
(7439117, 10, 5, 0, 5, 5, 1),
(9118178, 10, 5, 0, 5, 5, 1),
(9540060, 10, 5, 0, 5, 5, 1),
(9541953, 10, 5, 0, 5, 5, 1),
(9555514, 10, 5, 0, 5, 5, 1),
(9602562, 10, 5, 0, 5, 5, 1),
(9619518, 10, 5, 0, 5, 5, 1),
(9627295, 10, 5, 0, 5, 5, 1),
(9629702, 10, 5, 0, 5, 5, 1),
(10723015, 10, 5, 0, 5, 5, 1),
(10775753, 10, 5, 0, 5, 5, 1),
(10778236, 10, 5, 0, 5, 5, 1),
(10844463, 10, 5, 0, 5, 5, 1),
(10846157, 10, 5, 0, 5, 5, 1),
(10847351, 10, 5, 0, 5, 5, 1),
(10848316, 10, 5, 0, 5, 5, 1),
(10956121, 10, 5, 0, 5, 5, 1),
(11264888, 10, 5, 0, 5, 5, 1),
(11898335, 10, 5, 0, 5, 5, 1),
(12045627, 10, 5, 0, 5, 5, 1),
(12701387, 10, 5, 0, 5, 5, 1),
(12849928, 10, 5, 0, 5, 5, 1),
(13188691, 10, 5, 0, 5, 5, 1),
(13527711, 10, 5, 0, 5, 5, 1),
(13695847, 10, 5, 0, 5, 5, 1),
(13991250, 10, 5, 0, 5, 5, 1),
(13991971, 10, 5, 0, 5, 5, 1),
(14091124, 10, 5, 0, 5, 5, 1),
(14159756, 10, 5, 0, 5, 5, 1),
(14292469, 10, 5, 0, 5, 5, 1),
(14677589, 10, 5, 0, 5, 5, 1),
(15170003, 10, 5, 0, 5, 5, 1),
(15351688, 10, 5, 0, 5, 5, 1),
(15693145, 10, 5, 0, 5, 5, 1),
(16385182, 10, 5, 0, 5, 5, 1),
(16403903, 10, 5, 0, 5, 5, 1),
(17354607, 10, 5, 0, 5, 5, 1),
(18103232, 10, 5, 0, 5, 5, 1),
(18356682, 10, 5, 0, 5, 5, 1),
(18912216, 10, 5, 0, 5, 5, 1),
(20351422, 10, 5, 0, 5, 5, 1),
(23316126, 10, 5, 0, 5, 5, 1),
(24418577, 10, 5, 0, 5, 5, 1),
(25471240, 10, 5, 0, 5, 5, 1),
(26197135, 10, 5, 0, 5, 5, 1),
(29517943, 10, 5, 0, 5, 5, 1),
(29880797, 10, 5, 0, 5, 5, 1),
(30088284, 10, 5, 0, 5, 5, 1),
(30395804, 10, 5, 0, 5, 5, 1),
(3759671, 10, 5, 0, 5, 5, 1),
(4374529, 10, 5, 0, 5, 5, 1),
(5260810, 10, 5, 0, 5, 5, 1),
(6269299, 10, 5, 0, 5, 5, 1),
(7391773, 10, 5, 0, 5, 5, 1),
(7392496, 20, 0, 0, 0, 0, 1),
(7404027, 10, 5, 0, 5, 5, 1),
(7415067, 10, 5, 0, 5, 5, 1),
(7423485, 10, 5, 0, 5, 5, 1),
(7423486, 10, 5, 0, 5, 5, 1),
(7424546, 10, 5, 0, 5, 5, 1),
(7439117, 10, 5, 0, 5, 5, 1),
(9118178, 10, 5, 0, 5, 5, 1),
(9540060, 10, 5, 0, 5, 5, 1),
(9541953, 10, 5, 0, 5, 5, 1),
(9555514, 10, 5, 0, 5, 5, 1),
(9602562, 10, 5, 0, 5, 5, 1),
(9619518, 10, 5, 0, 5, 5, 1),
(9627295, 10, 5, 0, 5, 5, 1),
(9629702, 10, 5, 0, 5, 5, 1),
(10723015, 10, 5, 0, 5, 5, 1),
(10775753, 10, 5, 0, 5, 5, 1),
(10778236, 10, 5, 0, 5, 5, 1),
(10844463, 10, 5, 0, 5, 5, 1),
(10846157, 10, 5, 0, 5, 5, 1),
(10847351, 10, 5, 0, 5, 5, 1),
(10848316, 10, 5, 0, 5, 5, 1),
(10956121, 10, 5, 0, 5, 5, 1),
(11264888, 10, 5, 0, 5, 5, 1),
(11898335, 10, 5, 0, 5, 5, 1),
(12045627, 10, 5, 0, 5, 5, 1),
(12701387, 10, 5, 0, 5, 5, 1),
(12849928, 10, 5, 0, 5, 5, 1),
(13188691, 10, 5, 0, 5, 5, 1),
(13527711, 10, 5, 0, 5, 5, 1),
(13695847, 10, 5, 0, 5, 5, 1),
(13991250, 10, 5, 0, 5, 5, 1),
(13991971, 10, 5, 0, 5, 5, 1),
(14091124, 10, 5, 0, 5, 5, 1),
(14159756, 10, 5, 0, 5, 5, 1),
(14292469, 10, 5, 0, 5, 5, 1),
(14677589, 10, 5, 0, 5, 5, 1),
(15170003, 10, 5, 0, 5, 5, 1),
(15351688, 10, 5, 0, 5, 5, 1),
(15693145, 10, 5, 0, 5, 5, 1),
(16385182, 10, 5, 0, 5, 5, 1),
(16403903, 10, 5, 0, 5, 5, 1),
(17354607, 10, 5, 0, 5, 5, 1),
(18103232, 10, 5, 0, 5, 5, 1),
(18356682, 10, 5, 0, 5, 5, 1),
(18912216, 10, 5, 0, 5, 5, 1),
(20351422, 10, 5, 0, 5, 5, 1),
(23316126, 10, 5, 0, 5, 5, 1),
(24418577, 10, 5, 0, 5, 5, 1),
(25471240, 10, 5, 0, 5, 5, 1),
(26197135, 10, 5, 0, 5, 5, 1),
(29517943, 10, 5, 0, 5, 5, 1),
(29880797, 10, 5, 0, 5, 5, 1),
(30088284, 10, 5, 0, 5, 5, 1),
(30395804, 10, 5, 0, 5, 5, 1),
(3759671, 10, 5, 0, 5, 5, 1),
(4374529, 10, 5, 0, 5, 5, 1),
(5260810, 10, 5, 0, 5, 5, 1),
(6269299, 10, 5, 0, 5, 5, 1),
(7391773, 10, 5, 0, 5, 5, 1),
(7392496, 20, 0, 0, 0, 0, 1),
(7404027, 10, 5, 0, 5, 5, 1),
(7415067, 10, 5, 0, 5, 5, 1),
(7423485, 10, 5, 0, 5, 5, 1),
(7423486, 10, 5, 0, 5, 5, 1),
(7424546, 10, 5, 0, 5, 5, 1),
(7439117, 10, 5, 0, 5, 5, 1),
(9118178, 10, 5, 0, 5, 5, 1),
(9540060, 10, 5, 0, 5, 5, 1),
(9541953, 10, 5, 0, 5, 5, 1),
(9555514, 10, 5, 0, 5, 5, 1),
(9602562, 10, 5, 0, 5, 5, 1),
(9619518, 10, 5, 0, 5, 5, 1),
(9627295, 10, 5, 0, 5, 5, 1),
(9629702, 10, 5, 0, 5, 5, 1),
(10723015, 10, 5, 0, 5, 5, 1),
(10775753, 10, 5, 0, 5, 5, 1),
(10778236, 10, 5, 0, 5, 5, 1),
(10844463, 10, 5, 0, 5, 5, 1),
(10846157, 10, 5, 0, 5, 5, 1),
(10847351, 10, 5, 0, 5, 5, 1),
(10848316, 10, 5, 0, 5, 5, 1),
(10956121, 10, 5, 0, 5, 5, 1),
(11264888, 10, 5, 0, 5, 5, 1),
(11898335, 10, 5, 0, 5, 5, 1),
(12045627, 10, 5, 0, 5, 5, 1),
(12701387, 10, 5, 0, 5, 5, 1),
(12849928, 10, 5, 0, 5, 5, 1),
(13188691, 10, 5, 0, 5, 5, 1),
(13527711, 10, 5, 0, 5, 5, 1),
(13695847, 10, 5, 0, 5, 5, 1),
(13991250, 10, 5, 0, 5, 5, 1),
(13991971, 10, 5, 0, 5, 5, 1),
(14091124, 10, 5, 0, 5, 5, 1),
(14159756, 10, 5, 0, 5, 5, 1),
(14292469, 10, 5, 0, 5, 5, 1),
(14677589, 10, 5, 0, 5, 5, 1),
(15170003, 10, 5, 0, 5, 5, 1),
(15351688, 10, 5, 0, 5, 5, 1),
(15693145, 10, 5, 0, 5, 5, 1),
(16385182, 10, 5, 0, 5, 5, 1),
(16403903, 10, 5, 0, 5, 5, 1),
(17354607, 10, 5, 0, 5, 5, 1),
(18103232, 10, 5, 0, 5, 5, 1),
(18356682, 10, 5, 0, 5, 5, 1),
(18912216, 10, 5, 0, 5, 5, 1),
(20351422, 10, 5, 0, 5, 5, 1),
(23316126, 10, 5, 0, 5, 5, 1),
(24418577, 10, 5, 0, 5, 5, 1),
(25471240, 10, 5, 0, 5, 5, 1),
(26197135, 10, 5, 0, 5, 5, 1),
(29517943, 10, 5, 0, 5, 5, 1),
(29880797, 10, 5, 0, 5, 5, 1),
(30088284, 10, 5, 0, 5, 5, 1),
(30395804, 10, 5, 0, 5, 5, 1),
(31212121, 1, 2, 3, 12, 0, 0),
(2121213, 0, 0, 0, 0, 0, 1),
(2134123, 0, 0, 0, 0, 0, 0),
(3124123, 0, 0, 0, 0, 0, 1),
(3412341, 0, 0, 0, 0, 0, 1),
(1123345, 0, 0, 0, 0, 0, 1),
(34235355, 0, 0, 0, 0, 0, 1),
(23232323, 5, 0, 0, 0, 0, 1);

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
(2025, 'intensivo', 1, 0),
(2025, 'regular', 1, 1),
(2026, 'intensivo', 0, 0),
(2026, 'regular', 0, 0);

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

--
-- Volcado de datos para la tabla `tbl_aprobados`
--

INSERT INTO `tbl_aprobados` (`apro_estado`, `apro_cantidad`, `uc_codigo`, `sec_codigo`, `ani_anio`, `ani_tipo`, `fase_numero`, `doc_cedula`) VALUES
(0, 0, 'PIACA090103', 'IN1113', 2025, 'regular', 0, 18103232),
(0, 0, 'PIELE072203', 'IN2101', 2025, 'regular', 0, 30088284),
(0, 0, 'PIPST234109', 'IN1101', 2025, 'regular', 0, 10848316),
(0, 0, 'PIPST234109', 'IN1102', 2025, 'regular', 0, 10848316),
(0, 0, 'PIPST234109', 'IN1104', 2025, 'regular', 0, 10848316),
(0, 0, 'PIPST234309', 'IN3101', 2025, 'regular', 0, 7391773),
(0, 0, 'PIPST234309', 'IN3102', 2025, 'regular', 0, 7391773),
(1, 0, 'PIELE078303', 'IN3101', 2025, 'regular', 0, 25471240),
(1, 0, 'PIELE078303', 'IN3102', 2025, 'regular', 0, 25471240),
(0, 0, 'PIACA090103', 'IN1113', 2025, 'regular', 0, 18103232);

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
('Arquite', 0, 'hacei'),
('arquitecto', 0, 'nombrearqui'),
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
('CATEGORIA12', 0, 'CATEGORIAS'),
('Insstit', 0, 'catteroshxs'),
('Instructor', 1, 'Instructor'),
('Pasante', 0, 'En prueba de trabajo'),
('PRUEBA', 0, 'HOLAD'),
('RIJALSS', 0, 'CARRASQUERO'),
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
('Actividades Acreditables I, II', 1, 0),
('Administración de Base de Dato', 1, 0),
('Algorítmica y Programación', 1, 0),
('Arquitectura del Computador', 1, 0),
('Auditoria de Sistemas', 1, 0),
('Base de Datos', 1, 0),
('Centro de Estudio Investigació', 1, 0),
('Currículo', 0, 0),
('Deporte', 1, 0),
('Doctorados', 1, 0),
('Educación Municipalizada', 1, 0),
('Eje Epistemológico', 1, 0),
('Eje Estético Lúdico', 1, 0),
('Eje Etico Político', 1, 0),
('EJEMPLOW', 0, 4),
('Electiva 1', 1, 0),
('Electiva II', 1, 0),
('Electiva III', 1, 0),
('Electiva IV', 1, 0),
('EMTICL', 1, 0),
('Formación Crítica TI, TII, TII', 1, 0),
('Gestión de Proyectos', 1, 0),
('Gestión TIC', 1, 0),
('HOLAAA', 0, 7),
('Holamund', 0, 3),
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
('otropey', 0, 4),
('PNFA-PNFI', 1, 0),
('Preparaduría', 1, 0),
('proeeeto', 0, 8),
('Programación', 1, 0),
('Proyecto Sociotecnológico I', 1, 0),
('Proyecto Sociotecnológico II', 1, 0),
('Proyecto Sociotecnológico III', 1, 0),
('Proyecto Sociotecnológico IV', 1, 0),
('PRUEBA', 0, 5),
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
  `cat_nombre` varchar(30) NOT NULL,
  `doc_prefijo` char(1) NOT NULL DEFAULT '',
  `doc_cedula` int(11) NOT NULL,
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

INSERT INTO `tbl_docente` (`cat_nombre`, `doc_prefijo`, `doc_cedula`, `doc_nombre`, `doc_apellido`, `doc_correo`, `doc_dedicacion`, `doc_condicion`, `doc_estado`, `doc_observacion`, `doc_ingreso`, `doc_anio_concurso`, `doc_tipo_concurso`) VALUES
('Instructor', 'V', 3759671, 'Norma', 'Barreto', 'prueba@pnfi.edu.ve', 'Exclusiva', 'Ordinario', 1, '- Integra Comisión Organización docente - Integrante Comisión Currículo - Integra Com. Seguimiento Egresado', '1998-03-10', '2003-06-01', 'Oposición'),
('Instructor', 'V', 4374529, 'Jackob', 'Jiménez', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '', '2000-03-25', '2000-03-01', 'Credenciales'),
('Instructor', 'V', 5260810, 'José', 'Tillero', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2016-07-19', NULL, NULL),
('Instructor', 'V', 6269299, 'Francys', 'Barreto', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2006-01-01', NULL, NULL),
('Instructor', 'V', 7391773, 'Edecio', 'Freitez', 'edeciofreitez@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 7392496, 'Pura', 'Castillo', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2009-10-05', '2019-11-01', 'Oposición'),
('Instructor', 'V', 7404027, 'Oswaldo', 'Aparicio', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, 'Solicitará Permiso por viaje', '2004-01-19', '2003-04-01', 'Oposicion'),
('Instructor', 'V', 7415067, 'Lisbeth', 'Oropeza', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, 'Docente con descarga académico-administrativa del 50% por Estudios de Doctorado', '2004-01-19', '2003-06-01', 'Oposicion'),
('Instructor', 'V', 7423485, 'Ingrid', 'Figueroa', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '- Docente Enlace Comisión Seguimiento al Egresado - Vicerrectorado Académico - Integra Comisión Grupo de Trabajo de Estudiantes - Adscrito al PNFI desde 15-01-2020', '2008-01-18', '2007-01-01', 'Credenciales'),
('Instructor', 'V', 7423486, 'Lérida', 'Figueroa', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2022-10-25', NULL, NULL),
('Instructor', 'V', 7424546, 'Paola', 'Ruggero', 'paolarugsg@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 7439117, 'Sullín', 'Santaella', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '- Descarga Completa por (Dpto EMTICL) - Docente Diplomado EMTICL - Facilita Cursos de Posgrado - - Desarrolladora Aulas Virtuales en DEA', '2006-01-09', '2013-04-01', 'Oposicion'),
('Instructor', 'V', 9118178, 'Iris', 'Daza', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '', '2010-07-26', '2010-06-01', 'Credenciales'),
('Instructor', 'V', 9540060, 'Maribel', 'Durán', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2019-09-24', NULL, NULL),
('Instructor', 'V', 9541953, 'Nelson', 'Montilla', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado', 1, 'Carga administrativa contratado por tiempo determinado. Cumplira sus horas en la unidad de sistemas de la uptaeb. 25/10/2021 Hasta el -.......', '2020-01-27', NULL, NULL),
('Instructor', 'V', 9555514, 'Lisbeth', 'Flores', 'florbeth08@yahoo.es', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 9602562, 'Samary', 'Páez', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '- Solicito Permiso x Contrato Colectivo para elaboración de trabajo de Tesis Doctoral EN ESPERA DE DEFINICION DE ESTADO', '2006-05-28', '2013-04-01', 'Oposicion'),
('Instructor', 'V', 9619518, 'Sonia', 'Córdoba', 'prueba@pnfi.edu.ve', 'Exclusiva', 'Ordinario', 1, '- Integra la Comisión de EMTICL del PNFI - Colabora con la Educación Municipalizada PNFI (Misión Sucre) Directora de PNFI', '2003-06-16', '2003-06-01', 'Oposicion'),
('Instructor', 'V', 9627295, 'Douglas', 'Nelo', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2016-06-20', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 9629702, 'Ruben', 'Godoy', 'prueba@pnfi.edu.ve', 'Exclusiva', 'Suplente', 1, '', '2022-10-25', NULL, NULL),
('Instructor', 'V', 10723015, 'Sol', 'Hernández', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '- Docente Enlace Revista Digital del PNFI - Integrante de Comisión de Trayecto I. - Jefe de Despacho de la UPTAEB - Subcoordinadora Línea Institucional Gestión TIC ( Pasa a Agregado a partir 31-10-18) - Representa al PNFI ante Sistema Nacional de For', '2013-01-15', '2014-06-01', 'Credenciales'),
('Instructor', 'V', 10775753, 'Wilmar', 'Marrufo', 'prueba@pnfi.edu.ve', 'Exclusiva', 'Ordinario', 1, '', '2008-01-17', '2013-04-01', 'Oposicion'),
('Instructor', 'V', 10778236, 'Alexis', 'Dorante', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2016-06-20', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 10844463, 'Edith', 'Urdaneta', 'edyiurav@hotmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 10846157, 'Juan', 'Jiménez', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2016-09-23', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 10847351, 'Leany', 'González', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '1998-03-10', '2003-04-01', 'Oposicion'),
('Instructor', 'V', 10848316, 'Enrique', 'Ramos', 'uptaebenrique@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 10956121, 'Lissette', 'Torrealba', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '- de la UPTAEB - del PNFI -Integra de PNFI.', '2013-06-15', '2014-06-01', 'Credenciales'),
('Instructor', 'V', 11264888, 'Lerida', 'Figueroa', 'leryfigueroa2019@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 11898335, 'Eduardo', 'Venegas', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2015-02-04', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 12045627, 'Pedro', 'Castro', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2016-07-19', NULL, NULL),
('Instructor', 'V', 12701387, 'Fidel', 'Aguilar', 'fidelaguilar3000@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 12849928, 'Darwin', 'Velásquez', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '', '2013-01-15', '2014-06-01', 'Credenciales'),
('Instructor', 'V', 13188691, 'Ellery', 'López', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '- Integra Comisión del PNFI', '2017-01-30', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 13527711, 'Marling', 'Brito', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2017-10-02', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 13695847, 'Angelica', 'Rojas', 'angelicarojas@iujo.edu.ve', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 13991250, 'Ligia', 'Durán', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2017-02-06', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 13991971, 'Angelismar', 'Terán', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2016-01-01', NULL, NULL),
('Instructor', 'V', 14091124, 'Jehamar', 'Lovera', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '', '2013-01-15', '2014-06-01', 'Credenciales'),
('Instructor', 'V', 14159756, 'Aracelys', 'Terán', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '', '2012-06-15', '2014-06-01', 'Credenciales'),
('Instructor', 'V', 14292469, 'María', 'Mendoza', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2016-06-20', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 14677589, 'Miguel', 'Rodríguez', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '- Integrante de la Comisión de Economía Comunal', '2015-06-29', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 15170003, 'Aida', 'Sivira', 'prueba@pnfi.edu.veE', 'Tiempo Completo', 'Ordinario', 1, '', '2016-07-06', '2019-11-01', 'Oposición'),
('Instructor', 'V', 15351688, 'Francis', 'Rodriguez', 'francisyrm2601@hotmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 15693145, 'Indira', 'González', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2019-10-25', NULL, NULL),
('Instructor', 'V', 16385182, 'Hermes', 'Gordillo', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '', '2013-03-02', '2014-06-01', 'Credenciales'),
('Instructor', 'V', 16403903, 'Orlando', 'Guerra', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2012-01-04', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 17354607, 'María', 'Linares', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2012-07-12', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 18103232, 'Carlos', 'Moreno', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, 'Docente del PNF Deporte, atiende TC horas en PNFI', '2017-04-20', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 18356682, 'Judith', 'Gomez', 'Rebecagomez1808@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 18912216, 'Cesar', 'Perez', 'cesarupf72@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 20351422, 'Kenlimar', 'Alvarado', 'docenteuniversidad@uptaeb.com', 'Tiempo Completo', 'Ordinario', 1, '', '2025-08-12', NULL, 'Oposición'),
('Instructor', 'V', 23316126, 'Nangelys', 'Oviedo', 'nangelisg@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 24418577, 'Douglas', 'Ramos', 'douglasramos0210@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 25471240, 'José', 'Sequera', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2021-10-25', NULL, NULL),
('Instructor', 'V', 26197135, 'Maria', 'Diaz', 'mjcazorla1997@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 29517943, 'Sabrina', 'Colmenarez', 'sabrinacolmenarez16@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 29880797, 'Davianys', 'Guerrero', 'davianystra@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 30088284, 'Jose', 'Escalona', 'joseescalona1505@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 30395804, 'Jhoanly', 'Hernandez', 'duranjhoa16.pnfi@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL);

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
('ejesonyooo', 'ejmjsdosm', 0),
('Epistemológico', 'Eje integrador centrado en la construcción del conocimiento científico y tecnológico', 1),
('Epitetoto', 'holaaa', 0),
('Estético Lúdico', 'Eje integrador dedicado a actividades culturales, deportivas y de desarrollo personal', 1),
('Ético Político-Socio Ambiental', 'Eje integrador que combina aspectos éticos, políticos y ambientales', 1),
('Ético-Político', 'Eje integrador enfocado en la formación ciudadana, valores éticos y participación política', 1),
('Politico ', 'Eje politico', 0),
('prima', 'pamaewehsd', 0),
('PRUEBAS', 'PRUEBASQW', 0),
('Trabajo-Productivo', 'Eje integrador orientado a la vinculación con el sector productivo y el desarrollo de proyectos', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_espacio`
--

CREATE TABLE `tbl_espacio` (
  `esp_numero` varchar(30) NOT NULL,
  `esp_tipo` varchar(30) NOT NULL,
  `esp_estado` tinyint(1) NOT NULL DEFAULT 1,
  `esp_edificio` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_espacio`
--

INSERT INTO `tbl_espacio` (`esp_numero`, `esp_tipo`, `esp_estado`, `esp_edificio`) VALUES
('1', 'Aula', 1, 'Giraluna'),
('1', 'Aula', 1, 'Hilandera'),
('1', 'Aula', 1, 'Orinoco'),
('1', 'Aula', 1, 'Rio 7 Estrellas'),
('10', 'Aula', 1, 'Giraluna'),
('10', 'Aula', 1, 'Hilandera'),
('10', 'Aula', 1, 'Rio 7 Estrellas'),
('11', 'Aula', 1, 'Giraluna'),
('11', 'Aula', 1, 'Hilandera'),
('11', 'Aula', 1, 'Rio 7 Estrellas'),
('12', 'Aula', 1, 'Giraluna'),
('12', 'Aula', 1, 'Hilandera'),
('12', 'Aula', 1, 'Rio 7 Estrellas'),
('13', 'Aula', 1, 'Giraluna'),
('13', 'Aula', 1, 'Hilandera'),
('13', 'Aula', 1, 'Rio 7 Estrellas'),
('14', 'Aula', 1, 'Giraluna'),
('14', 'Aula', 1, 'Hilandera'),
('14', 'Aula', 1, 'Rio 7 Estrellas'),
('15', 'Aula', 1, 'Giraluna'),
('15', 'Aula', 1, 'Hilandera'),
('15', 'Aula', 1, 'Rio 7 Estrellas'),
('16', 'Aula', 1, 'Giraluna'),
('16', 'Aula', 1, 'Rio 7 Estrellas'),
('17', 'Aula', 1, 'Giraluna'),
('18', 'Aula', 1, 'Giraluna'),
('19', 'Aula', 1, 'Giraluna'),
('2', 'Aula', 1, 'Giraluna'),
('2', 'Aula', 1, 'Hilandera'),
('2', 'Aula', 1, 'Orinoco'),
('2', 'Aula', 1, 'Rio 7 Estrellas'),
('20', 'Aula', 1, 'Giraluna'),
('21', 'Aula', 1, 'Giraluna'),
('22', 'Aula', 1, 'Giraluna'),
('22', 'Aula', 1, 'Hilandera'),
('22', 'Laboratorio', 1, 'Hilandera'),
('23', 'Aula', 1, 'Giraluna'),
('24', 'Aula', 1, 'Giraluna'),
('25', 'Aula', 1, 'Giraluna'),
('26', 'Aula', 1, 'Giraluna'),
('27', 'Aula', 1, 'Giraluna'),
('3', 'Aula', 1, 'Hilandera'),
('3', 'Aula', 1, 'Orinoco'),
('3', 'Aula', 1, 'Rio 7 Estrellas'),
('3', 'Laboratorio', 1, 'Giraluna'),
('34', 'Aula', 0, 'Giraluna'),
('4', 'Aula', 1, 'Hilandera'),
('4', 'Aula', 1, 'Orinoco'),
('4', 'Aula', 1, 'Rio 7 Estrellas'),
('4', 'Laboratorio', 1, 'Rio 7 Estrellas'),
('46', 'Aula', 0, 'Giraluna'),
('5', 'Aula', 1, 'Hilandera'),
('5', 'Aula', 1, 'Rio 7 Estrellas'),
('5', 'Laboratorio', 1, 'Giraluna'),
('6', 'Aula', 1, 'Hilandera'),
('6', 'Aula', 1, 'Orinoco'),
('6', 'Aula', 1, 'Rio 7 Estrellas'),
('6', 'Laboratorio', 1, 'Hilandera'),
('7', 'Aula', 1, 'Hilandera'),
('7', 'Aula', 1, 'Orinoco'),
('7', 'Aula', 1, 'Rio 7 Estrellas'),
('8', 'Aula', 1, 'Hilandera'),
('8', 'Aula', 1, 'Orinoco'),
('9', 'Aula', 1, 'Giraluna'),
('9', 'Aula', 1, 'Hilandera'),
('9', 'Aula', 1, 'Orinoco'),
('9', 'Aula', 1, 'Rio 7 Estrellas'),
('Hardware', 'Laboratorio', 1, 'Hilandera'),
('Software', 'Laboratorio', 1, 'Hilandera');

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
(2025, 'regular', 2, '2025-06-23', '2025-11-27'),
(2025, 'intensivo', 1, '2025-10-01', '2025-10-31'),
(2026, 'regular', 1, '2026-01-01', '2026-01-30'),
(2026, 'regular', 2, '2026-02-01', '2026-02-19'),
(2026, 'intensivo', 1, '2026-01-08', '2026-01-14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_horario`
--

CREATE TABLE `tbl_horario` (
  `hor_estado` tinyint(1) DEFAULT NULL,
  `sec_codigo` varchar(30) DEFAULT NULL,
  `tur_nombre` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_horario`
--

INSERT INTO `tbl_horario` (`hor_estado`, `sec_codigo`, `tur_nombre`) VALUES
(1, 'IN0423', 'Mañana'),
(1, 'IN1103', 'Mañana'),
(1, 'IN1123', 'Mañana'),
(1, 'IN1133', 'Mañana'),
(1, 'IN1403', 'Mañana'),
(1, 'IN1203', 'Tarde'),
(1, 'IN1143', 'Mañana'),
(1, 'IN1213', 'Tarde'),
(1, 'IN2103', 'Mañana'),
(1, 'IN2113', 'Mañana'),
(1, 'IN2133', 'Mañana'),
(1, 'IN2403', 'Mañana'),
(1, 'IN3103', 'Mañana'),
(1, 'IN3104', 'Mañana'),
(1, 'IN3113', 'Mañana'),
(1, 'IN1202', 'Tarde'),
(1, 'IN1204', 'Tarde'),
(1, 'IN1214', 'Tarde'),
(1, 'IN2101', 'Mañana'),
(1, 'IN2102', 'Mañana'),
(1, 'IN2104', 'Mañana'),
(1, 'IN3101', 'Mañana'),
(1, 'IN3102', 'Mañana'),
(1, 'IN4402', 'Mañana'),
(1, 'IN4404', 'Mañana'),
(1, 'IN1101', 'Mañana'),
(1, 'IN1102', 'Mañana'),
(1, 'IN1104', 'Mañana'),
(1, 'IN1113', 'Mañana'),
(1, 'IN2114', 'Mañana'),
(1, 'IN2123', 'Mañana'),
(1, 'IN0413', 'Mañana');

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
  `per_apertura` date NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `per_fase` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_per`
--

INSERT INTO `tbl_per` (`ani_anio`, `per_apertura`, `ani_tipo`, `per_fase`) VALUES
(2025, '2025-06-23', 'regular', 1),
(2026, '2026-02-01', 'regular', 1),
(2025, '2026-01-01', 'regular', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_prosecusion`
--

CREATE TABLE `tbl_prosecusion` (
  `sec_origen` varchar(30) NOT NULL,
  `ani_origen` int(11) NOT NULL,
  `sec_promocion` varchar(30) NOT NULL,
  `ani_destino` int(11) NOT NULL,
  `pro_cantidad` int(11) NOT NULL DEFAULT 0,
  `pro_estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_seccion`
--

CREATE TABLE `tbl_seccion` (
  `sec_codigo` varchar(30) NOT NULL,
  `sec_cantidad` int(11) NOT NULL DEFAULT 0,
  `sec_estado` tinyint(1) NOT NULL,
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `grupo_union_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_seccion`
--

INSERT INTO `tbl_seccion` (`sec_codigo`, `sec_cantidad`, `sec_estado`, `ani_anio`, `ani_tipo`, `grupo_union_id`) VALUES
('IIN4103', 0, 1, 2025, 'regular', NULL),
('IIN4403', 0, 1, 2025, 'regular', NULL),
('IN0413', 0, 1, 2025, 'regular', 'grupo_68f2b5460a8c62.29071220'),
('IN0423', 0, 1, 2025, 'regular', NULL),
('IN1101', 1, 1, 2025, 'regular', 'grupo_68dcbe74e9fbe0.04656473'),
('IN1102', 0, 1, 2025, 'regular', 'grupo_68dcbe74e9fbe0.04656473'),
('IN1103', 0, 1, 2025, 'regular', 'grupo_68f1eb5b82b6f9.74113922'),
('IN1104', 0, 1, 2025, 'regular', 'grupo_68dcbe74e9fbe0.04656473'),
('IN1113', 0, 1, 2025, 'regular', 'grupo_68f1eb5b82b6f9.74113922'),
('IN1123', 0, 1, 2025, 'regular', NULL),
('IN1133', 0, 1, 2025, 'regular', NULL),
('IN1143', 0, 1, 2025, 'regular', 'grupo_68dcbe74e9fbe0.04656473'),
('IN1202', 0, 1, 2025, 'regular', NULL),
('IN1203', 0, 1, 2025, 'regular', NULL),
('IN1204', 0, 1, 2025, 'regular', NULL),
('IN1213', 0, 1, 2025, 'regular', NULL),
('IN1214', 0, 1, 2025, 'regular', NULL),
('IN1403', 0, 1, 2025, 'regular', NULL),
('IN2101', 0, 1, 2025, 'regular', NULL),
('IN2102', 0, 1, 2025, 'regular', NULL),
('IN2103', 0, 1, 2025, 'regular', NULL),
('IN2104', 0, 1, 2025, 'regular', NULL),
('IN2113', 0, 1, 2025, 'regular', 'grupo_68f2b8b5dcca70.68942892'),
('IN2114', 0, 1, 2025, 'regular', 'grupo_68f2b8b5dcca70.68942892'),
('IN2123', 0, 1, 2025, 'regular', 'grupo_68f2b8b5dcca70.68942892'),
('IN2133', 0, 1, 2025, 'regular', NULL),
('IN2403', 0, 1, 2025, 'regular', NULL),
('IN3101', 0, 1, 2025, 'regular', NULL),
('IN3102', 0, 1, 2025, 'regular', NULL),
('IN3103', 0, 1, 2025, 'regular', NULL),
('IN3104', 0, 1, 2025, 'regular', NULL),
('IN3113', 0, 1, 2025, 'regular', NULL),
('IN4402', 0, 1, 2025, 'regular', NULL),
('IN4404', 0, 1, 2025, 'regular', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_titulo`
--

CREATE TABLE `tbl_titulo` (
  `tit_estado` tinyint(1) NOT NULL,
  `tit_prefijo` varchar(30) NOT NULL,
  `tit_nombre` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_titulo`
--

INSERT INTO `tbl_titulo` (`tit_estado`, `tit_prefijo`, `tit_nombre`) VALUES
(1, 'Dr.', 'Cs Educación '),
(0, 'Dr.', 'PRUEBASS'),
(1, 'Esp.', 'Experto Elearning'),
(1, 'Esp.', 'Organización Sit. Información'),
(1, 'Esp.', 'Sist. Información'),
(1, 'Esp.', 'Telematica Informática'),
(1, 'Ing.', 'Computación'),
(0, 'Ing.', 'Gatos'),
(1, 'Ing.', 'Informática'),
(0, 'Ing.', 'rijals'),
(0, 'Ing.', 'rijas'),
(1, 'Ing.', 'Sistemas'),
(1, 'Lic.', 'Administración Mención Informática'),
(1, 'Lic.', 'Cs Información'),
(1, 'Lic.', 'Cs Matemáticas'),
(1, 'Lic.', 'Cultura y Física y deporte'),
(1, 'Lic.', 'Educación Mención Matemática'),
(1, 'Lic.', 'Matemática'),
(1, 'Msc.', 'Ciencias de la Computación Mención Inteligencia Ar'),
(1, 'Msc.', 'Cs Gerencia Educaccional'),
(1, 'Msc.', 'Cs Información'),
(1, 'Msc.', 'Cs Orientación'),
(1, 'Msc.', 'Educación Superior'),
(1, 'Msc.', 'Educación Superior Mención Docencia Universitaria'),
(1, 'Msc.', 'Educación Superior Mención Gerencia Educacional'),
(1, 'Msc.', 'Gerenc. Emp.'),
(1, 'Msc.', 'Ing. Industrial'),
(0, 'Msc.', 'Matemática Pura'),
(1, 'Prof.', 'Educación Física y deporte'),
(1, 'Prof.', 'Geografia e Historia'),
(1, 'Prof.', 'Inglés'),
(1, 'TSU.', 'Analista de Sistemas'),
(0, 'TSU.', 'Politicaa');

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
  `eje_nombre` varchar(30) NOT NULL,
  `uc_codigo` varchar(30) NOT NULL DEFAULT '',
  `uc_nombre` varchar(100) NOT NULL,
  `uc_creditos` int(11) NOT NULL DEFAULT 0,
  `uc_periodo` varchar(10) NOT NULL,
  `uc_estado` tinyint(1) NOT NULL,
  `area_nombre` varchar(30) NOT NULL,
  `uc_trayecto` varchar(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_uc`
--

INSERT INTO `tbl_uc` (`eje_nombre`, `uc_codigo`, `uc_nombre`, `uc_creditos`, `uc_periodo`, `uc_estado`, `area_nombre`, `uc_trayecto`) VALUES
('Epistemológico', 'IDIO4', 'IDIOMAS VI', 23, 'Anual', 1, 'Idiomas', '4'),
('Epistemológico', 'PIABD090403', 'Administración de bases de datos', 3, 'Fase I', 1, 'Bases de Datos', '4'),
('Estético Lúdico', 'PIACA090103', 'Actividades Acreditables I', 23, 'Anual', 1, 'Actividades', '1'),
('Estético Lúdico', 'PIACA090203', 'Actividades Acreditables II', 3, 'Anual', 1, 'Actividades', '2'),
('Estético Lúdico', 'PIACA090303', 'Actividades Acreditables III', 3, 'Anual', 1, 'Actividades', '3'),
('Estético Lúdico', 'PIACA090403', 'Actividades Acreditables IV', 3, 'Anual', 1, 'Actividades', '4'),
('Epistemológico', 'PIALP306112', 'Algorítmica y Programación', 12, 'Anual', 1, 'Programación', '1'),
('Epistemológico', 'PIARC234109', 'Arquitectura del Computador', 9, 'Anual', 1, 'Arquitectura', '1'),
('Epistemológico', 'PIAUI120404', 'Auditoria de sistemas', 4, 'Fase II', 1, 'Seguridad', '4'),
('Epistemológico', 'PIBAD090203', 'Base de Datos', 3, 'Fase I', 1, 'Bases de Datos', '2'),
('Epistemológico', 'PIELE072103', 'Electiva I', 3, 'Fase II', 1, 'Electivas', '1'),
('Epistemológico', 'PIELE072203', 'Electiva II', 3, 'Fase II', 1, 'Electivas', '2'),
('Epistemológico', 'PIELE072403', 'Electiva IV', 3, 'Fase II', 1, 'Electivas', '4'),
('Epistemológico', 'PIELE078303', 'Electiva III', 3, 'Fase II', 1, 'Electivas', '3'),
('Ético-Político', 'PIFOC090103', 'Formación Crítica I', 3, 'Anual', 1, 'Formación Crítica', '1'),
('Ético-Político', 'PIFOC090203', 'Formación Crítica II', 3, 'Anual', 1, 'Formación Crítica', '2'),
('Ético Político-Socio Ambiental', 'PIFOC090303', 'Formación Crítica III', 3, 'Anual', 1, 'Formación Crítica', '3'),
('Ético Político-Socio Ambiental', 'PIFOC090403', 'Formación Crítica IV', 3, 'Anual', 1, 'Formación Crítica', '4'),
('Epistemológico', 'PIGPI120404', 'Gestión de proyecto Informático', 4, 'Fase I', 1, 'Gestión', '4'),
('Epistemológico', 'PIIDI090103', 'Idiomas I', 3, 'Fase I', 1, 'Idiomas', '1'),
('Epistemológico', 'PIIDI090403', 'Idiomas II', 3, 'Anual', 1, 'Idiomas', '4'),
('Epistemológico', 'PIINO078303', 'Investigación de operaciones', 3, 'Fase II', 1, 'Investigación', '3'),
('Epistemológico', 'PIINS090203', 'Ingeniería del Software I', 3, 'Fase I', 1, 'Ingeniería Software', '2'),
('Epistemológico', 'PIINS252309', 'Ingeniería de Software II', 9, 'Anual', 1, 'Ingeniería Software', '3'),
('Ético-Político', 'PIIUP052002', 'Introducción a la universidad y a los programas nacionales de formacion', 2, 'Anual', 1, 'Introducción', '0'),
('Epistemológico', 'PIMAT090003', 'Matemática', 3, 'Anual', 1, 'Matemáticas', '0'),
('Epistemológico', 'PIMAT156206', 'Matemática II', 6, 'Anual', 1, 'Matemáticas', '2'),
('Epistemológico', 'PIMAT156306', 'Matemática Aplicada', 6, 'Anual', 1, 'Matemáticas', '3'),
('Epistemológico', 'PIMAT234109', 'Matemática I', 9, 'Anual', 1, 'Matemáticas', '1'),
('Epistemológico', 'PIMOB078303', 'Modelado de bases de datos', 3, 'Fase I', 1, 'Bases de Datos', '3'),
('Ético-Político', 'PIPNN078003', 'Proyecto nacional y nueva ciudadanía', 3, 'Anual', 1, 'Proyectos', '0'),
('Epistemológico', 'PIPRO306212', 'Programación II', 12, 'Anual', 1, 'Programación', '2'),
('Trabajo-Productivo', 'PIPST234109', 'Proyecto Socio Tecnológico I', 9, 'Anual', 1, 'Proyectos', '1'),
('Trabajo-Productivo', 'PIPST234209', 'Proyecto Socio Tecnológico II', 9, 'Anual', 1, 'Proyectos', '2'),
('Trabajo-Productivo', 'PIPST234309', 'Proyecto Socio Tecnológico III', 9, 'Anual', 1, 'Proyectos', '3'),
('Trabajo-Productivo', 'PIPST360412', 'Proyecto Socio Tecnológico IV', 12, 'Anual', 1, 'Proyectos', '4'),
('Epistemológico', 'PIREA084403', 'Redes Avanzadas', 3, 'Fase II', 1, 'Redes', '4'),
('Epistemológico', 'PIREC156206', 'Redes de Computadoras', 6, 'Anual', 1, 'Redes', '2'),
('Epistemológico', 'PISEI120404', 'Seguridad Informática', 4, 'Fase I', 1, 'Seguridad', '4'),
('Epistemológico', 'PISIO078303', 'Sistemas Operativos', 3, 'Fase I', 1, 'Sistemas Operativos', '3'),
('Epistemológico', 'PITIC032002', 'Tecnologías de la información y comunicación', 2, 'Anual', 1, 'Tecnologías', '0');

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
(15170003, 'Ing.', 'Computación'),
(2134123, 'Esp.', 'Experto Elearning'),
(1123345, 'Esp.', 'Experto Elearning'),
(3759671, 'Dr.', 'Cs Educación '),
(3759671, 'Msc.', 'Gerenc. Emp.'),
(2121213, 'Esp.', 'Experto Elearning'),
(23232323, 'Dr.', 'Cs Educación ');

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
('IDIO4', 18356682),
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
('PIELE072103', 11264888),
('PIELE072103', 7423486),
('PIARC234109', 7424546),
('PIMAT234109', 17354607),
('PIALP306112', 15693145),
('PIELE072103', 26197135),
('PIALP306112', 7391773),
('PIARC234109', 10778236),
('PIARC234109', 11264888),
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

INSERT INTO `uc_horario` (`uc_codigo`, `doc_cedula`, `subgrupo`, `sec_codigo`, `ani_anio`, `esp_numero`, `hor_dia`, `hor_horainicio`, `hor_horafin`, `esp_tipo`, `esp_edificio`) VALUES
('PIIUP052002', 18356682, NULL, 'IN0423', 2025, '15', 'Miércoles', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIMAT090003', 10775753, NULL, 'IN0423', 2025, '21', 'Sábado', '09:20', '10:40', 'Aula', 'Giraluna'),
('PITIC032002', NULL, NULL, 'IN0423', 2025, '21', 'Sábado', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIPNN078003', 14159756, NULL, 'IN0423', 2025, '21', 'Sábado', '13:00', '14:20', 'Aula', 'Giraluna'),
('PIACA090103', 18103232, NULL, 'IN1103', 2025, '12', 'Miércoles', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090103', 23316126, NULL, 'IN1103', 2025, '12', 'Miércoles', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072103', 29880797, NULL, 'IN1103', 2025, '3', 'Miércoles', '10:40', '12:00', 'Laboratorio', 'Giraluna'),
('PIPST234109', 15170003, NULL, 'IN1103', 2025, '9', 'Viernes', '08:00', '10:00', 'Aula', 'Giraluna'),
('PIMAT234109', 9627295, NULL, 'IN1103', 2025, '9', 'Viernes', '10:00', '12:00', 'Aula', 'Giraluna'),
('PIALP306112', 9118178, 'A', 'IN1103', 2025, '3', 'Martes', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10778236, 'B', 'IN1103', 2025, 'Hardware', 'Martes', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10778236, 'A', 'IN1103', 2025, 'Hardware', 'Martes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9118178, 'B', 'IN1103', 2025, '3', 'Martes', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIALP306112', 10723015, 'A', 'IN1123', 2025, '5', 'Lunes', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 11898335, 'B', 'IN1123', 2025, 'Hardware', 'Lunes', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 11898335, 'A', 'IN1123', 2025, 'Hardware', 'Lunes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 10723015, 'B', 'IN1123', 2025, '5', 'Lunes', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIPST234109', 10848316, NULL, 'IN1123', 2025, '7', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIMAT234109', 14677589, NULL, 'IN1123', 2025, '7', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072103', 29880797, NULL, 'IN1123', 2025, '3', 'Miércoles', '08:00', '09:20', 'Laboratorio', 'Giraluna'),
('PIACA090103', 18103232, NULL, 'IN1123', 2025, '14', 'Miércoles', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIFOC090103', 23316126, NULL, 'IN1123', 2025, '14', 'Miércoles', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIMAT234109', 14677589, NULL, 'IN1133', 2025, '7', 'Martes', '08:00', '09:20', 'Aula', 'Rio 7 Estrellas'),
('PIACA090103', 12701387, NULL, 'IN1133', 2025, '7', 'Martes', '09:20', '10:40', 'Aula', 'Rio 7 Estrellas'),
('PIFOC090103', 24418577, NULL, 'IN1133', 2025, '10', 'Viernes', '08:00', '09:20', 'Aula', 'Rio 7 Estrellas'),
('PIELE072103', 11264888, NULL, 'IN1133', 2025, '6', 'Viernes', '09:20', '10:40', 'Laboratorio', 'Hilandera'),
('PIPST234109', 13527711, NULL, 'IN1133', 2025, '10', 'Viernes', '10:40', '12:00', 'Aula', 'Rio 7 Estrellas'),
('PIALP306112', 10846157, 'A', 'IN1133', 2025, '5', 'Sábado', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 7424546, 'B', 'IN1133', 2025, 'Hardware', 'Sábado', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 7424546, 'A', 'IN1133', 2025, 'Hardware', 'Sábado', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 10846157, 'B', 'IN1133', 2025, '5', 'Sábado', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIACA090103', 12701387, NULL, 'IN1403', 2025, '9', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 11264888, NULL, 'IN1403', 2025, '6', 'Viernes', '08:00', '09:20', 'Laboratorio', 'Hilandera'),
('PIFOC090103', 13527711, NULL, 'IN1403', 2025, '11', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT234109', 17354607, NULL, 'IN1403', 2025, '9', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIPST234109', 7423485, NULL, 'IN1403', 2025, '11', 'Viernes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIALP306112', 15693145, 'A', 'IN1403', 2025, '3', 'Sábado', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 7424546, 'B', 'IN1403', 2025, 'Hardware', 'Sábado', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 7424546, 'A', 'IN1403', 2025, 'Hardware', 'Sábado', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 15693145, 'B', 'IN1403', 2025, '3', 'Sábado', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIFOC090103', 23316126, NULL, 'IN1203', 2025, '12', 'Miércoles', '13:00', '14:20', 'Aula', 'Hilandera'),
('PIACA090103', 18103232, NULL, 'IN1203', 2025, '12', 'Miércoles', '14:20', '15:40', 'Aula', 'Hilandera'),
('PIELE072103', 26197135, NULL, 'IN1203', 2025, '12', 'Miércoles', '15:40', '17:00', 'Aula', 'Hilandera'),
('PIMAT234109', 15351688, NULL, 'IN1203', 2025, '12', 'Jueves', '13:00', '15:00', 'Aula', 'Hilandera'),
('PIPST234109', 26197135, NULL, 'IN1203', 2025, '12', 'Jueves', '15:00', '17:00', 'Aula', 'Hilandera'),
('PIALP306112', 7391773, 'A', 'IN1203', 2025, '3', 'Viernes', '13:00', '15:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10844463, 'B', 'IN1203', 2025, 'Hardware', 'Viernes', '13:00', '15:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10844463, 'A', 'IN1203', 2025, 'Hardware', 'Viernes', '15:00', '17:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 7391773, 'B', 'IN1203', 2025, '3', 'Viernes', '15:00', '17:00', 'Laboratorio', 'Giraluna'),
('PIFOC090103', 9629702, NULL, 'IN1143', 2025, '14', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST234109', 10848316, NULL, 'IN1143', 2025, '14', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090103', 16385182, NULL, 'IN1143', 2025, '14', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 30395804, NULL, 'IN1143', 2025, '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 15351688, NULL, 'IN1143', 2025, '14', 'Jueves', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIALP306112', 9629702, 'A', 'IN1143', 2025, '3', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10844463, 'B', 'IN1143', 2025, 'Hardware', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10844463, 'A', 'IN1143', 2025, 'Hardware', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9629702, 'B', 'IN1143', 2025, '3', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIALP306112', 10846157, NULL, 'IN1213', 2025, '3', 'Lunes', '13:00', '15:00', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 18912216, NULL, 'IN1213', 2025, '12', 'Martes', '13:00', '15:00', 'Aula', 'Hilandera'),
('PIACA090203', 16385182, NULL, 'IN2103', 2025, '10', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090203', 24418577, NULL, 'IN2103', 2025, '10', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 17354607, NULL, 'IN2103', 2025, '10', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIPRO306212', 13188691, NULL, 'IN2103', 2025, '6', 'Jueves', '09:20', '12:00', 'Laboratorio', 'Hilandera'),
('PIPST234209', 13991250, NULL, 'IN2103', 2025, '10', 'Viernes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIREC156206', 15693145, NULL, 'IN2103', 2025, '10', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIELE072203', 30088284, NULL, 'IN2103', 2025, '6', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIPST234209', 16403903, NULL, 'IN2113', 2025, '4', 'Lunes', '08:00', '10:00', 'Aula', 'Hilandera'),
('PIPRO306212', 10846157, NULL, 'IN2113', 2025, '6', 'Lunes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090203', 24418577, NULL, 'IN2113', 2025, '11', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIACA090203', 18103232, NULL, 'IN2113', 2025, '11', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 14677589, NULL, 'IN2113', 2025, '11', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREC156206', 13188691, NULL, 'IN2113', 2025, '12', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072203', 9555514, NULL, 'IN2113', 2025, '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIPRO306212', 29517943, NULL, 'IN2133', 2025, '6', 'Martes', '08:00', '09:20', 'Laboratorio', 'Hilandera'),
('PIPST234209', 9540060, NULL, 'IN2133', 2025, '13', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIACA090203', 18103232, NULL, 'IN2133', 2025, '13', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIELE072203', 30088284, NULL, 'IN2133', 2025, '6', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIREC156206', 14159756, NULL, 'IN2133', 2025, '9', 'Sábado', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090203', 14159756, NULL, 'IN2133', 2025, '9', 'Sábado', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 10775753, NULL, 'IN2133', 2025, '9', 'Sábado', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREC156206', 13188691, NULL, 'IN2403', 2025, '11', 'Martes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIACA090203', 20351422, NULL, 'IN2403', 2025, '11', 'Martes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIELE072203', 29517943, NULL, 'IN2403', 2025, '6', 'Viernes', '08:00', '09:20', 'Laboratorio', 'Hilandera'),
('PIPST234209', 9555514, NULL, 'IN2403', 2025, '12', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIMAT156206', 15351688, NULL, 'IN2403', 2025, '12', 'Viernes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIPRO306212', 25471240, NULL, 'IN2403', 2025, '6', 'Sábado', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIFOC090203', 14159756, NULL, 'IN2403', 2025, '21', 'Sábado', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIACA090303', 16385182, NULL, 'IN3103', 2025, '6', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIELE078303', 25471240, NULL, 'IN3103', 2025, '6', 'Sábado', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090303', 15170003, NULL, 'IN3103', 2025, '12', 'Viernes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIINO078303', 7392496, NULL, 'IN3103', 2025, '6', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIINS252309', 13991971, NULL, 'IN3103', 2025, '12', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156306', 18912216, NULL, 'IN3103', 2025, '6', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIPST234309', 7391773, NULL, 'IN3103', 2025, '12', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIACA090303', 12701387, NULL, 'IN3104', 2025, '12', 'Lunes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE078303', 30088284, NULL, 'IN3104', 2025, '3', 'Lunes', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIMAT156306', 18912216, NULL, 'IN3104', 2025, '26', 'Martes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIINO078303', 7392496, NULL, 'IN3104', 2025, '26', 'Martes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIINS252309', 13991971, NULL, 'IN3104', 2025, '26', 'Viernes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIFOC090303', 24418577, NULL, 'IN3104', 2025, '26', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIPST234309', 10844463, NULL, 'IN3104', 2025, '26', 'Viernes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIELE078303', 30088284, NULL, 'IN3113', 2025, '3', 'Lunes', '08:00', '09:20', 'Laboratorio', 'Giraluna'),
('PIACA090303', 12701387, NULL, 'IN3113', 2025, '13', 'Lunes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIINO078303', 7392496, NULL, 'IN3113', 2025, '10', 'Martes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIMAT156306', 18912216, NULL, 'IN3113', 2025, '10', 'Martes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIPST234309', 7392496, NULL, 'IN3113', 2025, '13', 'Viernes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIINS252309', 30395804, NULL, 'IN3113', 2025, '13', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIFOC090303', 24418577, NULL, 'IN3113', 2025, '13', 'Viernes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIALP306112', 10846157, NULL, 'IN1202', 2025, '3', 'Lunes', '13:00', '15:00', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 18912216, NULL, 'IN1202', 2025, '12', 'Martes', '13:00', '15:00', 'Aula', 'Hilandera'),
('PIALP306112', 10846157, NULL, 'IN1204', 2025, '3', 'Lunes', '13:00', '15:00', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 18912216, NULL, 'IN1204', 2025, '12', 'Martes', '13:00', '15:00', 'Aula', 'Hilandera'),
('PIALP306112', 10846157, NULL, 'IN1214', 2025, '3', 'Lunes', '13:00', '15:00', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 18912216, NULL, 'IN1214', 2025, '12', 'Martes', '13:00', '15:00', 'Aula', 'Hilandera'),
('PIACA090203', 16385182, NULL, 'IN2101', 2025, '10', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090203', 24418577, NULL, 'IN2101', 2025, '10', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 17354607, NULL, 'IN2101', 2025, '10', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIPRO306212', 13188691, NULL, 'IN2101', 2025, '6', 'Jueves', '09:20', '12:00', 'Laboratorio', 'Hilandera'),
('PIPST234209', 13991250, NULL, 'IN2101', 2025, '10', 'Viernes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIREC156206', 15693145, NULL, 'IN2101', 2025, '10', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIELE072203', 30088284, NULL, 'IN2101', 2025, '6', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIPST234209', 16403903, NULL, 'IN2102', 2025, '4', 'Lunes', '08:00', '10:00', 'Aula', 'Hilandera'),
('PIPRO306212', 10846157, NULL, 'IN2102', 2025, '6', 'Lunes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090203', 24418577, NULL, 'IN2102', 2025, '11', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIACA090203', 18103232, NULL, 'IN2102', 2025, '11', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 14677589, NULL, 'IN2102', 2025, '11', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREC156206', 13188691, NULL, 'IN2102', 2025, '12', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072203', 9555514, NULL, 'IN2102', 2025, '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIPRO306212', 29517943, NULL, 'IN2104', 2025, '6', 'Lunes', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIPST234209', 7439117, NULL, 'IN2104', 2025, '4', 'Lunes', '10:00', '12:00', 'Aula', 'Hilandera'),
('PIMAT156206', 17354607, NULL, 'IN2104', 2025, '8', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090203', 9629702, NULL, 'IN2104', 2025, '8', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIACA090203', 12701387, NULL, 'IN2104', 2025, '8', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREC156206', 11898335, NULL, 'IN2104', 2025, '13', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072203', 5260810, NULL, 'IN2104', 2025, 'Software', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Hilandera'),
('PIACA090303', 16385182, NULL, 'IN3101', 2025, '6', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIELE078303', 25471240, NULL, 'IN3101', 2025, '6', 'Sábado', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090303', 15170003, NULL, 'IN3101', 2025, '12', 'Viernes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIINO078303', 7392496, NULL, 'IN3101', 2025, '6', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIINS252309', 13991971, NULL, 'IN3101', 2025, '12', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156306', 18912216, NULL, 'IN3101', 2025, '6', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIPST234309', 7391773, NULL, 'IN3101', 2025, '12', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIACA090303', 16385182, NULL, 'IN3102', 2025, '6', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIELE078303', 25471240, NULL, 'IN3102', 2025, '6', 'Sábado', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090303', 15170003, NULL, 'IN3102', 2025, '12', 'Viernes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIINO078303', 7392496, NULL, 'IN3102', 2025, '6', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIINS252309', 13991971, NULL, 'IN3102', 2025, '12', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156306', 18912216, NULL, 'IN3102', 2025, '6', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIPST234309', 7391773, NULL, 'IN3102', 2025, '12', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIREA084403', 5260810, NULL, 'IN4402', 2025, '5', 'Lunes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIAUI120404', 16403903, NULL, 'IN4402', 2025, '5', 'Lunes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090403', 16385182, NULL, 'IN4402', 2025, '15', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST360412', 16403903, NULL, 'IN4402', 2025, '15', 'Martes', '09:20', '11:20', 'Aula', 'Hilandera'),
('PIFOC090403', 13527711, NULL, 'IN4402', 2025, '15', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera'),
('IDIO4', 18356682, NULL, 'IN4402', 2025, '15', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072403', 7391773, NULL, 'IN4402', 2025, 'Software', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIREA084403', 5260810, NULL, 'IN4404', 2025, '5', 'Lunes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIAUI120404', 16403903, NULL, 'IN4404', 2025, '5', 'Lunes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090403', 16385182, NULL, 'IN4404', 2025, '15', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST360412', 16403903, NULL, 'IN4404', 2025, '15', 'Martes', '09:20', '11:20', 'Aula', 'Hilandera'),
('PIFOC090403', 13527711, NULL, 'IN4404', 2025, '15', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera'),
('IDIO4', 18356682, NULL, 'IN4404', 2025, '15', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072403', 7391773, NULL, 'IN4404', 2025, 'Software', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090103', 9629702, NULL, 'IN1101', 2025, '14', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST234109', 10848316, NULL, 'IN1101', 2025, '14', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090103', 16385182, NULL, 'IN1101', 2025, '14', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 30395804, NULL, 'IN1101', 2025, '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 15351688, NULL, 'IN1101', 2025, '14', 'Jueves', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIALP306112', 9629702, NULL, 'IN1101', 2025, '3', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10844463, NULL, 'IN1101', 2025, 'Hardware', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10844463, NULL, 'IN1101', 2025, 'Hardware', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9629702, NULL, 'IN1101', 2025, '3', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIFOC090103', 9629702, NULL, 'IN1102', 2025, '14', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST234109', 10848316, NULL, 'IN1102', 2025, '14', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090103', 16385182, NULL, 'IN1102', 2025, '14', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 30395804, NULL, 'IN1102', 2025, '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 15351688, NULL, 'IN1102', 2025, '14', 'Jueves', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIALP306112', 9629702, NULL, 'IN1102', 2025, '3', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10844463, NULL, 'IN1102', 2025, 'Hardware', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10844463, NULL, 'IN1102', 2025, 'Hardware', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9629702, NULL, 'IN1102', 2025, '3', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIFOC090103', 9629702, NULL, 'IN1104', 2025, '14', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST234109', 10848316, NULL, 'IN1104', 2025, '14', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090103', 16385182, NULL, 'IN1104', 2025, '14', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 30395804, NULL, 'IN1104', 2025, '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 15351688, NULL, 'IN1104', 2025, '14', 'Jueves', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIALP306112', 9629702, NULL, 'IN1104', 2025, '3', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10844463, NULL, 'IN1104', 2025, 'Hardware', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10844463, NULL, 'IN1104', 2025, 'Hardware', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9629702, NULL, 'IN1104', 2025, '3', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIACA090103', 18103232, NULL, 'IN1113', NULL, '12', 'Miércoles', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090103', 23316126, NULL, 'IN1113', NULL, '12', 'Miércoles', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072103', 29880797, NULL, 'IN1113', NULL, '3', 'Miércoles', '10:40', '12:00', 'Laboratorio', 'Giraluna'),
('PIPST234109', 15170003, NULL, 'IN1113', NULL, '9', 'Viernes', '08:00', '10:00', 'Aula', 'Giraluna'),
('PIMAT234109', 9627295, NULL, 'IN1113', NULL, '9', 'Viernes', '10:00', '12:00', 'Aula', 'Giraluna'),
('PIALP306112', 9118178, NULL, 'IN1113', NULL, '3', 'Martes', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10778236, NULL, 'IN1113', NULL, 'Hardware', 'Martes', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10778236, NULL, 'IN1113', NULL, 'Hardware', 'Martes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9118178, NULL, 'IN1113', NULL, '3', 'Martes', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIACA090103', 18103232, NULL, 'IN1113', NULL, '12', 'Miércoles', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090103', 23316126, NULL, 'IN1113', NULL, '12', 'Miércoles', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072103', 29880797, NULL, 'IN1113', NULL, '3', 'Miércoles', '10:40', '12:00', 'Laboratorio', 'Giraluna'),
('PIPST234109', 15170003, NULL, 'IN1113', NULL, '9', 'Viernes', '08:00', '10:00', 'Aula', 'Giraluna'),
('PIMAT234109', 9627295, NULL, 'IN1113', NULL, '9', 'Viernes', '10:00', '12:00', 'Aula', 'Giraluna'),
('PIALP306112', 9118178, NULL, 'IN1113', NULL, '3', 'Martes', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10778236, NULL, 'IN1113', NULL, 'Hardware', 'Martes', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10778236, NULL, 'IN1113', NULL, 'Hardware', 'Martes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9118178, NULL, 'IN1113', NULL, '3', 'Martes', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIACA090103', 18103232, NULL, 'IN1113', 2025, '12', 'Miércoles', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090103', 23316126, NULL, 'IN1113', 2025, '12', 'Miércoles', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072103', 29880797, NULL, 'IN1113', 2025, '3', 'Miércoles', '10:40', '12:00', 'Laboratorio', 'Giraluna'),
('PIPST234109', 15170003, NULL, 'IN1113', 2025, '9', 'Viernes', '08:00', '10:00', 'Aula', 'Giraluna'),
('PIMAT234109', 9627295, NULL, 'IN1113', 2025, '9', 'Viernes', '10:00', '12:00', 'Aula', 'Giraluna'),
('PIALP306112', 9118178, 'A', 'IN1113', 2025, '3', 'Martes', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 10778236, 'B', 'IN1113', 2025, 'Hardware', 'Martes', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIARC234109', 10778236, 'A', 'IN1113', 2025, 'Hardware', 'Martes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 9118178, 'B', 'IN1113', 2025, '3', 'Martes', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIPST234209', 16403903, NULL, 'IN2114', 2025, '4', 'Lunes', '08:00', '10:00', 'Aula', 'Hilandera'),
('PIPRO306212', 10846157, NULL, 'IN2114', 2025, '6', 'Lunes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090203', 24418577, NULL, 'IN2114', 2025, '11', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIACA090203', 18103232, NULL, 'IN2114', 2025, '11', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 14677589, NULL, 'IN2114', 2025, '11', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREC156206', 13188691, NULL, 'IN2114', 2025, '12', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072203', 9555514, NULL, 'IN2114', 2025, '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIPST234209', 16403903, NULL, 'IN2123', 2025, '4', 'Lunes', '08:00', '10:00', 'Aula', 'Hilandera'),
('PIPRO306212', 10846157, NULL, 'IN2123', 2025, '6', 'Lunes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090203', 24418577, NULL, 'IN2123', 2025, '11', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIACA090203', 18103232, NULL, 'IN2123', 2025, '11', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 14677589, NULL, 'IN2123', 2025, '11', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREC156206', 13188691, NULL, 'IN2123', 2025, '12', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072203', 9555514, NULL, 'IN2123', 2025, '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIIUP052002', 10844463, NULL, 'IN0413', 2025, '8', 'Sábado', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPNN078003', 14159756, NULL, 'IN0413', 2025, '8', 'Sábado', '10:40', '12:00', 'Aula', 'Hilandera'),
('PITIC032002', NULL, NULL, 'IN0413', 2025, '8', 'Sábado', '09:20', '10:40', 'Aula', 'Hilandera');

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
('05033', 'IDIO4', 42, 23, 2),
('05033', 'PIPST360412', 144, 216, 6),
('05033', 'PIREA084403', 18, 66, 4),
('05033', 'PISEI120404', 48, 72, 4),
('55', 'PIIUP052002', 4, 70, 2),
('55', 'PIELE072103', 4, 4, 4),
('55', 'PIINS090203', 4, 23, 20),
('55', 'PIINS252309', 6, 50, 50),
('55', 'PIELE072403', 8, 4, 4),
('55', 'PIIUP052002', 5, 58, 6),
('55', 'PIALP306112', 5, 36, 7),
('55', 'PIELE072403', 7, 9, 4),
('55', 'PIELE072203', 4, 7, 8),
('55', 'PISIO078303', 85, 5, 7),
('55', 'PIIUP052002', 5, 58, 6),
('55', 'PIIUP052002', 5, 58, 6),
('55', 'PIALP306112', 5, 36, 7),
('55', 'PIELE072103', 4, 4, 4),
('55', 'PIELE072203', 4, 7, 8),
('55', 'PIINS090203', 4, 23, 20),
('55', 'PIINS252309', 6, 50, 50),
('55', 'PISIO078303', 85, 5, 7),
('55', 'PIELE072403', 8, 4, 4),
('55', 'PIELE072403', 8, 4, 4),
('PMF', 'PIMAT090003', 2, 22, 11),
('PMF', 'PIACA090103', 55, 4, 50),
('PMF', 'PIACA090203', 3, 2, 20),
('PMF', 'PIACA090303', 3, 3, 30),
('PMF', 'PIFOC090403', 2, 2, 2),
('prueba ', 'PIIUP052002', 2, 2, 40),
('prueba ', 'PIACA090103', 3, 3, 4),
('prueba ', 'PIACA090203', 30, 3, 3),
('prueba ', 'PIPST234309', 2, 20, 20),
('prueba ', 'PIPST360412', 4, 4, 40);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `coordinacion_docente`
--
ALTER TABLE `coordinacion_docente`
  ADD KEY `fk_cor_doc` (`cor_nombre`),
  ADD KEY `doc_cedula` (`doc_cedula`);

--
-- Indices de la tabla `docente_horario`
--
ALTER TABLE `docente_horario`
  ADD KEY `fk_doc_horario` (`doc_cedula`),
  ADD KEY `fk_horario_doc` (`sec_codigo`);

--
-- Indices de la tabla `per_aprobados`
--
ALTER TABLE `per_aprobados`
  ADD KEY `fk_per_aprobados_anio` (`ani_anio`,`ani_tipo`),
  ADD KEY `fk_per_aprobados_seccion` (`sec_codigo`),
  ADD KEY `fk_per_aprobados_uc` (`uc_codigo`);

--
-- Indices de la tabla `tbl_actividad`
--
ALTER TABLE `tbl_actividad`
  ADD KEY `fk_doc_act` (`doc_cedula`);

--
-- Indices de la tabla `tbl_anio`
--
ALTER TABLE `tbl_anio`
  ADD PRIMARY KEY (`ani_anio`,`ani_tipo`);

--
-- Indices de la tabla `tbl_aprobados`
--
ALTER TABLE `tbl_aprobados`
  ADD KEY `aprobados_anios` (`ani_anio`,`ani_tipo`),
  ADD KEY `aprobados_docente` (`doc_cedula`),
  ADD KEY `aprobados_seccion` (`sec_codigo`),
  ADD KEY `aprobados_uc` (`uc_codigo`);

--
-- Indices de la tabla `tbl_area`
--
ALTER TABLE `tbl_area`
  ADD PRIMARY KEY (`area_nombre`);

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
  ADD KEY `fk_docente_categoria` (`cat_nombre`);

--
-- Indices de la tabla `tbl_eje`
--
ALTER TABLE `tbl_eje`
  ADD PRIMARY KEY (`eje_nombre`);

--
-- Indices de la tabla `tbl_espacio`
--
ALTER TABLE `tbl_espacio`
  ADD PRIMARY KEY (`esp_numero`,`esp_tipo`,`esp_edificio`) USING BTREE;

--
-- Indices de la tabla `tbl_fase`
--
ALTER TABLE `tbl_fase`
  ADD KEY `fk_ani_anio_ani_tipo_fase` (`ani_anio`,`ani_tipo`);

--
-- Indices de la tabla `tbl_horario`
--
ALTER TABLE `tbl_horario`
  ADD KEY `fk_horario_seccion` (`sec_codigo`),
  ADD KEY `fk_horario_turno` (`tur_nombre`);

--
-- Indices de la tabla `tbl_malla`
--
ALTER TABLE `tbl_malla`
  ADD PRIMARY KEY (`mal_codigo`);

--
-- Indices de la tabla `tbl_per`
--
ALTER TABLE `tbl_per`
  ADD KEY `fk_per_ani_anio_ani_tipo` (`ani_anio`,`ani_tipo`);

--
-- Indices de la tabla `tbl_prosecusion`
--
ALTER TABLE `tbl_prosecusion`
  ADD PRIMARY KEY (`sec_origen`,`ani_origen`,`sec_promocion`,`ani_destino`),
  ADD KEY `fk_prosecucion_seccion_origen` (`sec_origen`),
  ADD KEY `fk_prosecucion_seccion_promocion` (`sec_promocion`);

--
-- Indices de la tabla `tbl_seccion`
--
ALTER TABLE `tbl_seccion`
  ADD PRIMARY KEY (`sec_codigo`,`ani_anio`),
  ADD KEY `fk_ani_anio_ani_tipo_secion` (`ani_anio`,`ani_tipo`);

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
  ADD KEY `fk_uc_eje` (`eje_nombre`),
  ADD KEY `fk_uc_area` (`area_nombre`);

--
-- Indices de la tabla `titulo_docente`
--
ALTER TABLE `titulo_docente`
  ADD KEY `fk_doc_titulo` (`doc_cedula`),
  ADD KEY `fk_titulo_docente` (`tit_prefijo`,`tit_nombre`);

--
-- Indices de la tabla `uc_docente`
--
ALTER TABLE `uc_docente`
  ADD KEY `doc_uc` (`doc_cedula`),
  ADD KEY `uc_doc` (`uc_codigo`);

--
-- Indices de la tabla `uc_horario`
--
ALTER TABLE `uc_horario`
  ADD KEY `hor_uc` (`uc_codigo`),
  ADD KEY `uc_hor` (`sec_codigo`),
  ADD KEY `hor_espacio` (`esp_numero`,`esp_tipo`,`esp_edificio`);

--
-- Indices de la tabla `uc_malla`
--
ALTER TABLE `uc_malla`
  ADD KEY `malla_uc` (`uc_codigo`),
  ADD KEY `uc_malla` (`mal_codigo`);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `coordinacion_docente`
--
ALTER TABLE `coordinacion_docente`
  ADD CONSTRAINT `doc_cedula` FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cor_doc` FOREIGN KEY (`cor_nombre`) REFERENCES `tbl_coordinacion` (`cor_nombre`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `docente_horario`
--
ALTER TABLE `docente_horario`
  ADD CONSTRAINT `fk_doc_horario` FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_horario_doc` FOREIGN KEY (`sec_codigo`) REFERENCES `tbl_horario` (`sec_codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `per_aprobados`
--
ALTER TABLE `per_aprobados`
  ADD CONSTRAINT `fk_per_aprobados_anio` FOREIGN KEY (`ani_anio`,`ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_per_aprobados_seccion` FOREIGN KEY (`sec_codigo`) REFERENCES `tbl_seccion` (`sec_codigo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_per_aprobados_uc` FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_actividad`
--
ALTER TABLE `tbl_actividad`
  ADD CONSTRAINT `doc_actividad` FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_aprobados`
--
ALTER TABLE `tbl_aprobados`
  ADD CONSTRAINT `aprobados_anios` FOREIGN KEY (`ani_anio`,`ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `aprobados_docente` FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `aprobados_seccion` FOREIGN KEY (`sec_codigo`) REFERENCES `tbl_seccion` (`sec_codigo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `aprobados_uc` FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_docente`
--
ALTER TABLE `tbl_docente`
  ADD CONSTRAINT `doc_cat` FOREIGN KEY (`cat_nombre`) REFERENCES `tbl_categoria` (`cat_nombre`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_fase`
--
ALTER TABLE `tbl_fase`
  ADD CONSTRAINT `ani_anio_ani_tipo_fase` FOREIGN KEY (`ani_anio`) REFERENCES `tbl_anio` (`ani_anio`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_horario`
--
ALTER TABLE `tbl_horario`
  ADD CONSTRAINT `horario_seccion` FOREIGN KEY (`sec_codigo`) REFERENCES `tbl_seccion` (`sec_codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `horario_turno` FOREIGN KEY (`tur_nombre`) REFERENCES `tbl_turno` (`tur_nombre`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_per`
--
ALTER TABLE `tbl_per`
  ADD CONSTRAINT `per_ani_anio_ani_tipo` FOREIGN KEY (`ani_anio`,`ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_seccion`
--
ALTER TABLE `tbl_seccion`
  ADD CONSTRAINT `ani_anio_seccion` FOREIGN KEY (`ani_anio`,`ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tbl_uc`
--
ALTER TABLE `tbl_uc`
  ADD CONSTRAINT `fk_area_uc` FOREIGN KEY (`area_nombre`) REFERENCES `tbl_area` (`area_nombre`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_eje_uc` FOREIGN KEY (`eje_nombre`) REFERENCES `tbl_eje` (`eje_nombre`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `titulo_docente`
--
ALTER TABLE `titulo_docente`
  ADD CONSTRAINT `fk_doc_titulo` FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_titulo_docente` FOREIGN KEY (`tit_prefijo`,`tit_nombre`) REFERENCES `tbl_titulo` (`tit_prefijo`, `tit_nombre`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `uc_docente`
--
ALTER TABLE `uc_docente`
  ADD CONSTRAINT `doc_uc` FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `uc_doc` FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`);

--
-- Filtros para la tabla `uc_horario`
--
ALTER TABLE `uc_horario`
  ADD CONSTRAINT `hor_espacio` FOREIGN KEY (`esp_numero`,`esp_tipo`,`esp_edificio`) REFERENCES `tbl_espacio` (`esp_numero`, `esp_tipo`, `esp_edificio`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hor_uc` FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `uc_hor` FOREIGN KEY (`sec_codigo`) REFERENCES `tbl_horario` (`sec_codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `uc_malla`
--
ALTER TABLE `uc_malla`
  ADD CONSTRAINT `malla_uc` FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `uc_malla` FOREIGN KEY (`mal_codigo`) REFERENCES `tbl_malla` (`mal_codigo`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
