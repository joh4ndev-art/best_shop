<?php
// 1. Iniciar la sesión al principio de todo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluimos la conexión a la base de datos
include 'conexion.php'; // Usa el nombre exacto de tu archivo de conexión

// ====================================================================
// CONFIGURACIÓN DE ZONA HORARIA Y OFERTA FLASH DIARIA (SINCRONIZADO)
// ====================================================================
date_default_timezone_set('America/Bogota');
$fecha_hoy_colombia = date('Y-m-d');
$semilla_diaria = strtotime($fecha_hoy_colombia);

// Obtener los IDs de los 2 productos en oferta flash de hoy
$query_ids_ofertas = "SELECT id FROM productos ORDER BY RAND($semilla_diaria) LIMIT 2";
$res_ids = mysqli_query($conexion, $query_ids_ofertas);
$ids_productos_oferta = [];
if ($res_ids) {
    while ($row = mysqli_fetch_assoc($res_ids)) {
        $ids_productos_oferta[] = intval($row['id']);
    }
}
// ====================================================================

// Capturar el término de búsqueda enviado desde la barra superior
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!empty($busqueda)) {
    // Si hay búsqueda, filtramos por nombre o descripción de producto
    $term = "%{$busqueda}%";
    $stmt = mysqli_prepare($conexion, "SELECT * FROM productos WHERE nombre LIKE ? OR descripcion LIKE ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "ss", $term, $term);
    mysqli_stmt_execute($stmt);
    $result_catalogo = mysqli_stmt_get_result($stmt);
} else {
    // Consulta para traer TODOS los productos ordenados del más reciente al más antiguo
    $query_catalogo = "SELECT * FROM productos ORDER BY id DESC";
    $result_catalogo = mysqli_query($conexion, $query_catalogo);
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
    <title>Catálogo Completo | BE$T</title>
    
    <!-- Hojas de estilo modulares para el Catálogo -->
    <link rel="stylesheet" href="assets/css/global.css?v=1.0">
    <link rel="stylesheet" href="assets/css/header-footer.css?v=3.0">
    <link rel="stylesheet" href="assets/css/tienda.css?v=1.0">

    <!-- REGLAS CSS PERSONALIZADAS: ALINEACIÓN HORIZONTAL Y FRASE CON LÍNEAS DORADAS -->
    <style>
        /* 1. NAVEGADOR PRINCIPAL EN GRID 3 COLUMNAS SIMÉTRICAS */
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
            gap: 10px !important;
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

        /* Estilos del Botón Volver al inicio */
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

        /* CAJA VITRINA DE PRODUCTO (PC Y BASE) */
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

        /* IMAGEN DEL PRODUCTO SIN DEFORMACIONES */
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

        /* ANIMACIÓN 3D PARA EL LOGO DEL CATÁLOGO */
        .logo-3d-spin {
            max-width: 180px;
            height: auto;
            display: inline-block;
            animation: girar3D 6s linear infinite;
            transform-style: preserve-3d;
        }

        @keyframes girar3D {
            0% {
                transform: rotateY(0deg);
            }
            100% {
                transform: rotateY(360deg);
            }
        }

        /* FRASE EN CURSIVA CON LÍNEAS DORADAS LATERALES */
        .subtitulo-lineas-doradas {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin: 15px 0 30px 0;
            width: 100%;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .subtitulo-lineas-doradas span {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-style: italic;
            font-size: 18px;
            color: #e5c158;
            white-space: nowrap;
            letter-spacing: 0.5px;
            text-shadow: 0 0 5px rgba(212, 175, 55, 0.3);
        }

        .subtitulo-lineas-doradas::before,
        .subtitulo-lineas-doradas::after {
            content: "";
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #d4af37, transparent);
        }

        /* CORRECCIÓN DEFINITIVA DE ALTURA DE VITRINA EN MÓVILES */
        @media (max-width: 768px) {
            .navbar {
                padding: 6px 10px !important;
                min-height: 60px !important;
                grid-template-columns: 1fr auto 1fr !important;
            }

            .logo-img {
                height: 42px !important;
                max-height: 42px !important;
                max-width: 120px !important;
            }

            .btn-back-link {
                padding: 6px 8px;
                border: none;
                font-size: 18px;
            }

            .btn-back-link .btn-text {
                display: none;
            }

            .subtitulo-lineas-doradas span {
                font-size: 14px;
            }

            /* TARJETA DEL PRODUCTO EN MÓVILES */
            .card-gold {
                width: 100% !important;
                max-width: 340px !important;
                margin: 0 auto !important;
            }

            /* VITRINA: ALTA Y RECTANGULAR (PROPORCIÓN DE CATÁLOGO DE MODA 3:4) */
            .product-card-img-box {
                height: 380px !important; /* Altura fija amplia para móviles */
                aspect-ratio: 3 / 4 !important; /* Proporción estándar de ropa */
                padding: 10px !important;
            }

            .product-card-img-box img {
                width: 100% !important;
                height: 100% !important;
                max-width: 100% !important;
                max-height: 100% !important;
                object-fit: contain !important; /* Garantiza cero deformación */
            }
        }
    </style>
</head>
<body>

    <!-- Header Personalizado para Catálogo -->
    <header class="navbar">
        <div class="nav-left">
            <a href="index.php" class="btn-back-link" title="Volver al inicio">
                <span>←</span>
                <span class="btn-text">Volver al inicio</span>
            </a>
        </div>
        
        <div class="nav-center">
            <a href="index.php" class="logo-best">
                <img src="assets/img/img_logo_be$t.png" alt="BE$T Logo" class="logo-img">
            </a>
        </div>
        
        <div class="nav-right">
            <form action="catalogo.php" method="GET" class="search-nav-form" style="display: inline-flex; align-items: center; background: rgba(255,255,255,0.05); border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 20px; padding: 2px 6px;">
                <input type="text" name="q" placeholder="Buscar..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" style="background: transparent; border: none; color: #fff; padding: 2px 4px; font-size: 12px; outline: none; width: 90px;">
                <button type="submit" class="nav-icon-btn" title="Buscar productos" style="background: transparent; border: none; cursor: pointer; display: flex; align-items: center; padding: 0 2px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>

            <?php 
                $cant_carrito = 0;
                if (!empty($_SESSION['carrito'])) {
                    $cant_carrito = array_sum(array_column($_SESSION['carrito'], 'cantidad'));
                }
            ?>
            <a href="carrito.php" class="nav-icon-btn" title="Ver Carrito" style="position: relative; display: flex; align-items: center; text-decoration: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <?php if ($cant_carrito > 0): ?>
                    <span style="position: absolute; top: -6px; right: -8px; background: #e74c3c; color: #fff; font-size: 10px; font-weight: bold; width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <?= $cant_carrito; ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </header>

    <main class="section-container">
        <h1 class="gold-title">CATÁLOGO COMPLETO</h1>

        <!-- LOGO CON ANIMACIÓN 3D -->
        <div style="text-align: center; margin: 15px 0 10px 0; perspective: 1000px;">
            <img src="assets/img/logo_3d_be$t.png" alt="BE$T Logo 3D" class="logo-3d-spin">
        </div>

        <!-- FRASE EN CURSIVA CON LÍNEAS DORADAS LATERALES -->
        <div class="subtitulo-lineas-doradas">
            <span>Explora toda nuestra colección exclusiva</span>
        </div>

        <div class="products-row" style="flex-wrap: wrap; justify-content: center; gap: 20px;">
            <?php if ($result_catalogo && mysqli_num_rows($result_catalogo) > 0): ?>
                <?php while($producto = mysqli_fetch_assoc($result_catalogo)): 
                    // VALIDAR SI ES UNA OFERTA FLASH DEL DÍA
                    $id_prod = intval($producto['id']);
                    $es_oferta_flash = in_array($id_prod, $ids_productos_oferta);
                    
                    // CÁLCULO DE DESCUENTO
                    $precio_original = floatval($producto['precio']);
                    
                    if ($es_oferta_flash) {
                        $descuento = 30; // 30% fijo por la oferta flash diaria
                    } else {
                        $descuento = intval($producto['descuento'] ?? 0); // Descuento normal de la base de datos
                    }
                    
                    $precio_final = ($descuento > 0) ? $precio_original * (1 - ($descuento / 100)) : $precio_original;
                ?>
                    <div class="card-gold">
                        <!-- Enlace que lleva a la vista de detalle enviando el ID -->
                        <a href="producto.php?id=<?= $producto['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                            <!-- USO DE LA CLASE CORRECTA DEL CSS PARA MOSTRAR EL FONDO -->
                            <div class="product-card-img-box">
                                <img src="uploads/productos_img/<?= htmlspecialchars($producto['imagen']); ?>" alt="<?= htmlspecialchars($producto['nombre']); ?>">
                            </div>
                            <h3 class="product-name" style="margin-top: 10px;"><?= htmlspecialchars($producto['nombre']); ?></h3>
                        </a>

                        <!-- PRECIO CON SOPORTE DE DESCUENTO -->
                        <p class="product-price">
                            <?php if ($descuento > 0): ?>
                                <span style="text-decoration: line-through; color: #888; font-size: 13px; margin-right: 6px;">
                                    $ <?= number_format($precio_original, 0, ',', '.'); ?>
                                </span>
                                <span style="color: #d4af37; font-weight: bold;">
                                    $ <?= number_format($precio_final, 0, ',', '.'); ?> COP
                                </span>
                            <?php else: ?>
                                $ <?= number_format($precio_original, 0, ',', '.'); ?> COP
                            <?php endif; ?>
                        </p>

                        <!-- BOTÓN O INSIGNIA DE DESCUENTO -->
                        <?php if ($descuento > 0): ?>
                            <a href="producto.php?id=<?= $producto['id']; ?>" class="btn-descuento-tag" style="display: block; width: 100%; background: #e74c3c; color: #fff; text-align: center; padding: 10px 0; border-radius: 4px; font-weight: bold; text-decoration: none; font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase;">
                                <?= $es_oferta_flash ? '⚡ OFERTA FLASH -30%' : 'DESCUENTO DEL ' . $descuento . '%'; ?>
                            </a>
                        <?php else: ?>
                            <a href="producto.php?id=<?= $producto['id']; ?>" class="btn-gold" style="display: block; width: 100%; text-align: center; padding: 10px 0; text-decoration: none; font-size: 13px; font-weight: bold; letter-spacing: 0.5px; box-sizing: border-box;">
                                VER PRENDA
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #fff; text-align: center;">No hay productos disponibles en el catálogo.</p>
            <?php endif; ?>
        </div>

        <div class="btn-center-container" style="margin-top: 40px; text-align: center;">
            <a href="index.php" class="btn-gold">VOLVER AL INICIO</a>
        </div>
    </main>

    <!-- Banner de Sellos de Confianza y Entidades -->
    <div class="trust-banner-container">
        <img src="assets/img/sellos-confianza.png" alt="Entidades de Comercio y Gobierno Colombia" class="trust-banner-img">
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

</body>
</html>