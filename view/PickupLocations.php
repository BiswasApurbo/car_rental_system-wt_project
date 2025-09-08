<?php
session_start();
require_once '../model/PickupModel.php';


$branches = getBranches();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branchId = $_POST['branch'] ?? '';
    $userId = $_SESSION['user_id'] ?? 1; 

    if (empty($branchId)) {
        die("Please select a branch.");
    }

    
    $selectedBranch = array_filter($branches, fn($b) => $b['id'] == $branchId);
    $branch = reset($selectedBranch);

    if (!$branch) die("Branch not found.");

   
    if (addPickup($userId, $branch)) {
        echo "<h2 style='color:green;'>Pickup Location Confirmed!</h2>";
        echo "<p>Branch: ".htmlspecialchars($branch['branch_name'])."</p>";
        echo "<p>City: ".htmlspecialchars($branch['city'])."</p>";
        echo "<p>Hours: ".htmlspecialchars($branch['hours'])."</p>";
        echo "<p>After Hours: ".htmlspecialchars($branch['after_hours'])."</p>";
        echo "<p>Amenities: ".htmlspecialchars($branch['amenities'])."</p>";

        echo '<br><input type="button" value="Back to services" onclick="window.location.href=\'customer_services.php\'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">';
        echo ' <input type="button" value="Back to Profile" onclick="window.location.href=\'profile.php\'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">';
        exit;
    } else {
        die("Failed to save pickup location.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pickup Locations</title>
<link rel="stylesheet" href="../asset/designs.css">
</head>
<body>
<h1>Pickup Locations</h1>

<div class="container">
<form method="POST" action="" id="pickupForm">
    <label>Search by Airport or City:</label>
    <input type="text" id="branchSearch" placeholder="Enter airport or city" oninput="filterBranches()" autocomplete="off"/>
    <p style="font-size:12px; color:#555; margin-top:0;">Examples: Dhaka, Rangpur, Chittagong, Cox's Bazar, Sylhet, Khulna, Naogaon, Barishal</p>

    <label>Available Branches:</label>
    <select name="branch" id="branchDropdown" onchange="updateBranchDetails()">
        <option value="">-- Select Branch --</option>
    </select>
    <p id="branchError" style="color:red; font-size:12px;"></p>

    <div id="branchDetails" style="margin-top:10px; display:none;">
        <p><strong>Hours:</strong> <span id="branchHours"></span></p>
        <p><strong>After-Hours Procedure:</strong> <span id="afterHours"></span></p>
        <p><strong>Amenities:</strong> <span id="amenities"></span></p>
    </div>

    <input type="submit" value="Confirm Pickup Location"/>
    <br><br>
    <input type="button" value="Back to services" onclick="window.location.href='customer_services.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
    <input type="button" value="Back to Profile" onclick="window.location.href='profile.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
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

document.getElementById("pickupForm").addEventListener("submit", function(event) {
    const branchDropdown = document.getElementById("branchDropdown");
    if (branchDropdown.value === "") {
        document.getElementById("branchError").textContent = "Please select a branch from the list.";
        event.preventDefault();
    }
});
</script>
</body>
</html>
