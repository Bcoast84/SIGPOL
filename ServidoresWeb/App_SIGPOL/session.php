<?php
// SESSION.PHP - Gestión de sesión

// Configuración de seguridad para las cookies de sesión
ini_set('session.cookie_httponly', '1');   // La cookie no puede ser accedida vía JavaScript
ini_set('session.cookie_secure', '1');     // La cookie solo se envía por HTTPS
ini_set('session.use_strict_mode', '1');   // No acepta IDs de sesión inválidos o generados por el cliente
ini_set('session.use_only_cookies', '1');  // No permite pasar el ID de sesión por la URL
ini_set('session.cookie_samesite', 'Strict'); // Evita envío de la cookie en peticiones cross-site

// Nombre único de la sesión para la aplicación SIGPOL
session_name('SIGPOLSESSID');

// -------------------------
// Conexión PDO centralizada
// -------------------------
// Se importa la conexión desde db.php
require_once __DIR__ . '/db.php';

// -------------------------
// Funciones para usar SQL Server como almacenamiento de sesiones
// -------------------------

function abrir($savePath, $sessionName) { return true; }
function cerrar() { return true; }

function leer($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT data_session FROM dbo.SesionesPHP WHERE id_session = ?");
    $stmt->execute([$id]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
    return $fila['data_session'] ?? '';
}

function escribir($id, $data) {
    global $pdo;
    $stmt = $pdo->prepare("
        MERGE dbo.SesionesPHP AS target
        USING (SELECT ? AS id_session, ? AS data_session, GETDATE() AS last_update) AS source
        ON target.id_session = source.id_session
        WHEN MATCHED THEN 
            UPDATE SET data_session = source.data_session, last_update = source.last_update
        WHEN NOT MATCHED THEN
            INSERT (id_session, data_session, last_update) 
            VALUES (source.id_session, source.data_session, source.last_update);
    ");
    return $stmt->execute([$id, $data]);
}

function destruir($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM dbo.SesionesPHP WHERE id_session = ?");
    return $stmt->execute([$id]);
}

function gc($max_lifetime) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM dbo.SesionesPHP WHERE DATEDIFF(SECOND, last_update, GETDATE()) > ?");
    return $stmt->execute([$max_lifetime]);
}

// -------------------------
// Configurar PHP para usar estas funciones de sesión
// -------------------------
session_set_save_handler(
    'abrir',
    'cerrar',
    'leer',
    'escribir',
    'destruir',
    'gc'
);

// Arranca la sesión con el handler definido
session_start();
?>
