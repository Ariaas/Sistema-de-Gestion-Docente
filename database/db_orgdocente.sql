-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-09-2025 a las 07:30:26
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
('Doctorados', 7404027, 1),
('PNFA-PNFI', 7404027, 1),
('Gestión TIC', 7404027, 1),
('Eje Etico Político', 3759671, 1),
('Eje Epistemológico', 3759671, 1),
('Tutorías Académicas', 3759671, 1),
('Centro de Estudio Investigació', 9619518, 1),
('EMTICL', 9619518, 1),
('Laboratorios', 9619518, 1),
('Educación Municipalizada', 9619518, 1),
('Proyecto Sociotecnológico I', 10723015, 1),
('EMTICL', 10723015, 1),
('Ingenierías', 10723015, 1),
('Proyecto Sociotecnológico IV', 10723015, 1),
('Idiomas', 13188691, 1),
('Laboratorios', 13188691, 1),
('Eje Estético Lúdico', 18103232, 1),
('Deporte', 18103232, 1),
('Investigación', 10775753, 1),
('Gestión TIC', 10775753, 1),
('Preparaduría', 14293781, 1),
('Currículo', 25471240, 1),
('PNFA-PNFI', 25471240, 1),
('Proyecto Sociotecnológico III', 25471240, 1),
('Proyecto Sociotecnológico II', 5260810, 1),
('Nocturna y Fin de Semana', 10956121, 1),
('Laboratorios', 10956121, 1),
('Académica', 7439117, 1),
('EMTICL', 7439117, 1),
('Preparaduría', 14677589, 1),
('Laboratorios', 10778236, 1),
('Doctorados', 7404027, 1),
('PNFA-PNFI', 7404027, 1),
('Gestión TIC', 7404027, 1),
('Eje Etico Político', 3759671, 1),
('Eje Epistemológico', 3759671, 1),
('Tutorías Académicas', 3759671, 1),
('Centro de Estudio Investigació', 9619518, 1),
('EMTICL', 9619518, 1),
('Laboratorios', 9619518, 1),
('Educación Municipalizada', 9619518, 1),
('Proyecto Sociotecnológico I', 10723015, 1),
('EMTICL', 10723015, 1),
('Ingenierías', 10723015, 1),
('Proyecto Sociotecnológico IV', 10723015, 1),
('Idiomas', 13188691, 1),
('Laboratorios', 13188691, 1),
('Eje Estético Lúdico', 18103232, 1),
('Deporte', 18103232, 1),
('Investigación', 10775753, 1),
('Gestión TIC', 10775753, 1),
('Preparaduría', 14293781, 1),
('Currículo', 25471240, 1),
('PNFA-PNFI', 25471240, 1),
('Proyecto Sociotecnológico III', 25471240, 1),
('Proyecto Sociotecnológico II', 5260810, 1),
('Nocturna y Fin de Semana', 10956121, 1),
('Laboratorios', 10956121, 1),
('Académica', 7439117, 1),
('EMTICL', 7439117, 1),
('Preparaduría', 14677589, 1),
('Laboratorios', 10778236, 1),
('Formación Crítica TI, TII, TII', 13527711, 1),
('Formación Crítica TI, TII, TII', 23316126, 1),
('Formación Crítica TI, TII, TII', 15170003, 1),
('Formación Crítica TI, TII, TII', 24418577, 1),
('Formación Crítica TI, TII, TII', 9629702, 1),
('Formación Crítica TI, TII, TII', 14159756, 1),
('Algorítmica y Programación', 7439117, 1),
('Algorítmica y Programación', 10723015, 1),
('Algorítmica y Programación', 10846157, 1),
('Algorítmica y Programación', 15693145, 1),
('Algorítmica y Programación', 9629702, 1),
('Algorítmica y Programación', 7391773, 1),
('Matemática I, II, III, IV', 14677589, 1),
('Matemática I, II, III, IV', 9627295, 1),
('Matemática I, II, III, IV', 17354607, 1),
('Matemática I, II, III, IV', 15351688, 1),
('Matemática I, II, III, IV', 10775753, 1),
('Proyecto Sociotecnológico I', 7423485, 1),
('Proyecto Sociotecnológico I', 15170003, 1),
('Proyecto Sociotecnológico I', 26197135, 1),
('Proyecto Sociotecnológico I', 10848316, 1),
('Proyecto Sociotecnológico I', 13527711, 1),
('Proyecto Sociotecnológico II', 25471240, 1),
('Proyecto Sociotecnológico II', 13991250, 1),
('Proyecto Sociotecnológico II', 16403903, 1),
('Proyecto Sociotecnológico II', 7439117, 1),
('Proyecto Sociotecnológico II', 9540060, 1),
('Proyecto Sociotecnológico II', 9555514, 1),
('Proyecto Sociotecnológico III', 7391773, 1),
('Proyecto Sociotecnológico III', 10844463, 1),
('Proyecto Sociotecnológico IV', 16403903, 1),
('Actividades Acreditables I, II', 12701387, 1),
('Actividades Acreditables I, II', 18103232, 1),
('Actividades Acreditables I, II', 16385182, 1),
('Actividades Acreditables I, II', 20351422, 1),
('Electiva 1', 11264888, 1),
('Electiva 1', 29880797, 1),
('Electiva 1', 30395804, 1),
('Electiva 1', 26197135, 1),
('Idiomas I', 18356682, 1),
('Idiomas I', 13695847, 1),
('Idiomas II', 18356682, 1),
('Arquitectura del Computador', 10778236, 1),
('Arquitectura del Computador', 11264888, 1),
('Arquitectura del Computador', 11898335, 1),
('Arquitectura del Computador', 7424546, 1),
('Arquitectura del Computador', 10844463, 1),
('Programación', 25471240, 1),
('Programación', 13188691, 1),
('Programación', 10846157, 1),
('Programación', 29517943, 1),
('Electiva II', 30395804, 1),
('Electiva II', 30088284, 1),
('Electiva II', 9555514, 1),
('Electiva II', 5260810, 1),
('Electiva II', 29517943, 1),
('Ingeniería del Software', 13991971, 1),
('Ingeniería del Software', 11264888, 1),
('Ingeniería del Software', 30395804, 1),
('Ingeniería del Software', 9555514, 1),
('Redes del Computador', 11898335, 1),
('Redes del Computador', 13188691, 1),
('Redes del Computador', 14159756, 1),
('Base de Datos', 5260810, 1),
('Base de Datos', 13991250, 1),
('Base de Datos', 9555514, 1),
('Base de Datos', 9540060, 1),
('Matemática Aplicada', 14677589, 1),
('Matemática Aplicada', 18912216, 1),
('Ingeniería del Software II', 13991971, 1),
('Ingeniería del Software II', 30395804, 1),
('Electiva III', 25471240, 1),
('Electiva III', 30088284, 1),
('Modelado de Base de Datos', 5260810, 1),
('Modelado de Base de Datos', 10844463, 1),
('Modelado de Base de Datos', 9540060, 1),
('Gestión de Proyectos', 16403903, 1),
('Auditoria de Sistemas', 16403903, 1),
('Electiva IV', 7391773, 1),
('Administración de Base de Dato', 5260810, 1),
('Seguridad', 25471240, 1),
('Redes Avanzadas', 5260810, 1),
('Doctorados', 7404027, 1),
('PNFA-PNFI', 7404027, 1),
('Gestión TIC', 7404027, 1),
('Eje Etico Político', 3759671, 1),
('Eje Epistemológico', 3759671, 1),
('Tutorías Académicas', 3759671, 1),
('Centro de Estudio Investigació', 9619518, 1),
('EMTICL', 9619518, 1),
('Laboratorios', 9619518, 1),
('Educación Municipalizada', 9619518, 1),
('Proyecto Sociotecnológico I', 10723015, 1),
('EMTICL', 10723015, 1),
('Ingenierías', 10723015, 1),
('Proyecto Sociotecnológico IV', 10723015, 1),
('Idiomas', 13188691, 1),
('Laboratorios', 13188691, 1),
('Eje Estético Lúdico', 18103232, 1),
('Deporte', 18103232, 1),
('Investigación', 10775753, 1),
('Gestión TIC', 10775753, 1),
('Preparaduría', 14293781, 1),
('Currículo', 25471240, 1),
('PNFA-PNFI', 25471240, 1),
('Proyecto Sociotecnológico III', 25471240, 1),
('Proyecto Sociotecnológico II', 5260810, 1),
('Nocturna y Fin de Semana', 10956121, 1),
('Laboratorios', 10956121, 1),
('Académica', 7439117, 1),
('EMTICL', 7439117, 1),
('Preparaduría', 14677589, 1),
('Laboratorios', 10778236, 1),
('Doctorados', 7404027, 1),
('PNFA-PNFI', 7404027, 1),
('Gestión TIC', 7404027, 1),
('Eje Etico Político', 3759671, 1),
('Eje Epistemológico', 3759671, 1),
('Tutorías Académicas', 3759671, 1),
('Centro de Estudio Investigació', 9619518, 1),
('EMTICL', 9619518, 1),
('Laboratorios', 9619518, 1),
('Educación Municipalizada', 9619518, 1),
('Proyecto Sociotecnológico I', 10723015, 1),
('EMTICL', 10723015, 1),
('Ingenierías', 10723015, 1),
('Proyecto Sociotecnológico IV', 10723015, 1),
('Idiomas', 13188691, 1),
('Laboratorios', 13188691, 1),
('Eje Estético Lúdico', 18103232, 1),
('Deporte', 18103232, 1),
('Investigación', 10775753, 1),
('Gestión TIC', 10775753, 1),
('Preparaduría', 14293781, 1),
('Currículo', 25471240, 1),
('PNFA-PNFI', 25471240, 1),
('Proyecto Sociotecnológico III', 25471240, 1),
('Proyecto Sociotecnológico II', 5260810, 1),
('Nocturna y Fin de Semana', 10956121, 1),
('Laboratorios', 10956121, 1),
('Académica', 7439117, 1),
('EMTICL', 7439117, 1),
('Preparaduría', 14677589, 1),
('Laboratorios', 10778236, 1),
('Formación Crítica TI, TII, TII', 13527711, 1),
('Formación Crítica TI, TII, TII', 23316126, 1),
('Formación Crítica TI, TII, TII', 15170003, 1),
('Formación Crítica TI, TII, TII', 24418577, 1),
('Formación Crítica TI, TII, TII', 9629702, 1),
('Formación Crítica TI, TII, TII', 14159756, 1),
('Algorítmica y Programación', 7439117, 1),
('Algorítmica y Programación', 10723015, 1),
('Algorítmica y Programación', 10846157, 1),
('Algorítmica y Programación', 15693145, 1),
('Algorítmica y Programación', 9629702, 1),
('Algorítmica y Programación', 7391773, 1),
('Matemática I, II, III, IV', 14677589, 1),
('Matemática I, II, III, IV', 9627295, 1),
('Matemática I, II, III, IV', 17354607, 1),
('Matemática I, II, III, IV', 15351688, 1),
('Matemática I, II, III, IV', 10775753, 1),
('Proyecto Sociotecnológico I', 7423485, 1),
('Proyecto Sociotecnológico I', 15170003, 1),
('Proyecto Sociotecnológico I', 26197135, 1),
('Proyecto Sociotecnológico I', 10848316, 1),
('Proyecto Sociotecnológico I', 13527711, 1),
('Proyecto Sociotecnológico II', 25471240, 1),
('Proyecto Sociotecnológico II', 13991250, 1),
('Proyecto Sociotecnológico II', 16403903, 1),
('Proyecto Sociotecnológico II', 7439117, 1),
('Proyecto Sociotecnológico II', 9540060, 1),
('Proyecto Sociotecnológico II', 9555514, 1),
('Proyecto Sociotecnológico III', 7391773, 1),
('Proyecto Sociotecnológico III', 10844463, 1),
('Proyecto Sociotecnológico IV', 16403903, 1),
('Actividades Acreditables I, II', 12701387, 1),
('Actividades Acreditables I, II', 18103232, 1),
('Actividades Acreditables I, II', 16385182, 1),
('Actividades Acreditables I, II', 20351422, 1),
('Electiva 1', 11264888, 1),
('Electiva 1', 29880797, 1),
('Electiva 1', 30395804, 1),
('Electiva 1', 26197135, 1),
('Idiomas I', 18356682, 1),
('Idiomas I', 13695847, 1),
('Idiomas II', 18356682, 1),
('Arquitectura del Computador', 10778236, 1),
('Arquitectura del Computador', 11264888, 1),
('Arquitectura del Computador', 11898335, 1),
('Arquitectura del Computador', 7424546, 1),
('Arquitectura del Computador', 10844463, 1),
('Programación', 25471240, 1),
('Programación', 13188691, 1),
('Programación', 10846157, 1),
('Programación', 29517943, 1),
('Electiva II', 30395804, 1),
('Electiva II', 30088284, 1),
('Electiva II', 9555514, 1),
('Electiva II', 5260810, 1),
('Electiva II', 29517943, 1),
('Ingeniería del Software', 13991971, 1),
('Ingeniería del Software', 11264888, 1),
('Ingeniería del Software', 30395804, 1),
('Ingeniería del Software', 9555514, 1),
('Redes del Computador', 11898335, 1),
('Redes del Computador', 13188691, 1),
('Redes del Computador', 14159756, 1),
('Base de Datos', 5260810, 1),
('Base de Datos', 13991250, 1),
('Base de Datos', 9555514, 1),
('Base de Datos', 9540060, 1),
('Matemática Aplicada', 14677589, 1),
('Matemática Aplicada', 18912216, 1),
('Ingeniería del Software II', 13991971, 1),
('Ingeniería del Software II', 30395804, 1),
('Electiva III', 25471240, 1),
('Electiva III', 30088284, 1),
('Modelado de Base de Datos', 5260810, 1),
('Modelado de Base de Datos', 10844463, 1),
('Modelado de Base de Datos', 9540060, 1),
('Gestión de Proyectos', 16403903, 1),
('Auditoria de Sistemas', 16403903, 1),
('Electiva IV', 7391773, 1),
('Administración de Base de Dato', 5260810, 1),
('Seguridad', 25471240, 1),
('Redes Avanzadas', 5260810, 1),
('Doctorados', 7404027, 1),
('PNFA-PNFI', 7404027, 1),
('Gestión TIC', 7404027, 1),
('Eje Etico Político', 3759671, 1),
('Eje Epistemológico', 3759671, 1),
('Tutorías Académicas', 3759671, 1),
('Centro de Estudio Investigació', 9619518, 1),
('EMTICL', 9619518, 1),
('Laboratorios', 9619518, 1),
('Educación Municipalizada', 9619518, 1),
('Proyecto Sociotecnológico I', 10723015, 1),
('EMTICL', 10723015, 1),
('Ingenierías', 10723015, 1),
('Proyecto Sociotecnológico IV', 10723015, 1),
('Idiomas', 13188691, 1),
('Laboratorios', 13188691, 1),
('Eje Estético Lúdico', 18103232, 1),
('Deporte', 18103232, 1),
('Investigación', 10775753, 1),
('Gestión TIC', 10775753, 1),
('Preparaduría', 14293781, 1),
('Currículo', 25471240, 1),
('PNFA-PNFI', 25471240, 1),
('Proyecto Sociotecnológico III', 25471240, 1),
('Proyecto Sociotecnológico II', 5260810, 1),
('Nocturna y Fin de Semana', 10956121, 1),
('Laboratorios', 10956121, 1),
('Académica', 7439117, 1),
('EMTICL', 7439117, 1),
('Preparaduría', 14677589, 1),
('Laboratorios', 10778236, 1),
('Doctorados', 7404027, 1),
('PNFA-PNFI', 7404027, 1),
('Gestión TIC', 7404027, 1),
('Eje Etico Político', 3759671, 1),
('Eje Epistemológico', 3759671, 1),
('Tutorías Académicas', 3759671, 1),
('Centro de Estudio Investigació', 9619518, 1),
('EMTICL', 9619518, 1),
('Laboratorios', 9619518, 1),
('Educación Municipalizada', 9619518, 1),
('Proyecto Sociotecnológico I', 10723015, 1),
('EMTICL', 10723015, 1),
('Ingenierías', 10723015, 1),
('Proyecto Sociotecnológico IV', 10723015, 1),
('Idiomas', 13188691, 1),
('Laboratorios', 13188691, 1),
('Eje Estético Lúdico', 18103232, 1),
('Deporte', 18103232, 1),
('Investigación', 10775753, 1),
('Gestión TIC', 10775753, 1),
('Preparaduría', 14293781, 1),
('Currículo', 25471240, 1),
('PNFA-PNFI', 25471240, 1),
('Proyecto Sociotecnológico III', 25471240, 1),
('Proyecto Sociotecnológico II', 5260810, 1),
('Nocturna y Fin de Semana', 10956121, 1),
('Laboratorios', 10956121, 1),
('Académica', 7439117, 1),
('EMTICL', 7439117, 1),
('Preparaduría', 14677589, 1),
('Laboratorios', 10778236, 1),
('Formación Crítica TI, TII, TII', 13527711, 1),
('Formación Crítica TI, TII, TII', 23316126, 1),
('Formación Crítica TI, TII, TII', 15170003, 1),
('Formación Crítica TI, TII, TII', 24418577, 1),
('Formación Crítica TI, TII, TII', 9629702, 1),
('Formación Crítica TI, TII, TII', 14159756, 1),
('Algorítmica y Programación', 7439117, 1),
('Algorítmica y Programación', 10723015, 1),
('Algorítmica y Programación', 10846157, 1),
('Algorítmica y Programación', 15693145, 1),
('Algorítmica y Programación', 9629702, 1),
('Algorítmica y Programación', 7391773, 1),
('Matemática I, II, III, IV', 14677589, 1),
('Matemática I, II, III, IV', 9627295, 1),
('Matemática I, II, III, IV', 17354607, 1),
('Matemática I, II, III, IV', 15351688, 1),
('Matemática I, II, III, IV', 10775753, 1),
('Proyecto Sociotecnológico I', 7423485, 1),
('Proyecto Sociotecnológico I', 15170003, 1),
('Proyecto Sociotecnológico I', 26197135, 1),
('Proyecto Sociotecnológico I', 10848316, 1),
('Proyecto Sociotecnológico I', 13527711, 1),
('Proyecto Sociotecnológico II', 25471240, 1),
('Proyecto Sociotecnológico II', 13991250, 1),
('Proyecto Sociotecnológico II', 16403903, 1),
('Proyecto Sociotecnológico II', 7439117, 1),
('Proyecto Sociotecnológico II', 9540060, 1),
('Proyecto Sociotecnológico II', 9555514, 1),
('Proyecto Sociotecnológico III', 7391773, 1),
('Proyecto Sociotecnológico III', 10844463, 1),
('Proyecto Sociotecnológico IV', 16403903, 1),
('Actividades Acreditables I, II', 12701387, 1),
('Actividades Acreditables I, II', 18103232, 1),
('Actividades Acreditables I, II', 16385182, 1),
('Actividades Acreditables I, II', 20351422, 1),
('Electiva 1', 11264888, 1),
('Electiva 1', 29880797, 1),
('Electiva 1', 30395804, 1),
('Electiva 1', 26197135, 1),
('Idiomas I', 18356682, 1),
('Idiomas I', 13695847, 1),
('Idiomas II', 18356682, 1),
('Arquitectura del Computador', 10778236, 1),
('Arquitectura del Computador', 11264888, 1),
('Arquitectura del Computador', 11898335, 1),
('Arquitectura del Computador', 7424546, 1),
('Arquitectura del Computador', 10844463, 1),
('Programación', 25471240, 1),
('Programación', 13188691, 1),
('Programación', 10846157, 1),
('Programación', 29517943, 1),
('Electiva II', 30395804, 1),
('Electiva II', 30088284, 1),
('Electiva II', 9555514, 1),
('Electiva II', 5260810, 1),
('Electiva II', 29517943, 1),
('Ingeniería del Software', 13991971, 1),
('Ingeniería del Software', 11264888, 1),
('Ingeniería del Software', 30395804, 1),
('Ingeniería del Software', 9555514, 1),
('Redes del Computador', 11898335, 1),
('Redes del Computador', 13188691, 1),
('Redes del Computador', 14159756, 1),
('Base de Datos', 5260810, 1),
('Base de Datos', 13991250, 1),
('Base de Datos', 9555514, 1),
('Base de Datos', 9540060, 1),
('Matemática Aplicada', 14677589, 1),
('Matemática Aplicada', 18912216, 1),
('Ingeniería del Software II', 13991971, 1),
('Ingeniería del Software II', 30395804, 1),
('Electiva III', 25471240, 1),
('Electiva III', 30088284, 1),
('Modelado de Base de Datos', 5260810, 1),
('Modelado de Base de Datos', 10844463, 1),
('Modelado de Base de Datos', 9540060, 1),
('Gestión de Proyectos', 16403903, 1),
('Auditoria de Sistemas', 16403903, 1),
('Electiva IV', 7391773, 1),
('Administración de Base de Dato', 5260810, 1),
('Seguridad', 25471240, 1),
('Redes Avanzadas', 5260810, 1),
('Doctorados', 7404027, 1),
('PNFA-PNFI', 7404027, 1),
('Gestión TIC', 7404027, 1),
('Eje Etico Político', 3759671, 1),
('Eje Epistemológico', 3759671, 1),
('Tutorías Académicas', 3759671, 1),
('Centro de Estudio Investigació', 9619518, 1),
('EMTICL', 9619518, 1),
('Laboratorios', 9619518, 1),
('Educación Municipalizada', 9619518, 1),
('Proyecto Sociotecnológico I', 10723015, 1),
('EMTICL', 10723015, 1),
('Ingenierías', 10723015, 1),
('Proyecto Sociotecnológico IV', 10723015, 1),
('Idiomas', 13188691, 1),
('Laboratorios', 13188691, 1),
('Eje Estético Lúdico', 18103232, 1),
('Deporte', 18103232, 1),
('Investigación', 10775753, 1),
('Gestión TIC', 10775753, 1),
('Preparaduría', 14293781, 1),
('Currículo', 25471240, 1),
('PNFA-PNFI', 25471240, 1),
('Proyecto Sociotecnológico III', 25471240, 1),
('Proyecto Sociotecnológico II', 5260810, 1),
('Nocturna y Fin de Semana', 10956121, 1),
('Laboratorios', 10956121, 1),
('Académica', 7439117, 1),
('EMTICL', 7439117, 1),
('Preparaduría', 14677589, 1),
('Laboratorios', 10778236, 1),
('Doctorados', 7404027, 1),
('PNFA-PNFI', 7404027, 1),
('Gestión TIC', 7404027, 1),
('Eje Etico Político', 3759671, 1),
('Eje Epistemológico', 3759671, 1),
('Tutorías Académicas', 3759671, 1),
('Centro de Estudio Investigació', 9619518, 1),
('EMTICL', 9619518, 1),
('Laboratorios', 9619518, 1),
('Educación Municipalizada', 9619518, 1),
('Proyecto Sociotecnológico I', 10723015, 1),
('EMTICL', 10723015, 1),
('Ingenierías', 10723015, 1),
('Proyecto Sociotecnológico IV', 10723015, 1),
('Idiomas', 13188691, 1),
('Laboratorios', 13188691, 1),
('Eje Estético Lúdico', 18103232, 1),
('Deporte', 18103232, 1),
('Investigación', 10775753, 1),
('Gestión TIC', 10775753, 1),
('Preparaduría', 14293781, 1),
('Currículo', 25471240, 1),
('PNFA-PNFI', 25471240, 1),
('Proyecto Sociotecnológico III', 25471240, 1),
('Proyecto Sociotecnológico II', 5260810, 1),
('Nocturna y Fin de Semana', 10956121, 1),
('Laboratorios', 10956121, 1),
('Académica', 7439117, 1),
('EMTICL', 7439117, 1),
('Preparaduría', 14677589, 1),
('Laboratorios', 10778236, 1),
('Formación Crítica TI, TII, TII', 13527711, 1),
('Formación Crítica TI, TII, TII', 23316126, 1),
('Formación Crítica TI, TII, TII', 15170003, 1),
('Formación Crítica TI, TII, TII', 24418577, 1),
('Formación Crítica TI, TII, TII', 9629702, 1),
('Formación Crítica TI, TII, TII', 14159756, 1),
('Algorítmica y Programación', 7439117, 1),
('Algorítmica y Programación', 10723015, 1),
('Algorítmica y Programación', 10846157, 1),
('Algorítmica y Programación', 15693145, 1),
('Algorítmica y Programación', 9629702, 1),
('Algorítmica y Programación', 7391773, 1),
('Matemática I, II, III, IV', 14677589, 1),
('Matemática I, II, III, IV', 9627295, 1),
('Matemática I, II, III, IV', 17354607, 1),
('Matemática I, II, III, IV', 15351688, 1),
('Matemática I, II, III, IV', 10775753, 1),
('Proyecto Sociotecnológico I', 7423485, 1),
('Proyecto Sociotecnológico I', 15170003, 1),
('Proyecto Sociotecnológico I', 26197135, 1),
('Proyecto Sociotecnológico I', 10848316, 1),
('Proyecto Sociotecnológico I', 13527711, 1),
('Proyecto Sociotecnológico II', 25471240, 1),
('Proyecto Sociotecnológico II', 13991250, 1),
('Proyecto Sociotecnológico II', 16403903, 1),
('Proyecto Sociotecnológico II', 7439117, 1),
('Proyecto Sociotecnológico II', 9540060, 1),
('Proyecto Sociotecnológico II', 9555514, 1),
('Proyecto Sociotecnológico III', 7391773, 1),
('Proyecto Sociotecnológico III', 10844463, 1),
('Proyecto Sociotecnológico IV', 16403903, 1),
('Actividades Acreditables I, II', 12701387, 1),
('Actividades Acreditables I, II', 18103232, 1),
('Actividades Acreditables I, II', 16385182, 1),
('Actividades Acreditables I, II', 20351422, 1),
('Electiva 1', 11264888, 1),
('Electiva 1', 29880797, 1),
('Electiva 1', 30395804, 1),
('Electiva 1', 26197135, 1),
('Idiomas I', 18356682, 1),
('Idiomas I', 13695847, 1),
('Idiomas II', 18356682, 1),
('Arquitectura del Computador', 10778236, 1),
('Arquitectura del Computador', 11264888, 1),
('Arquitectura del Computador', 11898335, 1),
('Arquitectura del Computador', 7424546, 1),
('Arquitectura del Computador', 10844463, 1),
('Programación', 25471240, 1),
('Programación', 13188691, 1),
('Programación', 10846157, 1),
('Programación', 29517943, 1),
('Electiva II', 30395804, 1),
('Electiva II', 30088284, 1),
('Electiva II', 9555514, 1),
('Electiva II', 5260810, 1),
('Electiva II', 29517943, 1),
('Ingeniería del Software', 13991971, 1),
('Ingeniería del Software', 11264888, 1),
('Ingeniería del Software', 30395804, 1),
('Ingeniería del Software', 9555514, 1),
('Redes del Computador', 11898335, 1),
('Redes del Computador', 13188691, 1),
('Redes del Computador', 14159756, 1),
('Base de Datos', 5260810, 1),
('Base de Datos', 13991250, 1),
('Base de Datos', 9555514, 1),
('Base de Datos', 9540060, 1),
('Matemática Aplicada', 14677589, 1),
('Matemática Aplicada', 18912216, 1),
('Ingeniería del Software II', 13991971, 1),
('Ingeniería del Software II', 30395804, 1),
('Electiva III', 25471240, 1),
('Electiva III', 30088284, 1),
('Modelado de Base de Datos', 5260810, 1),
('Modelado de Base de Datos', 10844463, 1),
('Modelado de Base de Datos', 9540060, 1),
('Gestión de Proyectos', 16403903, 1),
('Auditoria de Sistemas', 16403903, 1),
('Electiva IV', 7391773, 1),
('Administración de Base de Dato', 5260810, 1),
('Seguridad', 25471240, 1),
('Redes Avanzadas', 5260810, 1),
('Doctorados', 7404027, 1),
('PNFA-PNFI', 7404027, 1),
('Gestión TIC', 7404027, 1),
('Eje Etico Político', 3759671, 1),
('Eje Epistemológico', 3759671, 1),
('Tutorías Académicas', 3759671, 1),
('Centro de Estudio Investigació', 9619518, 1),
('EMTICL', 9619518, 1),
('Laboratorios', 9619518, 1),
('Educación Municipalizada', 9619518, 1),
('Proyecto Sociotecnológico I', 10723015, 1),
('EMTICL', 10723015, 1),
('Ingenierías', 10723015, 1),
('Proyecto Sociotecnológico IV', 10723015, 1),
('Idiomas', 13188691, 1),
('Laboratorios', 13188691, 1),
('Eje Estético Lúdico', 18103232, 1),
('Deporte', 18103232, 1),
('Investigación', 10775753, 1),
('Gestión TIC', 10775753, 1),
('Preparaduría', 14293781, 1),
('Currículo', 25471240, 1),
('PNFA-PNFI', 25471240, 1),
('Proyecto Sociotecnológico III', 25471240, 1),
('Proyecto Sociotecnológico II', 5260810, 1),
('Nocturna y Fin de Semana', 10956121, 1),
('Laboratorios', 10956121, 1),
('Académica', 7439117, 1),
('EMTICL', 7439117, 1),
('Preparaduría', 14677589, 1),
('Laboratorios', 10778236, 1),
('Doctorados', 7404027, 1),
('PNFA-PNFI', 7404027, 1),
('Gestión TIC', 7404027, 1),
('Eje Etico Político', 3759671, 1),
('Eje Epistemológico', 3759671, 1),
('Tutorías Académicas', 3759671, 1),
('Centro de Estudio Investigació', 9619518, 1),
('EMTICL', 9619518, 1),
('Laboratorios', 9619518, 1),
('Educación Municipalizada', 9619518, 1),
('Proyecto Sociotecnológico I', 10723015, 1),
('EMTICL', 10723015, 1),
('Ingenierías', 10723015, 1),
('Proyecto Sociotecnológico IV', 10723015, 1),
('Idiomas', 13188691, 1),
('Laboratorios', 13188691, 1),
('Eje Estético Lúdico', 18103232, 1),
('Deporte', 18103232, 1),
('Investigación', 10775753, 1),
('Gestión TIC', 10775753, 1),
('Preparaduría', 14293781, 1),
('Currículo', 25471240, 1),
('PNFA-PNFI', 25471240, 1),
('Proyecto Sociotecnológico III', 25471240, 1),
('Proyecto Sociotecnológico II', 5260810, 1),
('Nocturna y Fin de Semana', 10956121, 1),
('Laboratorios', 10956121, 1),
('Académica', 7439117, 1),
('EMTICL', 7439117, 1),
('Preparaduría', 14677589, 1),
('Laboratorios', 10778236, 1),
('Formación Crítica TI, TII, TII', 13527711, 1),
('Formación Crítica TI, TII, TII', 23316126, 1),
('Formación Crítica TI, TII, TII', 15170003, 1),
('Formación Crítica TI, TII, TII', 24418577, 1),
('Formación Crítica TI, TII, TII', 9629702, 1),
('Formación Crítica TI, TII, TII', 14159756, 1),
('Algorítmica y Programación', 7439117, 1),
('Algorítmica y Programación', 10723015, 1),
('Algorítmica y Programación', 10846157, 1),
('Algorítmica y Programación', 15693145, 1),
('Algorítmica y Programación', 9629702, 1),
('Algorítmica y Programación', 7391773, 1),
('Matemática I, II, III, IV', 14677589, 1),
('Matemática I, II, III, IV', 9627295, 1),
('Matemática I, II, III, IV', 17354607, 1),
('Matemática I, II, III, IV', 15351688, 1),
('Matemática I, II, III, IV', 10775753, 1),
('Proyecto Sociotecnológico I', 7423485, 1),
('Proyecto Sociotecnológico I', 15170003, 1),
('Proyecto Sociotecnológico I', 26197135, 1),
('Proyecto Sociotecnológico I', 10848316, 1),
('Proyecto Sociotecnológico I', 13527711, 1),
('Proyecto Sociotecnológico II', 25471240, 1),
('Proyecto Sociotecnológico II', 13991250, 1),
('Proyecto Sociotecnológico II', 16403903, 1),
('Proyecto Sociotecnológico II', 7439117, 1),
('Proyecto Sociotecnológico II', 9540060, 1),
('Proyecto Sociotecnológico II', 9555514, 1),
('Proyecto Sociotecnológico III', 7391773, 1),
('Proyecto Sociotecnológico III', 10844463, 1),
('Proyecto Sociotecnológico IV', 16403903, 1),
('Actividades Acreditables I, II', 12701387, 1),
('Actividades Acreditables I, II', 18103232, 1),
('Actividades Acreditables I, II', 16385182, 1),
('Actividades Acreditables I, II', 20351422, 1),
('Electiva 1', 11264888, 1),
('Electiva 1', 29880797, 1),
('Electiva 1', 30395804, 1),
('Electiva 1', 26197135, 1),
('Idiomas I', 18356682, 1),
('Idiomas I', 13695847, 1),
('Idiomas II', 18356682, 1),
('Arquitectura del Computador', 10778236, 1),
('Arquitectura del Computador', 11264888, 1),
('Arquitectura del Computador', 11898335, 1),
('Arquitectura del Computador', 7424546, 1),
('Arquitectura del Computador', 10844463, 1),
('Programación', 25471240, 1),
('Programación', 13188691, 1),
('Programación', 10846157, 1),
('Programación', 29517943, 1),
('Electiva II', 30395804, 1),
('Electiva II', 30088284, 1),
('Electiva II', 9555514, 1),
('Electiva II', 5260810, 1),
('Electiva II', 29517943, 1),
('Ingeniería del Software', 13991971, 1),
('Ingeniería del Software', 11264888, 1),
('Ingeniería del Software', 30395804, 1),
('Ingeniería del Software', 9555514, 1),
('Redes del Computador', 11898335, 1),
('Redes del Computador', 13188691, 1),
('Redes del Computador', 14159756, 1),
('Base de Datos', 5260810, 1),
('Base de Datos', 13991250, 1),
('Base de Datos', 9555514, 1),
('Base de Datos', 9540060, 1),
('Matemática Aplicada', 14677589, 1),
('Matemática Aplicada', 18912216, 1),
('Ingeniería del Software II', 13991971, 1),
('Ingeniería del Software II', 30395804, 1),
('Electiva III', 25471240, 1),
('Electiva III', 30088284, 1),
('Modelado de Base de Datos', 5260810, 1),
('Modelado de Base de Datos', 10844463, 1),
('Modelado de Base de Datos', 9540060, 1),
('Gestión de Proyectos', 16403903, 1),
('Auditoria de Sistemas', 16403903, 1),
('Electiva IV', 7391773, 1),
('Administración de Base de Dato', 5260810, 1),
('Seguridad', 25471240, 1),
('Redes Avanzadas', 5260810, 1),
('Investigación de Operaciones', 7392496, 1),
('Proyecto Sociotecnológico III', 7392496, 1),
('Sistemas Operativos', 7392496, 1);

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
(5260810, '4403'),
(16403903, '4403'),
(16385182, '4403'),
(13527711, '4403'),
(18356682, '4403'),
(7391773, '4403'),
(16385182, '2103'),
(24418577, '2103'),
(17354607, '2103'),
(13188691, '2103'),
(16403903, '2113'),
(10846157, '2113'),
(24418577, '2113'),
(18103232, '2113'),
(14677589, '2113'),
(13188691, '2113'),
(9555514, '2113'),
(29517943, '2123'),
(7439117, '2123'),
(17354607, '2123'),
(9629702, '2123'),
(12701387, '2123'),
(11898335, '2123'),
(5260810, '2123'),
(29517943, '2133'),
(9540060, '2133'),
(18103232, '2133'),
(30088284, '2133'),
(14159756, '2133'),
(10775753, '2133'),
(13188691, '2403'),
(20351422, '2403'),
(29517943, '2403'),
(9555514, '2403'),
(15351688, '2403'),
(25471240, '2403'),
(14159756, '2403'),
(9629702, '1143'),
(10848316, '1143'),
(16385182, '1143'),
(30395804, '1143'),
(15351688, '1143'),
(10844463, '1143'),
(10846157, '1213'),
(18912216, '1213'),
(14677589, '1133'),
(12701387, '1133'),
(24418577, '1133'),
(7423486, '1133'),
(13527711, '1133'),
(10846157, '1133'),
(7424546, '1133'),
(12701387, '1403'),
(7423486, '1403'),
(13527711, '1403'),
(17354607, '1403'),
(7423485, '1403'),
(15693145, '1403'),
(7424546, '1403'),
(23316126, '1203'),
(18103232, '1203'),
(26197135, '1203'),
(15351688, '1203'),
(7391773, '1203'),
(10844463, '1203'),
(9118178, '1103'),
(10778236, '1103'),
(18103232, '1103'),
(23316126, '1103'),
(29880797, '1103'),
(15170003, '1103'),
(9627295, '1103'),
(15170003, '1113'),
(29880797, '1113'),
(18103232, '1113'),
(7439117, '1113'),
(11264888, '1113'),
(10723015, '1123'),
(11898335, '1123'),
(10848316, '1123'),
(14677589, '1123'),
(29880797, '1123'),
(18103232, '1123'),
(23316126, '1123'),
(13527711, '0103'),
(13695847, '0103'),
(9627295, '0103'),
(17354607, '0113'),
(13527711, '0113'),
(13695847, '0113'),
(9627295, '0123'),
(10848316, '0123'),
(18356682, '0123'),
(9118178, '0403'),
(10775753, '0403'),
(14159756, '0403'),
(18356682, '0423'),
(10775753, '0423'),
(14159756, '0423');

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
(30395804, 10, 5, 0, 5, 5, 1);

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
('Instructor', 1, 'Instructor'),
('Titular', 1, 'Titular');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_coordinacion`
--

CREATE TABLE `tbl_coordinacion` (
  `cor_nombre` varchar(30) NOT NULL,
  `cor_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_coordinacion`
--

INSERT INTO `tbl_coordinacion` (`cor_nombre`, `cor_estado`) VALUES
('Académica', 1),
('Actividades Acreditables I, II', 1),
('Administración de Base de Dato', 1),
('Algorítmica y Programación', 1),
('Arquitectura del Computador', 1),
('Auditoria de Sistemas', 1),
('Base de Datos', 1),
('Centro de Estudio Investigació', 1),
('Currículo', 1),
('Deporte', 1),
('Doctorados', 1),
('Educación Municipalizada', 1),
('Eje Epistemológico', 1),
('Eje Estético Lúdico', 1),
('Eje Etico Político', 1),
('Electiva 1', 1),
('Electiva II', 1),
('Electiva III', 1),
('Electiva IV', 1),
('EMTICL', 1),
('Formación Crítica TI, TII, TII', 1),
('Gestión de Proyectos', 1),
('Gestión TIC', 1),
('Idiomas', 1),
('Idiomas I', 1),
('Idiomas II', 1),
('Ingeniería del Software', 1),
('Ingeniería del Software II', 1),
('Ingenierías', 1),
('Investigación', 1),
('Investigación de Operaciones', 1),
('Laboratorios', 1),
('Matemática Aplicada', 1),
('Matemática I, II, III, IV', 1),
('Modelado de Base de Datos', 1),
('Nocturna y Fin de Semana', 1),
('PNFA-PNFI', 1),
('Preparaduría', 1),
('Programación', 1),
('Proyecto Sociotecnológico I', 1),
('Proyecto Sociotecnológico II', 1),
('Proyecto Sociotecnológico III', 1),
('Proyecto Sociotecnológico IV', 1),
('Redes Avanzadas', 1),
('Redes del Computador', 1),
('Seguridad', 1),
('Sistemas Operativos', 1),
('Tutorías Académicas', 1);

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
('Instructor', 'V', 3759671, 'Norma', 'Barreto', 'prueba@pnfi.edu.ve', 'Exclusiva', 'Ordinario', 1, '- Integra Comisión Organización docente - Integrante Comisión Currículo - Integra Com. Seguimiento Egresado', '1998-03-10', '2003-06-01', 'Oposicion'),
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
('Instructor', 'V', 15170003, 'Aida', 'Sivira', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2016-07-06', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 15351688, 'Francis', 'Rodriguez', 'francisyrm2601@hotmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 15693145, 'Indira', 'González', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Suplente', 1, '', '2019-10-25', NULL, NULL),
('Instructor', 'V', 16385182, 'Hermes', 'Gordillo', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Contratado por Credenciales', 1, '', '2013-03-02', '2014-06-01', 'Credenciales'),
('Instructor', 'V', 16403903, 'Orlando', 'Guerra', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2012-01-04', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 17354607, 'María', 'Linares', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, '', '2012-07-12', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 18103232, 'Carlos', 'Moreno', 'prueba@pnfi.edu.ve', 'Tiempo Completo', 'Ordinario', 1, 'Docente del PNF Deporte, atiende TC horas en PNFI', '2017-04-20', '2019-11-01', 'Oposicion'),
('Instructor', 'V', 18356682, 'Judith', 'Gomez', 'Rebecagomez1808@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 18912216, 'Cesar', 'Perez', 'cesarupf72@gmail.com', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
('Instructor', 'V', 20351422, 'Kenlimar', 'Alvarado', '', 'Tiempo Completo', 'Ordinario', 1, NULL, '2025-08-12', NULL, NULL),
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
-- Estructura de tabla para la tabla `tbl_docente_preferencia`
--

CREATE TABLE `tbl_docente_preferencia` (
  `doc_cedula` int(11) NOT NULL,
  `dia_semana` varchar(10) NOT NULL COMMENT 'Ej: lunes, martes, etc.',
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_docente_preferencia`
--

