<?php
    if(isset($_GET['error'])){
        $error = $_GET['error'];
        if($error == "Invalid_user"){
            $err1= "Please type valid username/password!";
        }elseif($error == "badrequest"){
            $err2= "Please login first!";
        }
    }
?>
<html>
<head>
<title>Car Rental System - Login</title>
<link rel="stylesheet" type="text/css" href="../asset/auth.css">
</head>
<body>
<h1>Login Page</h1>
<form method="post" action="../controller/loginCheck.php" enctype="multipart/form-data" onsubmit="return loginCheck()">
<fieldset>
        <?php if(isset($err2)){ ?>
        <p style="color:red; font-weight:bold;"><?= $err2 ?></p>
    <?php } ?>
    <?php if(isset($err1)){ ?>
        <p style="color:red; font-weight:bold;"><?= $err1 ?></p>
    <?php } ?>
    Username: 
    <input type="text" id="loginUsername" name="username" onblur="checkLoginUsername()" />
    <p id="loginUError" class="error-msg"></p>

    Password: 
    <input type="password" id="loginPassword" name="password" onblur="checkLoginPassword()" />
    <p id="loginPError" class="error-msg"></p>

    <label><input type="checkbox" name="remember" value="1"> Remember me</label><br>

    <input type="submit" value="Login" />
    <p id="loginSuccess" class="error-msg"></p>
</fieldset>
<p>
<a href="signup.html">Sign up</a> | 
<a href="forgot.html">Forgot Password?</a>
</p>
</form>

<script>
function checkLoginUsername() {
    let username = document.getElementById('loginUsername').value;
    if (username == "") {
        document.getElementById('loginUError').innerHTML = "Please type username!";
    } else {
        document.getElementById('loginUError').innerHTML = "";
    }
}

function checkLoginPassword() {
    let password = document.getElementById('loginPassword').value;
    if (password == "") {
        document.getElementById('loginPError').innerHTML = "Please type password!";
    } else {
        document.getElementById('loginPError').innerHTML = "";
    }
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
