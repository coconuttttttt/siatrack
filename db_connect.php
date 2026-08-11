<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "siatrack_db";
$port = 3306;

$conn = new mysqli($host, $user, $pass, $dbname, $port);

$passwords_to_try = ["", "root", "admin", "1234"];

$conn = null;

foreach ($passwords_to_try as $pwd) {
    try {
        $conn = @new mysqli($host, $user, $pwd, $dbname);
        if (!$conn->connect_error) {
            break;
        }
    } catch (Exception $e) {
    }
}

if (!$conn || $conn->connect_error) {
    die("Connection failed: " . ($conn ? $conn->connect_error : "Could not connect to MySQL server. Please check your MySQL password."));
}
?>