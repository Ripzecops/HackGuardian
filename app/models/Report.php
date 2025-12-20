<?php
require_once __DIR__ . '/../../core/Database.php';

class Report {
    private $conn;
    private $table = 'reports';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new report
    public function create($task_id, $findings) {
        $sql = "INSERT INTO $this->table (task_id, findings) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$task_id, $findings]);
    }

    // Get all reports
    public function getAll() {
        $sql = "SELECT r.*, t.title as task_title 
                FROM $this->table r 
                JOIN tasks t ON r.task_id = t.id 
                ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update report status
    public function updateStatus($id, $status) {
        $sql = "UPDATE $this->table SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    // Get report by ID
    public function getById($id) {
        $sql = "SELECT * FROM $this->table WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>