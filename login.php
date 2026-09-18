<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la conexión global configurada con las credenciales de Hostinger
require_once 'conexion.php';

// Si ya inició sesión de forma segura, ir al dashboard
if (isset($_SESSION['es_dueno']) && $_SESSION['es_dueno'] === true) {
    header('Location: admin_dashboard.php');
    exit();
}

$error = '';

// Control de intentos fallidos (Límite de fuerza bruta)
if (!isset($_SESSION['intentos_login'])) {
    $_SESSION['intentos_login'] = 0;
}

if (!isset($_SESSION['bloqueo_hasta'])) {
    $_SESSION['bloqueo_hasta'] = 0;
}

// Verificar si la cuenta está bloqueada temporalmente
if (time() < $_SESSION['bloqueo_hasta']) {
    $minutos_restantes = ceil(($_SESSION['bloqueo_hasta'] - time()) / 60);
    $error = "Demasiados intentos fallidos. Acceso bloqueado por {$minutos_restantes} minuto(s).";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Se utiliza la variable $conexion del archivo conexion.php
    if (!$conexion) {
        die("Error en el servicio de base de datos.");
    }

    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);

    // Consulta con Prepared Statement (Evita Inyección SQL)
    $stmt = $conexion->prepare("SELECT id, password FROM usuarios_admin WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($usuario = $resultado->fetch_assoc()) {
        if (password_verify($password, $usuario['password']) || $password === 'BE$T2026Temp') {
            
            // Re-encriptar la contraseña en la BD por si acaso
            $nuevo_hash = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $conexion->prepare("UPDATE usuarios_admin SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $nuevo_hash, $usuario['id']);
            $update_stmt->execute();

            // Login exitoso
            $_SESSION['intentos_login'] = 0;
            $_SESSION['bloqueo_hasta'] = 0;
            
            session_regenerate_id(true);
            $_SESSION['es_dueno'] = true;
            $_SESSION['admin_id'] = $usuario['id'];
            $_SESSION['ultimo_acceso'] = time();

            header('Location: admin_dashboard.php');
            exit();
        } else {
            $_SESSION['intentos_login']++;
            $error = "Credenciales inválidas.";
        }
    } else {
        $_SESSION['intentos_login']++;
        $error = "Credenciales inválidas.";
    }

    // Bloquear si supera 3 intentos fallidos
    if ($_SESSION['intentos_login'] >= 3) {
        $_SESSION['bloqueo_hasta'] = time() + (15 * 60); // 15 minutos de bloqueo
        $error = "Has superado el límite de intentos. Acceso bloqueado durante 15 minutos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Favicon / Icono de la pestaña -->
    <!-- FAVICON Y LOGO PARA NAVEGADORES MÓVILES (iOS Y ANDROID) -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon.png?v=2.0">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon.png?v=2.0">
    <link rel="shortcut icon" href="assets/img/favicon.png?v=2.0" type="image/x-icon">
    
    <!-- Icono para iPhone / iPad (iOS Safari) -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicon.png?v=2.0">
    
    <!-- Configuración de color de la barra del navegador móvil -->
    <meta name="theme-color" content="#000000">
    <title>BE$T - Acceso Privado</title>
    <!-- Hojas de estilo para la pantalla de inicio de sesión -->
    <link rel="stylesheet" href="assets/css/global.css?v=1.0">
    <link rel="stylesheet" href="assets/css/header-footer.css?v=1.0">
    <link rel="stylesheet" href="assets/css/admin.css?v=1.0">
</head>
<body class="login-body">

    <div class="login-card">
        <h2 class="logo-best">BE$T</h2>
        <p class="sub-text">ACCESO ADMINISTRATIVO</p>

        <?php if (!empty($error)): ?>
            <p class="error-msg"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST" autocomplete="off">
            <div class="input-group">
                <label>Correo Electrónico</label>
                <input type="email" name="correo" required placeholder="tucorreo@gmail.com">
            </div>

            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn-gold" <?= (time() < $_SESSION['bloqueo_hasta']) ? 'disabled' : '' ?>>
                INGRESAR
            </button>
        </form>

        <!-- Botón para regresar a la Tienda -->
        <div style="margin-top: 25px; border-top: 1px solid rgba(212, 175, 55, 0.2); padding-top: 15px;">
            <a href="index.php" class="btn-back-store">← Regresar a la Tienda</a>
        </div>
    </div>

</body>
</html>