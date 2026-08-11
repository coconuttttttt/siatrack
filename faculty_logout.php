<?php
session_start();

// Burahin lahat ng session data para ma-logout ang teacher
session_unset();
session_destroy();

// I-redirect siya pabalik sa Faculty Login page
header("Location: faculty_login.php");
exit();
?>