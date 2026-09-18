<?php
session_start();
include 'conexion.php';

// Inicializar el carrito en la sesión si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
    $talla = isset($_POST['talla']) ? trim($_POST['talla']) : 'S';

    if ($id_producto > 0 && $cantidad > 0) {
        // Consultar la base de datos para validar stock y recalcular precio por seguridad
        $stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id_producto);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $prod_db = $res->fetch_assoc();

            // CÁLCULO DE SEGURIDAD EN EL SERVIDOR
            $precio_original = floatval($prod_db['precio']);
            $descuento = intval($prod_db['descuento'] ?? 0);
            $precio_final = ($descuento > 0) ? $precio_original * (1 - ($descuento / 100)) : $precio_original;

            // Clave única por producto y talla
            $item_key = $id_producto . '_' . $talla;

            if (isset($_SESSION['carrito'][$item_key])) {
                // Si la prenda en esa talla ya está en el carrito, sumar la cantidad
                $_SESSION['carrito'][$item_key]['cantidad'] += $cantidad;
            } else {
                // Agregar nuevo elemento
                $_SESSION['carrito'][$item_key] = [
                    'id' => $prod_db['id'],
                    'nombre' => $prod_db['nombre'],
                    'precio' => $precio_final, // Precio real cobrado (con descuento)
                    'precio_original' => $precio_original,
                    'descuento' => $descuento,
                    'cantidad' => $cantidad,
                    'talla' => $talla,
                    'imagen' => $prod_db['imagen']
                ];
            }

            // Responder según el tipo de petición (AJAX o Formulario normal)
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Producto agregado al carrito',
                    'total_items' => array_sum(array_column($_SESSION['carrito'], 'cantidad'))
                ]);
                exit;
            } else {
                // Redireccionar al carrito
                header('Location: carrito.php');
                exit;
            }
        }
    }
}

// Redirección de respaldo si falla la validación
header('Location: catalogo.php');
exit;
?>