<?php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    if (isset($_COOKIE['status']) && $_COOKIE['status'] === '1') {
        $_SESSION['status'] = true;
        if (!isset($_SESSION['username']) && isset($_COOKIE['remember_user'])) {
            $_SESSION['username'] = $_COOKIE['remember_user'];
        }
    } else {
        header('location: ../view/login.php?error=badrequest');
        exit;
    }
}
?>
<html>
<head>
    <title>Customer Profiles</title>
    <link rel="stylesheet" href="../asset/customer_profiles.css">
</head>
<body>
    <h1>Customer Profiles</h1>

    <form onsubmit="return false;">
        <fieldset>
            <legend>Driver License Scanner</legend>

            <label for="license">Upload License:</label>
            <input type="file" id="license" accept=".jpg,.png,.pdf"><br><br>

            <input type="button" value="Scan & Autofill" onclick="scanLicense()"><br><br>

            <label for="name">Full Name:</label>
            <input type="text" id="name"><br>
            <span class="error" id="nameError"></span><br>

            <label for="licenseNo">License No:</label>
            <input type="text" id="licenseNo"><br>
            <span class="error" id="licenseError"></span><br>
        </fieldset>

        <fieldset>
            <legend>Preference Center</legend>

            <label for="seat">Seat Position:</label>
            <select id="seat">
                <option value="">--Select--</option>
                <option>Front-Left</option>
                <option>Front-Right</option>
                <option>Back-Left</option>
                <option>Back-Right</option>
            </select><br>
            <span class="error" id="seatError"></span><br>

            <label for="mirror">Mirror Position:</label>
            <select id="mirror">
                <option value="">--Select--</option>
                <option>Standard</option>
                <option>Wide Angle</option>
                <option>Blind Spot Adjusted</option>
            </select><br>
            <span class="error" id="mirrorError"></span><br><br>

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

    <script>
        function scanLicense() {
            const file = document.getElementById('license').files[0];
            if (!file) {
                alert("Please upload a license file.");
                return;
            }

            document.getElementById('name').value = "John Doe";
            document.getElementById('licenseNo').value = "DL-0987654321";
            clearErrors();
            alert("License scanned and details auto-filled.");
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
                alert("Preferences saved:\nName: " + name + "\nSeat: " + seat + "\nMirror: " + mirror);
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
