<?php
session_start();
include 'db_connect.php';

if (isset($_POST['subject']) && isset($_SESSION['teacher_id'])) {
    $subject = $_POST['subject'];
    $teacher_id = $_SESSION['teacher_id'];
    $today = date('Y-m-d');

    // Buburahin lang ang attendance para sa subject na ito, ni teacher na ito, ngayong araw.
    $query = "DELETE FROM attendance WHERE subject = ? AND teacher_id = ? AND date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sis", $subject, $teacher_id, $today);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Attendance has been reset for this session.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to reset attendance.']);
    }
    $stmt->close();
}
$conn->close();
?>