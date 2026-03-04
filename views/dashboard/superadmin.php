<?php
/**
 * Dashboard exclusivo del Superadministrador
 */
require_once __DIR__ . '/../../config/app.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Solo superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header("Location: " . url('views/auth/login.php'));
    exit();
}

$_SESSION['last_activity'] = time();

// --- Cargar datos ---
require_once __DIR__ . '/../../models/StatisticalReportModel.php';
require_once __DIR__ . '/../../config/Database.php';

$statsModel = new StatisticalReportModel();
$db = (new Database())->getConnection();

// Helper: ejecutar query simple y devolver valor
function saQuery($db, $sql) {
    try {
        $r = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $r ? array_values($r)[0] : 0;
    } catch (Exception $e) { return 0; }
}

// KPIs Globales
$totalUsers       = saQuery($db, "SELECT COUNT(*) FROM users");
$activeUsers      = saQuery($db, "SELECT COUNT(*) FROM users WHERE is_active = 1");
$totalAwardees    = saQuery($db, "SELECT COUNT(*) FROM awardees");
$totalContracts   = saQuery($db, "SELECT COUNT(*) FROM contracts");
$activeContracts  = saQuery($db, "SELECT COUNT(*) FROM contracts WHERE status = 'active'");
$totalStalls      = saQuery($db, "SELECT COUNT(*) FROM market_stalls");
$totalRoles       = saQuery($db, "SELECT COUNT(*) FROM roles");
$totalDepts       = saQuery($db, "SELECT COUNT(*) FROM departments");

// Infracciones
$infraTotal       = saQuery($db, "SELECT COUNT(*) FROM infractions WHERE status_logical='active'");
$infraActive      = saQuery($db, "SELECT COUNT(*) FROM infractions WHERE infraction_status IN ('Reported','In Process') AND status_logical='active'");
$infraResolved    = saQuery($db, "SELECT COUNT(*) FROM infractions WHERE infraction_status = 'Resolved' AND status_logical='active'");

// Sanciones / Multas
$sanctionTotal    = saQuery($db, "SELECT COUNT(*) FROM sanctions");
$sanctionPaid     = saQuery($db, "SELECT COUNT(*) FROM sanctions WHERE sanction_status = 'Paid'");
$sanctionPending  = saQuery($db, "SELECT COUNT(*) FROM sanctions WHERE sanction_status IN ('Imposed','Pending')");
$totalFinesBs     = saQuery($db, "SELECT COALESCE(SUM(fine_amount),0) FROM sanctions WHERE sanction_status = 'Paid'");

// Cobros del mes
$totalCollectedMonth = saQuery($db, "SELECT COALESCE(SUM(amount_paid),0) FROM fee_payments WHERE MONTH(payment_date)=MONTH(NOW()) AND YEAR(payment_date)=YEAR(NOW())");
$totalFinesMonth     = saQuery($db, "SELECT COALESCE(SUM(amount_paid),0) FROM fine_payments WHERE MONTH(payment_date)=MONTH(NOW()) AND YEAR(payment_date)=YEAR(NOW())");

