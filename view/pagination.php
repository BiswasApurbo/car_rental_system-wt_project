<?php
// Initialize variables for page number and items per page
$pageNumber = "";
$itemsPerPage = "";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pageNumber = $_POST['pageNumber'] ?? '';
    $itemsPerPage = $_POST['itemsPerPage'] ?? '';

    // Handle validation errors (not using PHP for real-time validation, only for form submission)
    $errors = [];
    if (empty($pageNumber)) {
        $errors[] = 'Please select a page number.';
    }
    if (empty($itemsPerPage)) {
        $errors[] = 'Please select the number of items per page.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagination</title>
    <link rel="stylesheet" type="text/css" href="ad.css">
</head>
<body>
    <h1>Pagination</h1>

    <form method="POST" onsubmit="return false;">
        <fieldset>
            <legend>Pagination Controls</legend>

            <label for="pageNumber">Page:</label>
            <select id="pageNumber" name="pageNumber">
                <option value="">--Select--</option>
                <option value="1" <?php echo ($pageNumber == '1') ? 'selected' : ''; ?>>1</option>
                <option value="2" <?php echo ($pageNumber == '2') ? 'selected' : ''; ?>>2</option>
                <option value="3" <?php echo ($pageNumber == '3') ? 'selected' : ''; ?>>3</option>
            </select>
            <span class="error" id="pageNumberError"><?php echo isset($errors) && in_array('Please select a page number.', $errors) ? 'Please select a page number.' : ''; ?></span><br><br>

            <label for="itemsPerPage">Items per page:</label>
            <select id="itemsPerPage" name="itemsPerPage">
                <option value="">--Select--</option>
                <option value="10" <?php echo ($itemsPerPage == '10') ? 'selected' : ''; ?>>10</option>
                <option value="25" <?php echo ($itemsPerPage == '25') ? 'selected' : ''; ?>>25</option>
                <option value="50" <?php echo ($itemsPerPage == '50') ? 'selected' : ''; ?>>50</option>
            </select>
            <span class="error" id="itemsPerPageError"><?php echo isset($errors) && in_array('Please select the number of items per page.', $errors) ? 'Please select the number of items per page.' : ''; ?></span><br><br>

            <input type="button" value="Go" onclick="checkPagination()">
        </fieldset>
    </form>

    <form>
        <fieldset>
            <input type="button" value="Back to Admin Panel" onclick="window.location.href='admin_panel.html'">
        </fieldset>
    </form>

    <!-- Display success message after validation -->
    <div id="paginationResult" style="display:none; margin-top: 20px;">
        <p id="successMessage" style="color: green;"></p>
    </div>

    <script>
        function checkPagination() {
            const pageNumber = document.getElementById('pageNumber').value;
            const itemsPerPage = document.getElementById('itemsPerPage').value;

            let isValid = true;

            // Clear previous error messages
            clearErrors();

            // Validate Page Number selection
            if (pageNumber === "") {
                document.getElementById('pageNumberError').innerText = "Please select a page number.";
                isValid = false;
            }

            // Validate Items Per Page selection
            if (itemsPerPage === "") {
                document.getElementById('itemsPerPageError').innerText = "Please select the number of items per page.";
                isValid = false;
            }

            if (isValid) {
                // Show the success message with selected values
                document.getElementById('paginationResult').style.display = 'block';
                document.getElementById('successMessage').innerText = "Pagination settings applied: Page " + pageNumber + ", Items per page: " + itemsPerPage;

                // Optionally, submit the form via JavaScript or perform further actions
            }
        }

        function clearErrors() {
            document.getElementById('pageNumberError').innerText = "";
            document.getElementById('itemsPerPageError').innerText = "";
        }
    </script>

</body>
</html>
