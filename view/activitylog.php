<?php
session_start();
require_once('../model/userModel.php');
require_once('../model/activityLogModel.php'); 

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    header('location: ../view/login.php?error=not_logged_in');
    exit;
}

// Validate dates (ensure dateFrom is not greater than dateTo)
$fromDate = isset($_POST['dateFrom']) ? $_POST['dateFrom'] : '2025-01-01'; 
$toDate = isset($_POST['dateTo']) ? $_POST['dateTo'] : date('Y-m-d'); 
$username = isset($_POST['user']) ? $_POST['user'] : ''; 

if ($fromDate > $toDate) {
    $errorMessage = "The 'From Date' cannot be later than the 'To Date'. Please correct it.";
    // Optionally, handle this error with a session or display message.
}

$logs = getActivityLog($fromDate, $toDate, $username);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Activity Logs</title>
  <link rel="stylesheet" type="text/css" href="../asset/activitylog.css">
  <script>
    // JavaScript Validation for the Date Fields
    function validateForm() {
      const fromDate = document.getElementById("dateFrom").value;
      const toDate = document.getElementById("dateTo").value;

      // Check if 'from' date is earlier than 'to' date
      if (new Date(fromDate) > new Date(toDate)) {
        alert("The 'From Date' cannot be later than the 'To Date'. Please correct it.");
        return false;
      }

      return true;
    }
  </script>
</head>
<body>

  <div class="log-container">
    <h2>Activity Logs</h2>

    <div class="filters">
      <form method="POST" onsubmit="return validateForm()">
        <label for="dateFrom">Date From:</label>
        <input type="date" id="dateFrom" name="dateFrom" value="<?php echo htmlspecialchars($fromDate); ?>">

        <label for="dateTo">Date To:</label>
        <input type="date" id="dateTo" name="dateTo" value="<?php echo htmlspecialchars($toDate); ?>">

        <label for="user">User:</label>
        <input type="text" id="user" name="user" placeholder="Enter username" value="<?php echo htmlspecialchars($username); ?>">

        <button class="btn" type="submit">Filter</button>
      </form>
    </div>

    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>User</th>
          <th>Action</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
          <tr>
            <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($log['date']))); ?></td>
            <td><?php echo htmlspecialchars($log['user']); ?></td>
            <td><?php echo htmlspecialchars($log['action']); ?></td>
            <td><?php echo htmlspecialchars($log['details']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</body>
</html>
