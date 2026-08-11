<?php
// save_evaluation.php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $student_id = $_POST['student_id'];
    $teacher_id = $_POST['teacher_id'];
    $engagement = $_POST['engagement'];
    $effectiveness = $_POST['effectiveness'];
    $punctuality = $_POST['punctuality'];

    $stmt = $conn->prepare("INSERT INTO evaluations (student_id, teacher_id, engagement_score, effectiveness_score, punctuality_score) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiii", $student_id, $teacher_id, $engagement, $effectiveness, $punctuality);

    if ($stmt->execute()) {
        echo "<script>
                alert('Evaluation submitted successfully! Maraming salamat.');
                window.location.href = 'dashboard.php';
              </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Bawal i-access ang page na ito ng direkta.";
}

$conn->close();
?>