<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fuelLevel = $_POST['fuelLevel'] ?? '';
    $refuelLiters = $_POST['refuelLiters'] ?? '';

    if ($fuelLevel === '' || $refuelLiters === '' || !isset($_FILES['receiptUpload'])) {
        die("Please fill all fields and upload receipt.");
    }

    if (floatval($fuelLevel) < floatval($refuelLiters)) {
        die("Error: Fuel level at checkout must be greater than or equal to refuel liters.");
    }

    $uploadDir = "GasReceiptUploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $file = $_FILES['receiptUpload'];
    $fileName = time() . "_" . basename($file['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $_SESSION['fuelRecord'] = [
            'fuelLevel' => $fuelLevel,
            'refuelLiters' => $refuelLiters,
            'receipt' => $filePath,
            'time' => date('Y-m-d H:i:s')
        ];

        echo "<h2 style='color:green;'>Fuel record submitted successfully!</h2>";
        echo "<p>Fuel Level: ".htmlspecialchars($fuelLevel)." liters</p>";
        echo "<p>Refuel Liters: ".htmlspecialchars($refuelLiters)." liters</p>";
        echo "<p>Receipt: <a href='".htmlspecialchars($filePath)."' target='_blank'>View Receipt</a></p>";

        echo '<br><input type="button" value="Back to services" onclick="window.location.href=\'customer_services.php\'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">';
        echo ' <input type="button" value="Back to Profile" onclick="window.location.href=\'profile.php\'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">';

        exit;
    } else {
        die("Failed to upload receipt.");
    }
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
<form id="fuelForm" action="FuelTracking.php" method="POST" onsubmit="return submitFuel()" enctype="multipart/form-data">
    <label>Fuel Level at Checkout (liters):</label>
    <input type="number" id="fuelLevel" name="fuelLevel" min="0" step="0.1" placeholder="e.g., 5.5" />

    <label>Refuel Needed (liters):</label>
    <input type="number" id="refuelLiters" name="refuelLiters" min="0" step="0.1" placeholder="Enter liters to refuel" oninput="calculateCost()" />

    <p><strong>Price per liter:</strong> TK 120</p>
    <p><strong>Total Refuel Cost:</strong> TK <span id="totalCost">0.00</span></p>

    <label>Upload Gas Receipt:</label>
    <input type="file" id="receiptUpload" name="receiptUpload" accept="image/*" />

    <p id="formError" style="color:red; font-size:12px;"></p>

    <input type="submit" value="Submit Fuel Record" />
    <br> <br>
    <input type="button" value="Back to services" onclick="window.location.href='customer_services.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
    <input type="button" value="Back to Profile" onclick="window.location.href='profile.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
</form>
</div>

<script>
const pricePerLiter = 120;

function calculateCost() {
    const liters = parseFloat(document.getElementById("refuelLiters").value);
    const total = (!isNaN(liters) && liters > 0) ? liters * pricePerLiter : 0;
    document.getElementById("totalCost").textContent = total.toFixed(2);
}

function submitFuel() {
    const fuelLevel = parseFloat(document.getElementById("fuelLevel").value);
    const refuelLiters = parseFloat(document.getElementById("refuelLiters").value);
    const receipt = document.getElementById("receiptUpload").files.length;

    let valid = true;
    let errorMsg = "";

    if (isNaN(fuelLevel) || fuelLevel < 0) {
        errorMsg += "Please enter a valid fuel level.\n";
        valid = false;
    }
    if (isNaN(refuelLiters) || refuelLiters <= 0) {
        errorMsg += "Please enter refuel liters.\n";
        valid = false;
    }
    if (fuelLevel < refuelLiters) {
        errorMsg += "Fuel level at checkout must be greater than or equal to refuel needed.\n";
        valid = false;
    }
    if (receipt === 0) {
        errorMsg += "Please upload a gas receipt.\n";
        valid = false;
    }

    document.getElementById("formError").textContent = errorMsg;
    return valid;
}
</script>
</body>
</html>
