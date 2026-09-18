<?php

// Iniciar sesión si ne está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Incluimos la conexión a la base de datos
include 'conexion.php'; 

// Configurar la zona horaria oficial de Colombia
date_default_timezone_set('America/Bogota');

// Obtener la fecha actual de Colombia (Formato: YYYY-MM-DD) para fijar la aleatoriedad diaria
$fecha_hoy_colombia = date('Y-m-d');
$semilla_diaria = strtotime($fecha_hoy_colombia);

// NUEVA SECCIÓN OFERTAS: Traer 2 productos aleatorios fijos para todo el día usando la fecha como semilla
$query_ofertas = "SELECT * FROM productos ORDER BY RAND($semilla_diaria) LIMIT 2";
$result_ofertas = mysqli_query($conexion, $query_ofertas);

// CAPTURAR LOS IDS DE LA OFERTA FLASH PARA VALIDACIÓN CRUZADA EN TIENDA
$ids_productos_oferta = [];
// Como vamos a recorrer $result_ofertas abajo, clonamos la consulta o guardamos los IDs de inmediato:
$query_ids_ofertas = "SELECT id FROM productos ORDER BY RAND($semilla_diaria) LIMIT 2";
$res_ids = mysqli_query($conexion, $query_ids_ofertas);
if ($res_ids) {
    while ($r = mysqli_fetch_assoc($res_ids)) {
        $ids_productos_oferta[] = intval($r['id']);
    }
}

// PUNTO B: Traer 8 productos para la sección Nuestra Tienda
$query_tienda = "SELECT * FROM productos ORDER BY id DESC LIMIT 8";
$result_tienda = mysqli_query($conexion, $query_tienda);

