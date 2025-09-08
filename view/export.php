<?php 
session_start();
require_once('../model/userModel.php');
require_once('../model/exportModel.php');

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

$username = $_SESSION['username'] ?? ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dateFrom'], $_POST['dateTo'])) { 
    $from = $_POST['dateFrom']; 
    $to = $_POST['dateTo']; 


    $fromDate = DateTime::createFromFormat('Y-m-d', $from);
    $toDate = DateTime::createFromFormat('Y-m-d', $to);


    if ($fromDate > $toDate) {
        echo "The 'From' date cannot be later than the 'To' date.";
        exit;
    }

    $data = [ 
        "Bookings" => getBookings($from, $to, $username), 
        "Customer Profiles" => getCustomerProfiles($from, $to, $username), 
        "Insurance Records" => getInsuranceRecords($from, $to, $username), 
        "Loyalty Points" => getLoyaltyPoints($from, $to, $username), 
        "Vehicle Damage Reports" => getVehicleDamageReports($from, $to, $username), 
    ]; 

    header('Content-Type: text/csv'); 
    header('Content-Disposition: attachment;filename="export_' . date('Y-m-d_H-i-s') . '.csv"'); 
    $out = fopen('php://output', 'w'); 
    foreach ($data as $section => $rows) { 
        if (!empty($rows)) { 
            fputcsv($out, [$section]); 
            fputcsv($out, array_keys($rows[0])); 
            foreach ($rows as $row) { 
                fputcsv($out, $row); 
            } 
            fputcsv($out, []); 
        } 
    } 
    fclose($out); 
    exit; 
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data Export</title>
    <link rel="stylesheet" href="../asset/export.css">
    <script>
        function validateForm(event) {
            const from = document.getElementById("dateFrom").value;
            const to = document.getElementById("dateTo").value;


            if (!from || !to) {
                alert("Please select both From and To dates.");
                event.preventDefault();
                return false;
            }


            const fromDate = new Date(from);
            const toDate = new Date(to);

            if (fromDate > toDate) {
                alert("The 'From' date cannot be later than the 'To' date.");
                event.preventDefault();
                return false;
            }

            return true;
        }

        function ajax() {
            let from = document.getElementById('dateFrom').value;
            let to = document.getElementById('dateTo').value;

            let data = {
                'dateFrom': from,
                'dateTo': to
            };

            let jsonData = JSON.stringify(data);

            let xhttp = new XMLHttpRequest();
            xhttp.open('POST', 'exportData.php', true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send('data=' + jsonData);

            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById('msg').innerHTML = 'Data Export Successful!';
                }
            };
        }
    </script>
</head>
<body>
    <div class="export-container">
        <h2>Export Data</h2>
        <form method="POST" onsubmit="return validateForm(event)">
            <label for="dateFrom">From:</label>
            <input type="date" id="dateFrom" name="dateFrom" required>
            <label for="dateTo">To:</label>
            <input type="date" id="dateTo" name="dateTo" required>
            <input type="submit" value="Download CSV">
            <br>
            <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
        </form>

        <h3 id="msg"></h3>
    </div>
</body>
</html>
