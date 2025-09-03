<?php
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['new_password'] ?? '');

    if (strlen($newPassword) < 4) {
        $error = "Password must be at least 4 characters!";
    } else {
        $_SESSION['password'] = $newPassword;

        $success = "Password has been reset successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Password</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .err { color:red; font-weight:600; }
        .ok { color:green; font-weight:600; }
    </style>
</head>
<body>
    <h1>Reset Password</h1>
    <form method="post" action="" onsubmit="return resetCheck()">
        <fieldset>
            New Password:
            <input type="password" id="newPassword" name="new_password" onblur="checkNewPassword()" />
            <p id="resetPError" class="err"><?= htmlspecialchars($error) ?></p>
            <input type="submit" value="Reset Password" />
            <?php if ($success): ?>
                <p class="ok"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>
        </fieldset>
        <p><a href="login.php">Back to Login</a></p>
    </form>
    <script>
        function checkNewPassword() {
            let password = document.getElementById('newPassword').value;
            if (password.length < 4) {
                document.getElementById('resetPError').innerHTML = "Password must be at least 4 characters!";
            } else {
                document.getElementById('resetPError').innerHTML = "";
            }
        }
        function resetCheck() {
            checkNewPassword();
            return document.getElementById('resetPError').innerHTML === "";
        }
    </script>
</body>
</html>
