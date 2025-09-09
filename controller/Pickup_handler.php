<?php
session_start();
require_once '../model/PickupModel.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branchId = $_POST['branch'] ?? '';
    $userId = $_SESSION['user_id'] ?? 1;

    if (empty($branchId)) {
        echo json_encode(['success' => false, 'message' => 'Please select a branch.']);
        exit;
    }

    $branches = getBranches();
    $selectedBranch = array_filter($branches, fn($b) => $b['id'] == $branchId);
    $branch = reset($selectedBranch);

    if (!$branch) {
        echo json_encode(['success' => false, 'message' => 'Branch not found.']);
        exit;
    }

    if (addPickup($userId, $branch)) {
        echo json_encode(['success' => true, 'data' => [
            'branch_name' => htmlspecialchars($branch['branch_name']),
            'city' => htmlspecialchars($branch['city']),
            'hours' => htmlspecialchars($branch['hours']),
            'after_hours' => htmlspecialchars($branch['after_hours']),
            'amenities' => htmlspecialchars($branch['amenities'])
        ]]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save pickup location.']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
exit;
