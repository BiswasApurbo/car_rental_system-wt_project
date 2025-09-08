<?php
session_start();
require_once "../model/FuelModel.php";

$user_id = $_SESSION['user_id'] ?? 1;
$pricePerLiter = getPricePerLiter(); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fuelLimit = floatval($_POST['fuelLimit'] ?? 0);
    $refuelLiters = floatval($_POST['refuelLiters'] ?? 0);
    $pricePerLiterPost = floatval($_POST['pricePerLiter'] ?? 0);
    $totalCost = floatval($_POST['totalCost'] ?? 0);

    if ($fuelLimit <= 0 || $refuelLiters <= 0 || !isset($_FILES['receiptUpload'])) {
        die("<p style='color:red;'>Please fill all fields and upload receipt.</p>");
    }

    if ($fuelLimit < $refuelLiters) {
        die("<p style='color:red;'>Fuel limit must be greater than or equal to refuel liters.</p>");
    }

    $uploadDir = "../view/GasReceiptUploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $file = $_FILES['receiptUpload'];
    $fileName = time() . "_" . basename($file['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $success = addFuelRecord($user_id, $fuelLimit, $refuelLiters, $pricePerLiterPost, $totalCost, $fileName);

        if ($success) {
            echo "<h2 style='color:green;'>✅ Fuel record submitted successfully!</h2><br>";
            echo "<p>Fuel Limit: ".htmlspecialchars($fuelLimit)." liters</p>";
            echo "<p>Refuel Liters: ".htmlspecialchars($refuelLiters)." liters</p>";
            echo "<p>Price per Liter: TK ".htmlspecialchars($pricePerLiterPost)."</p>";
            echo "<p>Total Refuel Cost: TK ".htmlspecialchars($totalCost)."</p>";
            echo "<p>Receipt: <a href='".htmlspecialchars($filePath)."' target='_blank'>View Receipt</a></p><br>";
            echo '<input type="button" value="Back to Services" onclick="window.location.href=\'customer_services.php\'" style="margin-right:10px;background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">';
            echo '<input type="button" value="Back to Profile" onclick="window.location.href=\'profile.php\'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">';
        } else {
            echo "<p style='color:red;'>❌ Failed to save fuel record. Try again.</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ Failed to upload receipt.</p>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fuel Tracking</title>
    <link rel="stylesheet" href="../asset/designs.css">
</head>
<body>
<h1>Fuel Tracking</h1>
<div class="container">
<form id="fuelForm" action="" method="POST" onsubmit="return submitFuel()" enctype="multipart/form-data">
    <label>Fuel Limit (liters):</label>
    <input type="number" id="fuelLimit" name="fuelLimit" min="0" step="0.1" placeholder="Enter fuel limit" />

    <label>Refuel Needed (liters):</label>
    <input type="number" id="refuelLiters" name="refuelLiters" min="0" step="0.1" placeholder="Enter liters to refuel" oninput="calculateCost()" />

    <p><strong>Price per liter:</strong> TK <span id="displayPricePerLiter"><?= number_format($pricePerLiter, 2) ?></span></p>
    <p><strong>Total Refuel Cost:</strong> TK <span id="totalCost">0.00</span></p>

    <input type="hidden" name="pricePerLiter" id="pricePerLiter" value="<?= number_format($pricePerLiter, 2) ?>" />
    <input type="hidden" name="totalCost" id="hiddenTotalCost" />

    <label>Upload Gas Receipt:</label>
    <input type="file" id="receiptUpload" name="receiptUpload" accept="image/*" />

    <p id="formError" style="color:red; font-size:12px;"></p>
    <input type="submit" value="Submit Fuel Record" />
    <br><br>
    <input type="button" value="Back to Services" onclick="window.location.href='customer_services.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
    <input type="button" value="Back to Profile" onclick="window.location.href='profile.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
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

function submitFuel() {
    const fuelLimit = parseFloat(document.getElementById("fuelLimit").value);
    const refuelLiters = parseFloat(document.getElementById("refuelLiters").value);
    const receipt = document.getElementById("receiptUpload").files.length;

    let valid = true;
    let errorMsg = "";

    if (isNaN(fuelLimit) || fuelLimit <= 0) { errorMsg += "Please enter a valid fuel limit.\n"; valid = false; }
    if (isNaN(refuelLiters) || refuelLiters <= 0) { errorMsg += "Please enter refuel liters.\n"; valid = false; }
    if (fuelLimit < refuelLiters) { errorMsg += "Fuel limit must be greater than or equal to refuel liters.\n"; valid = false; }
    if (receipt === 0) { errorMsg += "Please upload a gas receipt.\n"; valid = false; }

    document.getElementById("formError").textContent = errorMsg;
    return valid;
}
</script>
</body>
</html>
