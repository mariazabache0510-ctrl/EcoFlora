<?php
// ============================================
// 1. INICIAR SESIÓN PRIMERO
// ============================================
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

$usuarios = $conexion->query("
    SELECT id_usuario, nombre, usuario, email, telefono, direccion, ciudad, rol, fecha_registro 
    FROM usuarios 
    ORDER BY fecha_registro DESC
");


include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-11 col-xl-10">
            
            <div class="card users-card">
                
                <!-- HEADER -->
                <div class="users-header text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-people me-2"></i>
                        Administración de Usuarios
                    </h4>
                    <small class="text-white-50">Gestiona los clientes registrados</small>
                </div>
                
                <!-- CUERPO -->
                <div class="card-body p-4 p-md-5">
                    
                    <!-- ALERTAS -->
                    <?php if(isset($_GET['eliminado'])): ?>
                        <div class="alert-users alert-users-success">
                            <i class="bi bi-check-circle-fill"></i>
                            Usuario eliminado correctamente.
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_GET['actualizado'])): ?>
                        <div class="alert-users alert-users-success">
                            <i class="bi bi-check-circle-fill"></i>
                            Usuario actualizado correctamente.
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_GET['error'])): ?>
                        <div class="alert-users alert-users-error">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- TABLA -->
                    <div class="table-users-container">
                        <table class="table table-users">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Ciudad</th>
                                    <th>Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($u = $usuarios->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($u['nombre']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <i class="bi bi-telephone me-1 text-muted"></i>
                                        <?php echo htmlspecialchars($u['telefono'] ?: '—'); ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-geo-alt me-1 text-muted"></i>
                                        <?php echo htmlspecialchars($u['ciudad'] ?: '—'); ?>
                                    </td>
                                    <td>
                                        <span class="badge-rol <?php echo ($u['rol']=='admin') ? 'badge-admin' : 'badge-cliente'; ?>">
                                            <?php echo $u['rol']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="<?php echo BASE_URL; ?>/admin/editar_usuario.php?id=<?php echo $u['id_usuario']; ?>" 
                                               class="btn-action btn-edit">
                                                <i class="bi bi-pencil"></i>
                                                Editar
                                            </a>
                                            
                                            <?php if($u['id_usuario'] != $_SESSION['id_usuario']): ?>
                                            <a href="<?php echo BASE_URL; ?>/admin/eliminar_usuario.php?id=<?php echo $u['id_usuario']; ?>" 
                                               class="btn-action btn-delete"
                                               onclick="return confirm('¿Seguro que deseas eliminar a <?php echo htmlspecialchars($u['nombre']); ?>?');">
                                                <i class="bi bi-trash"></i>
                                                Eliminar
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- BOTÓN VOLVER -->
                    <a href="<?php echo BASE_URL; ?>/admin/panel_admin.php" class="btn-volver">
                        <i class="bi bi-arrow-left"></i>
                        Volver al panel
                    </a>
                    
                </div>
                
            </div>
            
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>