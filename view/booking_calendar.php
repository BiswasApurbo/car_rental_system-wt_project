<?php
// PHP for storing or displaying booking information if submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data (Pickup Date, Return Date, Pickup Time)
    $pickupDate = $_POST['pickup'] ?? '';
    $returnDate = $_POST['return'] ?? '';
    $pickupTime = $_POST['time'] ?? '';
    
    // Check if data is valid (server-side validation)
    $errors = [];
    if (empty($pickupDate)) {
        $errors[] = 'Pickup Date is required.';
    }
    if (empty($returnDate)) {
        $errors[] = 'Return Date is required.';
    }
    if (empty($pickupTime)) {
        $errors[] = 'Pickup Time is required.';
    }

    // If no errors, we can simulate booking confirmation (store to DB or send email)
    if (empty($errors)) {
        // Simulate saving the booking (you can store it in a database or send a confirmation email)
        $confirmationMessage = "Booking confirmed successfully! Pickup Date: $pickupDate, Return Date: $returnDate, Pickup Time: $pickupTime";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lets Book Your Trip</title>
    <link rel="stylesheet" href="../asset/ad.css">
</head>
<body>
    <h2>Booking Calendar</h2>
    <form method="POST" action="">
        <fieldset>
            <legend>Date & Time Selection</legend>

            <label for="pickup">Pickup Date:</label>
            <input type="date" id="pickup" name="pickup" value="<?php echo isset($pickupDate) ? $pickupDate : ''; ?>"><br>
            <div id="pickupError" class="error-message">
                <?php echo isset($errors) && in_array('Pickup Date is required.', $errors) ? 'Pickup Date is required.' : ''; ?>
            </div>

            <label for="return">Return Date:</label>
            <input type="date" id="return" name="return" value="<?php echo isset($returnDate) ? $returnDate : ''; ?>"><br>
            <div id="returnError" class="error-message">
                <?php echo isset($errors) && in_array('Return Date is required.', $errors) ? 'Return Date is required.' : ''; ?>
            </div>

            <label for="time">Pickup Time:</label>
            <input type="time" id="time" name="time" placeholder="10:00 AM" value="<?php echo isset($pickupTime) ? $pickupTime : ''; ?>"><br>
            <div id="timeError" class="error-message">
                <?php echo isset($errors) && in_array('Pickup Time is required.', $errors) ? 'Pickup Time is required.' : ''; ?>
            </div>

            <input type="button" value="Check Rates" onclick="showRateCalendar()">
        </fieldset>

        <fieldset>
            <legend>Booking Summary</legend>
            <input type="button" value="Reserve Now" onclick="showBookingSummary()">
            <input type="button" value="1-Click Confirm" onclick="confirmBooking()">
        </fieldset>

        <div id="bookingSummary" style="display: none;">
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

        <!-- Display confirmation message here -->
        <div id="confirmationMessage">
            <?php echo isset($confirmationMessage) ? $confirmationMessage : ''; ?>
        </div>
    </form>

    <script>
        // Show rate calendar with validation
        function showRateCalendar() {
            const pickupDate = document.getElementById('pickup').value;
            const returnDate = document.getElementById('return').value;
            let isValid = true;

            // Clear previous error messages
            document.getElementById('pickupError').innerText = '';
            document.getElementById('returnError').innerText = '';

            // Validation for pickup and return dates
            if (!pickupDate) {
                document.getElementById('pickupError').innerText = 'Pickup Date is required.';
                isValid = false;
            }

            if (!returnDate) {
                document.getElementById('returnError').innerText = 'Return Date is required.';
                isValid = false;
            }

            // If validation passes, show rate calendar
            if (isValid) {
                // Add dynamic pricing logic if needed
                document.getElementById('pickupError').innerText = 'Dynamic Pricing Notice:\n- Weekends have a 15% surcharge.\n- Longer rentals get discounts.';
            }
        }

        // Show booking summary with validation
        function showBookingSummary() {
            const pickup = document.getElementById('pickup').value;
            const ret = document.getElementById('return').value;
            const time = document.getElementById('time').value;
            let isValid = true;

            // Clear previous error messages
            document.getElementById('pickupError').innerText = '';
            document.getElementById('returnError').innerText = '';
            document.getElementById('timeError').innerText = '';

            // Validation for pickup, return date, and time
            if (!pickup) {
                document.getElementById('pickupError').innerText = 'Pickup Date is required.';
                isValid = false;
            }

            if (!ret) {
                document.getElementById('returnError').innerText = 'Return Date is required.';
                isValid = false;
            }

            if (!time) {
                document.getElementById('timeError').innerText = 'Pickup Time is required.';
                isValid = false;
            }

            // If validation passes, show the booking summary
            if (isValid) {
                document.getElementById('showPickup').innerText = pickup;
                document.getElementById('showReturn').innerText = ret;
                document.getElementById('showTime').innerText = time;

                document.getElementById('bookingSummary').style.display = 'block';
            }
        }

        // Confirm booking with validation
        function confirmBooking() {
            const pickup = document.getElementById('pickup').value;
            const ret = document.getElementById('return').value;
            const time = document.getElementById('time').value;
            let isValid = true;

            // Clear previous error messages
            document.getElementById('pickupError').innerText = '';
            document.getElementById('returnError').innerText = '';
            document.getElementById('timeError').innerText = '';

            // Validation for pickup, return date, and time
            if (!pickup) {
                document.getElementById('pickupError').innerText = 'Pickup Date is required.';
                isValid = false;
            }

            if (!ret) {
                document.getElementById('returnError').innerText = 'Return Date is required.';
                isValid = false;
            }

            if (!time) {
                document.getElementById('timeError').innerText = 'Pickup Time is required.';
                isValid = false;
            }

            // If validation passes, show confirmation on the page
            if (isValid) {
                // Remove any previous confirmation messages
                document.getElementById('confirmationMessage').innerText = '';

                // Show the confirmation message
                document.getElementById('confirmationMessage').innerText = "Booking confirmed successfully! Thank you for choosing our service.";
            }
        }
    </script>
</body>
</html>
