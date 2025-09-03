<?php
session_start();

$username = '';
$email = '';
$errors = ['username'=>'', 'email'=>'', 'password'=>''];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '') {
        $errors['username'] = 'Please type username!';
    } elseif (mb_strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters!';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email!';
    }

    if (strlen($password) < 4) {
        $errors['password'] = 'Password must be at least 4 characters!';
    }

    if ($errors['username'] === '' && $errors['email'] === '' && $errors['password'] === '') {
        $success = 'Signup successful! You can now log in.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Car Rental System - Signup</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .error-msg { color: red; font-weight: 600; margin: 4px 0; }
        .ok { color: green; font-weight: 700; margin-top: 10px; text-align: center; }
        .center-under { text-align: center; font-weight: 700; margin: 6px 0 14px; color: green; }
    </style>
</head>
<body>
    <h1>Signup Page</h1>

    <?php if ($success): ?>
        <p class="center-under"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" action="" onsubmit="return signupCheck()">
        <fieldset>
            Username:
            <input type="text" id="signupUsername" name="username" value="<?= htmlspecialchars($username) ?>" onblur="checkSignupUsername()" />
            <p id="signupUError" class="error-msg"><?= htmlspecialchars($errors['username']) ?></p>

            Email:
            <input type="text" id="signupEmail" name="email" value="<?= htmlspecialchars($email) ?>" onblur="checkSignupEmail()" />
            <p id="signupEError" class="error-msg"><?= htmlspecialchars($errors['email']) ?></p>

            Password:
            <input type="password" id="signupPassword" name="password" onblur="checkSignupPassword()" />
            <p id="signupPError" class="error-msg"><?= htmlspecialchars($errors['password']) ?></p>

            <input type="submit" value="Sign Up" />
            <p id="signupSuccess" class="ok"></p>
        </fieldset>
<p style="text-align:center;">
    <input type="button" 
           value="Login" 
           onclick="window.location.href='login.php'">
</p>
    </form>

    <script>
        function checkSignupUsername() {
            const username = document.getElementById('signupUsername').value.trim();
            let msg = "";
            if (username === "") msg = "Please type username!";
            else if (username.length < 3) msg = "Username must be at least 3 characters!";
            document.getElementById('signupUError').innerHTML = msg;
        }
        function checkSignupEmail() {
            const email = document.getElementById('signupEmail').value.trim();
            const valid = email !== "" && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            document.getElementById('signupEError').innerHTML = valid ? "" : "Please enter a valid email!";
        }
        function checkSignupPassword() {
            const password = document.getElementById('signupPassword').value;
            document.getElementById('signupPError').innerHTML =
                password.length < 4 ? "Password must be at least 4 characters!" : "";
        }
        function signupCheck() {
            checkSignupUsername();
            checkSignupEmail();
            checkSignupPassword();
            const ok =
                document.getElementById('signupUError').innerHTML === "" &&
                document.getElementById('signupEError').innerHTML === "" &&
                document.getElementById('signupPError').innerHTML === "";
            document.getElementById('signupSuccess').innerHTML = ok ? "Submitting…" : "";
            return ok;
        }
    </script>
</body>
</html>
