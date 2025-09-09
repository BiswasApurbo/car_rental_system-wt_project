<?php
session_start();
require_once "../model/FuelModel.php";

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fuelLimit = floatval($_POST['fuelLimit'] ?? 0);
    $refuelLiters = floatval($_POST['refuelLiters'] ?? 0);
    $pricePerLiter = floatval($_POST['pricePerLiter'] ?? 0);
    $totalCost = floatval($_POST['totalCost'] ?? 0);

    if ($fuelLimit <= 0 || $refuelLiters <= 0 || $fuelLimit < $refuelLiters || !isset($_FILES['receiptUpload'])) {
        echo json_encode(['success' => false, 'message' => 'Please provide valid input and upload receipt.']);
        exit;
    }

    $uploadDir = "../view/GasReceiptUploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $file = $_FILES['receiptUpload'];
    $fileName = time() . "_" . basename($file['name']);
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload receipt.']);
        exit;
    }

    $success = addFuelRecord($user_id, $fuelLimit, $refuelLiters, $pricePerLiter, $totalCost, $fileName);

    if ($success) {
        echo json_encode([
            'success' => true,
            'data' => [
                'fuelLimit' => $fuelLimit,
                'refuelLiters' => $refuelLiters,
                'pricePerLiter' => number_format($pricePerLiter,2),
                'totalCost' => number_format($totalCost,2),
                'receiptPath' => htmlspecialchars($filePath)
            ]
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save fuel record.']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
exit;
