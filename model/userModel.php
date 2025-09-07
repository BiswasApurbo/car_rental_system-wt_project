<?php
require_once('db.php');

function login($user){
    $con = getConnection();
    $sql = "SELECT * FROM users WHERE username='{$user['username']}' AND password='{$user['password']}'";
    $result = mysqli_query($con, $sql);
    $count = mysqli_num_rows($result);
    return $count == 1;
}

function addUser($user){
    $con = getConnection();
    $role = isset($user['role']) ? $user['role'] : 'User';
    $username = mysqli_real_escape_string($con, $user['username']);
    $password = mysqli_real_escape_string($con, $user['password']);
    $email    = mysqli_real_escape_string($con, $user['email']);
    $roleEsc  = mysqli_real_escape_string($con, $role);

    $sql = "INSERT INTO users (username, password, email, role) VALUES ('{$username}', '{$password}', '{$email}', '{$roleEsc}')";
    return mysqli_query($con, $sql);
}

function getAlluser(){
    $con = getConnection();
    $sql = "SELECT * FROM users";
    $result = mysqli_query($con, $sql);
    $users = [];
    while($row = mysqli_fetch_assoc($result)){
        array_push($users, $row);
    }
    return $users;
}

function getUserById($id){
    $con = getConnection();
    $sql = "SELECT * FROM users WHERE id={$id}";
    $result = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($result);
}

function updateUser($user){
    $con = getConnection();
    if (empty($user['id'])) return false;
    $id = (int)$user['id'];
    $username = isset($user['username']) ? mysqli_real_escape_string($con, $user['username']) : '';
    $email = isset($user['email']) ? mysqli_real_escape_string($con, $user['email']) : '';

    if (isset($user['profile'])) {
        $profile = mysqli_real_escape_string($con, $user['profile']);
        $sql = "UPDATE users SET username='{$username}', email='{$email}', profile='{$profile}' WHERE id={$id}";
    } else {
        $sql = "UPDATE users SET username='{$username}', email='{$email}' WHERE id={$id}";
    }

    return mysqli_query($con, $sql);
}

function deleteUser($id){
    $con = getConnection();
    $id = (int)$id;
    if ($id <= 0) return false;
    $sql = "DELETE FROM users WHERE id=" . $id;
    return mysqli_query($con, $sql);
}

function getTotalUsers() {
    $con = getConnection();
    $sql = "SELECT COUNT(*) AS total_users FROM users";
    $result = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($result)['total_users'];
}

function getActiveBookings() {
    $con = getConnection();
    $sql = "SELECT COUNT(*) AS active_bookings FROM bookings WHERE status IN ('Pending', 'Confirmed')";
    $result = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($result)['active_bookings'];
}

function getFleetVehicles() {
    $con = getConnection();
    $sql = "SELECT COUNT(*) AS fleet_vehicles FROM vehicles WHERE status = 'Available'";
    $result = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($result)['fleet_vehicles'];
}

function getPendingDamageReports() {
    $con = getConnection();
    $sql = "SELECT COUNT(*) AS pending_damage_reports FROM vehicle_damage_reports";
    $result = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($result)['pending_damage_reports'];
}

function getUserBookings($userId) {
    $con = getConnection();
    $sql = "SELECT COUNT(*) AS total_bookings FROM bookings WHERE user_id={$userId}";
    $result = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($result)['total_bookings'];
}

function getUpcomingPickups($userId) {
    $con = getConnection();
    $sql = "SELECT COUNT(*) AS upcoming_pickups FROM bookings WHERE user_id={$userId} AND pickup_date >= CURDATE()";
    $result = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($result)['upcoming_pickups'];
}

function getLoyaltyPoints($userId) {
    $con = getConnection();
    $sql = "SELECT points FROM loyalty_points WHERE user_id={$userId}";
    $result = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($result)['points'];
}
?>
