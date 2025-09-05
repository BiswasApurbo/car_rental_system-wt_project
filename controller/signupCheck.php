<?php
    session_start();
    require_once('../model/userModel.php');

    $username = trim($_REQUEST['username'] ?? '');
    $password = trim($_REQUEST['password'] ?? '');
    $email = trim($_REQUEST['email'] ?? '');

    if($username == "" || $password == "" || $email == ""){
        header('location: ../view/signup.php?error=badrequest');
        exit;
    }

    $users = getAlluser();
    $exists = false;
    foreach($users as $u){
        if(isset($u['email']) && $u['email'] === $email){
            $exists = true;
            break;
        }
    }

    if($exists){
        header('location: ../view/signup.php?error=email_exists');
        exit;
    } else {
        $user = ['username'=> $username, 'password'=>$password, 'email'=> $email, 'role' => 'User'];
        $status = addUser($user);
        if($status){
           header('location: ../view/login.php?success=registered');
           exit;
        }else{
           header('location: ../view/signup.php?error=regerror');
           exit;
        }
    }
?>
