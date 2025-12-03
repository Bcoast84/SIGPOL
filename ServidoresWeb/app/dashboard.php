<?php
// dashboard.php: Panel principal del sistema SIGPOL.

// Incluimos control sesiones.
require_once __DIR__ . '/session.php';

// Verificamos si el usuario ha iniciado sesión.
// Si no existe 'nombre_usuario' o 'rol' en la sesión, se redirige al login.
if (!isset($_SESSION["nombre_usuario"]) || !isset($_SESSION["rol"])) {
    header("Location: login.php");
    exit;
}

// Variables de sesión.
$usuario = $_SESSION["nombre_usuario"];
$rol = $_SESSION["rol"];
$servidor = gethostname(); // Nombre del servidor actual (depuración)
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel SIGPOL</title>
  <style>
    /* Estilos generales */
    body { margin:0; padding:0; background-color:#0b3d0b; font-family:Arial,sans-serif; }

    /* Cabecera con logo y datos del usuario */
    .header { display:flex; align-items:center; background-color:white; padding:10px 20px; box-shadow:0 2px 5px rgba(0,0,0,0.3); }
    .header img { height:80px; margin-right:20px; }

    /* Información del usuario*/
    .user-info { margin-left:auto; display:flex; flex-direction:column; align-items:flex-end; font-weight:bold; color:#003366; text-align:right; }
    .user-info span.role { font-size:14px; font-weight:normal; color:#666; }
    .user-info i { font-style:normal; margin-right:8px; font-size:20px; }

    /* Contenedor principal del dashboard */
    .dashboard { display:flex; flex-direction:column; align-items:center; margin-top:60px; }

    /* Botones de navegación */
    .button { background-color:#2e7d32; color:white; border:none; padding:15px 40px; margin:15px; border-radius:10px; box-shadow:0 4px 8px rgba(0,0,0,0.3); font-size:18px; cursor:pointer; width:250px; text-align:center; transition:background-color 0.3s ease, transform 0.2s ease; text-decoration:none; }
    .button:hover { background-color:#1b5e20; transform:translateY(-2px); }

    /* Botón de logout */
    .logout { margin-top:30px; }
    .logout a { background-color:#d32f2f; padding:10px 20px; border-radius:6px; color:white; text-decoration:none; font-weight:bold; transition:background-color 0.3s ease; }
    .logout a:hover { background-color:#b71c1c; }

    /* Ajustes para móviles (no ajustados aún) */
    @media (max-width:600px){
      .header img{height:60px;}
      .dashboard{margin-top:30px;}
      .button{width:90%; max-width:300px;}
    }
  </style>
</head>
<body>
  <div class="header">
    <img src="imagenes/logo.jpeg" alt="Logo SIGPOL">

    <!-- Información del usuario -->
    <div class="user-info">
      <span>¡Bienvenido, <i>👤</i> <?= htmlspecialchars($usuario) ?>!</span>
      <span class="role">Rol: <?= htmlspecialchars($rol) ?></span>
      <span class="server">Servidor: <?= htmlspecialchars($servidor) ?></span>
    </div>
  </div>

  <div class="dashboard">
    <!-- Título del panel -->
    <h1 style="color:white; font-size:24px;">PANEL PRINCIPAL</h1>

    <!-- Botones comunes -->
    <a href="denuncias.php" class="button">DENUNCIAS</a>

    <!-- Botón de informes: solo jefes y admins -->
    <?php if ($rol !== "guardia"): ?>
      <a href="informes.php" class="button">INFORMES</a>
    <?php endif; ?>

    <!-- Botones comunes -->
    <a href="personas.php" class="button">PERSONAS</a>
    <a href="vehiculos.php" class="button">VEHÍCULOS</a>

    <!-- Botón exclusivo para admins -->
    <?php if ($rol === "admin"): ?>
      <a href="gestion_usuarios.php" class="button" style="background-color:#004d40;">GESTIÓN DE USUARIOS</a>
    <?php endif; ?>

    <!-- Botón de cierre de sesión -->
    <div class="logout">
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>
</body>
</html>
