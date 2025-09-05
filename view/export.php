<?php
session_start();
require_once('../model/userModel.php');
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    if (isset($_COOKIE['status']) && (string)$_COOKIE['status'] === '1') {
        $_SESSION['status'] = true;
        if (!isset($_SESSION['username']) && isset($_COOKIE['remember_user'])) {
            $_SESSION['username'] = $_COOKIE['remember_user'];
        }
        if (!isset($_SESSION['role']) && isset($_COOKIE['remember_role'])) {
            $c = strtolower(trim((string)$_COOKIE['remember_role']));
            $_SESSION['role'] = ($c === 'admin') ? 'Admin' : 'User';
        }
    } else {
        header('location: ../view/login.php?error=badrequest');
        exit;
    }
}
if (strtolower($_SESSION['role']) !== 'admin') {
    header('location: ../view/login.php?error=badrequest');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Data Export</title>
  <link rel="stylesheet" href="export.css">
</head>
<body>

  <div class="export-container">
    <h2>Export Data</h2>

    <form>
      <label for="dateFrom">From:</label>
      <input type="date" id="dateFrom" name="dateFrom" required>

      <label for="dateTo">To:</label>
      <input type="date" id="dateTo" name="dateTo" required>

      <label for="format">Select Format:</label>
      <select id="format" name="format" required>
        <option value="pdf">PDF</option>
        <option value="csv">CSV</option>
      </select>

      <label for="schedule">Schedule Export:</label>
      <select id="schedule" name="schedule">
        <option value="none">None</option>
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
      </select>

      <input type="submit" value="Download">
    </form>
  </div>

</body>
</html>
