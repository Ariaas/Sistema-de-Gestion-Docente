-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-10-2025 a las 06:05:31
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
(31, 13, 'registrar'),
(31, 13, 'modificar'),
(31, 13, 'eliminar'),
(31, 17, 'registrar'),
(31, 17, 'modificar'),
(31, 17, 'eliminar'),
(31, 11, 'registrar'),
(31, 11, 'modificar'),
(31, 11, 'eliminar'),
(31, 14, 'registrar'),
(31, 14, 'modificar'),
(31, 14, 'eliminar'),
(31, 12, 'registrar'),
(31, 12, 'modificar'),
(31, 12, 'eliminar');

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

--
-- Volcado de datos para la tabla `tbl_bitacora`
--

INSERT INTO `tbl_bitacora` (`usu_id`, `bit_modulo`, `bit_accion`, `bit_fecha`, `bit_estado`) VALUES
(1, 'anio', 'eliminar', '2025-09-25 07:42:33', 1),
(1, 'anio', 'eliminar', '2025-10-01 02:51:19', 1),
(1, 'anio', 'eliminar', '2025-10-12 03:26:48', 1),
(1, 'anio', 'eliminar', '2025-10-21 04:17:57', 1),
(1, 'anio', 'eliminar', '2025-10-25 01:24:35', 1),
(1, 'anio', 'eliminar', '2025-10-25 01:24:42', 1),
(1, 'anio', 'eliminar', '2025-10-25 01:40:20', 1),
(1, 'anio', 'eliminar', '2025-10-25 01:40:44', 1),
(1, 'anio', 'modificar', '2025-09-25 04:56:37', 1),
(1, 'anio', 'modificar', '2025-09-30 02:37:15', 1),
(1, 'anio', 'modificar', '2025-09-30 03:26:31', 1),
(1, 'anio', 'modificar', '2025-09-30 03:29:27', 1),
(1, 'anio', 'modificar', '2025-10-12 03:30:18', 1),
(1, 'anio', 'modificar', '2025-10-21 04:38:23', 1),
(1, 'anio', 'modificar', '2025-10-21 04:38:39', 1),
(1, 'anio', 'modificar', '2025-10-25 01:40:39', 1),
(1, 'anio', 'modificar', '2026-02-25 16:04:15', 1),
(1, 'anio', 'modificar', '2026-10-21 04:13:22', 1),
(1, 'anio', 'registrar', '2025-09-25 04:55:32', 1),
(1, 'anio', 'registrar', '2025-10-01 02:47:57', 1),
(1, 'anio', 'registrar', '2025-10-01 02:51:00', 1),
(1, 'anio', 'registrar', '2025-10-16 16:02:28', 1),
(1, 'anio', 'registrar', '2025-10-23 15:07:21', 1),
(1, 'anio', 'registrar', '2025-10-23 15:07:30', 1),
(1, 'anio', 'registrar', '2025-10-24 15:23:39', 1),
(1, 'anio', 'registrar', '2025-10-24 15:24:59', 1),
(1, 'anio', 'registrar', '2025-10-25 01:39:43', 1),
(1, 'anio', 'registrar', '2025-10-25 01:40:02', 1),
(1, 'anio', 'registrar', '2026-10-21 04:11:40', 1),
(1, 'Archivo', 'eliminar acta', '2025-10-24 23:52:42', 1),
(1, 'Archivo', 'eliminar acta', '2025-10-25 00:52:05', 1),
(1, 'Archivo', 'eliminar acta', '2025-10-25 01:44:14', 1),
(1, 'Archivo', 'eliminar acta', '2025-10-25 01:44:21', 1),
(1, 'Archivo', 'eliminar acta', '2025-10-25 01:44:27', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-16 01:52:21', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-16 02:31:53', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-16 02:33:49', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-16 02:34:28', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-16 02:36:32', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-16 02:37:09', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-16 02:43:17', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-16 02:47:46', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-16 02:48:16', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-16 06:01:16', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-16 06:01:44', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-16 15:37:11', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-24 23:37:30', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-24 23:53:37', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-24 23:54:41', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-24 23:55:09', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-25 00:51:44', 1),
(1, 'Archivo', 'Registrar acta', '2025-10-25 01:44:06', 1),
(1, 'Archivo', 'Registrar notas', '2025-09-30 02:06:49', 1),
(1, 'area', 'eliminar', '2025-09-25 04:51:37', 1),
(1, 'area', 'eliminar', '2025-10-24 15:28:12', 1),
(1, 'area', 'eliminar', '2025-10-25 01:41:59', 1),
(1, 'area', 'modificar', '2025-09-25 04:51:23', 1),
(1, 'area', 'modificar', '2025-10-02 04:21:12', 1),
(1, 'area', 'modificar', '2025-10-24 15:29:42', 1),
(1, 'area', 'modificar', '2025-10-25 01:41:51', 1),
(1, 'area', 'registrar', '2025-09-25 04:50:59', 1),
(1, 'area', 'registrar', '2025-10-24 15:27:53', 1),
(1, 'area', 'registrar', '2025-10-25 01:41:38', 1),
(1, 'categoria', 'eliminar', '2025-09-25 04:53:34', 1),
(1, 'categoria', 'eliminar', '2025-10-24 15:29:30', 1),
(1, 'categoria', 'eliminar', '2025-10-25 01:04:29', 1),
(1, 'categoria', 'eliminar', '2025-10-25 01:04:54', 1),
(1, 'categoria', 'eliminar', '2025-10-25 01:42:31', 1),
(1, 'categoria', 'modificar', '2025-09-25 04:53:26', 1),
(1, 'categoria', 'modificar', '2025-10-24 15:29:24', 1),
(1, 'categoria', 'modificar', '2025-10-25 01:04:45', 1),
(1, 'categoria', 'modificar', '2025-10-25 01:42:24', 1),
(1, 'categoria', 'registrar', '2025-09-25 04:53:05', 1),
(1, 'categoria', 'registrar', '2025-10-24 15:29:12', 1),
(1, 'categoria', 'registrar', '2025-10-25 01:04:14', 1),
(1, 'categoria', 'registrar', '2025-10-25 01:04:38', 1),
(1, 'categoria', 'registrar', '2025-10-25 01:42:15', 1),
(1, 'Coordinacion', 'eliminar', '2025-10-02 04:17:45', 1),
(1, 'Coordinacion', 'eliminar', '2025-10-02 04:18:18', 1),
(1, 'Coordinacion', 'eliminar', '2025-10-24 15:26:29', 1),
(1, 'Coordinacion', 'eliminar', '2025-10-25 00:29:28', 1),
(1, 'Coordinacion', 'eliminar', '2025-10-25 00:29:54', 1),
(1, 'Coordinacion', 'eliminar', '2025-10-25 01:01:12', 1),
(1, 'Coordinacion', 'eliminar', '2025-10-25 01:01:28', 1),
(1, 'Coordinacion', 'eliminar', '2025-10-25 01:33:31', 1),
(1, 'Coordinacion', 'eliminar', '2025-10-25 01:41:20', 1),
(1, 'Coordinacion', 'modificar', '2025-10-25 01:00:40', 1),
(1, 'Coordinacion', 'modificar', '2025-10-25 01:00:47', 1),
(1, 'Coordinacion', 'modificar', '2025-10-25 01:01:03', 1),
(1, 'Coordinacion', 'modificar', '2025-10-25 01:41:13', 1),
(1, 'Coordinacion', 'registrar', '2025-10-02 02:33:28', 1),
(1, 'Coordinacion', 'registrar', '2025-10-02 04:16:54', 1),
(1, 'Coordinacion', 'registrar', '2025-10-02 04:17:02', 1),
(1, 'Coordinacion', 'registrar', '2025-10-02 04:17:09', 1),
(1, 'Coordinacion', 'registrar', '2025-10-02 04:17:22', 1),
(1, 'Coordinacion', 'registrar', '2025-10-02 04:17:26', 1),
(1, 'Coordinacion', 'registrar', '2025-10-02 04:17:52', 1),
(1, 'Coordinacion', 'registrar', '2025-10-24 15:26:11', 1),
(1, 'Coordinacion', 'registrar', '2025-10-25 00:29:13', 1),
(1, 'Coordinacion', 'registrar', '2025-10-25 00:29:40', 1),
(1, 'Coordinacion', 'registrar', '2025-10-25 01:00:14', 1),
(1, 'Coordinacion', 'registrar', '2025-10-25 01:00:22', 1),
(1, 'Coordinacion', 'registrar', '2025-10-25 01:41:00', 1),
(1, 'docente', 'activar', '2025-10-24 20:16:17', 1),
(1, 'docente', 'activar', '2025-10-24 20:16:21', 1),
(1, 'docente', 'activar', '2025-10-24 20:19:24', 1),
(1, 'docente', 'activar', '2025-10-24 20:19:34', 1),
(1, 'docente', 'activar', '2025-10-24 20:22:20', 1),
(1, 'docente', 'activar', '2025-10-24 20:22:27', 1),
(1, 'docente', 'activar', '2025-10-24 20:23:41', 1),
(1, 'docente', 'activar', '2025-10-24 20:23:52', 1),
(1, 'docente', 'activar', '2025-10-24 20:25:28', 1),
(1, 'docente', 'activar', '2025-10-24 23:13:58', 1),
(1, 'docente', 'eliminar', '2025-10-24 14:57:29', 1),
(1, 'docente', 'eliminar', '2025-10-24 14:59:59', 1),
(1, 'docente', 'eliminar', '2025-10-24 19:47:51', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:05:23', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:05:55', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:06:05', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:07:24', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:07:34', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:07:46', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:07:54', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:12:43', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:13:01', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:13:05', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:13:16', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:14:17', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:19:30', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:22:16', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:22:24', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:23:39', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:23:50', 1),
(1, 'docente', 'eliminar', '2025-10-24 20:25:25', 1),
(1, 'docente', 'eliminar', '2025-10-24 23:14:18', 1),
(1, 'docente', 'modificar', '2025-10-24 18:32:36', 1),
(1, 'docente', 'modificar', '2025-10-24 19:39:52', 1),
(1, 'docente', 'modificar', '2025-10-25 00:34:50', 1),
(1, 'docente', 'modificar', '2025-10-25 00:35:37', 1),
(1, 'docente', 'modificar', '2025-10-25 02:14:00', 1),
(1, 'docente', 'modificar', '2025-10-25 02:19:10', 1),
(1, 'docente', 'registrar', '2025-10-24 14:52:09', 1),
(1, 'docente', 'registrar', '2025-10-24 14:52:30', 1),
(1, 'docente', 'registrar', '2025-10-24 14:57:01', 1),
(1, 'docente', 'registrar', '2025-10-24 14:59:53', 1),
(1, 'docente', 'registrar', '2025-10-24 19:47:45', 1),
(1, 'docente', 'registrar', '2025-10-24 19:59:37', 1),
(1, 'docente', 'registrar', '2025-10-24 20:02:13', 1),
(1, 'docente', 'registrar', '2025-10-24 20:02:17', 1),
(1, 'docente', 'registrar', '2025-10-24 20:02:45', 1),
(1, 'docente', 'registrar', '2025-10-24 20:05:13', 1),
(1, 'docente', 'registrar', '2025-10-24 20:05:45', 1),
(1, 'docente', 'registrar', '2025-10-24 23:19:33', 1),
(1, 'docente', 'registrar', '2025-10-25 00:48:49', 1),
(1, 'docente', 'registrar', '2025-10-25 02:12:47', 1),
(1, 'eje', 'eliminar', '2025-09-25 04:48:21', 1),
(1, 'eje', 'eliminar', '2025-10-24 15:31:14', 1),
(1, 'eje', 'eliminar', '2025-10-25 00:55:15', 1),
(1, 'eje', 'eliminar', '2025-10-25 00:56:27', 1),
(1, 'eje', 'eliminar', '2025-10-25 01:43:08', 1),
(1, 'eje', 'modificar', '2025-09-25 04:48:02', 1),
(1, 'eje', 'modificar', '2025-09-25 04:48:14', 1),
(1, 'eje', 'modificar', '2025-10-25 00:55:43', 1),
(1, 'eje', 'modificar', '2025-10-25 00:56:01', 1),
(1, 'eje', 'modificar', '2025-10-25 00:56:19', 1),
(1, 'eje', 'modificar', '2025-10-25 01:42:59', 1),
(1, 'eje', 'registrar', '2025-09-25 04:47:47', 1),
(1, 'eje', 'registrar', '2025-10-24 15:31:03', 1),
(1, 'eje', 'registrar', '2025-10-25 00:55:05', 1),
(1, 'eje', 'registrar', '2025-10-25 00:55:29', 1),
(1, 'eje', 'registrar', '2025-10-25 01:42:49', 1),
(1, 'espacios', 'eliminar', '2025-10-25 01:20:06', 1),
(1, 'espacios', 'eliminar', '2025-10-25 01:20:21', 1),
(1, 'espacios', 'eliminar', '2025-10-25 01:20:50', 1),
(1, 'espacios', 'modificar', '2025-10-24 15:00:29', 1),
(1, 'espacios', 'modificar', '2025-10-24 15:00:36', 1),
(1, 'espacios', 'modificar', '2025-10-24 17:46:15', 1),
(1, 'espacios', 'modificar', '2025-10-24 17:46:32', 1),
(1, 'espacios', 'modificar', '2025-10-24 17:52:05', 1),
(1, 'espacios', 'modificar', '2025-10-24 18:24:26', 1),
(1, 'espacios', 'modificar', '2025-10-24 18:28:48', 1),
(1, 'espacios', 'modificar', '2025-10-24 18:32:23', 1),
(1, 'espacios', 'registrar', '2025-10-24 15:00:18', 1),
(1, 'espacios', 'registrar', '2025-10-24 18:38:34', 1),
(1, 'espacios', 'registrar', '2025-10-24 18:41:34', 1),
(1, 'espacios', 'registrar', '2025-10-24 18:42:03', 1),
(1, 'espacios', 'registrar', '2025-10-25 01:19:32', 1),
(1, 'espacios', 'registrar', '2025-10-25 01:20:33', 1),
(1, 'malla curricular', 'activar', '2025-10-24 15:22:15', 1),
(1, 'malla curricular', 'activar', '2025-10-24 15:22:28', 1),
(1, 'malla curricular', 'desactivar', '2025-10-24 15:22:13', 1),
(1, 'malla curricular', 'desactivar', '2025-10-24 15:22:25', 1),
(1, 'malla curricular', 'modificar', '2025-10-24 19:09:08', 1),
(1, 'malla curricular', 'registrar', '2025-10-25 01:14:02', 1),
(1, 'malla curricular', 'registrar', '2025-10-25 01:15:51', 1),
(1, 'Respaldo', 'Guardar Respaldo', '2025-10-25 01:44:52', 1),
(1, 'Respaldo', 'Restaurar Sistema', '2025-10-24 14:58:47', 1),
(1, 'rol', 'eliminar', '2025-10-24 15:45:26', 1),
(1, 'rol', 'eliminar', '2025-10-25 02:43:42', 1),
(1, 'rol', 'modificar', '2025-10-25 02:43:32', 1),
(1, 'rol', 'registrar', '2025-10-24 15:45:01', 1),
(1, 'rol', 'registrar', '2025-10-25 02:42:39', 1),
(1, 'titulo', 'eliminar', '2025-09-25 04:50:04', 1),
(1, 'titulo', 'eliminar', '2025-10-24 15:31:49', 1),
(1, 'titulo', 'eliminar', '2025-10-24 15:31:59', 1),
(1, 'titulo', 'eliminar', '2025-10-25 01:34:41', 1),
(1, 'titulo', 'eliminar', '2025-10-25 01:43:39', 1),
(1, 'titulo', 'modificar', '2025-09-25 04:49:23', 1),
(1, 'titulo', 'modificar', '2025-09-25 04:49:57', 1),
(1, 'titulo', 'modificar', '2025-10-02 03:45:23', 1),
(1, 'titulo', 'modificar', '2025-10-02 04:05:26', 1),
(1, 'titulo', 'modificar', '2025-10-02 04:05:42', 1),
(1, 'titulo', 'modificar', '2025-10-25 01:43:30', 1),
(1, 'titulo', 'registrar', '2025-09-25 04:48:52', 1),
(1, 'titulo', 'registrar', '2025-10-24 15:31:31', 1),
(1, 'titulo', 'registrar', '2025-10-25 01:43:22', 1),
(1, 'Unidad Curricular', 'activar', '2025-10-24 15:22:04', 1),
(1, 'Unidad Curricular', 'eliminar', '2025-10-24 15:21:59', 1),
(1, 'Unidad Curricular', 'modificar', '2025-10-24 15:21:46', 1),
(1, 'Unidad Curricular', 'modificar', '2025-10-24 15:21:53', 1),
(1, 'Unidad Curricular', 'modificar', '2025-10-24 18:29:01', 1),
(1, 'Unidad Curricular', 'modificar', '2025-10-24 18:29:43', 1),
(1, 'Unidad Curricular', 'modificar', '2025-10-24 18:59:26', 1),
(1, 'Unidad Curricular', 'modificar', '2025-10-24 18:59:45', 1),
(1, 'Unidad Curricular', 'modificar', '2025-10-24 19:00:23', 1),
(1, 'Unidad Curricular', 'modificar', '2025-10-24 19:00:30', 1),
(1, 'Unidad Curricular', 'modificar', '2025-10-24 19:00:34', 1),
(1, 'Unidad Curricular', 'modificar', '2025-10-24 19:02:58', 1),
(1, 'Unidad Curricular', 'modificar', '2025-10-24 19:03:10', 1),
(1, 'Unidad Curricular', 'modificar', '2025-10-24 19:04:07', 1),
(1, 'Unidad Curricular', 'modificar', '2025-10-24 19:15:20', 1),
(1, 'Unidad Curricular', 'registrar', '2025-10-24 23:39:31', 1),
(1, 'Unidad Curricular', 'registrar', '2025-10-24 23:47:18', 1),
(1, 'usuario', 'eliminar', '2025-10-25 01:55:35', 1),
(1, 'usuario', 'eliminar', '2025-10-25 02:45:08', 1),
(1, 'usuario', 'modificar', '2025-09-27 18:14:20', 1),
(1, 'usuario', 'modificar', '2025-09-27 19:59:12', 1),
(1, 'usuario', 'modificar', '2025-09-30 01:55:15', 1),
(1, 'usuario', 'modificar', '2025-10-25 04:03:15', 1),
(1, 'usuario', 'registrar', '2025-10-25 01:55:09', 1),
(1, 'usuario', 'registrar', '2025-10-25 02:44:34', 1);

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

--
-- Volcado de datos para la tabla `tbl_notificacion`
--

INSERT INTO `tbl_notificacion` (`not_id`, `not_notificacion`, `not_fecha`, `not_fin`, `not_estado`, `not_activo`) VALUES
(1, 'Hoy es el cierre de la fase 2 del año 2025 (Regular).', '2026-02-25 16:04:37', '2025-10-19', 0, 0),
(2, 'En 2 semanas abrirá PER fase 1 del año 2025.', '2026-10-16 16:17:18', '2025-10-20', 0, 0),
(3, 'Hoy es el cierre de la fase 1 del año 2025 (Regular).', '2026-10-16 16:17:18', '2025-10-20', 0, 0),
(4, 'La fase 2 del año 2025 (Regular) está a punto de cerrarse: faltan 10 días.', '2026-10-16 16:17:18', '2025-10-20', 0, 0),
(5, 'La fase 1 del año 2025 (Intensivo) está a punto de cerrarse: faltan 4 días.', '2026-10-16 16:17:18', '2025-10-20', 0, 0),
(6, 'La fase 2 del año 2025 (Regular) está a punto de cerrarse: faltan 19 días.', '2026-10-16 16:17:18', '2025-10-31', 0, 0),
(7, 'La fase 2 del año 2025 (Regular) está a punto de cerrarse: faltan 14 días.', '2026-10-16 16:17:18', '2025-11-05', 0, 1),
(8, 'La fase 1 del año 2026 (Intensivo) está a punto de cerrarse: faltan 14 días.', '2027-10-16 16:17:52', '2026-11-05', 0, 1),
(9, 'La fase 2 del año 2025 (Regular) está a punto de cerrarse: faltan 14 días.', '2026-10-21 04:10:19', '2025-11-05', 0, 0),
(10, 'El año 2025 (Intensivo) está a punto de cerrar: faltan 7 días.', '2025-10-24 15:54:17', '2025-11-13', 1, 0);

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
(1, 'Administrador', 1),
(31, 'Docentes', 0);

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
(1, 'LigiaDuran', 'prueba@pnfi.edu.ve', '$2y$10$fYpv49aUoBWTPuZxKK//7uBrGs4K1ZrkYnaVuOaxCW80HRO5LDNke', 'public/assets/profile/ligiaDuran.png?v=1754037180097', 13991250, 'Ligia Durán', 1, '79940294', '2025-09-27 14:54:26', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD KEY `rol_permiso` (`rol_id`),
  ADD KEY `permiso_rol` (`per_id`);

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
  MODIFY `not_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tbl_permisos`
--
ALTER TABLE `tbl_permisos`
  MODIFY `per_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `tbl_rol`
--
ALTER TABLE `tbl_rol`
  MODIFY `rol_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `tbl_usuario`
--
ALTER TABLE `tbl_usuario`
  MODIFY `usu_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD CONSTRAINT `permiso_rol` FOREIGN KEY (`per_id`) REFERENCES `tbl_permisos` (`per_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rol_permiso` FOREIGN KEY (`rol_id`) REFERENCES `tbl_rol` (`rol_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_bitacora`
--
ALTER TABLE `tbl_bitacora`
  ADD CONSTRAINT `usu_bitacora` FOREIGN KEY (`usu_id`) REFERENCES `tbl_usuario` (`usu_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_usuario`
--
ALTER TABLE `tbl_usuario`
  ADD CONSTRAINT `usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `tbl_rol` (`rol_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
