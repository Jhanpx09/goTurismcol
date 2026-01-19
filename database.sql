CREATE DATABASE IF NOT EXISTS umb_viajes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE umb_viajes;

CREATE TABLE IF NOT EXISTS usuario (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  correo VARCHAR(190) NOT NULL UNIQUE,
  contrasena_hash VARCHAR(255) NOT NULL,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rol (
  id_rol INT AUTO_INCREMENT PRIMARY KEY,
  nombre_rol VARCHAR(60) NOT NULL UNIQUE,
  descripcion VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usuario_rol (
  id_usuario INT NOT NULL,
  id_rol INT NOT NULL,
  PRIMARY KEY (id_usuario, id_rol),
  CONSTRAINT fk_ur_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_ur_rol FOREIGN KEY (id_rol) REFERENCES rol(id_rol) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS destino (
  id_destino INT AUTO_INCREMENT PRIMARY KEY,
  pais VARCHAR(120) NOT NULL,
  ciudad VARCHAR(120) NULL,
  descripcion_general TEXT NULL,
  bandera_path VARCHAR(255) NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS destino_destacado (
  id_destacado INT AUTO_INCREMENT PRIMARY KEY,
  id_destino INT NOT NULL,
  titulo VARCHAR(180) NOT NULL,
  descripcion TEXT NOT NULL,
  imagen_path VARCHAR(255) NOT NULL,
  orden INT NOT NULL DEFAULT 0,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  CONSTRAINT fk_dest_destacado FOREIGN KEY (id_destino) REFERENCES destino(id_destino) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hero_slide (
  id_slide INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(180) NOT NULL,
  descripcion TEXT NOT NULL,
  enlace_texto VARCHAR(120) NOT NULL,
  enlace_url VARCHAR(255) NOT NULL,
  imagen_path VARCHAR(255) NOT NULL,
  orden INT NOT NULL DEFAULT 0,
  intervalo_segundos INT NOT NULL DEFAULT 7,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Si la tabla hero_slide ya existe, ejecutar una vez:
-- ALTER TABLE hero_slide ADD COLUMN intervalo_segundos INT NOT NULL DEFAULT 7;

CREATE TABLE IF NOT EXISTS requisito_viaje (
  id_requisito INT AUTO_INCREMENT PRIMARY KEY,
  id_destino INT NOT NULL,
  titulo_requisito VARCHAR(180) NOT NULL,
  descripcion_requisito TEXT NOT NULL,
  tipo_requisito VARCHAR(80) NOT NULL,
  fuente_oficial TEXT NULL,
  fecha_ultima_actualizacion DATE NOT NULL,
  creado_por INT NOT NULL,
  estado ENUM('vigente','no_vigente') NOT NULL DEFAULT 'vigente',
  CONSTRAINT fk_req_destino FOREIGN KEY (id_destino) REFERENCES destino(id_destino) ON DELETE CASCADE,
  CONSTRAINT fk_req_creador FOREIGN KEY (creado_por) REFERENCES usuario(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS actualizacion_requisito (
  id_actualizacion INT AUTO_INCREMENT PRIMARY KEY,
  id_requisito INT NOT NULL,
  descripcion_cambio TEXT NOT NULL,
  fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_por INT NOT NULL,
  CONSTRAINT fk_act_req FOREIGN KEY (id_requisito) REFERENCES requisito_viaje(id_requisito) ON DELETE CASCADE,
  CONSTRAINT fk_act_usuario FOREIGN KEY (actualizado_por) REFERENCES usuario(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS experiencia_viajero (
  id_experiencia INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_destino INT NOT NULL,
  titulo VARCHAR(180) NOT NULL,
  contenido TEXT NOT NULL,
  fecha_envio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  estado_moderacion ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  fecha_publicacion DATETIME NULL,
  CONSTRAINT fk_exp_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_exp_destino FOREIGN KEY (id_destino) REFERENCES destino(id_destino) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS moderacion_experiencia (
  id_moderacion INT AUTO_INCREMENT PRIMARY KEY,
  id_experiencia INT NOT NULL,
  id_admin INT NOT NULL,
  decision ENUM('aprobada','rechazada') NOT NULL,
  observacion VARCHAR(255) NULL,
  fecha_revision DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mod_exp FOREIGN KEY (id_experiencia) REFERENCES experiencia_viajero(id_experiencia) ON DELETE CASCADE,
  CONSTRAINT fk_mod_admin FOREIGN KEY (id_admin) REFERENCES usuario(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS aviso_actualizacion (
  id_aviso INT AUTO_INCREMENT PRIMARY KEY,
  id_destino INT NOT NULL,
  titulo_aviso VARCHAR(180) NOT NULL,
  detalle_aviso TEXT NOT NULL,
  fecha_publicacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  publicado_por INT NOT NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  CONSTRAINT fk_av_dest FOREIGN KEY (id_destino) REFERENCES destino(id_destino) ON DELETE CASCADE,
  CONSTRAINT fk_av_pub FOREIGN KEY (publicado_por) REFERENCES usuario(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT IGNORE INTO rol (id_rol, nombre_rol, descripcion) VALUES
  (1,'Viajero','Usuario que consulta información y publica experiencias'),
  (2,'Administrador','Usuario con permisos de administración y moderación');

-- Admin por defecto: admin@umb.local / Admin123!
INSERT IGNORE INTO usuario (id_usuario, correo, contrasena_hash, estado)
VALUES (1, 'admin@umb.local', '$2y$10$y7HjVxwH5vQ2Yz3U2JgC5eNw8oGq2lqA.3cYqj8KQm7pXwQk8bKZK', 'activo');

INSERT IGNORE INTO usuario_rol (id_usuario, id_rol) VALUES (1,2);

INSERT IGNORE INTO destino (id_destino, pais, ciudad, descripcion_general, estado) VALUES
  (1,'Colombia','Bogotá','Capital de Colombia. Punto frecuente de salida y llegada.','activo'),
  (2,'Estados Unidos','Nueva York','Destino internacional frecuente para turismo y negocios.','activo');

INSERT IGNORE INTO requisito_viaje (id_requisito, id_destino, titulo_requisito, descripcion_requisito, tipo_requisito, fuente_oficial, fecha_ultima_actualizacion, creado_por, estado)
VALUES
  (1,2,'Pasaporte vigente','El viajero debe contar con pasaporte vigente durante el ingreso y permanencia según el caso.','documental','Fuente oficial recomendada: sitios gubernamentales del país destino.','2025-01-01',1,'vigente');
