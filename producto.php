<?php
session_start();
include 'conexion.php';

// CONFIGURACIÓN DE ZONA HORARIA Y OFERTA FLASH DIARIA (SINCRONIZADO CON COLOMBIA)
date_default_timezone_set('America/Bogota');
$fecha_hoy_colombia = date('Y-m-d');
$semilla_diaria = strtotime($fecha_hoy_colombia);

// Obtener los IDs de los 2 productos en oferta flash de hoy basados en la semilla diaria
$query_ids_ofertas = "SELECT id FROM productos ORDER BY RAND($semilla_diaria) LIMIT 2";
$res_ids = mysqli_query($conexion, $query_ids_ofertas);
$ids_productos_oferta = [];
if ($res_ids) {
    while ($row = mysqli_fetch_assoc($res_ids)) {
        $ids_productos_oferta[] = intval($row['id']);
    }
}

// OBTENER DATOS DEL PRODUCTO
$id_producto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$producto = null;

if ($id_producto > 0) {
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $producto = $res->fetch_assoc();
    }
}

if (!$producto) {
    header('Location: catalogo.php');
    exit;
}

// VALIDAR SI ES UNA OFERTA FLASH DEL DÍA (30% DE DESCUENTO AUTOMÁTICO)
$es_oferta_flash = in_array(intval($producto['id']), $ids_productos_oferta);

// CÁLCULO DE DESCUENTO Y PRECIOS
$precio_original = floatval($producto['precio']);

if ($es_oferta_flash) {
    $porcentaje_desc = 30; // 30% fijo por la oferta flash diaria
} else {
    $porcentaje_desc = intval($producto['descuento'] ?? 0); // Descuento normal de la base de datos
}

if ($porcentaje_desc > 0) {
    $precio_final = $precio_original - ($precio_original * ($porcentaje_desc / 100));
} else {
    $precio_final = $precio_original;
}

$tallas_guardadas = !empty($producto['tallas']) ? $producto['tallas'] : 'S, M, L, XL';
$tallas_array = array_map('trim', array_map('strtoupper', explode(',', $tallas_guardadas)));
$stock_actual = intval($producto['stock']);
$esta_agotado = ($stock_actual <= 0);

