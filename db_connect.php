<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "siatrack_db"; // PALITAN MO ITO: Gawaing 'siatrack_db' para mag-match sa VM

$conn = new mysqli($host, $user, $pass, $dbname);

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
?>