<?php
session_start();
require_once '../model/PickupModel.php';

$branches = getBranches();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pickup Locations</title>
<link rel="stylesheet" href="../asset/designs.css">
<style>
    .message { font-size:14px; margin:4px 0; }
    .green { color:green; }
    .red { color:red; }
    .button-container { display:flex; gap:10px; margin-top:20px; }
    #branchDetails { margin-top:10px; }
    #result { margin-top:20px; padding:15px; border:1px solid #ddd; border-radius:6px; }
</style>
</head>
<body>
<h1>Pickup Locations</h1>

<div class="container">
<form id="pickupForm" method="POST">
    <label>Search by Airport or City:</label>
    <input type="text" id="branchSearch" placeholder="Enter airport or city" oninput="filterBranches()" autocomplete="off"/>
    <p style="font-size:12px; color:#555; margin-top:0;">Examples: Dhaka, Rangpur, Chittagong, Cox's Bazar, Sylhet, Khulna, Naogaon, Barishal</p>

    <label>Available Branches:</label>
    <select name="branch" id="branchDropdown" onchange="updateBranchDetails()">
        <option value="">-- Select Branch --</option>
    </select>
    <p id="branchError" class="red message"></p>

    <div id="branchDetails" style="display:none;">
        <p><strong>Hours:</strong> <span id="branchHours"></span></p>
        <p><strong>After-Hours Procedure:</strong> <span id="afterHours"></span></p>
        <p><strong>Amenities:</strong> <span id="amenities"></span></p>
    </div>

    <input type="submit" value="Confirm Pickup Location"/>
    <div class="button-container">
        <input type="button" value="Back to services" onclick="window.location.href='customer_services.php'">
        <input type="button" value="Back to Profile" onclick="window.location.href='profile.php'">
    </div>
    <p id="result"></p>
</form>
</div>

<script>
const branches = <?= json_encode($branches) ?>;

function populateBranches(list) {
    const dropdown = document.getElementById("branchDropdown");
    dropdown.innerHTML = '<option value="">-- Select Branch --</option>';
    list.forEach(branch => {
        const option = document.createElement("option");
        option.value = branch.id;
        option.textContent = branch.branch_name + " (" + branch.city + ")";
        dropdown.appendChild(option);
    });
}

function filterBranches() {
    const search = document.getElementById("branchSearch").value.toLowerCase();
    const filtered = branches.filter(b =>
        b.branch_name.toLowerCase().includes(search) ||
        b.city.toLowerCase().includes(search)
    );
    populateBranches(filtered);
    document.getElementById("branchDetails").style.display = "none";
    document.getElementById("branchError").textContent = "";
}

function updateBranchDetails() {
    const selectedId = document.getElementById("branchDropdown").value;
    const branch = branches.find(b => b.id == selectedId);
    if (branch) {
        document.getElementById("branchHours").textContent = branch.hours;
        document.getElementById("afterHours").textContent = branch.after_hours;
        document.getElementById("amenities").textContent = branch.amenities;
        document.getElementById("branchDetails").style.display = "block";
        document.getElementById("branchError").textContent = "";
    } else {
        document.getElementById("branchDetails").style.display = "none";
    }
}


populateBranches(branches);

document.getElementById("pickupForm").addEventListener("submit", function(e){
    e.preventDefault();
    const branchId = document.getElementById("branchDropdown").value;
    const resultDiv = document.getElementById("result");
    const branchError = document.getElementById("branchError");

    branchError.textContent = '';
    resultDiv.textContent = '';

    if (!branchId) {
        branchError.textContent = "Please select a branch from the list.";
        return;
    }

    const formData = new FormData();
    formData.append("branch", branchId);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "../controller/Pickup_handler.php", true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.success) {
                    const branch = res.data;
                    resultDiv.innerHTML = `
                        <h2 class="green">Pickup Location Confirmed!</h2>
                        <p><strong>Branch:</strong> ${branch.branch_name}</p>
                        <p><strong>City:</strong> ${branch.city}</p>
                        <p><strong>Hours:</strong> ${branch.hours}</p>
                        <p><strong>After Hours:</strong> ${branch.after_hours}</p>
                        <p><strong>Amenities:</strong> ${branch.amenities}</p>
                    `;
                    document.getElementById("pickupForm").reset();
                    document.getElementById("branchDetails").style.display = "none";
                } else {
                    branchError.textContent = res.message;
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