// ENLACE DIRECTO A WHATSAPP
$numero_whatsapp = "573178500108";
$msg_wa = "Hola, me interesa la prenda " . $producto['nombre'];
if ($porcentaje_desc > 0) {
    $msg_wa .= " (Con " . $porcentaje_desc . "% OFF a $" . number_format($precio_final, 0, ',', '.') . " COP)";
} else {
    $msg_wa .= " por $" . number_format($precio_original, 0, ',', '.') . " COP";
}
$url_whatsapp = "https://wa.me/" . $numero_whatsapp . "?text=" . urlencode($msg_wa);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon / Icono de la pestaña -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon.png?v=2.0">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon.png?v=2.0">
    <link rel="shortcut icon" href="assets/img/favicon.png?v=2.0" type="image/x-icon">
    
    <!-- Icono para iPhone / iPad (iOS Safari) -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicon.png?v=2.0">
    
    <!-- Configuración de color de la barra del navegador móvil -->
    <meta name="theme-color" content="#000000">
    <title><?= htmlspecialchars($producto['nombre']) ?> | BE$T</title>
    <link rel="stylesheet" href="assets/css/global.css?v=1.0">
    <link rel="stylesheet" href="assets/css/header-footer.css?v=3.0">
    <link rel="stylesheet" href="assets/css/tienda.css?v=<?= time(); ?>">

    <!-- REGLAS DE CONTROL DIRECTO (CSS GRID 3 COLUMNAS Y AJUSTES DE IMAGEN) -->
    <style>
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
            text-align: center !important;
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
    
        /* Logo Agrandado en PC */
        .logo-img {
            height: 75px !important;
            max-height: 75px !important;
            width: auto !important;
            object-fit: contain !important;
            display: block !important;
        }
    
        /* Estilos del Botón Volver al Catálogo */
        .btn-back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            font-size: 13px;
            color: #d4af37 !important;
            border: 1px solid #d4af37;
            border-radius: 4px;
            text-decoration: none;
            white-space: nowrap;
            background: transparent;
            transition: all 0.2s ease;
        }
    
        .btn-back-link:hover {
            background: rgba(212, 175, 55, 0.1);
        }
    
        /* CONTENEDOR DE LA COLUMNA DE IMAGEN */
        .product-detail-img-col {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            box-sizing: border-box;
        }
    
        /* VITRINA PRINCIPAL DE DETALLE DEL PRODUCTO (PC) */
        .main-img-box {
            background-color: #000000 !important;
            background-image: url('assets/img/fondo_card_best.png') !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            padding: 15px;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100%;
            max-width: 100%;
            height: 480px !important; /* Altura suficiente para PC */
            aspect-ratio: 3 / 4 !important; /* Proporción 3:4 vertical */
            border-radius: 10px;
            margin: 0 auto;
            box-sizing: border-box;
            overflow: hidden !important;
        }
    
        #main-product-img {
            max-width: 100% !important;
            max-height: 100% !important;
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important; /* Previene cualquier aplastamiento o estiramiento */
            background: transparent !important;
            display: block !important;
            margin: auto !important;
        }

        /* ESTILOS PARA LA CAJA DE VISUALIZACIONES REALES */
        .live-views-box {
            background-color: #000000 !important;
            border: 1px solid #d4af37 !important;
            border-radius: 8px;
            padding: 10px 14px;
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #d4af37 !important;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.15);
        }

        .live-dot {
            width: 10px;
            height: 10px;
            background-color: #2ecc71;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px #2ecc71;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.25); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
    
        /* Ajustes para Celulares (< 768px) */
        @media (max-width: 768px) {
            .navbar {
                padding: 6px 10px !important;
                min-height: 60px !important;
                grid-template-columns: 45px 1fr 45px !important;
            }
    
            .logo-img {
                height: 46px !important;
                max-height: 46px !important;
                max-width: 135px !important;
            }
    
            .btn-back-link {
                padding: 6px 10px;
                border: none;
                font-size: 18px;
            }
    
            .btn-back-link .btn-text {
                display: none;
            }
    
            /* VITRINA EN MÓVILES (PROPORCIÓN RECTANGULAR ALTA) */
            .main-img-box {
                padding: 10px;
                height: 380px !important;
                aspect-ratio: 3 / 4 !important;
            }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="nav-left">
            <a href="catalogo.php" class="btn-back-link" title="Volver al catálogo">
                <span>←</span>
                <span class="btn-text">Volver al catálogo</span>
            </a>
        </div>
        <div class="nav-center">
            <a href="index.php" class="logo-best">
                <img src="assets/img/img_logo_be$t.png" alt="BE$T Logo" class="logo-img">
            </a>
        </div>
        <div class="nav-right">
            <!-- Espacio reservado para balance simétrico en Grid -->
        </div>
    </header>

    <main class="product-detail-container">
        <div class="product-detail-card">
            
            <!-- GALERÍA DE IMÁGENES -->
            <div class="product-detail-img-col">
                <div class="main-img-box">
                    <img id="main-product-img" src="uploads/productos_img/<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                </div>
            </div>

            <!-- DETALLES DE LA PRENDA -->
            <div class="product-detail-info-col">
                <h1 class="product-title-detail"><?= htmlspecialchars($producto['nombre']) ?></h1>
                
                <!-- ETIQUETA Y PRECIO CON DESCUENTO -->
                <div class="product-price-box" style="margin: 10px 0;">
                    <?php if ($porcentaje_desc > 0): ?>
                        <span style="text-decoration: line-through; color: #888; font-size: 16px; margin-right: 8px;">
                            $ <?= number_format($precio_original, 0, ',', '.') ?>
                        </span>
                        <span class="product-price-detail" style="color: #d4af37; font-weight: bold; font-size: 22px;">
                            $ <?= number_format($precio_final, 0, ',', '.') ?> COP
                        </span>
                        <span style="background: #e74c3c; color: #fff; font-size: 12px; font-weight: bold; padding: 3px 8px; border-radius: 4px; margin-left: 8px; vertical-align: middle;">
                            -<?= $porcentaje_desc ?>% OFF <?= $es_oferta_flash ? '(OFERTA FLASH)' : '' ?>
                        </span>
                    <?php else: ?>
                        <p class="product-price-detail">$ <?= number_format($precio_original, 0, ',', '.') ?> COP</p>
                    <?php endif; ?>
                </div>

                <!-- CAJA DE VISUALIZACIONES REALES INTERCAMBIABLES -->
                <div class="live-views-box">
                    <span class="live-dot"></span>
                    <span>🐐 <strong id="live-view-count" style="color: #d4af37; font-size: 16px;">12</strong> personas están viendo este producto ahora</span>
                </div>

                <!-- SELECTOR DE TALLAS INTERACTIVO -->
                <div class="size-selector-box">
                    <label>Selecciona tu Talla:</label>
                    <div class="size-options">
                        <?php 
                        $tallas_estandar = ['S', 'M', 'L', 'XL'];
                        $primera_talla_validada = '';
                        foreach ($tallas_estandar as $index => $talla): 
                            $existe = in_array($talla, $tallas_array);
                            $disponible = ($existe && !$esta_agotado);
                            
                            if ($disponible && empty($primera_talla_validada)) {
                                $primera_talla_validada = $talla;
                            }
                        ?>
                            <button 
                                type="button" 
                                class="btn-size <?= !$disponible ? 'disabled' : '' ?> <?= ($talla === $primera_talla_validada) ? 'active' : '' ?>" 
                                data-talla="<?= $talla ?>"
                                data-existe="<?= $existe ? '1' : '0' ?>"
                                data-stock="<?= $disponible ? $stock_actual : '0' ?>"
                                onclick="seleccionarTalla(this)"
                                <?= !$disponible ? 'style="opacity: 0.4; cursor: not-allowed;"' : '' ?>
                            >
                                <?= $talla ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div id="status-talla" class="status-box" style="display: <?= $esta_agotado ? 'block' : 'none' ?>; margin-top: 15px;">
                        <?php if ($esta_agotado): ?>
                            <span style="color: #e74c3c; font-weight: bold;">❌ Prenda agotada en inventario.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- FORMULARIO DIRIGIDO A AGREGAR_AL_CARRITO.PHP -->
                <form action="agregar_al_carrito.php" method="POST" style="margin-top: 20px;">
                    <!-- DATOS OCULTOS DEL PRODUCTO -->
                    <input type="hidden" name="id_producto" value="<?= $producto['id'] ?>">
                    <input type="hidden" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>">
                    <input type="hidden" name="precio" value="<?= $precio_final ?>">
                    <input type="hidden" name="precio_original" value="<?= $precio_original ?>">
                    <input type="hidden" name="descuento" value="<?= $porcentaje_desc ?>">
                    <input type="hidden" name="imagen" value="<?= htmlspecialchars($producto['imagen']) ?>">
                    
                    <!-- TALLA SELECCIONADA -->
                    <input type="hidden" name="talla" id="talla-seleccionada" value="<?= !empty($primera_talla_validada) ? $primera_talla_validada : 'S' ?>">

                    <!-- CONTROL DE CANTIDAD -->
                    <div class="quantity-selector-box" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: bold; margin-bottom: 8px;">Cantidad:</label>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <button type="button" class="btn-qty" onclick="modificarCantidad(-1)" <?= $esta_agotado ? 'disabled' : '' ?> style="width: 38px; height: 38px; background: #111; color: #d4af37; border: 1px solid #d4af37; font-size: 18px; font-weight: bold; border-radius: 6px; cursor: pointer;">-</button>
                            
                            <input type="number" name="cantidad" id="cantidad-prod" value="<?= $esta_agotado ? '0' : '1' ?>" min="<?= $esta_agotado ? '0' : '1' ?>" max="<?= $stock_actual ?>" readonly style="width: 55px; height: 38px; text-align: center; background: #000; color: #fff; border: 1px solid #333; border-radius: 6px; font-weight: bold; font-size: 16px;">
                            
                            <button type="button" class="btn-qty" onclick="modificarCantidad(1)" <?= $esta_agotado ? 'disabled' : '' ?> style="width: 38px; height: 38px; background: #111; color: #d4af37; border: 1px solid #d4af37; font-size: 18px; font-weight: bold; border-radius: 6px; cursor: pointer;">+</button>
                        </div>
                    </div>

                    <div class="product-description-box" style="margin-bottom: 20px;">
                        <h3>Descripción de la prenda</h3>
                        <p><?= !empty($producto['descripcion']) ? nl2br(htmlspecialchars($producto['descripcion'])) : 'Prenda exclusiva de la colección BE$T. Excelente calidad, diseño urbano y confección de alto nivel.'; ?></p>
                    </div>

                    <div class="product-actions-box" style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button type="submit" name="agregar_carrito" id="btn-submit-cart" class="btn-gold btn-add-cart-detail" <?= $esta_agotado ? 'disabled style="opacity: 0.5; cursor: not-allowed; background: #444; color: #888;"' : '' ?> style="flex: 1; min-width: 200px;">
                            <?= $esta_agotado ? 'PRENDA AGOTADA' : 'Añadir al Carrito' ?>
                        </button>

                        <a href="<?= $url_whatsapp ?>" id="btn-wa-link" target="_blank" class="btn-wa-direct" <?= $esta_agotado ? 'style="pointer-events: none; opacity: 0.5; flex: 1; min-width: 200px; background: #333; color: #777; text-align: center; padding: 12px 15px; border-radius: 6px; font-weight: bold; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;"' : 'style="flex: 1; min-width: 200px; background: #25d366; color: #fff; text-align: center; padding: 12px 15px; border-radius: 6px; font-weight: bold; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;"' ?>>
                            <span>📲</span> Pedir por WhatsApp
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <div class="trust-banner-container">
        <img src="assets/img/sellos-confianza.png" alt="Entidades de Comercio" class="trust-banner-img">
    </div>

    <div class="footer-legal-links">
        <p>
            © 2026, SHOP$$$ · 
            <a href="enlaces.php?seccion=privacidad">Política de privacidad</a> · 
            <a href="enlaces.php?seccion=reembolso">Política de reembolso</a> · 
            <a href="enlaces.php?seccion=contacto">Información de contacto</a> · 
            <a href="enlaces.php?seccion=terminos">Términos del servicio</a>
        </p>
    </div>

    <script>
    let maxStockPermitido = <?= $stock_actual ?>;

    // SCRIPT PARA ROTAR LAS VISUALIZACIONES DINÁMICAMENTE (12 - 14 - 20 - 10)
    const valoresVistas = [12, 14, 20, 10];
    let indiceActual = 0;

    function rotarVisualizaciones() {
        const spanVistas = document.getElementById('live-view-count');
        if (spanVistas) {
            indiceActual = (indiceActual + 1) % valoresVistas.length;
            spanVistas.innerText = valoresVistas[indiceActual];
        }
    }

    // Cambia de número cada 4 segundos de forma automática
    setInterval(rotarVisualizaciones, 4000);

    function seleccionarTalla(btn) {
        const existe = btn.getAttribute('data-existe') === '1';
        const stock = parseInt(btn.getAttribute('data-stock'));
        const talla = btn.getAttribute('data-talla');
        const statusBox = document.getElementById('status-talla');
        const btnSubmit = document.getElementById('btn-submit-cart');

        if (!existe || stock <= 0) {
            statusBox.style.display = 'block';
            statusBox.className = 'status-box status-err';
            statusBox.innerHTML = `<span style="color: #e74c3c; font-weight: bold;">❌ Talla ${talla} no disponible o agotada.</span>`;
            
            btnSubmit.disabled = true;
            btnSubmit.style.opacity = '0.5';
            btnSubmit.style.cursor = 'not-allowed';
            btnSubmit.style.background = '#444';
            btnSubmit.style.color = '#888';
            btnSubmit.innerText = 'TALLA AGOTADA';
            return;
        }

        const botones = document.querySelectorAll('.btn-size');
        botones.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        document.getElementById('talla-seleccionada').value = talla;
        statusBox.style.display = 'block';
        statusBox.className = 'status-box status-ok';
        statusBox.innerHTML = `<span style="color: #2ecc71; font-weight: bold;">✔ Talla ${talla} disponible (${stock} ${stock === 1 ? 'unidad' : 'unidades'}).</span>`;

        btnSubmit.disabled = false;
        btnSubmit.style.opacity = '1';
        btnSubmit.style.cursor = 'pointer';
        btnSubmit.style.background = '';
        btnSubmit.style.color = '';
        btnSubmit.innerText = 'Añadir al Carrito';
    }

    function modificarCantidad(cambio) {
        const inputCant = document.getElementById('cantidad-prod');
        let valorActual = parseInt(inputCant.value) || 0;
        let nuevoValor = valorActual + cambio;

        if (maxStockPermitido <= 0) return;

        if (nuevoValor >= 1 && nuevoValor <= maxStockPermitido) {
            inputCant.value = nuevoValor;
        }
    }
    </script>
</body>
</html>