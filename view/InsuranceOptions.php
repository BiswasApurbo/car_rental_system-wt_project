<?php
session_start();
require_once "../model/InsuranceModel.php";


$settings = getSettings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Insurance Options</title>
<link rel="stylesheet" href="../asset/designs.css">
<style>
    .message { font-size:14px; margin:4px 0; }
    .green { color:green; }
    .red { color:red; }
    .button-container { display:flex; gap:10px; margin-top:20px; }
    #result { margin-top:20px; padding:15px; border:1px solid #ddd; border-radius:6px; }
</style>
</head>
<body>
<h1>Insurance Options</h1>
<div class="container">
<form id="insuranceForm" method="POST">
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
        <p id="claimError" class="message red"></p>
    </div>

    <br><br>
    <input type="submit" value="Confirm Option"/>
    <div class="button-container">
        <input type="button" value="Back to services" onclick="window.location.href='customer_services.php'">
        <input type="button" value="Back to Profile" onclick="window.location.href='profile.php'">
    </div>
    <p id="result"></p>
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
    document.getElementById("otherClaimDiv").style.display = (claim === "other") ? "block" : "none";
}

document.getElementById("insuranceForm").addEventListener("submit", function(e){
    e.preventDefault();

    const tier = document.getElementById("tier").value;
    const claimDropdown = document.getElementById("claimDropdown").value;
    const otherClaim = document.getElementById("otherClaim").value.trim();
    const resultDiv = document.getElementById("result");
    const claimError = document.getElementById("claimError");

    claimError.textContent = '';
    resultDiv.textContent = '';

    if (!tier) {
        resultDiv.innerHTML = "<p class='red'>Please select a coverage tier.</p>";
        return;
    }
    if (!claimDropdown) {
        claimError.textContent = "Please select or enter a claim.";
        return;
    }

    const formData = new FormData();
    formData.append("tier", tier);
    formData.append("claim", claimDropdown);
    formData.append("otherClaim", otherClaim);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "../controller/Insurance_handler.php", true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.success) {
                    resultDiv.innerHTML = `
                        <h2 class="green">Insurance Option Confirmed!</h2>
                        <p><strong>Tier:</strong> ${res.data.tier}</p>
                        <p><strong>Deductible:</strong> TK${res.data.deductible}</p>
                        <p><strong>Claim:</strong> ${res.data.claim}</p>
                    `;
                    document.getElementById("insuranceForm").reset();
                    document.getElementById("claimContainer").style.display = "none";
                    document.getElementById("otherClaimDiv").style.display = "none";
                } else {
                    resultDiv.innerHTML = `<p class='red'><strong>${res.message}</strong></p>`;
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
