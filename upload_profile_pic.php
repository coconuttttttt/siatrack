<?php
session_start();
include 'db_connect.php';

if ($_FILES['file']['name']) {
    $type = $_POST['user_type'];
    $id = ($type == 'teacher') ? $_SESSION['teacher_id'] : $_SESSION['admin_id'];
    $table = ($type == 'teacher') ? 'faculty' : 'admins'; // I-adjust base sa table name mo
    $pk = ($type == 'teacher') ? 'teacher_id' : 'admin_id';

    $filename = time() . '_' . $_FILES['file']['name'];
    $location = "uploads/" . $filename;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
        $sql = "UPDATE $table SET profile_pic = ? WHERE $pk = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $filename, $id);
        $stmt->execute();
        echo "Success";
    }
}
?>