<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Customer Services</title>
<link rel="stylesheet" href="../asset/customer_services.css">
<script>
document.addEventListener('DOMContentLoaded', function () {
const buttons = document.querySelectorAll('.service-btn');
buttons.forEach(btn => {
btn.addEventListener('click', function (e) {
btn.disabled = true;
setTimeout(() => btn.disabled = false, 800);
});
});
});
</script>
</head>
<body>
<main class="container">
<h1>Customer Services</h1>
<p class="subtitle">Choose a service:</p>
<div class="grid">
<a class="service-btn" href="DamageReports.php">Car Damage Report</a>
<a class="service-btn" href="PricingCalculator.php">Car Rental Calculator</a>
<a class="service-btn" href="InsuranceOptions.php">Insurance Options</a>
<a class="service-btn" href="PickupLocations.php">Pickup Locations</a>
<a class="service-btn" href="FuelTracking.php">Fuel Track &amp; Receipt</a>
<a class="service-btn" href="MaintenanceRecords.php">Maintenance Records</a>
<a class="service-btn" href="LoyaltyProgram.php">Loyalty Program</a>
<a class="service-btn" href="user_dashboard.php">Back To Dashboard</a>
</div>
<footer class="foot"></footer>
</main>
</body>
</html>