<?php
require_once __DIR__ . '/../../controllers/EuroRateController.php';

$controller = new EuroRateController();
$data = $controller->index();
$rates = $data['rates'];
$page_title = $data['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

$months = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
?>

<style>
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffab00 0%, #ffcf50 100%);
        color: white;
    }
    .main-container {
        padding: 1.5rem;
        background-color: #f5f5f9;
    }
    #ratesTable thead th {
        background-color: #000000 !important;
        color: white !important;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
        padding: 1.25rem 1rem;
    }
    #ratesTable thead th:first-child {
        border-top-left-radius: 8px;
    }
    #ratesTable thead th:last-child {
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
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #fff4de !important;">
                                    <i class="ri-money-euro-box-line" style="color: #ffab00; font-size: 1.5rem;"></i>
                                </div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <a href="create.php" class="btn btn-warning text-white px-4 shadow-sm">
                                <i class="ri-add-line me-1"></i> Registrar Tasa
                            </a>
                        </div>

                        <!-- Métrica Rápida -->
                        <div class="card border-0 bg-gradient-warning overflow-hidden mb-4" style="border-radius: 0.5rem; box-shadow: 0 4px 15px rgba(255, 171, 0, 0.2);">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                        <i class="ri-exchange-funds-line ri-2x text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 text-white fw-bold">
                                            <?php echo !empty($rates) ? number_format($rates[0]['bs_value'], 2) . ' Bs.' : 'No establecida'; ?>
                                        </h3>
                                        <p class="mb-0 text-white-50 fw-semibold">Tasa Vigente (Euro)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100" id="ratesTable">
                                <thead>
                                    <tr>
                                        <th>Período</th>
                                        <th>Valor del Euro (Bs.)</th>
                                        <th>Fecha Registro</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rates as $rate): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-dark">
                                                    <?php echo ($months[$rate['month']] ?? $rate['month']) . ' ' . $rate['year']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-warning p-2" style="font-size: 1rem;">
                                                    <?php echo number_format($rate['bs_value'], 2); ?> Bs.
                                                </span>
                                            </td>
                                            <td><?php echo isset($rate['created_at']) ? date('d/m/Y H:i', strtotime($rate['created_at'])) : 'N/A'; ?></td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="edit.php?id=<?php echo $rate['id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="confirmDelete(<?php echo $rate['id']; ?>)">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
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
    function confirmDelete(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer.",
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
            $('#ratesTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                }
            });
        }
    });
</script>
