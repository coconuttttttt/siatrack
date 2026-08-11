<?php
session_start();
include 'db_connect.php';

if (isset($_SESSION['admin_id'])) {
    session_unset();
}

if (isset($_SESSION['student_id'])) {
    header("Location: evaluate_faculty.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nfc_uid = $_POST['nfc_uid'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT student_id, first_name, last_name, course_section FROM students WHERE nfc_uid = ? AND password = ?");
    $stmt->bind_param("ss", $nfc_uid, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        
        $_SESSION['student_id'] = $student['student_id'];
        $_SESSION['student_name'] = $student['first_name'] . " " . $student['last_name'];
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        
        body { 
            display: flex; 
            height: 100vh; 
            overflow: hidden; 
            background: #e6e6e6;
        }

        .split-layout {
            display: flex;
            width: 100%;
            height: 100%;
        }

        .left-side {
            flex: 1;
            background: url('student.jpg') no-repeat center center;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .left-overlay {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(4px);
            padding: 20px 40px;
            border-radius: 20px;
            text-align: center;
        }

        .left-overlay h1 {
            font-size: 2.8rem;
            font-weight: 800;
            color: #000;
            margin-bottom: 5px;
        }

        .left-overlay p {
            font-size: 1.2rem;
            color: #222;
            font-weight: 500;
        }

        .right-side {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #e0e0e0;
            padding: 20px;
        }

        .login-card { 
            background: white; 
            border-radius: 20px; 
            box-shadow: 0px 10px 30px rgba(0,0,0,0.15); 
            width: 100%; 
            max-width: 400px; 
            padding: 60px 40px 40px;
            position: relative; 
            text-align: center; 
        }

        .logo-wrapper {
            position: absolute;
            top: -45px;
            left: 50%;
            transform: translateX(-50%);
            width: 90px;
            height: 90px;
            background: white;
            border: 1px solid #111;
            padding: 2px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        h2 { 
            color: #a30000; 
            font-size: 24px; 
            margin-bottom: 5px; 
            font-weight: 700; 
        }
        
        p.subtitle { 
            color: #555; 
            font-size: 13px; 
            margin-bottom: 30px;
        }

        .error-msg { 
            color: #dc3545; 
            font-size: 12px; 
            margin-bottom: 15px; 
            font-weight: 600; 
            background: #f8d7da; 
            padding: 12px; 
            border-radius: 8px; 
            border: 1px solid #f5c6cb; 
            text-align: left; 
        }
        
        .input-group { 
            margin-bottom: 20px; 
            text-align: left; 
        }
        
        .input-group label { 
            display: block; 
            font-size: 12px; 
            color: #444; 
            margin-bottom: 8px; 
            font-weight: 500; 
        }
        
        .input-wrapper {
            display: flex;
            align-items: center;
            border: 1px solid #ffcccc;
            border-radius: 10px;
            padding: 12px 15px;
            background: white;
            box-shadow: 0 0 5px rgba(255, 0, 0, 0.05);
            transition: 0.3s;
        }

        .input-wrapper:focus-within {
            border-color: #a30000;
            box-shadow: 0 0 8px rgba(163, 0, 0, 0.2);
        }

        .input-wrapper i.icon {
            font-size: 18px;
            color: #111;
            margin-right: 12px;
        }

        .input-wrapper input {
            border: none;
            outline: none;
            flex: 1;
            font-size: 13px;
            color: #333;
            width: 100%;
        }

        .input-wrapper i.toggle-password {
            font-size: 16px;
            color: #555;
            cursor: pointer;
            margin-left: 8px;
        }

        .options {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            font-size: 12px;
            color: #555;
        }

        .options input[type="checkbox"] {
            margin-right: 8px;
            accent-color: #a30000;
        }
        
        .btn-login { 
            width: 100%; 
            padding: 14px; 
            background: #b30000; 
            color: white; 
            border: none; 
            border-radius: 25px; 
            font-weight: 600; 
            font-size: 16px; 
            cursor: pointer; 
            transition: all 0.3s ease; 
        }
        
        .btn-login:hover { 
            background: #8a0000; 
        }
        
        .forgot-link { 
            display: block; 
            margin-top: 20px; 
            color: #555; 
            text-decoration: none; 
            font-size: 12px; 
        }

        .forgot-link:hover { 
            color: #a30000; 
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .split-layout { flex-direction: column; }
            .left-side { display: none; }
            .right-side { padding: 40px 20px; }
        }
    </style>
</head>
<body>

<div class="split-layout">
    <div class="left-side">
        <div class="left-overlay">
            <h1>Be a SIAn!</h1>
            <p>Southern Isabela Academy Portal</p>
        </div>
    </div>
    
    <div class="right-side">
        <div class="login-card">
            <div class="logo-wrapper">
                <img src="sia_logo.png" alt="SIA Logo">
            </div>
            
            <h2>Student Access</h2>
            <p class="subtitle">Sign in to access your evaluation form</p>
            
            <?php if($error != "") echo "<div class='error-msg'><i class='fa-solid fa-triangle-exclamation'></i> $error</div>"; ?>

            <form action="" method="POST" id="loginForm">
                <div class="input-group">
                    <label>NFC UID (Username)</label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-id-card icon"></i>
                        <input type="text" name="nfc_uid" required placeholder="Scan your card or type UID">
                    </div>
                </div>
                
                <div class="input-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock icon"></i>
                        <input type="password" name="password" id="passwordInput" required placeholder="Enter your password">
                        <i class="fa-regular fa-eye-slash toggle-password" onclick="togglePassword()"></i>
                    </div>
                </div>
                
                <div class="options">
                    <input type="checkbox" id="remember">
                    <label for="remember">Remember me</label>
                </div>
                
                <button type="submit" class="btn-login">Sign In</button>
            </form>
            
            <a href="dashboard.php" class="forgot-link">Back to Main Portal</a>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passInput = document.getElementById('passwordInput');
        const icon = document.querySelector('.toggle-password');
        
        if (passInput.type === 'password') {
            passInput.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            passInput.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
</script>

</body>
</html>