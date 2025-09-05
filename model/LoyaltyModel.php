<?php
require_once "db.php";

class LoyaltyModel {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function getPoints($user_id) {
        $stmt = $this->conn->prepare("SELECT points, tier FROM loyalty_points WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row) {
            
            $points = 2000;
            $tier = "Gold";
            $stmt2 = $this->conn->prepare("INSERT INTO loyalty_points (user_id, points, tier) VALUES (?, ?, ?)");
            $stmt2->bind_param("iis", $user_id, $points, $tier);
            $stmt2->execute();

            $row = ['points' => $points, 'tier' => $tier];
        }

        return $row;
    }

    public function redeemPoints($user_id, $redeem) {
        $current = $this->getPoints($user_id);
        if ($redeem <= $current['points']) {
            $newPoints = $current['points'] - $redeem;
            $tier = $this->calculateTier($newPoints);

            $stmt = $this->conn->prepare("UPDATE loyalty_points SET points = ?, tier = ?, last_updated = CURRENT_TIMESTAMP WHERE user_id = ?");
            $stmt->bind_param("isi", $newPoints, $tier, $user_id);
            $stmt->execute();

            return ['success' => true, 'points' => $newPoints, 'tier' => $tier];
        } else {
            return ['success' => false];
        }
    }

    private function calculateTier($points) {
        if ($points >= 4000) return "Platinum";
        elseif ($points >= 2000) return "Gold";
        else return "Silver";
    }
}
?>
