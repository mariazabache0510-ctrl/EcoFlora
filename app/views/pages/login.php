<?php
// ============================================
// 1. INICIAR SESIÓN AL INICIO (ANTES DE TODO)
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 3) . '/config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    
    if (empty($usuario) || empty($password)) {
        $error = "Completa todos los campos.";
    } else {
        $stmt = $conexion->prepare("SELECT id_usuario, nombre, usuario, password, rol FROM usuarios WHERE usuario = ? OR email = ?");
        $stmt->execute([$usuario, $usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            if (password_verify($password, $user['password'])) {
                // La sesión ya está iniciada arriba, solo guardamos datos
                $_SESSION['id_usuario'] = $user['id_usuario'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['rol'] = $user['rol'];
                
                // Migrar carrito de sesión
                if (isset($_SESSION['sesion_carrito']) && !empty($_SESSION['sesion_carrito'])) {
                    foreach ($_SESSION['sesion_carrito'] as $id_planta => $cantidad) {
                        $stmt = $conexion->prepare("INSERT INTO carrito (id_usuario, id_planta, cantidad) VALUES (?, ?, ?) ON CONFLICT (id_usuario, id_planta) DO UPDATE SET cantidad = carrito.cantidad + EXCLUDED.cantidad");
                        $stmt->execute([$user['id_usuario'], $id_planta, $cantidad]);
                    }
                    unset($_SESSION['sesion_carrito']);
                }
                
                // Redirigir según rol
                if ($user['rol'] === 'admin') {
                    header("Location: " . BASE_URL . "/admin/panel_admin.php");
                } else {
                    header("Location: " . BASE_URL . "/index.php");
                }
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
    }
}


include dirname(__DIR__, 1) . '/partials/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0" style="border-radius: 20px;">
                <div class="card-header bg-white border-0 text-center pt-4">
                    <i class="bi bi-flower1 fs-1" style="color: var(--verde-medio);"></i>
                    <h4 class="mt-2 fw-bold">Bienvenido a EcoFlora</h4>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Usuario o Email</label>
                            <input type="text" name="usuario" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-ecoflora w-100">Ingresar</button>
                    </form>
                    <div class="text-center mt-3">
                        <small>¿No tienes cuenta? <a href="<?php echo BASE_URL; ?>/registro.php" class="text-decoration-none fw-bold" style="color: var(--verde-medio);">Regístrate</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 1) . '/partials/footer.php'; ?>
