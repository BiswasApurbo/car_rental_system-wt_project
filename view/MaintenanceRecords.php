<?php
session_start();
require_once "../model/MaintenanceModel.php"; 

$user_id = $_SESSION['user_id'] ?? 1;  

$services = getServicesByUser($user_id);
$alerts = [];
if (!empty($services)) {
    $last_service = $services[0];
    $km_since_last = intval($last_service['odometer']);
    if ($km_since_last >= 10000) $alerts[] = "Your vehicle is due for major maintenance!";
    if ($km_since_last >= 5000) $alerts[] = "Oil change is recommended soon.";
}

$recommended = getRecommendedServices();
$next_odometer = !empty($services) ? intval($services[0]['odometer']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Maintenance Records Dashboard</title>
<link rel="stylesheet" href="../asset/MaintenanceRecords.css">
<style>
.message { font-size:14px; margin:4px 0; }
.green { color:green; }
.red { color:red; }
#result { margin-top:20px; padding:15px; border:1px solid #ddd; border-radius:6px; }
</style>
<script>
function fillService(serviceName, odometerValue, remarks) {
    document.getElementById('serviceName').value = serviceName;
    document.getElementById('odometer').value = odometerValue;
    document.getElementById('remarks').value = remarks;
}


function submitService(e) {
    e.preventDefault();
    const date = document.querySelector("input[name='date']").value;
    const service_name = document.getElementById('serviceName').value;
    const odometer = document.getElementById('odometer').value;
    const remarks = document.getElementById('remarks').value;
    const resultDiv = document.getElementById("result");

    if (!date || !service_name || odometer === '') {
        resultDiv.innerHTML = "<p class='red'>Please fill all required fields.</p>";
        return;
    }

    const formData = new FormData();
    formData.append("date", date);
    formData.append("service_name", service_name);
    formData.append("odometer", odometer);
    formData.append("remarks", remarks);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "../controller/Maintenance_handler.php", true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.success) {
                    resultDiv.innerHTML = "<p class='green'>" + res.message + "</p>";
              
                    loadServices();
                    document.getElementById('serviceForm').reset();
                } else {
                    resultDiv.innerHTML = "<p class='red'>" + res.message + "</p>";
                }
            } catch(e) {
                resultDiv.innerHTML = "<p class='red'>Server error parsing response.</p>";
            }
        } else {
            resultDiv.innerHTML = "<p class='red'>Server error.</p>";
        }
    };
    xhr.send(formData);
}

function deleteService(id) {
    if (!confirm("Are you sure?")) return;
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "../controller/Maintenance_handler.php", true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const res = JSON.parse(xhr.responseText);
                const resultDiv = document.getElementById("result");
                if (res.success) {
                    resultDiv.innerHTML = "<p class='green'>" + res.message + "</p>";
                    loadServices();
                } else {
                    resultDiv.innerHTML = "<p class='red'>" + res.message + "</p>";
                }
            } catch(e) {
                document.getElementById("result").innerHTML = "<p class='red'>Server error parsing response.</p>";
            }
        }
    };
    xhr.send("delete_id=" + id);
}


function loadServices() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "../controller/Maintenance_handler.php?fetch=1", true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const res = JSON.parse(xhr.responseText);
                const tbody = document.getElementById("serviceTimeline");
                tbody.innerHTML = "";
                if (res.success && res.services.length > 0) {
                    res.services.forEach(s => {
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
                            <td>${s.service_date}</td>
                            <td>${s.service_name}</td>
                            <td>${s.odometer}</td>
                            <td>${s.remarks}</td>
                            <td><a href="#" onclick="deleteService(${s.id}); return false;">Delete</a></td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = "<tr><td colspan='5'>No service records found.</td></tr>";
                }
            } catch(e) {
                console.log("Error parsing JSON");
            }
        }
    };
    xhr.send();
}

window.onload = function() {
    loadServices();
};
</script>
</head>
<body>
<div class="form-wrapper">
<fieldset>
<h1>Maintenance Records</h1>

<div id="result"></div>

<h2>🔔 Notifications / Alerts</h2>
<div>
<?php
if (!empty($alerts)) {
    echo "<ul>";
    foreach($alerts as $a) echo "<li>$a</li>";
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
        echo "<li><a href='#' onclick=\"fillService('{$r['service_name']}', {$rec_odometer}, 'Recommended service'); return false;\">{$r['service_name']}</a></li>";
    }
    echo "</ul>";
} else { echo "<p>No recommended services.</p>"; }
?>
</div>

<h2>🛠 Service Timeline</h2>
<table border="1" cellpadding="8" cellspacing="0">
    <thead>
    <tr>
        <th>Date</th>
        <th>Service Performed</th>
        <th>Odometer</th>
        <th>Remarks</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody id="serviceTimeline">
        <tr><td colspan="5">Loading...</td></tr>
    </tbody>
</table>

<h2>📊 Add New Service / Odometer Log</h2>
<form id="serviceForm" onsubmit="submitService(event)">
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
