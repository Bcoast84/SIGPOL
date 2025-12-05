CREATE DATABASE SIGPOL;  
GO  
  
USE SIGPOL;  
GO  

-- ========================================
-- TABLA: Cuartel (sin dirección)
-- ========================================
CREATE TABLE Cuartel (  
    id_cuartel INT PRIMARY KEY IDENTITY(1,1),  
    nombre VARCHAR(100) NOT NULL,  
    telefono VARCHAR(20),
    email VARCHAR(100),
    fecha_creacion DATETIME DEFAULT GETDATE()
);  

-- ========================================
-- TABLA: Persona (sin dirección integrada)
-- ========================================
CREATE TABLE Persona (  
    id_persona INT PRIMARY KEY IDENTITY(1,1),  
    nombre VARCHAR(100) NOT NULL,  
    apellido1 VARCHAR(100) NOT NULL,  
    apellido2 VARCHAR(100),  
    dni VARCHAR(20) UNIQUE NOT NULL,  
    fecha_nacimiento DATE,  
    email VARCHAR(100),  
    telefono VARCHAR(20),  
    nacionalidad VARCHAR(50) DEFAULT 'Española',
    activo BIT DEFAULT 1,
    fecha_registro DATETIME DEFAULT GETDATE()
);  

-- ========================================
-- TABLA: Direccion (vinculada a Persona Y Cuartel)
-- ========================================
CREATE TABLE Direccion (  
    id_direccion INT PRIMARY KEY IDENTITY(1,1),  
    id_persona INT NULL,  -- NULL si es dirección de cuartel
    id_cuartel INT NULL,  -- NULL si es dirección de persona
    calle VARCHAR(150),  
    ciudad VARCHAR(100),  
    provincia VARCHAR(100),  
    codigo_postal VARCHAR(10),  
    tipo VARCHAR(20) CHECK (tipo IN ('Habitual', 'Trabajo', 'Temporal', 'Cuartel', 'Otro')),
    principal BIT DEFAULT 0,  -- Indica si es la dirección principal
    FOREIGN KEY (id_persona) REFERENCES Persona(id_persona) ON DELETE CASCADE,
    FOREIGN KEY (id_cuartel) REFERENCES Cuartel(id_cuartel) ON DELETE CASCADE,
    CONSTRAINT CHK_Direccion_Entidad CHECK (
        (id_persona IS NOT NULL AND id_cuartel IS NULL) OR 
        (id_persona IS NULL AND id_cuartel IS NOT NULL)
    )  -- Asegura que sea de persona O de cuartel, no ambos
);  

-- ========================================
-- TABLA: Vehiculo (mejorada)
-- ========================================
CREATE TABLE Vehiculo (  
    id_vehiculo INT PRIMARY KEY IDENTITY(1,1),  
    id_persona INT NOT NULL,  
    matricula VARCHAR(15) UNIQUE NOT NULL,  
    marca VARCHAR(50),  
    modelo VARCHAR(50),  
    color VARCHAR(30),
    año_fabricacion INT,
    tipo_vehiculo VARCHAR(30) CHECK (tipo_vehiculo IN ('Turismo', 'Motocicleta', 'Furgoneta', 'Camión', 'Otro')),
    fecha_registro DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (id_persona) REFERENCES Persona(id_persona) ON DELETE CASCADE
);  

-- ========================================
-- TABLA: Usuario (mejorada con seguridad)
-- ========================================
CREATE TABLE Usuario (  
    id_usuario INT PRIMARY KEY IDENTITY(1,1),  
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,  
    contrasena_hash VARCHAR(255) NOT NULL,  
    rol VARCHAR(20) CHECK (rol IN ('Guardia', 'Sargento', 'Comandante', 'Administrador')) NOT NULL,  
    id_cuartel INT NOT NULL,  
    id_persona INT NOT NULL UNIQUE,  -- Un usuario solo puede tener una persona asociada
    ultimo_acceso DATETIME,
    intentos_fallidos INT DEFAULT 0,
    bloqueado BIT DEFAULT 0,
    fecha_creacion DATETIME DEFAULT GETDATE(),
    activo BIT DEFAULT 1,
    FOREIGN KEY (id_cuartel) REFERENCES Cuartel(id_cuartel),  
    FOREIGN KEY (id_persona) REFERENCES Persona(id_persona)
);  

-- ========================================
-- TABLA: Denuncia (mejorada con estado)
-- ========================================
CREATE TABLE Denuncia (  
    id_denuncia INT PRIMARY KEY IDENTITY(1,1),  
    numero_expediente VARCHAR(50) UNIQUE NOT NULL,  -- Número único de expediente
    fecha DATETIME NOT NULL DEFAULT GETDATE(),  
    descripcion NVARCHAR(MAX),
    estado VARCHAR(20) CHECK (estado IN ('Abierta', 'En Proceso', 'Cerrada', 'Archivada')) DEFAULT 'Abierta',
    prioridad VARCHAR(15) CHECK (prioridad IN ('Baja', 'Media', 'Alta', 'Urgente')) DEFAULT 'Media',
    id_usuario INT NOT NULL,  -- Usuario que tramita
    id_cuartel INT NOT NULL,  -- Cuartel donde se registra
    fecha_cierre DATETIME,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario),  
    FOREIGN KEY (id_cuartel) REFERENCES Cuartel(id_cuartel)
);  

