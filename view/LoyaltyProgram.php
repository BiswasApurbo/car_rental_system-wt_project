<?php
session_start();


if (!isset($_SESSION['points'])) {
    $_SESSION['points'] = 2000; 
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeemPoints'])) {
    $redeem = intval($_POST['redeemPoints']);
    if ($redeem <= $_SESSION['points']) {
        $_SESSION['points'] -= $redeem;
        $message = "<p class='alert-box alert-success'>✅ Successfully redeemed {$redeem} points! Remaining points: {$_SESSION['points']}</p>";
    } else {
        $message = "<p class='alert-box alert-danger'>❌ Not enough points to redeem.</p>";
    }
}

$currentPoints = $_SESSION['points'];

if ($currentPoints >= 2500) {
    $tier = "Platinum";
    $nextTierPoints = "Max tier reached 🎉";
    $progress = ($currentPoints / 3000) * 100;
} elseif ($currentPoints >= 1000) {
    $tier = "Gold";
    $nextTierPoints = 2500 - $currentPoints;
    $progress = ($currentPoints / 3000) * 100;
} else {
    $tier = "Silver";
    $nextTierPoints = 1000 - $currentPoints;
    $progress = ($currentPoints / 3000) * 100;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Loyalty Program</title>
    <link rel="stylesheet" href="../asset/LoyaltyProgram.css">
    <style>
        .form-wrapper { max-width: 700px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px; }
        .points-bar { background: #eee; border-radius: 10px; width: 100%; height: 20px; margin-bottom: 15px; }
        .points-progress { height: 100%; background: #1f6feb; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 8px; text-align: left; }
        .alert-box { padding: 10px; margin-bottom: 15px; border-radius: 6px; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
        input[type=submit], input[type=button] { cursor: pointer; }
    </style>
</head>
<body>
<div class="form-wrapper">
    <fieldset>
        <h1>Loyalty Program Dashboard</h1>

     
        <?= $message ?>

        
        <h2>💎 Points Tracker</h2>
        <p>Current Points: <strong id="currentPoints"><?= $currentPoints ?></strong></p>
        <p>Current Tier: <strong><?= $tier ?></strong></p>
        <p>Points to Next Tier: <strong><?= $nextTierPoints ?></strong></p>
        <div class="points-bar">
            <div class="points-progress" style="width: <?= $progress ?>%;"></div>
        </div>

        <h2>🎁 Reward Catalog</h2>
        <table>
            <tr>
                <th>Reward</th>
                <th>Points Required</th>
                <th>Action</th>
            </tr>
            <tr>
                <td>Free Coffee</td>
                <td>100</td>
                <td>
                    <form method="post" onsubmit="return validateRedeem(100)">
                        <input type="hidden" name="redeemPoints" value="100">
                        <input type="submit" value="Redeem">
                    </form>
                </td>
            </tr>
            <tr>
                <td>Discount Voucher tk.10000</td>
                <td>500</td>
                <td>
                    <form method="post" onsubmit="return validateRedeem(500)">
                        <input type="hidden" name="redeemPoints" value="500">
                        <input type="submit" value="Redeem">
                    </form>
                </td>
            </tr>
            <tr>
                <td>Free Upgrade</td>
                <td>1000</td>
                <td>
                    <form method="post" onsubmit="return validateRedeem(1000)">
                        <input type="hidden" name="redeemPoints" value="1000">
                        <input type="submit" value="Redeem">
                    </form>
                </td>
            </tr>
        </table>

       
        <h2>🏆 Tier Benefits</h2>
        <ul>
            <li>Silver: 0-999 points – Basic benefits</li>
            <li>Gold: 1000-2499 points – Free upgrades, priority support</li>
            <li>Platinum: 2500-3000 points – Exclusive rewards, VIP support</li>
        </ul>

        <br><br>
        <input type="button" value="Back to services" onclick="window.location.href='customer_services.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
        <input type="button" value="Back to Profile" onclick="window.location.href='profile.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;"> 

    </fieldset>
</div>

<script>
function validateRedeem(requiredPoints) {
    const currentPoints = parseInt(document.getElementById('currentPoints').innerText);
    if (requiredPoints > currentPoints) {
        alert("You do not have enough points to redeem this reward.");
        return false;
    }
    return true;
}
</script>
</body>
</html>
