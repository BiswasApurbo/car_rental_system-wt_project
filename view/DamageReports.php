<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $damageMarks = $_POST['damageMarks'] ?? '';
    $signature   = $_POST['signature'] ?? '';
    $markedPhoto = $_POST['markedPhoto'] ?? '';

    // --- PHP Validation ---
    if (empty($_FILES['vehiclePhoto']['name'])) {
        $error = "Vehicle photo is required.";
    } elseif (empty($damageMarks)) {
        $error = "Damage marks are required.";
    } elseif (empty($signature)) {
        $error = "Signature is required.";
    } elseif (empty($markedPhoto)) {
        $error = "Marked vehicle image is missing.";
    }

    if (!isset($error)) {
        // Create upload directory
        $uploadDir = 'CarDamage_uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        // Save original vehicle photo with unique name
        $ext       = pathinfo($_FILES['vehiclePhoto']['name'], PATHINFO_EXTENSION);
        $photoName = uniqid('vehicle_', true) . '.' . strtolower($ext);
        $photoPath = $uploadDir . $photoName;
        move_uploaded_file($_FILES['vehiclePhoto']['tmp_name'], $photoPath);

        // Save signature
        $sigData     = preg_replace('#^data:image/\w+;base64,#i', '', $signature);
        $sigFileName = 'signature_' . time() . '.png';
        $sigPath     = $uploadDir . $sigFileName;
        file_put_contents($sigPath, base64_decode($sigData));

        // Save marked vehicle image
        $markedData     = preg_replace('#^data:image/\w+;base64,#i', '', $markedPhoto);
        $markedFileName = 'markedVehicle_' . time() . '.png';
        $markedPath     = $uploadDir . $markedFileName;
        file_put_contents($markedPath, base64_decode($markedData));

        // Save session info
        $_SESSION['damageReport'] = [
            'photo'        => $photoName,
            'markedPhoto'  => $markedFileName,
            'damageMarks'  => $damageMarks,
            'signatureFile'=> $sigFileName,
            'time'         => date('Y-m-d H:i:s')
        ];

        $successMsg = "Damage report submitted successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vehicle Damage Report</title>
    <link rel="stylesheet" href="DamageReportsdesign.css">
</head>
<body>
<div class="form-wrapper">
    <?php if (!empty($error)): ?>
        <p style="color:red;"><strong><?= htmlspecialchars($error) ?></strong></p>
    <?php elseif (!empty($successMsg)): ?>
        <p style="color:green;"><strong><?= htmlspecialchars($successMsg) ?></strong></p>
        <p><strong>Original Photo:</strong> <?= htmlspecialchars($photoPath) ?></p>
        <p><strong>Marked Vehicle Image:</strong> <?= htmlspecialchars($markedPath) ?></p>
        <p><strong>Signature Saved:</strong> <?= htmlspecialchars($sigPath) ?></p>
        <pre><?php print_r($_SESSION['damageReport']); ?></pre>
    <?php endif; ?>

    <form id="damageForm" action="" method="POST" enctype="multipart/form-data" onsubmit="return submitReport()">
        <fieldset>
            <h1>Vehicle Damage Report</h1>

            <!-- Vehicle Photo Upload -->
            <label>Upload Vehicle Photo</label>
            <input type="file" id="photoUpload" name="vehiclePhoto" accept="image/*" onchange="loadImage(event)">
            <p id="instruction"></p>

            <!-- Canvas for marking damages -->
            <div id="canvasContainer">
                <canvas id="canvas"></canvas>
            </div>
            <button type="button" id="undoBtn" onclick="undoLastCircle()">Undo Last Mark</button>
            <input type="hidden" name="damageMarks" id="damageMarks">
            <input type="hidden" name="markedPhoto" id="markedPhoto">
            <p id="canvasError"></p>

            <!-- Signature -->
            <label>Customer Signature</label>
            <canvas id="signatureCanvas"></canvas>
            <button type="button" id="clearSigBtn" onclick="clearSignature()">Clear Signature</button>
            <input type="hidden" name="signature" id="signature">
            <p id="sigError"></p>

            <!-- Submit -->
            <input type="submit" value="Submit Report">
            <p id="success"></p>
        </fieldset>
    </form>
</div>

<script>
    // Canvas for damage marks
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    const image = new Image();
    let circles = [];
    let isDrawing = false;
    let startX, startY;
    let isImageLoaded = false;

    // Canvas for signature
    const sigCanvas = document.getElementById('signatureCanvas');
    const sigCtx = sigCanvas.getContext('2d');
    let drawing = false;

    // Load vehicle photo
    function loadImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                image.onload = function () {
                    // scale image to max width 800px
                    const scale = image.width > 800 ? 800 / image.width : 1;
                    canvas.width = image.width * scale;
                    canvas.height = image.height * scale;
                    canvas.style.display = "block";
                    isImageLoaded = true;
                    drawCanvas();
                    document.getElementById('instruction').innerText = "Click and drag on image to circle the damaged area.";
                };
                image.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

    // Damage marking
    canvas.addEventListener("mousedown", (e) => {
        if (!isImageLoaded) return;
        const rect = canvas.getBoundingClientRect();
        startX = e.clientX - rect.left;
        startY = e.clientY - rect.top;
        isDrawing = true;
    });

    canvas.addEventListener("mouseup", (e) => {
        if (!isDrawing) return;
        const rect = canvas.getBoundingClientRect();
        const endX = e.clientX - rect.left;
        const endY = e.clientY - rect.top;
        const radius = Math.sqrt(Math.pow(endX - startX, 2) + Math.pow(endY - startY, 2)) / 2;
        const centerX = (startX + endX) / 2;
        const centerY = (startY + endY) / 2;

        circles.push({ x: centerX, y: centerY, r: radius, time: new Date().toISOString() });
        isDrawing = false;
        drawCanvas();

        document.getElementById('canvasError').innerText = "Damage area marked.";
        document.getElementById('canvasError').style.color = "green";
    });

    function drawCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
        for (const c of circles) {
            ctx.beginPath();
            ctx.arc(c.x, c.y, c.r, 0, Math.PI * 2);
            ctx.strokeStyle = "red";
            ctx.lineWidth = 2;
            ctx.stroke();
        }
    }

    function undoLastCircle() {
        if (circles.length > 0) {
            circles.pop();
            drawCanvas();
            document.getElementById('canvasError').innerText = "Last mark removed.";
            document.getElementById('canvasError').style.color = "orange";
        }
    }

    // Signature
    sigCanvas.width = sigCanvas.offsetWidth;
    sigCanvas.height = 100;

    sigCanvas.addEventListener('mousedown', () => drawing = true);
    sigCanvas.addEventListener('mouseup', () => {
        drawing = false;
        sigCtx.beginPath();
    });
    sigCanvas.addEventListener('mousemove', drawSignature);

    function drawSignature(e) {
        if (!drawing) return;
        const rect = sigCanvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        sigCtx.lineWidth = 2;
        sigCtx.lineCap = 'round';
        sigCtx.strokeStyle = 'black';
        sigCtx.lineTo(x, y);
        sigCtx.stroke();
        sigCtx.beginPath();
        sigCtx.moveTo(x, y);
    }

    function clearSignature() {
        sigCtx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
        document.getElementById('sigError').innerText = "";
    }

    // JS Validation
    function submitReport() {
        let valid = true;

        // Validate damage image
        if (!isImageLoaded) {
            document.getElementById('canvasError').innerText = "Please upload a vehicle image first.";
            document.getElementById('canvasError').style.color = "red";
            valid = false;
        } else if (circles.length === 0) {
            document.getElementById('canvasError').innerText = "Please mark at least one damage area.";
            document.getElementById('canvasError').style.color = "red";
            valid = false;
        }

        // Validate signature
        const blank = document.createElement('canvas');
        blank.width = sigCanvas.width;
        blank.height = sigCanvas.height;
        const blankData = blank.toDataURL();
        const sigData = sigCanvas.toDataURL();

        if (sigData === blankData) {
            document.getElementById('sigError').innerText = "Signature is required.";
            document.getElementById('sigError').style.color = "red";
            valid = false;
        } else {
            document.getElementById('signature').value = sigData;
            document.getElementById('sigError').innerText = "";
        }

        // Save damage marks
        document.getElementById('damageMarks').value = JSON.stringify(circles);

        // Save marked vehicle image
        const markedData = canvas.toDataURL('image/png');
        document.getElementById('markedPhoto').value = markedData;

        return valid;
    }
</script>
</body>
</html>
