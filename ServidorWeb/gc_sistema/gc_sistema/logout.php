<?php
// logout.php
session_start();

// 1. Destruir todas las variables de sesión (limpiar el array $_SESSION)
// Esto asegura que, incluso si la sesión no se destruye inmediatamente,
// las claves 'nombre_usuario' y 'rol' ya no existen.
$_SESSION = array();

// 2. Si se está usando una cookie de sesión, forzar su expiración.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // Establecer la cookie en el pasado (tiempo - 42000)
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finalmente, destruir el archivo/ID de sesión en el servidor.
session_destroy();

// Redirigir a la página de login
header("Location: login.php");
exit;
?>