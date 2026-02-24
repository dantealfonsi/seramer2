<?php
require_once __DIR__ . '/../../controllers/MarketStallController.php';

$controller = new MarketStallController();
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];
$data_view = $controller->edit($id);

if (!$data_view) {
    header('Location: index.php');
    exit;
}

$stall = $data_view['stall'];
$page_title = $data_view['page_title'];
$zones = $data_view['zones'];
$sectors = $data_view['sectors'];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->update($id, $_POST);
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
                                <li class="breadcrumb-item active">Editar</li>
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
                                            <!-- Assuming we can find the stall's current zone from its sector logic, but let's just make it selectable -->
                                            <option value="<?php echo $zone['id']; ?>">
                                                <?php echo htmlspecialchars($zone['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sector</label>
                                    <select name="sector_id" id="sector_select" class="form-control" required>
                                        <option value="">Seleccione Sector</option>
                                        <?php foreach ($sectors as $sector): ?>
                                            <option value="<?php echo $sector['id']; ?>" 
                                                    data-zone="<?php echo $sector['zone_id']; ?>"
                                                    <?php echo ($stall['sector_id'] == $sector['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($sector['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Número de Local</label>
                                    <input type="text" name="stall_number" class="form-control" value="<?php echo htmlspecialchars($stall['stall_number']); ?>" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Descripción / Ubicación</label>
                                    <textarea name="location_description" class="form-control" rows="2"><?php echo htmlspecialchars($stall['location_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="ri-close-line me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-refresh-line me-1"></i> Actualizar
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
    
    // We don't reset value on edit unless user changes zone explicitly
    // But initially we might want to set the zone select based on the selected sector
}

// Set initial zone based on selected sector
document.addEventListener('DOMContentLoaded', function() {
    const sectorSelect = document.getElementById('sector_select');
    const selectedOption = sectorSelect.options[sectorSelect.selectedIndex];
    if (selectedOption && selectedOption.getAttribute('data-zone')) {
        document.getElementById('zone_select').value = selectedOption.getAttribute('data-zone');
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
