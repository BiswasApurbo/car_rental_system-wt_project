<?php
// Simulating vehicle data (would normally come from a database)
$vehicles = [
    ['type' => 'SUV', 'feature' => 'AC', 'price' => '2001-4000', 'img' => 'images/toyota-fortuner.jpg', 'name' => 'Toyota Fortuner'],
    ['type' => 'Sedan', 'feature' => 'GPS', 'price' => '4001-6000', 'img' => 'images/honda-accord.jpg', 'name' => 'Honda Accord'],
    ['type' => 'Van', 'feature' => 'Child Seat', 'price' => '0-2000', 'img' => 'images/toyota-hiace.jpg', 'name' => 'Toyota HiAce'],
    ['type' => 'SUV', 'feature' => 'GPS', 'price' => '2001-4000', 'img' => 'images/toyota-fortuner.jpg', 'name' => 'Toyota Fortuner'],
    ['type' => 'Sedan', 'feature' => 'AC', 'price' => '0-2000', 'img' => 'images/honda-accord.jpg', 'name' => 'Honda Accord']
];
 
// Handling form submissions for filtering
$typeFilter = isset($_POST['type']) ? $_POST['type'] : '';
$featureFilter = isset($_POST['feature']) ? $_POST['feature'] : '';
$priceFilter = isset($_POST['price']) ? $_POST['price'] : '';
 
// Filtering the vehicles based on the selected criteria
$filteredVehicles = $vehicles;
 
if ($typeFilter !== '') {
    $filteredVehicles = array_filter($filteredVehicles, function($vehicle) use ($typeFilter) {
        return $vehicle['type'] === $typeFilter;
    });
}
 
if ($featureFilter !== '') {
    $filteredVehicles = array_filter($filteredVehicles, function($vehicle) use ($featureFilter) {
        return $vehicle['feature'] === $featureFilter;
    });
}
 
if ($priceFilter !== '') {
    $filteredVehicles = array_filter($filteredVehicles, function($vehicle) use ($priceFilter) {
        return $vehicle['price'] === $priceFilter;
    });
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Inventory</title>
    <link rel="stylesheet" href="../asset/ad.css">
</head>
<body>
    <h1>Vehicle Inventory</h1>
 
    <form method="POST" onsubmit="return false;">
        <fieldset>
            <legend>Filter Vehicles</legend>
 
            <label for="type">Type:</label>
            <select id="type" name="type">
                <option value="">--Select--</option>
                <option value="SUV" <?php if ($typeFilter == 'SUV') echo 'selected'; ?>>SUV</option>
                <option value="Sedan" <?php if ($typeFilter == 'Sedan') echo 'selected'; ?>>Sedan</option>
                <option value="Hatchback" <?php if ($typeFilter == 'Hatchback') echo 'selected'; ?>>Hatchback</option>
                <option value="Van" <?php if ($typeFilter == 'Van') echo 'selected'; ?>>Van</option>
            </select><br>
            <div id="typeError" class="error-message"></div>
 
            <label for="feature">Feature:</label>
            <select id="feature" name="feature">
                <option value="">--Select--</option>
                <option value="AC" <?php if ($featureFilter == 'AC') echo 'selected'; ?>>AC</option>
                <option value="GPS" <?php if ($featureFilter == 'GPS') echo 'selected'; ?>>GPS</option>
                <option value="Child Seat" <?php if ($featureFilter == 'Child Seat') echo 'selected'; ?>>Child Seat</option>
            </select><br>
            <div id="featureError" class="error-message"></div>
 
            <label for="price">Price Range:</label>
            <select id="price" name="price">
                <option value="">--Select--</option>
                <option value="0-2000" <?php if ($priceFilter == '0-2000') echo 'selected'; ?>>0-2000</option>
                <option value="2001-4000" <?php if ($priceFilter == '2001-4000') echo 'selected'; ?>>2001-4000</option>
                <option value="4001-6000" <?php if ($priceFilter == '4001-6000') echo 'selected'; ?>>4001-6000</option>
            </select><br>
            <div id="priceError" class="error-message"></div>
 
            <input type="button" value="Apply Filter" onclick="applyFilter()">
        </fieldset>
 
        <fieldset>
            <legend>Fleet Gallery & Tours</legend>
            <input type="button" value="View Gallery" onclick="showGallery()">
            <input type="button" value="360° Tour" onclick="alert('Opening 360° tour...')">
            <input type="button" value="Check Availability" onclick="showAvailability()">
        </fieldset>
 
        <div id="gallery" style="display: none;">
            <h3>Fleet Gallery</h3>
            <div id="vehiclesGallery">
                <!-- Vehicles will be dynamically added here based on filters -->
                <?php
                if (count($filteredVehicles) > 0) {
                    foreach ($filteredVehicles as $vehicle) {
                        echo "<div class='vehicle-item'>";
                        echo "<img src='{$vehicle['img']}' alt='{$vehicle['name']}' width='220' class='vehicle-image'>";
                        echo "<h4>{$vehicle['name']}</h4>";
                        echo "<p>Type: {$vehicle['type']} - Feature: {$vehicle['feature']} - Price: {$vehicle['price']}</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No vehicles found based on the selected filter.</p>";
                }
                ?>
            </div>
        </div>
 
        <div id="vehicleDetails" style="display: none;">
            <h3>Available Now</h3>
            <ul>
                <li>SUV - Toyota Fortuner</li>
                <li>Sedan - Honda Accord</li>
                <li>Van - Toyota HiAce</li>
            </ul>
        </div>
 
        <br>
        <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.html'">
    </form>
 
    <script>
        // Function to apply the filter and display filtered vehicles
        function applyFilter() {
            var type = document.getElementById("type").value;
            var feature = document.getElementById("feature").value;
            var price = document.getElementById("price").value;
 
            // Clear previous error messages
            document.getElementById("typeError").innerHTML = '';
            document.getElementById("featureError").innerHTML = '';
            document.getElementById("priceError").innerHTML = '';
 
            // Validation: If any field is not selected, show an error message below that field
            if (type === "") {
                document.getElementById("typeError").innerHTML = "Please select a vehicle type.";
            }
            if (feature === "") {
                document.getElementById("featureError").innerHTML = "Please select a vehicle feature.";
            }
            if (price === "") {
                document.getElementById("priceError").innerHTML = "Please select a price range.";
            }
 
            // If any validation fails, do not proceed with filtering
            if (type === "" || feature === "" || price === "") {
                return;
            }
 
            // Submit the form to apply the filter
            document.forms[0].submit();
        }
 
        // Function to show the gallery
        function showGallery() {
            document.getElementById("gallery").style.display = "block";
            document.getElementById("vehicleDetails").style.display = "none";
        }
 
        // Function to show availability details
        function showAvailability() {
            document.getElementById("vehicleDetails").style.display = "block";
            document.getElementById("gallery").style.display = "none";
        }
    </script>
</body>
</html>