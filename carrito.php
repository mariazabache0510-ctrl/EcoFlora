<?php
require_once __DIR__ . '/config/conexion.php';
include __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $id_planta = intval($_POST['id_planta']);
    $cantidad = max(1, intval($_POST['cantidad']));
    $st = $conexion->prepare("SELECT stock FROM plantas WHERE id_planta = ?");
    $st->execute([$id_planta]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    $stock = $row['stock'] ?? 0;
    if ($stock >= $cantidad) {
        if (isset($_SESSION['id_usuario'])) {
            $stmt = $conexion->prepare("INSERT INTO carrito (id_usuario, id_planta, cantidad) VALUES (?, ?, ?) ON CONFLICT (id_usuario, id_planta) DO UPDATE SET cantidad = LEAST(carrito.cantidad + EXCLUDED.cantidad, ?)");
            $stmt->execute([$_SESSION['id_usuario'], $id_planta, $cantidad, $stock]);
        } else {
            if (!isset($_SESSION['sesion_carrito'])) $_SESSION['sesion_carrito'] = [];
            $_SESSION['sesion_carrito'][$id_planta] = min(($_SESSION['sesion_carrito'][$id_planta] ?? 0) + $cantidad, $stock);
        }
    }
    header("Location: " . BASE_URL . "/carrito.php");
    exit();
}

if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    if (isset($_SESSION['id_usuario'])) {
        $stmt = $conexion->prepare("DELETE FROM carrito WHERE id_usuario = ? AND id_planta = ?");
        $stmt->execute([$_SESSION['id_usuario'], $id]);
    } else {
        unset($_SESSION['sesion_carrito'][$id]);
    }
    header("Location: " . BASE_URL . "/carrito.php");
    exit();
}

if (isset($_POST['actualizar'])) {
    foreach ($_POST['cantidad'] as $id => $cantidad) {
        $cantidad = max(1, intval($cantidad));
        if (isset($_SESSION['id_usuario'])) {
            $stmt = $conexion->prepare("UPDATE carrito SET cantidad = ? WHERE id_usuario = ? AND id_planta = ?");
            $stmt->execute([$cantidad, $_SESSION['id_usuario'], $id]);
        } else {
            $_SESSION['sesion_carrito'][$id] = $cantidad;
        }
    }
    header("Location: " . BASE_URL . "/carrito.php");
    exit();
}

$items_array = [];
$total = 0;
if (isset($_SESSION['id_usuario'])) {
    $stmt = $conexion->prepare("SELECT c.*, p.nombre, p.precio, p.imagen, p.stock FROM carrito c JOIN plantas p ON c.id_planta = p.id_planta WHERE c.id_usuario = ?");
    $stmt->execute([$_SESSION['id_usuario']]);
    $items_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (isset($_SESSION['sesion_carrito']) && !empty($_SESSION['sesion_carrito'])) {
    $ids = array_map('intval', array_keys($_SESSION['sesion_carrito']));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conexion->prepare("SELECT id_planta, nombre, precio, imagen, stock FROM plantas WHERE id_planta IN ($placeholders)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $i) {
        $i['cantidad'] = $_SESSION['sesion_carrito'][$i['id_planta']];
        $items_array[] = $i;
    }
}
$hay_items = count($items_array) > 0;
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4" style="color: var(--verde-oscuro);"><i class="bi bi-cart3 me-2"></i>Tu carrito</h2>
    <?php if (!$hay_items): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x fs-1 text-muted"></i>
            <p class="mt-3 text-muted">Tu carrito está vacío.</p>
            <a href="<?php echo BASE_URL; ?>/catalogo.php" class="btn btn-ecoflora mt-2">Ir al catálogo</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="table-responsive">
                    <table class="table table-ecoflora mb-0 align-middle">
                        <thead><tr><th>Producto</th><th class="text-center">Precio</th><th class="text-center" style="width: 150px;">Cantidad</th><th class="text-end">Subtotal</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach($items_array as $item):
                                $subtotal = $item['precio'] * $item['cantidad'];
                                $total += $subtotal;
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <!-- IMAGEN REAL DE LA PLANTA (con fallback si no existe) -->
                                        <img src="imagenes/<?php echo $item['imagen']; ?>" 
                                             class="rounded" 
                                             style="width: 60px; height: 60px; object-fit: cover;"
                                             onerror="this.src='https://placehold.co/100x100/2d6a4f/ffffff?text=<?php echo urlencode($item['nombre']); ?>'"
                                             alt="<?php echo $item['nombre']; ?>">
                                        <div class="ms-3"><h6 class="mb-0 fw-bold"><?php echo $item['nombre']; ?></h6></div>
                                    </div>
                                </td>
                                <td class="text-center">$<?php echo number_format($item['precio'], 0, ',', '.'); ?></td>
                                <td><input type="number" name="cantidad[<?php echo $item['id_planta']; ?>]" value="<?php echo $item['cantidad']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="form-control text-center"></td>
                                <td class="text-end fw-bold">$<?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                <td class="text-end"><a href="?eliminar=<?php echo $item['id_planta']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Eliminar?')"><i class="bi bi-trash"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-group-divider">
                            <tr><td colspan="3" class="text-end fw-bold fs-5">Total:</td><td class="text-end fw-bold fs-5" style="color: var(--verde-oscuro);">$<?php echo number_format($total, 0, ',', '.'); ?></td><td></td></tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer bg-white border-0 p-3 d-flex justify-content-between">
                    <div><button type="submit" name="actualizar" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i>Actualizar</button></div>
                    <div>
                        <a href="<?php echo BASE_URL; ?>/catalogo.php" class="btn btn-outline-ecoflora me-2">Seguir comprando</a>
                        <?php if(isset($_SESSION['id_usuario'])): ?>
                            <a href="<?php echo BASE_URL; ?>/confirmar_pedido.php" class="btn btn-ecoflora"><i class="bi bi-truck me-1"></i>Solicitar domicilio</a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-ecoflora">Ingresa para continuar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
