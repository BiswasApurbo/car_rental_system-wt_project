<?php
session_start();
require_once('../model/vehicleModel.php');            

// Guard: Ensure the user is logged in
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// Sanitize function
function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Read the filters from the form submission
$typeId    = $_POST['type'] ?? ''; 
$featureId = $_POST['feature'] ?? '';
$priceStr  = $_POST['price'] ?? '';
$page      = max(1, (int)($_POST['page'] ?? 1));
$perPage   = 12; 

// Validate the filters
$errors = [];
if ($priceStr !== '' && !preg_match('/^\d+\-\d+$/', $priceStr)) {
    $errors['price'] = 'Invalid price range (e.g., 2000-5000).';
}
if ($typeId !== '' && !ctype_digit((string)$typeId)) {
    $errors['type'] = 'Invalid vehicle type.';
}
if ($featureId !== '' && !ctype_digit((string)$featureId)) {
    $errors['feature'] = 'Invalid feature.';
}

// Return errors if any
if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => $errors]);
    exit;
}

// Build filters
$filters = [];
if ($typeId !== '') $filters['type_id'] = (int)$typeId;
if ($featureId !== '') $filters['feature_id'] = (int)$featureId;
if ($priceStr !== '') $filters['price'] = $priceStr;

// Get the total number of filtered vehicles
$total = countVehiclesFiltered($filters);
$totalPages = ceil($total / $perPage);

// Ensure the requested page is within bounds
if ($page > $totalPages) $page = $totalPages;

// Get the filtered vehicles for the current page
$vehicles = getVehiclesFiltered($filters, $page, $perPage);

// Prepare the vehicle list HTML to send back to the frontend
$vehicleHtml = '';
foreach ($vehicles as $v) {
    $vehicleHtml .= '<div class="vehicle">';
    // Correctly construct the image path
    $imagePath = !empty($v['img']) ? '../' . h($v['img']) : '../asset/placeholder-vehicle.jpg';  // Provide fallback image
    $vehicleHtml .= '<img src="' . $imagePath . '" alt="' . h($v['make'] . ' ' . $v['model']) . '">';
    $vehicleHtml .= '<h3>' . h($v['make'] . ' ' . $v['model']) . ' (' . h($v['model_year']) . ')</h3>';
    $vehicleHtml .= '<p>Type: ' . h($v['type_name']) . '</p>';
    $vehicleHtml .= '<p>Rate: ' . h(number_format((float)$v['daily_rate'], 2)) . '</p>';
    $vehicleHtml .= '<p>' . ($v['seats'] ? h($v['seats']) . ' seats • ' : '') . h($v['transmission']) . ' ' . ($v['fuel_type'] ? ' • ' . h($v['fuel_type']) : '') . '</p>';
    $vehicleHtml .= '<button type="button" class="btn" onclick="window.location.href=\'booking_calendar.php?vehicle_id=' . (int)$v['id'] . '\'">Check Availability</button>';
    $vehicleHtml .= '</div>';
}

// Prepare pagination HTML
$paginationHtml = '';
for ($p = 1; $p <= $totalPages; $p++) {
    if ($p == $page) {
        $paginationHtml .= '<span class="active">' . $p . '</span>';
    } else {
        $paginationHtml .= '<a href="#" onclick="submitFilters()">' . $p . '</a>';
    }
}

// Send the response as JSON
echo json_encode([
    'status' => 'success',
    'vehicles' => $vehicleHtml,
    'pagination' => $paginationHtml,
    'total' => $total,
    'totalPages' => $totalPages
]);
?>
