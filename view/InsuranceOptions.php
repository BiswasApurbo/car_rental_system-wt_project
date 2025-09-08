<?php
session_start();
require_once "../model/InsuranceModel.php";

// Fetch all settings
$settings = getSettings();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tier = $_POST['tier'] ?? '';
    $claim = $_POST['claim'] ?? '';
    $otherClaim = $_POST['otherClaim'] ?? '';

    if (empty($tier)) die("Please select a coverage tier.");
    if (empty($claim)) die("Please select or enter a claim.");

    // Use the 'other' claim if provided
    if ($claim === "other" && !empty($otherClaim)) {
        $claim = $otherClaim;
    }

    $deductible = $settings[$tier] ?? 0;
    $userId = $_SESSION['user_id'] ?? 1; 

    // Insert record
    addRecord($userId, $tier, $deductible, $claim);

    echo "<h2 style='color:green;'>Insurance Option Confirmed!</h2>";
    echo "<p><strong>Tier:</strong> $tier</p>";
    echo "<p><strong>Deductible:</strong> TK$deductible</p>";
    echo "<p><strong>Claim:</strong> ".htmlspecialchars($claim)."</p>";

    echo '<div style="margin-top:20px;">
            <input type="button" value="Back to services" onclick="window.location.href=\'customer_services.php\'" 
            style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;margin-right:10px;">
            <input type="button" value="Back to Profile" onclick="window.location.href=\'profile.php\'" 
            style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
          </div>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Insurance Options</title>
<link rel="stylesheet" href="../asset/designs.css">
</head>
<body>
<h1>Insurance Options</h1>
<div class="container">
<form method="POST" action="InsuranceOptions.php" id="insuranceForm">
    <label>Select Coverage Tier:</label>
    <select name="tier" id="tier" onchange="fetchClaims()" required>
        <option value="">-- Select Tier --</option>
        <?php foreach($settings as $tier => $deductible): ?>
            <option value="<?= htmlspecialchars($tier) ?>">
                <?= ucfirst($tier) ?> (Deductible: TK<?= number_format($deductible,2) ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <div id="claimContainer" style="margin-top:15px; display:none;">
        <label>Select Claim Example:</label>
        <select name="claim" id="claimDropdown" onchange="toggleOther()">
            <option value="">-- Select Claim --</option>
        </select>
        <div id="otherClaimDiv" style="display:none; margin-top:5px;">
            <input type="text" name="otherClaim" id="otherClaim" placeholder="Describe your claim here...">
        </div>
    </div>

    <br><br>
    <input type="submit" value="Confirm Option"/>
    <br><br>
    <input type="button" value="Back to services" onclick="window.location.href='customer_services.php'" 
    style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;margin-right:10px;">
    <input type="button" value="Back to Profile" onclick="window.location.href='profile.php'" 
    style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
</form>
</div>

<script>
function fetchClaims() {
    const tier = document.getElementById("tier").value;
    const claimDropdown = document.getElementById("claimDropdown");
    const container = document.getElementById("claimContainer");
    const otherDiv = document.getElementById("otherClaimDiv");

    claimDropdown.innerHTML = '<option value="">-- Select Claim --</option>';
    otherDiv.style.display = "none";

    if (tier) {
        container.style.display = "block";

        const examples = {
            basic: ["Minor scratch", "Broken side mirror", "Flat tire replacement"],
            standard: ["Moderate dent repair", "Rear bumper damage", "Windshield crack"],
            premium: ["Major accident repair", "Engine replacement", "Total loss coverage"]
        };

        if (examples[tier]) {
            examples[tier].forEach(c => {
                const opt = document.createElement("option");
                opt.value = c;
                opt.textContent = c;
                claimDropdown.appendChild(opt);
            });
        }

        const optOther = document.createElement("option");
        optOther.value = "other";
        optOther.textContent = "Other";
        claimDropdown.appendChild(optOther);
    } else {
        container.style.display = "none";
    }
}

function toggleOther() {
    const claim = document.getElementById("claimDropdown").value;
    const otherDiv = document.getElementById("otherClaimDiv");
    otherDiv.style.display = (claim === "other") ? "block" : "none";
}
</script>
</body>
</html>
