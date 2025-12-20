<?php
require_once __DIR__ . '/../models/Request.php';
require_once __DIR__ . '/AuthController.php';

class UserController {
    private $requestModel;
    private $auth;

    public function __construct() {
        $this->requestModel = new Request();
        $this->auth = new AuthController();
    }

    // User dashboard
    public function dashboard() {
        $this->auth->checkAuth();
        $this->auth->checkRole(['user']);
        
        $user_id = $_SESSION['user_id'];
        $requests = $this->requestModel->getByUser($user_id);
        
        require_once __DIR__ . '/../views/user/dashboard.php';
    }

    // Create new request
    public function createRequest() {
        $this->auth->checkAuth();
        $this->auth->checkRole(['user']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            
            if ($this->requestModel->create($user_id, $title, $description)) {
                $success = "Request submitted successfully!";
            } else {
                $error = "Failed to submit request!";
            }
        }
        
        require_once __DIR__ . '/../views/user/create_request.php';
    }

    // View user profile
    public function profile() {
        $this->auth->checkAuth();
        $this->auth->checkRole(['user']);
        
        require_once __DIR__ . '/../views/user/profile.php';
    }
}
?>