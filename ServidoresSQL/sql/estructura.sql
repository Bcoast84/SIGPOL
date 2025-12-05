CREATE DATABASE SIGPOL;
GO

USE SIGPOL;
GO

-- Tabla Cuartel
CREATE TABLE Cuartel (
    id_cuartel INT PRIMARY KEY IDENTITY(1,1),
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(200)
);

-- Tabla Persona (modificada sin campo 'direccion')
CREATE TABLE Persona (
    id_persona INT PRIMARY KEY IDENTITY(1,1),
    nombre VARCHAR(100) NOT NULL,
    apellido1 VARCHAR(100) NOT NULL,
    apellido2 VARCHAR(100),
    dni VARCHAR(20) UNIQUE NOT NULL,
    fecha_nacimiento DATE,
    email VARCHAR(100),
    telefono VARCHAR(20),
    nacionalidad VARCHAR(50),
    activo BIT DEFAULT 1
);

-- Tabla Direccion (mantenida y vinculada a Persona)
CREATE TABLE Direccion (
    id_direccion INT PRIMARY KEY IDENTITY(1,1),
    id_persona INT NOT NULL,
    calle VARCHAR(150),
    ciudad VARCHAR(100),
    provincia VARCHAR(100),
    codigo_postal VARCHAR(10),
    tipo VARCHAR(20),
    FOREIGN KEY (id_persona) REFERENCES Persona(id_persona)
);

-- Tabla Vehiculo
CREATE TABLE Vehiculo (
    id_vehiculo INT PRIMARY KEY IDENTITY(1,1),
    id_persona INT NOT NULL,
    matricula VARCHAR(15),
    marca VARCHAR(50),
    modelo VARCHAR(50),
    color VARCHAR(30),
    FOREIGN KEY (id_persona) REFERENCES Persona(id_persona)
);

-- Tabla Relacion entre personas
CREATE TABLE Relacion (
    id_relacion INT PRIMARY KEY IDENTITY(1,1),
    id_persona_origen INT NOT NULL,
    id_persona_destino INT NOT NULL,
    tipo_relacion VARCHAR(50),
    FOREIGN KEY (id_persona_origen) REFERENCES Persona(id_persona),
    FOREIGN KEY (id_persona_destino) REFERENCES Persona(id_persona)
);

-- Tabla Usuario
CREATE TABLE Usuario (
    id_usuario INT PRIMARY KEY IDENTITY(1,1),
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena_hash VARCHAR(255) NOT NULL,
    rol VARCHAR(20) CHECK (rol IN ('Guardia', 'Sargento', 'Comandante', 'Administrador')),
    id_cuartel INT NOT NULL,
    id_persona INT NOT NULL,
    FOREIGN KEY (id_cuartel) REFERENCES Cuartel(id_cuartel),
    FOREIGN KEY (id_persona) REFERENCES Persona(id_persona)
);

-- Tabla Denuncia
CREATE TABLE Denuncia (
    id_denuncia INT PRIMARY KEY IDENTITY(1,1),
    fecha DATETIME NOT NULL,
    descripcion NVARCHAR(MAX),
    id_usuario INT NOT NULL,
    id_cuartel INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario),
    FOREIGN KEY (id_cuartel) REFERENCES Cuartel(id_cuartel)
);

-- Tabla PersonaDenuncia
CREATE TABLE PersonaDenuncia (
    id_persona_denuncia INT PRIMARY KEY IDENTITY(1,1),
    id_denuncia INT NOT NULL,
    id_persona INT NOT NULL,
    rol_en_denuncia VARCHAR(50),
    FOREIGN KEY (id_denuncia) REFERENCES Denuncia(id_denuncia),
    FOREIGN KEY (id_persona) REFERENCES Persona(id_persona)
);

-- Tabla Informe
CREATE TABLE Informe (
    id_informe INT PRIMARY KEY IDENTITY(1,1),
    fecha DATETIME NOT NULL,
    contenido NVARCHAR(MAX),
    id_usuario INT NOT NULL,
    id_denuncia INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario),
    FOREIGN KEY (id_denuncia) REFERENCES Denuncia(id_denuncia)
);

-- Tabla RegistroAcceso
CREATE TABLE RegistroAcceso (
    id_registro INT PRIMARY KEY IDENTITY(1,1),
    id_usuario INT NOT NULL,
    accion VARCHAR(100),
    entidad VARCHAR(50),
    fecha DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario)
);

--Tabla SesionesPHP
CREATE TABLE [dbo].[SesionesPHP](
    [id_session]  NOT NULL,
    [data_session] [nvarchar](max) NULL,
    [last_update] [datetime] NULL,
    CONSTRAINT PK_SesionesPHP PRIMARY KEY (id_session)
);
