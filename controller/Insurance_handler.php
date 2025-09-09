<?php
session_start();
require_once "../model/InsuranceModel.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tier = $_POST['tier'] ?? '';
    $claim = $_POST['claim'] ?? '';
    $otherClaim = $_POST['otherClaim'] ?? '';

    if (empty($tier)) {
        echo json_encode(['success' => false, 'message' => 'Please select a coverage tier.']);
        exit;
    }
    if (empty($claim)) {
        echo json_encode(['success' => false, 'message' => 'Please select or enter a claim.']);
        exit;
    }

    if ($claim === "other" && !empty($otherClaim)) {
        $claim = $otherClaim;
    }

    $settings = getSettings();
    $deductible = $settings[$tier] ?? 0;
    $userId = $_SESSION['user_id'] ?? 1;

    addRecord($userId, $tier, $deductible, $claim);

    echo json_encode([
        'success' => true,
        'data' => [
            'tier' => $tier,
            'deductible' => $deductible,
            'claim' => htmlspecialchars($claim)
        ]
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
exit;
