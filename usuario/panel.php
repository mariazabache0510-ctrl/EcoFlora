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

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            
            <div class="card panel-card">
                
                <div class="panel-header text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-person-circle me-2"></i>
                        Panel del Cliente
                    </h4>
                    <small class="text-white-50">EcoFlora Vivero</small>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    
                    <div class="mb-4">
                        <div class="welcome-text">
                            ¡Hola, <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>! 👋
                        </div>
                        <p class="description-text mb-0">
                            Bienvenido a tu espacio personal. Desde aquí puedes gestionar tus pedidos, 
                            actualizar tu información y explorar tu historial de navegación.
                        </p>
                    </div>
                    
                    <div class="action-buttons">
                        
                        <a href="<?php echo BASE_URL; ?>/catalogo.php" class="btn btn-panel btn-comprar">
                            <i class="bi bi-shop"></i>
                            <span>Ir a comprar</span>
                        </a>
                        
                        <a href="<?php echo BASE_URL; ?>/usuario/domicilios.php" class="btn btn-panel btn-pedidos">
                            <i class="bi bi-truck"></i>
                            <span>Mis Pedidos</span>
                        </a>
                        
                        <a href="<?php echo BASE_URL; ?>/usuario/editar_perfil.php" class="btn btn-panel btn-editar">
                            <i class="bi bi-pencil-square"></i>
                            <span>Editar mis datos</span>
                        </a>
                        
                        <a href="<?php echo BASE_URL; ?>/usuario/historial.php" class="btn btn-panel btn-historial">
                            <i class="bi bi-clock-history"></i>
                            <span>Historial</span>
                        </a>
                        
                        <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-panel btn-salir">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Cerrar sesión</span>
                        </a>
                        
                    </div>
                    
                </div>
                
                <div class="card-footer bg-transparent border-0 pb-4 px-4 px-md-5">
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-shield-check me-2 text-success"></i>
                        <span>Tu información está protegida con seguridad SSL</span>
                    </div>
                </div>
                
            </div>
            
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>