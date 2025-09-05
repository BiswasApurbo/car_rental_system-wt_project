<?php
require_once(__DIR__ . '/db.php');


function bm_getUserIdByUsername($username){
    $con = getConnection();
    $stmt = mysqli_prepare($con, "SELECT id FROM users WHERE username=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    return $row ? (int)$row['id'] : 0;
}

function bm_getVehicleById($vehicleId){
    $con = getConnection();
    $stmt = mysqli_prepare($con, "SELECT id, make, model FROM vehicles WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $vehicleId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($res) ?: null;
}

function bm_isVehicleAvailable($vehicleId, $pickupDate, $returnDate){
    $con = getConnection();
    $stmt = mysqli_prepare(
        $con,
        "SELECT 1
           FROM bookings
          WHERE vehicle_id=?
            AND status IN ('Pending','Confirmed','PickedUp')
            AND NOT (return_date < ? OR pickup_date > ?)
          LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "iss", $vehicleId, $pickupDate, $returnDate);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return (mysqli_num_rows($res) === 0);
}

function bm_createBooking($userId, $vehicleId, $pickupDate, $returnDate, $pickupTime, $totalPrice = 0.0){
    $con = getConnection();
    $stmt = mysqli_prepare(
        $con,
        "INSERT INTO bookings(user_id, vehicle_id, pickup_date, return_date, pickup_time, status, total_price)
         VALUES (?, ?, ?, ?, ?, 'Pending', ?)"
    );
    mysqli_stmt_bind_param($stmt, "iisssd", $userId, $vehicleId, $pickupDate, $returnDate, $pickupTime, $totalPrice);
    $ok = mysqli_stmt_execute($stmt);
    if (!$ok) return false;
    return mysqli_insert_id($con);
}
