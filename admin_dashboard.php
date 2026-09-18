<?php
session_start();

// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de seguridad y sesión
if (!isset($_SESSION['es_dueno']) || $_SESSION['es_dueno'] !== true) {
    header('Location: login.php');
    exit();
}

// Cierre por inactividad (30 min)
if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php?expirado=1');
    exit();
}
$_SESSION['ultimo_acceso'] = time();

// Incluir la conexión global configurada con Hostinger
require_once 'conexion.php';

if (!$conexion) {
    die("Error de conexión a la base de datos.");
}

// -------------------------------------------------------------
// CONFIGURACIÓN DE ZONA HORARIA (COLOMBIA / GMT-5)
// -------------------------------------------------------------
date_default_timezone_set('America/Bogota');
$conexion->query("SET time_zone = '-05:00'");

// -------------------------------------------------------------
// OBTENER MÉTRICAS PARA LAS TARJETAS EN TIEMPO REAL
// -------------------------------------------------------------

// Ventas del día (usando CURDATE() sincronizado con Colombia)
$res_ventas = $conexion->query("SELECT SUM(total) as total_hoy FROM ventas WHERE DATE(fecha) = CURDATE()");
$ventas_hoy = ($res_ventas && $row = $res_ventas->fetch_assoc()) ? ($row['total_hoy'] ?? 0) : 0;

// Total de prendas en stock
$res_stock = $conexion->query("SELECT SUM(stock) as total_stock FROM productos");
$total_stock = ($res_stock && $row = $res_stock->fetch_assoc()) ? ($row['total_stock'] ?? 0) : 0;

// Alertas de stock bajo (menos de 3 unidades)
$res_alertas = $conexion->query("SELECT COUNT(*) as alertas FROM productos WHERE stock <= 3");
$alertas_stock = ($res_alertas && $row = $res_alertas->fetch_assoc()) ? ($row['alertas'] ?? 0) : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- Favicon / Icono de la pestaña -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon.png?v=2.0">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon.png?v=2.0">
    <link rel="shortcut icon" href="assets/img/favicon.png?v=2.0" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicon.png?v=2.0">
    
    <meta name="theme-color" content="#000000">
    <title>BE$T - Panel de Control</title>
    <link rel="stylesheet" href="assets/css/global.css?v=1.0">
    <link rel="stylesheet" href="assets/css/admin.css?v=1.0">
