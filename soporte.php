<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// BANCO DE 10 TESTIMONIOS CON PRODUCTOS Y FOTOS DE CLIENTES
$banco_testimonios = [
    [
        "nombre" => "Carlos Mendoza",
        "estrellas" => 5,
        "comentario" => "La calidad de la tela es brutal y la horma queda perfecta. Llegó a Medellín en solo 2 días.",
        "prenda" => "Camiseta Oversize Black",
        "foto_cliente" => "https://i.pravatar.cc/100?img=11",
        "foto_producto" => "uploads/productos_img/1723908861_prueba_descuento.png"
    ],
    [
        "nombre" => "Santiago Gómez",
        "estrellas" => 5,
        "comentario" => "Excelente atención por WhatsApp. Me asesoraron bien con la talla y la prenda superó mis expectativas.",
        "prenda" => "Hoodie BE$T Gold Edition",
        "foto_cliente" => "https://i.pravatar.cc/100?img=12",
        "foto_producto" => "uploads/productos_img/1723908861_prueba_descuento.png"
    ],
    [
        "nombre" => "Andrés Felipe",
        "estrellas" => 5,
        "comentario" => "100% recomendados. La prenda se siente de altísima gama y el empaque vino impecable.",
        "prenda" => "Camisa Nike Sport",
        "foto_cliente" => "https://i.pravatar.cc/100?img=33",
        "foto_producto" => "uploads/productos_img/1723908861_prueba_descuento.png"
    ],
    [
        "nombre" => "Mateo Rivera",
        "estrellas" => 4,
        "comentario" => "Muy buena ropa urbana, el estampado del logo 3D se ve genial en vivo.",
        "prenda" => "Camisa Adidas Classic",
        "foto_cliente" => "https://i.pravatar.cc/100?img=60",
        "foto_producto" => "uploads/productos_img/1723908861_prueba_descuento.png"
    ],
    [
        "nombre" => "Daniel Valencia",
        "estrellas" => 5,
        "comentario" => "Superó mis expectativas. Ya es el tercer pedido que hago con BE$T y nunca fallan.",
        "prenda" => "Bermuda Streetwear",
        "foto_cliente" => "https://i.pravatar.cc/100?img=68",
        "foto_producto" => "uploads/productos_img/1723908861_prueba_descuento.png"
    ],
    [
        "nombre" => "Camilo Torres",
        "estrellas" => 5,
        "comentario" => "Envío rápido a Cali. El diseño y la textura del material son de otro nivel.",
        "prenda" => "Camiseta Gold Standard",
        "foto_cliente" => "https://i.pravatar.cc/100?img=15",
        "foto_producto" => "uploads/productos_img/1723908861_prueba_descuento.png"
    ],
    [
        "nombre" => "Alejandro Osorio",
        "estrellas" => 5,
        "comentario" => "Puntuales en la entrega y la calidad es la mejor que he comprado en tiendas online.",
        "prenda" => "Hoodie Black Luxury",
        "foto_cliente" => "https://i.pravatar.cc/100?img=53",
        "foto_producto" => "uploads/productos_img/1723908861_prueba_descuento.png"
    ],
    [
        "nombre" => "Sebastian Cruz",
        "estrellas" => 5,
        "comentario" => "Totalmente satisfecho con la compra. El fit es exactamente como se muestra en las fotos.",
        "prenda" => "Camiseta Oversize White",
        "foto_cliente" => "https://i.pravatar.cc/100?img=3",
        "foto_producto" => "uploads/productos_img/1723908861_prueba_descuento.png"
    ],
    [
        "nombre" => "Juan Pablo Ríos",
        "estrellas" => 4,
        "comentario" => "Buena calidad de confección. Llegó a tiempo para mi viaje.",
        "prenda" => "Jogger Street Luxury",
        "foto_cliente" => "https://i.pravatar.cc/100?img=17",
        "foto_producto" => "uploads/productos_img/1723908861_prueba_descuento.png"
    ],
    [
        "nombre" => "Kevin Arango",
        "estrellas" => 5,
        "comentario" => "La mejor marca urbana del momento. Los detalles dorados se ven increíbles.",
        "prenda" => "Camiseta BE$T Edition",
        "foto_cliente" => "https://i.pravatar.cc/100?img=57",
        "foto_producto" => "uploads/productos_img/1723908861_prueba_descuento.png"
    ]
];

