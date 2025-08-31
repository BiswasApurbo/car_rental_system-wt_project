<?php
session_start();

$quote = null;
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $days = $_POST['days'] ?? '';
    $promo = $_POST['promo'] ?? '';
    $totalAmount = $_POST['totalAmount'] ?? '';

    // PHP validation
    if (empty($days) || !is_numeric($days) || $days <= 0) {
        $error = "Please enter a valid number of rental days.";
    } else {
        // Valid promo codes
        $validPromo = ['1234'=>50, '5678'=>30];
        $discountPercent = $validPromo[$promo] ?? 0;

        // Base fee calculation
        $baseFee = 500 * $days;
        $discount = ($discountPercent / 100) * $baseFee;
        $tax = 0.1 * $baseFee;
        $total = $baseFee + $tax - $discount;

        // Save session data
        $_SESSION['quote'] = [
            'days'     => $days,
            'promo'    => $promo,
            'discount' => $discountPercent,
            'baseFee'  => $baseFee,
            'tax'      => $tax,
            'total'    => $total
        ];

        $quote = $_SESSION['quote'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pricing Calculator</title>
    <link rel="stylesheet" href="designs.css">
    <style>
        .message { font-size: 14px; margin: 4px 0; }
        .green { color: green; }
        .red { color: red; }
    </style>
</head>
<body>
<h1>Car Rental Pricing Calculator</h1>

<div class="container">
    <?php if ($error): ?>
        <p style="color:red;"><strong><?= htmlspecialchars($error) ?></strong></p>
    <?php elseif ($quote): ?>
        <h2>Quote Generated Successfully!</h2>
        <p>Rental Days: <?= htmlspecialchars($quote['days']) ?></p>
        <p>Promo Code: <?= htmlspecialchars($quote['promo']) ?> (<?= $quote['discount'] ?>% discount)</p>
        <p>Base Fee: TK<?= number_format($quote['baseFee'], 2) ?></p>
        <p>Tax (10%): TK<?= number_format($quote['tax'], 2) ?></p>
        <p>Discount: TK<?= number_format(($quote['discount']/100)*$quote['baseFee'], 2) ?></p>
        <p><strong>Total: TK<?= number_format($quote['total'], 2) ?></strong></p>
    <?php endif; ?>

    <form id="pricingForm" action="" method="POST" onsubmit="return calculateQuote()">
        <label>Rental Days:</label>
        <input type="number" name="days" id="days" oninput="updatePrice()" min="1"/>

        <label>Promo Code:</label>
        <input type="text" name="promo" id="promo" oninput="applyPromo()"/>
        <div class="note">
            Use <b>1234</b> for <b>50%</b> off<br>
            Use <b>5678</b> for <b>30%</b> off
        </div>
        <p id="promoMessage" class="message"></p>

        <h3>Fee Breakdown:</h3>
        <p>Base Fee (TK500/day): <span id="base">TK0.00</span></p>
        <p>Tax (10%): <span id="tax">TK0.00</span></p>
        <p>Discount: <span id="discount">TK0.00</span></p>
        <p><strong>Total: <span id="total">TK0.00</span></strong></p>

        <input type="hidden" name="totalAmount" id="totalAmount"/>
        <input type="submit" value="Get Quote"/>
    </form>
</div>

<script>
    let baseRate = 500;
    let discountPercent = 0;

    function applyPromo() {
        const code = document.getElementById("promo").value.trim();
        const msg = document.getElementById("promoMessage");

        if (code === "1234") {
            discountPercent = 50;
            msg.textContent = "50% off applied!";
            msg.className = "message green";
        } else if (code === "5678") {
            discountPercent = 30;
            msg.textContent = "30% off applied!";
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
            setPrices(0,0,0,0);
            return;
        }

        const baseFee = baseRate * days;
        const discount = (discountPercent/100)*baseFee;
        const tax = 0.1 * baseFee;
        const total = baseFee + tax - discount;

        setPrices(baseFee, tax, discount, total);
        document.getElementById("totalAmount").value = total.toFixed(2);
    }

    function setPrices(base, tax, discount, total) {
        document.getElementById("base").textContent = "TK"+base.toFixed(2);
        document.getElementById("tax").textContent = "TK"+tax.toFixed(2);
        document.getElementById("discount").textContent = "TK"+discount.toFixed(2);
        document.getElementById("total").textContent = "TK"+total.toFixed(2);
    }

    function calculateQuote() {
        const days = parseInt(document.getElementById("days").value);
        if (isNaN(days) || days <= 0) {
            alert("Please enter valid rental days.");
            return false;
        }
        return true; // let PHP handle final processing
    }
</script>
</body>
</html>
