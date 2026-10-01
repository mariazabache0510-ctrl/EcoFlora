<?php
require_once __DIR__ . '/../src/config/bootstrap.php';

$host = env('DB_HOST', 'localhost');
$puerto = env('DB_PORT', '5432');
$usuario = env('DB_USERNAME', 'postgres');
$password = env('DB_PASSWORD', 'postgres');
$base_datos = env('DB_DATABASE', 'ecoflora_db');

try {
    $conexion = new PDO(
        "pgsql:host=$host;port=$puerto;dbname=$base_datos",
        $usuario,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$appUrl = env('APP_URL', 'http://localhost:8080');
$parsedUrl = parse_url($appUrl);
$baseHost = $parsedUrl['host'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = ($parsedUrl['scheme'] ?? (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')) . '://';

define('BASE_URL', $protocol . $baseHost);

function registrarHistorial($conexion, $pagina, $url) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $id_usuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conexion->prepare("INSERT INTO historial_navegacion (id_usuario, ip_address, pagina, url) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_usuario, $ip, $pagina, $url]);
}
?>
