<?php
session_start();
require_once('../model/userModel.php');
require_once('../model/paginationModel.php');

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$dataset = $_POST['dataset'] ?? 'users';
$per = (int)($_POST['per'] ?? 10);
$page = max(1,(int)($_POST['page'] ?? 1));

// Determine total and fetch rows
switch($dataset){
    case 'users':
        $total = pg_count_users();
        $rows = pg_fetch_users($per, ($page-1)*$per);
        $head = '<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>';
        break;
    case 'bookings':
        $total = pg_count_bookings();
        $rows = pg_fetch_bookings($per, ($page-1)*$per);
        $head = '<tr><th>Booking#</th><th>User</th><th>Vehicle</th><th>Dates</th><th>Status</th></tr>';
        break;
    case 'vehicles':
    default:
        $total = pg_count_vehicles();
        $rows = pg_fetch_vehicles($per, ($page-1)*$per);
        $head = '<tr><th>ID</th><th>Make & Model</th><th>Year</th><th>Status</th><th>Daily Rate</th><th>Actions</th></tr>';
        break;
}

$totalPages = max(1, ceil($total/$per));

// Generate table rows
$html = '';
if(!$rows){
    $colspan = substr_count($head,'<th>');
    $html .= '<tr><td colspan="'.$colspan.'">No data found.</td></tr>';
}else{
    foreach($rows as $r){
        if($dataset==='users'){
            $html .= '<tr><td>'.(int)$r['id'].'</td><td>'.h($r['username']).'</td><td>'.h($r['email']).'</td><td>'.h($r['role']??'User').'</td></tr>';
        }elseif($dataset==='bookings'){
            $html .= '<tr><td>'.(int)$r['id'].'</td><td>'.h($r['username']).'</td><td>'.h($r['make'].' '.$r['model']).'</td><td>'.h($r['pickup_date'].' → '.$r['return_date']).'</td><td>'.h($r['status']).'</td></tr>';
        }else{
            $html .= '<tr><td>'.(int)$r['id'].'</td><td>'.h($r['make'].' '.$r['model']).'</td><td>'.h($r['model_year']).'</td><td>'.h($r['status']).'</td><td>'.number_format((float)$r['daily_rate'],2).'</td><td><a href="booking_calendar.php?vehicle_id='.(int)$r['id'].'">Book</a></td></tr>';
        }
    }
}

// Pagination links
$paginationHtml = '';
for($p=1;$p<=$totalPages;$p++){
    if($p==$page) $paginationHtml .= '<span class="pager active">'.$p.'</span>';
    else $paginationHtml .= '<a class="pager" onclick="goToPage('.$p.')">'.$p.'</a>';
}

echo json_encode([
    'status'=>'success',
    'head'=>$head,
    'html
