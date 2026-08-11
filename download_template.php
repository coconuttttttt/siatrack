<?php
// download_template.php

// I-set ang headers para maging downloadable CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Siatrack_Student_Import_Template.csv');

// Buksan ang "output stream"
$output = fopen('php://output', 'w');

// I-set ang column headers base sa format na gusto natin
fputcsv($output, array('NFC UID', 'First Name', 'Middle Name', 'Last Name', 'Suffix', 'Parent Contact'));

// Maglagay ng isang sample row para gabay ni teacher (optional)
fputcsv($output, array('1234567890', 'Juan', 'Protacio', 'Dela Cruz', 'Jr', '09123456789'));

fclose($output);
exit();
?>