<?php
session_start();
require_once "../model/PricingModel.php";

$settings = getSettings();
$baseFeePerDay = $settings['base_fee_per_day'] ?? 500;
$promoCodes = getAllPromoCodes();
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
    #result { margin-top: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 6px; }
</style>
</head>
<body>
<h1>Car Rental Pricing Calculator</h1>

<div class="container">
<form id="pricingForm" method="POST">
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

<div id="result"></div>
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
    const discountAmount = (discountPercent/100) * baseFee;
    const tax = 0.1 * baseFee;
    const total = baseFee + tax - discountAmount;

    setPrices(baseFee, tax, discountAmount, total, discountPercent);
    document.getElementById("totalAmount").value = total.toFixed(2);
}

function setPrices(base, tax, discount, total, percent) {
    document.getElementById("base").textContent = "TK"+base.toFixed(2);
    document.getElementById("tax").textContent = "TK"+tax.toFixed(2);
    document.getElementById("discount").textContent = "TK"+discount.toFixed(2);
    document.getElementById("discountPercent").textContent = percent+"%";
    document.getElementById("total").textContent = "TK"+total.toFixed(2);
}

// AJAX submission
document.getElementById("pricingForm").addEventListener("submit", function(e){
    e.preventDefault();

    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "../controller/PricingCalculator_handler.php", true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

    xhr.onload = function() {
        const resultDiv = document.getElementById("result");
        if (xhr.status === 200) {
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.success) {
                    resultDiv.innerHTML = `
                        <h2 style="color:green;">Quote Generated Successfully!</h2>
                        <p>Rental Days: ${res.quote.days}</p>
                        <p>Promo Code: ${res.quote.promo} (${res.quote.discountPercent}% discount)</p>
                        <p>Base Fee: TK${res.quote.baseFee.toFixed(2)}</p>
                        <p>Tax (10%): TK${res.quote.tax.toFixed(2)}</p>
                        <p>Discount: TK${res.quote.discountAmount.toFixed(2)}</p>
                        <p><strong>Total: TK${res.quote.total.toFixed(2)}</strong></p>
                    `;
                } else {
                    resultDiv.innerHTML = `<p style="color:red;"><strong>${res.error}</strong></p>`;
                }
            } catch(e) {
                resultDiv.innerHTML = "<p style='color:red;'>Server error parsing response.</p>";
            }
        } else {
            resultDiv.innerHTML = "<p style='color:red;'>Server error.</p>";
        }
    };

    xhr.send(formData);
});
</script>
</body>
</html>
