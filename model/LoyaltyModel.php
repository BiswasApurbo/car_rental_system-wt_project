<?php
require_once "db.php";

function getLoyaltyPoints($user_id) {
    $conn = getConnection();

    $stmt = $conn->prepare("SELECT points, tier FROM loyalty_points WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        
        $points = 2000;
        $tier = "Gold";
        $stmt2 = $conn->prepare("INSERT INTO loyalty_points (user_id, points, tier) VALUES (?, ?, ?)");
        $stmt2->bind_param("iis", $user_id, $points, $tier);
        $stmt2->execute();

        $row = ['points' => $points, 'tier' => $tier];
    }

    return $row;
}


function redeemLoyaltyPoints($user_id, $redeem) {
    $current = getLoyaltyPoints($user_id);

    if ($redeem <= $current['points']) {
        $newPoints = $current['points'] - $redeem;
        $tier = calculateLoyaltyTier($newPoints);

        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE loyalty_points SET points = ?, tier = ?, last_updated = CURRENT_TIMESTAMP WHERE user_id = ?");
        $stmt->bind_param("isi", $newPoints, $tier, $user_id);
        $stmt->execute();

        return ['success' => true, 'points' => $newPoints, 'tier' => $tier];
    } else {
        return ['success' => false];
    }
}

function calculateLoyaltyTier($points) {
    if ($points >= 4000) return "Platinum";
    elseif ($points >= 2000) return "Gold";
    else return "Silver";
}
?>
