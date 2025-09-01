<?php
session_start();

// Initialize points if not set
if (!isset($_SESSION['points'])) {
    $_SESSION['points'] = 1250; // starting points
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

// Determine tier
if ($currentPoints >= 2000) {
    $tier = "Platinum";
    $nextTierPoints = "Max tier reached 🎉";
    $progress = 100;
} elseif ($currentPoints >= 500) {
    $tier = "Gold";
    $nextTierPoints = 2000 - $currentPoints;
    $progress = ($currentPoints / 2000) * 100;
} else {
    $tier = "Silver";
    $nextTierPoints = 500 - $currentPoints;
    $progress = ($currentPoints / 500) * 100;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Loyalty Program</title>
    <link rel="stylesheet" href="LoyaltyProgram.css">
</head>
<body>
<div class="form-wrapper">
    <fieldset>
        <h1>Loyalty Program Dashboard</h1>

        <!-- Message -->
        <?= $message ?>

        <!-- Points Tracker -->
        <h2>💎 Points Tracker</h2>
        <p>Current Points: <strong id="currentPoints"><?= $currentPoints ?></strong></p>
        <p>Current Tier: <strong><?= $tier ?></strong></p>
        <p>Points to Next Tier: <strong><?= $nextTierPoints ?></strong></p>
        <div class="points-bar">
            <div class="points-progress" style="width: <?= $progress ?>%;"></div>
        </div>

        <!-- Reward Catalog -->
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

        <!-- Tier Benefits -->
        <h2>🏆 Tier Benefits</h2>
        <ul>
            <li>Silver: 0-499 points – Basic benefits</li>
            <li>Gold: 500-1999 points – Free upgrades, priority support</li>
            <li>Platinum: 2000+ points – Exclusive rewards, VIP support</li>
        </ul>
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