// Contar el total de prendas en el carrito
$total_prendas_carrito = 0;
if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $total_prendas_carrito += $item['cantidad'];
    }
}
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
    <title>BE$T - Tienda Oficial</title>
    
    <!-- Hojas de estilo modulares (Vista Cliente) -->
    <link rel="stylesheet" href="assets/css/global.css?v=1.0">
    <link rel="stylesheet" href="assets/css/header-footer.css?v=3.0">
    <link rel="stylesheet" href="assets/css/tienda.css?v=1.0">

    <!-- REGLAS DE CONTROL DIRECTO PARA LA NAVBAR, TAMAÑO DEL LOGO Y CONTADOR -->
    <style>
        /* 1. NAVEGADOR PRINCIPAL EN GRID 3 COLUMNAS SIMÉTRicas */
        .navbar {
            display: grid !important;
            grid-template-columns: 1fr auto 1fr !important; /* 3 columnas: Izq, Centro exacto, Der */
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
    
        /* Columna Izquierda */
        .nav-left {
            display: flex !important;
            justify-content: flex-start !important;
            align-items: center !important;
        }
    
        /* Columna Central (Matemáticamente centrada en la pantalla) */
        .nav-center {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            text-align: center !important;
        }
    
        /* Columna Derecha */
        .nav-right {
            display: flex !important;
            justify-content: flex-end !important;
            align-items: center !important;
            gap: 10px !important;
        }
    
        .logo-best {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
    
        /* Tamaño del Logo en PC */
        .logo-img {
            height: 70px !important;
            max-height: 70px !important;
            width: auto !important;
            object-fit: contain !important;
            display: block !important;
        }
    
        /* ESTILO PARA LAS CAJAS CON EL FONDO MARCO BE$T (PC Y BASE) */
        .product-card-img-box {
            background-color: #000000 !important;
            background-image: url('assets/img/fondo_card_best.png?v=5.0') !important;
            background-size: cover !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
            border: 1px solid rgba(212, 175, 55, 0.4) !important;
            border-radius: 10px !important;
            width: 100% !important;
            height: 320px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 20px !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
        }
    
        /* IMAGEN DEL PRODUCTO PROPORCIONADA */
        .product-card-img-box img {
            width: auto !important;
            height: auto !important;
            max-width: 90% !important;
            max-height: 90% !important;
            object-fit: contain !important;
            background: transparent !important;
            background-color: transparent !important;
            display: block !important;
        }

        /* ESTILOS PARA EL CONTADOR DE OFERTAS DE 24 HORAS Y TÍTULO */
        .ofertas-header-container {
            text-align: center;
            margin: 15px 0 20px 0;
        }

        .gold-ofertas-title {
            color: #d4af37 !important;
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-shadow: 0 0 12px rgba(212, 175, 55, 0.4);
            margin-bottom: 0px;
        }

        .countdown-wrapper {
            display: inline-flex;
            gap: 15px;
            background: rgba(20, 20, 20, 0.9);
            border: 1px solid #d4af37;
            padding: 12px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.6);
            margin-top: 15px;
        }

        .countdown-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 45px;
        }

        .countdown-number {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
            font-family: monospace;
        }

        .countdown-label {
            color: #d4af37;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .countdown-separator {
            color: #d4af37;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            padding-bottom: 12px;
        }
    
        /* 2. REGLAS PARA CELULARES Y TABLETS (< 768px) CON CENTRADO PERFECTO */
        @media (max-width: 768px) {
            .navbar {
                padding: 6px 12px !important;
                min-height: 60px !important;
                grid-template-columns: 1fr auto 1fr !important;
            }
    
            .logo-img {
                height: 42px !important;
                max-height: 42px !important;
                max-width: 130px !important;
            }
    
            /* Ajuste del formulario de búsqueda móvil */
            .search-nav-form {
                display: flex !important;
                align-items: center !important;
                position: relative !important;
            }

            .search-nav-form input {
                width: 0px !important;
                padding: 0 !important;
                opacity: 0;
                transition: all 0.3s ease;
                border: 1px solid rgba(212, 175, 55, 0.5);
                border-radius: 4px;
                background: #000;
                color: #fff;
            }
    
            /* Clase activa mediante JS para despliegue fluido */
            .search-nav-form.search-open input {
                width: 110px !important;
                padding: 4px 8px !important;
                opacity: 1;
                margin-right: 5px;
            }
    
            .card-gold {
                width: 100% !important;
                max-width: 340px !important;
                margin: 0 auto !important;
            }
    
            .product-card-img-box {
                height: 380px !important;
                aspect-ratio: 3 / 4 !important;
                padding: 10px !important;
            }
    
            .product-card-img-box img {
                width: 100% !important;
                height: 100% !important;
                max-width: 100% !important;
                max-height: 100% !important;
                object-fit: contain !important;
            }

            .gold-ofertas-title {
                font-size: 1.7rem;
            }

            .countdown-wrapper {
                padding: 8px 15px;
                gap: 10px;
                margin-top: 10px;
            }

            .countdown-number {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Barra de Navegación -->
    <header class="navbar">
        <!-- Izquierda: Botón Hamburguesa -->
        <div class="nav-left">
            <button class="menu-toggle-btn" id="openMenuBtn" aria-label="Abrir menú">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>
    
        <!-- Centro: Logo BE$T (Perfectamente Centrado) -->
        <div class="nav-center">
            <a href="index.php" class="logo-best">
                <img src="assets/img/img_logo_be$t.png" alt="BE$T Logo" class="logo-img">
            </a>
        </div>
    
        <!-- Derecha: Carrito + Lupa de Búsqueda -->
        <div class="nav-right">
            <!-- Ícono Carrito de Compras -->
            <a href="carrito.php" class="nav-icon-btn cart-icon-wrapper" title="Ver mi carrito">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <?php if (isset($total_prendas_carrito) && $total_prendas_carrito > 0): ?>
                    <span class="cart-badge-count"><?= $total_prendas_carrito ?></span>
                <?php endif; ?>
            </a>
    
            <!-- FORMULARIO LUPA BÚSQUEDA CORREGIDO -->
            <form action="catalogo.php" method="GET" class="search-nav-form" id="searchForm">
                <input type="text" name="q" id="searchInput" placeholder="Buscar..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                <button type="button" class="nav-icon-btn" id="searchBtn" title="Buscar productos">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>
        </div>
    </header>

    <!-- Menú Desplegable Lateral (Off-canvas) -->
    <div class="side-menu-overlay" id="menuOverlay"></div>
    <aside class="side-menu" id="sideMenu">
        <div class="side-menu-header">
            <span class="side-menu-title">MENÚ</span>
            <button class="close-menu-btn" id="closeMenuBtn">&times;</button>
        </div>
        <nav class="side-menu-nav">
            <a href="#inicio" class="menu-link">Inicio</a>
            <a href="#ofertas" class="menu-link">Ofertas</a>
            <a href="#tienda" class="menu-link">Tienda</a>
            <a href="#redes" class="menu-link">Redes Sociales</a>
            <a href="carrito.php" class="menu-link">Mi Carrito</a>
            <a href="soporte.php" class="menu-link">Opiniones y Preguntas</a>
        </nav>
    </aside>

    <!-- SECCIÓN INICIO: TÍTULO OF€RTAS (ARRIBA) -> LOGO 3D (MEDIO) -> CONTADOR 24H (ABAJO) -->
    <section id="inicio" class="section-container" style="padding-top: 15px; text-align: center;">
        <!-- 1. Título Dorado OF€RTAS en la parte superior -->
        <div class="ofertas-header-container" style="margin-bottom: 10px;">
            <h2 class="gold-ofertas-title">OF€RTAS</h2>
        </div>

        <!-- 2. Logo 3D Animado en el medio -->
        <div class="logo-3d-wrapper" style="margin: 5px 0;">
            <img src="assets/img/logo_3d_be$t.png" alt="BE$T 3D Logo" class="logo-3d-animated">
        </div>

        <!-- 3. Contador sincronizado con la medianoche de Colombia -->
        <div class="ofertas-header-container" style="margin-top: 5px; margin-bottom: 15px;">
            <div class="countdown-wrapper" id="countdownTimer">
                <div class="countdown-box">
                    <span class="countdown-number" id="hours">00</span>
                    <span class="countdown-label">Horas</span>
                </div>
                <div class="countdown-separator">:</div>
                <div class="countdown-box">
                    <span class="countdown-number" id="minutes">00</span>
                    <span class="countdown-label">Min</span>
                </div>
                <div class="countdown-separator">:</div>
                <div class="countdown-box">
                    <span class="countdown-number" id="seconds">00</span>
                    <span class="countdown-label">Seg</span>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN: PRODUCTOS EN OFERTA FLASH (2 productos aleatorios fijos del día con 30% de descuento) -->
    <section id="ofertas" class="section-container" style="padding-top: 0px;">
        <div class="products-row">
            <?php if ($result_ofertas && mysqli_num_rows($result_ofertas) > 0): ?>
                <?php while($oferta_flash = mysqli_fetch_assoc($result_ofertas)): 
                    $precio_orig = floatval($oferta_flash['precio']);
                    $desc_porcentaje = 30; // 30% de descuento fijo para esta sección
                    $precio_oferta = $precio_orig * (1 - ($desc_porcentaje / 100));
                ?>
                    <div class="card-gold">
                        <a href="producto.php?id=<?= $oferta_flash['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                            <div class="product-card-img-box">
                                <img src="uploads/productos_img/<?= $oferta_flash['imagen']; ?>" alt="<?= htmlspecialchars($oferta_flash['nombre']); ?>">
                            </div>
                            <h3 class="product-name" style="margin-top: 10px;"><?= htmlspecialchars($oferta_flash['nombre']); ?></h3>
                        </a>
                        
                        <p class="product-price">
                            <span style="text-decoration: line-through; color: #888; font-size: 13px; margin-right: 6px;">
                                $ <?= number_format($precio_orig, 0, ',', '.'); ?>
                            </span>
                            <span style="color: #d4af37; font-weight: bold;">
                                $ <?= number_format($precio_oferta, 0, ',', '.'); ?> COP
                            </span>
                        </p>

                        <a href="producto.php?id=<?= $oferta_flash['id']; ?>" class="btn-descuento-tag" style="display: block; width: 100%; background: #e74c3c; color: #fff; text-align: center; padding: 10px 0; border-radius: 4px; font-weight: bold; text-decoration: none; font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase;">
                            OFERTA FLASH 30% OFF
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #fff;">No hay ofertas disponibles en este momento.</p>
            <?php endif; ?>
        </div>
    </section>

    <hr class="gold-divider">

    <!-- 2. SECCIÓN: TIENDA (Muestra de 8 productos) -->
    <section id="tienda" class="section-container">
        <h2 class="gold-title">NUESTRA TIENDA</h2>
        
        <div class="products-row">
            <?php if ($result_tienda && mysqli_num_rows($result_tienda) > 0): ?>
                <?php while($oferta = mysqli_fetch_assoc($result_tienda)): 
                    $id_prod_tienda = intval($oferta['id']);
                    $es_oferta_flash_tienda = in_array($id_prod_tienda, $ids_productos_oferta);
                    
                    $precio_original_tienda = floatval($oferta['precio']);
                    
                    // Si está en la oferta flash, aplica 30%, de lo contrario toma el descuento de la BD
                    if ($es_oferta_flash_tienda) {
                        $descuento_tienda = 30;
                    } else {
                        $descuento_tienda = intval($oferta['descuento'] ?? 0);
                    }
                    
                    $precio_final_tienda = ($descuento_tienda > 0) ? $precio_original_tienda * (1 - ($descuento_tienda / 100)) : $precio_original_tienda;
                ?>
                    <div class="card-gold">
                        <a href="producto.php?id=<?= $oferta['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                            <div class="product-card-img-box">
                                <img src="uploads/productos_img/<?= $oferta['imagen']; ?>" alt="<?= htmlspecialchars($oferta['nombre']); ?>">
                            </div>
                            <h3 class="product-name" style="margin-top: 10px;"><?= htmlspecialchars($oferta['nombre']); ?></h3>
                        </a>
                        
                        <p class="product-price">
                            <?php if ($descuento_tienda > 0): ?>
                                <span style="text-decoration: line-through; color: #888; font-size: 13px; margin-right: 6px;">
                                    $ <?= number_format($precio_original_tienda, 0, ',', '.'); ?>
                                </span>
                                <span style="color: #d4af37; font-weight: bold;">
                                    $ <?= number_format($precio_final_tienda, 0, ',', '.'); ?> COP
                                </span>
                            <?php else: ?>
                                $ <?= number_format($precio_original_tienda, 0, ',', '.'); ?> COP
                            <?php endif; ?>
                        </p>

                        <?php if ($descuento_tienda > 0): ?>
                            <a href="producto.php?id=<?= $oferta['id']; ?>" class="btn-descuento-tag" style="display: block; width: 100%; background: #e74c3c; color: #fff; text-align: center; padding: 10px 0; border-radius: 4px; font-weight: bold; text-decoration: none; font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase;">
                                <?= $es_oferta_flash_tienda ? '⚡ OFERTA FLASH -30%' : 'DESCUENTO DEL ' . $descuento_tienda . '%'; ?>
                            </a>
                        <?php else: ?>
                            <a href="producto.php?id=<?= $oferta['id']; ?>" class="btn-gold" style="display: block; width: 100%; text-align: center; padding: 10px 0; text-decoration: none; font-size: 13px; font-weight: bold; letter-spacing: 0.5px; box-sizing: border-box;">
                                VER PRENDA
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No hay productos registrados en la tienda.</p>
            <?php endif; ?>
        </div>

        <div class="btn-center-container" style="margin-top: 25px;">
            <a href="catalogo.php" class="btn-gold">VER CATÁLOGO COMPLETO</a>
        </div>
    </section>

    <hr class="gold-divider">

    <!-- 3. SECCIÓN FINAL: REDES SOCIALES -->
    <section id="redes" class="section-container">
        <h2 class="gold-title">SÍGUENOS Y CONTÁCTANOS</h2>
        <div class="social-icons-wrapper">
            <a href="https://www.instagram.com/best.shop1414" target="_blank" class="social-icon-btn" title="Instagram">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                </svg>
            </a>
            <a href="https://www.facebook.com/share/1GHjn4eaQG/?mibextid=wwXIfr" target="_blank" class="social-icon-btn" title="Facebook">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                </svg>
            </a>
            <a href="https://www.tiktok.com/@best.shop1414" target="_blank" class="social-icon-btn" title="TikTok">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
                </svg>
            </a>
            <a href="https://wa.me/573178500108" target="_blank" class="social-icon-btn" title="WhatsApp">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                </svg>
            </a>
        </div>
    </section>

    <!-- Script para menú lateral, control de búsqueda móvil y temporizador exacto a la medianoche de Colombia -->
    <script>
        const openMenuBtn = document.getElementById('openMenuBtn');
        const closeMenuBtn = document.getElementById('closeMenuBtn');
        const sideMenu = document.getElementById('sideMenu');
        const menuOverlay = document.getElementById('menuOverlay');
        const menuLinks = document.querySelectorAll('.menu-link');

        function openMenu() {
            sideMenu.classList.add('active');
            menuOverlay.classList.add('active');
        }

        function closeMenu() {
            sideMenu.classList.remove('active');
            menuOverlay.classList.remove('active');
        }

        openMenuBtn.addEventListener('click', openMenu);
        closeMenuBtn.addEventListener('click', closeMenu);
        menuOverlay.addEventListener('click', closeMenu);

        menuLinks.forEach(link => {
            link.addEventListener('click', closeMenu);
        });

        // CONTROL DE BÚSQUEDA EN MÓVILES
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');

        searchBtn.addEventListener('click', function (e) {
            if (window.innerWidth <= 768) {
                if (!searchForm.classList.contains('search-open')) {
                    e.preventDefault();
                    searchForm.classList.add('search-open');
                    searchInput.focus();
                } else {
                    if (searchInput.value.trim() !== "") {
                        searchForm.submit();
                    }
                }
            } else {
                searchForm.submit();
            }
        });

        document.addEventListener('click', function (e) {
            if (!searchForm.contains(e.target) && window.innerWidth <= 768) {
                searchForm.classList.remove('search-open');
            }
        });

        // TEMPORIZADOR BASADO EN LA HORA ESTÁNDAR DE COLOMBIA (RESTANTE HASTA LAS 00:00)
        function startColombiaMidnightCountdown() {
            const x = setInterval(function() {
                // Obtener la hora actual exacta en la zona horaria de Colombia usando toLocaleString
                const options = { timeZone: 'America/Bogota', year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: false };
                const colombiaDateStr = new Intl.DateTimeFormat('en-US', options).format(new Date());
                const colombiaNow = new Date(colombiaDateStr);

                // Crear la fecha de la próxima medianoche (00:00:00 del día siguiente en Colombia)
                let colombiaMidnight = new Date(colombiaNow);
                colombiaMidnight.setDate(colombiaMidnight.getDate() + 1);
                colombiaMidnight.setHours(0, 0, 0, 0);

                // Calcular la distancia en milisegundos hasta la medianoche
                let distance = colombiaMidnight.getTime() - colombiaNow.getTime();

                if (distance < 0) {
                    distance = 0;
                }

                let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById("hours").innerText = String(hours).padStart(2, '0');
                document.getElementById("minutes").innerText = String(minutes).padStart(2, '0');
                document.getElementById("seconds").innerText = String(seconds).padStart(2, '0');
            }, 1000);
        }

        startColombiaMidnightCountdown();
    </script>

    <!-- Banner de Sellos de Confianza y Entidades -->
    <div class="trust-banner-container">
        <img src="assets/img/sellos-confianza.png" alt="Entidades de Comercio y Gobierno Colombia" class="trust-banner-img">
    </div>

    <div class="footer-legal-links">
        <p>
            © 2026 SHOP$$$ · 
            <a href="enlaces.php?seccion=privacidad">Política de privacidad</a> · 
            <a href="enlaces.php?seccion=reembolso">Política de reembolso</a> · 
            <a href="enlaces.php?seccion=contacto">Información de contacto</a> · 
            <a href="enlaces.php?seccion=terminos">Términos del servicio</a>
        </p>
    </div>

</body>
</html>