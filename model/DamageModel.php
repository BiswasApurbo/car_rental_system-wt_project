<?php
require_once "db.php";

function addDamageReport($userId, $vehiclePhoto, $markedPhoto, $damageMarks, $signatureFile) {
    $conn = getConnection();
    $stmt = $conn->prepare(
        "INSERT INTO vehicle_damage_reports 
         (user_id, vehicle_photo, marked_photo, damage_marks, signature_file) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issss", $userId, $vehiclePhoto, $markedPhoto, $damageMarks, $signatureFile);
    return $stmt->execute();
}
?>
