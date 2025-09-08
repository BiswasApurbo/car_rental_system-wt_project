<?php
require_once 'db.php';

function getBranches() {
    $conn = getConnection();
    $result = $conn->query("SELECT * FROM `branches` ORDER BY `city`, `branch_name`");
    return $result->fetch_all(MYSQLI_ASSOC);
}


function addPickup($userId, $branch) {
    $conn = getConnection();
    $stmt = $conn->prepare(
        "INSERT INTO `user_pickups` 
        (`user_id`, `branch_id`, `branch_name`, `city`, `hours`, `after_hours`, `amenities`) 
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "iisssss", 
        $userId, 
        $branch['id'], 
        $branch['branch_name'], 
        $branch['city'], 
        $branch['hours'], 
        $branch['after_hours'], 
        $branch['amenities']
    );
    return $stmt->execute();
}
?>