// Contratos por tipo
$contractTypes = $db->query("SELECT type, COUNT(*) as total FROM contracts GROUP BY type ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);

// Registro de usuarios recientes
$recentUsers = $db->query("SELECT u.username, u.email, u.is_active, u.created_at FROM users u ORDER BY u.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Quejas abiertas
$openComplaints = saQuery($db, "SELECT COUNT(*) FROM complaints WHERE complaint_status IN ('Received','In Process')");

// Cajas activas
$activeCashRegisters = saQuery($db, "SELECT COUNT(*) FROM cash_registers WHERE status='active'");

// Alertas activas
$activeAlerts = saQuery($db, "SELECT COUNT(*) FROM alerts WHERE status='active'");

// Inspectores
$totalInspectors = saQuery($db, "SELECT COUNT(*) FROM inspectors WHERE is_active=1");
$reportsThisMonth = saQuery($db, "SELECT COUNT(*) FROM inspection_reports WHERE MONTH(creation_date)=MONTH(NOW()) AND YEAR(creation_date)=YEAR(NOW())");

// Para gráficos
$infractionsChart = $statsModel->getInfractionsByMonth(6);
$employeesChart   = $statsModel->getEmployeesByDepartment();

// Cobros por mes (ultimos 6)
$collectionByMonth = [];
$collectionLabels  = [];
$spanish_months = ['Jan'=>'Ene','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Abr','May'=>'May','Jun'=>'Jun',
                   'Jul'=>'Jul','Aug'=>'Ago','Sep'=>'Sep','Oct'=>'Oct','Nov'=>'Nov','Dec'=>'Dic'];
for ($i = 5; $i >= 0; $i--) {
    $collectionLabels[] = $spanish_months[date('M', strtotime("-$i months"))];
    $monyear = date('Y-m', strtotime("-$i months"));
    $y = date('Y', strtotime("-$i months"));
    $m = date('m', strtotime("-$i months"));
    $feeAmt  = saQuery($db, "SELECT COALESCE(SUM(amount_paid),0) FROM fee_payments WHERE YEAR(payment_date)=$y AND MONTH(payment_date)=$m");
    $fineAmt = saQuery($db, "SELECT COALESCE(SUM(amount_paid),0) FROM fine_payments WHERE YEAR(payment_date)=$y AND MONTH(payment_date)=$m");
    $collectionByMonth[] = round((float)$feeAmt + (float)$fineAmt, 2);
}

$userName = $_SESSION['user_name'] ?? 'Super Admin';

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
.sa-hero {
    background: linear-gradient(135deg, #1a1f3c 0%, #2d3561 50%, #3d4a8a 100%);
    border-radius: 1rem;
    color: #fff;
    padding: 2rem 2.5rem;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.sa-hero::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}
.sa-hero::after {
    content: '';
    position: absolute;
    bottom: -60px; right: 80px;
    width: 280px; height: 280px;
    background: rgba(255,255,255,0.03);
    border-radius: 50%;
}
.sa-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 50px; padding: 0.3rem 1rem;
    font-size: 0.75rem; font-weight: 700; letter-spacing: 0.08em;
    text-transform: uppercase; color: #c9d8ff;
    margin-bottom: 1rem;
}
.sa-kpi-card {
    background: #fff;
    border-radius: 0.75rem;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    border: 1px solid #f0f0f5;
    transition: transform 0.18s, box-shadow 0.18s;
    height: 100%;
}
.sa-kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.11); }
.sa-kpi-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.sa-kpi-value { font-size: 1.75rem; font-weight: 800; line-height: 1; color: #1a1f3c; }
.sa-kpi-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #9b9db5; margin-top: 0.25rem; }
.sa-kpi-sub { font-size: 0.8rem; color: #6c757d; margin-top: 0.2rem; }
.sa-section-title {
    font-size: 0.7rem; font-weight: 800; letter-spacing: 0.1em;
    text-transform: uppercase; color: #9b9db5;
    margin: 1.75rem 0 0.75rem;
    border-bottom: 1px solid #f0f0f5; padding-bottom: 0.4rem;
}
.sa-chart-card {
    background: #fff; border-radius: 0.75rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    border: 1px solid #f0f0f5; overflow: hidden;
}
.sa-chart-card .sa-chart-header {
    padding: 1rem 1.5rem 0.5rem;
    border-bottom: 1px solid #f5f5f8;
}
.sa-chart-card .sa-chart-header h6 { font-weight: 700; font-size: 0.9rem; color: #1a1f3c; margin: 0; }
.sa-chart-card .sa-chart-body { padding: 1.25rem 1.5rem; }
.sa-quicklink {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.65rem 1rem; border-radius: 0.5rem;
    color: #2d3561; font-weight: 600; font-size: 0.85rem;
    background: #f7f8ff; border: 1px solid #ebebf8;
    text-decoration: none; transition: all 0.15s;
}
.sa-quicklink:hover { background: #eceeff; color: #696cff; border-color: #c2c6ff; transform: translateX(3px); }
.sa-quicklink i { font-size: 1.1rem; }
</style>

<div class="main-content" style="padding: 1.5rem;">
<div class="container-fluid">

    <!-- Hero -->
    <div class="sa-hero">
        <div class="sa-badge"><i class="ri-shield-star-line"></i> Superadministrador</div>
        <h2 class="fw-bold mb-1" style="font-size:1.8rem;">Panel de Control General</h2>
        <p class="mb-0" style="opacity:0.75;">Bienvenido, <strong><?= htmlspecialchars($userName) ?></strong>. Vista completa del sistema SERAMER — <?= date('d \d\e F \d\e Y') ?>.</p>
        <div class="mt-3 d-flex flex-wrap gap-3">
            <div style="background:rgba(255,255,255,0.1);border-radius:0.5rem;padding:0.5rem 1rem;">
                <div style="font-size:0.65rem;opacity:0.7;text-transform:uppercase;letter-spacing:0.08em;">Usuarios Activos</div>
                <div style="font-size:1.3rem;font-weight:800;"><?= $activeUsers ?> / <?= $totalUsers ?></div>
            </div>
            <div style="background:rgba(255,255,255,0.1);border-radius:0.5rem;padding:0.5rem 1rem;">
                <div style="font-size:0.65rem;opacity:0.7;text-transform:uppercase;letter-spacing:0.08em;">Contratos Activos</div>
                <div style="font-size:1.3rem;font-weight:800;"><?= $activeContracts ?></div>
            </div>
            <div style="background:rgba(255,255,255,0.1);border-radius:0.5rem;padding:0.5rem 1rem;">
                <div style="font-size:0.65rem;opacity:0.7;text-transform:uppercase;letter-spacing:0.08em;">Cajas Activas</div>
                <div style="font-size:1.3rem;font-weight:800;"><?= $activeCashRegisters ?></div>
            </div>
            <div style="background:rgba(255,255,255,0.1);border-radius:0.5rem;padding:0.5rem 1rem;">
                <div style="font-size:0.65rem;opacity:0.7;text-transform:uppercase;letter-spacing:0.08em;">Alertas Activas</div>
                <div style="font-size:1.3rem;font-weight:800;"><?= $activeAlerts ?></div>
            </div>
        </div>
    </div>

    <!-- ===== KPIs: SISTEMA ===== -->
    <div class="sa-section-title"><i class="ri-database-2-line me-1"></i>Sistema & Usuarios</div>
    <div class="row g-3">
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="sa-kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#eceeff;color:#696cff;"><i class="ri-user-3-line"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?= $totalUsers ?></div>
                        <div class="sa-kpi-label">Usuarios</div>
                        <div class="sa-kpi-sub"><?= $activeUsers ?> activos</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="sa-kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#e8fadf;color:#71dd37;"><i class="ri-group-line"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?= $totalAwardees ?></div>
                        <div class="sa-kpi-label">Adjudicatarios</div>
                        <div class="sa-kpi-sub">Registrados</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="sa-kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#fff2d6;color:#ffab00;"><i class="ri-store-2-line"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?= $totalStalls ?></div>
                        <div class="sa-kpi-label">Puestos</div>
                        <div class="sa-kpi-sub">Mercado</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="sa-kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#fce8e8;color:#ff4c51;"><i class="ri-shield-line"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?= $totalRoles ?></div>
                        <div class="sa-kpi-label">Roles</div>
                        <div class="sa-kpi-sub"><?= $totalDepts ?> Depts.</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="sa-kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#e0f3ff;color:#03c3ec;"><i class="ri-user-search-line"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?= $totalInspectors ?></div>
                        <div class="sa-kpi-label">Inspectores</div>
                        <div class="sa-kpi-sub"><?= $reportsThisMonth ?> repts. mes</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="sa-kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#fde8ff;color:#c955e8;"><i class="ri-chat-voice-line"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?= $openComplaints ?></div>
                        <div class="sa-kpi-label">Quejas Abiertas</div>
                        <div class="sa-kpi-sub">Sin resolver</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== KPIs: CONTRATOS ===== -->
    <div class="sa-section-title"><i class="ri-file-list-3-line me-1"></i>Contratos</div>
    <div class="row g-3">
        <div class="col-6 col-sm-4 col-lg-3">
            <div class="sa-kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#eceeff;color:#696cff;"><i class="ri-file-list-line"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?= $totalContracts ?></div>
                        <div class="sa-kpi-label">Total Contratos</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-3">
            <div class="sa-kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#e8fadf;color:#71dd37;"><i class="ri-checkbox-circle-line"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?= $activeContracts ?></div>
                        <div class="sa-kpi-label">Contratos Activos</div>
                    </div>
                </div>
            </div>
        </div>
        <?php foreach ($contractTypes as $ct): ?>
        <div class="col-6 col-sm-4 col-lg-3">
            <div class="sa-kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#f0f8ff;color:#03c3ec;"><i class="ri-contract-line"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?= $ct['total'] ?></div>
                        <div class="sa-kpi-label"><?= htmlspecialchars(ucfirst($ct['type'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ===== KPIs: INFRACCIONES & MULTAS ===== -->
    <div class="sa-section-title"><i class="ri-alert-line me-1"></i>Infracciones & Multas</div>
    <div class="row g-3">
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="sa-kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#fff2d6;color:#ffab00;"><i class="ri-error-warning-line"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?= $infraActive ?></div>
                        <div class="sa-kpi-label">Infracciones Activas</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="sa-kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#e8fadf;color:#71dd37;"><i class="ri-check-double-line"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?= $infraResolved ?></div>
                        <div class="sa-kpi-label">Resueltas</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="sa-kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#fce8e8;color:#ff4c51;"><i class="ri-scales-line"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?= $sanctionPending ?></div>
                        <div class="sa-kpi-label">Multas Pendientes</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="sa-kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#e8fadf;color:#71dd37;"><i class="ri-money-dollar-circle-line"></i></div>
                    <div>
                        <div class="sa-kpi-value"><?= $sanctionPaid ?></div>
                        <div class="sa-kpi-label">Multas Pagadas</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-4">
            <div class="sa-kpi-card" style="border-left: 4px solid #71dd37;">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#e8fadf;color:#71dd37; width:56px;height:56px;"><i class="ri-funds-line"></i></div>
                    <div>
                        <div class="sa-kpi-value" style="font-size:1.35rem;">Bs. <?= number_format((float)$totalFinesBs, 2, ',', '.') ?></div>
                        <div class="sa-kpi-label">Total Recaudado (Multas)</div>
                        <div class="sa-kpi-sub">Histórico pagado</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== COBROS DEL MES ===== -->
    <div class="sa-section-title"><i class="ri-bank-card-line me-1"></i>Cobranza — Mes Actual</div>
    <div class="row g-3 mb-3">
        <div class="col-sm-6">
            <div class="sa-kpi-card" style="border-left: 4px solid #696cff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#eceeff;color:#696cff;width:56px;height:56px;"><i class="ri-receipt-line"></i></div>
                    <div>
                        <div class="sa-kpi-value" style="font-size:1.35rem;">Bs. <?= number_format((float)$totalCollectedMonth, 2, ',', '.') ?></div>
                        <div class="sa-kpi-label">Cobros de Contratos (mes)</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="sa-kpi-card" style="border-left: 4px solid #ffab00;">
                <div class="d-flex align-items-center gap-3">
                    <div class="sa-kpi-icon" style="background:#fff2d6;color:#ffab00;width:56px;height:56px;"><i class="ri-money-dollar-box-line"></i></div>
                    <div>
                        <div class="sa-kpi-value" style="font-size:1.35rem;">Bs. <?= number_format((float)$totalFinesMonth, 2, ',', '.') ?></div>
                        <div class="sa-kpi-label">Cobros de Multas (mes)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== GRÁFICOS ===== -->
    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <div class="sa-chart-card">
                <div class="sa-chart-header">
                    <h6><i class="ri-bar-chart-2-line me-2" style="color:#696cff;"></i>Recaudación Total (Últimos 6 Meses)</h6>
                </div>
                <div class="sa-chart-body">
                    <canvas id="collectionChart" height="260"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="sa-chart-card">
                <div class="sa-chart-header">
                    <h6><i class="ri-pie-chart-2-line me-2" style="color:#696cff;"></i>Empleados por Departamento</h6>
                </div>
                <div class="sa-chart-body">
                    <canvas id="deptChart" height="260"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-6">
            <div class="sa-chart-card">
                <div class="sa-chart-header">
                    <h6><i class="ri-line-chart-line me-2" style="color:#696cff;"></i>Infracciones por Mes</h6>
                </div>
                <div class="sa-chart-body">
                    <canvas id="infractionsChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <!-- Accesos Rápidos -->
            <div class="sa-chart-card h-100">
                <div class="sa-chart-header">
                    <h6><i class="ri-compass-3-line me-2" style="color:#696cff;"></i>Accesos Rápidos del Administrador</h6>
                </div>
                <div class="sa-chart-body">
                    <div class="d-flex flex-column gap-2">
                        <a href="../users/index.php" class="sa-quicklink"><i class="ri-user-settings-line"></i>Gestión de Usuarios</a>
                        <a href="../roles/index.php" class="sa-quicklink"><i class="ri-shield-keyhole-line"></i>Gestión de Roles</a>
                        <a href="../awardees/index.php" class="sa-quicklink"><i class="ri-group-line"></i>Adjudicatarios</a>
                        <a href="../contracts/index.php" class="sa-quicklink"><i class="ri-file-list-3-line"></i>Contratos</a>
                        <a href="../cash_registers/index.php" class="sa-quicklink"><i class="ri-archive-drawer-line"></i>Gestión de Cajas</a>
                        <a href="../departments/index.php" class="sa-quicklink"><i class="ri-building-line"></i>Departamentos</a>
                        <a href="../statistical-reports/index.php" class="sa-quicklink"><i class="ri-bar-chart-line"></i>Reportes Estadísticos</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usuarios Recientes -->
    <div class="sa-section-title mt-4"><i class="ri-user-add-line me-1"></i>Últimos Usuarios Registrados</div>
    <div class="sa-chart-card mb-4">
        <div class="sa-chart-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead style="background:#f7f8ff;">
                        <tr>
                            <th class="ps-4">Usuario</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Fecha de Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                        <tr>
                            <td class="ps-4 fw-semibold"><?= htmlspecialchars($u['username']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="badge" style="background:#e8fadf;color:#71dd37;font-weight:700;">Activo</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#fce8e8;color:#ff4c51;font-weight:700;">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted"><?= $u['created_at'] ? date('d/m/Y H:i', strtotime($u['created_at'])) : '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script src="<?php echo vendor('libs/chartjs/chartjs.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const palette = ['#696cff','#71dd37','#ffab00','#ff4c51','#03c3ec','#c955e8','#2d3561','#4a90d9'];

    // --- Recaudación Total por mes ---
    new Chart(document.getElementById('collectionChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($collectionLabels) ?>,
            datasets: [{
                label: 'Recaudado (Bs.)',
                data: <?= json_encode($collectionByMonth) ?>,
                backgroundColor: 'rgba(105,108,255,0.7)',
                borderColor: '#696cff',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { callback: v => 'Bs. ' + v.toLocaleString('es-VE') } } },
            plugins: { legend: { display: false } }
        }
    });

    // --- Empleados por Departamento ---
    new Chart(document.getElementById('deptChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($employeesChart['labels']) ?>,
            datasets: [{
                data: <?= json_encode($employeesChart['data']) ?>,
                backgroundColor: <?= json_encode($employeesChart['backgroundColor']) ?>,
                borderColor: '#fff', borderWidth: 2, hoverOffset: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
        }
    });

    // --- Infracciones por mes ---
    const ctxI = document.getElementById('infractionsChart').getContext('2d');
    const grad = ctxI.createLinearGradient(0,0,0,300);
    grad.addColorStop(0, 'rgba(255,171,0,0.5)');
    grad.addColorStop(1, 'rgba(255,171,0,0.02)');
    new Chart(ctxI, {
        type: 'line',
        data: {
            labels: <?= json_encode($infractionsChart['labels']) ?>,
            datasets: [{
                label: 'Infracciones',
                data: <?= json_encode($infractionsChart['data']) ?>,
                borderColor: '#ffab00', backgroundColor: grad,
                fill: true, tension: 0.4, borderWidth: 3,
                pointBackgroundColor: '#fff', pointBorderColor: '#ffab00', pointBorderWidth: 2, pointRadius: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { display: false } }
        }
    });
});
</script>
