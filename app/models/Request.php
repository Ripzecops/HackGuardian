<?php
require_once __DIR__ . '/../../core/Database.php';

class Request {
    private $conn;
    private $table = 'requests';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new request
    public function create($user_id, $title, $description) {
        $sql = "INSERT INTO $this->table (user_id, title, description) 
                VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$user_id, $title, $description]);
    }

    // Get all requests
    public function getAll() {
        $sql = "SELECT r.*, u.username FROM $this->table r 
                JOIN users u ON r.user_id = u.id 
                ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get user's requests
    public function getByUser($user_id) {
        $sql = "SELECT * FROM $this->table WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update request
    public function update($id, $title, $description, $status) {
        $sql = "UPDATE $this->table SET title = ?, description = ?, status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$title, $description, $status, $id]);
    }

    // Delete request
    public function delete($id) {
        $sql = "DELETE FROM $this->table WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Get request by ID
    public function getById($id) {
        $sql = "SELECT * FROM $this->table WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>