<?php
$host = "localhost";
$puerto = "5433";
$usuario = "postgres";
$password = "1122515853";  // Reemplaza con la contraseña de PostgreSQL
$base_datos = "ecoflora_db";

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

$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host_name = $_SERVER['HTTP_HOST'];
// Sin sufijo /ecoflora: correcto para `php -S localhost:8000` desde esta carpeta.
// Si más adelante sirves bajo XAMPP como /ecoflora, añade de nuevo: . '/ecoflora'
define('BASE_URL', $protocolo . $host_name);

function registrarHistorial($conexion, $pagina, $url) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $id_usuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conexion->prepare("INSERT INTO historial_navegacion (id_usuario, ip_address, pagina, url) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_usuario, $ip, $pagina, $url]);
}
?>
