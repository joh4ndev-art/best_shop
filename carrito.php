<?php
session_start();

$telefono_whatsapp = "573178500108"; 

// -------------------------------------------------------------
// ACCIONES DEL CARRITO
// -------------------------------------------------------------
if (isset($_GET['eliminar'])) {
    $key = $_GET['eliminar'];
    if (isset($_SESSION['carrito'][$key])) {
        unset($_SESSION['carrito'][$key]);
    }
    header('Location: carrito.php');
    exit();
}

if (isset($_GET['vaciar']) && $_GET['vaciar'] == 1) {
    unset($_SESSION['carrito']);
    header('Location: carrito.php');
    exit();
}

if (isset($_GET['modificar_cant'])) {
    $key = $_GET['key'] ?? '';
    $modo = $_GET['modificar_cant'];
    
    if (isset($_SESSION['carrito'][$key])) {
        if ($modo === 'mas') {
            $_SESSION['carrito'][$key]['cantidad'] += 1;
        } elseif ($modo === 'menos') {
            $_SESSION['carrito'][$key]['cantidad'] -= 1;
            if ($_SESSION['carrito'][$key]['cantidad'] <= 0) {
                unset($_SESSION['carrito'][$key]);
            }
        }
    }
    header('Location: carrito.php');
    exit();
}

// -------------------------------------------------------------
// TOTALES, AHORRO Y MENSAJE WHATSAPP
// -------------------------------------------------------------
$total_unidades = 0;
$gran_total = 0;
$total_ahorro = 0;
$texto_whatsapp = "👋 ¡Hola! Me gustaría confirmar la compra del siguiente pedido en *BE\$T*:\n\n";

if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $key => $item) {
        $precio_cobrado = floatval($item['precio']);
        $precio_original = isset($item['precio_original']) ? floatval($item['precio_original']) : $precio_cobrado;
        $descuento = isset($item['descuento']) ? intval($item['descuento']) : 0;
        
        $subtotal = $precio_cobrado * $item['cantidad'];
        $subtotal_original = $precio_original * $item['cantidad'];
        
        $total_unidades += $item['cantidad'];
        $gran_total += $subtotal;
        $total_ahorro += ($subtotal_original - $subtotal);
        
        $talla_str = !empty($item['talla']) ? " (Talla: {$item['talla']})" : "";
        $desc_str = ($descuento > 0) ? " [{$descuento}% OFF]" : "";

        $texto_whatsapp .= "• *{$item['nombre']}*{$talla_str}{$desc_str}\n  Cant: {$item['cantidad']} x $" . number_format($precio_cobrado, 0, ',', '.') . " = $" . number_format($subtotal, 0, ',', '.') . " COP\n\n";
    }
    
    $texto_whatsapp .= "📦 *Total Unidades:* {$total_unidades}\n";
    if ($total_ahorro > 0) {
        $texto_whatsapp .= "🎉 *Ahorro Total por Descuento:* $" . number_format($total_ahorro, 0, ',', '.') . " COP\n";
    }
    $texto_whatsapp .= "💰 *Monto Total a Pagar:* $" . number_format($gran_total, 0, ',', '.') . " COP\n\n";
    $texto_whatsapp .= "Quedo atento para coordinar el pago y envío. ¡Muchas gracias!";
}

