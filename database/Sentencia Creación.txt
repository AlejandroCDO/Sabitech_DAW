-- =====================================================
-- SABITECH WEB - Base de datos (versión corregida)
-- Coincide con el diagrama "Modelo de Datos - SABITECH WEB (versión corregida)"
-- Motor: MySQL 8 / MariaDB 10.4+ (los CHECK se aplican de verdad)
-- Úsalo sobre una base de datos VACÍA. Si ya tienes datos, usa
-- migracion_desde_sql_actual.sql
-- =====================================================
CREATE DATABASE IF NOT EXISTS sabitech_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE sabitech_db;

-- =====================================================
-- 1. roles
-- =====================================================
CREATE TABLE roles (
  id      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre  VARCHAR(50)     NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 2. ubicaciones
-- =====================================================
CREATE TABLE ubicaciones (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  provincia      VARCHAR(100)    NOT NULL,
  municipio      VARCHAR(100)    NOT NULL,
  calle          VARCHAR(100)    NOT NULL,
  codigo_postal  VARCHAR(10)     NOT NULL,
  numero         VARCHAR(10)     NOT NULL,
  puerta         VARCHAR(10)     NULL DEFAULT NULL,
  nombre         VARCHAR(50)     NOT NULL,
  created_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ubicaciones_provincia_municipio (provincia, municipio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 3. ponentes
-- =====================================================
CREATE TABLE ponentes (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre        VARCHAR(100)    NOT NULL,
  biografia     TEXT            NULL,
  foto_url      VARCHAR(255)    NULL DEFAULT NULL,
  especialidad  VARCHAR(100)    NULL DEFAULT NULL,
  linkedin_url  VARCHAR(255)    NULL DEFAULT NULL,
  created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 4. usuarios  (FK -> roles)
-- =====================================================
CREATE TABLE usuarios (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  rol_id            BIGINT UNSIGNED NOT NULL,
  nombre            VARCHAR(100)    NOT NULL,
  email             VARCHAR(150)    NOT NULL,
  telefono          VARCHAR(15)     NOT NULL,
  fecha_nacimiento  DATE            NOT NULL,
  password          VARCHAR(255)    NOT NULL,
  created_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email),
  KEY idx_usuarios_rol_id (rol_id),
  CONSTRAINT fk_usuarios_rol
    FOREIGN KEY (rol_id) REFERENCES roles (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 5. charlas  (FK -> ponentes, ubicaciones)
--    Relaciones: ponente 1-N charlas, ubicacion 1-N charlas
-- =====================================================
CREATE TABLE charlas (
  id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ponente_id       BIGINT UNSIGNED NOT NULL,
  ubicacion_id     BIGINT UNSIGNED NOT NULL,
  titulo           VARCHAR(100)    NOT NULL,
  descripcion      TEXT            NOT NULL,
  inicio           DATETIME        NOT NULL,
  fin              DATETIME        NOT NULL,
  aforo            INT             NOT NULL,
  plazas_ocupadas  INT             NOT NULL DEFAULT 0,
  cancelada        TINYINT(1)      NOT NULL DEFAULT 0,
  created_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_charlas_ponente_id (ponente_id),
  KEY idx_charlas_ubicacion_id (ubicacion_id),
  KEY idx_charlas_inicio (inicio),
  CONSTRAINT chk_charlas_aforo         CHECK (aforo > 0),
  CONSTRAINT chk_charlas_plazas        CHECK (plazas_ocupadas >= 0 AND plazas_ocupadas <= aforo),
  CONSTRAINT chk_charlas_fechas        CHECK (fin > inicio),
  CONSTRAINT fk_charlas_ponente
    FOREIGN KEY (ponente_id) REFERENCES ponentes (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_charlas_ubicacion
    FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 6. reservas  (FK -> usuarios, charlas)
--    Una charla tiene N reservas; cada reserva es de 1 charla.
--    UNIQUE (usuario_id, charla_id): nadie reserva dos veces la misma charla.
-- =====================================================
CREATE TABLE reservas (
  id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id          BIGINT UNSIGNED NOT NULL,
  charla_id           BIGINT UNSIGNED NOT NULL,
  estado              ENUM('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente',
  qr_secret           VARCHAR(64)     NOT NULL,
  email_intentos      INT             NOT NULL DEFAULT 0,
  email_ultimo_envio  TIMESTAMP       NULL DEFAULT NULL,
  created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_reservas_usuario_charla (usuario_id, charla_id),
  UNIQUE KEY uq_reservas_qr_secret (qr_secret),
  KEY idx_reservas_charla_estado (charla_id, estado),
  CONSTRAINT chk_reservas_intentos CHECK (email_intentos >= 0),
  CONSTRAINT fk_reservas_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_reservas_charla
    FOREIGN KEY (charla_id) REFERENCES charlas (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 7. asistencias  (FK -> reservas, usuarios[validador])
--    Relación reservas 1 - 0..1 asistencias (UNIQUE reserva_id).
--    La charla y el asistente se obtienen a través de la reserva,
--    por eso NO hay usuario_id ni charla_id aquí.
-- =====================================================
CREATE TABLE asistencias (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  reserva_id    BIGINT UNSIGNED NOT NULL,
  validado_por  BIGINT UNSIGNED NOT NULL,
  qr_token      VARCHAR(128)    NULL DEFAULT NULL,
  validada_en   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_asistencias_reserva_id (reserva_id),
  UNIQUE KEY uq_asistencias_qr_token (qr_token),
  KEY idx_asistencias_validado_por (validado_por),
  CONSTRAINT fk_asistencias_reserva
    FOREIGN KEY (reserva_id) REFERENCES reservas (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_asistencias_validador
    FOREIGN KEY (validado_por) REFERENCES usuarios (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;