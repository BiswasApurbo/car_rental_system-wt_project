<?php
require_once "db.php"; 

class DamageModel {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function addDamageReport($userId, $vehiclePhoto, $markedPhoto, $damageMarks, $signatureFile) {
        $stmt = $this->conn->prepare("INSERT INTO vehicle_damage_reports (user_id, vehicle_photo, marked_photo, damage_marks, signature_file) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $userId, $vehiclePhoto, $markedPhoto, $damageMarks, $signatureFile);
        return $stmt->execute();
    }
}
?>