$url_whatsapp = "https://wa.me/{$telefono_whatsapp}?text=" . urlencode($texto_whatsapp);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- FAVICON Y LOGO PARA NAVEGADORES MÓVILES (iOS Y ANDROID) -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon.png?v=2.0">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon.png?v=2.0">
    <link rel="shortcut icon" href="assets/img/favicon.png?v=2.0" type="image/x-icon">
    
    <!-- Icono para iPhone / iPad (iOS Safari) -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicon.png?v=2.0">
    
    <!-- Configuración de color de la barra del navegador móvil -->
    <meta name="theme-color" content="#000000">
    <title>BE$T - Mi Carrito de Compras</title>
    
    <link rel="stylesheet" href="assets/css/global.css?v=1.0">
    <link rel="stylesheet" href="assets/css/header-footer.css?v=3.0">
    <link rel="stylesheet" href="assets/css/carrito.css?v=1.0">

    <!-- REGLAS DE CONTROL DIRECTO (CSS GRID) -->
    <style>
        /* Navbar en PC con CSS Grid 3 columnas */
        .navbar {
            display: grid !important;
            grid-template-columns: 1fr auto 1fr !important;
            align-items: center !important;
            width: 100% !important;
            padding: 10px 15px !important;
            box-sizing: border-box !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 1000 !important;
            background: rgba(10, 10, 10, 0.95) !important;
            min-height: 80px !important;
        }

        .nav-left {
            display: flex !important;
            justify-content: flex-start !important;
            align-items: center !important;
        }

        .nav-center {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }

        .nav-right {
            display: flex !important;
            justify-content: flex-end !important;
            align-items: center !important;
        }

        .logo-best {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Logo agrandado en PC */
        .logo-img {
            height: 75px !important;
            max-height: 75px !important;
            width: auto !important;
            object-fit: contain !important;
            display: block !important;
        }

        /* Botón de regresar */
        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            font-size: 13px;
            color: #d4af37;
            border: 1px solid #d4af37;
            border-radius: 4px;
            text-decoration: none;
            white-space: nowrap;
            background: transparent;
            transition: all 0.2s ease;
        }

        .btn-back-home:hover {
            background: rgba(212, 175, 55, 0.1);
        }

        /* Ajustes para Celulares */
        @media (max-width: 768px) {
            .navbar {
                padding: 6px 10px !important;
                min-height: 60px !important;
                grid-template-columns: 45px 1fr 45px !important; /* Equilibrio perfecto */
            }

            .logo-img {
                height: 46px !important;
                max-height: 46px !important;
                max-width: 135px !important;
            }

            .btn-back-home {
                padding: 6px 10px;
                border: none;
                font-size: 18px;
            }

            /* Ocultar el texto en móvil para que no choque con el logo */
            .btn-back-home .btn-text {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Header estructurado en una sola etiqueta -->
    <header class="navbar">
        <div class="nav-left">
            <a href="index.php" class="btn-back-home" title="Regresar al Inicio">
                <span>←</span>
                <span class="btn-text">Regresar al Inicio</span>
            </a>
        </div>
        
        <div class="nav-center">
            <a href="index.php" class="logo-best">
                <img src="assets/img/img_logo_be$t.png" alt="BE$T Logo" class="logo-img">
            </a>
        </div>
        
        <div class="nav-right">
            <!-- Espacio reservado para equilibrar la columna izquierda -->
        </div>
    </header>

    <div class="cart-container">
        <h1 class="gold-title" style="margin-bottom: 30px; font-size: 28px; letter-spacing: 2px;">TU CARRITO DE COMPRAS</h1>

        <?php if (!empty($_SESSION['carrito'])): ?>
            <div style="overflow-x: auto;">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Prenda</th>
                            <th>Talla</th>
                            <th>Precio Unitario</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['carrito'] as $key => $item): 
                            $precio_cobrado = floatval($item['precio']);
                            $precio_orig = isset($item['precio_original']) ? floatval($item['precio_original']) : $precio_cobrado;
                            $descuento = isset($item['descuento']) ? intval($item['descuento']) : 0;
                            $subtotal_item = $precio_cobrado * $item['cantidad'];
                        ?>
                            <tr>
                                <td>
                                    <div class="cart-product-info">
                                        <img src="uploads/productos_img/<?= !empty($item['imagen']) ? htmlspecialchars($item['imagen']) : 'default.jpg' ?>" class="cart-img" alt="Prenda">
                                        <div>
                                            <strong><?= htmlspecialchars($item['nombre']) ?></strong>
                                            <?php if ($descuento > 0): ?>
                                                <br><span style="background: #e74c3c; color: #fff; font-size: 11px; font-weight: bold; padding: 2px 6px; border-radius: 3px; display: inline-block; margin-top: 3px;">
                                                    <?= $descuento ?>% OFF
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><span style="color: #d4af37; font-weight: bold;"><?= htmlspecialchars($item['talla'] ?? 'N/A') ?></span></td>
                                <td>
                                    <?php if ($descuento > 0): ?>
                                        <span style="text-decoration: line-through; color: #888; font-size: 12px; display: block;">
                                            $<?= number_format($precio_orig, 0, ',', '.') ?> COP
                                        </span>
                                    <?php endif; ?>
                                    <span style="color: #d4af37; font-weight: bold;">
                                        $<?= number_format($precio_cobrado, 0, ',', '.') ?> COP
                                    </span>
                                </td>
                                <td>
                                    <div class="qty-control">
                                        <a href="carrito.php?modificar_cant=menos&key=<?= urlencode($key) ?>" class="btn-qty-sm">-</a>
                                        <span style="min-width: 20px; text-align: center; font-weight: bold;"><?= $item['cantidad'] ?></span>
                                        <a href="carrito.php?modificar_cant=mas&key=<?= urlencode($key) ?>" class="btn-qty-sm">+</a>
                                    </div>
                                </td>
                                <td><strong style="color: #fff;">$<?= number_format($subtotal_item, 0, ',', '.') ?> COP</strong></td>
                                <td>
                                    <a href="carrito.php?eliminar=<?= urlencode($key) ?>" class="btn-danger-link" onclick="return confirm('¿Quitar esta prenda?')">🗑️ Quitar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-summary-wrapper">
                <div class="summary-box">
                    <div class="summary-row">
                        <span style="color: #aaa;">Total Prendas:</span>
                        <span><strong><?= $total_unidades ?> pcs</strong></span>
                    </div>

                    <?php if (isset($total_ahorro) && $total_ahorro > 0): ?>
                        <div class="summary-row" style="color: #2ecc71;">
                            <span>Ahorro por Oferta:</span>
                            <span><strong>-$<?= number_format($total_ahorro, 0, ',', '.') ?> COP</strong></span>
                        </div>
                    <?php endif; ?>

                    <div class="summary-row total">
                        <span>Total Pedido:</span>
                        <span>$<?= number_format($gran_total, 0, ',', '.') ?> COP</span>
                    </div>

                    <a href="<?= $url_whatsapp ?>" target="_blank" class="btn-ws">
                        📲 FINALIZAR COMPRA EN WHATSAPP
                    </a>
                </div>

                <div class="bottom-nav-actions">
                    <a href="catalogo.php" class="btn-outline-gold">← VER CATÁLOGO</a>
                    <a href="carrito.php?vaciar=1" onclick="return confirm('¿Vaciar todo el carrito?')" class="btn-danger-link">Vaciar Carrito</a>
                </div>
            </div>

        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; background: #0a0a0a; border: 1px solid #222; border-radius: 8px;">
                <p style="font-size: 18px; color: #888; margin-bottom: 20px;">Tu carrito está actualmente vacío.</p>
                <a href="index.php" class="btn-outline-gold">EXPLORAR TIENDA</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>