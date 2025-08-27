<?php
    session_start();
    if(!isset($_SESSION['status']) || $_SESSION['status'] !== true){
        if(isset($_COOKIE['status']) && $_COOKIE['status'] === '1'){
            $_SESSION['status'] = true;
            if(!isset($_SESSION['username']) && isset($_COOKIE['remember_user'])){
                $_SESSION['username'] = $_COOKIE['remember_user'];
            }
        }else{
            header('location: ../view/login.php?error=badrequest');
            exit;
        }
    }
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
?>
