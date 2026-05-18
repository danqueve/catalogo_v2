-- Catálogo Web Autogestionable
-- MySQL 8 / utf8mb4

CREATE DATABASE IF NOT EXISTS catalogo
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE catalogo;

CREATE TABLE IF NOT EXISTS usuarios (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario       VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  nombre        VARCHAR(100) NOT NULL,
  creado_en     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contraseña por defecto: admin123
INSERT INTO usuarios (usuario, password_hash, nombre)
VALUES ('admin', '$2y$10$FWC7GDIwvy24H4Flrv8UxOQDlSHI9eAKvoZK.x9WcV/Lm14q0vL4e', 'Administrador')
ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS categorias (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre    VARCHAR(80)  NOT NULL UNIQUE,
  slug      VARCHAR(100) NOT NULL UNIQUE,
  imagen    VARCHAR(255) DEFAULT NULL,
  activo    TINYINT(1)   NOT NULL DEFAULT 1,
  creado_en DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_cat_creado (creado_en DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS articulos (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id     INT UNSIGNED  NOT NULL,
  nombre           VARCHAR(150)  NOT NULL,
  descripcion      TEXT          DEFAULT NULL,
  imagen           VARCHAR(255)  NOT NULL,
  precio_contado   DECIMAL(12,2) DEFAULT NULL,
  cuotas_sem_cant  SMALLINT UNSIGNED DEFAULT NULL,
  cuotas_sem_monto DECIMAL(12,2)     DEFAULT NULL,
  cuotas_mes_cant  SMALLINT UNSIGNED DEFAULT NULL,
  cuotas_mes_monto DECIMAL(12,2)     DEFAULT NULL,
  activo           TINYINT(1)    NOT NULL DEFAULT 1,
  creado_en        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_art_cat FOREIGN KEY (categoria_id)
    REFERENCES categorias(id) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_art_cat    (categoria_id),
  INDEX idx_art_creado (creado_en DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
