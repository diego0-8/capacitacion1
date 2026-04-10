-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-04-2026 a las 16:00:36
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
-- Base de datos: `capacitacion1`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `capacitaciones_asignadas`
--

CREATE TABLE `capacitaciones_asignadas` (
  `id_asignacion` int(11) NOT NULL,
  `cedula_asesor` varchar(10) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_capacitacion` enum('pendiente','en_progreso','evaluacion_pendiente','completado') DEFAULT 'pendiente',
  `progreso_porcentaje` int(11) DEFAULT 0,
  `calificacion_obtenida` decimal(3,2) DEFAULT 0.00,
  `fecha_completado` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id_cursos` int(11) NOT NULL,
  `nombre_curso` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `cedula_coordinador` varchar(10) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id_cursos`, `nombre_curso`, `descripcion`, `estado`, `cedula_coordinador`, `fecha_creacion`) VALUES
(1, 'administracion de empress', 'socio haga algo por usted', 'inactivo', '24141424', '2026-03-31 20:38:57'),
(2, 'CIBERSEGURIDAD PARA EL ASESOR', 'Objetivo: Establecer una línea base de seguridad en el equipo físico (PC) y el entorno de acceso al CRM para mitigar riesgos de intrusión desde el primer contacto.', 'activo', '24141424', '2026-04-08 13:28:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos_modulos`
--

CREATE TABLE `cursos_modulos` (
  `id_modulo` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `modulo` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos_modulos`
--

INSERT INTO `cursos_modulos` (`id_modulo`, `id_curso`, `modulo`, `titulo`, `fecha_creacion`) VALUES
(1, 2, 1, '1. Fundamentos y Seguridad en el Endpoint', '2026-04-08 13:57:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_evaluacion`
--

CREATE TABLE `intentos_evaluacion` (
  `id_intento` int(11) NOT NULL,
  `cedula_asesor` varchar(10) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `puntaje_obtenido` decimal(3,2) DEFAULT NULL,
  `resultado` enum('aprobado','reprobado') NOT NULL,
  `fecha_intento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lecciones`
--

CREATE TABLE `lecciones` (
  `id_leccion` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_modulo` int(11) DEFAULT NULL,
  `titulo_leccion` varchar(150) NOT NULL,
  `contenido` text NOT NULL DEFAULT '',
  `imagen_path` varchar(255) DEFAULT NULL,
  `imagen_texto` text DEFAULT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  `ruta_video` varchar(255) DEFAULT NULL,
  `orden` int(11) NOT NULL,
  `duracion_minutos` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `leccion_completado`
--

CREATE TABLE `leccion_completado` (
  `id_leccion` int(11) NOT NULL,
  `cedula_asesor` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `modulo_quiz_config`
--

CREATE TABLE `modulo_quiz_config` (
  `id_modulo` int(11) NOT NULL,
  `preguntas_requeridas` tinyint(4) NOT NULL DEFAULT 1,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `modulo_preguntas`
--

CREATE TABLE `modulo_preguntas` (
  `id_pregunta_modulo` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `tipo` enum('imagen_par','vf','multi') NOT NULL,
  `enunciado` text NOT NULL,
  `orden` tinyint(4) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `modulo_opciones`
--

CREATE TABLE `modulo_opciones` (
  `id_opcion` int(11) NOT NULL,
  `id_pregunta_modulo` int(11) NOT NULL,
  `clave` enum('a','b','c','d','ok','bad','true','false') NOT NULL,
  `texto` varchar(255) DEFAULT NULL,
  `imagen_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `modulo_preguntas_respuesta`
--

CREATE TABLE `modulo_preguntas_respuesta` (
  `id_pregunta_modulo` int(11) NOT NULL,
  `id_opcion_correcta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `modulo_intentos`
--

CREATE TABLE `modulo_intentos` (
  `id_intento_modulo` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `cedula_asesor` varchar(10) NOT NULL,
  `total_preguntas` tinyint(4) NOT NULL,
  `correctas` tinyint(4) NOT NULL,
  `aprobado` tinyint(1) NOT NULL,
  `fecha_intento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `modulo_respuestas`
--

CREATE TABLE `modulo_respuestas` (
  `id_intento_modulo` int(11) NOT NULL,
  `id_pregunta_modulo` int(11) NOT NULL,
  `id_opcion_elegida` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `modulo_completado`
--

CREATE TABLE `modulo_completado` (
  `id_modulo` int(11) NOT NULL,
  `cedula_asesor` varchar(10) NOT NULL,
  `fecha_completado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `lecciones`
--

INSERT INTO `lecciones` (`id_leccion`, `id_curso`, `id_modulo`, `titulo_leccion`, `contenido`, `imagen_path`, `imagen_texto`, `video_path`, `ruta_video`, `orden`, `duracion_minutos`) VALUES
(1, 1, NULL, 'tareas presenciales para todo el curso videos y demás situaciones', '', NULL, NULL, 'uploads/coordinador/videos/1/WhatsApp_Video_2026-03-31_at_3.55.08_PM.mp4', NULL, 1, 250);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_evaluacion`
--

CREATE TABLE `preguntas_evaluacion` (
  `id_pregunta` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `enunciado` text NOT NULL,
  `opcion_a` varchar(200) NOT NULL,
  `opcion_b` varchar(200) NOT NULL,
  `opcion_c` varchar(200) NOT NULL,
  `opcion_d` varchar(200) NOT NULL,
  `respuesta_correcta` enum('a','b','c','d') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `cedula` varchar(10) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `clave` varchar(250) NOT NULL,
  `rol` enum('administrador','coordinador','asesor') NOT NULL DEFAULT 'asesor',
  `email` varchar(100) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `pin_verificacion_hash` varchar(255) DEFAULT NULL,
  `pin_verificacion_expira_en` datetime DEFAULT NULL,
  `pin_verificacion_intentos` int(11) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`cedula`, `nombre`, `usuario`, `clave`, `rol`, `email`, `estado`, `fecha_creacion`) VALUES
('23122312', 'camilo calderon', 'admin1', '$2y$10$WIn0px9Qn19dchVgJYBaP.rEb6Vpw/mOZhnmC2Yk6wWw/qHLeB5QK', 'administrador', 'admin@gmailcom', 'activo', '2026-03-25 20:03:24'),
('24141424', 'Armando benedetti', 'coord1', '$2y$10$AoXZT0SjhB8hWq/7DAT0T.xVhAK5CDTnwCaJD8xPlqz7/IhwHF9tS', 'coordinador', 'coord@gmail.com', 'activo', '2026-03-25 20:56:22'),
('3212132123', 'samara rodriguez', 'prueba1', '$2y$10$No1meqDv3.HmedHSQ2LWBeRchRw1GinJNHKJjatQ6paSXjMTz0H8e', 'asesor', 'rodriguez@gmail.com', 'activo', '2026-03-31 21:09:15');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_asesores_atrasados`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_asesores_atrasados` (
`asesor` varchar(100)
,`nombre_curso` varchar(100)
,`fecha_asignacion` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_progreso_asesores`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_progreso_asesores` (
`cedula` varchar(10)
,`asesor` varchar(100)
,`nombre_curso` varchar(100)
,`progreso_porcentaje` int(11)
,`estado_capacitacion` enum('pendiente','en_progreso','evaluacion_pendiente','completado')
,`calificacion_obtenida` decimal(3,2)
,`fecha_asignacion` timestamp
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_asesores_atrasados`
--
DROP TABLE IF EXISTS `vista_asesores_atrasados`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_asesores_atrasados`  AS SELECT `u`.`nombre` AS `asesor`, `c`.`nombre_curso` AS `nombre_curso`, `ca`.`fecha_asignacion` AS `fecha_asignacion` FROM ((`capacitaciones_asignadas` `ca` join `usuarios` `u` on(`ca`.`cedula_asesor` = `u`.`cedula`)) join `cursos` `c` on(`ca`.`id_curso` = `c`.`id_cursos`)) WHERE `ca`.`estado_capacitacion` = 'pendiente' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_progreso_asesores`
--
DROP TABLE IF EXISTS `vista_progreso_asesores`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_progreso_asesores`  AS SELECT `u`.`cedula` AS `cedula`, `u`.`nombre` AS `asesor`, `c`.`nombre_curso` AS `nombre_curso`, `ca`.`progreso_porcentaje` AS `progreso_porcentaje`, `ca`.`estado_capacitacion` AS `estado_capacitacion`, `ca`.`calificacion_obtenida` AS `calificacion_obtenida`, `ca`.`fecha_asignacion` AS `fecha_asignacion` FROM ((`capacitaciones_asignadas` `ca` join `usuarios` `u` on(`ca`.`cedula_asesor` = `u`.`cedula`)) join `cursos` `c` on(`ca`.`id_curso` = `c`.`id_cursos`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `capacitaciones_asignadas`
--
ALTER TABLE `capacitaciones_asignadas`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD KEY `fk_curso_cap` (`id_curso`),
  ADD KEY `idx_estado` (`estado_capacitacion`),
  ADD KEY `idx_asesor_busqueda` (`cedula_asesor`),
  ADD KEY `idx_seguimiento_estado` (`estado_capacitacion`,`cedula_asesor`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id_cursos`),
  ADD KEY `idx_cursos_coordinador` (`cedula_coordinador`);

--
-- Indices de la tabla `cursos_modulos`
--
ALTER TABLE `cursos_modulos`
  ADD PRIMARY KEY (`id_modulo`),
  ADD UNIQUE KEY `uq_curso_mod_num` (`id_curso`,`modulo`),
  ADD KEY `idx_curso_mod` (`id_curso`);

--
-- Indices de la tabla `intentos_evaluacion`
--
ALTER TABLE `intentos_evaluacion`
  ADD PRIMARY KEY (`id_intento`),
  ADD KEY `fk_curso_intento` (`id_curso`),
  ADD KEY `idx_historial` (`cedula_asesor`,`fecha_intento`);

--
-- Indices de la tabla `lecciones`
--
ALTER TABLE `lecciones`
  ADD PRIMARY KEY (`id_leccion`),
  ADD KEY `idx_lecciones_modulo` (`id_modulo`),
  ADD KEY `idx_modulo_orden` (`id_modulo`,`orden`),
  ADD KEY `idx_curso_orden` (`id_curso`,`orden`),
  ADD KEY `idx_curso_clase` (`id_curso`,`orden`);

--
-- Indices de la tabla `leccion_completado`
--
ALTER TABLE `leccion_completado`
  ADD PRIMARY KEY (`id_leccion`,`cedula_asesor`),
  ADD KEY `idx_lec_comp_asesor` (`cedula_asesor`);

--
-- Indices de la tabla `modulo_quiz_config`
--
ALTER TABLE `modulo_quiz_config`
  ADD PRIMARY KEY (`id_modulo`);

--
-- Indices de la tabla `modulo_preguntas`
--
ALTER TABLE `modulo_preguntas`
  ADD PRIMARY KEY (`id_pregunta_modulo`),
  ADD UNIQUE KEY `uq_mod_preg_orden` (`id_modulo`,`orden`),
  ADD KEY `idx_mod_preg_modulo` (`id_modulo`);

--
-- Indices de la tabla `modulo_opciones`
--
ALTER TABLE `modulo_opciones`
  ADD PRIMARY KEY (`id_opcion`),
  ADD UNIQUE KEY `uq_mod_opc_clave` (`id_pregunta_modulo`,`clave`),
  ADD KEY `idx_mod_opc_preg` (`id_pregunta_modulo`);

--
-- Indices de la tabla `modulo_preguntas_respuesta`
--
ALTER TABLE `modulo_preguntas_respuesta`
  ADD PRIMARY KEY (`id_pregunta_modulo`),
  ADD UNIQUE KEY `uq_mod_resp_opc` (`id_opcion_correcta`);

--
-- Indices de la tabla `modulo_intentos`
--
ALTER TABLE `modulo_intentos`
  ADD PRIMARY KEY (`id_intento_modulo`),
  ADD KEY `idx_mod_intento_hist` (`id_modulo`,`cedula_asesor`,`fecha_intento`);

--
-- Indices de la tabla `modulo_respuestas`
--
ALTER TABLE `modulo_respuestas`
  ADD PRIMARY KEY (`id_intento_modulo`,`id_pregunta_modulo`),
  ADD KEY `idx_mod_resp_preg` (`id_pregunta_modulo`),
  ADD KEY `idx_mod_resp_opc` (`id_opcion_elegida`);

--
-- Indices de la tabla `modulo_completado`
--
ALTER TABLE `modulo_completado`
  ADD PRIMARY KEY (`id_modulo`,`cedula_asesor`);

--
-- Indices de la tabla `preguntas_evaluacion`
--
ALTER TABLE `preguntas_evaluacion`
  ADD PRIMARY KEY (`id_pregunta`),
  ADD KEY `idx_preguntas_curso` (`id_curso`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`cedula`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_nombre` (`nombre`),
  ADD KEY `idx_busqueda_nombre` (`nombre`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `capacitaciones_asignadas`
--
ALTER TABLE `capacitaciones_asignadas`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id_cursos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cursos_modulos`
--
ALTER TABLE `cursos_modulos`
  MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `intentos_evaluacion`
--
ALTER TABLE `intentos_evaluacion`
  MODIFY `id_intento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lecciones`
--
ALTER TABLE `lecciones`
  MODIFY `id_leccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `modulo_preguntas`
--
ALTER TABLE `modulo_preguntas`
  MODIFY `id_pregunta_modulo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `modulo_opciones`
--
ALTER TABLE `modulo_opciones`
  MODIFY `id_opcion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `modulo_intentos`
--
ALTER TABLE `modulo_intentos`
  MODIFY `id_intento_modulo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preguntas_evaluacion`
--
ALTER TABLE `preguntas_evaluacion`
  MODIFY `id_pregunta` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `capacitaciones_asignadas`
--
ALTER TABLE `capacitaciones_asignadas`
  ADD CONSTRAINT `fk_asesor_cap` FOREIGN KEY (`cedula_asesor`) REFERENCES `usuarios` (`cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_curso_cap` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_cursos`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `fk_curso_coordinador` FOREIGN KEY (`cedula_coordinador`) REFERENCES `usuarios` (`cedula`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `cursos_modulos`
--
ALTER TABLE `cursos_modulos`
  ADD CONSTRAINT `fk_curso_modulos` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_cursos`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `intentos_evaluacion`
--
ALTER TABLE `intentos_evaluacion`
  ADD CONSTRAINT `fk_asesor_intento` FOREIGN KEY (`cedula_asesor`) REFERENCES `usuarios` (`cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_curso_intento` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_cursos`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `lecciones`
--
ALTER TABLE `lecciones`
  ADD CONSTRAINT `fk_modulo_leccion` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_curso_lecciones` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_cursos`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `leccion_completado`
--
ALTER TABLE `leccion_completado`
  ADD CONSTRAINT `fk_lec_comp_leccion` FOREIGN KEY (`id_leccion`) REFERENCES `lecciones` (`id_leccion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lec_comp_asesor` FOREIGN KEY (`cedula_asesor`) REFERENCES `usuarios` (`cedula`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_quiz_config`
--
ALTER TABLE `modulo_quiz_config`
  ADD CONSTRAINT `fk_modulo_quiz_config_mod` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_preguntas`
--
ALTER TABLE `modulo_preguntas`
  ADD CONSTRAINT `fk_mod_preg_mod` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_opciones`
--
ALTER TABLE `modulo_opciones`
  ADD CONSTRAINT `fk_mod_opc_preg` FOREIGN KEY (`id_pregunta_modulo`) REFERENCES `modulo_preguntas` (`id_pregunta_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_preguntas_respuesta`
--
ALTER TABLE `modulo_preguntas_respuesta`
  ADD CONSTRAINT `fk_mod_resp_preg` FOREIGN KEY (`id_pregunta_modulo`) REFERENCES `modulo_preguntas` (`id_pregunta_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_resp_opc` FOREIGN KEY (`id_opcion_correcta`) REFERENCES `modulo_opciones` (`id_opcion`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_intentos`
--
ALTER TABLE `modulo_intentos`
  ADD CONSTRAINT `fk_mod_intento_mod` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_intento_asesor` FOREIGN KEY (`cedula_asesor`) REFERENCES `usuarios` (`cedula`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_respuestas`
--
ALTER TABLE `modulo_respuestas`
  ADD CONSTRAINT `fk_mod_resp_intento` FOREIGN KEY (`id_intento_modulo`) REFERENCES `modulo_intentos` (`id_intento_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_resp_preg2` FOREIGN KEY (`id_pregunta_modulo`) REFERENCES `modulo_preguntas` (`id_pregunta_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_resp_opc2` FOREIGN KEY (`id_opcion_elegida`) REFERENCES `modulo_opciones` (`id_opcion`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_completado`
--
ALTER TABLE `modulo_completado`
  ADD CONSTRAINT `fk_mod_compl_mod` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_compl_asesor` FOREIGN KEY (`cedula_asesor`) REFERENCES `usuarios` (`cedula`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `preguntas_evaluacion`
--
ALTER TABLE `preguntas_evaluacion`
  ADD CONSTRAINT `fk_curso_preguntas` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_cursos`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
