<?php
require_once('db.php');

function getBookings($from, $to, $username) {
    $con = getConnection();
    $sql = "SELECT b.id, u.username, u.email, v.make, v.model, b.pickup_date, b.return_date, b.status, b.total_price
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN vehicles v ON b.vehicle_id = v.id
            WHERE b.pickup_date BETWEEN ? AND ? AND u.username = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sss", $from, $to, $username);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getCustomerProfiles($from, $to, $username) {
    $con = getConnection();
    $sql = "SELECT cp.id, u.username, cp.full_name, cp.license_no, cp.seat_pref, cp.mirror_pref, cp.updated_at
            FROM customer_profiles cp
            JOIN users u ON cp.user_id = u.id
            WHERE cp.updated_at BETWEEN ? AND ? AND u.username = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sss", $from, $to, $username);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getInsuranceRecords($from, $to, $username) {
    $con = getConnection();
    $sql = "SELECT ir.id, u.username, ir.tier, ir.deductible, ir.claim, ir.created_at
            FROM insurance_records ir
            JOIN users u ON ir.user_id = u.id
            WHERE ir.created_at BETWEEN ? AND ? AND u.username = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sss", $from, $to, $username);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getVehicleDamageReports($from, $to, $username) {
    $con = getConnection();
    $sql = "SELECT vdr.id, u.username, vdr.damage_marks, vdr.vehicle_photo, vdr.marked_photo, vdr.signature_file, vdr.created_at
            FROM vehicle_damage_reports vdr
            JOIN users u ON vdr.user_id = u.id
            WHERE vdr.created_at BETWEEN ? AND ? AND u.username = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sss", $from, $to, $username);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
