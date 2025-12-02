<?php
// gestion_usuarios.php: Administración de usuarios en SIGPOL.
// Solo accesible para rol 'admin'.

// Incluimos control de desiones y conexión.
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verificamos que el usuario esté autenticado
if (!isset($_SESSION["nombre_usuario"]) || !isset($_SESSION["rol"])) {
    header("Location: login.php");
    exit;
}

// Control de acceso por rol: solo admin y jefe pueden entrar
if ($_SESSION["rol"] !== 'admin' && $_SESSION["rol"] !== 'jefe') {
    header("Location: acceso_denegado.php");
    exit;
}

$usuario = $_SESSION["nombre_usuario"];
$rol     = $_SESSION["rol"];

// Solo admin puede acceder a la gestión de usuarios
if ($rol !== "admin") {
    die("Acceso denegado. Solo administradores pueden gestionar usuarios.");
}

$mensaje = '';

// -------------------------
// CREAR USUARIO
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $password       = trim($_POST['password'] ?? '');
    $rol_nuevo      = trim($_POST['rol_nuevo'] ?? '');
    $dni            = trim($_POST['dni'] ?? '');
    $cuartel_nom    = trim($_POST['cuartel'] ?? '');

    if ($nombre_usuario && $password && $rol_nuevo && $dni && $cuartel_nom) {
        // Buscar persona por DNI
        $stmt = $pdo->prepare("SELECT id_persona FROM Persona WHERE dni = ?");
        $stmt->execute([$dni]);
        $persona = $stmt->fetch();

        if (!$persona) {
            $mensaje = "No existe ninguna persona con ese DNI. Debe crearse primero en el módulo de Personas.";
        } else {
            $id_persona = $persona['id_persona'];

            // Buscar cuartel
            $stmt = $pdo->prepare("SELECT id_cuartel FROM Cuartel WHERE nombre = ?");
            $stmt->execute([$cuartel_nom]);
            $cua = $stmt->fetch();

            if (!$cua) {
                $id_cuartel = substr(uniqid("CUA"), 0, 10);
                $stmt = $pdo->prepare("INSERT INTO Cuartel (id_cuartel, nombre, id_direccion) VALUES (?, ?, ?)");
                $stmt->execute([$id_cuartel, $cuartel_nom, 'DIRDEFAULT']);
            } else {
                $id_cuartel = $cua['id_cuartel'];
            }

            // Insertar usuario con contraseña hasheada
            $stmt = $pdo->prepare("INSERT INTO Usuario (nombre_usuario, contrasena_hash, rol, id_cuartel, id_persona) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $nombre_usuario,
                password_hash($password, PASSWORD_DEFAULT),
                $rol_nuevo,
                $id_cuartel,
                $id_persona
            ]);

            $mensaje = "Usuario creado correctamente.";
        }
    } else {
        $mensaje = "Completa todos los campos para crear un usuario.";
    }
}

// -------------------------
// ELIMINAR USUARIO
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $id_usuario = $_POST['id_usuario'] ?? '';
    if ($id_usuario) {
        $stmt = $pdo->prepare("DELETE FROM Usuario WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
        $mensaje = "Usuario eliminado correctamente.";
    }
}

// -------------------------
// LISTADO DE USUARIOS
// -------------------------
$stmt = $pdo->query("SELECT u.id_usuario, u.nombre_usuario, u.rol, p.dni, c.nombre AS cuartel
                     FROM Usuario u
                     LEFT JOIN Persona p ON u.id_persona = p.id_persona
                     LEFT JOIN Cuartel c ON u.id_cuartel = c.id_cuartel
                     ORDER BY u.nombre_usuario ASC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Gestión de Usuarios - SIGPOL</title>
    <style>
        /* === Estilos generales === */
        body {
            margin: 0;
            padding: 0;
            background-color: #0b3d0b;
            font-family: Arial, sans-serif;
        }
        
        .header {
            display: flex;
            align-items: center;
            background-color: white;
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }
        
        .header img {
            height: 80px;
            margin-right: 20px;
        }
        
        .user-info {
            margin-left: auto;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            font-weight: bold;
            color: #003366;
            text-align: right;
        }
        
        .user-info span.role {
            font-size: 14px;
            font-weight: normal;
            color: #666;
        }
        
        .user-info i {
            font-style: normal;
            margin-right: 8px;
            font-size: 20px;
        }

        .dashboard {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 40px;
        }
        
        .container {
            width: 100%;
            max-width: 500px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        
        h2, h3 {
            color: #003366;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 12px;
        }
        
        .form-group label {
            font-weight: bold;
            margin-bottom: 6px;
            color: #2e7d32;
        }
        
        .form-group input,
        .form-group select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 8px;
        }
        
        button {
            background-color: #2e7d32;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        
        button:hover {
            background-color: #1b5e20;
            transform: translateY(-1px);
        }

        .mensaje {
            text-align: center;
            font-weight: bold;
            color: #003366;
            margin: 10px 0 0;
        }

        table {
            border-collapse: collapse;
            margin-top: 20px;
            width: 100%;
        }
        
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        
        th {
            background-color: #003366;
            color: white;
        }

        .back {
            margin-top: 20px;
            text-align: center;
        }
        
        .back a {
            background-color: #2e7d32;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
        
        .back a:hover {
            background-color: #1b5e20;
        }
    </style>
</head>
<body>
    <!-- Cabecera con logo y usuario -->
    <div class="header">
        <img src="imagenes/logo.jpeg" alt="Logo SIGPOL">
        <div class="user-info">
            <span>¡Bienvenido, <i>👤</i> <?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?>!</span>
            <span class="role">Rol: <?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="dashboard">
        <!-- Formulario de creación de usuario -->
        <div class="container">
            <h2>Crear Usuario</h2>

            <?php if ($mensaje): ?>
                <div class="mensaje"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="nombre_usuario">Nombre de Usuario:</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" />
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" />
                </div>

                <div class="form-group">
                    <label for="rol_nuevo">Rol:</label>
                    <select id="rol_nuevo" name="rol_nuevo">
                        <option value="guardia">Guardia</option>
                        <option value="jefe">Jefe</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="dni">DNI de la Persona:</label>
                    <input type="text" id="dni" name="dni" />
                </div>

                <div class="form-group">
                    <label for="cuartel">Cuartel:</label>
                    <input type="text" id="cuartel" name="cuartel" />
                </div>

                <div class="buttons">
                    <button type="submit" name="crear">Crear Usuario</button>
                </div>
            </form>
        </div>

        <!-- Listado de usuarios existentes -->
        <div class="container resultados">
            <h3>Usuarios existentes</h3>
            <table>
                <tr>
                    <th>Nombre de Usuario</th>
                    <th>Rol</th>
                    <th>DNI</th>
                    <th>Cuartel</th>
                    <th>Acción</th>
                </tr>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['nombre_usuario'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($u['rol'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($u['dni'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($u['cuartel'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <!-- Botón para eliminar usuario -->
                            <form method="post" style="margin:0;">
                                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($u['id_usuario'], ENT_QUOTES, 'UTF-8') ?>" />
                                <button type="submit" name="eliminar" style="background-color:#d32f2f;">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Enlace de retorno al panel principal -->
        <div class="back">
            <a href="dashboard.php">Volver al Panel</a>
        </div>
    </div>
</body>
</html>