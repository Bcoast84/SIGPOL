<?php
// DB.PHP - Conexión PDO a SQL Server
// Listener: SIGPOL-LIST1

try {
    $pdo = new PDO(
        "sqlsrv:Server=192.168.1.60,1433;Database=SIGPOL;Encrypt=Yes;TrustServerCertificate=Yes",
        "sigpol_app",        // Usuario dedicado para la aplicación
        "Abcd1234."
    );

    // Excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Si falla la conexión, se detiene y da error
    die("Error de conexión: " . $e->getMessage());
}
?>
