<?php
include 'db_connect.php';

// I-set ang headers para maging downloadable CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Siatrack_Students_List_'.date('Y-m-d').'.csv');

// Buksan ang "output stream"
$output = fopen('php://output', 'w');

// I-set ang column headers sa Excel
fputcsv($output, array('NFC UID', 'First Name', 'Middle Name', 'Last Name', 'Suffix', 'Strand & Section', 'Parent Contact', 'Password'));

// Kunin ang data mula sa database
$query = "SELECT nfc_uid, first_name, middle_name, last_name, suffix, course_section, parent_contact, password FROM students ORDER BY course_section ASC, last_name ASC";
$result = $conn->query($query);

while($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>