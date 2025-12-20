<?php
require_once __DIR__ . '/../../core/Database.php';

class Task {
    private $conn;
    private $table = 'tasks';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new task
    public function create($title, $description, $assigned_to, $assigned_by) {
        $sql = "INSERT INTO $this->table (title, description, assigned_to, assigned_by) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$title, $description, $assigned_to, $assigned_by]);
    }

    // Get all tasks
    public function getAll() {
        $sql = "SELECT t.*, u1.username as assigned_to_name, u2.username as assigned_by_name 
                FROM $this->table t 
                JOIN users u1 ON t.assigned_to = u1.id 
                JOIN users u2 ON t.assigned_by = u2.id 
                ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get tasks assigned to user
    public function getAssignedTo($user_id) {
        $sql = "SELECT t.*, u.username as assigned_by_name 
                FROM $this->table t 
                JOIN users u ON t.assigned_by = u.id 
                WHERE t.assigned_to = ? 
                ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update task status
    public function updateStatus($id, $status) {
        $sql = "UPDATE $this->table SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    // Delete task
    public function delete($id) {
        $sql = "DELETE FROM $this->table WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>