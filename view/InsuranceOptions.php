<?php
session_start();
require_once '../model/InsuranceModel.php';

$model = new InsuranceModel();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $coverage = $_POST['coverage'] ?? '';
    $claim = $_POST['claim'] ?? '';

    if (empty($coverage)) die("Please select a coverage tier.");
    if (empty($claim)) die("Please select or enter a claim example.");

    $coverageData = [
        'basic' => ['deductible'=>5000],
        'standard' => ['deductible'=>3000],
        'premium' => ['deductible'=>1000]
    ];

    if (!isset($coverageData[$coverage])) die("Invalid coverage selected.");

    $userId = $_SESSION['user_id'] ?? 1;
    $deductible = $coverageData[$coverage]['deductible'];

    $model->addInsuranceOption($userId, $coverage, $deductible, $claim);

    $_SESSION['insurance'] = [
        'tier' => $coverage,
        'deductible' => $deductible,
        'claim' => $claim,
        'time' => date('Y-m-d H:i:s')
    ];

    echo "<h2>Insurance Option Confirmed!</h2>";
    echo "<p>Coverage Tier: ".htmlspecialchars($coverage)."</p>";
    echo "<p>Deductible: TK".$deductible."</p>";
    echo "<p>Selected Claim Example: ".htmlspecialchars($claim)."</p>";

    echo '<br><input type="button" value="Back to services" onclick="window.location.href=\'customer_services.php\'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">';
    echo ' <input type="button" value="Back to Profile" onclick="window.location.href=\'profile.php\'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">';

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
    <form id="insuranceForm" action="" method="POST" onsubmit="return submitInsurance()">
        <label>Select Coverage Tier:</label>
        <select name="coverage" id="coverage" onchange="updateCoverage()">
            <option value="">-- Select Tier --</option>
            <option value="basic">Basic</option>
            <option value="standard">Standard</option>
            <option value="premium">Premium</option>
        </select>

        <h3>Deductible Amount:</h3>
        <p id="deductible">TK0</p>

        <h3>Claim Examples:</h3>
        <select name="claim" id="claimDropdown" onchange="checkOtherClaim()">
            <option value="">-- Select Claim --</option>
        </select>

        <div id="otherClaimContainer" style="display:none; margin-top:5px;">
            <input type="text" id="otherClaim" name="otherClaim" placeholder="Describe your claim here...">
        </div>

        <input type="submit" value="Confirm Option"/>
        <br><br>
        <input type="button" value="Back to services" onclick="window.location.href='customer_services.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
        <input type="button" value="Back to Profile" onclick="window.location.href='profile.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
    </form>
</div>

<script>
const coverageData = {
    basic: ["Minor scratch", "Broken side mirror", "Flat tire replacement"],
    standard: ["Moderate dent repair", "Rear bumper damage", "Windshield crack"],
    premium: ["Major accident repair", "Engine replacement", "Total loss coverage"]
};

function updateCoverage() {
    const tier = document.getElementById("coverage").value;
    const deductibleEl = document.getElementById("deductible");
    const claimDropdown = document.getElementById("claimDropdown");
    const otherClaimContainer = document.getElementById("otherClaimContainer");

    claimDropdown.innerHTML = '<option value="">-- Select Claim --</option>';
    otherClaimContainer.style.display = "none";

    if (tier && coverageData[tier]) {
        const deductibleAmounts = { basic: 5000, standard: 3000, premium: 1000 };
        deductibleEl.textContent = "TK" + deductibleAmounts[tier];

        coverageData[tier].forEach(claim => {
            const option = document.createElement("option");
            option.value = claim;
            option.textContent = claim;
            claimDropdown.appendChild(option);
        });

        const otherOption = document.createElement("option");
        otherOption.value = "other";
        otherOption.textContent = "Other";
        claimDropdown.appendChild(otherOption);
    } else {
        deductibleEl.textContent = "TK0";
    }
}

function checkOtherClaim() {
    const claimDropdown = document.getElementById("claimDropdown");
    const otherClaimContainer = document.getElementById("otherClaimContainer");
    if (claimDropdown.value === "other") {
        otherClaimContainer.style.display = "block";
    } else {
        otherClaimContainer.style.display = "none";
    }
}

function submitInsurance() {
    const tier = document.getElementById("coverage").value;
    const claimDropdown = document.getElementById("claimDropdown");
    const selectedClaim = claimDropdown.value;

    if (tier === "") {
        alert("Please select a coverage tier.");
        return false;
    }
    if (selectedClaim === "") {
        alert("Please select a claim example.");
        return false;
    }
    if (selectedClaim === "other") {
        const otherText = document.getElementById("otherClaim").value.trim();
        if (otherText === "") {
            alert("Please describe your 'Other' claim.");
            return false;
        }
        const hiddenOther = document.createElement("input");
        hiddenOther.type = "hidden";
        hiddenOther.name = "claim";
        hiddenOther.value = otherText;
        document.getElementById("insuranceForm").appendChild(hiddenOther);
    }
    return true;
}
</script>
</body>
</html>
