<?php
 header('location: ../asset/activitylog.css');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Activity Logs</title>
</head>
<body>

  <div class="log-container">
    <h2>Activity Logs</h2>

    <div class="filters">
      <label for="date">Date:</label>
      <input type="date" id="date">

      <label for="user">User:</label>
      <input type="text" id="user" placeholder="Enter username">

      <label for="action">Action:</label>
      <select id="action">
        <option value="all">All</option>
        <option value="login">Login</option>
        <option value="update">Update</option>
        <option value="delete">Delete</option>
        <option value="export">Export</option>
      </select>

      <button class="btn">Filter</button>
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
        <tr>
          <td>2025-08-25 09:32</td>
          <td>admin</td>
          <td>Login</td>
          <td>Successful login</td>
        </tr>
        <tr>
          <td>2025-08-25 09:45</td>
          <td>rahat</td>
          <td>Update</td>
          <td>Edited profile info</td>
        </tr>
        <tr>
          <td>2025-08-25 10:02</td>
          <td>guest</td>
          <td>Delete</td>
          <td>Removed a comment</td>
        </tr>
      </tbody>
    </table>

    <div class="export">
      <button class="btn">Export CSV</button>
      <button class="btn">Export PDF</button>
    </div>
  </div>

</body>
</html>
