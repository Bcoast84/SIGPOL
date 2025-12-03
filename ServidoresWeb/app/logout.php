<?php
// logout.php: Cierra la sesión del usuario y redirige al login.

// Incluimos la gestión de sesión
require_once __DIR__ . '/session.php';

// Vaciamos el array $_SESSION para eliminar todas las variables de sesión
$_SESSION = [];

// Si la aplicación usa cookies de sesión, las invalidamos
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // Creamos una cookie con el mismo nombre que la de sesión pero expirada
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destruimos la sesión en el servidor
session_destroy();

// Enviamos al usuario de vuelta al login con un parámetro en la URL
header("Location: login.php?error=loggedout");
exit;