-- ========================================
-- TABLA: PersonaDenuncia (mejorada)
-- ========================================
CREATE TABLE PersonaDenuncia (  
    id_persona_denuncia INT PRIMARY KEY IDENTITY(1,1),  
    id_denuncia INT NOT NULL,  
    id_persona INT NOT NULL,  
    rol_en_denuncia VARCHAR(50) CHECK (rol_en_denuncia IN ('Denunciante', 'Denunciado', 'Testigo', 'Víctima', 'Implicado')) NOT NULL,
    declaracion NVARCHAR(MAX),
    fecha_registro DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (id_denuncia) REFERENCES Denuncia(id_denuncia) ON DELETE CASCADE,  
    FOREIGN KEY (id_persona) REFERENCES Persona(id_persona),
    CONSTRAINT UQ_PersonaDenuncia UNIQUE (id_denuncia, id_persona, rol_en_denuncia)  
);  

-- ========================================
-- TABLA: Informe (mejorada)
-- ========================================
CREATE TABLE Informe (  
    id_informe INT PRIMARY KEY IDENTITY(1,1),  
    numero_informe VARCHAR(50) UNIQUE NOT NULL,
    fecha DATETIME NOT NULL DEFAULT GETDATE(),  
    contenido NVARCHAR(MAX),
    tipo_informe VARCHAR(30) CHECK (tipo_informe IN ('Inicial', 'Seguimiento', 'Final', 'Complementario')),
    id_usuario INT NOT NULL,  
    id_denuncia INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario),  
    FOREIGN KEY (id_denuncia) REFERENCES Denuncia(id_denuncia) ON DELETE CASCADE
);

-- ========================================
-- TABLA: Auditoria (CRÍTICA)
-- ========================================
CREATE TABLE Auditoria (
    id_auditoria INT PRIMARY KEY IDENTITY(1,1),
    tabla_afectada VARCHAR(50) NOT NULL,
    operacion VARCHAR(10) CHECK (operacion IN ('INSERT', 'UPDATE', 'DELETE')) NOT NULL,
    id_usuario INT,
    id_registro INT,  -- ID del registro afectado
    fecha DATETIME DEFAULT GETDATE(),
    datos_anteriores NVARCHAR(MAX),
    datos_nuevos NVARCHAR(MAX),
    ip_origen VARCHAR(45),  -- IPv4 o IPv6
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario)
);

-- ========================================
-- TABLA: SesionesPHP (almacenar sesiones PHP)
-- ========================================
CREATE TABLE [dbo].[SesionesPHP](
    [id_session] [int] IDENTITY(1,1) NOT NULL, 
    [data_session] [nvarchar](max) NULL,
    [last_update] [datetime] NULL,
    CONSTRAINT PK_SesionesPHP PRIMARY KEY (id_session)
);

-- ========================================
-- VISTAS para consultas frecuentes
-- ========================================

-- ========================================
-- Vista: Usuarios con información completa
-- ========================================
CREATE VIEW vw_UsuariosCompletos AS
SELECT 
    u.id_usuario,
    u.nombre_usuario,
    u.rol,
    p.nombre,
    p.apellido1,
    p.apellido2,
    p.dni,
    p.telefono,
    p.email,
    c.nombre AS cuartel,
    d.ciudad AS ciudad_cuartel,
    u.activo,
    u.ultimo_acceso
FROM Usuario u
INNER JOIN Persona p ON u.id_persona = p.id_persona
INNER JOIN Cuartel c ON u.id_cuartel = c.id_cuartel
LEFT JOIN Direccion d ON c.id_cuartel = d.id_cuartel AND d.tipo = 'Cuartel';
GO
-- ========================================
-- VISTA: Cuarteles con dirección
-- ========================================
CREATE VIEW vw_CuartelesCompletos AS
SELECT 
    c.id_cuartel,
    c.nombre,
    c.telefono,
    c.email,
    d.calle,
    d.ciudad,
    d.provincia,
    d.codigo_postal
FROM Cuartel c
LEFT JOIN Direccion d ON c.id_cuartel = d.id_cuartel AND d.tipo = 'Cuartel';
GO

-- ========================================
-- VISTA: Vehículos con DNI y Ciudad del Propietario
-- ========================================
CREATE VIEW vw_VehiculosConPropietario AS
SELECT 
    v.id_vehiculo,
    v.matricula,
    v.marca,
    v.modelo,
    v.color,
    v.año_fabricacion,
    v.tipo_vehiculo,
    v.fecha_registro,
    p.dni,
    d.ciudad
FROM Vehiculo v
INNER JOIN Persona p ON v.id_persona = p.id_persona
LEFT JOIN Direccion d ON p.id_persona = d.id_persona AND d.principal = 1;
GO