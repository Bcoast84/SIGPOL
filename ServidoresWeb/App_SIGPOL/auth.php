<?php
// AUTH.PHP - Autenticación de usuarios

// Incluye gestión de sesión y conexión a base de datos
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

// Solo permite peticiones POST para evitar peticiones por GET) por GET)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

// Recoge credenciales del formulario
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Evitar campos vacíos
if ($username === '' || $password === '') {
    header("Location: login.php?error=empty");
    exit;
}

try {
    // -------------------------
    // Buscar usuario en la tabla Usuario
    // -------------------------
    $stmt = $pdo->prepare("SELECT nombre_usuario, contrasena_hash, rol 
                           FROM Usuario 
                           WHERE nombre_usuario = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // -------------------------
    // Verificar contraseña
    // -------------------------
    if ($user && password_verify($password, $user['contrasena_hash'])) {
        // Regenera ID de sesión para evitar fijación de sesión
        session_regenerate_id(true);

        // Guarda datos en la sesión
        $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
        $_SESSION['rol']            = $user['rol'];

        // Redirige al dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        // Credenciales inválidas
        header("Location: login.php?error=invalid");
        exit;
    }
} catch (PDOException $e) {
    // Error de conexión o consulta
    header("Location: login.php?error=connection");
    exit;
}
