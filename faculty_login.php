<?php
session_start();
include 'db_connect.php';

if (isset($_SESSION['teacher_id'])) {
    header("Location: faculty_dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email']; 
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT teacher_id, full_name, department FROM faculty WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $teacher = $result->fetch_assoc();
        $_SESSION['teacher_id'] = $teacher['teacher_id'];
        $_SESSION['teacher_name'] = $teacher['full_name'];
        $_SESSION['department'] = $teacher['department'];
        
        header("Location: faculty_dashboard.php");
        exit();
    } else {
        $error = "Invalid Email or Password!";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SIATRACK | Faculty Login</title>
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
            font-size: 13px; 
            margin-bottom: 15px; 
            font-weight: 600; 
            background: #f8d7da; 
            padding: 10px; 
            border-radius: 5px; 
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

        .input-wrapper .suffix {
            font-size: 13px;
            font-weight: 600;
            color: #111;
            margin-left: 8px;
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

        #loader-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 9999;
        }

        .loader-logo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid #a30000;
            padding: 5px;
            background: white;
            animation: pulse-ring 1.5s infinite ease-in-out;
        }

        .loader-logo img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            animation: rotate-logo 2s infinite linear;
        }

        .loader-text {
            margin-top: 20px;
            font-weight: 700;
            color: #a30000;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-size: 14px;
        }

        @keyframes rotate-logo {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(163, 0, 0, 0.4); }
            70% { box-shadow: 0 0 0 20px rgba(163, 0, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(163, 0, 0, 0); }
        }

        @media (max-width: 768px) {
            .split-layout { flex-direction: column; }
            .left-side { display: none; }
            .right-side { padding: 40px 20px; }
        }
    </style>
</head>
<body>

<div id="loader-container">
    <div class="loader-logo">
        <img src="sia_logo.png" alt="SIA Logo">
    </div>
    <div class="loader-text">Authenticating...</div>
</div>

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
            
            <h2>Faculty Access</h2>
            <p class="subtitle">Sign in to access your dashboard</p>
            
            <?php if($error != "") echo "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> $error</div>"; ?>

            <form action="" method="POST" id="loginForm">
                <div class="input-group">
                    <label>Institutional Email</label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-user icon"></i>
                        <input type="email" name="email" required placeholder="e.g., faculty@sian.edu">
                        <span class="suffix">@siac.edu</span>
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
            
            <a href="#" class="forgot-link">Forgot Password?</a>
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

    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        document.getElementById('loader-container').style.display = 'flex';
        setTimeout(function() {
            form.submit();
        }, 3000);
    });
</script>

</body>
</html>