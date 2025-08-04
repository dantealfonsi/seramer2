<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../public/libs/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../public/libs/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../public/libs/phpmailer/src/Exception.php';

class UserController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
    }

    /**
     * Mostrar lista de usuarios con filtros y paginación
     * @param array $params
     * @return array
     */
    public function index($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        // Obtener parámetros
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = 10;
        
        // Verificar roles
        $is_manager = AuthMiddleware::isManager();
        $is_rrhh = AuthMiddleware::hasAccessToDepartment('Recursos Humanos');
        
        $result = [];
        
        if ($is_rrhh) {
            // RRHH ve todos los usuarios con filtro opcional
            $department_filter = isset($params['department']) ? $params['department'] : '';
            $result['users'] = $this->userModel->getAll($page, $limit, $department_filter);
            $result['total_users'] = $this->userModel->countUsers($department_filter);
            $result['departments'] = ['Recursos Humanos', 'Liquidacion', 'Fiscalizacion', 'Cobranza'];
            $result['page_title'] = 'Gestión de Usuarios - Vista Completa';
            $result['department_filter'] = $department_filter;
        } else if ($is_manager) {
            // Jefe de departamento ve solo usuarios de su departamento
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $user_id = $_SESSION['user_id'];
            $result['users'] = $this->userModel->getUsersByManagerDepartment($user_id, $page, $limit);
            $result['total_users'] = $this->userModel->countUsersByManagerDepartment($user_id);
            $result['departments'] = [$is_manager['name']];
            $result['department_filter'] = $is_manager['name'];
            $result['page_title'] = 'Gestión de Usuarios - Departamento: ' . $is_manager['name'];
        } else {
            return [
                'success' => false,
                'message' => 'No tiene permisos para acceder a esta sección',
                'redirect' => '../dashboard/dashboard.php'
            ];
        }
        
        $result['total_pages'] = ceil($result['total_users'] / $limit);
        $result['current_page'] = $page;
        $result['is_manager'] = $is_manager;
        $result['is_rrhh'] = $is_rrhh;
        $result['success'] = true;
        
        return $result;
    }

    /**
     * Mostrar formulario de creación y procesar creación de usuario
     * @param array $params
     * @return array
     */
    public function create($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        // Verificar roles
        $is_manager = AuthMiddleware::isManager();
        $is_rrhh = AuthMiddleware::hasAccessToDepartment('Recursos Humanos');
        
        $result = [
            'success' => true,
            'message' => '',
            'messageType' => '',
            'errors' => [],
            'staff_id' => '',
            'username' => '',
            'email' => '',
            'is_manager' => $is_manager,
            'is_rrhh' => $is_rrhh
        ];
        
        // Si es POST, procesar creación
        if (isset($params['_method']) && $params['_method'] === 'POST') {
            $staff_id = $params['staff_id'] ?? '';
            $username = trim($params['username'] ?? '');
            $password = $params['password'] ?? '';
            $confirm_password = $params['confirm_password'] ?? '';
            $email = trim($params['email'] ?? '');
            
            // Validaciones
            $errors = $this->validateUserCreation($staff_id, $username, $password, $confirm_password, $email);
            
            if (!empty($errors)) {
                $result['errors'] = $errors;
                $result['staff_id'] = $staff_id;
                $result['username'] = $username;
                $result['email'] = $email;
                $result['success'] = false;
            } else {
                // Verificar permisos específicos de manager
                if ($is_manager && !$is_rrhh && !empty($staff_id)) {
                    $available_staff = $this->userModel->getStaffWithoutUserByDepartment($is_manager['id']);
                    $staff_ids = array_column($available_staff, 'id');
                    
                    if (!in_array($staff_id, $staff_ids)) {
                        $result['errors'][] = 'No tiene permisos para crear usuarios para este personal';
                        $result['success'] = false;
                    }
                }
                
                if ($result['success']) {
                    $creation_result = $this->userModel->createUserForStaff($staff_id, $username, $password, $email);
                    
                    if ($creation_result['success']) {
                        $result['message'] = $creation_result['message'];
                        $result['messageType'] = 'success';
                        // Limpiar formulario
                        $result['staff_id'] = $result['username'] = $result['email'] = '';
                    } else {
                        $result['message'] = $creation_result['message'];
                        $result['messageType'] = 'danger';
                        $result['success'] = false;
                    }
                }
            }
        }
        
        // Obtener personal disponible según el rol
        if ($is_rrhh) {
            $result['available_staff'] = $this->userModel->getAllStaffWithoutUser();
        } else if ($is_manager) {
            $result['available_staff'] = $this->userModel->getStaffWithoutUserByDepartment($is_manager['id']);
        }
        
        return $result;
    }

    /**
     * Mostrar detalles de un usuario específico
     * @param int $user_id
     * @return array
     */
    public function view($user_id) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        if (!$user_id) {
            return [
                'success' => false,
                'message' => 'ID de usuario no válido',
                'redirect' => 'index.php'
            ];
        }
        
        // Obtener datos del usuario
        $user = $this->userModel->getUserWithStaffDetails($user_id);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado',
                'redirect' => 'index.php?error=user_not_found'
            ];
        }
        
        // Verificar permisos
        $permission_check = $this->checkUserPermissions($user);
        if (!$permission_check['success']) {
            return $permission_check;
        }
        
        return [
            'success' => true,
            'user' => $user,
            'is_manager' => AuthMiddleware::isManager(),
            'is_rrhh' => AuthMiddleware::hasAccessToDepartment('Recursos Humanos')
        ];
    }

    /**
     * Mostrar formulario de edición y procesar edición de usuario
     * @param int $user_id
     * @param array $params
     * @return array
     */
    public function edit($user_id, $params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        if (!$user_id) {
            return [
                'success' => false,
                'message' => 'ID de usuario no válido',
                'redirect' => 'index.php'
            ];
        }
        
        // Obtener datos del usuario
        $user = $this->userModel->getUserWithStaffDetails($user_id);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado',
                'redirect' => 'index.php?error=user_not_found'
            ];
        }
        
        // Verificar permisos
        $permission_check = $this->checkUserPermissions($user);
        if (!$permission_check['success']) {
            return $permission_check;
        }
        
        $result = [
            'success' => true,
            'user' => $user,
            'message' => '',
            'messageType' => '',
            'errors' => [],
            'is_manager' => AuthMiddleware::isManager(),
            'is_rrhh' => AuthMiddleware::hasAccessToDepartment('Recursos Humanos')
        ];
        
        // Si es POST, procesar edición
        if (isset($params['_method']) && $params['_method'] === 'POST') {
            $username = trim($params['username'] ?? '');
            $email = trim($params['email'] ?? '');
            $status = $params['status'] ?? '';
            $change_password = isset($params['change_password']);
            $password = $params['password'] ?? '';
            $confirm_password = $params['confirm_password'] ?? '';
            
            // Validaciones
            $errors = $this->validateUserEdition($username, $email, $status, $change_password, $password, $confirm_password, $user_id);
            
            if (!empty($errors)) {
                $result['errors'] = $errors;
                $result['success'] = false;
            } else {
                // Preparar datos para actualización
                $update_data = [
                    'username' => $username,
                    'email' => $email,
                    'status' => $status
                ];
                
                if ($change_password) {
                    $update_data['password'] = $password;
                }
                
                $update_result = $this->userModel->update($user_id, $update_data);
                
                if ($update_result) {
                    $result['message'] = 'Usuario actualizado exitosamente';
                    $result['messageType'] = 'success';
                    // Actualizar datos del usuario para mostrar los cambios
                    $result['user'] = $this->userModel->getUserWithStaffDetails($user_id);
                } else {
                    $result['message'] = 'Error al actualizar el usuario';
                    $result['messageType'] = 'danger';
                    $result['success'] = false;
                }
            }
        }
        
        return $result;
    }

    /**
     * Desactivar un usuario
     * @param int $user_id
     * @return array
     */
    public function deactivate($user_id) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        if (!$user_id) {
            return [
                'success' => false,
                'message' => 'ID de usuario no válido',
                'redirect' => 'index.php?error=invalid_user'
            ];
        }
        
        // Obtener datos del usuario
        $user = $this->userModel->getUserWithStaffDetails($user_id);
        
        if (!$user) {
            error_log("DEACTIVATE ERROR: getUserWithStaffDetails failed for user_id: $user_id");
            return [
                'success' => false,
                'message' => 'Usuario no encontrado en la base de datos',
                'redirect' => 'index.php?error=user_not_found'
            ];
        }
        
        // Verificar permisos
        $permission_check = $this->checkUserPermissions($user);
        if (!$permission_check['success']) {
            return $permission_check;
        }
        
        // Verificar que el usuario no esté ya inactivo
        if ($user['status'] == 'inactive') {
            return [
                'success' => false,
                'message' => 'El usuario ya está inactivo',
                'redirect' => 'edit.php?id=' . $user_id . '&error=already_inactive'
            ];
        }
        
        // Procesar desactivación
        $result = $this->userModel->deactivateUser($user_id);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Usuario desactivado exitosamente',
                'redirect' => 'index.php?success=user_deactivated'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al desactivar el usuario',
                'redirect' => 'index.php?error=deactivation_failed'
            ];
        }
    }

    /**
     * Reactivar un usuario
     * @param int $user_id
     * @return array
     */
    public function reactivate($user_id) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        if (!$user_id) {
            return [
                'success' => false,
                'message' => 'ID de usuario no válido',
                'redirect' => 'index.php?error=invalid_user'
            ];
        }
        
        // Obtener datos del usuario
        $user = $this->userModel->getUserWithStaffDetails($user_id);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado',
                'redirect' => 'index.php?error=user_not_found'
            ];
        }
        
        // Verificar permisos
        $permission_check = $this->checkUserPermissions($user);
        if (!$permission_check['success']) {
            return $permission_check;
        }
        
        // Verificar que el usuario esté inactivo
        if ($user['status'] == 'active') {
            return [
                'success' => false,
                'message' => 'El usuario ya está activo',
                'redirect' => 'edit.php?id=' . $user_id . '&error=already_active'
            ];
        }
        
        // Procesar reactivación
        $result = $this->userModel->reactivateUser($user_id);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Usuario reactivado exitosamente',
                'redirect' => 'index.php?success=user_reactivated'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al reactivar el usuario',
                'redirect' => 'index.php?error=reactivation_failed'
            ];
        }
    }

    /**
     * Validar datos para creación de usuario
     * @param string $staff_id
     * @param string $username
     * @param string $password
     * @param string $confirm_password
     * @param string $email
     * @return array
     */
    private function validateUserCreation($staff_id, $username, $password, $confirm_password, $email) {
        $errors = [];
        
        if (empty($staff_id)) {
            $errors[] = 'Debe seleccionar un miembro del personal';
        }
        
        if (empty($username)) {
            $errors[] = 'El nombre de usuario es requerido';
        } elseif (strlen($username) < 3) {
            $errors[] = 'El nombre de usuario debe tener al menos 3 caracteres';
        }
        
        if (empty($password)) {
            $errors[] = 'La contraseña es requerida';
        } elseif (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Las contraseñas no coinciden';
        }
        
        if (empty($email)) {
            $errors[] = 'El email es requerido';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El formato del email no es válido';
        }
        
        return $errors;
    }

    /**
     * Validar datos para edición de usuario
     * @param string $username
     * @param string $email
     * @param string $status
     * @param bool $change_password
     * @param string $password
     * @param string $confirm_password
     * @param int $user_id
     * @return array
     */
    private function validateUserEdition($username, $email, $status, $change_password, $password, $confirm_password, $user_id) {
        $errors = [];
        
        if (empty($username)) {
            $errors[] = 'El nombre de usuario es requerido';
        } elseif (strlen($username) < 3) {
            $errors[] = 'El nombre de usuario debe tener al menos 3 caracteres';
        }
        
        if (empty($email)) {
            $errors[] = 'El email es requerido';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El formato del email no es válido';
        }
        
        if (empty($status) || !in_array($status, ['active', 'inactive'])) {
            $errors[] = 'Debe seleccionar un estado válido';
        }
        
        if ($change_password) {
            if (empty($password)) {
                $errors[] = 'La nueva contraseña es requerida';
            } elseif (strlen($password) < 6) {
                $errors[] = 'La contraseña debe tener al menos 6 caracteres';
            }
            
            if ($password !== $confirm_password) {
                $errors[] = 'Las contraseñas no coinciden';
            }
        }
        
        // Verificar si username ya existe (excepto para el usuario actual)
        if (!empty($username)) {
            $existing_user = $this->userModel->getByUsername($username);
            if ($existing_user && $existing_user['id'] != $user_id) {
                $errors[] = 'El nombre de usuario ya está en uso';
            }
        }
        
        // Verificar si email ya existe (excepto para el usuario actual)
        if (!empty($email)) {
            $existing_user = $this->userModel->getByEmail($email);
            if ($existing_user && $existing_user['id'] != $user_id) {
                $errors[] = 'El email ya está en uso';
            }
        }
        
        return $errors;
    }

    /**
     * Verificar permisos de acceso para un usuario específico
     * @param array $user
     * @return array
     */
    private function checkUserPermissions($user) {
        $is_manager = AuthMiddleware::isManager();
        $is_rrhh = AuthMiddleware::hasAccessToDepartment('Recursos Humanos');
        
        // RRHH puede acceder a cualquier usuario
        if ($is_rrhh) {
            return ['success' => true];
        }
        
        // Jefes solo pueden acceder a usuarios de su departamento
        if ($is_manager) {
            // Si el usuario no tiene department_id (sin staff), solo RRHH puede manejarlo
            if (!isset($user['department_id']) || $user['department_id'] === null) {
                return [
                    'success' => false,
                    'message' => 'Solo RRHH puede gestionar usuarios sin departamento asignado',
                    'redirect' => 'index.php?error=no_permission'
                ];
            }
            
            if ($user['department_id'] != $is_manager['id']) {
                return [
                    'success' => false,
                    'message' => 'No tiene permisos para acceder a este usuario',
                    'redirect' => 'index.php?error=no_permission'
                ];
            }
            return ['success' => true];
        }
        
        return [
            'success' => false,
            'message' => 'No tiene permisos suficientes',
            'redirect' => '../dashboard/dashboard.php'
        ];
    }

    /**
     * Enviar un correo electrónico utilizando PHPMailer
     * @param string $to
     * @param string $subject
     * @param string $body
     * @return array
     */

    public function sendEmail($to, $subject, $body) {
    // Configuración de PHPMailer
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $obj = array('status' => 0, 'message' => 'Error al enviar el correo');
        try {
            $mail->isSMTP();
            $mail->Host = ''; //example: 'server121.web-hosting.com'
            $mail->SMTPAuth = true;
            $mail->Username = ''; //example: 'pepe@gmail.online';
            $mail->Password = ''; //example: 'password123';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Caracteres y codificacion
            $mail->CharSet = 'UTF-8';    

            $mail->setFrom('pepe@gmail.online', 'SERAMER');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            if($mail->send()){
            $obj['status'] = 1;
            $obj['message'] = 'Codigo enviado';
            } else {
            $obj['message'] = $mail->ErrorInfo;
            }
            return $obj;

        } catch (PHPMailer\PHPMailer\Exception $e) {
            $obj['status'] = 0;
            $obj['message'] = $mail->ErrorInfo;
            return $obj;  
        }
    }    

        /**
     * Muestra el formulario para solicitar el restablecimiento o procesa la solicitud.
     * @param array $params
     */
    public function forgotPassword($params = []) {
        $database = new Database();
        $data = [
            'success' => false,
            'message' => '',
            'email' => $params['email'] ?? ''
        ];        
        $resetCodigo = rand(100000, 999999); // Generar un codigo de 6 digitos
        $email = $data['email'];
        $obj = array('status' => 0,'code' =>null, 'message' => 'Error al enviar el correo',
                     'email' => $email, 'code' => null);        


        $subject = "Restablecimiento de Contraseña para " . PROJECT_NAME;
        $body = "Hola " . htmlspecialchars($data['email']) . ",<br><br>"
                        . "Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.<br>"
                        . "Copia el siguiente codigo para crear una nueva contraseña:<br><br>"
                        . "Codigo: <h2>" . $resetCodigo . "</h2><br><br>"
                        . "Si no solicitaste este cambio, ignora este correo.";

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $obj['message'] = "Formato de correo electronico invalido";
            return $obj;
        } else {
            // Verificar si el usuario existe usando el modelo
            $existing_user = $this->userModel->getByEmail($email);
            if ($existing_user) {
                $result = $this->sendEmail($email, $subject, $body);
                if ($result['status'] == 1) {
                    $obj['status'] = 1;
                    $obj['code'] = $resetCodigo;
                    $obj['message'] = 'Codigo enviado';
                } else {
                    $obj['status'] = 1;
                    $obj['message'] = 'Mail - Error al enviar el correo';
                    $obj['code'] = $resetCodigo; // Mantener el código para el formulario
                }
                // Guardar el codigo en la base de datos
                $query = "UPDATE users SET password_reset_token = :reset_token, password_reset_expires = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE email = :email";
                $params = [
                    ':reset_token' => $resetCodigo,
                    ':email' => $email
                ];
                $database->executeQuery($query, $params);
            } else {
                $obj['message'] = 'El correo no está registrado en ' . PROJECT_NAME;
            }
        }
        return $obj;
    }

    /**
     * Muestra el formulario para restablecer la contraseña o procesa el restablecimiento.
     * @param string $token
     * @param array $params
     */
    public function resetPassword($token, $params = []) {
        $data = [
            'success' => false,
            'message' => '',
            'messageType' => '',
            'token' => $token
        ];

        // Validar token y obtener user_id
        $user_id = $this->userModel->getUserIdByResetToken($token);

        if (!$user_id) {
            $data['message'] = "El enlace de restablecimiento es inválido o ha expirado.";
            $data['messageType'] = 'danger';
            return $data;
        }
        
        $user = $this->userModel->getUserWithStaffDetails($user_id);
        if ($user) {
            $data['success'] = true;
            $data['username'] = $user['username'];
        } else {
            $data['message'] = "Usuario no encontrado, el token es inválido o el usuario fue eliminado.";
            $data['messageType'] = 'danger';
            return $data;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $params['password'] ?? '';
            $confirm_password = $params['confirm_password'] ?? '';

            if (empty($password) || strlen($password) < 6) {
                $data['message'] = "La contraseña debe tener al menos 6 caracteres.";
                $data['messageType'] = 'danger';
                $data['success'] = false;
            } elseif ($password !== $confirm_password) {
                $data['message'] = "Las contraseñas no coinciden.";
                $data['messageType'] = 'danger';
                $data['success'] = false;
            } else {
                if ($this->userModel->updatePassword($user_id, $password)) {
                    $this->userModel->deleteResetToken($token);
                    $data['message'] = "Tu contraseña ha sido restablecida exitosamente. Ahora puedes iniciar sesión.";
                    $data['messageType'] = 'success';
                    $data['success'] = true;
                    $data['redirect_to_login'] = true; // Señal para la vista
                } else {
                    $data['message'] = "Error al actualizar la contraseña. Por favor, inténtalo de nuevo.";
                    $data['messageType'] = 'danger';
                    $data['success'] = false;
                }
            }
        }

        return $data;
    }

    /**
     * Genera el HTML del correo electrónico para el restablecimiento de contraseña.
     * @param string $reset_link El enlace para restablecer la contraseña.
     * @return string El HTML del correo electrónico.
     */
    // En el UserController, añade este nuevo método privado
    private function generateEmailHtml($reset_link) {
        // Definimos el color morado principal
        $primaryColor = '#696cff'; // Este es el morado de la plantilla de AdminLTE
        $textColor = '#45465e'; // Gris oscuro para el texto
        $buttonColor = '#696cff';
        $buttonHoverColor = '#5f61d2';
        $footerTextColor = '#98a6ad';

        $html = <<<EOT
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Restablecer Contraseña - Mercado Municipal de Carúpano</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
                    line-height: 1.6;
                    color: {$textColor};
                    background-color: #f4f5fa;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 40px auto;
                    padding: 20px;
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    border-top: 5px solid {$primaryColor};
                }
                .header {
                    text-align: center;
                    padding-bottom: 20px;
                    border-bottom: 1px solid #e0e0e0;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                    color: {$primaryColor};
                }
                .content {
                    padding: 20px 0;
                }
                .content p {
                    font-size: 16px;
                    margin-bottom: 15px;
                }
                .button-container {
                    text-align: center;
                    margin: 30px 0;
                }
                .button {
                    display: inline-block;
                    padding: 12px 24px;
                    font-size: 16px;
                    color: #ffffff;
                    background-color: {$buttonColor};
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    border: none;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                }
                .button:hover {
                    background-color: {$buttonHoverColor};
                }
                .footer {
                    text-align: center;
                    padding-top: 20px;
                    border-top: 1px solid #e0e0e0;
                    font-size: 12px;
                    color: {$footerTextColor};
                }
                .footer a {
                    color: {$footerTextColor};
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Restablecimiento de Contraseña</h1>
                </div>
                <div class="content">
                    <p>Hola,</p>
                    <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta. Si no hiciste esta solicitud, puedes ignorar este correo.</p>
                    <p>Para crear una nueva contraseña, haz clic en el botón de abajo. Este enlace expirará en 1 hora.</p>
                    <div class="button-container">
                        <a href="{$reset_link}" class="button">Restablecer Contraseña</a>
                    </div>
                    <p>Si tienes problemas con el botón, copia y pega la siguiente URL en tu navegador:</p>
                    <p><a href="{$reset_link}">{$reset_link}</a></p>
                    <p>Gracias,</p>
                    <p>El equipo del Mercado Municipal de Carúpano.</p>
                </div>
                <div class="footer">
                    <p>
                        &copy; 2025 Mercado Municipal de Carúpano. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </body>
        </html>
        EOT;

        return $html;
    }    

}