<?php
 header('location: ../asset/export.css');
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
