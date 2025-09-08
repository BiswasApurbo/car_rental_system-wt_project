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
$username = "";
$rememberChecked = false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Car Rental System - Login</title>
<link rel="stylesheet" type="text/css" href="../asset/auth.css">
<style>
.error-msg { color: red; font-weight: 600; margin: 4px 0; }
</style>
</head>
<body>
<h1>Login Page</h1>

<?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
    <p style="text-align:center; font-weight:bold; color:green; margin:8px 0 12px;">
        Registration successful! Please login.
    </p>
<?php endif; ?>

<form method="post" action="../controller/loginCheck.php" onsubmit="return loginCheck()">
<fieldset>
    <?php if ($err2): ?>
        <p style="color:red; font-weight:bold;"><?= htmlspecialchars($err2) ?></p>
    <?php endif; ?>
    <?php if ($err1): ?>
        <p style="color:red; font-weight:bold;"><?= htmlspecialchars($err1) ?></p>
    <?php endif; ?>

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
    <input type="button" value="Forgot Password" onclick="window.location.href='forgot.php'">
    <input type="button" value="Sign Up" onclick="window.location.href='signup.php'">
</div>
</form>

<script>
function checkLoginUsername() {
    const username = document.getElementById('loginUsername').value.trim();
    document.getElementById('loginUError').innerHTML =
        username === "" ? "Please type username!" : "";
}

function checkLoginPassword() {
    const password = document.getElementById('loginPassword').value;
    document.getElementById('loginPError').innerHTML =
        password === "" ? "Please type password!" : "";
}

function loginCheck() {
    checkLoginUsername();
    checkLoginPassword();
    const uError = document.getElementById('loginUError').innerHTML;
    const pError = document.getElementById('loginPError').innerHTML;
    return uError === "" && pError === "";
}
</script>
</body>
</html>
