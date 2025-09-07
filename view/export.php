<?php
session_start();
require_once('../model/userModel.php');
require_once('../model/exportModel.php');
require_once(__DIR__ . '/../lib/fpdf/fpdf.php'); 

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

$username = $_SESSION['username'] ?? '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from   = $_POST['dateFrom'];
    $to     = $_POST['dateTo'];
    $format = $_POST['format'];


    $data = [
        "Bookings"               => getBookings($from, $to, $username),
        "Customer Profiles"      => getCustomerProfiles($from, $to, $username),
        "Insurance Records"      => getInsuranceRecords($from, $to, $username),
        "Loyalty Points"         => getLoyaltyPoints($from, $to, $username),
        "Vehicle Damage Reports" => getVehicleDamageReports($from, $to, $username),
    ];

    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="export.csv"');
        $out = fopen('php://output', 'w');

        foreach ($data as $section => $rows) {
            if (!empty($rows)) {
                fputcsv($out, [$section]);
                fputcsv($out, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }
                fputcsv($out, []);
            }
        }
        fclose($out);
        exit;

    } elseif ($format === 'pdf') {
    $pdf = new FPDF();
    $pdf->AddPage();

    foreach ($data as $section => $rows) {
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,$section,0,1,'C');
        $pdf->Ln(2);

        if (!empty($rows)) {
            $pdf->SetFont('Arial','B',10);
            $headers = array_keys($rows[0]);
            $colWidth = 190 / count($headers);


            foreach ($headers as $header) {
                $pdf->Cell($colWidth, 7, $header, 1, 0, 'C');
            }
            $pdf->Ln();

            $pdf->SetFont('Arial','',9);


            foreach ($rows as $row) {
                foreach ($row as $cell) {
                    if (is_array($cell)) $cell = json_encode($cell);
                    $pdf->MultiCell($colWidth, 6, $cell, 1);

                    $pdf->SetX($pdf->GetX() + $colWidth);
                }
                $pdf->Ln();
            }

        } else {
            $pdf->SetFont('Arial','I',10);
            $pdf->Cell(0, 6, "No records found.", 1, 1, 'C');
        }

        $pdf->Ln(5);
    }

    $pdf->Output('D', 'export.pdf');
    exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Data Export</title>
  <link rel="stylesheet" href="../asset/export.css">
</head>
<body>

<div class="export-container">
    <h2>Export Data</h2>

    <form method="POST">
        <label for="dateFrom">From:</label>
        <input type="date" id="dateFrom" name="dateFrom" required>

        <label for="dateTo">To:</label>
        <input type="date" id="dateTo" name="dateTo" required>

        <label for="format">Select Format:</label>
        <select id="format" name="format" required>
            <option value="csv">CSV</option>
        </select>

        <label for="schedule">Schedule Export:</label>
        <select id="schedule" name="schedule">
            <option value="none">None</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
        </select>

        <input type="submit" value="Download">
    </form>
</div>

</body>
</html>