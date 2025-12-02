<?php
// informes.php: Página de informes del sistema SIGPOL.
// Sin terminar aún

// Incluimos control de sesión y la conexión a la base de datos
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

// Variables de sesión para mostrar en la cabecera
$usuario = $_SESSION["nombre_usuario"] ?? "Usuario";
$rol     = $_SESSION["rol"] ?? "—";
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>En Construcción - SIGPOL</title>
  <style>
    /* === Estilos generales === */
    body {
      margin: 0;
      padding: 0;
      background-color: #0b3d0b;
      font-family: Arial, sans-serif;
    }

    /* === Cabecera con logo e info de usuario === */
    .header {
      display: flex;
      align-items: center;
      background-color: white;
      padding: 10px 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
    .header img { height: 80px; margin-right: 20px; }
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

    /* === Contenedor principal === */
    .dashboard {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: calc(100vh - 120px);
      margin-top: 40px;
    }

    .container {
      width: auto;
      max-width: 800px;
      margin: 20px auto;
      background: #fff;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
      text-align: center;
    }

    .construccion-img {
      max-width: 100%;
      height: auto;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    h2 {
      color: #003366;
      margin-bottom: 15px;
      text-align: center;
    }

    p {
      color: #666;
      font-size: 16px;
      line-height: 1.6;
      margin-bottom: 20px;
    }

    /* === Botón de volver al panel === */
    .back {
      margin-top: 20px;
      text-align: center;
    }
    .back a {
      background-color: #2e7d32;
      color: white;
      padding: 12px 24px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      display: inline-block;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .back a:hover {
      background-color: #1b5e20;
      transform: translateY(-1px);
    }

    /* === Ajustes para móviles === */
    @media (max-width:600px){
      .header img { height: 60px; }
      .container { padding: 20px; max-width: 90%; }
      h2 { font-size: 20px; }
      p { font-size: 14px; }
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
    <div class="container">
      <img src="imagenes/construccion.svg" alt="En Construcción" class="construccion-img">
      <h2>Página en Construcción</h2>
      <p>Esta sección está actualmente en desarrollo. Estamos trabajando para ofrecerte la mejor experiencia posible.</p>
      <p>Por favor, vuelve pronto para ver las novedades.</p>

      <div class="back">
        <a href="dashboard.php">Volver al Panel</a>
      </div>
    </div>
  </div>
</body>
</html>
