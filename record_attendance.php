<?php
session_start();
include 'db_connect.php';

// Set timezone para tama ang oras sa Pilipinas
date_default_timezone_set('Asia/Manila');

// 1. LOGIC PARA SA DASHBOARD (Manual Click via POST)
if (isset($_POST['student_id'])) {
    header('Content-Type: application/json'); // Importante para sa Dashboard AJAX
    
    $student_id = $_POST['student_id'];
    $type = $_POST['type']; // 'IN' or 'OUT'
    $subject = $_POST['subject'];
    $teacher_id = $_SESSION['teacher_id'];
    $today = date('Y-m-d');

    if ($type === 'IN') {
        // I-check kung may record na para sa subject na ito ngayong araw
        $check = $conn->prepare("SELECT attendance_id FROM attendance WHERE student_id = ? AND subject = ? AND date = ?");
        $check->bind_param("iss", $student_id, $subject, $today);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Already timed-in for this subject today.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO attendance (student_id, teacher_id, subject, date, time_in, status) VALUES (?, ?, ?, ?, NOW(), 'Present')");
        $stmt->bind_param("iiss", $student_id, $teacher_id, $subject, $today);
    } else {
        // UPDATE para sa Time Out
        $stmt = $conn->prepare("UPDATE attendance SET time_out = NOW() WHERE student_id = ? AND subject = ? AND date = ? AND time_out IS NULL");
        $stmt->bind_param("iss", $student_id, $subject, $today);
    }

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => "Attendance $type recorded successfully!"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No active Time-In record found to Time-Out.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    exit;
}

// 2. LOGIC PARA SA NFC HARDWARE (via GET nfc_uid)
if (isset($_GET['nfc_uid'])) {
    $nfc_uid = $_GET['nfc_uid'];
    $today = date('Y-m-d');

    // Hanapin yung estudyante base sa NFC UID
    $stmt = $conn->prepare("SELECT student_id, first_name FROM students WHERE nfc_uid = ?");
    $stmt->bind_param("s", $nfc_uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $student_id = $student['student_id'];
        
        // I-check ang huling attendance record ngayong araw
        $check_att = $conn->prepare("SELECT attendance_id, time_out FROM attendance WHERE student_id = ? AND date = ? ORDER BY attendance_id DESC LIMIT 1");
        $check_att->bind_param("is", $student_id, $today);
        $check_att->execute();
        $att_result = $check_att->get_result();

        if ($att_result->num_rows > 0) {
            $att_row = $att_result->fetch_assoc();
            
            // Kung wala pang Time Out, i-update ito
            if (is_null($att_row['time_out'])) {
                $update = $conn->prepare("UPDATE attendance SET time_out = NOW() WHERE attendance_id = ?");
                $update->bind_param("i", $att_row['attendance_id']);
                echo $update->execute() ? "Success: Time Out recorded for " . $student['first_name'] : "Error saving Time Out.";
            } else {
                echo "Notice: " . $student['first_name'] . " already completed attendance today.";
            }
        } else {
            // Kung wala pang record, i-insert bilang Time In
            // Note: Sa hardware scan, kailangan mo ring i-consider kung paano ita-tag ang 'subject' at 'teacher_id'
            $insert = $conn->prepare("INSERT INTO attendance (student_id, date, time_in, status) VALUES (?, ?, NOW(), 'Present')");
            $insert->bind_param("is", $student_id, $today);
            echo $insert->execute() ? "Success: Time In recorded for " . $student['first_name'] : "Error saving Time In.";
        }
    } else {
        echo "Error: NFC Card not registered.";
    }
    exit;
}

echo "Invalid Request: No data received.";
?>