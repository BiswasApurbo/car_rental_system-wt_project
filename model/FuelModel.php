<?php
require_once "db.php";


function getPricePerLiter() {
    $conn = getConnection();
    $sql = "SELECT price_per_liter FROM fuel_settings ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['price_per_liter'];
    }
    return 0;
}

function addFuelRecord($user_id, $fuel_limit, $refuel_liters, $price_per_liter, $total_cost, $receipt_file) {
    $conn = getConnection();
    $stmt = $conn->prepare("
        INSERT INTO fuel_records (user_id, fuel_limit, refuel_liters, price_per_liter, total_cost, receipt_file)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("idddds", $user_id, $fuel_limit, $refuel_liters, $price_per_liter, $total_cost, $receipt_file);
    return $stmt->execute();
}
?>
