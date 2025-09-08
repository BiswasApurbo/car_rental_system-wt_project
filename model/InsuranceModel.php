<?php
require_once "db.php";

// Get all insurance settings (tier => deductible)
function getSettings() {
    $conn = getConnection();
    $settings = [];
    $sql = "SELECT tier, deductible FROM insurance_settings";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['tier']] = floatval($row['deductible']);
        }
    }
    return $settings;
}

// Get claim examples for a specific tier
function getClaimExamples($tier) {
    $conn = getConnection();
    $examples = [];
    $stmt = $conn->prepare("SELECT example_text FROM insurance_claim_examples WHERE tier = ?");
    $stmt->bind_param("s", $tier);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $examples[] = $row['example_text'];
    }
    return $examples;
}

// Add a new insurance record
function addRecord($userId, $tier, $deductible, $claim) {
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO insurance_records (user_id, tier, deductible, claim) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $userId, $tier, $deductible, $claim);
    return $stmt->execute();
}
?>
