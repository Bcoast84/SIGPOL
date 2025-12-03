<?php
session_start();

// Si el usuario ya tiene la variable de sesión abrimos dashboard
if (isset($_SESSION['nombre_usuario'])) {
    header("Location: dashboard.php"); 
    exit; 
}

// Si no está autenticado, lo mandamos al login.
header("Location: login.php");
exit;
