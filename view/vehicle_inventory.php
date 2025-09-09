<?php
session_start();
require_once('../model/vehicleModel.php');
require_once('../model/userModel.php');            


if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    header('Location: ../view/login.php?error=badrequest');
    exit;
}

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$types    = getVehicleTypes();   
$features = getFeatures();       

$errors     = ['type'=>'','feature'=>'','price'=>''];
$message    = '';
$typeId     = $_POST['type'] ?? '';     
$featureId  = $_POST['feature'] ?? '';  
$priceStr   = $_POST['price'] ?? '';   
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 12;


if ($typeId !== '') {
    if (!ctype_digit((string)$typeId)) { $errors['type'] = 'Invalid vehicle type.'; $typeId=''; }
}

if ($featureId !== '') {
    if (!ctype_digit((string)$featureId)) { $errors['feature'] = 'Invalid feature.'; $featureId=''; }
}

if ($priceStr !== '') {
    if (!preg_match('/^\d+\-\d+$/', $priceStr)) { $errors['price'] = 'Invalid price range (e.g., 2000-5000).'; $priceStr=''; }
}

$filters = [];
if ($typeId !== '')    $filters['type_id']    = (int)$typeId;
if ($featureId !== '') $filters['feature_id']  = (int)$featureId;
if ($priceStr !== '')  $filters['price']       = $priceStr;

$total       = countVehiclesFiltered($filters);
$totalPages  = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$vehicles    = getVehiclesFiltered($filters, $page, $perPage);
if ($total === 0) $message = 'No vehicles matched your filters.';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Inventory</title>
    <link rel="stylesheet" href="../asset/ad.css">
    <style>
        .card{max-width:1100px;margin:18px auto;padding:18px;background:#fff;border-radius:8px;box-shadow:0 0 8px #ddd}
        fieldset{border:none;padding:0;margin:0 0 12px}
        label{display:inline-block;min-width:110px;margin-right:6px}
        select{min-width:180px}
        .error-message{color:#c0392b;font-weight:600;margin:4px 0 10px}
        .validation{font-weight:700;margin:8px 0;color:#2e7d32}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;margin-top:16px}
        .vehicle{border:1px solid #e5e5e5;border-radius:8px;padding:12px;background:#fafafa}
        .vehicle img{width:100%;height:160px;object-fit:cover;border-radius:6px}
        .pagination{display:flex;gap:8px;margin-top:16px;flex-wrap:wrap}
        .pagination a,.pagination span{padding:6px 10px;border:1px solid #ddd;border-radius:6px;text-decoration:none}
        .pagination .active{background:#2c3e50;color:#fff;border-color:#2c3e50}
        .controls{display:flex;gap:14px;flex-wrap:wrap}
        .btn{display:inline-block;padding:8px 12px;border:1px solid #2c3e50;border-radius:6px;background:#2c3e50;color:#fff;cursor:pointer}
        .btn.secondary{background:#fff;color:#2c3e50}
    </style>
    <script>
      
        function submitFilters() {
            const formData = new FormData(document.getElementById('filterForm'));
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../controller/processVehicle.php', true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        
                        document.getElementById('vehicleList').innerHTML = response.vehicles;
                        
                        document.getElementById('pagination').innerHTML = response.pagination;
                    } else {
                        alert(response.message);
                    }
                }
            };
            xhr.send(formData);
        }

        
        function resetFilters() {
            document.getElementById('filterForm').reset();
            submitFilters();
        }
    </script>
</head>
<body>
<div class="card">
    <h1>Vehicle Inventory</h1>

    <form id="filterForm" method="POST" action="" onsubmit="event.preventDefault(); submitFilters();">
        <div class="controls">
            <fieldset>
                <label for="type">Type:</label>
                <select id="type" name="type" onchange="submitFilters()">
                    <option value="">--Any--</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= (int)$t['id'] ?>" <?= ($typeId!=='' && (int)$t['id']===(int)$typeId)?'selected':'' ?>>
                            <?= h($t['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($errors['type']): ?><div class="error-message"><?= h($errors['type']) ?></div><?php endif; ?>
            </fieldset>

            <fieldset>
                <label for="feature">Feature:</label>
                <select id="feature" name="feature" onchange="submitFilters()">
                    <option value="">--Any--</option>
                    <?php foreach ($features as $f): ?>
                        <option value="<?= (int)$f['id'] ?>" <?= ($featureId!=='' && (int)$f['id']===(int)$featureId)?'selected':'' ?>>
                            <?= h($f['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($errors['feature']): ?><div class="error-message"><?= h($errors['feature']) ?></div><?php endif; ?>
            </fieldset>

            <fieldset>
                <label for="price">Price Range:</label>
                <select id="price" name="price" onchange="submitFilters()">
                    <option value="">--Any--</option>
                    <option value="0-2000" <?= ($priceStr==='0-2000')?'selected':'' ?>>0-2000</option>
                    <option value="2001-4000" <?= ($priceStr==='2001-4000')?'selected':'' ?>>2001-4000</option>
                    <option value="4001-6000" <?= ($priceStr==='4001-6000')?'selected':'' ?>>4001-6000</option>
                    <option value="6001-10000" <?= ($priceStr==='6001-10000')?'selected':'' ?>>6001-10000</option>
                </select>
                <?php if ($errors['price']): ?><div class="error-message"><?= h($errors['price']) ?></div><?php endif; ?>
            </fieldset>

            <button type="button" class="btn secondary" onclick="resetFilters()">Reset</button>
        </div>
    </form>

    <div id="vehicleList" class="grid">
      
        <?php if ($total > 0): ?>
            <?php foreach ($vehicles as $v): ?>
                <div class="vehicle">
                    <?php if (!empty($v['img'])): ?>
                        <img src="<?= h($v['img']) ?>" alt="<?= h($v['make'].' '.$v['model']) ?>">
                    <?php else: ?>
                        <img src="../asset/placeholder-vehicle.jpg" alt="No image available">
                    <?php endif; ?>
                    <h3><?= h($v['make'].' '.$v['model']) ?><?= $v['model_year'] ? ' ('.h($v['model_year']).')' : '' ?></h3>
                    <p>Type: <?= h($v['type_name']) ?></p>
                    <p>Rate: <?= h(number_format((float)$v['daily_rate'],2)) ?></p>
                    <p>
                        <?= $v['seats'] ? h($v['seats']).' seats • ' : '' ?>
                        <?= h($v['transmission'] ?: '') ?>
                        <?= $v['fuel_type'] ? ' • '.h($v['fuel_type']) : '' ?>
                    </p>
                    <button type="button" class="btn" onclick="window.location.href='booking_calendar.php?vehicle_id=<?= (int)$v['id'] ?>'">
                        Check Availability
                    </button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No vehicles found.</p>
        <?php endif; ?>
    </div>

    <div id="pagination">
      
        <?php
        if ($totalPages > 1):
            $qs = [
                'type'    => ($typeId === '' ? null : $typeId),
                'feature' => ($featureId === '' ? null : $featureId),
                'price'   => ($priceStr === '' ? null : $priceStr),
            ];
            $base = basename(__FILE__);
        ?>
        <div class="pagination" role="navigation" aria-label="Pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php if ($p == $page): ?>
                    <span class="active"><?= $p ?></span>
                <?php else: ?>
                    <?php $qs['page'] = $p; ?>
                    <a href="#" onclick="submitFilters()"><?= $p ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <br>
    <input type="button" class="btn secondary" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'">
</div>
</body>
</html>
