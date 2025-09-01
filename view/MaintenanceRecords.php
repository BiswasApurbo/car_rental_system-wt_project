<?php
$file = 'maintenance_data.json';
$data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

// Handle POST (Add Service)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newService = [
        "date" => trim($_POST['date']),
        "name" => trim($_POST['serviceName']),
        "odometer" => trim($_POST['odometer']),
        "remarks" => trim($_POST['remarks'])
    ];
    $data[] = $newService;
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    header('Content-Type: application/json');
    echo json_encode(["success" => true, "service" => $newService]);
    exit;
}

// Handle GET ?api=1 (fetch all services)
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Records Dashboard</title>
    <link rel="stylesheet" href="MaintenanceRecords.css">
</head>
<body>
<div class="form-wrapper">
    <fieldset>
        <h1>Maintenance Records</h1>

        <!-- Alert Dashboard -->
        <h2>🔔 Alerts</h2>
        <div id="alerts"></div>

        <!-- Notification Section -->
        <h2>📢 Service Notifications</h2>
        <div id="notifications">
            <button onclick="getServiceNotification('Car Wash')">Get Car Wash</button>
            <button onclick="getServiceNotification('Brake Check')">Get Brake Check</button>
            <button onclick="getServiceNotification('Tire Rotation')">Get Tire Rotation</button>
        </div>

        <!-- Service Timeline -->
        <h2>🛠 Service Timeline</h2>
        <table id="serviceTimeline">
            <tr>
                <th>Date</th>
                <th>Service Performed</th>
                <th>Odometer</th>
                <th>Remarks</th>
                <th>Action</th>
            </tr>
        </table>

        <!-- Odometer Log / Add Service -->
        <h2>📊 Add New Service / Odometer Log</h2>
        <form id="serviceForm">
            <label for="serviceDate">Date:</label>
            <input type="date" id="serviceDate" required>

            <label for="serviceName">Service Performed:</label>
            <input type="text" id="serviceName" required>

            <label for="odometer">Odometer (km):</label>
            <input type="number" id="odometer" required min="0">

            <label for="remarks">Remarks:</label>
            <input type="text" id="remarks">

            <input type="submit" value="Add Service">
        </form>
    </fieldset>
</div>

<script>
// Fetch all services from PHP
async function fetchServices(){
    const res = await fetch('MaintenanceRecords.php?api=1');
    return await res.json();
}

// Update Timeline
async function updateTimeline(){
    const table = document.getElementById('serviceTimeline');
    table.innerHTML = `<tr>
        <th>Date</th>
        <th>Service Performed</th>
        <th>Odometer</th>
        <th>Remarks</th>
        <th>Action</th>
    </tr>`;

    let services = await fetchServices();
    services.forEach((s, index) => {
        const row = table.insertRow();
        row.innerHTML = `<td>${s.date}</td>
                         <td>${s.name}</td>
                         <td>${s.odometer}</td>
                         <td>${s.remarks}</td>
                         <td><button onclick="deleteService(${index})">Delete</button></td>`;
    });
}

// Add Alert
function addAlert(message){
    const alertDiv = document.getElementById('alerts');
    const newAlert = document.createElement('div');
    newAlert.className = 'alert-box alert-success';
    newAlert.innerText = message;
    alertDiv.prepend(newAlert);
}

// Add New Service (Form Submit)
document.getElementById('serviceForm').addEventListener('submit', async function(e){
    e.preventDefault();
    const formData = new FormData();
    formData.append('date', document.getElementById('serviceDate').value);
    formData.append('serviceName', document.getElementById('serviceName').value);
    formData.append('odometer', document.getElementById('odometer').value);
    formData.append('remarks', document.getElementById('remarks').value);

    const res = await fetch('MaintenanceRecords.php', {
        method: 'POST',
        body: formData
    });
    const result = await res.json();

    if(result.success){
        addAlert(`New service added: ${result.service.name} at ${result.service.odometer} km`);
        updateTimeline();
        this.reset();
    }
});

// Delete Service (⚠ not fully implemented)
async function deleteService(index){
    let services = await fetchServices();
    const removed = services.splice(index,1)[0];

    // Would need PHP handler to rewrite JSON file
    addAlert(`Service deleted (not saved): ${removed.name} at ${removed.odometer} km`);
    updateTimeline();
}

// Add Service from Notifications
async function getServiceNotification(serviceName){
    const today = new Date().toISOString().split('T')[0];
    let remarks = "";
    if(serviceName.toLowerCase() === "car wash"){
        remarks = "Keeps Car neat & clean.";
    } else if(serviceName.toLowerCase() === "brake check"){
        remarks = "Ensures safe braking performance.";
    } else if(serviceName.toLowerCase() === "tire rotation"){
        remarks = "Increases tire life and improves handling.";
    } else {
        remarks = "Recommended service.";
    }

    const formData = new FormData();
    formData.append('date', today);
    formData.append('serviceName', serviceName);
    formData.append('odometer', "N/A");
    formData.append('remarks', remarks);

    const res = await fetch('MaintenanceRecords.php', {
        method: 'POST',
        body: formData
    });
    const result = await res.json();

    if(result.success){
        addAlert(`Service notification added: ${serviceName}`);
        updateTimeline();
    }
}

// Init
window.onload = function(){
    updateTimeline();
};
</script>
</body>
</html>
