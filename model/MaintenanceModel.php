<?php
require_once "db.php";

$conn = getConnection();


function addService($user_id, $service_date, $service_name, $odometer, $remarks) {
    global $conn;
    $stmt = $conn->prepare(
        "INSERT INTO maintenance_records (user_id, service_date, service_name, odometer, remarks) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issss", $user_id, $service_date, $service_name, $odometer, $remarks);
    return $stmt->execute();
}


function deleteService($id, $user_id) {
    global $conn;
    $stmt = $conn->prepare(
        "DELETE FROM maintenance_records WHERE id=? AND user_id=?"
    );
    $stmt->bind_param("ii", $id, $user_id);
    return $stmt->execute();
}


function getServicesByUser($user_id) {
    global $conn;
    $stmt = $conn->prepare(
        "SELECT * FROM maintenance_records WHERE user_id=? ORDER BY service_date DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_all(MYSQLI_ASSOC);
}


function getRecommendedServices() {
    global $conn;
    $res = $conn->query(
        "SELECT * FROM maintenance_recommendations ORDER BY service_name ASC"
    );
    return $res->fetch_all(MYSQLI_ASSOC);
}
