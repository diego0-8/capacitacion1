-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-04-2026 a las 23:58:34
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

--
-- Volcado de datos para la tabla `capacitaciones_asignadas`
--

INSERT INTO `capacitaciones_asignadas` (`id_asignacion`, `cedula_asesor`, `id_curso`, `fecha_asignacion`, `estado_capacitacion`, `progreso_porcentaje`, `calificacion_obtenida`, `fecha_completado`) VALUES
(1, '3212132123', 2, '2026-04-08 15:44:14', 'en_progreso', 33, 0.00, NULL),
(2, '7766776', 2, '2026-04-09 15:47:22', 'en_progreso', 0, 0.00, NULL);

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
(2, 'CIBERSEGURIDAD PARA EL ASESOR', 'Proteger la información sensible de los clientes mediante el uso estricto de contraseñas personales, el cumplimiento riguroso de los protocolos de validación de identidad y la vigilancia constante ante intentos de engaño (ingeniería social), asegurando que ningún dato salga del entorno corporativo ni entre ninguna amenaza externa a través de dispositivos o sitios no autorizados.', 'activo', '24141424', '2026-04-08 13:28:03');

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
(2, 2, 1, 'Fundamentos y Seguridad en el Endpoint', '2026-04-08 15:38:52'),
(3, 2, 2, 'Uso Seguro del Aplicativo y Gestión de Datos', '2026-04-08 19:54:05'),
(4, 2, 3, 'Seguridad en la Red y Navegación Corporativa', '2026-04-08 21:47:18');

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

--
-- Volcado de datos para la tabla `lecciones`
--

