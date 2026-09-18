<?php
// Obtener la sección enviada por la URL; si no viene ninguna, usa 'privacidad' por defecto
$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : 'privacidad';
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
    <title>Shop$$$ - Información Legal</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/header-footer.css?v=3.0">
    <link rel="stylesheet" href="assets/css/tienda.css">

    <!-- REGLAS DE CONTROL DIRECTO (CSS GRID 3 COLUMNAS) -->
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

        /* Estilos del Botón Volver a la Tienda */
        .btn-back-link {
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

        .btn-back-link:hover {
            background: rgba(212, 175, 55, 0.1);
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

            /* Ocultar texto en celular para mantener alineación limpia */
            .btn-back-link .btn-text {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Header / Navegación -->
    <header class="navbar">
        <div class="nav-left">
            <a href="index.php" class="btn-back-link" title="Volver a la tienda">
                <span>←</span>
                <span class="btn-text">Volver a la tienda</span>
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

    <!-- Contenido Dinámico de Páginas Legales -->
    <main class="legal-container">
        <div class="legal-card">
            <?php if ($seccion === 'privacidad'): ?>
                <h1>POLÍTICA DE PRIVACIDAD</h1>
                <p>En <strong>BE$T / Shop$$$</strong> nos tomamos muy en serio la seguridad de tus datos. La información recopilada en este sitio web se utiliza exclusivamente para el procesamiento de pedidos, facturación y envíos.</p>
                <p>No compartimos ni vendemos tu información personal a terceros bajo ninguna circunstancia.</p>

            <?php elseif ($seccion === 'reembolso'): ?>
                <h1>POLÍTICA DE REEMBOLSO Y CAMBIOS</h1>
                <p>Dispones de hasta <strong>5 días hábiles</strong> después de recibir tu prenda para solicitar un cambio o garantía, siempre y cuando la prenda no presente signos de uso y conserve sus etiquetas originales.</p>
                <p>Los gastos de envío por garantías son asumidos por la tienda previa verificación del estado del producto.</p>

            <?php elseif ($seccion === 'contacto'): ?>
                <h1>INFORMACIÓN DE CONTACTO</h1>
                <p>¿Tienes alguna duda sobre tu pedido o necesitas asesoría personalizada?</p>
                <p><strong>WhatsApp:</strong> +57 317 850 0108</p>
                <p><strong>Correo electrónico:</strong> soporte@bestshop.com</p>
                <p><strong>Horarios de atención:</strong> Lunes a Sábado de 9:00 AM a 7:00 PM.</p>

            <?php elseif ($seccion === 'terminos'): ?>
                <h1>TÉRMINOS DEL SERVICIO</h1>
                <p>Al realizar una compra en <strong>Shop$$$</strong>, aceptas los términos y condiciones estipulados para la venta de prendas y accesorios.</p>
                <p>Los precios están sujetos a cambios sin previo aviso y las promociones están limitadas hasta agotar existencias.</p>

            <?php else: ?>
                <h1>INFORMACIÓN NO ENCONTRADA</h1>
                <p>La sección solicitada no existe o ha sido movida.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Enlaces Legales / Footer -->
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