SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

SET NAMES utf8mb4;

CREATE TABLE `tbl_categoria` (
  `cat_nombre` varchar(30) NOT NULL,
  `cat_estado` tinyint(1) NOT NULL DEFAULT 1,
  `cat_descripcion` varchar(255) NOT NULL,
  PRIMARY KEY (`cat_nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_area` (
  `area_nombre` varchar(30) NOT NULL,
  `area_estado` tinyint(1) NOT NULL DEFAULT 1,
  `area_descripcion` varchar(255) NOT NULL,
  PRIMARY KEY (`area_nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_eje` (
  `eje_nombre` varchar(30) NOT NULL,
  `eje_descripcion` varchar(255) NOT NULL,
  `eje_estado` tinyint(1) NOT NULL,
  PRIMARY KEY (`eje_nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_coordinacion` (
  `cor_nombre` varchar(30) NOT NULL,
  `cor_estado` tinyint(1) NOT NULL DEFAULT 1,
  `coor_hora_descarga` int(3) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cor_nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_turno` (
  `tur_nombre` varchar(30) NOT NULL,
  `tur_horaInicio` time NOT NULL,
  `tur_horaFin` time NOT NULL,
  `tur_estado` tinyint(1) NOT NULL,
  PRIMARY KEY (`tur_nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_malla` (
  `mal_codigo` varchar(30) NOT NULL,
  `mal_nombre` varchar(30) NOT NULL DEFAULT '',
  `mal_descripcion` varchar(255) NOT NULL,
  `mal_cohorte` tinyint(1) NOT NULL DEFAULT 0,
  `mal_activa` tinyint(1) NOT NULL,
  PRIMARY KEY (`mal_codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_titulo` (
  `tit_prefijo` varchar(30) NOT NULL,
  `tit_nombre` varchar(80) NOT NULL,
  `tit_estado` tinyint(1) NOT NULL,
  PRIMARY KEY (`tit_prefijo`, `tit_nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_anio` (
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `ani_activo` tinyint(1) NOT NULL DEFAULT 0,
  `ani_estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ani_anio`, `ani_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `doc_tipo_concurso` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`doc_cedula`),
  FOREIGN KEY (`cat_nombre`) REFERENCES `tbl_categoria` (`cat_nombre`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_espacio` (
  `esp_numero` varchar(30) NOT NULL,
  `esp_tipo` varchar(30) NOT NULL,
  `esp_edificio` varchar(30) NOT NULL,
  `esp_estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`esp_numero`, `esp_tipo`, `esp_edificio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_uc` (
  `uc_codigo` varchar(30) NOT NULL,
  `eje_nombre` varchar(30) NOT NULL,
  `area_nombre` varchar(30) NOT NULL,
  `uc_nombre` varchar(100) NOT NULL,
  `uc_creditos` int(11) NOT NULL DEFAULT 0,
  `uc_periodo` varchar(10) NOT NULL,
  `uc_estado` tinyint(1) NOT NULL,
  `uc_trayecto` varchar(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uc_codigo`),
  FOREIGN KEY (`eje_nombre`) REFERENCES `tbl_eje` (`eje_nombre`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`area_nombre`) REFERENCES `tbl_area` (`area_nombre`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_seccion` (
  `sec_codigo` varchar(30) NOT NULL,
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `sec_cantidad` int(11) NOT NULL DEFAULT 0,
  `sec_estado` tinyint(1) NOT NULL,
  `grupo_union_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`sec_codigo`, `ani_anio`, `ani_tipo`),
  FOREIGN KEY (`ani_anio`, `ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_fase` (
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `fase_numero` tinyint(1) NOT NULL,
  `fase_apertura` date NOT NULL,
  `fase_cierre` date NOT NULL,
  FOREIGN KEY (`ani_anio`, `ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_per` (
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `per_apertura` date NOT NULL,
  `per_fase` tinyint(1) NOT NULL,
  FOREIGN KEY (`ani_anio`, `ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_horario` (
  `sec_codigo` varchar(30) NOT NULL,
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `tur_nombre` varchar(30) NOT NULL,
  `hor_estado` tinyint(1) DEFAULT NULL,
  FOREIGN KEY (`sec_codigo`, `ani_anio`, `ani_tipo`) REFERENCES `tbl_seccion` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`tur_nombre`) REFERENCES `tbl_turno` (`tur_nombre`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_prosecusion` (
  `sec_origen` varchar(30) NOT NULL,
  `ani_origen` int(11) NOT NULL,
  `ani_tipo_origen` varchar(10) NOT NULL,
  `sec_promocion` varchar(30) NOT NULL,
  `ani_destino` int(11) NOT NULL,
  `ani_tipo_destino` varchar(10) NOT NULL,
  `pro_cantidad` int(11) NOT NULL DEFAULT 0,
  `pro_estado` tinyint(1) NOT NULL,
  PRIMARY KEY (`sec_origen`, `ani_origen`, `ani_tipo_origen`, `sec_promocion`, `ani_destino`, `ani_tipo_destino`),
  FOREIGN KEY (`sec_origen`, `ani_origen`, `ani_tipo_origen`) REFERENCES `tbl_seccion` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`sec_promocion`, `ani_destino`, `ani_tipo_destino`) REFERENCES `tbl_seccion` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_actividad` (
  `doc_cedula` int(11) NOT NULL,
  `act_academicas` int(11) NOT NULL DEFAULT 0,
  `act_creacion_intelectual` int(11) NOT NULL,
  `act_integracion_comunidad` int(11) NOT NULL,
  `act_gestion_academica` int(11) NOT NULL,
  `act_otras` int(11) NOT NULL,
  `act_estado` tinyint(1) NOT NULL,
  FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `titulo_docente` (
  `doc_cedula` int(11) NOT NULL,
  `tit_prefijo` varchar(30) NOT NULL,
  `tit_nombre` varchar(80) NOT NULL,
  FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`tit_prefijo`, `tit_nombre`) REFERENCES `tbl_titulo` (`tit_prefijo`, `tit_nombre`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `uc_docente` (
  `uc_codigo` varchar(30) NOT NULL,
  `doc_cedula` int(11) NOT NULL,
  FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `esp_edificio` varchar(30) DEFAULT NULL,
  FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`esp_numero`, `esp_tipo`, `esp_edificio`) REFERENCES `tbl_espacio` (`esp_numero`, `esp_tipo`, `esp_edificio`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`sec_codigo`, `ani_anio`, `ani_tipo`) REFERENCES `tbl_seccion` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `uc_malla` (
  `mal_codigo` varchar(30) NOT NULL,
  `uc_codigo` varchar(30) NOT NULL,
  `mal_hora_independiente` int(11) NOT NULL DEFAULT 0,
  `mal_hora_asistida` int(11) NOT NULL DEFAULT 0,
  `mal_hora_academica` int(11) NOT NULL DEFAULT 0,
  FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`mal_codigo`) REFERENCES `tbl_malla` (`mal_codigo`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_aprobados` (
  `apro_estado` tinyint(1) NOT NULL,
  `apro_cantidad` int(11) NOT NULL DEFAULT 0,
  `uc_codigo` varchar(30) NOT NULL,
  `sec_codigo` varchar(30) NOT NULL,
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `fase_numero` tinyint(1) NOT NULL,
  `doc_cedula` int(11) DEFAULT NULL,
  FOREIGN KEY (`ani_anio`, `ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`sec_codigo`, `ani_anio`, `ani_tipo`) REFERENCES `tbl_seccion` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `per_aprobados` (
  `ani_anio` int(11) NOT NULL,
  `ani_tipo` varchar(10) NOT NULL,
  `fase_numero` tinyint(1) NOT NULL,
  `uc_codigo` varchar(30) NOT NULL,
  `sec_codigo` varchar(30) NOT NULL,
  `per_cantidad` int(11) NOT NULL DEFAULT 0,
  `per_aprobados` int(11) NOT NULL DEFAULT 0,
  `pa_estado` tinyint(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (`ani_anio`, `ani_tipo`) REFERENCES `tbl_anio` (`ani_anio`, `ani_tipo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`sec_codigo`, `ani_anio`, `ani_tipo`) REFERENCES `tbl_seccion` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`uc_codigo`) REFERENCES `tbl_uc` (`uc_codigo`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `coordinacion_docente` (
  `cor_nombre` varchar(30) NOT NULL,
  `doc_cedula` int(11) NOT NULL,
  `cor_doc_estado` tinyint(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`cor_nombre`) REFERENCES `tbl_coordinacion` (`cor_nombre`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `docente_horario` (
  `doc_cedula` int(11) DEFAULT NULL,
  `sec_codigo` varchar(30) DEFAULT NULL,
  `ani_anio` int(11) DEFAULT NULL,
  `ani_tipo` varchar(10) DEFAULT NULL,
  FOREIGN KEY (`doc_cedula`) REFERENCES `tbl_docente` (`doc_cedula`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`sec_codigo`, `ani_anio`, `ani_tipo`) REFERENCES `tbl_horario` (`sec_codigo`, `ani_anio`, `ani_tipo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
