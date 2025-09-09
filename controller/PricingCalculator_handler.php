<?php
session_start();
require_once "../model/PricingModel.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $days = intval($_POST['days'] ?? 0);
    $promo = $_POST['promo'] ?? '';

    if ($days <= 0) {
        echo json_encode(['success'=>false,'error'=>'Invalid rental days.']);
        exit;
    }

    $settings = getSettings();
    $baseFeePerDay = $settings['base_fee_per_day'] ?? 500;

    $baseFee = $baseFeePerDay * $days;
    $discountPercent = 0;
    $promoCodes = getAllPromoCodes();
    if (isset($promoCodes[$promo])) {
        $discountPercent = $promoCodes[$promo];
    }
    $discountAmount = ($discountPercent/100)*$baseFee;
    $tax = 0.1 * $baseFee;
    $total = $baseFee + $tax - $discountAmount;

    $quote = [
        'days'=>$days,
        'promo'=>$promo ?: 'N/A',
        'discountPercent'=>$discountPercent,
        'baseFee'=>$baseFee,
        'tax'=>$tax,
        'discountAmount'=>$discountAmount,
        'total'=>$total
    ];

    echo json_encode(['success'=>true,'quote'=>$quote]);
    exit;
}

echo json_encode(['success'=>false,'error'=>'Invalid request.']);
exit;
