<?php
session_start();
require_once "../model/MaintenanceModel.php";

header('Content-Type: application/json');
$user_id = $_SESSION['user_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['service_name'])) {
        $date = $_POST['date'] ?? '';
        $service_name = $_POST['service_name'] ?? '';
        $odometer = $_POST['odometer'] ?? '';
        $remarks = $_POST['remarks'] ?? '';

        if ($date && $service_name && $odometer !== '') {
            $success = addService($user_id, $date, $service_name, $odometer, $remarks);
            if ($success) {
                echo json_encode(['success'=>true, 'message'=>'New service added successfully!']);
                exit;
            } else {
                echo json_encode(['success'=>false, 'message'=>'Failed to add service.']);
                exit;
            }
        } else {
            echo json_encode(['success'=>false, 'message'=>'Please fill all required fields.']);
            exit;
        }
    }

    
    if (isset($_POST['delete_id'])) {
        $deleteId = intval($_POST['delete_id']);
        $success = deleteService($deleteId, $user_id);
        if ($success) {
            echo json_encode(['success'=>true, 'message'=>'Service deleted successfully!']);
            exit;
        } else {
            echo json_encode(['success'=>false, 'message'=>'Failed to delete service.']);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetch'])) {
    $services = getServicesByUser($user_id);
    echo json_encode(['success'=>true, 'services'=>$services]);
    exit;
}

echo json_encode(['success'=>false, 'message'=>'Invalid request.']);
exit;
