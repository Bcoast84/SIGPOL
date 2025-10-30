<?php
// dashboard.php
session_start();

// Verificar si el usuario ha iniciado sesión.
// Si no hay sesión, se redirige a login.php.
if (!isset($_SESSION["nombre_usuario"]) || !isset($_SESSION["rol"])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION["nombre_usuario"];
$rol = $_SESSION["rol"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel SIGPOL</title>
  <style>
    /* Estilos basados en tu diseño original */
    body {
      margin: 0;
      padding: 0;
      background-color: #0b3d0b; /* Fondo verde oscuro */
      font-family: Arial, sans-serif;
    }

    .header {
      display: flex;
      align-items: center;
      background-color: white;
      padding: 10px 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }

    .header img {
      /* Asumiendo que esta imagen existe en /gc_sistema/imagenes/logo.jpeg */
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
      margin-top: 60px;
    }

    .button {
      background-color: #2e7d32;
      color: white;
      border: none;
      padding: 15px 40px;
      margin: 15px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.3);
      font-size: 18px;
      cursor: pointer;
      width: 250px;
      text-align: center;
      transition: background-color 0.3s ease, transform 0.2s ease;
      text-decoration: none;
    }

    .button:hover {
      background-color: #1b5e20;
      transform: translateY(-2px);
    }

    .logout {
      margin-top: 30px;
    }

    .logout a {
      background-color: #d32f2f;
      padding: 10px 20px;
      border-radius: 6px;
      color: white;
      text-decoration: none;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    .logout a:hover {
      background-color: #b71c1c;
    }
    /* Responsividad */
    @media (max-width: 600px) {
        .header img {
            height: 60px;
        }
        .dashboard {
            margin-top: 30px;
        }
        .button {
            width: 90%;
            max-width: 300px;
        }
    }
  </style>
</head>
<body>
  <div class="header">
    <img src="imagenes/logo.jpeg" alt="Logo SIGPOL">
    <div class="user-info">
      <span>¡Bienvenido, <i>👤</i> <?= htmlspecialchars($usuario) ?>!</span>
      <span class="role">Rol: <?= htmlspecialchars($rol) ?></span>
    </div>
  </div>

  <div class="dashboard">
    <h1 style="color:white; font-size: 24px;">PANEL PRINCIPAL</h1>
    
    <!-- Botones de Acceso -->
    <a href="denuncias.html" class="button">DENUNCIAS</a>
    <a href="informes.html" class="button">INFORMES</a>
    <a href="personas.php" class="button">PERSONAS</a>
    <a href="vehiculos.html" class="button">VEHÍCULOS</a>

    <?php if ($rol === "Administrador"): ?>
      <a href="usuarios.html" class="button" style="background-color: #004d40;">GESTIONAR USUARIOS</a>
    <?php endif; ?>

    <div class="logout">
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>
</body>
</html>