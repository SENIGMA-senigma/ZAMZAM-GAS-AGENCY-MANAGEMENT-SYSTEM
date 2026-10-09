<?php
session_start();
require_once 'config.php';

if ($_SESSION['user_role'] !== 'Manager') exit("Access Denied");

if (isset($_POST['download_report'])) {
    $type = $_POST['report_type']; // 'monthly' or 'yearly'
    $year = intval($_POST['year']);
    $month = isset($_POST['month']) ? intval($_POST['month']) : null;

    // 1. Build the Query based on selection
    if ($type == 'monthly') {
        $filename = "Monthly_Report_" . date('F', mktime(0,0,0,$month)) . "_$year.csv";
        $sql = "SELECT order_date, customer_name, cylinder_type, total_amount, approval_status 
                FROM order_records 
                WHERE YEAR(order_date) = $year AND MONTH(order_date) = $month";
    } else {
        $filename = "Yearly_Report_$year.csv";
        $sql = "SELECT order_date, customer_name, cylinder_type, total_amount, approval_status 
                FROM order_records 
                WHERE YEAR(order_date) = $year";
    }

    $result = $conn->query($sql);

    // 2. Set Headers to trigger Download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    // 3. Open output stream and write data
    $output = fopen('php://output', 'w');

    // Add Column Headers to CSV
    fputcsv($output, array('Date', 'Customer', 'Cylinder Size', 'Amount (KES)', 'Status'));

    // Fetch and write data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}
?>