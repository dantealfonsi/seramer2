<?php
// Vista de edición de sanciones

session_start();

// Incluir los controladores necesarios
require_once __DIR__ . '/../../controllers/SanctionsController.php';
// Asumimos que estos controladores ya existen para obtener listas de opciones
require_once __DIR__ . '/../../controllers/InfractionsController.php';
require_once __DIR__ . '/../../controllers/SanctionTypesController.php';

$sanctionsController = new SanctionsController();
$infractionsController = new InfractionsController();
$sanctionTypesController = new SanctionTypesController();

// Obtener el ID de la sanción de la URL
$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'No se especificó una sanción para editar.'
    ];
    header('Location: index.php');
    exit;
}

$allowed_sanction_status = [
    'Imposed' => 'Impuesta',
    'Paid' => 'Pagada',
    'Pending' => 'Pendiente',
    'Canceled' => 'Cancelada'
];

// Manejar la petición POST para guardar el registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y obtener los datos del formulario
    $data = [
        'sanction_id'           => filter_input(INPUT_POST, 'sanction_id', FILTER_SANITIZE_NUMBER_INT),
        'infraction_id'         => filter_input(INPUT_POST, 'infraction_id', FILTER_SANITIZE_NUMBER_INT),
        'sanction_type_id'      => filter_input(INPUT_POST, 'sanction_type_id', FILTER_SANITIZE_NUMBER_INT),
        'fine_amount'           => filter_input(INPUT_POST, 'fine_amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'fine_currency'         => $_POST['fine_currency'] ?? '',
        'effect_start_date'     => $_POST['effect_start_date'] ?? '',
        'effect_end_date'       => $_POST['effect_end_date'] ?? '',
        'sanction_status'       => $_POST['sanction_status'] ?? '',
        'sanction_observations' => $_POST['sanction_observations'] ?? '',
        'is_repeat_offense'     => isset($_POST['is_repeat_offense']) ? 1 : 0,
        'imposed_by_user_id'    => 1 // Asumimos un ID de usuario por defecto
    ];

    // Llamar al controlador para actualizar el registro
    $result = $sanctionsController->update($data['sanction_id'],$data);

    if ($result['success']) {
        $_SESSION['flash_message'] = [
            'type'    => 'success',
            'message' => $result['message']
        ];
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['flash_message'] = [
            'type'    => 'error',
            'message' => $result['message']
        ];
    }
}

// Obtener los datos actuales de la sanción
$result = $sanctionsController->edit($id);

if (!$result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => $result['message']
    ];
    header('Location: index.php');
    exit;
}
$sanction = $result['sanction'];

// Obtener las listas para los selectores
$infractions = $infractionsController->index()['infractions'];
$sanction_types = $sanctionTypesController->index()['sanction_types'];

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Mensajes flash -->
                <?php if (isset($_SESSION['flash_message'])) : ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['flash_message']['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title d-flex align-items-center mb-0" style="font-size: 1.4rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-forbid-2-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                            Editar Sanción
                        </h5>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Volver al listado
                        </a>
                    </div>
                    
                    <div class="card-body">
                        <form action="edit.php?id=<?php echo htmlspecialchars($sanction['sanction_id']); ?>" method="POST">
                            <input type="hidden" name="sanction_id" value="<?php echo htmlspecialchars($sanction['sanction_id']); ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="infraction_id" class="form-label">Infracción</label>
                                    <select class="form-select" id="infraction_id" name="infraction_id" required>
                                        <option value="" disabled>Seleccionar Infracción</option>
                                        <?php foreach ($infractions as $infraction) : ?>
                                            <option value="<?php echo htmlspecialchars($infraction['infraction_id']); ?>" 
                                                    <?php echo ($infraction['infraction_id'] == $sanction['infraction_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($infraction['infraction_description']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sanction_type_id" class="form-label">Tipo de Sanción</label>
                                    <select class="form-select" id="sanction_type_id" name="sanction_type_id" required>
                                        <option value="" disabled>Seleccionar Tipo de Sanción</option>
                                        <?php foreach ($sanction_types as $type) : ?>
                                            <option value="<?php echo htmlspecialchars($type['sanction_type_id']); ?>" 
                                                    <?php echo ($type['sanction_type_id'] == $sanction['sanction_type_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($type['severity_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fine_amount" class="form-label">Monto de Multa</label>
                                    <input type="number" class="form-control" id="fine_amount" name="fine_amount" step="0.01" value="<?php echo htmlspecialchars($sanction['fine_amount'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fine_currency" class="form-label">Moneda</label>
                                    <input onkeyup="validarText('fine_currency',3,'errorTextFineCurrency')" type="text" class="form-control" id="fine_currency" name="fine_currency" value="<?php echo htmlspecialchars($sanction['fine_currency'] ?? ''); ?>">
                                    <div id="errorTextFineCurrency" style="color: red;"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="effect_start_date" class="form-label">Fecha de Inicio de Efecto</label>
                                    <input type="date" class="form-control" id="effect_start_date" name="effect_start_date" value="<?php echo htmlspecialchars($sanction['effect_start_date'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="effect_end_date" class="form-label">Fecha de Fin de Efecto</label>
                                    <input type="date" class="form-control" id="effect_end_date" name="effect_end_date" value="<?php echo htmlspecialchars($sanction['effect_end_date'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sanction_status" class="form-label">Estado de la Sanción</label>
                                    <select class="form-select" id="sanction_status" name="sanction_status" required>
                                        <?php $statuses = ['Imposed', 'Paid', 'Pending', 'Canceled']; ?>
                                        <?php foreach ($statuses as $status) : ?>
                                            <option value="<?php echo htmlspecialchars($status); ?>" 
                                                    <?php echo ($status == $sanction['sanction_status']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($allowed_sanction_status[$status]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3 d-flex align-items-center">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_repeat_offense" name="is_repeat_offense" value="1" 
                                               <?php echo ($sanction['is_repeat_offense'] == 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_repeat_offense">¿Es reincidencia?</label>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="sanction_observations" class="form-label">Observaciones</label>
                                    <textarea onKeyup="validarText('sanction_observations',8,'errorTextSanctionObservations')" class="form-control" id="sanction_observations" name="sanction_observations" rows="4"><?php echo htmlspecialchars($sanction['sanction_observations'] ?? ''); ?></textarea>
                                    <div id="errorTextSanctionObservations" style="color: red;"></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-lg mt-3">
                                    <i class="ri-save-line"></i> Actualizar Sanción
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>