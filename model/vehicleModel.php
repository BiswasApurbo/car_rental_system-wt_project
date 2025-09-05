<?php
require_once(__DIR__ . '/db.php'); 

function _dieOnError($con, $rsOrBool){
    if ($rsOrBool === false) {
        die('SQL error: ' . mysqli_error($con));
    }
}

function parsePriceRange($s){
    if (!is_string($s) || !preg_match('/^\d+\-\d+$/', $s)) return [null,null];
    [$a,$b] = array_map('intval', explode('-', $s, 2));
    if ($a > $b) return [null,null];
    return [$a,$b];
}


function getVehicleTypes(){
    $con = getConnection();
    $sql = "SELECT id, name FROM vehicle_types ORDER BY name";
    $rs  = mysqli_query($con, $sql);
    _dieOnError($con, $rs);
    $out = [];
    while ($row = mysqli_fetch_assoc($rs)) $out[] = $row;
    return $out;
}

function getFeatures(){
    $con = getConnection();
    $sql = "SELECT id, name FROM features ORDER BY name";
    $rs  = mysqli_query($con, $sql);
    _dieOnError($con, $rs);
    $out = [];
    while ($row = mysqli_fetch_assoc($rs)) $out[] = $row;
    return $out;
}

function countVehiclesFiltered($filters){
    $con = getConnection();

    $where  = [];
    $types  = "";
    $params = [];
    $joinFeature = false;

    if (!empty($filters['type_id'])) {
        $where[] = "v.type_id = ?";
        $types  .= "i";
        $params[] = (int)$filters['type_id'];
    }

    if (!empty($filters['feature_id'])) {
        $joinFeature = true;
        $where[] = "vf.feature_id = ?";
        $types  .= "i";
        $params[] = (int)$filters['feature_id'];
    }

    if (!empty($filters['price'])) {
        [$min,$max] = parsePriceRange($filters['price']);
        if ($min !== null && $max !== null) {
            $where[] = "v.daily_rate BETWEEN ? AND ?";
            $types  .= "dd";
            $params[] = $min;
            $params[] = $max;
        }
    }

    $sql = "SELECT COUNT(DISTINCT v.id) AS total
            FROM vehicles v";
    $sql .= $joinFeature ? " JOIN vehicle_features vf ON vf.vehicle_id = v.id"
                         : " LEFT JOIN vehicle_features vf ON vf.vehicle_id = v.id";
    if ($where) $sql .= " WHERE " . implode(" AND ", $where);

    $stmt = mysqli_prepare($con, $sql);
    if ($types !== "") mysqli_stmt_bind_param($stmt, $types, ...$params);
    $ok = mysqli_stmt_execute($stmt);
    _dieOnError($con, $ok ? true : false);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    return (int)($row['total'] ?? 0);
}

function getVehiclesFiltered($filters, $page = 1, $perPage = 12){
    $con = getConnection();

    $where  = [];
    $types  = "";
    $params = [];
    $joinFeature = false;

    if (!empty($filters['type_id'])) {
        $where[] = "v.type_id = ?";
        $types  .= "i";
        $params[] = (int)$filters['type_id'];
    }

    if (!empty($filters['feature_id'])) {
        $joinFeature = true;
        $where[] = "vf.feature_id = ?";
        $types  .= "i";
        $params[] = (int)$filters['feature_id'];
    }

    if (!empty($filters['price'])) {
        [$min,$max] = parsePriceRange($filters['price']);
        if ($min !== null && $max !== null) {
            $where[] = "v.daily_rate BETWEEN ? AND ?";
            $types  .= "dd";
            $params[] = $min;
            $params[] = $max;
        }
    }

    $sql = "SELECT v.id, v.make, v.model, v.model_year, v.daily_rate, v.seats, v.transmission, v.fuel_type,
                   t.name AS type_name,
                   (SELECT path FROM vehicle_images WHERE vehicle_id = v.id AND is_primary = 1 LIMIT 1) AS img
            FROM vehicles v
            JOIN vehicle_types t ON t.id = v.type_id";
    $sql .= $joinFeature ? " JOIN vehicle_features vf ON vf.vehicle_id = v.id"
                         : " LEFT JOIN vehicle_features vf ON vf.vehicle_id = v.id";
    if ($where) $sql .= " WHERE " . implode(" AND ", $where);
    $sql .= " GROUP BY v.id
              ORDER BY v.created_at DESC
              LIMIT ? OFFSET ?";

    $types  .= "ii";
    $params[] = (int)$perPage;
    $params[] = (int)(($page - 1) * $perPage);

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    $ok = mysqli_stmt_execute($stmt);
    _dieOnError($con, $ok ? true : false);
    $res = mysqli_stmt_get_result($stmt);

    $out = [];
    while ($row = mysqli_fetch_assoc($res)) $out[] = $row;
    return $out;
}
