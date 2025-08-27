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
<!DOCTYPE html>
<html>
<head>
    <title>Booking Calendar</title>
    <link rel="stylesheet" href="../asset/auth.css">
</head>
<body>
    <h1>Booking Calendar</h1>
    <form onsubmit="return false;">
        <fieldset>
            <legend>Date & Time Selection</legend>

            <label for="pickup">Pickup Date:</label>
            <input type="date" id="pickup"><br><br>

            <label for="return">Return Date:</label>
            <input type="date" id="return"><br><br>

            <label for="time">Pickup Time:</label>
            <input type="text" id="time" placeholder="10:00 AM"><br><br>

            <input type="button" value="Check Rates" onclick="showRateCalendar()">
        </fieldset>

        <fieldset>
            <legend>Booking Summary</legend>
            <input type="button" value="Reserve Now" onclick="showBookingSummary()">
            <input type="button" value="1-Click Confirm" onclick="confirmBooking()">
        </fieldset>

        <div id="bookingSummary" style="display:none;">
            <h3>Booking Summary:</h3>
            <ul>
                <li>Pickup Date: <span id="showPickup"></span></li>
                <li>Return Date: <span id="showReturn"></span></li>
                <li>Pickup Time: <span id="showTime"></span></li>
                <li>Price: <span id="showPrice">3500 tk</span></li>
            </ul>
        </div>

        <fieldset>
            <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'">
        </fieldset>
    </form>

    <script>
        function showRateCalendar() {
            const pickupDate = document.getElementById('pickup').value;
            const returnDate = document.getElementById('return').value;

            if (!pickupDate || !returnDate) {
                alert("Please select both pickup and return dates to check rates.");
                return;
            }

            alert("Dynamic Pricing Notice:\n- Weekends have a 15% surcharge.\n- Longer rentals get discounts.");
        }

        function showBookingSummary() {
            const pickup = document.getElementById('pickup').value;
            const ret = document.getElementById('return').value;
            const time = document.getElementById('time').value;

            if (!pickup || !ret || !time) {
                alert("Please fill out all fields before reserving.");
                return;
            }

            document.getElementById('showPickup').innerText = pickup;
            document.getElementById('showReturn').innerText = ret;
            document.getElementById('showTime').innerText = time;

            document.getElementById('bookingSummary').style.display = 'block';
        }

        function confirmBooking() {
            const pickup = document.getElementById('pickup').value;
            const ret = document.getElementById('return').value;
            const time = document.getElementById('time').value;

            if (!pickup || !ret || !time) {
                alert("Fill in all fields before confirming booking.");
                return;
            }

            alert("Booking confirmed successfully!\nThank you for choosing our service.");
        }
    </script>
</body>
</html>
