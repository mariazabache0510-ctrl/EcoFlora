<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . BASE_URL . "/login.php?error=Debes iniciar sesión");
    exit();
}

if ($_SESSION['rol'] !== 'cliente' && $_SESSION['rol'] !== 'usuario') {
    header("Location: " . BASE_URL . "/index.php?error=Acceso no autorizado");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/usuario/editar_perfil.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre     = isset($_POST['nombre'])     ? trim($_POST['nombre'])     : '';
$email      = isset($_POST['email'])      ? trim($_POST['email'])      : '';
$telefono   = isset($_POST['telefono'])   ? trim($_POST['telefono'])   : '';
$direccion  = isset($_POST['direccion'])  ? trim($_POST['direccion'])  : '';
$ciudad     = isset($_POST['ciudad'])     ? trim($_POST['ciudad'])     : '';

if (empty($nombre) || empty($email)) {
    header("Location: " . BASE_URL . "/usuario/editar_perfil.php?error=" . urlencode("Nombre y email son obligatorios"));
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: " . BASE_URL . "/usuario/editar_perfil.php?error=" . urlencode("El email no tiene un formato válido"));
    exit();
}

$check = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario <> ?");
$check->execute([$email, $id_usuario]);
if ($check->fetch(PDO::FETCH_ASSOC)) {
    header("Location: " . BASE_URL . "/usuario/editar_perfil.php?error=" . urlencode("El email ya está registrado por otro usuario"));
    exit();
}

$sql = $conexion->prepare("UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, direccion = ?, ciudad = ? WHERE id_usuario = ?");

if ($sql->execute([$nombre, $email, $telefono, $direccion, $ciudad, $id_usuario])) {
    // Actualizar el nombre en la sesión para que se vea reflejado inmediatamente
    $_SESSION['nombre'] = $nombre;
    
    header("Location: " . BASE_URL . "/usuario/editar_perfil.php?actualizado=1");
    exit();
} else {
    header("Location: " . BASE_URL . "/usuario/editar_perfil.php?error=" . urlencode("Error al actualizar los datos"));
    exit();
}
?>
