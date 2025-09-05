<?php
require_once "db.php";

class MaintenanceModel {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
        if (!$this->conn) {
            die("Database connection failed: " . mysqli_connect_error());
        }
    }

    public function getServicesByUser($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM maintenance_records WHERE user_id = ? ORDER BY service_date DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addService($user_id, $date, $name, $odometer, $remarks) {
        $stmt = $this->conn->prepare("INSERT INTO maintenance_records (user_id, service_date, service_name, odometer, remarks) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $user_id, $date, $name, $odometer, $remarks);
        $stmt->execute();
    }

    public function deleteService($id, $user_id) {
        $stmt = $this->conn->prepare("DELETE FROM maintenance_records WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
    }
}
?>
