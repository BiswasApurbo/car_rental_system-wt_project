<?php
require_once(__DIR__ . '/db.php'); ;

function getCustomerProfile($userId) {
    $con = getConnection();
    $sql = "SELECT * FROM customer_profiles WHERE user_id=$userId LIMIT 1";
    $result = mysqli_query($con, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function insertCustomerProfile($userId, $fullName, $licenseNo, $seat, $mirror, $licenseFile) {
    $con = getConnection();
    $fullName   = mysqli_real_escape_string($con, $fullName);
    $licenseNo  = mysqli_real_escape_string($con, $licenseNo);
    $seat       = mysqli_real_escape_string($con, $seat);
    $mirror     = mysqli_real_escape_string($con, $mirror);
    $licenseFile= mysqli_real_escape_string($con, $licenseFile);

    $sql = "INSERT INTO customer_profiles (user_id, full_name, license_no, seat_pref, mirror_pref, license_file)
            VALUES ($userId, '$fullName', '$licenseNo', '$seat', '$mirror', '$licenseFile')";
    return mysqli_query($con, $sql);
}

function getUserByName($username){
    $con = getConnection();
    $stmt = mysqli_prepare($con, "SELECT id, username, email, role, profile FROM users WHERE username=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    return $row ?: null;
}


function updateCustomerProfile($userId, $fullName, $licenseNo, $seat, $mirror, $licenseFile=null) {
    $con = getConnection();
    $fullName   = mysqli_real_escape_string($con, $fullName);
    $licenseNo  = mysqli_real_escape_string($con, $licenseNo);
    $seat       = mysqli_real_escape_string($con, $seat);
    $mirror     = mysqli_real_escape_string($con, $mirror);

    if ($licenseFile) {
        $licenseFile= mysqli_real_escape_string($con, $licenseFile);
        $sql = "UPDATE customer_profiles
                   SET full_name='$fullName', license_no='$licenseNo',
                       seat_pref='$seat', mirror_pref='$mirror',
                       license_file='$licenseFile'
                 WHERE user_id=$userId";
    } else {
        $sql = "UPDATE customer_profiles
                   SET full_name='$fullName', license_no='$licenseNo',
                       seat_pref='$seat', mirror_pref='$mirror'
                 WHERE user_id=$userId";
    }
    return mysqli_query($con, $sql);
}

function getRentalHistory($userId) {
    $con = getConnection();
    $sql = "SELECT b.id AS booking_id, v.make, v.model, b.pickup_date, b.return_date, b.status
              FROM bookings b
              JOIN vehicles v ON v.id = b.vehicle_id
             WHERE b.user_id=$userId
             ORDER BY b.created_at DESC
             LIMIT 10";
    $result = mysqli_query($con, $sql);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    }
    return $rows;
}
