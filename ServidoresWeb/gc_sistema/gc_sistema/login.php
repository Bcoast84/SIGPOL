<?php
// login.php
session_start();

$error = $_GET['error'] ?? '';
$mensaje = '';

switch ($error) {
  case 'empty':
    $mensaje = '❌ Debes introducir usuario y contraseña.';
    break;
  case 'wrongpass':
    $mensaje = '❌ Contraseña incorrecta.';
    break;
  case 'nouser':
    $mensaje = '❌ Usuario no encontrado.';
    break;
  case 'connection':
    $mensaje = '❌ Error de conexión a la base de datos.';
    break;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Acceso al sistema SIGPOL</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #0b3d0b;
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-box {
      background-color: white;
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.3);
      text-align: center;
      width: 300px;
    }

    .login-box img {
      width: 280px;
      margin-bottom: 10px;
      display: block;
    }

    .login-box h2 {
      margin-top: 0;
      margin-bottom: 20px;
      color: #003366;
    }

    .login-box input[type="text"],
    .login-box input[type="password"] {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .login-box button {
      background-color: #2e7d32;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
    }

    .login-box button:hover {
      background-color: #1b5e20;
    }

    .error-msg {
      color: red;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <img src="imagenes/logo.jpeg" alt="Logo SIGPOL">
    <h2>Acceso al sistema SIGPOL</h2>

    <?php if ($mensaje): ?>
      <div class="error-msg"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php echo "<p style='color:gray;font-size:12px;'>Contenedor activo: " . gethostname() . "</p>"; ?>
    <?php
     echo "<p>Contenedor NGINX: " . $_SERVER['NGINX_HOSTNAME'] . "</p>";
     echo "<p>Contenedor PHP-FPM: " . gethostname() . "</p>";
    ?>

    <form action="procesar_login.php" method="post">
      <input type="text" name="usuario" placeholder="Usuario" required>
      <input type="password" name="contraseña" placeholder="Contraseña" required>
      <button type="submit">Entrar</button>
    </form>
  </div>
</body>
</html>