INSERT INTO `tbl_docente_preferencia` (`doc_cedula`, `dia_semana`, `hora_inicio`, `hora_fin`) VALUES
(3759671, 'lunes', '08:00:00', '17:00:00'),
(3759671, 'martes', '08:00:00', '17:00:00'),
(3759671, 'miercoles', '08:00:00', '17:00:00'),
(4374529, 'jueves', '08:00:00', '12:00:00'),
(4374529, 'martes', '08:00:00', '12:00:00'),
(5260810, 'jueves', '17:00:00', '21:00:00'),
(5260810, 'martes', '17:00:00', '21:00:00'),
(6269299, 'jueves', '13:00:00', '17:00:00'),
(6269299, 'martes', '13:00:00', '17:00:00'),
(7392496, 'miercoles', '08:00:00', '12:00:00'),
(7392496, 'viernes', '13:00:00', '17:00:00'),
(7404027, 'lunes', '08:00:00', '12:00:00'),
(7404027, 'miercoles', '08:00:00', '12:00:00'),
(7415067, 'jueves', '17:00:00', '21:00:00'),
(7415067, 'martes', '17:00:00', '21:00:00'),
(7423485, 'martes', '13:00:00', '17:00:00'),
(7423485, 'viernes', '08:00:00', '12:00:00'),
(7439117, 'jueves', '13:00:00', '17:00:00'),
(7439117, 'martes', '08:00:00', '12:00:00'),
(9118178, 'jueves', '13:00:00', '17:00:00'),
(9118178, 'martes', '08:00:00', '12:00:00'),
(9540060, 'jueves', '13:00:00', '17:00:00'),
(9540060, 'lunes', '08:00:00', '12:00:00'),
(9541953, 'lunes', '08:00:00', '12:00:00'),
(9541953, 'miercoles', '08:00:00', '12:00:00'),
(9602562, 'lunes', '08:00:00', '12:00:00'),
(9602562, 'miercoles', '13:00:00', '17:00:00'),
(9619518, 'lunes', '08:00:00', '17:00:00'),
(9619518, 'miercoles', '08:00:00', '17:00:00'),
(9619518, 'viernes', '08:00:00', '17:00:00'),
(9627295, 'miercoles', '08:00:00', '12:00:00'),
(9627295, 'viernes', '13:00:00', '17:00:00'),
(9629702, 'lunes', '08:00:00', '17:00:00'),
(9629702, 'miercoles', '08:00:00', '17:00:00'),
(10723015, 'lunes', '08:00:00', '12:00:00'),
(10723015, 'miercoles', '13:00:00', '17:00:00'),
(10775753, 'lunes', '08:00:00', '17:00:00'),
(10775753, 'miercoles', '08:00:00', '17:00:00'),
(10778236, 'lunes', '13:00:00', '17:00:00'),
(10778236, 'miercoles', '08:00:00', '12:00:00'),
(10846157, 'lunes', '13:00:00', '17:00:00'),
(10846157, 'viernes', '08:00:00', '12:00:00'),
(10847351, 'lunes', '13:00:00', '17:00:00'),
(10847351, 'viernes', '08:00:00', '12:00:00'),
(10956121, 'lunes', '17:00:00', '21:00:00'),
(10956121, 'miercoles', '17:00:00', '21:00:00'),
(11898335, 'lunes', '13:00:00', '17:00:00'),
(11898335, 'viernes', '08:00:00', '12:00:00'),
(12045627, 'jueves', '17:00:00', '21:00:00'),
(12045627, 'martes', '17:00:00', '21:00:00'),
(12849928, 'jueves', '13:00:00', '17:00:00'),
(12849928, 'martes', '08:00:00', '12:00:00'),
(13188691, 'lunes', '08:00:00', '12:00:00'),
(13188691, 'miercoles', '13:00:00', '17:00:00'),
(13527711, 'lunes', '13:00:00', '17:00:00'),
(13527711, 'viernes', '08:00:00', '12:00:00'),
(13991250, 'martes', '08:00:00', '12:00:00'),
(13991250, 'viernes', '13:00:00', '17:00:00'),
(13991971, 'jueves', '13:00:00', '17:00:00'),
(13991971, 'lunes', '08:00:00', '12:00:00'),
(14091124, 'martes', '13:00:00', '17:00:00'),
(14091124, 'viernes', '08:00:00', '12:00:00'),
(14159756, 'miercoles', '08:00:00', '12:00:00'),
(14159756, 'viernes', '13:00:00', '17:00:00'),
(14292469, 'jueves', '13:00:00', '17:00:00'),
(14292469, 'martes', '08:00:00', '12:00:00'),
(14293781, 'jueves', '08:00:00', '12:00:00'),
(14293781, 'martes', '08:00:00', '12:00:00'),
(14677589, 'lunes', '13:00:00', '17:00:00'),
(14677589, 'viernes', '08:00:00', '12:00:00'),
(15170003, 'martes', '13:00:00', '17:00:00'),
(15170003, 'viernes', '08:00:00', '12:00:00'),
(15693145, 'jueves', '13:00:00', '17:00:00'),
(15693145, 'martes', '08:00:00', '12:00:00'),
(16385182, 'miercoles', '08:00:00', '12:00:00'),
(16385182, 'viernes', '13:00:00', '17:00:00'),
(16403903, 'jueves', '17:00:00', '21:00:00'),
(16403903, 'martes', '17:00:00', '21:00:00'),
(17354607, 'jueves', '13:00:00', '17:00:00'),
(17354607, 'martes', '08:00:00', '12:00:00'),
(18103232, 'lunes', '13:00:00', '17:00:00'),
(18103232, 'viernes', '08:00:00', '12:00:00'),
(25471240, 'lunes', '08:00:00', '12:00:00'),
(25471240, 'miercoles', '13:00:00', '17:00:00');

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
('2', 'Aula', 1, 'Hilandera'),
('2', 'Aula', 1, 'Orinoco'),
('2', 'Aula', 1, 'Rio 7 Estrellas'),
('20', 'Aula', 1, 'Giraluna'),
('21', 'Aula', 1, 'Giraluna'),
('22', 'Aula', 1, 'Giraluna'),
('23', 'Aula', 1, 'Giraluna'),
('24', 'Aula', 1, 'Giraluna'),
('25', 'Aula', 1, 'Giraluna'),
('26', 'Aula', 1, 'Giraluna'),
('27', 'Aula', 1, 'Giraluna'),
('3', 'Aula', 1, 'Hilandera'),
('3', 'Aula', 1, 'Orinoco'),
('3', 'Aula', 1, 'Rio 7 Estrellas'),
('3', 'Laboratorio', 1, 'Giraluna'),
('4', 'Aula', 1, 'Hilandera'),
('4', 'Aula', 1, 'Orinoco'),
('4', 'Aula', 1, 'Rio 7 Estrellas'),
('4', 'Laboratorio', 1, 'Rio 7 Estrellas'),
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
(2025, 'regular', 1, '2025-02-17', '2025-06-20'),
(2025, 'regular', 2, '2025-06-23', '2025-11-27'),
(2025, 'regular', 1, '2025-02-17', '2025-06-20'),
(2025, 'regular', 2, '2025-06-23', '2025-11-27'),
(2025, 'regular', 1, '2025-02-17', '2025-06-20'),
(2025, 'regular', 2, '2025-06-23', '2025-11-27'),
(2025, 'regular', 1, '2025-02-17', '2025-06-20'),
(2025, 'regular', 2, '2025-06-23', '2025-11-27'),
(2025, 'regular', 1, '2025-02-17', '2025-06-20'),
(2025, 'regular', 2, '2025-06-23', '2025-11-27'),
(2025, 'regular', 1, '2025-02-17', '2025-06-20'),
(2025, 'regular', 2, '2025-06-23', '2025-11-27'),
(2025, 'regular', 1, '2025-02-17', '2025-06-20'),
(2025, 'regular', 2, '2025-06-23', '2025-11-27'),
(2025, 'regular', 1, '2025-02-17', '2025-06-20'),
(2025, 'regular', 2, '2025-06-23', '2025-11-27'),
(2025, 'regular', 1, '2025-02-17', '2025-06-20'),
(2025, 'regular', 2, '2025-06-23', '2025-11-27');

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
(1, '4403', 'mañana'),
(1, '2103', 'mañana'),
(1, '2113', 'mañana'),
(1, '2123', 'mañana'),
(1, '2133', 'mañana'),
(1, '2403', 'mañana'),
(1, '1143', 'mañana'),
(1, '1213', 'tarde'),
(1, '1133', 'mañana'),
(1, '1403', 'mañana'),
(1, '1203', 'tarde'),
(1, '1103', 'mañana'),
(1, '1113', 'mañana'),
(1, '1123', 'mañana'),
(1, '0103', 'mañana'),
(1, '0113', 'mañana'),
(1, '0123', 'mañana'),
(1, '0403', 'mañana'),
(1, '0423', 'mañana'),
(1, '3113', 'mañana'),
(1, '3103', 'mañana');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_horario_docente`
--

CREATE TABLE `tbl_horario_docente` (
  `doc_cedula` int(11) DEFAULT NULL,
  `hdo_lapso` varchar(30) DEFAULT '',
  `hdo_tipoactividad` varchar(30) DEFAULT '',
  `hdo_descripcion` varchar(50) DEFAULT '',
  `hdo_dependencia` varchar(30) DEFAULT '',
  `hdo_observacion` varchar(50) DEFAULT '',
  `hdo_horas` int(11) DEFAULT 0,
  `hdo_estado` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_malla`
