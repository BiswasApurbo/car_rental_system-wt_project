<?php
require_once "db.php";

class InsuranceModel {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    
    public function getSettings() {
        $settings = [];
        $sql = "SELECT tier, deductible FROM insurance_settings";
        $result = $this->conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $settings[$row['tier']] = floatval($row['deductible']);
            }
        }
        return $settings;
    }

  
    public function getClaimExamples($tier) {
        $examples = [];
        $stmt = $this->conn->prepare("SELECT example_text FROM insurance_claim_examples WHERE tier = ?");
        $stmt->bind_param("s", $tier);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $examples[] = $row['example_text'];
        }
        return $examples;
    }

   
    public function addRecord($userId, $tier, $deductible, $claim) {
        $stmt = $this->conn->prepare("INSERT INTO insurance_records (user_id, tier, deductible, claim) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $userId, $tier, $deductible, $claim);
        return $stmt->execute();
    }
}
?>
