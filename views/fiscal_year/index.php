<?php
require_once __DIR__ . '/../../controllers/FiscalYearController.php';

$controller = new FiscalYearController();
$data = $controller->index();
$fiscalYears = $data['fiscalYears'];
$page_title = $data['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #696cff 0%, #7172ff 100%);
        color: white;
    }
    .main-container {
        padding: 1.5rem;
        background-color: #f5f5f9;
    }
    #fiscalYearTable thead th {
        background-color: #000000 !important;
        color: white !important;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
        padding: 1.25rem 1rem;
    }
    #fiscalYearTable thead th:first-child {
        border-top-left-radius: 8px;
    }
    #fiscalYearTable thead th:last-child {
        border-top-right-radius: 8px;
    }
</style>

<div class="main-content main-container">
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <h5 class="mb-0 d-flex align-items-center" style="font-size: 1.75rem; font-weight: 600; color: #43495b;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                                    <i class="ri-calendar-line" style="color: #696cff; font-size: 1.5rem;"></i>
                                </div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <a href="create.php" class="btn btn-primary px-4 shadow-sm">
                                <i class="ri-add-line me-1"></i> Nuevo Año Fiscal
                            </a>
                        </div>

                        <!-- Info Card -->
                        <div class="card border-0 bg-gradient-primary overflow-hidden mb-4" style="border-radius: 0.5rem;">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                        <i class="ri-calendar-check-line ri-2x text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 text-white fw-bold">Gestión Fiscal</h3>
                                        <p class="mb-0 text-white-50 fw-semibold">Control de períodos fiscales y facturación anual.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100" id="fiscalYearTable">
                                <thead>
                                    <tr>
                                        <th>Año</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Fin</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($fiscalYears as $fy): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-dark" style="font-size: 1.1rem;"><?php echo htmlspecialchars($fy['year']); ?></span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($fy['start_date'])); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($fy['end_date'])); ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-label-<?php echo $fy['status'] === 'active' ? 'success' : 'secondary'; ?> px-3 py-2">
                                                    <?php echo $fy['status'] === 'active' ? 'Activo' : 'Inactivo'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="edit.php?id=<?php echo $fy['id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                    <?php if ($fy['status'] !== 'active'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="confirmDelete(<?php echo $fy['id']; ?>, '<?php echo $fy['year']; ?>')">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" action="delete.php" style="display: none;">
    <input type="hidden" name="id" id="deleteId">
</form>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
    function confirmDelete(id, year) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Vas a eliminar el año fiscal ${year}. Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff3e1d',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        });
    }

    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#fiscalYearTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                order: [[0, 'desc']]
            });
        }
    });
</script>
