<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . BASE_URL . "/login.php?error=Debes iniciar sesión para solicitar un domicilio");
    exit();
}

if ($_SESSION['rol'] !== 'cliente' && $_SESSION['rol'] !== 'usuario') {
    header("Location: " . BASE_URL . "/index.php?error=Acceso no autorizado");
    exit();
}

$stmt = $conexion->prepare("SELECT c.*, p.nombre, p.precio, p.stock FROM carrito c JOIN plantas p ON c.id_planta = p.id_planta WHERE c.id_usuario = ?");
$stmt->execute([$_SESSION['id_usuario']]);
$items_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si no hay items, volver al carrito
if (count($items_array) === 0) {
    header("Location: " . BASE_URL . "/carrito.php");
    exit();
}

$total = 0;
foreach ($items_array as $i) {
    $total += $i['precio'] * $i['cantidad'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $notas = trim($_POST['notas'] ?? '');
    
    if (empty($direccion) || empty($telefono)) {
        $error = "La dirección y teléfono son obligatorios.";
    } else {
        try {
            $conexion->beginTransaction();
            $stmt = $conexion->prepare("INSERT INTO pedidos (id_usuario, total, direccion_envio, ciudad_envio, telefono_contacto, notas) VALUES (?,?,?,?,?,?) RETURNING id_pedido");
            $stmt->execute([$_SESSION['id_usuario'], $total, $direccion, $ciudad, $telefono, $notas]);
            $id_pedido = $stmt->fetchColumn();
            
            $stmt_det = $conexion->prepare("INSERT INTO pedido_detalle (id_pedido, id_planta, cantidad, precio_unitario) VALUES (?,?,?,?)");
            $stmt_stock = $conexion->prepare("UPDATE plantas SET stock = stock - ? WHERE id_planta = ?");
            foreach($items_array as $item) {
                $stmt_det->execute([$id_pedido, $item['id_planta'], $item['cantidad'], $item['precio']]);
                $stmt_stock->execute([$item['cantidad'], $item['id_planta']]);
            }
            
            $stmt_del = $conexion->prepare("DELETE FROM carrito WHERE id_usuario = ?");
            $stmt_del->execute([$_SESSION['id_usuario']]);
            $conexion->commit();
            
            header("Location: " . BASE_URL . "/usuario/domicilios.php?exito=1");
            exit();
        } catch (Exception $e) {
            $conexion->rollBack();
            $error = "Error al procesar el pedido. Intenta de nuevo.";
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4" style="color: var(--verde-oscuro);"><i class="bi bi-truck me-2"></i>Confirmar domicilio</h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 p-4" style="border-radius: 15px;">
                <h5 class="fw-bold mb-3">Datos de envío</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Dirección completa *</label>
                        <textarea name="direccion" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono de contacto *</label>
                            <input type="text" name="telefono" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notas adicionales</label>
                        <textarea name="notas" class="form-control" rows="2" placeholder="Ej: Dejar con portero, timbrar segundo piso..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-ecoflora w-100 btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Confirmar pedido
                    </button>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 p-4" style="border-radius: 15px; background: var(--verde-pastel);">
                <h5 class="fw-bold mb-3">Resumen del pedido</h5>
                <ul class="list-group list-group-flush bg-transparent">
                    <?php foreach($items_array as $item): ?>
                    <li class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                        <span><?php echo $item['nombre']; ?> x<?php echo $item['cantidad']; ?></span>
                        <strong>$<?php echo number_format($item['precio'] * $item['cantidad'], 0, ',', '.'); ?></strong>
                    </li>
                    <?php endforeach; ?>
                    <li class="list-group-item bg-transparent px-0 d-flex justify-content-between fs-5 fw-bold border-top">
                        <span>Total</span>
                        <span style="color: var(--verde-oscuro);">$<?php echo number_format($total, 0, ',', '.'); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
