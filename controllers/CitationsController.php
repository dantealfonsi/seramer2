<?php

require_once __DIR__ . '/../models/CitationsModel.php';
require_once __DIR__ . '/../models/InfractionsModel.php';
require_once __DIR__ . '/../config/app.php';

class CitationsController {
    private $citationsModel;
    private $infractionsModel;

    public function __construct() {
        $this->citationsModel = new CitationsModel();
        $this->infractionsModel = new InfractionsModel();
    }

    /**
     * Muestra una lista de citaciones para DataTables (sin paginación en el servidor).
     */
    public function index($params = []) {
        // Ejecutar actualización automática de estados antes de obtener la lista
        $this->citationsModel->checkAndAutoUpdateStatuses();

        // Ignoramos $page y $limit, ya que DataTables maneja la paginación en el cliente.
        $search = isset($params['search']) ? trim($params['search']) : '';
        
        // **IMPORTANTE:** Aquí asumimos que CitationsModel tiene un método 'getAllForDataTables' 
        // o modificamos getAll para que ignore $page y $limit si se envían valores nulos/cero.
        // Asumiendo que modificaremos el llamado al modelo:
        
        // Llama a getAll, pero ahora pasamos 0 o null para deshabilitar la paginación en el modelo.
        // ¡Necesitarás actualizar CitationsModel.php para manejar estos valores!
        $citations = $this->citationsModel->getAll(0, 0, $search); 
        
        // Ya no necesitamos $total ni $totalPages. $total_records es count($citations).
        $total = count($citations); 
        
        return [
            // Incluimos una clave 'success' para estandarizar la respuesta
            'success' => true,
            'citations' => $citations,
            // Las siguientes claves se mantienen, pero con valores simplificados/basados en el total.
            // La vista anterior las necesitaba, aunque DataTables las ignorará.
            'current_page' => 1,
            'total_pages' => 1,
            'total_records' => $total,
            'search' => $search,
            'page_title' => 'Gestión de Citaciones',
            'has_search' => !empty($search)
        ];
    }
    
    /**
     * Muestra una citación específica por su ID.
     */
    public function view($id) {
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de citación inválido'];
        }
        
        $citation = $this->citationsModel->getById($id);
        
        if (!$citation) {
            return ['success' => false, 'message' => 'Citación no encontrada'];
        }
        
        return [
            'success' => true,
            'citation' => $citation,
            'page_title' => 'Detalle de Citación #' . $citation['citation_id']
        ];
    }

    /**
     * Muestra el formulario para crear una nueva citación.
     */
    public function create() {
        // Suponiendo que el modelo tiene métodos para obtener listas para los dropdowns
        $infractions = $this->citationsModel->getInfractionsList();
        $mediators = $this->citationsModel->getMediatorsList();
        
        return [
            'page_title' => 'Programar Nueva Citación',
            'infractions' => $infractions,
            'mediators' => $mediators,
            'action' => 'create'
        ];
    }
    
    /**
     * Procesa la creación de una nueva citación.
     */
    public function store($data) {
        // Forzar estado 'Scheduled' al crear
        $data['citation_status'] = 'Scheduled';

        $validation = $this->validateCitationData($data);
        if (!$validation['success']) {
            return $validation; // Devuelve errores para mostrar en el formulario
        }
        
        $result = $this->citationsModel->create($data);
        
        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        if ($result['success']) {
            return ['success' => true, 'redirect' => 'index.php', 'message' => $result['message']];
        }
        return $result;
    }
    
    /**
     * Muestra el formulario para editar una citación.
     */
    public function edit($id) {
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de citación inválido'];
        }
        
        $citation = $this->citationsModel->getById($id);
        $infractions = $this->citationsModel->getInfractionsList();
        $mediators = $this->citationsModel->getMediatorsList();
        
        if (!$citation) {
            return [
                'success' => false,
                'page_title' => 'Error al Editar Citación #' . $id,
                'action' => 'edit'
            ];
        }

        return [
            'success' => true,
            'citation' => $citation,
            'infractions' => $infractions,
            'mediators' => $mediators,
            'page_title' => 'Editar Citación #' . $id,
            'action' => 'edit'
        ];
    }
    
    /**
     * Procesa la actualización de una citación.
     */
    public function update($id, $data) {
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de citación inválido'];
        }

        $validation = $this->validateCitationData($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        $result = $this->citationsModel->update($id, $data);
        
        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        if ($result['success']) {
            return ['success' => true, 'redirect' => 'index.php'];
        }
        return $result;
    }

    /**
     * Elimina una citación.
     */
    public function delete($id) {
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de citación inválido'];
        }
        
        $result = $this->citationsModel->delete($id);
        
        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        return $result;
    }

    /**
     * Valida los datos de la citación.
     * @param array $data
     * @return array
     */
    private function validateCitationData($data) {
        $errors = [];
        
        if (empty($data['infraction_id']) || !is_numeric($data['infraction_id'])) {
            $errors[] = 'El ID de la infracción es obligatorio y debe ser un número.';
        }
        
        if (empty(trim($data['location']))) {
            $errors[] = 'La ubicación es obligatoria.';
        }

        if (empty($data['mediator_user_id']) || !is_numeric($data['mediator_user_id'])) {
            $errors[] = 'El ID del mediador es obligatorio y debe ser un número.';
        }

        // Validar la fecha y hora
        if (empty($data['citation_datetime'])) {
            $errors[] = 'La fecha y hora de la citación son obligatorias.';
        } else {
            // Se puede usar DateTime::createFromFormat para una validación más estricta
            try {
                new DateTime($data['citation_datetime']);
            } catch (Exception $e) {
                $errors[] = 'El formato de fecha y hora no es válido.';
            }
        }
        
        // Validar que los valores de status sean los permitidos
        $allowed_status = ['Scheduled', 'In Process', 'Rescheduled', 'Completed', 'Canceled'];
        if (isset($data['citation_status']) && !in_array($data['citation_status'], $allowed_status)) {
            $errors[] = 'El estado de la citación no es válido.';
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Errores de validación', 'errors' => $errors];
        }
        
        return ['success' => true];
    }

    // Métodos adicionales para obtener listas de infracciones y mediadores
    public function getInfractionsList() {
        return $this->citationsModel->getInfractionsList();
    }
    public function getMediatorsList() {
        return $this->citationsModel->getMediatorsList();
    }
    
    public function getStallsList() {
        return $this->infractionsModel->getStallsList();
    }

    public function getInfractionsByAwardee($awardeeId) {
        return $this->infractionsModel->getInfractionsByAwardee($awardeeId);
    }

    public function getById($id) {
        return $this->citationsModel->getById($id);
    }
}