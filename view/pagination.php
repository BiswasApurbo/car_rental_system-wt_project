<?php
session_start();
require_once('../model/userModel.php');
require_once('../model/paginationModel.php');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    if (isset($_COOKIE['status']) && (string)$_COOKIE['status'] === '1') {
        $_SESSION['status'] = true;
        if (!isset($_SESSION['username']) && isset($_COOKIE['remember_user'])) {
            $_SESSION['username'] = $_COOKIE['remember_user'];
        }
        if (!isset($_SESSION['role']) && isset($_COOKIE['remember_role'])) {
            $c = strtolower(trim((string)$_COOKIE['remember_role']));
            $_SESSION['role'] = ($c === 'admin') ? 'Admin' : 'User';
        }
    } else {
        header('location: ../view/login.php?error=badrequest');
        exit;
    }
}

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$dataset = $_GET['dataset'] ?? 'vehicles';
$perParam = $_GET['per'] ?? '10';
$pageParam = $_GET['page'] ?? '1';

$allowedDatasets = ['vehicles','users','bookings'];
if (!in_array($dataset, $allowedDatasets, true)) $dataset = 'vehicles';

if (!preg_match('/^\d+$/', $perParam)) $perParam = '10';
$per = (int)$perParam;
if (!in_array($per, [5,10,25,50,100], true)) $per = 10;

if (!preg_match('/^\d+$/', $pageParam)) $pageParam = '1';
$page = max(1, (int)$pageParam);

$errors = [];

switch ($dataset) {
    case 'users':
        $total = pg_count_users();
        break;
    case 'bookings':
        $total = pg_count_bookings();
        break;
    case 'vehicles':
    default:
        $total = pg_count_vehicles();
        break;
}
$totalPages = max(1, (int)ceil($total / max(1,$per)));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $per;

switch ($dataset) {
    case 'users':
        $rows = pg_fetch_users($per, $offset);
        break;
    case 'bookings':
        $rows = pg_fetch_bookings($per, $offset);
        break;
    case 'vehicles':
    default:
        $rows = pg_fetch_vehicles($per, $offset);
        break;
}

function baseUrl() { return strtok($_SERVER['REQUEST_URI'], '?'); }
function q($arr){ return http_build_query($arr); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pagination</title>
  <link rel="stylesheet" href="../asset/ad.css">
  <style>
    .card{max-width:1100px;margin:18px auto;padding:18px;background:#fff;border-radius:8px;box-shadow:0 0 8px #ddd}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{border:1px solid #e1e1e1;padding:8px;text-align:left}
    th{background:#f7f7f7}
    .controls{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .pager a,.pager span{display:inline-block;padding:6px 10px;margin:0 3px;border:1px solid #ccc;border-radius:6px;text-decoration:none;color:#333}
    .pager .active{background:#2c3e50;color:#fff;border-color:#2c3e50}
    .muted{color:#666}
  </style>
</head>
<body>
<div class="card">
  <h1>Pagination</h1>

  <form method="GET" action="" class="controls">
    <label>Dataset:
      <select name="dataset" onchange="this.form.page.value=1;this.form.submit()">
        <?php foreach (['vehicles'=>'Vehicles','users'=>'Users','bookings'=>'Bookings'] as $k=>$v): ?>
          <option value="<?= h($k) ?>" <?= ($dataset===$k)?'selected':'' ?>><?= h($v) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Items per page:
      <select name="per" onchange="this.form.page.value=1;this.form.submit()">
        <?php foreach ([5,10,25,50,100] as $opt): ?>
          <option value="<?= $opt ?>" <?= ($per===$opt)?'selected':'' ?>><?= $opt ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <input type="hidden" name="page" value="<?= (int)$page ?>">

    <a class="pager" href="<?= h(baseUrl().'?'.q(['dataset'=>$dataset,'per'=>$per,'page'=>1])) ?>">⟵ First</a>
    <a class="pager" href="<?= h(baseUrl().'?'.q(['dataset'=>$dataset,'per'=>$per,'page'=>max(1,$page-1)])) ?>">‹ Prev</a>
    <span class="pager active">Page <?= (int)$page ?> / <?= (int)$totalPages ?> (<?= (int)$total ?> total)</span>
    <a class="pager" href="<?= h(baseUrl().'?'.q(['dataset'=>$dataset,'per'=>$per,'page'=>min($totalPages,$page+1)])) ?>">Next ›</a>
    <a class="pager" href="<?= h(baseUrl().'?'.q(['dataset'=>$dataset,'per'=>$per,'page'=>$totalPages])) ?>">Last ⟶</a>
    <button type="button" onclick="window.location.href='admin_dashboard.php'">Back to Dashboard</button>
  </form>

  <?php if ($dataset === 'vehicles'): ?>
    <table>
      <thead>
        <tr>
          <th style="width:10%">ID</th>
          <th style="width:30%">Make & Model</th>
          <th style="width:10%">Year</th>
          <th style="width:20%">Status</th>
          <th style="width:15%">Daily Rate</th>
          <th style="width:15%">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6">No vehicles found.</td></tr>
        <?php else: foreach ($rows as $v): ?>
          <tr>
            <td><?= (int)$v['id'] ?></td>
            <td><?= h($v['make'].' '.$v['model']) ?></td>
            <td><?= h($v['model_year']) ?></td>
            <td><?= h($v['status']) ?></td>
            <td><?= number_format((float)$v['daily_rate'], 2) ?></td>
            <td><a href="booking_calendar.php?vehicle_id=<?= (int)$v['id'] ?>">Book</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>

  <?php elseif ($dataset === 'users'): ?>
    <table>
      <thead>
        <tr>
          <th style="width:10%">ID</th>
          <th style="width:30%">Username</th>
          <th style="width:35%">Email</th>
          <th style="width:25%">Role</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="4">No users found.</td></tr>
        <?php else: foreach ($rows as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= h($u['username']) ?></td>
            <td><?= h($u['email']) ?></td>
            <td><?= h($u['role'] ?? 'User') ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>

  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th style="width:10%">Booking#</th>
          <th style="width:25%">User</th>
          <th style="width:30%">Vehicle</th>
          <th style="width:20%">Dates</th>
          <th style="width:15%">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="5">No bookings found.</td></tr>
        <?php else: foreach ($rows as $b): ?>
          <tr>
            <td><?= (int)$b['id'] ?></td>
            <td><?= h($b['username']) ?></td>
            <td><?= h($b['make'].' '.$b['model']) ?></td>
            <td><?= h($b['pickup_date'].' → '.$b['return_date']) ?></td>
            <td><?= h($b['status']) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  <?php endif; ?>


  <div class="controls" style="margin-top:12px;">
    <a class="pager" href="<?= h(baseUrl().'?'.q(['dataset'=>$dataset,'per'=>$per,'page'=>1])) ?>">⟵ First</a>
    <a class="pager" href="<?= h(baseUrl().'?'.q(['dataset'=>$dataset,'per'=>$per,'page'=>max(1,$page-1)])) ?>">‹ Prev</a>
    <span class="pager active">Page <?= (int)$page ?> / <?= (int)$totalPages ?> (<?= (int)$total ?> total)</span>
    <a class="pager" href="<?= h(baseUrl().'?'.q(['dataset'=>$dataset,'per'=>$per,'page'=>min($totalPages,$page+1)])) ?>">Next ›</a>
    <a class="pager" href="<?= h(baseUrl().'?'.q(['dataset'=>$dataset,'per'=>$per,'page'=>$totalPages])) ?>">Last ⟶</a>
  </div>
</div>
</body>
</html>
