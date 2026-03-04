<?php
/**
 * Dashboard por defecto — para departamentos sin panel propio
 */
require_once __DIR__ . '/../../config/app.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: " . url('views/auth/login.php'));
    exit();
}
$_SESSION['last_activity'] = time();

$userName   = $_SESSION['user_name'] ?? 'Usuario';
$department = $_SESSION['selected_department'] ?? 'Sin Departamento';
$userRole   = $_SESSION['user_role'] ?? 'user';

$hour = (int)date('H');
if ($hour >= 5 && $hour < 12)       $greeting = "Buenos días";
elseif ($hour >= 12 && $hour < 18)  $greeting = "Buenas tardes";
else                                 $greeting = "Buenas noches";

// Tip del día (rotativo)
$tips = [
    ["ri-shield-check-line", "Nunca compartas tus credenciales de acceso con otras personas.", "#696cff"],
    ["ri-lock-2-line", "Cierra sesión al terminar tu jornada para proteger la información.", "#ff4c51"],
    ["ri-refresh-line", "Si encuentras algún error o inconsistencia en el sistema, repórtalo al administrador.", "#ffab00"],
    ["ri-file-text-line", "Mantén la información de los registros siempre actualizada y verificada.", "#71dd37"],
    ["ri-customer-service-2-line", "Ante cualquier duda sobre el uso del sistema, contacta al soporte técnico.", "#03c3ec"],
];
$tip = $tips[date('j') % count($tips)];

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
.def-hero {
    background: linear-gradient(135deg, #f7f8ff 0%, #eef0ff 100%);
    border: 1px solid #e0e2f5;
    border-radius: 1.25rem;
    padding: 3rem 2.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.def-hero::before {
    content: '';
    position: absolute;
    top: -70px; left: 50%;
    transform: translateX(-50%);
    width: 320px; height: 320px;
    background: radial-gradient(circle, rgba(105,108,255,0.08), transparent 70%);
    pointer-events: none;
}
.def-avatar {
    width: 80px; height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #696cff, #9c9eff);
    display: flex; align-items: center; justify-content: center;
    font-size: 2.2rem; color: #fff;
    margin: 0 auto 1.25rem;
    box-shadow: 0 8px 24px rgba(105,108,255,0.3);
}
.def-dept-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: #eceeff; color: #696cff;
    border-radius: 50px; padding: 0.3rem 1rem;
    font-size: 0.75rem; font-weight: 700;
    letter-spacing: 0.06em; text-transform: uppercase;
    margin-bottom: 0.75rem;
}
.def-greeting {
    font-size: 1.9rem; font-weight: 800; color: #1a1f3c;
    line-height: 1.15;
}
.def-sub { color: #6c757d; margin-top: 0.5rem; font-size: 0.95rem; }
.def-tip-card {
    background: #fff; border-radius: 0.875rem;
    border: 1px solid #e0e2f5;
    padding: 1.25rem 1.5rem;
    display: flex; align-items: flex-start; gap: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
}
.def-tip-icon {
    width: 44px; height: 44px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; flex-shrink: 0;
}
.def-shortcut {
    text-decoration: none; color: #2d3561;
    background: #fff; border: 1px solid #e0e2f5;
    border-radius: 0.75rem; padding: 1.25rem;
    display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
    font-weight: 700; font-size: 0.82rem; text-align: center;
    transition: all 0.18s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.def-shortcut:hover {
    background: #eceeff; border-color: #c2c6ff;
    color: #696cff; transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(105,108,255,0.15);
}
.def-shortcut i { font-size: 1.75rem; }
.def-info-row {
    display: flex; align-items: center; gap: 0.6rem;
    padding: 0.6rem 0; border-bottom: 1px solid #f0f0f8;
    font-size: 0.88rem; color: #4a4a6a;
}
.def-info-row:last-child { border-bottom: none; }
.def-info-row i { color: #696cff; font-size: 1rem; width: 22px; }
</style>

<div class="main-content" style="padding: 1.5rem;">
<div class="container-fluid">
<div class="row justify-content-center">
<div class="col-xl-9 col-lg-11">

    <!-- Hero -->
    <div class="def-hero mb-4">
        <div class="def-avatar">
            <?= strtoupper(substr($userName, 0, 1)) ?>
        </div>
        <div class="def-dept-badge">
            <i class="ri-building-line"></i> <?= htmlspecialchars($department) ?>
        </div>
        <div class="def-greeting"><?= $greeting ?>, <?= htmlspecialchars($userName) ?> 👋</div>
        <p class="def-sub">Bienvenido al Sistema de Gestión SERAMER.<br>
        Usa el menú lateral para acceder a las funciones disponibles para tu departamento.</p>
        <div class="mt-3">
            <span class="badge" style="background:#eceeff;color:#696cff;font-size:0.75rem;padding:0.4rem 0.8rem;">
                <i class="ri-time-line me-1"></i><?= date('d \d\e F \d\e Y — H:i') ?>
            </span>
        </div>
    </div>

    <div class="row g-4 justify-content-center">

        <!-- Info de Sesión -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm mb-3" style="border-radius:0.875rem;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-uppercase" style="font-size:0.7rem;letter-spacing:0.08em;color:#9b9db5;">
                        <i class="ri-account-circle-line me-1"></i>Mi Sesión
                    </h6>
                    <div class="def-info-row"><i class="ri-user-line"></i> <?= htmlspecialchars($userName) ?></div>
                    <div class="def-info-row"><i class="ri-building-line"></i> <?= htmlspecialchars($department) ?></div>
                    <div class="def-info-row"><i class="ri-shield-line"></i> <?= htmlspecialchars(ucfirst($userRole)) ?></div>
                    <div class="def-info-row"><i class="ri-calendar-line"></i> <?= date('d/m/Y') ?></div>
                </div>
            </div>
        </div>

        <!-- Tip del día -->
        <div class="col-md-5">
            <div class="def-tip-card h-100" style="border-radius:0.875rem;box-shadow:0 2px 10px rgba(0,0,0,0.04);">
                <div class="def-tip-icon" style="background:<?= $tip[2] ?>22;color:<?= $tip[2] ?>;">
                    <i class="<?= $tip[0] ?>"></i>
                </div>
                <div>
                    <div style="font-size:0.65rem;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;color:#9b9db5;margin-bottom:0.25rem;">Consejo del día</div>
                    <p class="mb-0" style="font-size:0.82rem;color:#4a4a6a;line-height:1.5;"><?= $tip[1] ?></p>
                </div>
            </div>
        </div>

    </div>

    <!-- Mensaje informativo -->
    <div class="text-center mt-4 mb-3">
        <p class="text-muted" style="font-size:0.82rem;">
            <i class="ri-information-line me-1"></i>
            Este departamento no cuenta con un panel estadístico personalizado. Contacta al administrador si necesitas acceso a reportes específicos.
        </p>
    </div>

</div>
</div>
</div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
