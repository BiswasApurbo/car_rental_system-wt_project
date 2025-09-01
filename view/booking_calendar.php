<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pickupDate = $_POST['pickup'] ?? '';
    $returnDate = $_POST['return'] ?? '';
    $pickupTime = $_POST['time'] ?? '';
    
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

    if (empty($errors)) {
        $confirmationMessage = "Booking confirmed successfully! Pickup Date: $pickupDate, Return Date: $returnDate, Pickup Time: $pickupTime";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>Booking Calendar</title>
=======
    <title>Lets Book Your Trip</title>
>>>>>>> b81453adcf04999767cdf596d04fb18accb8bc94
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

        <div id="confirmationMessage">
            <?php echo isset($confirmationMessage) ? $confirmationMessage : ''; ?>
        </div>
    </form>

    <script>
        function showRateCalendar() {
            const pickupDate = document.getElementById('pickup').value;
            const returnDate = document.getElementById('return').value;
            let isValid = true;
            document.getElementById('pickupError').innerText = '';
            document.getElementById('returnError').innerText = '';
            if (!pickupDate) {
                document.getElementById('pickupError').innerText = 'Pickup Date is required.';
                isValid = false;
            }

            if (!returnDate) {
                document.getElementById('returnError').innerText = 'Return Date is required.';
                isValid = false;
            }
            if (isValid) {
                document.getElementById('pickupError').innerText = 'Dynamic Pricing Notice:\n- Weekends have a 15% surcharge.\n- Longer rentals get discounts.';
            }
        }
        function showBookingSummary() {
            const pickup = document.getElementById('pickup').value;
            const ret = document.getElementById('return').value;
            const time = document.getElementById('time').value;
            let isValid = true;

            document.getElementById('pickupError').innerText = '';
            document.getElementById('returnError').innerText = '';
            document.getElementById('timeError').innerText = '';

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
            if (isValid) {
                document.getElementById('showPickup').innerText = pickup;
                document.getElementById('showReturn').innerText = ret;
                document.getElementById('showTime').innerText = time;

                document.getElementById('bookingSummary').style.display = 'block';
            }
        }
        function confirmBooking() {
            const pickup = document.getElementById('pickup').value;
            const ret = document.getElementById('return').value;
            const time = document.getElementById('time').value;
            let isValid = true;

            document.getElementById('pickupError').innerText = '';
            document.getElementById('returnError').innerText = '';
            document.getElementById('timeError').innerText = '';

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
            if (isValid) {
               
                document.getElementById('confirmationMessage').innerText = '';
                document.getElementById('confirmationMessage').innerText = "Booking confirmed successfully! Thank you for choosing our service.";
            }
        }
    </script>
</body>
</html>
