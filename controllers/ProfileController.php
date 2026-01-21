<?php
// Controllers/ProfileController.php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/AuthController.php';

class ProfileController {
    private $userModel;
    private $authController;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->authController = new AuthController();
    }

    /**
     * Get user profile data
     * @return array|false
     */
    public function getUserProfileData() {
        // Enforce authentication
        $this->authController->requireAuth();

        $currentUser = $this->authController->getCurrentUser();
        $userId = $currentUser['id'];

        // Get detailed user info including specific staff details
        $userData = $this->userModel->getUserWithStaffDetails($userId);

        if (!$userData) {
            // Fallback if detail fetch fails
            $userData = $currentUser;
        }

        return $userData;
    }
}
