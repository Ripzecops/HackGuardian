<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../core/Session.php';

class AuthController {
    private $userModel;
    private $session;

    public function __construct() {
        $this->userModel = new User();
        $this->session = new Session();
    }

    // Show login page
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            $user = $this->userModel->login($email, $password);
            
            if ($user) {
                $this->session->set('user_id', $user['id']);
                $this->session->set('user_role', $user['role']);
                $this->session->set('username', $user['username']);
                
                // Redirect to dashboard based on role
                $this->redirectToDashboard($user['role']);
            } else {
                $error = "Invalid email or password!";
                require_once __DIR__ . '/../views/auth/login.php';
            }
        } else {
            require_once __DIR__ . '/../views/auth/login.php';
        }
    }

    // Show register page
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $role = $_POST['role'];
            
            if ($this->userModel->register($username, $email, $password, $role)) {
                header("Location: /?action=login");
                exit();
            } else {
                $error = "Registration failed!";
            }
        }
        require_once __DIR__ . '/../views/auth/register.php';
    }

    // Logout
    public function logout() {
        $this->session->destroy();
        header("Location: /?action=login");
        exit();
    }

    // Redirect based on role
    private function redirectToDashboard($role) {
        $dashboards = [
            'user' => 'user/dashboard',
            'hacker' => 'hacker/dashboard',
            'pentester' => 'pentester/dashboard',
            'engineer' => 'engineer/dashboard',
            'admin' => 'admin/dashboard'
        ];
        
        $url = "/?action=" . $dashboards[$role];
        header("Location: $url");
        exit();
    }

    // Check if user is logged in
    public function checkAuth() {
        if (!$this->session->isLoggedIn()) {
            header("Location: /?action=login");
            exit();
        }
    }

    // Check if user has specific role
    public function checkRole($allowedRoles) {
        $userRole = $this->session->getUserRole();
        if (!in_array($userRole, $allowedRoles)) {
            header("Location: /?action=login");
            exit();
        }
    }
}
?>