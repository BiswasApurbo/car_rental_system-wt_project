<?php
session_start();
require_once "../model/DamageModel.php";

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    die("<p style='color:red;'>User not logged in.</p>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $damageMarks = $_POST['damageMarks'] ?? '';
    $signature   = $_POST['signature'] ?? '';
    $markedPhoto = $_POST['markedPhoto'] ?? '';
    $vehiclePhotoFile = $_FILES['vehiclePhoto'] ?? null;

    if (!$vehiclePhotoFile || empty($damageMarks) || empty($signature) || empty($markedPhoto)) {
        die("<p style='color:red;'>Please fill all fields and mark damage.</p>");
    }

    $uploadDir = '../view/CarDamage_uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

   
    $ext = pathinfo($vehiclePhotoFile['name'], PATHINFO_EXTENSION);
    $vehiclePhotoName = 'vehicle_' . time() . '.' . strtolower($ext);
    $vehiclePhotoPath = $uploadDir . $vehiclePhotoName;
    move_uploaded_file($vehiclePhotoFile['tmp_name'], $vehiclePhotoPath);

    
    $markedPhotoName = 'markedVehicle_' . time() . '.png';
    $markedPhotoPath = $uploadDir . $markedPhotoName;
    $markedPhotoData = preg_replace('#^data:image/\w+;base64,#i', '', $markedPhoto);
    file_put_contents($markedPhotoPath, base64_decode($markedPhotoData));

    
    $signatureName = 'signature_' . time() . '.png';
    $signaturePath = $uploadDir . $signatureName;
    $signatureData = preg_replace('#^data:image/\w+;base64,#i', '', $signature);
    file_put_contents($signaturePath, base64_decode($signatureData));

   
    if (addDamageReport($userId, $vehiclePhotoName, $markedPhotoName, $damageMarks, $signatureName)) {
       
        ?>
        <h2 style="color:green;">✅ Damage report submitted successfully!</h2>
        <p><strong>Original Photo:</strong> <a href="<?= htmlspecialchars($vehiclePhotoPath) ?>" target="_blank">View</a></p>
        <p><strong>Marked Vehicle Image:</strong> <a href="<?= htmlspecialchars($markedPhotoPath) ?>" target="_blank">View</a></p>
        <p><strong>Signature:</strong> <a href="<?= htmlspecialchars($signaturePath) ?>" target="_blank">View</a></p><br>

        <input type="button" value="Back to services" onclick="window.location.href='customer_services.php'"
               style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
        <input type="button" value="Back to Profile" onclick="window.location.href='profile.php'"
               style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
        <?php
    } else {
        echo "<p style='color:red;'>Database insertion failed.</p>";
    }
} else {
    die("<p style='color:red;'>Invalid request method.</p>");
}
?>
