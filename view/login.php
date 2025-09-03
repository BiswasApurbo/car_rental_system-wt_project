<?php
session_start();

$err1 = $err2 = "";
if (isset($_GET['error'])) {
    $error = $_GET['error'];
    if ($error === "Invalid_user") {
        $err1 = "Please type valid username/password!";
    } elseif ($error === "badrequest") {
        $err2 = "Please login first!";
    }
}

$phpErrU = $phpErrP = "";
$username = $password = "";
$rememberChecked = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $rememberChecked = isset($_POST['remember']) && $_POST['remember'] === '1';

    if ($username === "") {
        $phpErrU = "Please type username!";
    }
    if ($password === "") {
        $phpErrP = "Please type password!";
    }

    if ($phpErrU === "" && $phpErrP === "") {
        $_SESSION['pending_login'] = [
            'username' => $username,
            'password' => $password,
            'remember' => $rememberChecked ? '1' : '0'
        ];
        header('Location: ../controller/loginCheck.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Car Rental System - Login</title>
<meta charset="utf-8" />
<link rel="stylesheet" type="text/css" href="../asset/auth.css">
</head>
<body>
<h1>Login Page</h1>

<form method="post" action="" enctype="multipart/form-data" onsubmit="return loginCheck()">
<fieldset>
    <?php if ($err2) { ?>
        <p style="color:red; font-weight:bold;"><?= htmlspecialchars($err2) ?></p>
    <?php } ?>
    <?php if ($err1) { ?>
        <p style="color:red; font-weight:bold;"><?= htmlspecialchars($err1) ?></p>
    <?php } ?>

    <?php if ($phpErrU) { ?>
        <p style="color:red; font-weight:bold;"><?= htmlspecialchars($phpErrU) ?></p>
    <?php } ?>
    <?php if ($phpErrP) { ?>
        <p style="color:red; font-weight:bold;"><?= htmlspecialchars($phpErrP) ?></p>
    <?php } ?>

    Username:
    <input type="text" id="loginUsername" name="username"
           value="<?= htmlspecialchars($username) ?>"
           onblur="checkLoginUsername()" />
    <p id="loginUError" class="error-msg"></p>

    Password:
    <input type="password" id="loginPassword" name="password" onblur="checkLoginPassword()" />
    <p id="loginPError" class="error-msg"></p>

    <label><input type="checkbox" name="remember" value="1" <?= $rememberChecked ? 'checked' : '' ?>> Remember me</label><br>

    <input type="submit" value="Login" />
    <p id="loginSuccess" class="error-msg"></p>
</fieldset>
<div style="display:flex; justify-content:center; gap:15px; margin-top:10px;">
    <input type="button" 
           value="Forgot Password" 
           onclick="window.location.href='forgot.html.php'">

    <input type="button" 
           value="Sign Up" 
           onclick="window.location.href='signup.php'">
</div>
</form>
<script>
function checkLoginUsername() {
    let username = document.getElementById('loginUsername').value;
    document.getElementById('loginUError').innerHTML =
        (username === "") ? "Please type username!" : "";
}
function checkLoginPassword() {
    let password = document.getElementById('loginPassword').value;
    document.getElementById('loginPError').innerHTML =
        (password === "") ? "Please type password!" : "";
}
function loginCheck() {
    let username = document.getElementById('loginUsername').value;
    let password = document.getElementById('loginPassword').value;

    if (username === "" || password === "") {
        checkLoginUsername();
        checkLoginPassword();
        return false;
    }
    return true;
}
</script>
</body>
</html>
