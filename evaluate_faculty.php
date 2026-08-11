<?php
session_start();
include 'db_connect.php';

// PROTEKSYON: Kung walang student_id, bawal pumasok.
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$student_strand = $_SESSION['student_strand'] ?? ''; // Naka-save ito dapat galing sa login

$success_msg = "";

// SUBMIT LOGIC
if (isset($_POST['submit_eval'])) {
    $teacher_id = $_POST['teacher_id'];
    $eng = $_POST['engagement'];
    $eff = $_POST['effectiveness'];
    $punc = $_POST['punctuality'];
    $comments = $_POST['comments'];

    $stmt = $conn->prepare("INSERT INTO evaluations (student_id, teacher_id, engagement, effectiveness, punctuality, comments) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiiis", $student_id, $teacher_id, $eng, $eff, $punc, $comments);
    if ($stmt->execute()) {
        $success_msg = "Evaluation submitted successfully!";
    }
}

// QUERY TEACHERS: Hanapin ang teachers na kapareho ng STRAND ng student
// At siguraduhin na hindi pa na-evaluate ng student na 'to.
$teacher_query = "SELECT DISTINCT f.teacher_id, f.full_name, c.subject_name 
                  FROM faculty f
                  JOIN classes c ON f.teacher_id = c.teacher_id
                  WHERE TRIM(c.course_section) = TRIM(?) 
                  AND f.teacher_id NOT IN (SELECT teacher_id FROM evaluations WHERE student_id = ?)";

$stmt_t = $conn->prepare($teacher_query);
$stmt_t->bind_param("si", $student_strand, $student_id);
$stmt_t->execute();
$teachers = $stmt_t->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SIATRACK | Evaluate</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Poppins', sans-serif; padding: 30px; }
        .container { max-width: 700px; margin: auto; }
        .header { background: #cc0000; color: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; text-align: center; }
        .eval-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 20px; border-left: 6px solid #ffcc00; }
        .stars { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 10px; margin: 10px 0; }
        .stars input { display: none; }
        .stars label { font-size: 24px; color: #ddd; cursor: pointer; }
        .stars input:checked ~ label { color: #ffcc00; }
        textarea { width: 100%; border: 1px solid #ddd; padding: 10px; border-radius: 8px; margin-top: 10px; }
        .btn-submit { background: #0033cc; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; margin-top: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Faculty Evaluation</h2>
        <p>Student: <b><?php echo $student_name; ?></b> | Strand: <?php echo $student_strand; ?></p>
        <a href="logout.php" style="color: white; font-size: 12px;">Logout</a>
    </div>

    <?php if($success_msg) echo "<div style='color:green; margin-bottom:15px;'>$success_msg</div>"; ?>

    <?php if($teachers->num_rows > 0): ?>
        <?php while($row = $teachers->fetch_assoc()): ?>
            <div class="eval-card">
                <form action="" method="POST">
                    <input type="hidden" name="teacher_id" value="<?php echo $row['teacher_id']; ?>">
                    <h3 style="margin:0;"><?php echo $row['full_name']; ?></h3>
                    <p style="font-size: 13px; color: #666;"><?php echo $row['subject_name']; ?></p>
                    
                    <label style="font-size: 13px;">Engagement:</label>
                    <div class="stars">
                        <?php for($i=5;$i>=1;$i--): ?>
                        <input type="radio" name="engagement" id="eng-<?php echo $row['teacher_id'].'-'.$i; ?>" value="<?php echo $i; ?>" required>
                        <label for="eng-<?php echo $row['teacher_id'].'-'.$i; ?>"><i class="fa-solid fa-star"></i></label>
                        <?php endfor; ?>
                    </div>

                    <label style="font-size: 13px;">Effectiveness:</label>
                    <div class="stars">
                        <?php for($i=5;$i>=1;$i--): ?>
                        <input type="radio" name="effectiveness" id="eff-<?php echo $row['teacher_id'].'-'.$i; ?>" value="<?php echo $i; ?>" required>
                        <label for="eff-<?php echo $row['teacher_id'].'-'.$i; ?>"><i class="fa-solid fa-star"></i></label>
                        <?php endfor; ?>
                    </div>

                    <label style="font-size: 13px;">Punctuality:</label>
                    <div class="stars">
                        <?php for($i=5;$i>=1;$i--): ?>
                        <input type="radio" name="punctuality" id="punc-<?php echo $row['teacher_id'].'-'.$i; ?>" value="<?php echo $i; ?>" required>
                        <label for="punc-<?php echo $row['teacher_id'].'-'.$i; ?>"><i class="fa-solid fa-star"></i></label>
                        <?php endfor; ?>
                    </div>

                    <textarea name="comments" placeholder="Your feedback..." required></textarea>
                    <button type="submit" name="submit_eval" class="btn-submit">Submit Evaluation</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="eval-card" style="text-align:center;">
            <i class="fa-solid fa-circle-check" style="font-size: 40px; color: #28a745;"></i>
            <p>Wala ka nang pending na evaluation o walang teacher na naka-assign sa strand mo.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>