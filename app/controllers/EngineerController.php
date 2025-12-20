<?php
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/AuthController.php';

class EngineerController {
    private $reportModel;
    private $auth;

    public function __construct() {
        $this->reportModel = new Report();
        $this->auth = new AuthController();
    }

    // Engineer dashboard
    public function dashboard() {
        $this->auth->checkAuth();
        $this->auth->checkRole(['engineer']);
        
        $reports = $this->reportModel->getAll();
        
        require_once __DIR__ . '/../views/engineer/dashboard.php';
    }

    // Approve report
    public function approveReport() {
        $this->auth->checkAuth();
        $this->auth->checkRole(['engineer']);
        
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->reportModel->updateStatus($id, 'approved')) {
                $success = "Report approved!";
            } else {
                $error = "Failed to approve report!";
            }
        }
        
        $reports = $this->reportModel->getAll();
        require_once __DIR__ . '/../views/engineer/dashboard.php';
    }

    // Reject report
    public function rejectReport() {
        $this->auth->checkAuth();
        $this->auth->checkRole(['engineer']);
        
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->reportModel->updateStatus($id, 'rejected')) {
                $success = "Report rejected!";
            } else {
                $error = "Failed to reject report!";
            }
        }
        
        $reports = $this->reportModel->getAll();
        require_once __DIR__ . '/../views/engineer/dashboard.php';
    }
}
?>