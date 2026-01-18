-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-01-2026 a las 10:01:39
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
-- Base de datos: `umb_viajes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actualizacion_requisito`
--

CREATE TABLE `actualizacion_requisito` (
  `id_actualizacion` int(11) NOT NULL,
  `id_requisito` int(11) NOT NULL,
  `descripcion_cambio` text NOT NULL,
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_por` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `actualizacion_requisito`
--

INSERT INTO `actualizacion_requisito` (`id_actualizacion`, `id_requisito`, `descripcion_cambio`, `fecha_actualizacion`, `actualizado_por`) VALUES
(1, 1, 'cambio 1', '2026-01-12 22:31:46', 1),
(4, 3, 'Ya no se necesita pasaporte', '2026-01-12 23:19:50', 1),
(5, 1, 'El viajero ya no nesecita pasaporte.', '2026-01-14 22:12:03', 1),
(6, 1, 'RR', '2026-01-14 22:48:15', 1),
(7, 1, 'change 1', '2026-01-14 23:32:53', 1),
(8, 1, 'hytr', '2026-01-14 23:48:19', 1),
(9, 6, 'tyu', '2026-01-15 00:17:24', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aviso_actualizacion`
--

CREATE TABLE `aviso_actualizacion` (
  `id_aviso` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `titulo_aviso` varchar(180) NOT NULL,
  `detalle_aviso` text NOT NULL,
  `fecha_publicacion` datetime NOT NULL DEFAULT current_timestamp(),
  `publicado_por` int(11) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `aviso_actualizacion`
--

INSERT INTO `aviso_actualizacion` (`id_aviso`, `id_destino`, `titulo_aviso`, `detalle_aviso`, `fecha_publicacion`, `publicado_por`, `estado`) VALUES
(1, 4, 'Nuevo permiso ESTA', 'saaddadsadsad', '2026-01-12 23:21:55', 1, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `destino`
--

CREATE TABLE `destino` (
  `id_destino` int(11) NOT NULL,
  `pais` varchar(120) NOT NULL,
  `ciudad` varchar(120) DEFAULT NULL,
  `descripcion_general` text DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `bandera_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `destino`
--

INSERT INTO `destino` (`id_destino`, `pais`, `ciudad`, `descripcion_general`, `estado`, `bandera_path`) VALUES
(2, 'Estados Unidos', 'Nueva York', 'Destino internacional frecuente para turismo y negocios.', 'activo', 'assets/flags/flag_2.png'),
(3, 'Peru', 'Lima', NULL, 'activo', 'assets/flags/flag_3.png'),
(4, 'Colombia', 'Bogota', 'NN', 'activo', 'assets/flags/flag_4.png'),
(6, 'Mexico', 'Cancun', NULL, 'activo', 'assets/flags/flag_6.png'),
(7, 'Argentina', 'Buenos aires', 'achyusadbhcds', 'activo', 'assets/flags/flag_7.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `destino_destacado`
--

CREATE TABLE `destino_destacado` (
  `id_destacado` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `titulo` varchar(180) NOT NULL,
  `descripcion` text NOT NULL,
  `imagen_path` varchar(255) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `destino_destacado`
--

INSERT INTO `destino_destacado` (`id_destacado`, `id_destino`, `titulo`, `descripcion`, `imagen_path`, `orden`, `estado`) VALUES
(9, 4, 'Bogota', 'Planes rapidos, cafes de especialidad y vistas panoramicas.', 'assets/img/main.webp', 1, 'activo'),
(10, 2, 'Madrid', 'Descripcion', 'assets/destinos_destacados/destacado_6969dc8dd4a45067881734.png', 2, 'activo'),
(11, 3, 'Peru', 'Peru', 'assets/destinos_destacados/destacado_6969f0e31b7f0205721264.png', 0, 'activo'),
(12, 6, 'Ecuador', 'texto', 'assets/destinos_destacados/destacado_6969f0f6e01ad197012039.png', 0, 'activo'),
(13, 2, 'San Francisco', 'golden gate', 'assets/destinos_destacados/destacado_6969f10d5a8ce734284218.png', 0, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `experiencia_viajero`
--

CREATE TABLE `experiencia_viajero` (
  `id_experiencia` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `titulo` varchar(180) NOT NULL,
  `contenido` text NOT NULL,
  `fecha_envio` datetime NOT NULL DEFAULT current_timestamp(),
  `estado_moderacion` enum('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `fecha_publicacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `experiencia_viajero`
--

INSERT INTO `experiencia_viajero` (`id_experiencia`, `id_usuario`, `id_destino`, `titulo`, `contenido`, `fecha_envio`, `estado_moderacion`, `fecha_publicacion`) VALUES
(2, 4, 2, 'LAS VEGASSSSSSS', 'PRUEEEEBAAAAAATRRRRRRRRFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF', '2026-01-12 00:55:32', 'aprobada', '2026-01-12 00:56:04'),
(3, 5, 7, 'ARGETINA AZUL', 'PASTEL DE CARNEPASTEL DE CARNEPASTEL DE CARNEPASTEL DE CARNEPASTEL DE CARNEPASTEL DE CARNEPASTEL DE CARNEPASTEL DE CARNEPASTEL DE CARNEPASTEL DE CARNEPASTEL DE CARNE', '2026-01-15 22:24:14', 'aprobada', '2026-01-15 22:25:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `moderacion_experiencia`
--

CREATE TABLE `moderacion_experiencia` (
  `id_moderacion` int(11) NOT NULL,
  `id_experiencia` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `decision` enum('aprobada','rechazada') NOT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `fecha_revision` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `moderacion_experiencia`
--

INSERT INTO `moderacion_experiencia` (`id_moderacion`, `id_experiencia`, `id_admin`, `decision`, `observacion`, `fecha_revision`) VALUES
(2, 2, 1, 'aprobada', NULL, '2026-01-12 00:56:04'),
(3, 3, 1, 'aprobada', NULL, '2026-01-15 22:25:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `requisito_viaje`
--

CREATE TABLE `requisito_viaje` (
  `id_requisito` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `titulo_requisito` varchar(180) NOT NULL,
  `descripcion_requisito` text NOT NULL,
  `tipo_requisito` varchar(80) NOT NULL,
  `fuente_oficial` text DEFAULT NULL,
  `fecha_ultima_actualizacion` date NOT NULL,
  `creado_por` int(11) NOT NULL,
  `estado` enum('vigente','no_vigente') NOT NULL DEFAULT 'vigente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `requisito_viaje`
--

INSERT INTO `requisito_viaje` (`id_requisito`, `id_destino`, `titulo_requisito`, `descripcion_requisito`, `tipo_requisito`, `fuente_oficial`, `fecha_ultima_actualizacion`, `creado_por`, `estado`) VALUES
(1, 2, 'Pasaporte vigente', 'El viajero debe contar con pasaporte vigente durante según el', 'recomendado', 'Fuente oficial recomendada: sitios gubernamentales del país destino.', '2026-01-14', 1, 'vigente'),
(3, 4, 'Pasaporte', 'Pasaporte vigente', 'obligatorio', 'www.colombia.com', '2026-01-12', 1, 'vigente'),
(6, 6, 'CARTNET', 'DE DEBE PORTAR EL CARNET DE VAN', 'obligatorio', 'MEXX', '2026-01-15', 1, 'vigente'),
(8, 6, 'nu', 'rtvgbhjnk', 'obligatorio', 'j', '2026-01-15', 1, 'vigente'),
(9, 7, 'Pasaporte', 'asvgfsadFWEDGSA<', 'obligatorio', 'ahrbhuiejnd', '2026-01-16', 1, 'vigente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(60) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'Viajero', 'Usuario que consulta información y publica experiencias'),
(2, 'Administrador', 'Usuario con permisos de administración y moderación');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `correo` varchar(190) NOT NULL,
  `contrasena_hash` varchar(255) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `correo`, `contrasena_hash`, `fecha_registro`, `estado`) VALUES
(1, 'admin@umb.local', '$2y$10$qt1gaaBO59utdxpFQMUCkuEb1CXY2qmK93h4yIQvwmFTU/8pggLIO', '2026-01-03 22:03:58', 'activo'),
(2, 'test@test.com', '$2y$10$MNnbehwA4JCvkR4C.V56luVqdCFTO8xkACRCgvX/WGk3inWqv9c7u', '2026-01-03 22:07:53', 'activo'),
(4, 'pruebas@test.com', '$2y$10$VAfLG1qZ29KHI982POODbewLZ7317yvTX1McN2fn1OhAElsWgBPrS', '2026-01-12 00:55:10', 'activo'),
(5, 'paiula@124.murat', '$2y$10$Y4VyBO.EM4eIRrKmaJ3dC.MIcOo7YRDwUACGGVd5prT.vtjmypJzC', '2026-01-15 22:23:27', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_rol`
--

CREATE TABLE `usuario_rol` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario_rol`
--

INSERT INTO `usuario_rol` (`id_usuario`, `id_rol`) VALUES
(1, 2),
(2, 1),
(4, 1),
(5, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actualizacion_requisito`
--
ALTER TABLE `actualizacion_requisito`
  ADD PRIMARY KEY (`id_actualizacion`),
  ADD KEY `fk_act_req` (`id_requisito`),
  ADD KEY `fk_act_usuario` (`actualizado_por`);

--
-- Indices de la tabla `aviso_actualizacion`
--
ALTER TABLE `aviso_actualizacion`
  ADD PRIMARY KEY (`id_aviso`),
  ADD KEY `fk_av_dest` (`id_destino`),
  ADD KEY `fk_av_pub` (`publicado_por`);

--
-- Indices de la tabla `destino`
--
ALTER TABLE `destino`
  ADD PRIMARY KEY (`id_destino`);

--
-- Indices de la tabla `destino_destacado`
--
ALTER TABLE `destino_destacado`
  ADD PRIMARY KEY (`id_destacado`),
  ADD KEY `fk_dest_destacado` (`id_destino`);

--
-- Indices de la tabla `experiencia_viajero`
--
ALTER TABLE `experiencia_viajero`
  ADD PRIMARY KEY (`id_experiencia`),
  ADD KEY `fk_exp_usuario` (`id_usuario`),
  ADD KEY `fk_exp_destino` (`id_destino`);

--
-- Indices de la tabla `moderacion_experiencia`
--
ALTER TABLE `moderacion_experiencia`
  ADD PRIMARY KEY (`id_moderacion`),
  ADD KEY `fk_mod_exp` (`id_experiencia`),
  ADD KEY `fk_mod_admin` (`id_admin`);

--
-- Indices de la tabla `requisito_viaje`
--
ALTER TABLE `requisito_viaje`
  ADD PRIMARY KEY (`id_requisito`),
  ADD KEY `fk_req_destino` (`id_destino`),
  ADD KEY `fk_req_creador` (`creado_por`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD PRIMARY KEY (`id_usuario`,`id_rol`),
  ADD KEY `fk_ur_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actualizacion_requisito`
--
ALTER TABLE `actualizacion_requisito`
  MODIFY `id_actualizacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `aviso_actualizacion`
--
ALTER TABLE `aviso_actualizacion`
  MODIFY `id_aviso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `destino`
--
ALTER TABLE `destino`
  MODIFY `id_destino` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `destino_destacado`
--
ALTER TABLE `destino_destacado`
  MODIFY `id_destacado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `experiencia_viajero`
--
ALTER TABLE `experiencia_viajero`
  MODIFY `id_experiencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `moderacion_experiencia`
--
ALTER TABLE `moderacion_experiencia`
  MODIFY `id_moderacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `requisito_viaje`
--
ALTER TABLE `requisito_viaje`
  MODIFY `id_requisito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actualizacion_requisito`
--
ALTER TABLE `actualizacion_requisito`
  ADD CONSTRAINT `fk_act_req` FOREIGN KEY (`id_requisito`) REFERENCES `requisito_viaje` (`id_requisito`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_act_usuario` FOREIGN KEY (`actualizado_por`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `aviso_actualizacion`
--
ALTER TABLE `aviso_actualizacion`
  ADD CONSTRAINT `fk_av_dest` FOREIGN KEY (`id_destino`) REFERENCES `destino` (`id_destino`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_av_pub` FOREIGN KEY (`publicado_por`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `destino_destacado`
--
ALTER TABLE `destino_destacado`
  ADD CONSTRAINT `fk_dest_destacado` FOREIGN KEY (`id_destino`) REFERENCES `destino` (`id_destino`) ON DELETE CASCADE;

--
-- Filtros para la tabla `experiencia_viajero`
--
ALTER TABLE `experiencia_viajero`
  ADD CONSTRAINT `fk_exp_destino` FOREIGN KEY (`id_destino`) REFERENCES `destino` (`id_destino`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exp_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `moderacion_experiencia`
--
ALTER TABLE `moderacion_experiencia`
  ADD CONSTRAINT `fk_mod_admin` FOREIGN KEY (`id_admin`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `fk_mod_exp` FOREIGN KEY (`id_experiencia`) REFERENCES `experiencia_viajero` (`id_experiencia`) ON DELETE CASCADE;

--
-- Filtros para la tabla `requisito_viaje`
--
ALTER TABLE `requisito_viaje`
  ADD CONSTRAINT `fk_req_creador` FOREIGN KEY (`creado_por`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `fk_req_destino` FOREIGN KEY (`id_destino`) REFERENCES `destino` (`id_destino`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD CONSTRAINT `fk_ur_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ur_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
