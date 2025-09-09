<?php
session_start();
require_once('../model/userModel.php');
require_once('../model/activityLogModel.php'); 

header('Content-Type: application/json');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$data = json_decode($_POST['filter'], true);

$fromDate = $data['fromDate'] ?? '2025-01-01';
$toDate   = $data['toDate']   ?? date('Y-m-d');
$username = $data['user']     ?? '';

if ($fromDate > $toDate) {
    echo json_encode(['error' => "The 'From Date' cannot be later than the 'To Date'."]);
    exit;
}

$logs = getActivityLog($fromDate, $toDate, $username);
echo json_encode($logs);
