<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['student_id'];
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $contact = $_POST['contact'];

    $query = "UPDATE students SET first_name = ?, last_name = ?, contact = ? WHERE student_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $fname, $lname, $contact, $id);

    if ($stmt->execute()) {
        header("Location: manage_students.php?success=updated");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>