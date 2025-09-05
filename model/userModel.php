<?php
    require_once('db.php');

    function login($user){
        $con = getConnection();
        $sql = "select * from users where username='{$user['username']}' and password='{$user['password']}'";
        $result = mysqli_query($con, $sql);
        $count = mysqli_num_rows($result);
        if($count == 1){
            return true;
        }else{
            return false;
        }
    }

function addUser($user){
    $con = getConnection();
    $role = isset($user['role']) ? $user['role'] : 'User';
    $username = mysqli_real_escape_string($con, $user['username']);
    $password = mysqli_real_escape_string($con, $user['password']);
    $email    = mysqli_real_escape_string($con, $user['email']);
    $roleEsc  = mysqli_real_escape_string($con, $role);

    $sql = "INSERT INTO users (username, password, email, role) VALUES ('{$username}', '{$password}', '{$email}', '{$roleEsc}')";
    if (mysqli_query($con, $sql)){
        return true;
    } else {
        error_log("addUser failed: " . mysqli_error($con));
        return false;
    }
}


    function getAlluser(){
        $con = getConnection();
        $sql = "select * from users";
        $result = mysqli_query($con, $sql);
        $users = [];

        while($row = mysqli_fetch_assoc($result)){
            array_push($users, $row);
        }

        return $users;
    }

    function getUserById($id){
        $con = getConnection();
        $sql = "select * from users where id={$id}";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row;
    }

    function updateUser($user){
        $con = getConnection();
        if (empty($user['id'])) return false;
        $id = (int)$user['id'];

    $username = isset($user['username']) ? mysqli_real_escape_string($con, $user['username']) : '';
    $email    = isset($user['email']) ? mysqli_real_escape_string($con, $user['email']) : '';

    if (isset($user['profile'])) {
        $profile = mysqli_real_escape_string($con, $user['profile']);
        $sql = "update users set username='{$username}', email='{$email}', profile='{$profile}' where id={$id}";
    } else {
        $sql = "update users set username='{$username}', email='{$email}' where id={$id}";
    }

    if (mysqli_query($con, $sql)) {
        return true;
    } else {
        error_log("updateUser failed: " . mysqli_error($con));
        return false;
    }
}


function deleteUser($id){
    $con = getConnection();
    $id = (int)$id;
    if ($id <= 0) return false;
    $sql = "DELETE FROM users WHERE id=" . $id;
    if (mysqli_query($con, $sql)) {
        return true;
    } else {
        error_log("deleteUser failed: " . mysqli_error($con));
        return false;
    }
}
?>
