<?php
require_once 'db.php';

class InsuranceModel {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function addInsuranceOption($userId, $coverageTier, $deductible, $claim) {
        $stmt = $this->conn->prepare(
            "INSERT INTO `insurance_options` 
            (`user_id`, `coverage_tier`, `deductible`, `claim`) 
            VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("isds", $userId, $coverageTier, $deductible, $claim);
        return $stmt->execute();
    }
}
