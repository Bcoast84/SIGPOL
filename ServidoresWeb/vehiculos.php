<?php
// vehiculos.php: Gestión de vehículos en el sistema SIGPOL.

// Incluimos control de sesión y conexión
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

// Variables de sesión para mostrar en la cabecera
$usuario = $_SESSION["nombre_usuario"] ?? "Usuario";
$rol     = $_SESSION["rol"] ?? "—";

// Variables para mensajes y resultados
$mensaje    = '';
$resultados = [];

// -------------------------
// PROCESAMIENTO DE FORMULARIO
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CREAR VEHÍCULO
    if (isset($_POST['crear'])) {
        $matricula = trim($_POST['matricula'] ?? '');
        $marca     = trim($_POST['marca'] ?? '');
        $modelo    = trim($_POST['modelo'] ?? '');
        $color     = trim($_POST['color'] ?? '');
        $dni       = trim($_POST['dni'] ?? '');

        if ($matricula !== '' && $marca !== '' && $dni !== '') {
            // Buscar id_persona por DNI
            $stmt = $pdo->prepare("SELECT id_persona FROM Persona WHERE dni = ?");
            $stmt->execute([$dni]);
            $persona = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($persona) {
                $id_persona = $persona['id_persona'];
                // Insertar vehículo asociado a la persona
                $stmt = $pdo->prepare("
                    INSERT INTO Vehiculo (matricula, marca, modelo, color, id_persona)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$matricula, $marca, $modelo, $color, $id_persona]);
                $mensaje = "Vehículo creado correctamente.";
            } else {
                $mensaje = "No existe ninguna persona con ese DNI.";
            }
        } else {
            $mensaje = "Completa al menos Matrícula, Marca y DNI de Persona para crear.";
        }
    }

    // CONSULTAR VEHÍCULOS
    elseif (isset($_POST['consultar'])) {
        $condiciones = [];
        $parametros  = [];

        if (!empty($_POST['matricula'])) {
            $condiciones[] = "v.matricula LIKE ?";
            $parametros[]  = "%" . $_POST['matricula'] . "%";
        }
        if (!empty($_POST['marca'])) {
            $condiciones[] = "v.marca LIKE ?";
            $parametros[]  = "%" . $_POST['marca'] . "%";
        }
        if (!empty($_POST['modelo'])) {
            $condiciones[] = "v.modelo LIKE ?";
            $parametros[]  = "%" . $_POST['modelo'] . "%";
        }
        if (!empty($_POST['color'])) {
            $condiciones[] = "v.color LIKE ?";
            $parametros[]  = "%" . $_POST['color'] . "%";
        }
        if (!empty($_POST['dni'])) {
            $condiciones[] = "p.dni LIKE ?";
            $parametros[]  = "%" . $_POST['dni'] . "%";
        }

        // Consulta para obtener datos del propietario
        $sql = "SELECT v.matricula, v.marca, v.modelo, v.color,
                       p.nombre, p.apellido1, p.apellido2, p.dni
                FROM Vehiculo v
                INNER JOIN Persona p ON v.id_persona = p.id_persona";

        if ($condiciones) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY v.matricula";

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
    <title>Gestión de Vehículos - SIGPOL</title>
    <style>
        /* === Estilos generales y de interfaz === */
        body {
            margin: 0;
            padding: 0;
            background-color: #0b3d0b;
            font-family: Arial, sans-serif;
        }
        
        .header {
            display: flex;
            align-items: center;
            background-color: white;
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
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

        .dashboard {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 40px;
        }
        
        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        
        h2, h3 {
            color: #003366;
            margin-bottom: 20px;
            text-align: center;
        }

        form.form-vehiculo {
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

        .mensaje {
            text-align: center;
            font-weight: bold;
            color: #003366;
            margin: 10px 0 0;
        }

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

        .container.resultados {
            margin-top: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
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

        @media (max-width: 600px) {
            .header img {
                height: 60px;
            }
            
            .container {
                padding: 20px;
                max-width: 90%;
            }
            
            .container.resultados {
                max-width: 95%;
            }
            
            button {
                width: 100%;
            }
            
            .buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Cabecera con logo y usuario -->
    <div class="header">
        <img src="imagenes/logo.jpeg" alt="Logo SIGPOL">
        <div class="user-info">
            <span>¡Bienvenido, <i>🚗</i> <?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?>!</span>
            <span class="role">Rol: <?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="dashboard">
        <!-- Formulario de creación/consulta -->
        <div class="container">
            <h2>Gestión de Vehículos</h2>

            <!-- Mensaje de confirmación o error -->
            <?php if ($mensaje): ?>
                <div class="mensaje"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <!-- Formulario para crear o consultar vehículos -->
            <form method="post" class="form-vehiculo">
                <div class="form-group">
                    <label for="matricula">Matrícula:</label>
                    <input type="text" id="matricula" name="matricula" />
                </div>
                <div class="form-group">
                    <label for="marca">Marca:</label>
                    <input type="text" id="marca" name="marca" />
                </div>
                <div class="form-group">
                    <label for="modelo">Modelo:</label>
                    <input type="text" id="modelo" name="modelo" />
                </div>
                <div class="form-group">
                    <label for="color">Color:</label>
                    <input type="text" id="color" name="color" />
                </div>
                <div class="form-group">
                    <label for="dni">Propietario (DNI):</label>
                    <input type="text" id="dni" name="dni" />
                </div>

                <!-- Botones de acción -->
                <div class="buttons">
                    <button type="submit" name="crear">Crear</button>
                    <button type="submit" name="consultar">Consultar</button>
                </div>
            </form>
        </div>

        <!-- Resultados de la consulta -->
        <?php if ($resultados): ?>
            <div class="container resultados">
                <h3>Resultados de la consulta</h3>
                <table>
                    <tr>
                        <th>Matrícula</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Color</th>
                        <th>Dueño - Nombre</th>
                        <th>Dueño - Apellido1</th>
                        <th>Dueño - Apellido2</th>
                        <th>Dueño - DNI</th>
                    </tr>
                    <?php foreach ($resultados as $fila): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$fila['matricula'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$fila['marca'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($fila['modelo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($fila['color'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$fila['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$fila['apellido1'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($fila['apellido2'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$fila['dni'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>

        <!-- Enlace de retorno al panel principal -->
        <div class="back">
            <a href="dashboard.php">Volver al Panel</a>
        </div>
    </div>
</body>
</html>