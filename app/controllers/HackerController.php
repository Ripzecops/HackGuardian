<?php
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/AuthController.php';

class HackerController {
    private $taskModel;
    private $reportModel;
    private $auth;

    public function __construct() {
        $this->taskModel = new Task();
        $this->reportModel = new Report();
        $this->auth = new AuthController();
    }

    // Hacker dashboard
    public function dashboard() {
        $this->auth->checkAuth();
        $this->auth->checkRole(['hacker']);
        
        $user_id = $_SESSION['user_id'];
        $tasks = $this->taskModel->getAssignedTo($user_id);
        
        require_once __DIR__ . '/../views/hacker/dashboard.php';
    }

    // Submit findings
    public function submitFindings() {
        $this->auth->checkAuth();
        $this->auth->checkRole(['hacker']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $task_id = $_POST['task_id'];
            $findings = $_POST['findings'];
            
            if ($this->reportModel->create($task_id, $findings)) {
                // Update task status
                $this->taskModel->updateStatus($task_id, 'completed');
                $success = "Findings submitted successfully!";
            } else {
                $error = "Failed to submit findings!";
            }
        }
        
        require_once __DIR__ . '/../views/hacker/submit_findings.php';
    }
}
?>