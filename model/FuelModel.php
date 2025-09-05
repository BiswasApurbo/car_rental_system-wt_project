<?php
require_once "db.php";

class FuelModel {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function addFuelRecord($user_id, $fuel_level, $refuel_liters, $receipt_path) {
        $stmt = $this->conn->prepare("INSERT INTO fuel_records (user_id, fuel_level, refuel_liters, receipt_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iddi", $user_id, $fuel_level, $refuel_liters, $receipt_path);
        return $stmt->execute();
    }
}
?>
