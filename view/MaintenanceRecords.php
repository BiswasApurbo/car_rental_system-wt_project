<?php
session_start();
require_once "../model/MaintenanceModel.php";

$model = new MaintenanceModel();
$user_id = $_SESSION['user_id'] ?? 1;  


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $date     = $_POST['date'] ?? '';
    $name     = $_POST['service_name'] ?? '';
    $odometer = $_POST['odometer'] ?? '';
    $remarks  = $_POST['remarks'] ?? '';

    if ($date && $name && $odometer !== '') {
        $model->addService($user_id, $date, $name, $odometer, $remarks);
        $message = "New service added successfully!";
    } else {
        $error = "Please fill in all required fields.";
    }
}

if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $model->deleteService($deleteId, $user_id);
    $message = "Service deleted successfully!";
}
$services = $model->getServicesByUser($user_id);

$alerts = [];
if (!empty($services)) {
    $last_service = $services[0]; 
    $km_since_last = intval($last_service['odometer']);
    
    if ($km_since_last >= 10000) {
        $alerts[] = "Your vehicle is due for major maintenance!";
    }
    if ($km_since_last >= 5000) {
        $alerts[] = "Oil change is recommended soon.";
    }
}

$recommended = $model->getRecommendedServices();
$next_odometer = !empty($services) ? intval($services[0]['odometer']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Maintenance Records Dashboard</title>
<link rel="stylesheet" href="../asset/MaintenanceRecords.css">
<script>
function fillService(serviceName, odometerValue, remarks) {
    document.getElementById('serviceName').value = serviceName;
    document.getElementById('odometer').value = odometerValue;
    document.getElementById('remarks').value = remarks;
}
</script>
</head>
<body>
<div class="form-wrapper">
<fieldset>
<h1>Maintenance Records</h1>

<?php if(isset($message)) echo "<div class='alert-box alert-success'>$message</div>"; ?>
<?php if(isset($error)) echo "<div class='alert-box alert-error'>$error</div>"; ?>

<h2>🔔 Notifications / Alerts</h2>
<div>
<?php
if (!empty($alerts)) {
    echo "<ul>";
    foreach($alerts as $a) {
        echo "<li>$a</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No notifications at this time.</p>";
}
?>
</div>

<h2>📝 Recommended Services</h2>
<div>
<?php
if (!empty($recommended)) {
    echo "<ul>";
    foreach($recommended as $r) {
        $rec_odometer = $next_odometer + intval($r['description'] ?? 0); 
        echo "<li>
            <a href='#' onclick=\"fillService('{$r['service_name']}', {$rec_odometer}, 'Recommended service'); return false;\">
                {$r['service_name']}
            </a>
        </li>";
    }
    echo "</ul>";
} else {
    echo "<p>No recommended services.</p>";
}
?>
</div>

<h2>🛠 Service Timeline</h2>
<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>Date</th>
        <th>Service Performed</th>
        <th>Odometer</th>
        <th>Remarks</th>
        <th>Action</th>
    </tr>
    <?php foreach($services as $s): ?>
    <tr>
        <td><?= htmlspecialchars($s['service_date']) ?></td>
        <td><?= htmlspecialchars($s['service_name']) ?></td>
        <td><?= htmlspecialchars($s['odometer']) ?></td>
        <td><?= htmlspecialchars($s['remarks']) ?></td>
        <td><a href="?delete=<?= $s['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>📊 Add New Service / Odometer Log</h2>
<form method="post">
    <input type="hidden" name="add_service" value="1">
    <label for="serviceDate">Date:</label>
    <input type="date" name="date" required><br><br>

    <label for="serviceName">Service Performed:</label>
    <input type="text" id="serviceName" name="service_name" required><br><br>

    <label for="odometer">Odometer (km):</label>
    <input type="number" id="odometer" name="odometer" required min="0"><br><br>

    <label for="remarks">Remarks:</label>
    <input type="text" id="remarks" name="remarks"><br><br>

    <input type="submit" value="Add Service">
</form>

<br>
<input type="button" value="Back to services" onclick="window.location.href='customer_services.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
<input type="button" value="Back to Profile" onclick="window.location.href='profile.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">

</fieldset>
</div>
</body>
</html>
