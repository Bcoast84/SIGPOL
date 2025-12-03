<?php
// denuncias.php: Gestión de denuncias en el sistema SIGPOL.

// Incluimos control de sesión y la conexión a la base de datos
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

// Variables de sesión 
$usuario = $_SESSION["nombre_usuario"] ?? "Usuario";
$rol     = $_SESSION["rol"] ?? "—";

// Variables para mensajes y resultados
$mensaje = '';
$resultados = [];

// Procesamiento de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recogemos y saneamos entradas
    $fecha        = trim($_POST['fecha'] ?? '');
    $agente_nom   = trim($_POST['agente'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $catalogacion = trim($_POST['catalogacion'] ?? '');
    $cuartel_nom  = trim($_POST['cuartel'] ?? '');
    $calle        = trim($_POST['calle'] ?? '');
    $numero       = trim($_POST['numero'] ?? '');
    $localidad    = trim($_POST['localidad'] ?? '');
    $provincia    = trim($_POST['provincia'] ?? '');
    $comunidad    = trim($_POST['comunidad'] ?? '');
    $codigo_postal= trim($_POST['codigo_postal'] ?? '');

    // -------------------------
    // CREAR DENUNCIA
    // -------------------------
    if (isset($_POST['crear'])) {
        if ($fecha && $agente_nom && $descripcion && $catalogacion && $cuartel_nom &&
            $calle && $numero && $localidad && $provincia && $comunidad && $codigo_postal) {

            // Verificamos que el agente exista
            $stmt = $pdo->prepare("SELECT id_usuario FROM Usuario WHERE nombre_usuario = ?");
            $stmt->execute([$agente_nom]);
            $usr = $stmt->fetch();

            if (!$usr) {
                $mensaje = "El agente no existe en la base de datos.";
            } else {
                // Verificamos si la dirección ya existe
                $stmt = $pdo->prepare("SELECT id_direccion FROM Direccion
                                       WHERE calle=? AND numero=? AND localidad=? AND provincia=? AND comunidad=? AND codigo_postal=?");
                $stmt->execute([$calle,$numero,$localidad,$provincia,$comunidad,$codigo_postal]);
                $dir = $stmt->fetch();

                if (!$dir) {
                    // Creamos nueva dirección
                    $id_direccion = substr(uniqid("DIR"),0,20);
                    $stmt = $pdo->prepare("INSERT INTO Direccion (id_direccion, calle, numero, localidad, provincia, comunidad, codigo_postal)
                                           VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$id_direccion,$calle,$numero,$localidad,$provincia,$comunidad,$codigo_postal]);
                } else {
                    $id_direccion = $dir['id_direccion'];
                }

                // Verificamos si el cuartel ya existe
                $stmt = $pdo->prepare("SELECT id_cuartel FROM Cuartel WHERE nombre = ?");
                $stmt->execute([$cuartel_nom]);
                $cua = $stmt->fetch();

                if (!$cua) {
                    // Creamos nuevo cuartel
                    $id_cuartel = substr(uniqid("CUA"),0,10);
                    $stmt = $pdo->prepare("INSERT INTO Cuartel (id_cuartel, nombre, id_direccion) VALUES (?, ?, ?)");
                    $stmt->execute([$id_cuartel, $cuartel_nom, $id_direccion]);
                } else {
                    $id_cuartel = $cua['id_cuartel'];
                }

                // Insertamos la denuncia
                $stmt = $pdo->prepare("
                    INSERT INTO Denuncia (fecha, descripcion, catalogacion, id_usuario, id_cuartel, id_direccion)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$fecha,$descripcion,$catalogacion,$usr['id_usuario'],$id_cuartel,$id_direccion]);
                $mensaje = "Denuncia creada correctamente.";
            }
        } else {
            $mensaje = "Completa todos los campos para crear.";
        }
    }

    // -------------------------
    // CONSULTAR DENUNCIAS
    // -------------------------
    if (isset($_POST['consultar'])) {
        $condiciones = [];
        $parametros = [];
        if ($fecha)        { $condiciones[] = "d.fecha=?"; $parametros[] = $fecha; }
        if ($agente_nom)   { $condiciones[] = "u.nombre_usuario LIKE ?"; $parametros[] = "%$agente_nom%"; }
        if ($descripcion)  { $condiciones[] = "d.descripcion LIKE ?"; $parametros[] = "%$descripcion%"; }
        if ($catalogacion) { $condiciones[] = "d.catalogacion LIKE ?"; $parametros[] = "%$catalogacion%"; }
        if ($cuartel_nom)  { $condiciones[] = "c.nombre LIKE ?"; $parametros[] = "%$cuartel_nom%"; }
        if ($calle)        { $condiciones[] = "dir.calle LIKE ?"; $parametros[] = "%$calle%"; }
        if ($numero)       { $condiciones[] = "dir.numero LIKE ?"; $parametros[] = "%$numero%"; }
        if ($localidad)    { $condiciones[] = "dir.localidad LIKE ?"; $parametros[] = "%$localidad%"; }
        if ($provincia)    { $condiciones[] = "dir.provincia LIKE ?"; $parametros[] = "%$provincia%"; }
        if ($comunidad)    { $condiciones[] = "dir.comunidad LIKE ?"; $parametros[] = "%$comunidad%"; }
        if ($codigo_postal){ $condiciones[] = "dir.codigo_postal LIKE ?"; $parametros[] = "%$codigo_postal%"; }

        $sql = "SELECT d.id_denuncia, d.fecha, u.nombre_usuario AS agente,
                       d.descripcion, d.catalogacion, c.nombre AS cuartel,
                       dir.calle, dir.numero, dir.localidad, dir.provincia, dir.comunidad, dir.codigo_postal
                FROM Denuncia d
                INNER JOIN Usuario u ON d.id_usuario = u.id_usuario
                INNER JOIN Cuartel c ON d.id_cuartel = c.id_cuartel
                LEFT JOIN Direccion dir ON d.id_direccion = dir.id_direccion";
        
        if ($condiciones) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }
        
        $sql .= " ORDER BY d.id_denuncia DESC";

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
    <title>Gestión de Denuncias - SIGPOL</title>
    <style>
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
            max-width: 500px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        /* Contenedor de resultados ajustado al contenido */
        .container.resultados {
            width: auto;
            max-width: 95%;
            min-width: 500px;
            padding: 20px;
        }

        h2, h3 {
            color: #003366;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 12px;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 6px;
            color: #2e7d32;
        }

        .form-group input,
        .form-group textarea {
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

        /* Tabla ajustada al contenido */
        .table-wrapper {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            border-collapse: collapse;
            width: auto;
            min-width: 100%;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            white-space: nowrap;
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
            display: inline-block;
        }

        .back a:hover {
            background-color: #1b5e20;
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
                min-width: auto;
            }
            
            button {
                width: 100%;
            }
            
            .buttons {
                flex-direction: column;
            }
            
            th, td {
                white-space: normal;
            }
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
            <h2>Gestión de Denuncias</h2>

            <?php if ($mensaje): ?>
                <div class="mensaje"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <!-- Formulario de creación/consulta -->
            <form method="post">
                <!-- Campos de denuncia -->
                <div class="form-group">
                    <label>Fecha:</label>
                    <input type="date" name="fecha">
                </div>
                <div class="form-group">
                    <label>Agente:</label>
                    <input type="text" name="agente">
                </div>
                <div class="form-group">
                    <label>Descripción:</label>
                    <textarea name="descripcion"></textarea>
                </div>
                <div class="form-group">
                    <label>Catalogación:</label>
                    <input type="text" name="catalogacion">
                </div>
                <div class="form-group">
                    <label>Cuartel:</label>
                    <input type="text" name="cuartel">
                </div>

                <!-- Bloque de dirección asociada a la denuncia -->
                <h3>Dirección de la denuncia</h3>
                <div class="form-group">
                    <label for="calle">Calle:</label>
                    <input type="text" id="calle" name="calle" />
                </div>
                <div class="form-group">
                    <label for="numero">Número:</label>
                    <input type="text" id="numero" name="numero" />
                </div>
                <div class="form-group">
                    <label for="localidad">Localidad:</label>
                    <input type="text" id="localidad" name="localidad" />
                </div>
                <div class="form-group">
                    <label for="provincia">Provincia:</label>
                    <input type="text" id="provincia" name="provincia" />
                </div>
                <div class="form-group">
                    <label for="comunidad">Comunidad Autónoma:</label>
                    <input type="text" id="comunidad" name="comunidad" />
                </div>
                <div class="form-group">
                    <label for="codigo_postal">Código Postal:</label>
                    <input type="text" id="codigo_postal" name="codigo_postal" />
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
                <div class="table-wrapper">
                    <table>
                        <tr>
                            <th>ID Denuncia</th>
                            <th>Fecha</th>
                            <th>Agente</th>
                            <th>Descripción</th>
                            <th>Catalogación</th>
                            <th>Cuartel</th>
                            <th>Calle</th>
                            <th>Número</th>
                            <th>Localidad</th>
                            <th>Provincia</th>
                            <th>Comunidad</th>
                            <th>Código Postal</th>
                        </tr>
                        <?php foreach ($resultados as $fila): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$fila['id_denuncia'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$fila['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$fila['agente'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$fila['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$fila['catalogacion'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$fila['cuartel'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$fila['calle'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$fila['numero'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$fila['localidad'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$fila['provincia'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$fila['comunidad'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$fila['codigo_postal'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Enlace de retorno al panel principal -->
        <div class="back">
            <a href="dashboard.php">Volver al Panel</a>
        </div>
    </div>
</body>
</html>