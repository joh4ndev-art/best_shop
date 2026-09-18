<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Control de acceso y cierre por inactividad
if (!isset($_SESSION['es_dueno']) || $_SESSION['es_dueno'] !== true) {
    header('Location: login.php');
    exit();
}
if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php?expirado=1');
    exit();
}
$_SESSION['ultimo_acceso'] = time();

// Incluir la conexión global configurada con las credenciales de Hostinger
require_once 'conexion.php';

if (!$conexion) {
    die("Error de conexión a la base de datos.");
}

// -------------------------------------------------------------
// CONFIGURACIÓN DE ZONA HORARIA (COLOMBIA / GMT-5)
// -------------------------------------------------------------
date_default_timezone_set('America/Bogota');
$conexion->query("SET time_zone = '-05:00'");

$mensaje = '';

// -------------------------------------------------------------
// 1. REGISTRAR NUEVA VENTA Y DESCONTAR STOCK CON TALLA
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_venta'])) {
    $id_producto = intval($_POST['id_producto']);
    $talla = trim($_POST['talla']);
    $cantidad = intval($_POST['cantidad']);

    // Consultar precio y stock actual del producto
    $stmt_prod = $conexion->prepare("SELECT nombre, precio, stock FROM productos WHERE id = ?");
    $stmt_prod->bind_param("i", $id_producto);
    $stmt_prod->execute();
    $res_prod = $stmt_prod->get_result()->fetch_assoc();

    if ($res_prod) {
        if ($res_prod['stock'] >= $cantidad) {
            $total_venta = $res_prod['precio'] * $cantidad;

            // 1. Guardar registro en la tabla ventas incluyendo talla, producto_id y cantidad
            $stmt_venta = $conexion->prepare("INSERT INTO ventas (producto_id, talla, cantidad, total) VALUES (?, ?, ?, ?)");
            $stmt_venta->bind_param("isid", $id_producto, $talla, $cantidad, $total_venta);
            $stmt_venta->execute();

            // 2. Descontar el stock en la tabla productos
            $nuevo_stock = $res_prod['stock'] - $cantidad;
            $stmt_stock = $conexion->prepare("UPDATE productos SET stock = ? WHERE id = ?");
            $stmt_stock->bind_param("ii", $nuevo_stock, $id_producto);
            $stmt_stock->execute();

            $mensaje = "¡Venta de {$cantidad}x {$res_prod['nombre']} (Talla: {$talla}) por $" . number_format($total_venta, 0, ',', '.') . " COP registrada con éxito!";
        } else {
            $mensaje = "Error: Stock insuficiente para realizar la venta (Disponibles: " . $res_prod['stock'] . ").";
        }
    }
}

// -------------------------------------------------------------
// 2. CÁLCULO DE TOTALES (DÍA Y MES)
// -------------------------------------------------------------
$mes_actual = date('Y-m');

// Total Vendido Hoy (usando CURDATE() sincronizado con GMT-5)
$res_hoy = $conexion->query("SELECT SUM(total) as total FROM ventas WHERE DATE(fecha) = CURDATE()");
$total_hoy = ($res_hoy && $row = $res_hoy->fetch_assoc()) ? ($row['total'] ?? 0) : 0;

// Total Vendido en el Mes
$res_mes = $conexion->query("SELECT SUM(total) as total FROM ventas WHERE DATE_FORMAT(fecha, '%Y-%m') = '$mes_actual'");
$total_mes = ($res_mes && $row = $res_mes->fetch_assoc()) ? ($row['total'] ?? 0) : 0;

