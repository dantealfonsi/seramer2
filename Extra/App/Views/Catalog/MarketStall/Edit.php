<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Editar Local</h5>
            </div>
            <div class="card-body">
                <form action="<?= $app['url'] ?>/marketstall/update/<?= $stall['id'] ?>" method="POST">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="zone_id" name="zone_id" required>
                                    <option value="">Seleccionar zona...</option>
                                    <?php foreach ($zones as $zone): ?>
                                        <option value="<?= $zone['id'] ?>" <?= $zone['id'] == $stall['zone_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($zone['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="zone_id">Zona *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="sector_id" name="sector_id" required>
                                    <option value="">Seleccionar sector...</option>
                                    <?php foreach ($sectors as $sector): ?>
                                        <option value="<?= $sector['id'] ?>" 
                                                data-zone="<?= $sector['zone_id'] ?>"
                                                <?= $sector['id'] == $stall['sector_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sector['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="sector_id">Sector *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="stall_number" name="stall_number" value="<?= htmlspecialchars($stall['stall_number']) ?>" placeholder="Ej: L-001" required />
                                <label for="stall_number">N° de Local *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="location_description" name="location_description" value="<?= htmlspecialchars($stall['location_description'] ?? '') ?>" placeholder="Ubicación" />
                                <label for="location_description">Ubicación</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri ri-save-line me-1"></i>
                                Actualizar
                            </button>
                            <a href="<?= $app['url'] ?>/marketstall/index" class="btn btn-outline-secondary">
                                <i class="ri ri-arrow-left-line me-1"></i>
                                Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos de sectores
    const allSectors = <?= json_encode($sectors ?? []) ?>;
    const currentSector = <?= $stall['sector_id'] ?>;
    
    console.log('Sectores cargados:', allSectors);
    console.log('Sector actual:', currentSector);
    
    const zoneSelect = document.getElementById('zone_id');
    const sectorSelect = document.getElementById('sector_id');
    
    // Filtrar sectores al cargar la página
    filterSectors(zoneSelect.value);
    
    // Cargar sectores cuando se selecciona una zona
    zoneSelect.addEventListener('change', function() {
        filterSectors(this.value);
    });
    
    function filterSectors(zoneId) {
        console.log('Zona seleccionada:', zoneId);
        
        if (zoneId) {
            const filtered = allSectors.filter(s => String(s.zone_id) === String(zoneId));
            
            console.log('Sectores filtrados:', filtered);
            
            if (filtered.length > 0) {
                let options = '<option value="">Seleccionar sector...</option>';
                filtered.forEach(sector => {
                    const selected = sector.id == currentSector ? 'selected' : '';
                    options += `<option value="${sector.id}" ${selected}>${sector.name}</option>`;
                });
                sectorSelect.innerHTML = options;
            } else {
                sectorSelect.innerHTML = '<option value="">No hay sectores en esta zona</option>';
            }
        } else {
            sectorSelect.innerHTML = '<option value="">Seleccionar zona primero...</option>';
        }
    }
});
</script>

