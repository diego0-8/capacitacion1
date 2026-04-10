-- Agrega tabla para insignias otorgadas a asesores.
-- Ejecutar en la BD `capacitacion1`.

CREATE TABLE IF NOT EXISTS `insignias_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cedula_asesor` varchar(10) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `otorgada_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `metadata` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_insignia` (`cedula_asesor`, `id_curso`, `tipo`),
  KEY `idx_curso` (`id_curso`),
  CONSTRAINT `fk_insignias_usuario_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_cursos`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

