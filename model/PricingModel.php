<?php
require_once "db.php"; 

function getSettings() {
    $conn = getConnection();
    $sql = "SELECT base_fee_per_day FROM pricing_settings LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row;
    }
    return ['base_fee_per_day' => 500]; 
}

function getAllPromoCodes() {
    $conn = getConnection();
    $codes = [];
    $sql = "SELECT promo_code, discount_percent FROM pricing_settings WHERE promo_code IS NOT NULL";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $codes[$row['promo_code']] = floatval($row['discount_percent']);
        }
    }
    return $codes;
}

function addRecord($userId, $days, $promo, $discountPercent, $discountAmount, $baseFee, $tax, $total) {
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO pricing_records (user_id, rental_days, promo_code, discount_percent, discount_amount, base_fee, tax, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isiddddd", $userId, $days, $promo, $discountPercent, $discountAmount, $baseFee, $tax, $total);
    return $stmt->execute();
}
?>
