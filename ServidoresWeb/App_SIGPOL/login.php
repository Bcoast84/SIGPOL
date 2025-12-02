<?php
// login.php: Pantalla de acceso al sistema SIGPOL.

// Incluimos el control de sesiones.
require_once __DIR__ . '/session.php';

// Capturamos el parámetro 'error' de la URL (si existe).
$error = $_GET['error'] ?? '';
$mensaje = '';

// Según el tipo de error recibido, mensaje correspondiente.
switch ($error) {
  case 'empty':      $mensaje = 'Debes introducir usuario y contraseña.'; break;
  case 'invalid':    $mensaje = 'Usuario o contraseña incorrectos.'; break;
  case 'connection': $mensaje = 'Error de conexión a la base de datos.'; break;
  case 'loggedout':  $mensaje = 'Sesión cerrada correctamente.'; break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Acceso al sistema SIGPOL</title>
  <style>
    /* Centrar el formulario en pantalla */
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: #0b3d0b; /* verde oscuro de fondo */
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    /* Caja blanca que contiene el formulario de login */
    .login-container {
      background-color: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.4);
      text-align: center;
      width: 350px;
    }

    /* Logo GC */
    .login-container img {
      width: 180px;
      margin-bottom: 20px;
    }

    /* Título del formulario */
    h2 {
      margin-bottom: 20px;
      color: #003366;
    }

    /* Mensajes de error*/
    .error {
      color: red;
      margin-bottom: 15px;
      font-weight: bold;
    }

    /* Campos de texto y contraseña */
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
    }

    /* Botón de envío */
    button {
      background-color: #2e7d32;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      width: 100%;
      transition: background-color 0.3s ease;
    }

    /* Efecto del botón */
    button:hover {
      background-color: #1b5e20;
    }
  </style>

</head>
<body>
  <div class="login-container">
    <!-- Logo GC -->
    <img src="imagenes/logo.jpeg" alt="Escudo SIGPOL">

    <!-- Título principal de la pantalla de login -->
    <h2>Acceso al sistema SIGPOL</h2>

    <!-- Si hay error que se muestre -->
    <?php if ($mensaje): ?>
      <div class="error"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <!-- Enviar credenciales a auth.php vía POST -->
    <form action="auth.php" method="post">
      <input type="text" name="username" placeholder="Usuario" required autocomplete="off">
      <input type="password" name="password" placeholder="Contraseña" required autocomplete="off">
      <button type="submit">Entrar</button>
    </form>

    <!-- Muestra el servidor actual y el balanceador que atendió la petición (depuración)-->
    <div class="info">
     Servidor: <?= htmlspecialchars(gethostname()) ?><br>
     Balanceador: <?= htmlspecialchars($_SERVER['HTTP_X_BALANCER'] ?? 'desconocido') ?>
    </div>
  </div>
</body>
</html>
