<?php
// Static list of users (simulating data without a database)
$users = [
    ['id' => 1, 'username' => 'admin1', 'email' => 'admin1@example.com', 'role' => 'Admin'],
    ['id' => 2, 'username' => 'editor1', 'email' => 'editor1@example.com', 'role' => 'Editor'],
    ['id' => 3, 'username' => 'user1', 'email' => 'user1@example.com', 'role' => 'User'],
    ['id' => 4, 'username' => 'car1', 'email' => 'car1@example.com', 'role' => 'Vehicles'],
    ['id' => 5, 'username' => 'user2', 'email' => 'user2@example.com', 'role' => 'User']
];

// Handling filter by role
$roleFilter = isset($_POST['roleFilter']) ? $_POST['roleFilter'] : 'All';
$filteredUsers = $users;

if ($roleFilter != 'All') {
    $filteredUsers = array_filter($users, function($user) use ($roleFilter) {
        return $user['role'] === $roleFilter;
    });
}

// Handling bulk delete
$validationMessage = '';
if (isset($_POST['bulkDelete'])) {
    if (!empty($_POST['user_ids'])) {
        $userIdsToDelete = $_POST['user_ids'];
        $filteredUsers = array_filter($filteredUsers, function($user) use ($userIdsToDelete) {
            return !in_array($user['id'], $userIdsToDelete);
        });
        $validationMessage = count($userIdsToDelete) . " users deleted.";
    } else {
        $validationMessage = "Please select at least one user to delete.";
    }
}

// Handling export to CSV
if (isset($_POST['exportCSV'])) {
    // Open output stream for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users.csv"');
    $output = fopen('php://output', 'w');
    
    // Add column headers
    fputcsv($output, ['User ID', 'Username', 'Email', 'Role']);

    // Write filtered users to CSV
    foreach ($filteredUsers as $user) {
        fputcsv($output, [$user['id'], $user['username'], $user['email'], $user['role']]);
    }

    fclose($output);
    exit();
}

// Handling single delete
if (isset($_GET['deleteUserId'])) {
    $userIdToDelete = $_GET['deleteUserId'];
    $filteredUsers = array_filter($filteredUsers, function($user) use ($userIdToDelete) {
        return $user['id'] != $userIdToDelete;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - User Management</title>
    <link rel="stylesheet" type="text/css" href="ad.css">
    <script>
        // Function for Bulk Delete
        function bulkDelete() {
            if (confirm("Are you sure you want to delete the selected users?")) {
                document.getElementById('bulkDeleteForm').submit();
            }
        }

        // Function for Export CSV
        function exportCSV() {
            document.getElementById('exportCSVForm').submit();
        }
    </script>
</head>
<body>
    <h1>Admin Panel - User Management</h1>
    <div class="admin-card">
        <!-- Filter by Role Section -->
        <form id="filterForm" method="POST" action="">
            <fieldset>
                <label for="adminFilter">Filter by role:</label>
                <select id="adminFilter" name="roleFilter" onchange="applyFilter()">
                    <option value="All" <?php if ($roleFilter == 'All') echo 'selected'; ?>>All</option>
                    <option value="Admin" <?php if ($roleFilter == 'Admin') echo 'selected'; ?>>Admin</option>
                    <option value="Editor" <?php if ($roleFilter == 'Editor') echo 'selected'; ?>>Editor</option>
                    <option value="User" <?php if ($roleFilter == 'User') echo 'selected'; ?>>User</option>
                    <option value="Vehicles" <?php if ($roleFilter == 'Vehicles') echo 'selected'; ?>>Vehicles</option>
                </select>
            </fieldset>
        </form>

        <!-- Validation Message Section -->
        <div id="validationMessage" style="margin: 10px; font-weight: bold;">
            <?php 
                if (!empty($validationMessage)) {
                    echo $validationMessage;
                }
            ?>
        </div>

        <!-- User List Section -->
        <form id="bulkDeleteForm" method="POST" action="">
            <fieldset>
                <label>Users List:</label>
                <table>
                    <tr>
                        <th>Select</th>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                    <?php
                    if (count($filteredUsers) > 0) {
                        foreach ($filteredUsers as $user) {
                            echo "<tr>";
                            echo "<td><input type='checkbox' name='user_ids[]' value='{$user['id']}'></td>";
                            echo "<td>{$user['id']}</td>";
                            echo "<td>{$user['username']}</td>";
                            echo "<td>{$user['email']}</td>";
                            echo "<td>{$user['role']}</td>";
                            echo "<td>
                                    <button type='button' onclick=\"window.location.href='admin_panel_user_management.php?deleteUserId={$user['id']}'\">Delete</button>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No users found</td></tr>";
                    }
                    ?>
                </table>
            </fieldset>

            <!-- Bulk Actions Section -->
            <fieldset>
                <input type="button" value="Bulk Delete" onclick="bulkDelete()">
                <input type="button" value="Export CSV" onclick="exportCSV()">
            </fieldset>
        </form>

        <!-- Navigation Back to Dashboard -->
        <fieldset>
            <input type="button" value="Back to Dashboard" onclick="window.location.href='admin_dashboard.html'">
        </fieldset>
    </div>
    
    <!-- Form for Exporting CSV -->
    <form id="exportCSVForm" method="POST" action="" style="display:none;">
        <input type="hidden" name="exportCSV" value="true">
    </form>
</body>
</html>
