<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branch = $_POST['branch'] ?? '';

    if (empty($branch)) {
        die("Please select a branch.");
    }

    $_SESSION['pickup'] = [
        'branch' => $branch,
        'time' => date('Y-m-d H:i:s')
    ];

    echo "<h2 style='color:green;'>Pickup Location Confirmed!</h2>";
    echo "<p>Branch: ".htmlspecialchars($branch)."</p>";

    echo '<br><input type="button" value="Back to services" onclick="window.location.href=\'customer_services.php\'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">';
    echo ' <input type="button" value="Back to Profile" onclick="window.location.href=\'profile.php\'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">';

    exit;
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
<form id="pickupForm" action="PickupLocations.php" method="POST" onsubmit="return submitPickup()">
    <label>Search by Airport or City:</label>
    <input type="text" id="branchSearch" placeholder="Enter airport or city" oninput="filterBranches()" autocomplete="off"/>
    <p style="font-size:12px; color:#555; margin-top:0;">Examples: Dhaka, Rangpur, Chittagong, Cox's Bazar, Sylhet, Khulna, Naogaon, Barishal</p>

    <h3>Available Branches:</h3>
    <select id="branchDropdown" name="branch" onchange="updateBranchDetails()">
        <option value="">-- Select Branch --</option>
    </select>
    <p id="branchError" style="color:red; font-size:12px;"></p>

    <div id="branchDetails" style="margin-top:10px; display:none;">
        <p><strong>Hours:</strong> <span id="branchHours"></span></p>
        <p><strong>After-Hours Procedure:</strong> <span id="afterHours"></span></p>
        <p><strong>Amenities:</strong> <span id="amenities"></span></p>
    </div>

    <input type="submit" value="Confirm Pickup Location"/>
    <br> <br>
    <input type="button" value="Back to services" onclick="window.location.href='customer_services.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
    <input type="button" value="Back to Profile" onclick="window.location.href='profile.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
    
</form>
</div>

<script>
const branches = [
    {name: "Dhaka Airport Branch", city: "Dhaka", hours: "08:00 - 22:00", afterHours: "Call 1234 for after-hours pickup", amenities: "Car Wash, Fuel, Waiting Lounge"},
    {name: "Dhaka Gulshan Branch", city: "Dhaka", hours: "09:00 - 21:00", afterHours: "Call 5678 for after-hours pickup", amenities: "Fuel, Waiting Lounge"},
    {name: "Dhaka Banani Branch", city: "Dhaka", hours: "08:30 - 20:30", afterHours: "Secure key drop", amenities: "Car Wash, Fuel"},
    {name: "Rangpur City Branch", city: "Rangpur", hours: "08:00 - 19:00", afterHours: "Call 1122 for after-hours pickup", amenities: "Fuel, Waiting Lounge"},
    {name: "Rangpur Airport Branch", city: "Rangpur", hours: "08:30 - 20:00", afterHours: "Leave keys in dropbox", amenities: "Fuel"},
    {name: "Chittagong City Branch", city: "Chittagong", hours: "07:30 - 20:30", afterHours: "Call 5678 for assistance", amenities: "Car Wash, Fuel"},
    {name: "Chittagong Airport Branch", city: "Chittagong", hours: "09:00 - 21:00", afterHours: "Secure key drop", amenities: "Fuel, Waiting Lounge"},
    {name: "Cox's Bazar Airport Branch", city: "Cox's Bazar", hours: "09:00 - 21:00", afterHours: "Leave keys in secure dropbox", amenities: "Fuel, Waiting Lounge"},
    {name: "Cox's Bazar City Branch", city: "Cox's Bazar", hours: "08:00 - 20:00", afterHours: "Call 3344 for after-hours", amenities: "Car Wash, Fuel"},
    {name: "Sylhet City Branch", city: "Sylhet", hours: "08:00 - 20:00", afterHours: "Call 7788 for after-hours", amenities: "Fuel, Waiting Lounge"},
    {name: "Sylhet Airport Branch", city: "Sylhet", hours: "09:00 - 21:00", afterHours: "Secure key drop", amenities: "Car Wash, Fuel"},
    {name: "Khulna City Branch", city: "Khulna", hours: "08:00 - 19:00", afterHours: "Call 8899 for after-hours", amenities: "Fuel, Waiting Lounge"},
    {name: "Khulna Airport Branch", city: "Khulna", hours: "09:00 - 21:00", afterHours: "Secure key drop", amenities: "Car Wash, Fuel"},
    {name: "Naogaon City Branch", city: "Naogaon", hours: "08:00 - 18:00", afterHours: "Call 5566 for after-hours", amenities: "Fuel, Waiting Lounge"},
    {name: "Barishal City Branch", city: "Barishal", hours: "08:00 - 19:00", afterHours: "Call 6677 for after-hours", amenities: "Fuel, Waiting Lounge"}
];

function populateBranches(list) {
    const dropdown = document.getElementById("branchDropdown");
    dropdown.innerHTML = '<option value="">-- Select Branch --</option>';
    list.forEach(branch => {
        const option = document.createElement("option");
        option.value = branch.name;
        option.textContent = branch.name;
        dropdown.appendChild(option);
    });
}

function filterBranches() {
    const search = document.getElementById("branchSearch").value.toLowerCase();
    const filtered = branches.filter(b => b.name.toLowerCase().includes(search) || b.city.toLowerCase().includes(search));
    populateBranches(filtered);
    document.getElementById("branchDetails").style.display = "none";
}

function updateBranchDetails() {
    const selected = document.getElementById("branchDropdown").value;
    const branch = branches.find(b => b.name === selected);
    if (branch) {
        document.getElementById("branchHours").textContent = branch.hours;
        document.getElementById("afterHours").textContent = branch.afterHours;
        document.getElementById("amenities").textContent = branch.amenities;
        document.getElementById("branchDetails").style.display = "block";
        document.getElementById("branchError").textContent = "";
    } else {
        document.getElementById("branchDetails").style.display = "none";
    }
}

function submitPickup() {
    const branch = document.getElementById("branchDropdown").value;
    if (branch === "") {
        document.getElementById("branchError").textContent = "Please select a branch from the list.";
        return false;
    }
    return true;
}

populateBranches(branches);
</script>
</body>
</html>
