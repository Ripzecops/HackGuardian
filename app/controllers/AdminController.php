<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Request.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/AuthController.php';

class AdminController {
    private $userModel;
    private $requestModel;
    private $taskModel;
    private $auth;

    public function __construct() {
        $this->userModel = new User();
        $this->requestModel = new Request();
        $this->taskModel = new Task();
        $this->auth = new AuthController();
    }

    // Admin dashboard
    public function dashboard() {
        $this->auth->checkAuth();
        $this->auth->checkRole(['admin']);
        
        $users = $this->userModel->getAll();
        $requests = $this->requestModel->getAll();
        $tasks = $this->taskModel->getAll();
        
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    // Manage users
    public function manageUsers() {
        $this->auth->checkAuth();
        $this->auth->checkRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'];
            $id = $_POST['id'];
            
            if ($action === 'delete') {
                $this->userModel->delete($id);
                $success = "User deleted!";
            }
        }
        
        $users = $this->userModel->getAll();
        require_once __DIR__ . '/../views/admin/manage_users.php';
    }
}
?>