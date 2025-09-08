<?php
require_once('db.php');

function login($user){
    $con = getConnection();
    $username = mysqli_real_escape_string($con, $user['username']);
    $password = mysqli_real_escape_string($con, $user['password']);

    $sql = "SELECT * FROM users WHERE username='{$username}' AND password='{$password}'";
    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        return true;
    }
    return false;
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

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }
    return $users;
}

function getUserById($id){
    $con = getConnection();
    $id = (int)$id;
    $sql = "SELECT * FROM users WHERE id={$id}";
    $result = mysqli_query($con, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row;
    }
    return null;
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

    $sql = "DELETE FROM users WHERE id={$id}";
    return mysqli_query($con, $sql);
}

function getTotalUsers() {
    $con = getConnection();
    $sql = "SELECT COUNT(*) AS total_users FROM users";
    $result = mysqli_query($con, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (int)$row['total_users'];
    }
    return 0;
}

function getActiveBookings() {
    $con = getConnection();
    $sql = "SELECT COUNT(*) AS active_bookings FROM bookings WHERE status IN ('Pending', 'Confirmed')";
    $result = mysqli_query($con, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (int)$row['active_bookings'];
    }
    return 0;
}

function getFleetVehicles() {
    $con = getConnection();
    $sql = "SELECT COUNT(*) AS fleet_vehicles FROM vehicles WHERE status = 'Available'";
    $result = mysqli_query($con, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (int)$row['fleet_vehicles'];
    }
    return 0;
}

function getPendingDamageReports() {
    $con = getConnection();
    $sql = "SELECT COUNT(*) AS pending_damage_reports FROM vehicle_damage_reports";
    $result = mysqli_query($con, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (int)$row['pending_damage_reports'];
    }
    return 0;
}

function getUserBookings($userId) {
    $con = getConnection();
    $userId = (int)$userId;
    $sql = "SELECT COUNT(*) AS total_bookings FROM bookings WHERE user_id={$userId}";
    $result = mysqli_query($con, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (int)$row['total_bookings'];
    }
    return 0;
}

function getUpcomingPickups($userId) {
    $con = getConnection();
    $userId = (int)$userId;
    $sql = "SELECT COUNT(*) AS upcoming_pickups FROM bookings WHERE user_id={$userId} AND pickup_date >= CURDATE()";
    $result = mysqli_query($con, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (int)$row['upcoming_pickups'];
    }
    return 0;
}

function getLoyaltyPoints($userId) {
    $con = getConnection();
    $userId = (int)$userId;
    $sql = "SELECT points FROM loyalty_points WHERE user_id={$userId}";
    $result = mysqli_query($con, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (int)$row['points'];
    }
    return 0;
}
?>
