<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conexion.php';
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php?error=Acceso no autorizado");
    exit();
}
$id_usuario = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_usuario == $_SESSION['id_usuario']) {
    header("Location: " . BASE_URL . "/admin/usuarios.php?error=No puedes eliminar tu propia cuenta");
    exit();
}
try {
    $conexion->beginTransaction();
    $stmt = $conexion->prepare("DELETE FROM carrito WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $stmt = $conexion->prepare("DELETE FROM historial_navegacion WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $stmt = $conexion->prepare("SELECT id_pedido FROM pedidos WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt_det = $conexion->prepare("DELETE FROM pedido_detalle WHERE id_pedido = ?");
    foreach ($pedidos as $p) {
        $stmt_det->execute([$p['id_pedido']]);
    }
    $stmt = $conexion->prepare("DELETE FROM pedidos WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $ok = $stmt->execute([$id_usuario]);
    $conexion->commit();
    if ($ok) header("Location: " . BASE_URL . "/admin/usuarios.php?eliminado=1");
    else header("Location: " . BASE_URL . "/admin/usuarios.php?error=No se pudo eliminar");
} catch (Exception $e) {
    $conexion->rollBack();
    header("Location: " . BASE_URL . "/admin/usuarios.php?error=No se pudo eliminar");
}
exit();
?>
