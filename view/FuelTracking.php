<?php
session_start();
require_once "../model/FuelModel.php";

$user_id = $_SESSION['user_id'] ?? 1;
$pricePerLiter = getPricePerLiter();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fuel Tracking</title>
    <link rel="stylesheet" href="../asset/designs.css">
    <style>
        .message { font-size:14px; margin:4px 0; }
        .green { color:green; }
        .red { color:red; }
        #result { margin-top:20px; padding:15px; border:1px solid #ddd; border-radius:6px; }
        .button-container { display:flex; gap:10px; margin-top:20px; }
    </style>
</head>
<body>
<h1>Fuel Tracking</h1>
<div class="container">
<form id="fuelForm" enctype="multipart/form-data">
    <label>Fuel Limit (liters):</label>
    <input type="number" id="fuelLimit" name="fuelLimit" min="0" step="0.1" placeholder="Enter fuel limit" />

    <label>Refuel Needed (liters):</label>
    <input type="number" id="refuelLiters" name="refuelLiters" min="0" step="0.1" placeholder="Enter liters to refuel" oninput="calculateCost()" />

    <p><strong>Price per liter:</strong> TK <span id="displayPricePerLiter"><?= number_format($pricePerLiter,2) ?></span></p>
    <p><strong>Total Refuel Cost:</strong> TK <span id="totalCost">0.00</span></p>

    <input type="hidden" name="pricePerLiter" id="pricePerLiter" value="<?= number_format($pricePerLiter,2) ?>" />
    <input type="hidden" name="totalCost" id="hiddenTotalCost" />

    <label>Upload Gas Receipt:</label>
    <input type="file" id="receiptUpload" name="receiptUpload" accept="image/*" />

    <p id="formError" class="red message"></p>
    <input type="submit" value="Submit Fuel Record"/>
    <div class="button-container">
        <input type="button" value="Back to Services" onclick="window.location.href='customer_services.php'">
        <input type="button" value="Back to Profile" onclick="window.location.href='profile.php'">
    </div>
    <p id="result"></p>
</form>
</div>

<script>
const pricePerLiterValue = <?= $pricePerLiter ?>;

function calculateCost() {
    const liters = parseFloat(document.getElementById("refuelLiters").value);
    const total = (!isNaN(liters) && liters > 0) ? liters * pricePerLiterValue : 0;
    document.getElementById("displayPricePerLiter").textContent = pricePerLiterValue.toFixed(2);
    document.getElementById("totalCost").textContent = total.toFixed(2);
    document.getElementById("pricePerLiter").value = pricePerLiterValue.toFixed(2);
    document.getElementById("hiddenTotalCost").value = total.toFixed(2);
}

document.getElementById("fuelForm").addEventListener("submit", function(e){
    e.preventDefault();
    const fuelLimit = parseFloat(document.getElementById("fuelLimit").value);
    const refuelLiters = parseFloat(document.getElementById("refuelLiters").value);
    const receiptFile = document.getElementById("receiptUpload").files[0];
    const formError = document.getElementById("formError");
    const resultDiv = document.getElementById("result");

    formError.textContent = "";
    resultDiv.textContent = "";

    let valid = true;
    let errorMsg = "";

    if (isNaN(fuelLimit) || fuelLimit <= 0) { errorMsg += "Enter valid fuel limit.\n"; valid = false; }
    if (isNaN(refuelLiters) || refuelLiters <= 0) { errorMsg += "Enter refuel liters.\n"; valid = false; }
    if (fuelLimit < refuelLiters) { errorMsg += "Fuel limit must be >= refuel liters.\n"; valid = false; }
    if (!receiptFile) { errorMsg += "Please upload a receipt.\n"; valid = false; }

    if (!valid) {
        formError.textContent = errorMsg;
        return;
    }

    const formData = new FormData();
    formData.append("fuelLimit", fuelLimit);
    formData.append("refuelLiters", refuelLiters);
    formData.append("pricePerLiter", document.getElementById("pricePerLiter").value);
    formData.append("totalCost", document.getElementById("hiddenTotalCost").value);
    formData.append("receiptUpload", receiptFile);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "../controller/Fuel_handler.php", true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.success) {
                    const r = res.data;
                    resultDiv.innerHTML = `
                        <h2 class="green">✅ Fuel record submitted successfully!</h2>
                        <p>Fuel Limit: ${r.fuelLimit} liters</p>
                        <p>Refuel Liters: ${r.refuelLiters} liters</p>
                        <p>Price per Liter: TK ${r.pricePerLiter}</p>
                        <p>Total Refuel Cost: TK ${r.totalCost}</p>
                        <p>Receipt: <a href='${r.receiptPath}' target='_blank'>View Receipt</a></p>
                    `;
                    document.getElementById("fuelForm").reset();
                    document.getElementById("totalCost").textContent = "0.00";
                } else {
                    formError.textContent = res.message;
                }
            } catch(e) {
                resultDiv.innerHTML = "<p class='red'>Server error parsing response.</p>";
            }
        } else {
            resultDiv.innerHTML = "<p class='red'>Server error.</p>";
        }
    };

    xhr.send(formData);
});
</script>
</body>
</html>
