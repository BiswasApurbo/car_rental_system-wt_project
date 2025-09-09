<?php
session_start();
require_once "../model/LoyaltyModel.php";

header('Content-Type: application/json');
$user_id = $_SESSION['user_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeemPoints'])){
    $redeem = intval($_POST['redeemPoints']);
    $result = redeemLoyaltyPoints($user_id, $redeem);

    $currentData = getLoyaltyPoints($user_id);
    $points = $currentData['points'];
    $tier = $currentData['tier'];
    $maxPoints = 5000;

    if($tier === "Platinum") $nextTierPoints = "Max tier reached 🎉";
    elseif($tier === "Gold") $nextTierPoints = 4000-$points;
    else $nextTierPoints = 2000-$points;

    $progress = ($points/$maxPoints)*100;

    if($result['success']){
        echo json_encode([
            "success"=>true,
            "message"=>"Redeemed {$redeem} points! Remaining points: {$points}",
            "points"=>$points,
            "tier"=>$tier,
            "nextTierPoints"=>$nextTierPoints,
            "progress"=>$progress
        ]);
    }else{
        echo json_encode([
            "success"=>false,
            "message"=>"Not enough points to redeem."
        ]);
    }
    exit;
}

echo json_encode(["success"=>false,"message"=>"Invalid request"]);
exit;
