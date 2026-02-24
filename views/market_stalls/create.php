<?php
require_once __DIR__ . '/../../controllers/MarketStallController.php';

$controller = new MarketStallController();
$data_view = $controller->create();
$page_title = $data_view['page_title'];
$zones = $data_view['zones'];
// sectors logic needs JS or preloading. Simple approach: Load all sectors or dynamic fetch. 
// For now, let's load all sectors and filter by JS if needed, or better, just list them.
// Actually controller create() returns 'sectors' as well.
$sectors = $data_view['sectors'];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->store($_POST);
    if ($result['success']) {
        header('Location: index.php');
        exit;
    } else {
        $error = $result['message'];
    }
}

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h4 class="card-title mb-1 d-flex align-items-center">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                                <i class="ri-store-2-line" style="color: #696cff; font-size: 1.5rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php">Locales</a></li>
                                <li class="breadcrumb-item active">Nuevo</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card-body">
                         <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Zona</label>
                                    <select id="zone_select" class="form-control" onchange="filterSectors()">
                                        <option value="">Seleccione Zona</option>
                                        <?php foreach ($zones as $zone): ?>
                                            <option value="<?php echo $zone['id']; ?>"><?php echo htmlspecialchars($zone['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sector</label>
                                    <select name="sector_id" id="sector_select" class="form-control" required>
                                        <option value="">Seleccione Sector</option>
                                        <?php foreach ($sectors as $sector): ?>
                                            <option value="<?php echo $sector['id']; ?>" data-zone="<?php echo $sector['zone_id']; ?>">
                                                <?php echo htmlspecialchars($sector['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Número de Local</label>
                                    <input type="text" name="stall_number" class="form-control" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Descripción / Ubicación</label>
                                    <textarea name="location_description" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="ri-close-line me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i> Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterSectors() {
    const zoneId = document.getElementById('zone_select').value;
    const sectorSelect = document.getElementById('sector_select');
    const options = sectorSelect.querySelectorAll('option[data-zone]');
    
    sectorSelect.value = "";
    
    options.forEach(opt => {
        if (!zoneId || opt.getAttribute('data-zone') == zoneId) {
            opt.style.display = 'block';
        } else {
            opt.style.display = 'none';
        }
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
