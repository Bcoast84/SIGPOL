<?php
// procesar_login.php
session_start();

// Mantenemos ini_set('display_errors', 1) para ver la salida de la depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificar que se haya enviado por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$usuario = $_POST["usuario"] ?? '';
$contrasena = $_POST["contraseña"] ?? '';

// Verificar que los campos no estén vacíos
if (empty($usuario) || empty($contrasena)) {
    header("Location: login.php?error=empty");
    exit;
}

// ----------------------------------------------------
// *** CONEXIÓN A LA BASE DE DATOS (CRÍTICO) ***
// ----------------------------------------------------
// Se fuerza la IP externa, ignorando getenv('DB_HOST')
$serverName = '192.168.1.10'; 
$database = getenv('DB_NAME') ?: 'gc_sistema'; // Nombre de tu base de datos
$uid = getenv('DB_USER') ?: 'SA'; 
$pwd = getenv('DB_PASS') ?: 'Abcd1234.'; 

// AÑADIDO PARA DEPURACIÓN
error_log("Intentando conexión a DB Host: " . $serverName . " y DB: " . $database);

try {
    // DSN (Data Source Name)
    // Se incluye la base de datos en el DSN
    $dsn = "sqlsrv:Server=$serverName,1433;Database=$database;TrustServerCertificate=yes";

    $conn = new PDO($dsn, $uid, $pwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ===================================================================
    //  CONSULTA FINAL: Se fuerza el nombre completo de la DB.
    // ===================================================================
    // Se utiliza la sintaxis completa: [NombreDB].[Esquema].[Tabla]
    $sql = "SELECT contrasena_hash, rol FROM gc_sistema.dbo.Usuario WHERE nombre_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usuario]);

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hashGuardado = $row["contrasena_hash"];

        if (password_verify($contrasena, $hashGuardado)) {
            $_SESSION["nombre_usuario"] = $usuario;
            $_SESSION["rol"] = $row["rol"];
            header("Location: dashboard.php");
            exit;
        } else {
            // Contraseña incorrecta
            header("Location: login.php?error=wrongpass");
            exit;
        }
    } else {
        // Usuario no encontrado
        header("Location: login.php?error=nouser");
        exit;
    }

} catch (PDOException $e) {
    // ESTE ES EL CÓDIGO DE DEPURACIÓN (Si falla, el problema es la contraseña)
    echo "<h1>❌ Error Crítico de Conexión o Consulta</h1>";
    echo "<p><strong>SQLSTATE:</strong> " . htmlspecialchars($e->getCode()) . "</p>";
    echo "<p><strong>Mensaje Detallado:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    exit; 
}