INSERT INTO `lecciones` (`id_leccion`, `id_curso`, `id_modulo`, `titulo_leccion`, `contenido`, `imagen_path`, `imagen_texto`, `video_path`, `ruta_video`, `orden`, `duracion_minutos`) VALUES
(1, 1, NULL, 'tareas presenciales para todo el curso videos y demás situaciones', '', NULL, NULL, NULL, 'uploads/coordinador/videos/1/WhatsApp_Video_2026-03-31_at_3.55.08_PM.mp4', 1, 250),
(2, 2, 2, '1.1 Gestión de Identidad y Autenticación Robusta', 'Más allá de las contraseñas básicas. Implementación de frases de contraseña (passphrases) y la importancia del MFA (Autenticación de Múltiples Factores) en el acceso al CRM.', 'uploads/lecciones/2/1775666978_primera_foto.jpg', 'Aprenderás a crear contraseñas empresariales siguiendo la regla de longitud sobre complejidad para que sean fáciles de recordar pero imposibles de adivinar. Veremos cómo gestionar tus accesos de forma segura dentro del sistema de la compañía, cumpliendo con los cambios obligatorios sin comprometer la seguridad de tu usuario.', 'uploads/lecciones/2/1775666582_TRUCO__C__mo_crear_una_CONTRASE__A_SEGURA_y_FACIL_de_recordar____.mp4', NULL, 1, 0),
(3, 2, 2, '1.2 Seguridad en el Navegador y Entorno Web', 'Configuración segura del navegador (Chrome/Edge). Uso de extensiones permitidas, limpieza de caché/cookies y el peligro de \"recordar contraseñas\" en equipos compartidos.', 'uploads/lecciones/2/1775669090_descarga.jpg', 'Configuraremos tu navegador y las herramientas del PC para que trabajen de forma privada. Aprenderás a identificar sitios web oficiales de la empresa y a evitar que el sistema guarde información confidencial automáticamente, asegurando que tu rastro digital se borre al terminar cada jornada.', 'uploads/lecciones/2/1775669090_video2.mp4', NULL, 2, 0),
(4, 2, 2, '1.3 Ingeniería Social: El eslabón más débil', 'Detección de Phishing, Vishing y Smishing enfocado en asesores. Cómo los atacantes intentan obtener las credenciales del CRM mediante engaños.', NULL, NULL, NULL, NULL, 3, 0),
(5, 2, 2, '1.4 Protección de Datos y Cumplimiento (Privacidad)', 'Manejo de datos sensibles de clientes dentro del CRM. Normativa de protección de datos (como la Ley 1581 en Colombia) aplicada al registro de información.', 'uploads/lecciones/2/1775669618_imagen_4.jpg', 'Tu puesto de trabajo es un área de alta seguridad. En esta clase entenderás por qué el manejo de datos de clientes está estrictamente vigilado por el sistema y la importancia de mantener tu PC bloqueado y tu área libre de dispositivos externos (como memorias USB), cumpliendo con las normas de confidencialidad de la empresa.', 'uploads/lecciones/2/1775669618_video3.mp4', NULL, 4, 0),
(6, 2, 3, '2.1 El ABC del Manejo de Datos Sensibles', 'Aprenderás a identificar qué información del cliente es privada y cómo tratarla dentro del sistema. Veremos por qué nunca debes copiar datos fuera de los campos autorizados del CRM y cómo el sistema protege la privacidad de cada persona que atendemos.\r\n\r\nFoco: Evitar el \"copy-paste\" de datos a documentos externos (Excel, Bloc de notas) o chats no autorizados.', 'uploads/lecciones/2/1775680088_datos.jpg', 'En esta clase explicamos que existen datos que, de filtrarse, pueden causar daños legales graves. Se enseña que la información (nombres, documentos, saldos) debe permanecer dentro de los campos del CRM. Se profundiza en el riesgo del \"portapapeles\" del PC: si copias un dato y luego entras a una página personal, ese dato podría quedar expuesto. La regla de oro es: \"Lo que está en el CRM, se queda en el CRM\"', 'uploads/lecciones/2/1775680088_video_datos_-2.mp4', NULL, 1, 0),
(7, 2, 3, '2.2 Alertas y Comportamientos Extraños del Sistema', 'Aprenderás a reconocer cuando el aplicativo no se comporta normal (ventanas emergentes sospechosas, lentitud extrema o solicitudes de datos inusuales). Sabrás exactamente qué hacer y a quién reportar si notas que alguien más podría estar usando tu sesión.\r\n\r\nDetección de posibles inyecciones de código o accesos concurrentes.', 'uploads/lecciones/2/1775681040_imagen_contrase__as.jpg', 'Un sistema seguro puede ser atacado. Aquí enseñamos al asesor a ser un \"vigilante\". Si el CRM se pone lento de repente, si aparecen anuncios (pop-ups) que antes no estaban, o si el sistema le pide su clave en medio de una gestión, son señales de alerta. La instrucción es clara: ante cualquier anomalía, se debe informar al supervisor de inmediato en lugar de intentar \"arreglarlo\" o ignorarlo.', 'uploads/lecciones/2/1775681040_Ciberseguridad__Contrase__as_seguras.mp4', NULL, 2, 0),
(8, 2, 3, '2.3 Comunicación Segura y Uso del Softphone', 'Las llamadas también son datos. En esta clase veremos cómo usar las herramientas de comunicación de forma segura, evitando mencionar datos sensibles en voz alta si no es estrictamente necesario y que las grabaciones de llamadas se guardan correctamente en el sistema.\"\r\n\r\nSeguridad en la voz y evitar que el asesor anote datos en papel mientras habla.', 'uploads/lecciones/2/1775682174_seguridad_de_voz.jpg', 'Cuando hablamos con un cliente, no solo estamos ofreciendo servicio; estamos gestionando activos de información. Una dirección, un número de identificación o una confirmación de pago son datos que, si se manejan mal, pueden comprometer la seguridad de la empresa y del cliente.\r\n\r\nPrivacidad auditiva: Evita repetir datos sensibles en voz alta (como números de tarjetas o contraseñas). Si necesitas confirmar un dato, pide al cliente que lo digite en el teclado o confírmalo de forma parcial (ej. \"¿Correcto, termina en 45?\").\r\n\r\nUso del Mute: Utiliza el botón de silencio (Mute) siempre que necesites consultar algo internamente o validar información fuera de la línea con el cliente.\r\n\r\nTipificación correcta: Asegúrate de cerrar cada caso con el estado adecuado en el CRM. Una llamada mal tipificada puede resultar en una grabación huérfana o difícil de localizar en caso de una auditoría.', NULL, NULL, 3, 0),
(9, 2, 3, '2.4 Cierre de Ciclo y Gestión de Sesiones', 'Aprenderás la importancia de finalizar correctamente cada gestión. Veremos por qué cerrar la ventana\' no es lo mismo que \'cerrar sesión\' y cómo asegurar que, al irte a break o terminar tu turno, el aplicativo quede totalmente protegido contra accesos de terceros.', 'uploads/lecciones/2/1775682640_CIERRE_DE_APLICATIVO.JPG', 'Imagina que entras a una bóveda de un banco con tu llave. Mientras estás adentro, la puerta está abierta para ti. Eso es una Sesión Activa.\r\n\r\nEn términos técnicos, cuando ingresas tu usuario y contraseña, el servidor del CRM crea un \"token\" o permiso de conexión. Este permiso tiene una duración. Si simplemente cierras la pestaña del navegador o la ventana, el permiso sigue vivo en el servidor.\r\n\r\nEl riesgo: Si un software malicioso (malware) está en el equipo o si un tercero se sienta en tu lugar, la conexión sigue establecida y pueden acceder a los datos de los clientes sin volver a pedir contraseña.\r\n\r\nEs vital entender la diferencia mecánica entre estas dos acciones:\r\n\r\nCerrar la ventana (X): Solo oculta la interfaz. La \"llave\" sigue puesta en la cerradura del servidor. La sesión permanece abierta hasta que expire por tiempo (timeout), lo cual puede tardar minutos u horas.\r\n\r\nCerrar Sesión (Logout): Envía una instrucción inmediata al servidor para destruir el token de conexión. Es la única forma de garantizar que la puerta está bajo llave.', NULL, NULL, 4, 0),
(10, 2, 4, '3.1 Navegación Segura y Sitios Permitidos', 'Aprenderás a identificar cuáles sitios web son seguros para tu trabajo y por qué navegar en páginas no autorizadas pone en riesgo la velocidad y la seguridad de tu CRM. Veremos cómo reconocer un sitio oficial de la empresa frente a uno falso.\"\r\n\r\nDiferenciación entre herramientas de trabajo y sitios de ocio que pueden contener scripts maliciosos.', 'uploads/lecciones/2/1775685683_sitios_seguros.jpg', 'Tu estación de trabajo es un túnel directo a la información privada de miles de personas. El sistema está optimizado para que el CRM y el Softphone funcionen con la máxima velocidad.\r\n\r\nSitios de Trabajo: Son las herramientas oficiales (CRM, bases de conocimiento, correo corporativo). Están protegidos y monitoreados.\r\n\r\nSitios de Ocio: Redes sociales personales, páginas de películas piratas o juegos online. Estos sitios suelen contener \"Scripts maliciosos\" (pequeños códigos ocultos) que se ejecutan solos al entrar y pueden ralentizar tu PC o capturar lo que escribes.\r\n\r\nLos atacantes crean páginas que se ven exactamente iguales a tu CRM para que tú mismo les entregues tu usuario y contraseña. Antes de escribir nada, revisa estos tres puntos:\r\n\r\nLa URL (Dirección): Mira bien el texto en la barra de arriba. Un sitio falso diría algo como crm-empresa-login.com en lugar de crm.empresa.com.\r\n\r\nEl Candado de Seguridad: Debe aparecer un candado cerrado a la izquierda de la dirección. Si el navegador te dice \"Sitio no seguro\", cierra la pestaña de inmediato.\r\n\r\nEl protocolo HTTPS: La dirección debe empezar siempre con https://. La \"S\" significa que la conexión entre tu PC y el servidor está cifrada.\r\n\r\nNavegar en un sitio no autorizado no solo te pone en riesgo a ti; pone en riesgo a todo el Call Center. Un solo virus que entre por una página de descargas puede:\r\n\r\nRalentizar el sistema: Haciendo que tus llamadas se corten o el CRM se pegue.\r\n\r\nRobar sesiones: Alguien externo podría usar tu usuario para ver datos prohibidos, y legalmente parecería que fuiste tú.', 'uploads/lecciones/2/1775685683_video4.mp4', NULL, 1, 0),
(11, 2, 4, '3.2 Descargas y Archivos Sospechosos', '', NULL, NULL, NULL, NULL, 2, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `leccion_completado`
--

CREATE TABLE `leccion_completado` (
  `id_leccion` int(11) NOT NULL,
  `cedula_asesor` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `leccion_completado`
--

INSERT INTO `leccion_completado` (`id_leccion`, `cedula_asesor`, `created_at`) VALUES
(2, '3212132123', '2026-04-08 16:30:23'),
(3, '3212132123', '2026-04-08 16:30:28'),
(4, '3212132123', '2026-04-08 16:30:32'),
(5, '3212132123', '2026-04-08 16:30:35');

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
-- Volcado de datos para la tabla `modulo_completado`
--

INSERT INTO `modulo_completado` (`id_modulo`, `cedula_asesor`, `fecha_completado`) VALUES
(2, '3212132123', '2026-04-08 16:30:53');

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

--
-- Volcado de datos para la tabla `modulo_intentos`
--

INSERT INTO `modulo_intentos` (`id_intento_modulo`, `id_modulo`, `cedula_asesor`, `total_preguntas`, `correctas`, `aprobado`, `fecha_intento`) VALUES
(1, 2, '3212132123', 1, 1, 1, '2026-04-08 16:30:53');

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

--
-- Volcado de datos para la tabla `modulo_opciones`
--

INSERT INTO `modulo_opciones` (`id_opcion`, `id_pregunta_modulo`, `clave`, `texto`, `imagen_path`) VALUES
(1, 1, 'true', 'Verdadero', NULL),
(2, 1, 'false', 'Falso', NULL),
(3, 2, 'true', 'Verdadero', NULL),
(4, 2, 'false', 'Falso', NULL),
(5, 3, 'true', 'Verdadero', NULL),
(6, 3, 'false', 'Falso', NULL),
(7, 4, 'a', 'Enviarlo por el chat interno a un compañero para que lo guarde.', NULL),
(8, 4, 'b', 'Anotarlo en un bloc de notas digital (Notepad) o en un post-it físico.', NULL),
(9, 4, 'c', 'Registrarlo exclusivamente en los campos de \"Notas\" o \"Observaciones\" dentro del CRM.', NULL),
(10, 4, 'd', 'Ignorarlo, probablemente es un error técnico del sistema (bug).', NULL),
(11, 5, 'a', 'Dejar la ventana del CRM abierta para no perder el hilo de lo que estabas haciendo.', NULL),
(12, 5, 'b', 'Bloquear el PC usando la combinación de teclas $Windows + L$.', NULL),
(13, 5, 'c', 'Solo cerrar la ventana del navegador haciendo clic en la \"X\".', NULL),
(14, 5, 'd', 'ninguna', NULL),
(15, 6, 'true', 'Verdadero', NULL),
(16, 6, 'false', 'Falso', NULL);

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

--
-- Volcado de datos para la tabla `modulo_preguntas`
--

INSERT INTO `modulo_preguntas` (`id_pregunta_modulo`, `id_modulo`, `tipo`, `enunciado`, `orden`, `fecha_creacion`) VALUES
(1, 2, 'vf', 'Si recibes un correo solicitando actualizar tu clave del CRM mediante un enlace externo es una acción correcta', 1, '2026-04-08 15:42:22'),
(2, 2, 'vf', '', 2, '2026-04-08 15:42:22'),
(3, 2, 'vf', '', 3, '2026-04-08 15:42:22'),
(4, 3, 'multi', 'Si necesitas recordar un dato importante del cliente para el final de la llamada, ¿cuál es la acción correcta?', 1, '2026-04-08 21:45:13'),
(5, 3, 'multi', 'Te levantas de tu puesto solo por un minuto para buscar una hoja a la impresora. ¿Cuál es el procedimiento obligatorio?', 2, '2026-04-08 21:45:13'),
(6, 4, 'vf', '\"Si una página web se ve exactamente igual a la plataforma oficial de la empresa y tiene el logotipo correcto, significa que es un sitio seguro y puedo ingresar mi usuario y contraseña sin riesgo.', 1, '2026-04-08 22:03:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulo_preguntas_respuesta`
--

CREATE TABLE `modulo_preguntas_respuesta` (
  `id_pregunta_modulo` int(11) NOT NULL,
  `id_opcion_correcta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modulo_preguntas_respuesta`
--

INSERT INTO `modulo_preguntas_respuesta` (`id_pregunta_modulo`, `id_opcion_correcta`) VALUES
(1, 2),
(2, 3),
(3, 5),
(4, 9),
(5, 12),
(6, 16);

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

--
-- Volcado de datos para la tabla `modulo_quiz_config`
--

INSERT INTO `modulo_quiz_config` (`id_modulo`, `preguntas_requeridas`, `activo`, `fecha_actualizacion`) VALUES
(2, 1, 1, '2026-04-08 15:42:22'),
(3, 2, 0, '2026-04-08 21:45:13'),
(4, 1, 0, '2026-04-08 22:03:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulo_respuestas`
--

CREATE TABLE `modulo_respuestas` (
  `id_intento_modulo` int(11) NOT NULL,
  `id_pregunta_modulo` int(11) NOT NULL,
  `id_opcion_elegida` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modulo_respuestas`
--

INSERT INTO `modulo_respuestas` (`id_intento_modulo`, `id_pregunta_modulo`, `id_opcion_elegida`) VALUES
(1, 1, 2);

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
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `pin_verificacion_hash` varchar(255) DEFAULT NULL,
  `pin_verificacion_expira_en` datetime DEFAULT NULL,
  `pin_verificacion_intentos` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`cedula`, `nombre`, `usuario`, `clave`, `rol`, `email`, `estado`, `fecha_creacion`, `pin_verificacion_hash`, `pin_verificacion_expira_en`, `pin_verificacion_intentos`) VALUES
('1213434431', 'Dilan Santiago Pinzón Galeano', 'prueba2', '$2y$10$ISPbmzY13EZF.7lmTjYrfuEbiUvHJDdFfhSVT6mStB21me077pyUO', 'asesor', 'tecnologia@onixbpo.com', 'activo', '2026-04-09 15:29:03', NULL, NULL, 0),
('23122312', 'camilo calderon', 'admin1', '$2y$10$WIn0px9Qn19dchVgJYBaP.rEb6Vpw/mOZhnmC2Yk6wWw/qHLeB5QK', 'administrador', 'admin@gmailcom', 'activo', '2026-03-25 20:03:24', NULL, NULL, 0),
('24141424', 'Armando benedetti', 'coord1', '$2y$10$AoXZT0SjhB8hWq/7DAT0T.xVhAK5CDTnwCaJD8xPlqz7/IhwHF9tS', 'coordinador', 'coord@gmail.com', 'activo', '2026-03-25 20:56:22', NULL, NULL, 0),
('3212132123', 'samara rodriguez', 'prueba1', '$2y$10$No1meqDv3.HmedHSQ2LWBeRchRw1GinJNHKJjatQ6paSXjMTz0H8e', 'asesor', 'rodriguez@gmail.com', 'activo', '2026-03-31 21:09:15', NULL, NULL, 0),
('554433221', 'Juana Vargas', 'prueba3', '$2y$10$Hn/PvByLe6xUiV81YlrGYeQULIR/tubAQh2qZUJW1qAhChgnPihPG', 'asesor', 'alejoguaquez@hotmail.com', 'inactivo', '2026-04-09 15:39:02', NULL, NULL, 0),
('7766776', 'karin leon', 'prueba4', '$2y$10$nTue4UTRV78hnsLMf5os/upTgqywqkzo1OTVO4p9LU9YTjKPElmwW', 'asesor', 'karin@gmail.com', 'activo', '2026-04-09 15:44:45', NULL, NULL, 0);

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
-- Indices de la tabla `modulo_completado`
--
ALTER TABLE `modulo_completado`
  ADD PRIMARY KEY (`id_modulo`,`cedula_asesor`),
  ADD KEY `fk_mod_compl_asesor` (`cedula_asesor`);

--
-- Indices de la tabla `modulo_intentos`
--
ALTER TABLE `modulo_intentos`
  ADD PRIMARY KEY (`id_intento_modulo`),
  ADD KEY `idx_mod_intento_hist` (`id_modulo`,`cedula_asesor`,`fecha_intento`),
  ADD KEY `fk_mod_intento_asesor` (`cedula_asesor`);

--
-- Indices de la tabla `modulo_opciones`
--
ALTER TABLE `modulo_opciones`
  ADD PRIMARY KEY (`id_opcion`),
  ADD UNIQUE KEY `uq_mod_opc_clave` (`id_pregunta_modulo`,`clave`),
  ADD KEY `idx_mod_opc_preg` (`id_pregunta_modulo`);

--
-- Indices de la tabla `modulo_preguntas`
--
ALTER TABLE `modulo_preguntas`
  ADD PRIMARY KEY (`id_pregunta_modulo`),
  ADD UNIQUE KEY `uq_mod_preg_orden` (`id_modulo`,`orden`),
  ADD KEY `idx_mod_preg_modulo` (`id_modulo`);

--
-- Indices de la tabla `modulo_preguntas_respuesta`
--
ALTER TABLE `modulo_preguntas_respuesta`
  ADD PRIMARY KEY (`id_pregunta_modulo`),
  ADD UNIQUE KEY `uq_mod_resp_opc` (`id_opcion_correcta`);

--
-- Indices de la tabla `modulo_quiz_config`
--
ALTER TABLE `modulo_quiz_config`
  ADD PRIMARY KEY (`id_modulo`);

--
-- Indices de la tabla `modulo_respuestas`
--
ALTER TABLE `modulo_respuestas`
  ADD PRIMARY KEY (`id_intento_modulo`,`id_pregunta_modulo`),
  ADD KEY `idx_mod_resp_preg` (`id_pregunta_modulo`),
  ADD KEY `idx_mod_resp_opc` (`id_opcion_elegida`);

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
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id_cursos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cursos_modulos`
--
ALTER TABLE `cursos_modulos`
  MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `intentos_evaluacion`
--
ALTER TABLE `intentos_evaluacion`
  MODIFY `id_intento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lecciones`
--
ALTER TABLE `lecciones`
  MODIFY `id_leccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `modulo_intentos`
--
ALTER TABLE `modulo_intentos`
  MODIFY `id_intento_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `modulo_opciones`
--
ALTER TABLE `modulo_opciones`
  MODIFY `id_opcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `modulo_preguntas`
--
ALTER TABLE `modulo_preguntas`
  MODIFY `id_pregunta_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  ADD CONSTRAINT `fk_curso_lecciones` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_cursos`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_modulo_leccion` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `leccion_completado`
--
ALTER TABLE `leccion_completado`
  ADD CONSTRAINT `fk_lec_comp_asesor` FOREIGN KEY (`cedula_asesor`) REFERENCES `usuarios` (`cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lec_comp_leccion` FOREIGN KEY (`id_leccion`) REFERENCES `lecciones` (`id_leccion`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_completado`
--
ALTER TABLE `modulo_completado`
  ADD CONSTRAINT `fk_mod_compl_asesor` FOREIGN KEY (`cedula_asesor`) REFERENCES `usuarios` (`cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_compl_mod` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_intentos`
--
ALTER TABLE `modulo_intentos`
  ADD CONSTRAINT `fk_mod_intento_asesor` FOREIGN KEY (`cedula_asesor`) REFERENCES `usuarios` (`cedula`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_intento_mod` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_opciones`
--
ALTER TABLE `modulo_opciones`
  ADD CONSTRAINT `fk_mod_opc_preg` FOREIGN KEY (`id_pregunta_modulo`) REFERENCES `modulo_preguntas` (`id_pregunta_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_preguntas`
--
ALTER TABLE `modulo_preguntas`
  ADD CONSTRAINT `fk_mod_preg_mod` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_preguntas_respuesta`
--
ALTER TABLE `modulo_preguntas_respuesta`
  ADD CONSTRAINT `fk_mod_resp_opc` FOREIGN KEY (`id_opcion_correcta`) REFERENCES `modulo_opciones` (`id_opcion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_resp_preg` FOREIGN KEY (`id_pregunta_modulo`) REFERENCES `modulo_preguntas` (`id_pregunta_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_quiz_config`
--
ALTER TABLE `modulo_quiz_config`
  ADD CONSTRAINT `fk_modulo_quiz_config_mod` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `modulo_respuestas`
--
ALTER TABLE `modulo_respuestas`
  ADD CONSTRAINT `fk_mod_resp_intento` FOREIGN KEY (`id_intento_modulo`) REFERENCES `modulo_intentos` (`id_intento_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_resp_opc2` FOREIGN KEY (`id_opcion_elegida`) REFERENCES `modulo_opciones` (`id_opcion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_resp_preg2` FOREIGN KEY (`id_pregunta_modulo`) REFERENCES `modulo_preguntas` (`id_pregunta_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `preguntas_evaluacion`
--
ALTER TABLE `preguntas_evaluacion`
  ADD CONSTRAINT `fk_curso_preguntas` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_cursos`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
