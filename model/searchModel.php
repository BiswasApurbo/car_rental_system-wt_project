<?php
require_once(__DIR__ . '/db.php');

function search_all($q = '', $category = 'All', $status = 'All', $limit = 50) {
    $q = trim((string)$q);
    $category = (string)$category;
    $status = (string)$status;
    $limit = max(1, min(200, (int)$limit));

    $out = [];


    $like = function($col) { return "$col LIKE ?"; };
    $wrap = function($s){ return '%' . $s . '%'; };

    $con = getConnection();


    if ($category === 'All' || $category === 'Vehicles') {
        $sql = "SELECT id, make, model, status, daily_rate
                  FROM vehicles
                 WHERE 1=1";
        $params = [];
        $types  = '';

        if ($q !== '') {
            $sql .= " AND (". $like('make') ." OR ". $like('model') ." OR ". $like('reg_no') .")";
            $params[] = $wrap($q); $params[] = $wrap($q); $params[] = $wrap($q);
            $types .= 'sss';
        }
        if ($status !== '' && $status !== 'All') {
            $sql .= " AND status = ?";
            $params[] = $status;
            $types .= 's';
        }
        $sql .= " ORDER BY make, model LIMIT ?";
        $params[] = $limit; $types .= 'i';

        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($r = mysqli_fetch_assoc($res)) {
            $out[] = [
                'category' => 'Vehicles',
                'name'     => $r['make'].' '.$r['model'],
                'status'   => $r['status'],
                'extra'    => 'Rate: '.number_format((float)$r['daily_rate'],2),
                'link'     => 'booking_calendar.php?vehicle_id='.(int)$r['id'],
            ];
        }
    }

    
    if ($category === 'All' || $category === 'Users') {
        $sql = "SELECT id, username, email, role FROM users WHERE 1=1";
        $params = [];
        $types  = '';
        if ($q !== '') {
            $sql .= " AND (". $like('username') ." OR ". $like('email') .")";
            $params[] = $wrap($q); $params[] = $wrap($q);
            $types .= 'ss';
        }
    
        $sql .= " ORDER BY username LIMIT ?";
        $params[] = $limit; $types .= 'i';

        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($r = mysqli_fetch_assoc($res)) {
            $out[] = [
                'category' => 'Users',
                'name'     => $r['username'],
                'status'   => $r['role'] ?: 'User',
                'extra'    => $r['email'] ?: '',
                'link'     => 'editUser.php?id='.(int)$r['id'],
            ];
        }
    }

    if ($category === 'All' || $category === 'Bookings') {
        $sql = "SELECT b.id, b.status, b.pickup_date, b.return_date, u.username, v.make, v.model
                  FROM bookings b
                  JOIN users u ON u.id = b.user_id
                  JOIN vehicles v ON v.id = b.vehicle_id
                 WHERE 1=1";
        $params = [];
        $types  = '';

        if ($q !== '') {
            $sql .= " AND (". $like('u.username') ." OR ". $like('v.make') ." OR ". $like('v.model') ." OR ". $like('b.id') .")";
            $params[] = $wrap($q); $params[] = $wrap($q); $params[] = $wrap($q); $params[] = $wrap($q);
            $types .= 'ssss';
        }
        if ($status !== '' && $status !== 'All') {
            $sql .= " AND b.status = ?";
            $params[] = $status; $types .= 's';
        }
        $sql .= " ORDER BY b.id DESC LIMIT ?";
        $params[] = $limit; $types .= 'i';

        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($r = mysqli_fetch_assoc($res)) {
            $out[] = [
                'category' => 'Bookings',
                'name'     => 'Booking #'.$r['id'].' — '.$r['make'].' '.$r['model'].' for '.$r['username'],
                'status'   => $r['status'],
                'extra'    => $r['pickup_date'].' → '.$r['return_date'],
                'link'     => '#', 
            ];
        }
    }

    return $out;
}
