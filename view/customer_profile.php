<?php
$name = $licenseNo = $seat = $mirror = "";
$nameError = $licenseError = $seatError = $mirrorError = "";
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $licenseNo = $_POST['licenseNo'] ?? '';
    $seat = $_POST['seat'] ?? '';
    $mirror = $_POST['mirror'] ?? '';

    $isValid = true;
    
    if (empty($name)) {
        $nameError = "Please fill up the name.";
        $isValid = false;
    }
    if (empty($licenseNo)) {
        $licenseError = "Please enter license number.";
        $isValid = false;
    }
    if (empty($seat)) {
        $seatError = "Please select seat preference.";
        $isValid = false;
    }
    if (empty($mirror)) {
        $mirrorError = "Please select mirror preference.";
        $isValid = false;
    }

    if ($isValid) {
        $successMessage = "Preferences saved successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profiles</title>
    <link rel="stylesheet" href="../asset/ad.css">
</head>
<body>
    <h2>Customer Profiles</h2>

    <form method="POST" onsubmit="return false;">
        <fieldset>
            <legend>Driver License Scanner</legend>

            <label for="license">Upload License:</label>
            <input type="file" id="license" name="license" accept=".jpg,.png,.pdf"><br><br>

            <input type="button" value="Scan & Autofill" onclick="scanLicense()"><br><br>

            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>"><br>
            <span class="error-message" id="nameError"><?php echo $nameError; ?></span><br>

            <label for="licenseNo">License No:</label>
            <input type="text" id="licenseNo" name="licenseNo" value="<?php echo htmlspecialchars($licenseNo); ?>"><br>
            <span class="error-message" id="licenseError"><?php echo $licenseError; ?></span><br>
        </fieldset>

        <fieldset>
            <legend>Preference Center</legend>

            <label for="seat">Seat Position:</label>
            <select id="seat" name="seat">
                <option value="">--Select--</option>
                <option value="Front-Left" <?php echo ($seat == 'Front-Left') ? 'selected' : ''; ?>>Front-Left</option>
                <option value="Front-Right" <?php echo ($seat == 'Front-Right') ? 'selected' : ''; ?>>Front-Right</option>
                <option value="Back-Left" <?php echo ($seat == 'Back-Left') ? 'selected' : ''; ?>>Back-Left</option>
                <option value="Back-Right" <?php echo ($seat == 'Back-Right') ? 'selected' : ''; ?>>Back-Right</option>
            </select><br>
            <span class="error-message" id="seatError"><?php echo $seatError; ?></span><br>

            <label for="mirror">Mirror Position:</label>
            <select id="mirror" name="mirror">
                <option value="">--Select--</option>
                <option value="Standard" <?php echo ($mirror == 'Standard') ? 'selected' : ''; ?>>Standard</option>
                <option value="Wide Angle" <?php echo ($mirror == 'Wide Angle') ? 'selected' : ''; ?>>Wide Angle</option>
                <option value="Blind Spot Adjusted" <?php echo ($mirror == 'Blind Spot Adjusted') ? 'selected' : ''; ?>>Blind Spot Adjusted</option>
            </select><br>
            <span class="error-message" id="mirrorError"><?php echo $mirrorError; ?></span><br><br>

            <input type="button" value="Save Preferences" onclick="savePreferences()">
        </fieldset>

        <fieldset>
            <legend>Rental History</legend>
            <ul>
                <li>Honda City - Jan 2024 (Receipt #12345)</li>
                <li>Toyota Fortuner - May 2024 (Receipt #67890)</li>
                <li>Hyundai H1 - July 2024 (Receipt #24680)</li>
            </ul>
        </fieldset>

        <fieldset>
            <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.html';">
        </fieldset>
    </form>


    <div id="savedPreferences" style="display:none;">
        <h3>Saved Preferences:</h3>
        <p id="savedName"></p>
        <p id="savedSeat"></p>
        <p id="savedMirror"></p>
    </div>

    <script>
        function scanLicense() {
            const file = document.getElementById('license').files[0];
            
            clearErrors();

            if (!file) {
                document.getElementById('licenseError').innerText = "Please upload a license file.";
                return;
            }

            document.getElementById('name').value = "John Doe";
            document.getElementById('licenseNo').value = "DL-0987654321";
            document.getElementById('license').value = ''; 
            document.getElementById('licenseError').innerText = '';
        }

        function savePreferences() {
            const name = document.getElementById('name').value.trim();
            const licenseNo = document.getElementById('licenseNo').value.trim();
            const seat = document.getElementById('seat').value;
            const mirror = document.getElementById('mirror').value;

            let isValid = true;
            clearErrors();

            if (name === "") {
                document.getElementById('nameError').innerText = "Please fill up the name.";
                isValid = false;
            }
            if (licenseNo === "") {
                document.getElementById('licenseError').innerText = "Please enter license number.";
                isValid = false;
            }
            if (seat === "") {
                document.getElementById('seatError').innerText = "Please select seat preference.";
                isValid = false;
            }
            if (mirror === "") {
                document.getElementById('mirrorError').innerText = "Please select mirror preference.";
                isValid = false;
            }

            if (isValid) {
                document.getElementById('savedName').innerText = "Full Name: " + name;
                document.getElementById('savedSeat').innerText = "Seat Position: " + seat;
                document.getElementById('savedMirror').innerText = "Mirror Position: " + mirror;

                document.getElementById('savedPreferences').style.display = 'block';
            }
        }

        function clearErrors() {
            document.getElementById('nameError').innerText = "";
            document.getElementById('licenseError').innerText = "";
            document.getElementById('seatError').innerText = "";
            document.getElementById('mirrorError').innerText = "";
        }
    </script>
</body>
</html>
