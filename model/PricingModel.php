<?php
require_once 'db.php';

class PricingModel {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function addQuote($userId, $rentalDays, $promoCode, $discountPercent, $discountAmount, $baseFee, $tax, $total) {
        $stmt = $this->conn->prepare(
            "INSERT INTO `pricing_quotes` 
            (`user_id`, `rental_days`, `promo_code`, `discount_percent`, `discount_amount`, `base_fee`, `tax`, `total`, `created_at`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)"
        );
        $stmt->bind_param(
            "iisddddd", 
            $userId, 
            $rentalDays, 
            $promoCode, 
            $discountPercent, 
            $discountAmount, 
            $baseFee, 
            $tax, 
            $total
        );
        return $stmt->execute();
    }
}
?>
