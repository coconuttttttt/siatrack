<?php
include 'db_connect.php';

if(isset($_GET['id'])) {
    $teacher_id = $_GET['id'];
    
    // Kunin lahat ng subjects at sections ni teacher
    $query = "SELECT subject_name, course_section FROM classes WHERE teacher_id = ? ORDER BY course_section ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        echo "<ul style='list-style:none; padding:0;'>";
        while($row = $result->fetch_assoc()) {
            echo "<li style='padding: 10px; border-bottom: 1px solid #ddd; font-size: 14px;'>
                    <i class='fa-solid fa-book-bookmark' style='color:#ffcc00; margin-right:10px;'></i>
                    <strong>" . htmlspecialchars($row['subject_name']) . "</strong><br>
                    <small style='color:#666; margin-left:25px;'>Class: " . htmlspecialchars($row['course_section']) . "</small>
                  </li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='text-align:center; padding:20px; color:#999;'>No subjects or strands assigned to this teacher yet.</p>";
    }
    $stmt->close();
}
?>