</head>
<body style="background-color: #050505; color: #ffffff; margin: 0; padding: 0; font-family: sans-serif;">

    <!-- NAVEGACIÓN SUPERIOR DEL ADMIN RESPONSIVA -->
    <header class="admin-header" style="display: flex !important; flex-wrap: wrap !important; align-items: center !important; justify-content: space-between !important; width: 100% !important; padding: 10px 12px !important; box-sizing: border-box !important; background: #0a0a0a !important; border-bottom: 1px solid rgba(212, 175, 55, 0.3) !important;">
        <div class="logo-admin" style="display: flex !important; align-items: center !important; gap: 8px !important;">
            <a href="admin_dashboard.php" style="text-decoration: none !important; display: flex !important; align-items: center !important; gap: 6px !important;">
                <img src="assets/img/img_logo_be$t.png" alt="BE$T Logo" style="height: 28px !important; width: auto !important; object-fit: contain !important;">
                <span style="color: #d4af37 !important; font-weight: bold !important; font-size: 11px !important; letter-spacing: 1px !important; border: 1px solid #d4af37 !important; padding: 1px 5px !important; border-radius: 4px !important; background: rgba(212, 175, 55, 0.1) !important;">ADMIN</span>
            </a>
        </div>
        
        <nav class="admin-nav" style="display: flex !important; align-items: center !important; gap: 6px !important; flex-wrap: wrap !important; margin-top: 4px !important;">
            <a href="admin_dashboard.php" class="nav-link active" style="color: #d4af37 !important; text-decoration: none !important; font-size: 12px !important; padding: 4px 6px !important; font-weight: bold !important;">Panel</a>
            <a href="inventario.php" class="nav-link" style="color: #ffffff !important; text-decoration: none !important; font-size: 12px !important; padding: 4px 6px !important;">Inventario</a>
            <a href="reportes.php" class="nav-link" style="color: #ffffff !important; text-decoration: none !important; font-size: 12px !important; padding: 4px 6px !important;">Reportes</a>
            <a href="logout.php" class="nav-link logout-btn" style="color: #ff4d4d !important; text-decoration: none !important; font-size: 11px !important; border: 1px solid #ff4d4d !important; padding: 2px 6px !important; border-radius: 4px !important; margin-left: 2px !important;">Salir</a>
        </nav>
    </header>

    <div class="section-container" style="padding: 15px 10px !important; max-width: 100% !important; box-sizing: border-box !important;">
        
        <h1 class="gold-title" style="font-size: 1.5rem !important; text-align: center !important; margin-bottom: 15px !important; color: #d4af37 !important;">PANEL DE CONTROL</h1>

        <!-- TARJETAS DE MÉTRICAS RÁPIDAS -->
        <div class="metrics-grid" style="display: grid !important; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)) !important; gap: 10px !important; margin-bottom: 20px !important;">
            
            <!-- TARJETA VENTAS DE HOY -->
            <div class="metric-card" style="background: #111 !important; border: 1px solid rgba(212,175,55,0.2) !important; border-radius: 8px !important; padding: 10px !important; display: flex !important; align-items: center !important; gap: 10px !important;">
                <span class="metric-icon" style="font-size: 1.5rem !important;">💰</span>
                <div class="metric-info">
                    <h3 style="font-size: 11px !important; margin: 0 !important; color: #aaa !important; text-transform: uppercase !important;">Ventas Hoy</h3>
                    <p class="metric-value" style="font-size: 13px !important; font-weight: bold !important; margin: 2px 0 0 0 !important; color: #d4af37 !important;">$<?= number_format($ventas_hoy, 0, ',', '.') ?></p>
                </div>
            </div>

            <!-- TARJETA STOCK TOTAL -->
            <div class="metric-card" style="background: #111 !important; border: 1px solid rgba(212,175,55,0.2) !important; border-radius: 8px !important; padding: 10px !important; display: flex !important; align-items: center !important; gap: 10px !important;">
                <span class="metric-icon" style="font-size: 1.5rem !important;">📦</span>
                <div class="metric-info">
                    <h3 style="font-size: 11px !important; margin: 0 !important; color: #aaa !important; text-transform: uppercase !important;">Stock Total</h3>
                    <p class="metric-value" style="font-size: 13px !important; font-weight: bold !important; margin: 2px 0 0 0 !important; color: #ffffff !important;"><?= number_format($total_stock, 0, ',', '.') ?> pcs</p>
                </div>
            </div>

            <!-- TARJETA STOCK BAJO -->
            <div class="metric-card <?= ($alertas_stock > 0) ? 'card-warning' : '' ?>" style="background: #111 !important; border: 1px solid <?= ($alertas_stock > 0) ? '#ff4d4d' : 'rgba(212,175,55,0.2)' ?> !important; border-radius: 8px !important; padding: 10px !important; display: flex !important; align-items: center !important; gap: 10px !important;">
                <span class="metric-icon" style="font-size: 1.5rem !important;">⚠️</span>
                <div class="metric-info">
                    <h3 style="font-size: 11px !important; margin: 0 !important; color: #aaa !important; text-transform: uppercase !important;">Stock Bajo</h3>
                    <p class="metric-value" style="font-size: 13px !important; font-weight: bold !important; margin: 2px 0 0 0 !important; color: <?= ($alertas_stock > 0) ? '#ff4d4d' : '#ffffff' ?> !important;"><?= $alertas_stock ?> Prods</p>
                </div>
            </div>
        </div>

        <hr class="gold-divider" style="border: 0; border-top: 1px solid rgba(212,175,55,0.2); margin: 20px 0;">

        <!-- AVISO DE AUTOMATIZACIÓN DE OFERTAS -->
        <div class="content-box text-left" style="background: #111 !important; border: 1px solid rgba(212,175,55,0.2) !important; border-radius: 8px !important; padding: 15px !important; box-sizing: border-box !important; text-align: center;">
            <h2 class="section-subtitle" style="font-size: 1.1rem !important; margin: 0 0 8px 0 !important; color: #d4af37 !important;">⚡ Sistema de Ofertas Automáticas (24h)</h2>
            <p class="section-desc" style="font-size: 12px !important; color: #aaa !important; margin: 0; line-height: 1.5;">
                Las ofertas del día y la sección <strong>OF€RTAS</strong> en la página principal ahora rotan de manera completamente automática. Cada 24 horas el sistema selecciona aleatoriamente 2 productos y les aplica un <strong>30% de descuento</strong> junto con su cuenta regresiva. ¡Ya no necesitas configurarlos de forma manual!
            </p>
        </div>

    </div>

</body>
</html>