--

CREATE TABLE `tbl_malla` (
  `mal_codigo` varchar(30) NOT NULL,
  `mal_nombre` varchar(30) NOT NULL DEFAULT '',
  `mal_descripcion` varchar(255) NOT NULL,
  `mal_cohorte` tinyint(1) NOT NULL DEFAULT 0,
  `mal_estado` tinyint(1) NOT NULL DEFAULT 1,
  `mal_activa` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_malla`
--

INSERT INTO `tbl_malla` (`mal_codigo`, `mal_nombre`, `mal_descripcion`, `mal_cohorte`, `mal_estado`, `mal_activa`) VALUES
('05033', 'PLAN DE ESTUDIO COHORTE III', 'Malla 2025', 3, 1, 1);

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
(2025, '2025-07-07', 'regular', 1),
(2025, '2025-07-07', 'regular', 1),
(2025, '2025-07-07', 'regular', 1),
(2025, '2025-07-07', 'regular', 1),
(2025, '2025-07-07', 'regular', 1),
(2025, '2025-07-07', 'regular', 1),
(2025, '2025-07-07', 'regular', 1),
(2025, '2025-07-07', 'regular', 1),
(2025, '2025-07-07', 'regular', 1),
(2025, '2025-07-07', 'regular', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_prosecusion`
--

CREATE TABLE `tbl_prosecusion` (
  `sec_origen` varchar(30) NOT NULL,
  `sec_promocion` varchar(30) NOT NULL,
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
  `ani_tipo` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_seccion`
--

INSERT INTO `tbl_seccion` (`sec_codigo`, `sec_cantidad`, `sec_estado`, `ani_anio`, `ani_tipo`) VALUES
('0103', 0, 1, 2025, 'regular'),
('0113', 0, 1, 2025, 'regular'),
('0123', 0, 1, 2025, 'regular'),
('0403', 0, 1, 2025, 'regular'),
('0423', 0, 1, 2025, 'regular'),
('1103', 0, 1, 2025, 'regular'),
('1113', 0, 1, 2025, 'regular'),
('1123', 0, 1, 2025, 'regular'),
('1133', 0, 1, 2025, 'regular'),
('1143', 0, 1, 2025, 'regular'),
('1203', 0, 1, 2025, 'regular'),
('1213', 0, 1, 2025, 'regular'),
('1403', 0, 1, 2025, 'regular'),
('2103', 0, 1, 2025, 'regular'),
('2113', 0, 1, 2025, 'regular'),
('2123', 0, 1, 2025, 'regular'),
('2133', 0, 1, 2025, 'regular'),
('2403', 0, 1, 2025, 'regular'),
('3103', 0, 1, 2025, 'regular'),
('3113', 0, 1, 2025, 'regular'),
('4403', 0, 1, 2025, 'regular');

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
(1, 'Dr.', 'Cs Educación'),
(1, 'Esp.', 'Experto Elearning'),
(1, 'Esp.', 'Organización Sit. Información'),
(1, 'Esp.', 'Sist. Información'),
(1, 'Esp.', 'Telematica Informática'),
(1, 'Ing.', 'Computación'),
(1, 'Ing.', 'Informática'),
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
(1, 'Msc.', 'Matemática Pura'),
(1, 'Prof.', 'Educación Física y deporte'),
(1, 'Prof.', 'Geografia e Historia'),
(1, 'Prof.', 'Inglés'),
(1, 'TSU.', 'Analista de Sistemas');

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
('Mañana', '08:00:00', '12:00:00', 1),
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
  `uc_electiva` tinyint(1) NOT NULL DEFAULT 0,
  `uc_estado` tinyint(1) NOT NULL,
  `area_nombre` varchar(30) NOT NULL,
  `uc_trayecto` varchar(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbl_uc`
--

INSERT INTO `tbl_uc` (`eje_nombre`, `uc_codigo`, `uc_nombre`, `uc_creditos`, `uc_periodo`, `uc_electiva`, `uc_estado`, `area_nombre`, `uc_trayecto`) VALUES
('Epistemológico', 'IDIO4', 'IDIOMAS VI', 23, 'Anual', 0, 1, 'Idiomas', '4'),
('Epistemológico', 'PIABD090403', 'Administración de bases de datos', 3, 'Fase I', 0, 1, 'Bases de Datos', '4'),
('Estético Lúdico', 'PIACA090103', 'Actividades Acreditable I', 3, 'Anual', 0, 1, 'Actividades', '1'),
('Estético Lúdico', 'PIACA090203', 'Actividades Acreditables II', 3, 'Anual', 0, 1, 'Actividades', '2'),
('Estético Lúdico', 'PIACA090303', 'Actividades Acreditables III', 3, 'Anual', 0, 1, 'Actividades', '3'),
('Estético Lúdico', 'PIACA090403', 'Actividades Acreditables IV', 3, 'Anual', 0, 1, 'Actividades', '4'),
('Epistemológico', 'PIALP306112', 'Algorítmica y Programación', 12, 'Anual', 0, 1, 'Programación', '1'),
('Epistemológico', 'PIARC234109', 'Arquitectura del Computador', 9, 'Anual', 0, 1, 'Arquitectura', '1'),
('Epistemológico', 'PIAUI120404', 'Auditoria de sistemas', 4, 'Fase II', 0, 1, 'Seguridad', '4'),
('Epistemológico', 'PIBAD090203', 'Base de Datos', 3, 'Fase I', 0, 1, 'Bases de Datos', '2'),
('Epistemológico', 'PIELE072103', 'Electiva I', 3, 'Fase II', 1, 1, 'Electivas', '1'),
('Epistemológico', 'PIELE072203', 'Electiva II', 3, 'Fase II', 1, 1, 'Electivas', '2'),
('Epistemológico', 'PIELE072403', 'Electiva IV', 3, 'Fase II', 1, 1, 'Electivas', '4'),
('Epistemológico', 'PIELE078303', 'Electiva III', 3, 'Fase II', 1, 1, 'Electivas', '3'),
('Ético-Político', 'PIFOC090103', 'Formación Crítica I', 3, 'Anual', 0, 1, 'Formación Crítica', '1'),
('Ético-Político', 'PIFOC090203', 'Formación Crítica II', 3, 'Anual', 0, 1, 'Formación Crítica', '2'),
('Ético Político-Socio Ambiental', 'PIFOC090303', 'Formación Crítica III', 3, 'Anual', 0, 1, 'Formación Crítica', '3'),
('Ético Político-Socio Ambiental', 'PIFOC090403', 'Formación Crítica IV', 3, 'Anual', 0, 1, 'Formación Crítica', '4'),
('Epistemológico', 'PIGPI120404', 'Gestión de proyecto Informático', 4, 'Fase I', 0, 1, 'Gestión', '4'),
('Epistemológico', 'PIIDI090103', 'Idiomas I', 3, 'Fase I', 0, 1, 'Idiomas', '1'),
('Epistemológico', 'PIIDI090403', 'Idiomas II', 3, 'Anual', 0, 1, 'Idiomas', '4'),
('Epistemológico', 'PIINO078303', 'Investigación de operaciones', 3, 'Fase II', 0, 1, 'Investigación', '3'),
('Epistemológico', 'PIINS090203', 'Ingeniería del Software I', 3, 'Fase I', 0, 1, 'Ingeniería Software', '2'),
('Epistemológico', 'PIINS252309', 'Ingeniería de Software II', 9, 'Anual', 0, 1, 'Ingeniería Software', '3'),
('Ético-Político', 'PIIUP052002', 'Introducción a la universidad y a los programas nacionales de formacion', 2, 'Anual', 0, 1, 'Introducción', '0'),
('Epistemológico', 'PIMAT090003', 'Matemática', 3, 'Anual', 0, 1, 'Matemáticas', '0'),
('Epistemológico', 'PIMAT156206', 'Matemática II', 6, 'Anual', 0, 1, 'Matemáticas', '2'),
('Epistemológico', 'PIMAT156306', 'Matemática Aplicada', 6, 'Anual', 0, 1, 'Matemáticas', '3'),
('Epistemológico', 'PIMAT234109', 'Matemática I', 9, 'Anual', 0, 1, 'Matemáticas', '1'),
('Epistemológico', 'PIMOB078303', 'Modelado de bases de datos', 3, 'Fase I', 0, 1, 'Bases de Datos', '3'),
('Ético-Político', 'PIPNN078003', 'Proyecto nacional y nueva ciudadanía', 3, 'Anual', 0, 1, 'Proyectos', '0'),
('Epistemológico', 'PIPRO306212', 'Programación II', 12, 'Anual', 0, 1, 'Programación', '2'),
('Trabajo-Productivo', 'PIPST234109', 'Proyecto Socio Tecnológico I', 9, 'Anual', 0, 1, 'Proyectos', '1'),
('Trabajo-Productivo', 'PIPST234209', 'Proyecto Socio Tecnológico II', 9, 'Anual', 0, 1, 'Proyectos', '2'),
('Trabajo-Productivo', 'PIPST234309', 'Proyecto Socio Tecnológico III', 9, 'Anual', 0, 1, 'Proyectos', '3'),
('Trabajo-Productivo', 'PIPST360412', 'Proyecto Socio Tecnológico IV', 12, 'Anual', 0, 1, 'Proyectos', '4'),
('Epistemológico', 'PIREA084403', 'Redes Avanzadas', 3, 'Fase II', 0, 1, 'Redes', '4'),
('Epistemológico', 'PIREC156206', 'Redes de Computadoras', 6, 'Anual', 0, 1, 'Redes', '2'),
('Epistemológico', 'PISEI120404', 'Seguridad Informática', 4, 'Fase I', 0, 1, 'Seguridad', '4'),
('Epistemológico', 'PISIO078303', 'Sistemas Operativos', 3, 'Fase I', 0, 1, 'Sistemas Operativos', '3'),
('Epistemológico', 'PITIC032002', 'Tecnologías de la información y comunicación', 2, 'Anual', 0, 1, 'Tecnologías', '0');

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
(15170003, 'Ing.', 'Computación'),
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
(7392496, 'Msc.', 'Cs Información');

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

INSERT INTO `uc_horario` (`uc_codigo`, `doc_cedula`, `subgrupo`, `sec_codigo`, `esp_numero`, `hor_dia`, `hor_horainicio`, `hor_horafin`, `esp_tipo`, `esp_edificio`) VALUES
('PIREA084403', 5260810, NULL, '4403', '5', 'Lunes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIAUI120404', 16403903, NULL, '4403', '5', 'Lunes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090403', 16385182, NULL, '4403', '15', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST360412', 16403903, NULL, '4403', '15', 'Martes', '09:20', '11:20', 'Aula', 'Hilandera'),
('PIFOC090403', 13527711, NULL, '4403', '15', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera'),
('IDIO4', 18356682, NULL, '4403', '15', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072403', 7391773, NULL, '4403', '15', 'Viernes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090203', 12701387, NULL, '2103', '10', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090203', 9629702, NULL, '2103', '10', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 10775753, NULL, '2103', '10', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIPRO306212', 10846157, NULL, '2103', '6', 'Jueves', '09:20', '12:00', 'Laboratorio', 'Hilandera'),
('PIPST234209', 7439117, NULL, '2113', '4', 'Lunes', '08:00', '10:00', 'Aula', 'Hilandera'),
('PIPRO306212', 10846157, NULL, '2113', '6', 'Lunes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090203', 9629702, NULL, '2113', '11', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIACA090203', 12701387, NULL, '2113', '11', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 10775753, NULL, '2113', '11', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREC156206', 11898335, NULL, '2113', '12', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072203', 5260810, NULL, '2113', '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIPRO306212', 10846157, NULL, '2123', '6', 'Lunes', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIPST234209', 7439117, NULL, '2123', '4', 'Lunes', '10:00', '12:00', 'Aula', 'Hilandera'),
('PIMAT156206', 10775753, NULL, '2123', '8', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090203', 9629702, NULL, '2123', '8', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIACA090203', 12701387, NULL, '2123', '8', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREC156206', 11898335, NULL, '2123', '13', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072203', 5260810, NULL, '2123', 'Software', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Hilandera'),
('PIPRO306212', 10846157, NULL, '2133', '6', 'Martes', '08:00', '09:20', 'Laboratorio', 'Hilandera'),
('PIPST234209', 7439117, NULL, '2133', '13', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIACA090203', 12701387, NULL, '2133', '13', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIELE072203', 5260810, NULL, '2133', '6', 'Viernes', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIREC156206', 11898335, NULL, '2133', '9', 'Sábado', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090203', 9629702, NULL, '2133', '9', 'Sábado', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156206', 10775753, NULL, '2133', '9', 'Sábado', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIREC156206', 11898335, NULL, '2403', '11', 'Martes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIACA090203', 12701387, NULL, '2403', '11', 'Martes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIELE072203', 5260810, NULL, '2403', '6', 'Viernes', '08:00', '09:20', 'Laboratorio', 'Hilandera'),
('PIPST234209', 7439117, NULL, '2403', '12', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIMAT156206', 10775753, NULL, '2403', '12', 'Viernes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIPRO306212', 10846157, NULL, '2403', '6', 'Sábado', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIFOC090203', 9629702, NULL, '2403', '21', 'Sábado', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIFOC090103', 9629702, NULL, '1143', '14', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPST234109', 7423485, NULL, '1143', '14', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIACA090103', 11898335, NULL, '1143', '14', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 7423486, NULL, '1143', '3', 'Jueves', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 9627295, NULL, '1143', '14', 'Jueves', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIARC234109', 7423486, NULL, '1143', 'Hardware', 'Miércoles', '08:00', '10:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 7391773, NULL, '1143', '3', 'Miércoles', '10:00', '12:00', 'Laboratorio', 'Giraluna'),
('PIALP306112', 7391773, NULL, '1213', '3', 'Lunes', '13:00', '15:00', 'Laboratorio', 'Giraluna'),
('PIMAT234109', 9627295, NULL, '1213', '12', 'Martes', '13:00', '15:00', 'Aula', 'Hilandera'),
('PIMAT234109', 9627295, NULL, '1133', '7', 'Martes', '08:00', '09:20', 'Aula', 'Rio 7 Estrellas'),
('PIACA090103', 11898335, NULL, '1133', '7', 'Martes', '09:20', '10:40', 'Aula', 'Rio 7 Estrellas'),
('PIFOC090103', 9629702, NULL, '1133', '10', 'Viernes', '08:00', '09:20', 'Aula', 'Rio 7 Estrellas'),
('PIELE072103', 7423486, NULL, '1133', '6', 'Viernes', '09:20', '10:40', 'Laboratorio', 'Hilandera'),
('PIPST234109', 7423485, NULL, '1133', '10', 'Viernes', '10:40', '12:00', 'Aula', 'Rio 7 Estrellas'),
('PIALP306112', 7391773, NULL, '1133', '5', 'Sábado', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 7423486, NULL, '1133', 'Hardware', 'Sábado', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIACA090103', 11898335, NULL, '1403', '9', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 7423486, NULL, '1403', '6', 'Viernes', '08:00', '09:20', 'Laboratorio', 'Hilandera'),
('PIFOC090103', 9629702, NULL, '1403', '11', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT234109', 9627295, NULL, '1403', '9', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIPST234109', 7423485, NULL, '1403', '11', 'Viernes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIALP306112', 7391773, NULL, '1403', '3', 'Sábado', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 7423486, NULL, '1403', 'Hardware', 'Sábado', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090103', 9629702, NULL, '1203', '12', 'Miércoles', '13:00', '14:20', 'Aula', 'Hilandera'),
('PIACA090103', 11898335, NULL, '1203', '12', 'Miércoles', '14:20', '15:40', 'Aula', 'Hilandera'),
('PIELE072103', 7423486, NULL, '1203', '12', 'Miércoles', '15:40', '17:00', 'Aula', 'Hilandera'),
('PIMAT234109', 9627295, NULL, '1203', '12', 'Jueves', '13:00', '15:00', 'Aula', 'Hilandera'),
('PIPST234109', 7423485, NULL, '1203', '12', 'Jueves', '15:00', '17:00', 'Aula', 'Hilandera'),
('PIALP306112', 7391773, NULL, '1203', '3', 'Viernes', '13:00', '15:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 7423486, NULL, '1203', 'Hardware', 'Viernes', '15:00', '17:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 7391773, NULL, '1103', '3', 'Martes', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 7423486, NULL, '1103', 'Hardware', 'Martes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIACA090103', 11898335, NULL, '1103', '12', 'Miércoles', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIFOC090103', 9629702, NULL, '1103', '12', 'Miércoles', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072103', 7423486, NULL, '1103', '3', 'Miércoles', '10:40', '12:00', 'Laboratorio', 'Giraluna'),
('PIPST234109', 7423485, NULL, '1103', '9', 'Viernes', '08:00', '10:00', 'Aula', 'Giraluna'),
('PIMAT234109', 9627295, NULL, '1103', '9', 'Viernes', '10:00', '12:00', 'Aula', 'Giraluna'),
('PIFOC090103', 9629702, NULL, '1113', '13', 'Miércoles', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIELE072103', 7423486, NULL, '1113', '3', 'Miércoles', '09:20', '10:40', 'Laboratorio', 'Giraluna'),
('PIACA090103', 11898335, NULL, '1113', '13', 'Miércoles', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIALP306112', 7391773, NULL, '1113', '5', 'Jueves', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 7423486, NULL, '1113', 'Hardware', 'Jueves', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIALP306112', 7391773, NULL, '1123', '5', 'Lunes', '08:00', '10:00', 'Laboratorio', 'Giraluna'),
('PIARC234109', 7423486, NULL, '1123', 'Hardware', 'Lunes', '10:00', '12:00', 'Laboratorio', 'Hilandera'),
('PIPST234109', 7423485, NULL, '1123', '7', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIMAT234109', 9627295, NULL, '1123', '7', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIELE072103', 7423486, NULL, '1123', '3', 'Miércoles', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIACA090103', 11898335, NULL, '1123', '14', 'Miércoles', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIFOC090103', 9629702, NULL, '1123', '14', 'Miércoles', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIPNN078003', 10848316, NULL, '0103', '13', 'Jueves', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIIUP052002', 9118178, NULL, '0103', '9', 'Viernes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIMAT090003', 9627295, NULL, '0103', '9', 'Viernes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIMAT090003', 9627295, NULL, '0113', '12', 'Jueves', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPNN078003', 10848316, NULL, '0113', '26', 'Jueves', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIIUP052002', 9118178, NULL, '0113', '26', 'Viernes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIMAT090003', 9627295, NULL, '0123', '26', 'Martes', '08:00', '10:00', 'Aula', 'Giraluna'),
('PIPNN078003', 10848316, NULL, '0123', '26', 'Martes', '10:00', '11:20', 'Aula', 'Giraluna'),
('PIIUP052002', 9118178, NULL, '0123', '26', 'Miércoles', '08:00', '10:00', 'Aula', 'Giraluna'),
('PIIUP052002', 9118178, NULL, '0403', '14', 'Miércoles', '08:00', '10:00', 'Aula', 'Hilandera'),
('PIMAT090003', 9627295, NULL, '0403', '8', 'Sábado', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIPNN078003', 10848316, NULL, '0403', '8', 'Sábado', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIIUP052002', 9118178, NULL, '0423', '15', 'Miércoles', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIMAT090003', 9627295, NULL, '0423', '21', 'Sábado', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIPNN078003', 10848316, NULL, '0423', '21', 'Sábado', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIELE078303', 25471240, NULL, '3113', '3', 'Lunes', '08:00', '09:20', 'Laboratorio', 'Giraluna'),
('PIACA090303', 12701387, NULL, '3113', '13', 'Lunes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIINO078303', 7392496, NULL, '3113', '10', 'Martes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIMAT156306', 18912216, NULL, '3113', '10', 'Martes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIPST234309', 7392496, NULL, '3113', '13', 'Viernes', '08:00', '09:20', 'Aula', 'Giraluna'),
('PIINS252309', 13991971, NULL, '3113', '13', 'Viernes', '09:20', '10:40', 'Aula', 'Giraluna'),
('PIFOC090303', 15170003, NULL, '3113', '13', 'Viernes', '10:40', '12:00', 'Aula', 'Giraluna'),
('PIACA090303', 12701387, NULL, '3103', '6', 'Martes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIELE078303', 25471240, NULL, '3103', '6', 'Sábado', '10:40', '12:00', 'Laboratorio', 'Hilandera'),
('PIFOC090303', 15170003, NULL, '3103', '12', 'Viernes', '10:40', '12:00', 'Aula', 'Hilandera'),
('PIINO078303', 7392496, NULL, '3103', '6', 'Martes', '08:00', '09:20', 'Aula', 'Hilandera'),
('PIINS252309', 13991971, NULL, '3103', '12', 'Viernes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIMAT156306', 18912216, NULL, '3103', '6', 'Martes', '09:20', '10:40', 'Aula', 'Hilandera'),
('PIPST234309', 7392496, NULL, '3103', '12', 'Viernes', '08:00', '09:20', 'Aula', 'Hilandera');

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
('05033', 'PISEI120404', 48, 72, 4),
('05033', 'IDIO4', 42, 23, 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `coordinacion_docente`
--
ALTER TABLE `coordinacion_docente`
  ADD KEY `fk_cor_doc` (`cor_nombre`),
  ADD KEY `fk_doc_cor` (`doc_cedula`);

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
  ADD PRIMARY KEY (`ani_anio`,`ani_tipo`,`uc_codigo`,`sec_codigo`,`fase_numero`),
  ADD KEY `fk_peraprobados_secc` (`sec_codigo`),
  ADD KEY `fk_peraprobados_uc` (`uc_codigo`),
  ADD KEY `fk_peraprobados_per` (`ani_anio`,`ani_tipo`);

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
  ADD PRIMARY KEY (`ani_anio`,`ani_tipo`,`uc_codigo`,`sec_codigo`,`fase_numero`),
  ADD KEY `fk_sec_aprob` (`sec_codigo`),
  ADD KEY `fk_uc_aprob` (`uc_codigo`),
  ADD KEY `fk_aprobados_docente_new` (`doc_cedula`);

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
-- Indices de la tabla `tbl_docente_preferencia`
--
ALTER TABLE `tbl_docente_preferencia`
  ADD PRIMARY KEY (`doc_cedula`,`dia_semana`);

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
-- Indices de la tabla `tbl_horario_docente`
--
ALTER TABLE `tbl_horario_docente`
  ADD KEY `fk_hodocente_doc` (`doc_cedula`);

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
  ADD PRIMARY KEY (`sec_origen`,`sec_promocion`),
  ADD KEY `fk_prosecucion_seccion_origen` (`sec_origen`),
  ADD KEY `fk_prosecucion_seccion_promocion` (`sec_promocion`);

--
-- Indices de la tabla `tbl_seccion`
--
ALTER TABLE `tbl_seccion`
  ADD PRIMARY KEY (`sec_codigo`),
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
  ADD KEY `fk_titdoc_docente` (`doc_cedula`),
  ADD KEY `fk_titulo_docente_a_tbl_titulo` (`tit_prefijo`,`tit_nombre`);

--
-- Indices de la tabla `uc_docente`
--
ALTER TABLE `uc_docente`
  ADD KEY `fk_ucdoc_docente` (`doc_cedula`),
  ADD KEY `fk_ucdoc_uc` (`uc_codigo`);

--
-- Indices de la tabla `uc_horario`
--
ALTER TABLE `uc_horario`
  ADD KEY `fk_uchorario_horario` (`sec_codigo`),
  ADD KEY `fk_uchorario_uc` (`uc_codigo`),
  ADD KEY `esp_numero` (`esp_numero`,`esp_tipo`,`esp_edificio`) USING BTREE;

--
-- Indices de la tabla `uc_malla`
--
ALTER TABLE `uc_malla`
  ADD KEY `fk_ucmalla_malla` (`mal_codigo`),
  ADD KEY `fk_ucmalla_uc` (`uc_codigo`);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `docente_horario`
--
ALTER TABLE `docente_horario`
  ADD CONSTRAINT `fk_doc_horario` FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_horario_doc` FOREIGN KEY (`sec_codigo`) REFERENCES `tbl_horario` (`sec_codigo`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
