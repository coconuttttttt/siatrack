<?php
session_start();
include 'db_connect.php';

// Proteksyon: Kung may naka-login na admin, linisin muna para walang conflict
if (isset($_SESSION['admin_id'])) {
    session_unset();
}

// Kung naka-login na ang student, idirekta agad sa evaluation form
if (isset($_SESSION['student_id'])) {
    header("Location: evaluate_faculty.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nfc_uid = $_POST['nfc_uid'];
    $password = $_POST['password'];

    // KINUHA NA NATIN ANG 'course_section' DITO
    $stmt = $conn->prepare("SELECT student_id, first_name, last_name, course_section FROM students WHERE nfc_uid = ? AND password = ?");
    $stmt->bind_param("ss", $nfc_uid, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        
        // I-SET ANG MGA SESSION VARIABLES
        $_SESSION['student_id'] = $student['student_id'];
        $_SESSION['student_name'] = $student['first_name'] . " " . $student['last_name'];
        
        // ITO ANG MAGTATANGGAL NG ERROR SA EVALUATE PAGE
        $_SESSION['student_strand'] = $student['course_section']; 
        
        header("Location: evaluate_faculty.php");
        exit();
    } else {
        $error = "Invalid NFC UID or Password!";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SIATRACK | Student Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        
        .login-card { background: white; border-radius: 15px; box-shadow: 0px 10px 40px rgba(0,0,0,0.1); width: 100%; max-width: 420px; overflow: hidden; text-align: center; position: relative; }
        
        .login-header-banner { background: url('student.jpg') no-repeat center center; background-size: cover; height: 160px; position: relative; display: flex; justify-content: center; border-bottom: 5px solid #ffcc00;}
        .login-header-banner::before { content:''; position: absolute; top:0; left:0; width:100%; height:100%; background: linear-gradient(to top, rgba(0,51,204,0.85) 0%, rgba(0,0,0,0.2) 100%);}
        
        .school-logo { width: 85px; height: 85px; border-radius: 50%; background: white; padding: 5px; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.2); position: absolute; bottom: -42px; left: 50%; transform: translateX(-50%); z-index: 10;}
        
        .login-body { padding: 60px 30px 30px; }
        h2 { color: #2c3e50; font-size: 24px; margin-bottom: 5px; font-weight: 700; }
        p.subtitle { color: #7f8c8d; font-size: 13px; margin-bottom: 25px;}
        
        .input-group { margin-bottom: 15px; text-align: left; }
        .input-group label { display: block; font-size: 12px; color: #555; margin-bottom: 5px; font-weight: 600; }
        .input-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: 0.3s;}
        .input-group input:focus { border-color: #0033cc; box-shadow: 0 0 8px rgba(0,51,204,0.1); outline: none;}
        
        .btn-login { width: 100%; padding: 15px; background: #cc0000; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 16px; cursor: pointer; transition: 0.3s; margin-top: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-login:hover { background: #990000; transform: translateY(-2px); }
        
        .error-msg { color: #dc3545; font-size: 12px; margin-bottom: 15px; font-weight: 600; background: #f8d7da; padding: 12px; border-radius: 8px; border: 1px solid #f5c6cb; text-align: left; }
        .back-link { display: block; margin-top: 25px; color: #7f8c8d; text-decoration: none; font-size: 13px; font-weight: 600; }
        .back-link:hover { color: #0033cc; }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header-banner">
            <img src="sia_logo.png" alt="SIA Logo" class="school-logo">
        </div>
        
        <div class="login-body">
            <h2>Student Portal</h2>
            <p class="subtitle">Southern Isabela Academy | Evaluation System</p>
            
            <?php if($error != "") echo "<div class='error-msg'><i class='fa-solid fa-triangle-exclamation'></i> $error</div>"; ?>

            <form action="" method="POST">
                <div class="input-group">
                    <label><i class="fa-solid fa-id-card"></i> NFC UID (Username)</label>
                    <input type="text" name="nfc_uid" required placeholder="Scan your card or type UID">
                </div>
                <div class="input-group">
                    <label><i class="fa-solid fa-lock"></i> Password</label>
                    <input type="password" name="password" required placeholder="Enter your password">
                </div>
                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-right-to-bracket"></i> LOG IN
                </button>
            </form>
            
            <a href="dashboard.php" class="back-link">&larr; Back to Main Portal</a>
        </div>
    </div>

</body>
</html>