<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . BASE_URL . "/login.php?error=Debes iniciar sesión");
    exit();
}

if ($_SESSION['rol'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php?error=Acceso no autorizado");
    exit();
}

$total_plantas = $conexion->query("SELECT COUNT(*) as c FROM plantas")->fetch(PDO::FETCH_ASSOC)['c'];
$total_pedidos = $conexion->query("SELECT COUNT(*) as c FROM pedidos")->fetch(PDO::FETCH_ASSOC)['c'];
$total_usuarios = $conexion->query("SELECT COUNT(*) as c FROM usuarios WHERE rol = 'cliente'")->fetch(PDO::FETCH_ASSOC)['c'];
$pendientes = $conexion->query("SELECT COUNT(*) as c FROM pedidos WHERE estado = 'pendiente'")->fetch(PDO::FETCH_ASSOC)['c'];


include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            
            <div class="card admin-card">
                
                <!-- HEADER -->
                <div class="admin-header text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Panel de Administración
                    </h4>
                    <small class="text-white-50">EcoFlora Vivero - Gestión integral</small>
                </div>
                
                <!-- CUERPO -->
                <div class="card-body p-4 p-md-5">
                    
                    <!-- SALUDO -->
                    <div class="mb-4">
                        <div class="admin-welcome">
                            ¡Hola, <span class="admin-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>! 👋
                        </div>
                        <p class="admin-description mb-0">
                            Bienvenido al centro de control. Desde aquí puedes gestionar usuarios, 
                            monitorear pedidos y administrar el inventario de plantas.
                        </p>
                    </div>
                    
                    <!-- STATS -->
                    <div class="stats-grid">
                        
                        <div class="stat-card stat-plantas">
                            <span class="stat-icon">🌱</span>
                            <div class="stat-number"><?php echo $total_plantas; ?></div>
                            <div class="stat-label">Plantas</div>
                        </div>
                        
                        <div class="stat-card stat-pedidos">
                            <span class="stat-icon">📦</span>
                            <div class="stat-number"><?php echo $total_pedidos; ?></div>
                            <div class="stat-label">Pedidos</div>
                        </div>
                        
                        <div class="stat-card stat-clientes">
                            <span class="stat-icon">👥</span>
                            <div class="stat-number"><?php echo $total_usuarios; ?></div>
                            <div class="stat-label">Clientes</div>
                        </div>
                        
                        <div class="stat-card stat-pendientes">
                            <span class="stat-icon">⏰</span>
                            <div class="stat-number"><?php echo $pendientes; ?></div>
                            <div class="stat-label">Pendientes</div>
                        </div>
                        
                    </div>
                    
                    <!-- BOTONES -->
                    <div class="admin-buttons">
                        
                        <a href="<?php echo BASE_URL; ?>/admin/usuarios.php" class="btn btn-admin btn-admin-usuarios">
                            <i class="bi bi-people"></i>
                            <span>Gestionar Usuarios</span>
                        </a>
                        
                        <a href="<?php echo BASE_URL; ?>/admin/pedidos_admin.php" class="btn btn-admin btn-admin-pedidos">
                            <i class="bi bi-truck"></i>
                            <span>Ver Pedidos / Domicilios</span>
                        </a>
                        
                        <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-admin btn-admin-tienda">
                            <i class="bi bi-shop"></i>
                            <span>Ver tienda</span>
                        </a>
                        
                        <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-admin btn-admin-salir">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Cerrar sesión</span>
                        </a>
                        
                    </div>
                    
                </div>
                
                <!-- FOOTER -->
                <div class="card-footer bg-transparent border-0 pb-4 px-4 px-md-5">
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-shield-check me-2 text-success"></i>
                        <span>Panel seguro - Solo personal autorizado</span>
                    </div>
                </div>
                
            </div>
            
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
