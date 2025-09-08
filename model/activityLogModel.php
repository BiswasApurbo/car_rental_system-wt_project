<?php

// Assuming you have a database connection function like getConnection()
function getActivityLog($fromDate, $toDate, $username) {
    // Get database connection
    $conn = getConnection();

    // Prepare the SQL query
    $query = "SELECT * FROM activity_logs WHERE user = ? AND date BETWEEN ? AND ? ORDER BY date DESC";
    
    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Bind parameters
    $stmt->bind_param("sss", $username, $fromDate, $toDate);

    // Execute the query
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Fetch the data into an array
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    // Return the logs
    return $logs;
}
?>
