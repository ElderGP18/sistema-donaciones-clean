-- ============================================================
-- DONATU - Plataforma de Gestión de Donaciones
-- Base de datos MySQL
-- Sprint 1 - Estructura base
-- ============================================================

CREATE DATABASE IF NOT EXISTS donatu_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE donatu_db;

-- ============================================================
-- TABLA: usuarios
-- ============================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario   INT AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL,
    correo       VARCHAR(150) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    rol          ENUM('admin', 'encargado') NOT NULL DEFAULT 'encargado',
    activo       TINYINT(1) NOT NULL DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: campanas
-- ============================================================
CREATE TABLE IF NOT EXISTS campanas (
    id_campana   INT AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(150) NOT NULL,
    descripcion  TEXT,
    meta         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    estado       ENUM('activa', 'finalizada', 'pausada') NOT NULL DEFAULT 'activa',
    fecha_inicio DATE NOT NULL,
    fecha_fin    DATE,
    id_usuario   INT NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: donantes
-- ============================================================
CREATE TABLE IF NOT EXISTS donantes (
    id_donante   INT AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL,
    correo       VARCHAR(150),
    telefono     VARCHAR(20),
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: donaciones
-- ============================================================
CREATE TABLE IF NOT EXISTS donaciones (
    id_donacion  INT AUTO_INCREMENT PRIMARY KEY,
    fecha        DATE NOT NULL,
    monto        DECIMAL(12,2) NOT NULL,
    id_campana   INT NOT NULL,
    id_donante   INT NOT NULL,
    id_usuario   INT NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_campana)  REFERENCES campanas(id_campana),
    FOREIGN KEY (id_donante)  REFERENCES donantes(id_donante),
    FOREIGN KEY (id_usuario)  REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: egresos
-- ============================================================
CREATE TABLE IF NOT EXISTS egresos (
    id_egreso    INT AUTO_INCREMENT PRIMARY KEY,
    fecha        DATE NOT NULL,
    concepto     VARCHAR(200) NOT NULL,
    monto        DECIMAL(12,2) NOT NULL,
    id_campana   INT NOT NULL,
    id_usuario   INT NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_campana)  REFERENCES campanas(id_campana),
    FOREIGN KEY (id_usuario)  REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- ============================================================
-- DATOS INICIALES
-- Admin: admin@donatu.com / password: Admin123!
-- ============================================================
INSERT INTO usuarios (nombre, correo, password, rol) VALUES
('Administrador', 'admin@donatu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Nota: el hash corresponde a la contraseña "password" de Laravel/bcrypt ejemplo
-- Para producción generar con: password_hash('Admin123!', PASSWORD_BCRYPT)