// Consultar Historial de Ventas Recientes con JOIN a la tabla de productos
$ventas_historial = $conexion->query("SELECT v.id, p.nombre, v.talla, v.cantidad, v.total, v.fecha 
                                     FROM ventas v 
                                     JOIN productos p ON v.producto_id = p.id 
                                     ORDER BY v.fecha DESC LIMIT 15");

// Productos con stock disponible para el selector
$productos_opt = $conexion->query("SELECT id, nombre, precio, stock FROM productos WHERE stock > 0 ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- Favicon / Icono de la pestaña -->
    <!-- FAVICON Y LOGO PARA NAVEGADORES MÓVILES (iOS Y ANDROID) -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon.png?v=2.0">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon.png?v=2.0">
    <link rel="shortcut icon" href="assets/img/favicon.png?v=2.0" type="image/x-icon">
    
    <!-- Icono para iPhone / iPad (iOS Safari) -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicon.png?v=2.0">
    
    <!-- Configuración de color de la barra del navegador móvil -->
    <meta name="theme-color" content="#000000">
    <title>BE$T - Reportes y Ventas</title>
    <link rel="stylesheet" href="assets/css/global.css?v=1.0">
    <link rel="stylesheet" href="assets/css/admin.css?v=1.0">
</head>
<body style="background-color: #050505; color: #ffffff; margin: 0; padding: 0; font-family: sans-serif;">

    <?php 
    $pagina_actual = basename($_SERVER['SCRIPT_NAME']); 
    ?>

    <!-- NAVEGACIÓN SUPERIOR DEL ADMIN RESPONSIVA -->
    <header class="admin-header" style="display: flex !important; flex-wrap: wrap !important; align-items: center !important; justify-content: space-between !important; width: 100% !important; padding: 10px 12px !important; box-sizing: border-box !important; background: #0a0a0a !important; border-bottom: 1px solid rgba(212, 175, 55, 0.3) !important;">
        <div class="logo-admin" style="display: flex !important; align-items: center !important; gap: 8px !important;">
            <a href="admin_dashboard.php" style="text-decoration: none !important; display: flex !important; align-items: center !important; gap: 6px !important;">
                <img src="assets/img/img_logo_be$t.png" alt="BE$T Logo" style="height: 28px !important; width: auto !important; object-fit: contain !important;">
                <span style="color: #d4af37 !important; font-weight: bold !important; font-size: 11px !important; letter-spacing: 1px !important; border: 1px solid #d4af37 !important; padding: 1px 5px !important; border-radius: 4px !important; background: rgba(212, 175, 55, 0.1) !important;">ADMIN</span>
            </a>
        </div>
        <nav class="admin-nav" style="display: flex !important; align-items: center !important; gap: 6px !important; flex-wrap: wrap !important; margin-top: 4px !important;">
            <a href="admin_dashboard.php" class="nav-link <?= ($pagina_actual == 'admin_dashboard.php') ? 'active' : '' ?>" style="color: #ffffff !important; text-decoration: none !important; font-size: 12px !important; padding: 4px 6px !important;">Panel</a>
            <a href="inventario.php" class="nav-link <?= ($pagina_actual == 'inventario.php') ? 'active' : '' ?>" style="color: #ffffff !important; text-decoration: none !important; font-size: 12px !important; padding: 4px 6px !important;">Inventario</a>
            <a href="reportes.php" class="nav-link <?= ($pagina_actual == 'reportes.php') ? 'active' : '' ?>" style="color: #d4af37 !important; text-decoration: none !important; font-size: 12px !important; padding: 4px 6px !important; font-weight: bold !important;">Reportes</a>
            <a href="logout.php" class="nav-link logout-btn" style="color: #ff4d4d !important; text-decoration: none !important; font-size: 11px !important; border: 1px solid #ff4d4d !important; padding: 2px 6px !important; border-radius: 4px !important; margin-left: 2px !important;">Salir</a>
        </nav>
    </header>

    <div class="section-container" style="padding: 15px 10px !important; max-width: 100% !important; box-sizing: border-box !important;">
        <h1 class="gold-title" style="font-size: 1.5rem !important; text-align: center !important; margin-bottom: 15px !important; color: #d4af37 !important;">REPORTES Y VENTAS</h1>

        <?php if (!empty($mensaje)): ?>
            <p style="text-align: center; color: #4caf50; font-weight: bold; margin-bottom: 20px; font-size: 13px;"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <!-- RESUMEN MONETARIO -->
        <div class="metrics-grid" style="display: grid !important; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) !important; gap: 12px !important; margin-bottom: 20px !important;">
            <div class="metric-card" style="background: #111 !important; border: 1px solid rgba(212,175,55,0.2) !important; border-radius: 8px !important; padding: 12px !important; display: flex !important; align-items: center !important; gap: 10px !important;">
                <span class="metric-icon" style="font-size: 1.5rem !important;">📅</span>
                <div class="metric-info">
                    <h3 style="font-size: 11px !important; color: #aaa !important; margin: 0 !important; text-transform: uppercase !important;">Ventas de Hoy</h3>
                    <p class="metric-value" style="font-size: 13px !important; color: #25d366 !important; font-weight: bold !important; margin: 4px 0 0 0 !important;">$<?= number_format($total_hoy, 0, ',', '.') ?> COP</p>
                </div>
            </div>

            <div class="metric-card" style="background: #111 !important; border: 1px solid rgba(212,175,55,0.2) !important; border-radius: 8px !important; padding: 12px !important; display: flex !important; align-items: center !important; gap: 10px !important;">
                <span class="metric-icon" style="font-size: 1.5rem !important;">📊</span>
                <div class="metric-info">
                    <h3 style="font-size: 11px !important; color: #aaa !important; margin: 0 !important; text-transform: uppercase !important;">Ventas del Mes</h3>
                    <p class="metric-value" style="font-size: 13px !important; color: #d4af37 !important; font-weight: bold !important; margin: 4px 0 0 0 !important;">$<?= number_format($total_mes, 0, ',', '.') ?> COP</p>
                </div>
            </div>
        </div>

        <!-- FORMULARIO DE REGISTRO RÁPIDO DE VENTAS -->
        <div class="content-box text-left margin-bottom-30" style="background: #111 !important; border: 1px solid rgba(212,175,55,0.2) !important; border-radius: 8px !important; padding: 12px !important; box-sizing: border-box !important; margin-bottom: 25px !important;">
            <h2 class="section-subtitle" style="font-size: 1.1rem !important; margin: 0 0 6px 0 !important; color: #d4af37 !important;">💵 Registrar Venta Manual</h2>
            <p class="section-desc" style="font-size: 11px !important; color: #888 !important; margin-bottom: 15px !important;">Selecciona la prenda y la talla vendida para descontar el stock e ingresar el dinero al reporte.</p>

            <form action="reportes.php" method="POST" class="inventory-form">
                <div class="form-grid" style="display: grid !important; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)) !important; gap: 10px !important;">
                    <div class="input-group" style="grid-column: 1 / -1;">
                        <label style="display: block; font-size: 12px; color: #ccc; margin-bottom: 4px;">Seleccionar Prenda</label>
                        <select name="id_producto" required class="select-gold" style="width: 100%; box-sizing: border-box; background: #000; color: #fff; border: 1px solid #333; border-radius: 6px; padding: 8px; font-size: 12px;">
                            <option value="">-- Seleccionar Prenda --</option>
                            <?php if ($productos_opt && $productos_opt->num_rows > 0): ?>
                                <?php while ($p = $productos_opt->fetch_assoc()): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= htmlspecialchars($p['nombre']) ?> - $<?= number_format($p['precio'], 0, ',', '.') ?> (Disp: <?= $p['stock'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label style="display: block; font-size: 12px; color: #ccc; margin-bottom: 4px;">Talla Vendida</label>
                        <select name="talla" required class="select-gold" style="width: 100%; box-sizing: border-box; background: #000; color: #fff; border: 1px solid #333; border-radius: 6px; padding: 8px; font-size: 12px;">
                            <option value="">-- Talla --</option>
                            <option value="S">S</option>
                            <option value="M">M</option>
                            <option value="L">L</option>
                            <option value="XL">XL</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label style="display: block; font-size: 12px; color: #ccc; margin-bottom: 4px;">Cantidad</label>
                        <input type="number" name="cantidad" min="1" value="1" required style="width: 100%; box-sizing: border-box; background: #000; color: #fff; border: 1px solid #333; border-radius: 6px; padding: 8px; font-size: 12px;">
                    </div>
                </div>

                <div class="btn-center-container" style="margin-top: 15px; width: 100%;">
                    <button type="submit" name="registrar_venta" class="btn-gold" style="width: 100% !important; background: linear-gradient(135deg, #d4af37, #aa820a) !important; color: #000 !important; font-weight: bold !important; border: none !important; padding: 12px !important; border-radius: 6px !important; cursor: pointer !important; font-size: 13px !important;">
                        REGISTRAR VENTA
                    </button>
                </div>
            </form>
        </div>

        <!-- HISTORIAL DE TRANSACCIONES CON NAVEGACIÓN FLUIDA -->
        <div class="content-box text-left" style="background: #111 !important; border: 1px solid rgba(212,175,55,0.2) !important; border-radius: 8px !important; padding: 12px !important; box-sizing: border-box !important;">
            <h2 class="section-subtitle" style="font-size: 1.1rem !important; margin: 0 0 15px 0 !important; color: #d4af37 !important;">📋 Historial de Últimas Ventas</h2>
            <div class="table-responsive" style="width: 100% !important; overflow-x: auto !important; -webkit-overflow-scrolling: touch !important;">
                <table class="gold-table" style="width: 100% !important; border-collapse: collapse !important; font-size: 12px !important; min-width: 500px !important;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(212, 175, 55, 0.3); text-align: left; color: #d4af37;">
                            <th style="padding: 8px;">ID</th>
                            <th style="padding: 8px;">Prenda</th>
                            <th style="padding: 8px;">Talla</th>
                            <th style="padding: 8px;">Cant.</th>
                            <th style="padding: 8px;">Total</th>
                            <th style="padding: 8px;">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ventas_historial && $ventas_historial->num_rows > 0): ?>
                            <?php while ($v = $ventas_historial->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td style="padding: 8px; color: #888;">#<?= $v['id'] ?></td>
                                    <td style="padding: 8px;"><strong style="color: #fff;"><?= htmlspecialchars($v['nombre']) ?></strong></td>
                                    <td style="padding: 8px;"><span style="color: #d4af37; font-weight: bold;"><?= htmlspecialchars($v['talla'] ?? 'N/A') ?></span></td>
                                    <td style="padding: 8px;"><?= $v['cantidad'] ?> pcs</td>
                                    <td style="padding: 8px;"><strong style="color: #25d366;">$<?= number_format($v['total'], 0, ',', '.') ?></strong></td>
                                    <td style="padding: 8px; color: #aaa; font-size: 11px;"><?= date('d/m/Y h:i A', strtotime($v['fecha'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #777; padding: 20px;">
                                    No hay ventas registradas aún.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>