<?php
session_start();

// Configuración de errores
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

// Incluir la conexión global configurada con Hostinger
require_once 'conexion.php';

if (!$conexion) {
    die("Error de conexión a la base de datos.");
}

$mensaje = '';

// -------------------------------------------------------------
// 1. ELIMINAR PRODUCTO
// -------------------------------------------------------------
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id_eliminar);
    if ($stmt->execute()) {
        $mensaje = "Producto eliminado correctamente.";
    }
}

// -------------------------------------------------------------
// FUNCIÓN CON DIAGNÓSTICO PARA PROCESAR SUBIDA DE IMÁGENES
// -------------------------------------------------------------
function procesarImagen($keyInput, $carpeta_destino = 'uploads/productos_img/') {
    if (!isset($_FILES[$keyInput])) {
        return null;
    }

    $error = $_FILES[$keyInput]['error'];

    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($error !== UPLOAD_ERR_OK) {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                die("<div style='padding:20px; background:#f8d7da; color:#721c24; font-family:sans-serif; margin:20px; border-radius:8px;'>
                        <strong>⚠️ ERROR DE TAMAÑO:</strong> La imagen en el campo <strong>'$keyInput'</strong> (<em>" . htmlspecialchars($_FILES[$keyInput]['name']) . "</em>) supera el límite máximo permitido por tu servidor PHP (upload_max_filesize).<br><br>
                        <strong>Solución:</strong> Aumenta el límite en tu <code>php.ini</code> o sube una imagen de menor peso.
                     </div>");
            case UPLOAD_ERR_PARTIAL:
                die("<div style='padding:20px; background:#fff3cd; color:#856404; font-family:sans-serif; margin:20px; border-radius:8px;'>
                        <strong>⚠️ ERROR:</strong> La imagen se subió parcialmente. Intenta de nuevo.
                     </div>");
            case UPLOAD_ERR_NO_TMP_DIR:
                die("<div style='padding:20px; background:#f8d7da; color:#721c24; font-family:sans-serif; margin:20px; border-radius:8px;'>
                        <strong>⚠️ ERROR DE SERVIDOR:</strong> Falta la carpeta temporal en la configuración de PHP.
                     </div>");
            case UPLOAD_ERR_CANT_WRITE:
                die("<div style='padding:20px; background:#f8d7da; color:#721c24; font-family:sans-serif; margin:20px; border-radius:8px;'>
                        <strong>⚠️ ERROR DE DISCO:</strong> No se pudo escribir el archivo en el disco del servidor.
                     </div>");
            default:
                die("<div style='padding:20px; background:#f8d7da; color:#721c24; font-family:sans-serif; margin:20px; border-radius:8px;'>
                        <strong>⚠️ ERROR DE SUBIDA:</strong> Código de error PHP: $error.
                     </div>");
        }
    }

    $ext = strtolower(pathinfo($_FILES[$keyInput]['name'], PATHINFO_EXTENSION));
    $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($ext, $ext_permitidas)) {
        die("<div style='padding:20px; background:#f8d7da; color:#721c24; font-family:sans-serif; margin:20px; border-radius:8px;'>
                <strong>⚠️ FORMATO NO PERMITIDO:</strong> El archivo <em>" . htmlspecialchars($_FILES[$keyInput]['name']) . "</em> tiene la extensión <strong>.$ext</strong>.<br>Solo se permiten imágenes: JPG, JPEG, PNG y WEBP.
             </div>");
    }

    if (!file_exists($carpeta_destino)) {
        if (!mkdir($carpeta_destino, 0777, true)) {
            die("<div style='padding:20px; background:#f8d7da; color:#721c24; font-family:sans-serif; margin:20px; border-radius:8px;'>
                    <strong>⚠️ ERROR DE PERMISOS:</strong> No se pudo crear la carpeta <code>$carpeta_destino</code>. Por favor créala manualmente en tu proyecto.
                 </div>");
        }
    }

    $archivo_nombre = time() . '_' . uniqid() . '.' . $ext;
    $ruta_completa = $carpeta_destino . $archivo_nombre;
    
    if (move_uploaded_file($_FILES[$keyInput]['tmp_name'], $ruta_completa)) {
        return $archivo_nombre;
    } else {
        die("<div style='padding:20px; background:#f8d7da; color:#721c24; font-family:sans-serif; margin:20px; border-radius:8px;'>
                <strong>⚠️ ERROR AL GUARDAR:</strong> No se pudo mover la imagen subida a la carpeta <code>$ruta_completa</code>.<br>Verifica los permisos de la carpeta en tu sistema operativo.
             </div>");
    }
}

// -------------------------------------------------------------
// 2. GUARDAR / EDITAR PRODUCTO (CON DESCRIPCIÓN Y DESCUENTO)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_producto'])) {
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $tallas = !empty($_POST['tallas']) ? trim($_POST['tallas']) : 'S,M,L,XL';
    $descripcion = !empty($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $descuento = !empty($_POST['descuento']) ? intval($_POST['descuento']) : 0;
    $id_prod = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
    
    $img1 = procesarImagen('imagen');
    $img2 = procesarImagen('imagen2');
    $img3 = procesarImagen('imagen3');

    if ($id_prod > 0) {
        $query_actual = $conexion->prepare("SELECT imagen, imagen2, imagen3 FROM productos WHERE id = ?");
        $query_actual->bind_param("i", $id_prod);
        $query_actual->execute();
        $actual = $query_actual->get_result()->fetch_assoc();

        $img1_final = $img1 ? $img1 : $actual['imagen'];
        $img2_final = $img2 ? $img2 : $actual['imagen2'];
        $img3_final = $img3 ? $img3 : $actual['imagen3'];

        $stmt = $conexion->prepare("UPDATE productos SET nombre=?, precio=?, stock=?, tallas=?, descripcion=?, descuento=?, imagen=?, imagen2=?, imagen3=? WHERE id=?");
        $stmt->bind_param("sdississsi", $nombre, $precio, $stock, $tallas, $descripcion, $descuento, $img1_final, $img2_final, $img3_final, $id_prod);
        
        if ($stmt->execute()) {
            $mensaje = "Producto actualizado con éxito.";
        } else {
            $mensaje = "Error al actualizar en la BD: " . $conexion->error;
        }
    } else {
        $stmt = $conexion->prepare("INSERT INTO productos (nombre, precio, stock, tallas, descripcion, descuento, imagen, imagen2, imagen3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdississs", $nombre, $precio, $stock, $tallas, $descripcion, $descuento, $img1, $img2, $img3);
        
        if ($stmt->execute()) {
            $mensaje = "Nuevo producto agregado con éxito.";
        } else {
            $mensaje = "Error al guardar en la BD: " . $conexion->error;
        }
    }
}

// Obtener inventario
$productos = $conexion->query("SELECT * FROM productos ORDER BY id DESC");
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
    <title>BE$T - Inventario</title>
    <!-- Hojas de estilo para la Gestión de Inventario -->
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
                <img src="assets/img/img_logo_be$t.png" alt="BE$T Logo" style="height: 28px !important; width: auto !important; object-fit: contain !important; flex-shrink: 0 !important;">
                <span style="color: #d4af37 !important; font-weight: bold !important; font-size: 11px !important; letter-spacing: 1px !important; border: 1px solid #d4af37 !important; padding: 1px 5px !important; border-radius: 4px !important; background: rgba(212, 175, 55, 0.1) !important;">ADMIN</span>
            </a>
        </div>
        <nav class="admin-nav" style="display: flex !important; align-items: center !important; gap: 6px !important; flex-wrap: wrap !important; margin-top: 4px !important;">
            <a href="admin_dashboard.php" class="nav-link <?= ($pagina_actual == 'admin_dashboard.php') ? 'active' : '' ?>" style="color: #ffffff !important; text-decoration: none !important; font-size: 12px !important; padding: 4px 6px !important;">Panel</a>
            <a href="inventario.php" class="nav-link <?= ($pagina_actual == 'inventario.php') ? 'active' : '' ?>" style="color: #d4af37 !important; text-decoration: none !important; font-size: 12px !important; padding: 4px 6px !important; font-weight: bold !important;">Inventario</a>
            <a href="reportes.php" class="nav-link <?= ($pagina_actual == 'reportes.php') ? 'active' : '' ?>" style="color: #ffffff !important; text-decoration: none !important; font-size: 12px !important; padding: 4px 6px !important;">Reportes</a>
            <a href="logout.php" class="nav-link logout-btn" style="color: #ff4d4d !important; text-decoration: none !important; font-size: 11px !important; border: 1px solid #ff4d4d !important; padding: 2px 6px !important; border-radius: 4px !important; margin-left: 2px !important;">Salir</a>
        </nav>
    </header>

    <div class="section-container" style="padding: 15px 10px !important; max-width: 100% !important; box-sizing: border-box !important;">
        <h1 class="gold-title" style="font-size: 1.5rem !important; text-align: center !important; margin-bottom: 15px !important; color: #d4af37 !important;">GESTIÓN DE INVENTARIO</h1>

        <?php if (!empty($mensaje)): ?>
            <p style="text-align: center; color: #4caf50; font-weight: bold; margin-bottom: 20px; font-size: 13px;"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <!-- FORMULARIO PARA REGISTRAR/EDITAR PRENDA -->
        <div class="content-box text-left margin-bottom-30" style="background: #111 !important; border: 1px solid rgba(212,175,55,0.2) !important; border-radius: 8px !important; padding: 12px !important; box-sizing: border-box !important; margin-bottom: 25px !important;">
            <h2 class="section-subtitle" id="form-title" style="font-size: 1.1rem !important; margin: 0 0 15px 0 !important; color: #d4af37 !important;">📦 Agregar Prenda</h2>
            
            <form action="inventario.php" method="POST" enctype="multipart/form-data" class="inventory-form">
                <input type="hidden" name="id_producto" id="id_producto" value="0">
                
                <div class="form-grid" style="display: grid !important; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)) !important; gap: 12px !important;">
                    <div class="input-group">
                        <label style="display: block; font-size: 12px; color: #ccc; margin-bottom: 4px;">Nombre de la Prenda</label>
                        <input type="text" name="nombre" id="nombre" required placeholder="Ej. Camiseta Oversize Negra" style="width: 100%; box-sizing: border-box; background: #000; color: #fff; border: 1px solid #333; border-radius: 6px; padding: 8px; font-size: 13px;">
                    </div>

                    <div class="input-group">
                        <label style="display: block; font-size: 12px; color: #ccc; margin-bottom: 4px;">Precio (COP)</label>
                        <input type="number" step="0.01" name="precio" id="precio" required placeholder="Ej. 85000" style="width: 100%; box-sizing: border-box; background: #000; color: #fff; border: 1px solid #333; border-radius: 6px; padding: 8px; font-size: 13px;">
                    </div>

                    <div class="input-group">
                        <label style="display: block; font-size: 12px; color: #ccc; margin-bottom: 4px;">Stock (Cantidad)</label>
                        <input type="number" name="stock" id="stock" required placeholder="Ej. 12" style="width: 100%; box-sizing: border-box; background: #000; color: #fff; border: 1px solid #333; border-radius: 6px; padding: 8px; font-size: 13px;">
                    </div>

                    <div class="input-group">
                        <label style="display: block; font-size: 12px; color: #ccc; margin-bottom: 4px;">Tallas Disponibles</label>
                        <input type="text" name="tallas" id="tallas" placeholder="Ej. S, M, L, XL" value="S, M, L, XL" style="width: 100%; box-sizing: border-box; background: #000; color: #fff; border: 1px solid #333; border-radius: 6px; padding: 8px; font-size: 13px;">
                    </div>

                    <div class="input-group">
                        <label style="display: block; font-size: 12px; color: #ccc; margin-bottom: 4px;">Descuento % <span style="color: #888;">(Opcional)</span></label>
                        <input type="number" min="0" max="100" name="descuento" id="descuento" placeholder="Ej. 20" value="0" style="width: 100%; box-sizing: border-box; background: #000; color: #fff; border: 1px solid #333; border-radius: 6px; padding: 8px; font-size: 13px;">
                    </div>

                    <div class="input-group" style="grid-column: 1 / -1;">
                        <label style="display: block; font-size: 12px; color: #ccc; margin-bottom: 4px;">Descripción de la Prenda</label>
                        <textarea name="descripcion" id="descripcion" rows="3" placeholder="Ej. Algodón de gramaje alto 240g, horma oversize..." style="width: 100%; box-sizing: border-box; background: #000; color: #fff; border: 1px solid #333; border-radius: 6px; padding: 8px; font-size: 13px; resize: vertical;"></textarea>
                    </div>

                    <div class="input-group">
                        <label style="display: block; font-size: 12px; color: #ccc; margin-bottom: 4px;">Imagen Principal <span id="lbl-img1">(Requerida)</span></label>
                        <input type="file" name="imagen" id="imagen" accept="image/*" style="width: 100%; box-sizing: border-box; background: #000; color: #fff; border: 1px solid #333; border-radius: 6px; padding: 6px; font-size: 11px;" onchange="previewImagen(this, 'prev-1')">
                        <div id="box-prev-1" style="margin-top: 8px; display: none;">
                            <img id="prev-1" src="" style="width: 50px; height: 50px; min-width: 50px; min-height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #d4af37; flex-shrink: 0 !important;">
                        </div>
                    </div>

                    <div class="input-group">
                        <label style="display: block; font-size: 12px; color: #ccc; margin-bottom: 4px;">Imagen 2 (Opcional)</label>
                        <input type="file" name="imagen2" id="imagen2" accept="image/*" style="width: 100%; box-sizing: border-box; background: #000; color: #fff; border: 1px solid #333; border-radius: 6px; padding: 6px; font-size: 11px;" onchange="previewImagen(this, 'prev-2')">
                        <div id="box-prev-2" style="margin-top: 8px; display: none;">
                            <img id="prev-2" src="" style="width: 50px; height: 50px; min-width: 50px; min-height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #d4af37; flex-shrink: 0 !important;">
                        </div>
                    </div>

                    <div class="input-group">
                        <label style="display: block; font-size: 12px; color: #ccc; margin-bottom: 4px;">Imagen 3 (Opcional)</label>
                        <input type="file" name="imagen3" id="imagen3" accept="image/*" style="width: 100%; box-sizing: border-box; background: #000; color: #fff; border: 1px solid #333; border-radius: 6px; padding: 6px; font-size: 11px;" onchange="previewImagen(this, 'prev-3')">
                        <div id="box-prev-3" style="margin-top: 8px; display: none;">
                            <img id="prev-3" src="" style="width: 50px; height: 50px; min-width: 50px; min-height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #d4af37; flex-shrink: 0 !important;">
                        </div>
                    </div>
                </div>

                <!-- CONTENEDOR DE BOTONES CORREGIDO PARA QUE NO SE SALGAN -->
                <div class="form-actions" style="display: flex !important; flex-wrap: wrap !important; gap: 10px !important; width: 100% !important; box-sizing: border-box !important; margin-top: 20px !important;">
                    <button type="submit" name="guardar_producto" class="btn-submit" id="btn-submit" style="flex: 1 1 130px !important; min-width: 0 !important; background: linear-gradient(135deg, #d4af37, #aa820a) !important; color: #000 !important; font-weight: bold !important; border: none !important; padding: 12px 10px !important; border-radius: 6px !important; cursor: pointer !important; text-align: center !important; font-size: 13px !important; box-sizing: border-box !important;">
                        GUARDAR PRENDA
                    </button>
                    <button type="button" onclick="limpiarFormulario()" class="btn-cancelar" style="flex: 1 1 110px !important; min-width: 0 !important; background: transparent !important; border: 1px solid #ff4d4d !important; color: #ff4d4d !important; font-weight: bold !important; padding: 12px 10px !important; border-radius: 6px !important; cursor: pointer !important; text-align: center !important; font-size: 13px !important; box-sizing: border-box !important;">
                        CANCELAR
                    </button>
                </div>
            </form>
        </div>

        <!-- TABLA DE PRODUCTOS CON DESPLAZAMIENTO FLUIDO -->
        <div class="content-box text-left" style="background: #111 !important; border: 1px solid rgba(212,175,55,0.2) !important; border-radius: 8px !important; padding: 12px !important; box-sizing: border-box !important;">
            <h2 class="section-subtitle" style="font-size: 1.1rem !important; margin: 0 0 15px 0 !important; color: #d4af37 !important;">📋 Catálogo Actual</h2>
            <div class="table-responsive" style="width: 100% !important; overflow-x: auto !important; -webkit-overflow-scrolling: touch !important;">
                <table class="gold-table" style="width: 100% !important; border-collapse: collapse !important; font-size: 12px !important; min-width: 550px !important;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(212, 175, 55, 0.3); text-align: left; color: #d4af37;">
                            <th style="padding: 8px;">Imágenes</th>
                            <th style="padding: 8px;">Nombre / Desc.</th>
                            <th style="padding: 8px;">Precio</th>
                            <th style="padding: 8px;">Desc.</th>
                            <th style="padding: 8px;">Stock</th>
                            <th style="padding: 8px;">Tallas</th>
                            <th style="padding: 8px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($productos && $productos->num_rows > 0): ?>
                            <?php while ($prod = $productos->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td style="padding: 8px;">
                                        <div style="display: flex; gap: 4px; align-items: center; flex-shrink: 0 !important;">
                                            <?php if (!empty($prod['imagen'])): ?>
                                                <img src="uploads/productos_img/<?= htmlspecialchars($prod['imagen']) ?>" class="table-img" alt="Foto 1" style="width: 38px; height: 38px; min-width: 38px; min-height: 38px; object-fit: cover; border-radius: 4px; border: 1px solid #d4af37; flex-shrink: 0 !important;">
                                            <?php else: ?>
                                                <span style="color:#777; font-size: 10px;">Sin foto</span>
                                            <?php endif; ?>

                                            <?php if (!empty($prod['imagen2'])): ?>
                                                <img src="uploads/productos_img/<?= htmlspecialchars($prod['imagen2']) ?>" class="table-img" alt="Foto 2" style="width: 38px; height: 38px; min-width: 38px; min-height: 38px; object-fit: cover; border-radius: 4px; opacity: 0.8; flex-shrink: 0 !important;">
                                            <?php endif; ?>

                                            <?php if (!empty($prod['imagen3'])): ?>
                                                <img src="uploads/productos_img/<?= htmlspecialchars($prod['imagen3']) ?>" class="table-img" alt="Foto 3" style="width: 38px; height: 38px; min-width: 38px; min-height: 38px; object-fit: cover; border-radius: 4px; opacity: 0.8; flex-shrink: 0 !important;">
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="padding: 8px;">
                                        <strong style="color: #fff; font-size: 12px;"><?= htmlspecialchars($prod['nombre']) ?></strong>
                                        <?php if (!empty($prod['descripcion'])): ?>
                                            <br><small style="color: #888; font-size: 11px; display: inline-block; margin-top: 2px; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($prod['descripcion']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 8px; font-weight: bold; color: #d4af37;">$<?= number_format($prod['precio'], 0, ',', '.') ?></td>
                                    
                                    <td style="padding: 8px;">
                                        <?php if (!empty($prod['descuento']) && $prod['descuento'] > 0): ?>
                                            <span style="background: #e74c3c; color: #fff; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 10px;">
                                                -<?= intval($prod['descuento']) ?>%
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #555; font-size: 11px;">N/A</span>
                                        <?php endif; ?>
                                    </td>

                                    <td style="padding: 8px;">
                                        <span style="color: <?= ($prod['stock'] <= 3) ? '#ff4d4d' : '#fff' ?>; font-weight: bold;">
                                            <?= $prod['stock'] ?> pcs
                                        </span>
                                    </td>
                                    <td style="padding: 8px; color: #d4af37; font-size: 11px;">
                                        <?= htmlspecialchars($prod['tallas'] ?? 'S, M, L, XL') ?>
                                    </td>
                                    <td style="padding: 8px;">
                                        <div style="display: flex; gap: 4px;">
                                            <button type="button" 
                                                    onclick='editarProducto(<?= json_encode($prod, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' 
                                                    style="background: rgba(212, 175, 55, 0.2); color: #d4af37; border: 1px solid #d4af37; padding: 4px 8px; border-radius: 4px; font-size: 11px; cursor: pointer;">
                                                Editar
                                            </button>
                                            
                                            <a href="inventario.php?eliminar=<?= $prod['id'] ?>" onclick="return confirm('¿Eliminar este producto?')" style="background: rgba(255, 77, 77, 0.1); color: #ff4d4d; border: 1px solid #ff4d4d; padding: 4px 8px; border-radius: 4px; font-size: 11px; text-decoration: none;">Borrar</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #777; padding: 20px;">
                                    No hay prendas registradas aún.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
    function previewImagen(input, targetId) {
        const file = input.files[0];
        const imgBox = document.getElementById('box-' + targetId);
        const imgTarget = document.getElementById(targetId);

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgTarget.src = e.target.result;
                imgBox.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            imgBox.style.display = 'none';
            imgTarget.src = '';
        }
    }

    function editarProducto(prod) {
        document.getElementById('id_producto').value = prod.id;
        document.getElementById('nombre').value = prod.nombre;
        document.getElementById('precio').value = prod.precio;
        document.getElementById('stock').value = prod.stock;
        document.getElementById('tallas').value = prod.tallas || 'S, M, L, XL';
        document.getElementById('descripcion').value = prod.descripcion || '';
        document.getElementById('descuento').value = prod.descuento || 0;

        document.getElementById('form-title').innerText = '✏️ Editar Prenda #' + prod.id;
        document.getElementById('btn-submit').innerText = 'ACTUALIZAR PRENDA';
        document.getElementById('lbl-img1').innerText = '(Opcional se conserva la foto actual)';

        cargarPrevia('prev-1', prod.imagen);
        cargarPrevia('prev-2', prod.imagen2);
        cargarPrevia('prev-3', prod.imagen3);

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function cargarPrevia(targetId, nombreFoto) {
        const imgBox = document.getElementById('box-' + targetId);
        const imgTarget = document.getElementById(targetId);
        if (nombreFoto && nombreFoto !== 'NULL' && nombreFoto.trim() !== '') {
            imgTarget.src = 'uploads/productos_img/' + nombreFoto;
            imgBox.style.display = 'block';
        } else {
            imgBox.style.display = 'none';
            imgTarget.src = '';
        }
    }

    function limpiarFormulario() {
        document.getElementById('id_producto').value = '0';
        document.getElementById('nombre').value = '';
        document.getElementById('precio').value = '';
        document.getElementById('stock').value = '';
        document.getElementById('tallas').value = 'S, M, L, XL';
        document.getElementById('descripcion').value = '';
        document.getElementById('descuento').value = '0';

        document.getElementById('imagen').value = '';
        document.getElementById('imagen2').value = '';
        document.getElementById('imagen3').value = '';

        document.getElementById('form-title').innerText = '📦 Agregar Prenda';
        document.getElementById('btn-submit').innerText = 'GUARDAR PRENDA';
        document.getElementById('lbl-img1').innerText = '(Requerida para nuevo producto)';

        ['prev-1', 'prev-2', 'prev-3'].forEach(id => {
            document.getElementById('box-' + id).style.display = 'none';
            document.getElementById(id).src = '';
        });
    }
    </script>
</body>
</html>