<?php
// Sample data that would normally come from a database
$sampleData = [
    ['name' => 'Car 1', 'category' => 'Vehicles', 'status' => 'Active'],
    ['name' => 'User 1', 'category' => 'Users', 'status' => 'Inactive'],
    ['name' => 'Booking 1', 'category' => 'Bookings', 'status' => 'Pending'],
    ['name' => 'Car 2', 'category' => 'Vehicles', 'status' => 'Active'],
    ['name' => 'User 2', 'category' => 'Users', 'status' => 'Active']
];

// Get filter values from the form (if any)
$query = isset($_POST['searchBox']) ? strtolower($_POST['searchBox']) : '';
$category = isset($_POST['categoryFilter']) ? $_POST['categoryFilter'] : 'All';
$status = isset($_POST['statusFilter']) ? $_POST['statusFilter'] : 'All';

// Filter the data based on selected category, status, and search query
$filteredData = array_filter($sampleData, function($item) use ($query, $category, $status) {
    return (
        ($category == 'All' || $item['category'] == $category) &&
        ($status == 'All' || $item['status'] == $status) &&
        (strpos(strtolower($item['name']), $query) !== false)
    );
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search & Filter</title>
    <link rel="stylesheet" type="text/css" href="ad.css">
    <script>
        function liveSearch() {
            var query = document.getElementById('searchBox').value.toLowerCase();
            var category = document.getElementById('categoryFilter').value;
            var status = document.getElementById('statusFilter').value;
            var resultsList = document.getElementById('resultsList');

            // Perform the search with PHP (re-rendering will be done after form submit)
            resultsList.innerHTML = '';

            if (query || category !== 'All' || status !== 'All') {
                <?php
                // Display the filtered data here using PHP
                if (count($filteredData) > 0) {
                    foreach ($filteredData as $item) {
                        echo "<li>{$item['name']} - {$item['category']} - {$item['status']}</li>";
                    }
                } else {
                    echo "<li>No results found</li>";
                }
                ?>
            }
        }

        function applyFilters() {
            liveSearch();
        }

        function clearFilters() {
            // Clear the search box and reset dropdowns to 'All'
            document.getElementById('searchBox').value = '';
            document.getElementById('categoryFilter').value = 'All';
            document.getElementById('statusFilter').value = 'All';
            // Call liveSearch to update results based on cleared filters
            liveSearch();
        }
    </script>
</head>
<body>
    <h1>Search & Filter</h1>
    <form method="POST" action="">
        <fieldset>
            <legend>Search & Filter</legend>
            <label for="searchBox">Search:</label>
            <input type="text" id="searchBox" name="searchBox" placeholder="Type keyword..." value="<?php echo isset($query) ? htmlspecialchars($query) : ''; ?>" onkeyup="liveSearch()">
            
            <label for="categoryFilter">Category:</label>
            <select id="categoryFilter" name="categoryFilter" onchange="applyFilters()">
                <option value="All" <?php echo ($category == 'All') ? 'selected' : ''; ?>>All</option>
                <option value="Vehicles" <?php echo ($category == 'Vehicles') ? 'selected' : ''; ?>>Vehicles</option>
                <option value="Users" <?php echo ($category == 'Users') ? 'selected' : ''; ?>>Users</option>
                <option value="Bookings" <?php echo ($category == 'Bookings') ? 'selected' : ''; ?>>Bookings</option>
            </select>
            
            <label for="statusFilter">Status:</label>
            <select id="statusFilter" name="statusFilter" onchange="applyFilters()">
                <option value="All" <?php echo ($status == 'All') ? 'selected' : ''; ?>>All</option>
                <option value="Active" <?php echo ($status == 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo ($status == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                <option value="Pending" <?php echo ($status == 'Pending') ? 'selected' : ''; ?>>Pending</option>
            </select>
        </fieldset>

        <!-- Clear Filter Button -->
        <input type="button" value="Clear Filters" onclick="clearFilters()">
    </form>

    <h2>Results:</h2>
    <ul id="resultsList">
        <?php
        // Display filtered results if available
        if (count($filteredData) > 0) {
            foreach ($filteredData as $item) {
                echo "<li>{$item['name']} - {$item['category']} - {$item['status']}</li>";
            }
        } else {
            echo "<li>No results found</li>";
        }
        ?>
    </ul>

    <form>
        <fieldset>
            <input type="button" value="Back to Admin Panel" onclick="window.location.href='admin_panel.html'">
        </fieldset>
    </form>
</body>
</html>