// SELECCIONAR 3 ÍNDICES ALEATORIOS
$keys_aleatorias = array_rand($banco_testimonios, 3);
$testimonios_mostrar = [
    $banco_testimonios[$keys_aleatorias[0]],
    $banco_testimonios[$keys_aleatorias[1]],
    $banco_testimonios[$keys_aleatorias[2]]
];
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
    <title>Soporte y Opiniones - BE$T</title>
    <!-- Hojas de estilo modulares para Soporte -->
    <link rel="stylesheet" href="assets/css/global.css?v=1.0">
    <link rel="stylesheet" href="assets/css/header-footer.css?v=3.0">
    <link rel="stylesheet" href="assets/css/tienda.css?v=1.0">

    <!-- REGLAS DE CONTROL DIRECTO PARA LA NAVBAR Y TAMAÑO DEL LOGO -->
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
            gap: 10px !important;
        }

        .logo-best {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Tamaño del logo en Escritorio/PC */
        .logo-img {
            height: 75px !important;
            max-height: 75px !important;
            width: auto !important;
            object-fit: contain !important;
            display: block !important;
        }

        /* Ajustes para Celulares */
        @media (max-width: 768px) {
            .navbar {
                padding: 6px 10px !important;
                min-height: 60px !important;
                grid-template-columns: 40px 1fr 40px !important;
            }

            .logo-img {
                height: 46px !important;
                max-height: 46px !important;
                max-width: 135px !important;
            }
        }
    </style>
</head>
<body>

    <!-- Header y Menú Lateral (Misma barra Grid) -->
    <header class="navbar">
        <!-- Izquierda: Botón Hamburguesa -->
        <div class="nav-left">
            <button class="menu-toggle-btn" id="openMenuBtn" aria-label="Abrir menú" style="background: transparent; border: none; padding: 4px; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Centro: Logo BE$T -->
        <div class="nav-center">
            <a href="index.php" class="logo-best">
                <img src="assets/img/img_logo_be$t.png" alt="BE$T Logo" class="logo-img">
            </a>
        </div>

        <!-- Derecha: Espacio reservado para balance simétrico del Grid -->
        <div class="nav-right"></div>
    </header>

    <!-- Menú Desplegable Lateral -->
    <div class="side-menu-overlay" id="menuOverlay"></div>
    <aside class="side-menu" id="sideMenu">
        <div class="side-menu-header">
            <span class="side-menu-title">MENÚ</span>
            <button class="close-menu-btn" id="closeMenuBtn">&times;</button>
        </div>
        <nav class="side-menu-nav">
            <a href="index.php" class="menu-link">← Volver a la Tienda</a>
            <a href="#comentarios" class="menu-link">Opiniones de Clientes</a>
            <a href="#preguntas" class="menu-link">Preguntas Frecuentes</a>
        </nav>
    </aside>

    <!-- 1. SECCIÓN: COMENTARIOS Y REVIEWS -->
    <section id="comentarios" class="section-container">
        <h1 class="gold-title">CALIFICACIONES REALES</h1>
        
        <div class="content-box">
            <!-- Tarjeta de Resumen de Calificaciones -->
            <div class="reviews-summary-grid">
                
                <!-- Columna Izquierda: Promedio y Avatares -->
                <div class="rating-overview">
                    <div class="avatars-group">
                        <img src="https://i.pravatar.cc/100?img=33" alt="Cliente 1">
                        <img src="https://i.pravatar.cc/100?img=12" alt="Cliente 2">
                        <img src="https://i.pravatar.cc/100?img=60" alt="Cliente 3">
                    </div>
                    <div class="overview-stars">★★★★★</div>
                    <div class="overview-score">4.9/5 - 2000+ Calificaciones</div>
                    <div class="overview-subtext">De Clientes Satisfechos</div>
                </div>

                <!-- Columna Derecha: Desglose de Estrellas -->
                <div class="rating-bars">
                    <div class="bar-row">
                        <span class="stars-label">★★★★★</span>
                        <div class="progress-bg"><div class="progress-fill" style="width: 95%;"></div></div>
                        <span class="count-label">1904</span>
                    </div>
                    <div class="bar-row">
                        <span class="stars-label">★★★★<span class="star-off">★</span></span>
                        <div class="progress-bg"><div class="progress-fill" style="width: 10%;"></div></div>
                        <span class="count-label">109</span>
                    </div>
                    <div class="bar-row">
                        <span class="stars-label">★★★<span class="star-off">★★</span></span>
                        <div class="progress-bg"><div class="progress-fill" style="width: 3%;"></div></div>
                        <span class="count-label">12</span>
                    </div>
                    <div class="bar-row">
                        <span class="stars-label">★★<span class="star-off">★★★</span></span>
                        <div class="progress-bg"><div class="progress-fill" style="width: 0%;"></div></div>
                        <span class="count-label">0</span>
                    </div>
                    <div class="bar-row">
                        <span class="stars-label">★<span class="star-off">★★★★</span></span>
                        <div class="progress-bg"><div class="progress-fill" style="width: 0%;"></div></div>
                        <span class="count-label">0</span>
                    </div>
                </div>
            </div>

            <!-- TARJETAS DE COMENTARIOS ALEATORIOS DINÁMICOS -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-top: 30px;">
                <?php foreach ($testimonios_mostrar as $rev): ?>
                    <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
                        
                        <div>
                            <!-- Encabezado Cliente -->
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                <img src="<?= $rev['foto_cliente'] ?>" alt="<?= $rev['nombre'] ?>" style="width: 42px; height: 42px; border-radius: 50%; border: 1px solid #d4af37; object-fit: cover;">
                                <div>
                                    <h4 style="color: #fff; margin: 0; font-size: 14px; font-weight: bold;"><?= htmlspecialchars($rev['nombre']) ?></h4>
                                    <span style="color: #d4af37; font-size: 12px;"><?= str_repeat('★', $rev['estrellas']) ?></span>
                                </div>
                            </div>

                            <!-- Texto Comentario -->
                            <p style="color: #ccc; font-size: 13px; line-height: 1.5; font-style: italic; margin-bottom: 15px; text-align: left;">
                                "<?= htmlspecialchars($rev['comentario']) ?>"
                            </p>
                        </div>

                        <!-- Prenda Comprada e Imagen del Producto -->
                        <div style="display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.5); padding: 8px 12px; border-radius: 6px; border-left: 3px solid #d4af37; text-align: left;">
                            <img src="<?= $rev['foto_producto'] ?>" alt="Prenda" style="width: 38px; height: 38px; border-radius: 4px; object-fit: cover; border: 1px solid #333;" onerror="this.src='assets/img/img_logo_be$t.png';">
                            <span style="color: #888; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                                Prenda: <strong style="color: #d4af37; display: block; font-size: 12px;"><?= htmlspecialchars($rev['prenda']) ?></strong>
                            </span>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <hr class="gold-divider">

    <!-- SECCIÓN: PREGUNTAS FRECUENTES -->
    <section id="preguntas" class="section-container">
        <h2 class="gold-title">PREGUNTAS FRECUENTES</h2>
        
        <div class="faq-accordion">
            
            <!-- Pregunta Nueva: Pago Contraentrega -->
            <div class="faq-item">
                <button class="faq-question">
                    <span>¿Por qué no ofrecemos pagos contraentrega?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>En nuestra tienda trabajamos únicamente con pagos anticipados, y te explicamos por qué esta modalidad es 100% segura y confiable:</p>
                    <p style="margin-top: 10px; margin-bottom: 5px;"><strong>✅ Procesos más rápidos y seguros:</strong> El pago anticipado nos permite despachar tu pedido sin demoras, evitando rechazos o pérdidas en la entrega.</p>
                    <p style="margin-bottom: 5px;"><strong>✅ Productos personalizados o hechos bajo pedido:</strong> Muchos de nuestros artículos se elaboran especialmente para ti, por lo que requieren un compromiso previo.</p>
                    <p style="margin-bottom: 5px;"><strong>✅ Costos optimizados:</strong> Al no usar contraentrega, evitamos recargos innecesarios que aumentan el valor del envío.</p>
                    <p style="margin-bottom: 15px;"><strong>✅ Reducción de fraudes:</strong> Esta modalidad disminuye significativamente las devoluciones injustificadas y pedidos falsos.</p>
                    
                    <p><strong>Sabemos que confiar es importante, por eso te garantizamos:</strong></p>
                    <ul style="list-style: none; padding-left: 0; margin-top: 8px; margin-bottom: 15px; line-height: 1.8;">
                        <li>📦 Número de guía para rastrear tu envío.</li>
                        <li>🧾 Pruebas reales de entregas anteriores.</li>
                        <li>🤝 Acompañamiento en todo el proceso.</li>
                        <li>🛡️ Pagos seguros mediante plataformas confiables.</li>
                    </ul>
                    <p style="color: #d4af37; font-weight: bold;">Tu tranquilidad es nuestra prioridad. ¡Puedes comprar con total confianza!</p>
                </div>
            </div>

            <!-- Pregunta 1 -->
            <div class="faq-item">
                <button class="faq-question">
                    <span>¿Cuáles son los métodos de pago disponibles?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>Aceptamos pagos en efectivo, transferencias bancarias, Nequi, Daviplata y tarjetas de crédito/débito.</p>
                </div>
            </div>

            <!-- Pregunta 2 -->
            <div class="faq-item">
                <button class="faq-question">
                    <span>¿Cuánto tiempo tarda en llegar mi pedido?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>Los envíos locales tardan entre 24 y 48 horas. Para envíos nacionales, el tiempo estimado es de 2 a 5 días hábiles.</p>
                </div>
            </div>

            <!-- Pregunta 3 -->
            <div class="faq-item">
                <button class="faq-question">
                    <span>¿Tienen política de cambios o devoluciones?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>Sí, dispones de hasta 5 días hábiles después de recibir tu prenda para solicitar un cambio, siempre y cuando conserve sus etiquetas y se encuentre en perfecto estado.</p>
                </div>
            </div>

            <!-- Pregunta 4 -->
            <div class="faq-item">
                <button class="faq-question">
                    <span>¿Cómo puedo saber mi talla exacta?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>En la descripción de cada prenda o consultando con nosotros por redes sociales te facilitamos la guía de medidas en centímetros para camisas, gorras y pantalones.</p>
                </div>
            </div>

        </div>
    </section>

    <!-- Script del menú lateral -->
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

        // Lógica para desplegar el acordeón de FAQ
        const faqQuestions = document.querySelectorAll('.faq-question');

        faqQuestions.forEach(question => {
            question.addEventListener('click', () => {
                const faqItem = question.parentElement;
                const faqAnswer = faqItem.querySelector('.faq-answer');

                // Cierra las demás preguntas para que solo haya una abierta a la vez
                document.querySelectorAll('.faq-item').forEach(item => {
                    if (item !== faqItem) {
                        item.classList.remove('active');
                        item.querySelector('.faq-answer').style.maxHeight = null;
                    }
                });

                // Alterna la pregunta seleccionada
                faqItem.classList.toggle('active');

                if (faqItem.classList.contains('active')) {
                    faqAnswer.style.maxHeight = faqAnswer.scrollHeight + "px";
                } else {
                    faqAnswer.style.maxHeight = null;
                }
            });
        });
    </script>

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