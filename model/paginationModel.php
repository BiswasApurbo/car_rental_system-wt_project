<?php
require_once(__DIR__ . '/db.php');


function pg_count_vehicles(): int {
    $con = getConnection();
    $res = mysqli_query($con, "SELECT COUNT(*) AS c FROM vehicles");
    $row = mysqli_fetch_assoc($res);
    return (int)($row['c'] ?? 0);
}
function pg_fetch_vehicles(int $limit, int $offset): array {
    $con = getConnection();
    $limit = max(1, min(200, $limit));
    $offset = max(0, $offset);
    $stmt = mysqli_prepare(
        $con,
        "SELECT id, make, model, model_year, status, daily_rate
           FROM vehicles
          ORDER BY make, model
          LIMIT ? OFFSET ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    return $rows;
}

function pg_count_users(): int {
    $con = getConnection();
    $res = mysqli_query($con, "SELECT COUNT(*) AS c FROM users");
    $row = mysqli_fetch_assoc($res);
    return (int)($row['c'] ?? 0);
}
function pg_fetch_users(int $limit, int $offset): array {
    $con = getConnection();
    $limit = max(1, min(200, $limit));
    $offset = max(0, $offset);
    $stmt = mysqli_prepare(
        $con,
        "SELECT id, username, email, role
           FROM users
          ORDER BY username
          LIMIT ? OFFSET ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    return $rows;
}

function pg_count_bookings(): int {
    $con = getConnection();
    $res = mysqli_query($con, "SELECT COUNT(*) AS c FROM bookings");
    $row = mysqli_fetch_assoc($res);
    return (int)($row['c'] ?? 0);
}
function pg_fetch_bookings(int $limit, int $offset): array {
    $con = getConnection();
    $limit = max(1, min(200, $limit));
    $offset = max(0, $offset);
    $stmt = mysqli_prepare(
        $con,
        "SELECT b.id,
                b.status,
                b.pickup_date,
                b.return_date,
                u.username,
                v.make, v.model
           FROM bookings b
           JOIN users u    ON u.id = b.user_id
           JOIN vehicles v ON v.id = b.vehicle_id
          ORDER BY b.id DESC
          LIMIT ? OFFSET ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    return $rows;
}
