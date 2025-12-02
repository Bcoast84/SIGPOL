<?php
// personas.php: Gestión de personas en SIGPOL.

// Incluimos el control de sesión y la conexión a la base de datos
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

// Aseguramos que la sesión esté activa
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verificamos que el usuario esté autenticado
if (!isset($_SESSION["nombre_usuario"]) || !isset($_SESSION["rol"])) {
    header("Location: login.php");
    exit;
}

// Variables de sesión para mostrar en la interfaz
$usuario = $_SESSION["nombre_usuario"] ?? "Usuario";
$rol     = $_SESSION["rol"] ?? "—";

// Variables para mensajes y resultados
$mensaje = '';
$resultados = [];

// Procesamiento de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // -------------------------
    // CREAR PERSONA
    // -------------------------
    if (isset($_POST['crear'])) {
        $nombre     = trim($_POST['nombre'] ?? '');
        $apellido1  = trim($_POST['apellido1'] ?? '');
        $apellido2  = trim($_POST['apellido2'] ?? '');
        $dni        = trim($_POST['dni'] ?? '');
        $fecha_nac  = trim($_POST['fecha_nac'] ?? '');

        if ($nombre !== '' && $apellido1 !== '' && $dni !== '' && $fecha_nac !== '') {
            $stmt = $pdo->prepare("
                INSERT INTO Persona (nombre, apellido1, apellido2, dni, fecha_nac)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nombre, $apellido1, $apellido2, $dni, $fecha_nac]);
            $mensaje = "Persona creada correctamente.";
        } else {
            $mensaje = "Completa al menos Nombre, Primer Apellido, DNI y Fecha de Nacimiento para crear.";
        }
    }
    // -------------------------
    // CONSULTAR PERSONAS
    // -------------------------
    elseif (isset($_POST['consultar'])) {
        $condiciones = [];
        $parametros  = [];

        if (!empty($_POST['nombre'])) {
            $condiciones[] = "nombre LIKE ?";
            $parametros[]  = "%".$_POST['nombre']."%";
        }
        if (!empty($_POST['apellido1'])) {
            $condiciones[] = "apellido1 LIKE ?";
            $parametros[]  = "%".$_POST['apellido1']."%";
        }
        if (!empty($_POST['apellido2'])) {
            $condiciones[] = "apellido2 LIKE ?";
            $parametros[]  = "%".$_POST['apellido2']."%";
        }
        if (!empty($_POST['dni'])) {
            $condiciones[] = "dni LIKE ?";
            $parametros[]  = "%".$_POST['dni']."%";
        }
        if (!empty($_POST['fecha_nac'])) {
            $condiciones[] = "fecha_nac = ?";
            $parametros[]  = $_POST['fecha_nac'];
        }

        $sql = "SELECT nombre, apellido1, apellido2, dni, fecha_nac FROM Persona";
        if ($condiciones) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }
        $sql .= " ORDER BY id_persona DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gestión de Personas - SIGPOL</title>
  <style>
    /* === Estilos generales === */
    body {
    margin: 0;
    padding: 0;
    background-color: #0b3d0b; /* verde oscuro de fondo */
    font-family: Arial, sans-serif;
    }

    /* === Cabecera con logo e información de usuario === */
    .header {
    display: flex;
    align-items: center;
    background-color: white;
    padding: 10px 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3); 
    }

    .header img {
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

    /* === Contenedor principal del dashboard === */
    .dashboard {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 40px;
    }

    /* === Caja blanca para formulario de personas === */
    .container {
    width: 100%;
    max-width: 500px;
    margin: 20px auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    h2, h3 {
    color: #003366;
    margin-bottom: 20px;
    text-align: center;
    }

    /* === Formulario de creación/consulta === */
    form.form-persona {
    display: flex;
    flex-direction: column;
    gap: 16px;
    }

    .form-group {
    display: flex;
    flex-direction: column;
    }

    .form-group label {
    font-weight: bold;
    margin-bottom: 6px;
    color: #2e7d32;
    }

    .form-group input {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
    }

    /* === Botones de acción === */
    .buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 8px;
    }

    button {
    background-color: #2e7d32;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    }

    button:hover {
    background-color: #1b5e20;
    transform: translateY(-1px);
    }

    /* === Mensajes informativos === */
    .mensaje {
    text-align: center;
    font-weight: bold;
    color: #003366;
    margin: 10px 0 0;
    }

    /* === Tabla de resultados === */
    table {
    border-collapse: collapse;
    margin-top: 20px;
    }

    th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
    }

    th {
    background-color: #003366;
    color: white;
    }

    /* === Botón de volver al panel === */
    .back {
    margin-top: 20px;
    text-align: center;
    }

    .back a {
    background-color: #2e7d32;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    }

    .back a:hover {
    background-color: #1b5e20;
    }

    /* === Contenedor de resultados de consulta === */
    .container.resultados {
    margin-top: 20px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    width: 95%;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
    }

    .container.resultados table {
    width: 100%;
    table-layout: auto;
    }

    .container.resultados h3 {
    text-align: center;
    color: #003366;
    margin-bottom: 15px;
    }

    /* === Ajustes para móviles === */
    @media (max-width:600px) {
    .header img { height: 60px; }
    .container { padding: 20px; max-width: 90%; }
    .container.resultados { max-width: 95%; }
    button { width: 100%; }
    .buttons { flex-direction: column; }
    }
  </style>
</head>
<body>
  <div class="header">
    <img src="imagenes/logo.jpeg" alt="Logo SIGPOL">
    <div class="user-info">
      <span>¡Bienvenido, <i>👤</i> <?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?>!</span>
      <span class="role">Rol: <?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>

  <div class="dashboard">
    <div class="container">
      <h2>Gestión de Personas</h2>

      <?php if ($mensaje): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <!-- Formulario de creación/consulta -->
      <form method="post" class="form-persona">
        <div class="form-group">
          <label for="nombre">Nombre:</label>
          <input type="text" id="nombre" name="nombre" />
        </div>
        <div class="form-group">
          <label for="apellido1">Primer Apellido:</label>
          <input type="text" id="apellido1" name="apellido1" />
        </div>
        <div class="form-group">
          <label for="apellido2">Segundo Apellido:</label>
          <input type="text" id="apellido2" name="apellido2" />
        </div>
        <div class="form-group">
          <label for="dni">DNI:</label>
          <input type="text" id="dni" name="dni" />
        </div>
        <div class="form-group">
          <label for="fecha_nac">Fecha de Nacimiento:</label>
          <input type="date" id="fecha_nac" name="fecha_nac" />
        </div>

        <div class="buttons">
          <button type="submit" name="crear">Crear</button>
          <button type="submit" name="consultar">Consultar</button>
        </div>
      </form>
    </div>

    <!-- Resultados de consulta -->
    <?php if ($resultados): ?>
      <div class="container resultados">
        <h3>Resultados de la consulta</h3>
        <table>
          <tr>
            <th>Nombre</th>
            <th>Primer apellido</th>
            <th>Segundo apellido</th>
            <th>DNI</th>
            <th>Fecha Nacimiento</th>
          </tr>
          <?php foreach ($resultados as $fila): ?>
            <tr>
              <td><?= htmlspecialchars((string)$fila['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$fila['apellido1'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($fila['apellido2'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$fila['dni'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$fila['fecha_nac'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
    <?php endif; ?>

    <div class="back">
      <a href="dashboard.php">Volver al Panel</a>
    </div>
  </div>
</body>
</html>