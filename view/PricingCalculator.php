<?php
session_start();
require_once "../model/PricingModel.php"; 


$settings = getSettings();
$baseFeePerDay = $settings['base_fee_per_day'] ?? 500; 
$promoCodes = getAllPromoCodes(); 

$quote = null;
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $days = $_POST['days'] ?? '';
    $promo = $_POST['promo'] ?? '';

    if (empty($days) || !is_numeric($days) || $days <= 0) {
        $error = "Please enter a valid number of rental days.";
    } else {
        $discountPercent = $promoCodes[$promo] ?? 0;
        $baseFee = $baseFeePerDay * $days;
        $discountAmount = ($discountPercent / 100) * $baseFee;
        $tax = 0.1 * $baseFee;
        $total = $baseFee + $tax - $discountAmount;

        $_SESSION['quote'] = [
            'days' => $days,
            'promo' => $promo,
            'discountPercent' => $discountPercent,
            'discountAmount' => $discountAmount,
            'baseFee' => $baseFee,
            'tax' => $tax,
            'total' => $total
        ];

        $quote = $_SESSION['quote'];

        if (isset($_SESSION['user_id'])) {
            addRecord($_SESSION['user_id'], $days, $promo, $discountPercent, $discountAmount, $baseFee, $tax, $total);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pricing Calculator</title>
    <link rel="stylesheet" href="../asset/designs.css">
    <style>
        .message { font-size: 14px; margin: 4px 0; }
        .green { color: green; }
        .red { color: red; }
        .button-container { display: flex; gap: 10px; margin-top: 20px; }
    </style>
</head>
<body>
<h1>Car Rental Pricing Calculator</h1>

<div class="container">
<?php if ($error): ?>
    <p style="color:red;"><strong><?= htmlspecialchars($error) ?></strong></p>
<?php endif; ?>

<?php if (!$quote): ?>
<form id="pricingForm" action="" method="POST" onsubmit="return calculateQuote()">
    <label>Rental Days:</label>
    <input type="number" name="days" id="days" oninput="updatePrice()" min="1"/>

    <label>Promo Code:</label>
    <input list="promoList" name="promo" id="promo" oninput="applyPromo()"/>
    <datalist id="promoList">
        <?php foreach ($promoCodes as $code => $percent): ?>
            <option value="<?= htmlspecialchars($code) ?>"><?= $percent ?>% off</option>
        <?php endforeach; ?>
    </datalist>
    <p id="promoMessage" class="message"></p>

    <h3>Fee Breakdown (Preview):</h3>
    <p>Base Fee (TK<?= number_format($baseFeePerDay,2) ?>/day): <span id="base">TK0.00</span></p>
    <p>Tax (10%): <span id="tax">TK0.00</span></p>
    <p>Discount: <span id="discount">TK0.00</span> (<span id="discountPercent">0%</span>)</p>
    <p><strong>Total: <span id="total">TK0.00</span></strong></p>

    <input type="hidden" name="totalAmount" id="totalAmount"/>
    <input type="submit" value="Get Quote"/>
    <br><br>
    <div class="button-container">
        <input type="button" value="Back to services" onclick="window.location.href='customer_services.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
        <input type="button" value="Back to Profile" onclick="window.location.href='profile.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
    </div>
</form>
<?php else: ?>
<div style="margin-top:20px;">
    <h2 style="color:green;">Quote Generated Successfully!</h2>
    <p>Rental Days: <?= htmlspecialchars($quote['days']) ?></p>
    <p>Promo Code: <?= htmlspecialchars($quote['promo']) ?> (<?= $quote['discountPercent'] ?>% discount)</p>
    <p>Base Fee: TK<?= number_format($quote['baseFee'],2) ?></p>
    <p>Tax (10%): TK<?= number_format($quote['tax'],2) ?></p>
    <p>Discount: TK<?= number_format($quote['discountAmount'],2) ?></p>
    <p><strong>Total: TK<?= number_format($quote['total'],2) ?></strong></p>

    <div class="button-container">
        <input type="button" value="Back to services" onclick="window.location.href='customer_services.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
        <input type="button" value="Back to Profile" onclick="window.location.href='profile.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
    </div>
</div>
<?php endif; ?>
</div>

<script>
let baseRate = <?= $baseFeePerDay ?>;
let discountPercent = 0;
let promoCodes = <?= json_encode($promoCodes) ?>;

function applyPromo() {
    const code = document.getElementById("promo").value.trim();
    const msg = document.getElementById("promoMessage");

    if (code in promoCodes) {
        discountPercent = promoCodes[code];
        msg.textContent = discountPercent + "% off applied!";
        msg.className = "message green";
    } else if (code === "") {
        discountPercent = 0;
        msg.textContent = "";
        msg.className = "message";
    } else {
        discountPercent = 0;
        msg.textContent = "Invalid promo code!";
        msg.className = "message red";
    }
    updatePrice();
}

function updatePrice() {
    const days = parseInt(document.getElementById("days").value);
    if (isNaN(days) || days <= 0) {
        setPrices(0,0,0,0,0);
        return;
    }

    const baseFee = baseRate * days;
    const discount = (discountPercent/100)*baseFee;
    const tax = 0.1 * baseFee;
    const total = baseFee + tax - discount;

    setPrices(baseFee, tax, discount, total, discountPercent);
    document.getElementById("totalAmount").value = total.toFixed(2);
}

function setPrices(base, tax, discount, total, percent) {
    document.getElementById("base").textContent = "TK"+base.toFixed(2);
    document.getElementById("tax").textContent = "TK"+tax.toFixed(2);
    document.getElementById("discount").textContent = "TK"+discount.toFixed(2);
    document.getElementById("discountPercent").textContent = percent+"%";
    document.getElementById("total").textContent = "TK"+total.toFixed(2);
}

function calculateQuote() {
    const days = parseInt(document.getElementById("days").value);
    if (isNaN(days) || days <= 0) {
        alert("Please enter valid rental days.");
        return false;
    }
    return true; 
}
</script>
</body>
</html>
