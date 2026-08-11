<?php
session_start();
include 'db_connect.php';

// Siguraduhin na naka-login ang teacher at may napiling subject
if (!isset($_SESSION['teacher_id']) || !isset($_GET['subject'])) {
    die("Access Denied or Subject not specified.");
}

$teacher_id = $_SESSION['teacher_id'];
$active_subject = $_GET['subject'];
$date_today = date('Y-m-d');
$display_date = date('F d, Y');

// I-set ang headers para maging downloadable CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Attendance_Log_'.$active_subject.'_'.$date_today.'.csv"');

// Buksan ang "output stream"
$output = fopen('php://output', 'w');

// --- ITO ANG SOLUSYON PARA SA EXCEL LAYOUT ---
// Maglagay ng UTF-8 BOM para automatic na maghiwalay ang columns sa Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// I-set ang main headers ng report
fputcsv($output, array('SIATRACK DAILY ATTENDANCE REPORT'));
fputcsv($output, array('Subject:', $active_subject));
fputcsv($output, array('Date:', $display_date));
fputcsv($output, array('')); // Blank row para sa spacing

// Header ng mismong attendance table
fputcsv($output, array('NO.', 'STUDENT NAME', 'TIME IN', 'TIME OUT', 'STATUS'));

// Kunin ang attendance data mula sa database
$query = "SELECT s.last_name, s.first_name, a.time_in, a.time_out, a.status 
          FROM attendance a 
          JOIN students s ON a.student_id = s.student_id 
          WHERE a.teacher_id = ? 
          AND a.subject = ? 
          AND a.date = ?
          ORDER BY s.last_name ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $teacher_id, $active_subject, $date_today);
$stmt->execute();
$result = $stmt->get_result();

$i = 1;
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Gawing Uppercase ang pangalan para maging formal tignan
        $full_name = strtoupper($row['last_name'] . ", " . $row['first_name']);
        
        // I-format ang oras nang maayos (halimbawa: 08:30 AM)
        $t_in = date("h:i A", strtotime($row['time_in']));
        
        // I-check kung may Time Out na, kung wala ay "---" ang ilalagay para malinis
        $t_out = ($row['time_out'] && $row['time_out'] != '00:00:00') ? date("h:i A", strtotime($row['time_out'])) : '---';
        
        // I-output ang bawat row sa CSV
        fputcsv($output, array(
            $i++, 
            $full_name, 
            $t_in, 
            $t_out, 
            $row['status']
        ));
    }
} else {
    // Kapag walang records na nahanap
    fputcsv($output, array('', 'NO RECORDS FOUND', '', '', ''));
}

fclose($output);
exit();
?>