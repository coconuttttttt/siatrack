<?php
session_start();
include 'db_connect.php';

// Kung naka-login na, idirekta agad sa faculty dashboard
if (isset($_SESSION['teacher_id'])) {
    header("Location: faculty_dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email']; // Username is now Email
    $password = $_POST['password'];

    // Query gamit ang email sa halip na teacher_id
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
        $error = "Invalid Gmail or Password!";
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        
        .login-card { background: white; border-radius: 15px; box-shadow: 0px 10px 40px rgba(0,0,0,0.1); width: 100%; max-width: 420px; overflow: hidden; text-align: center; }
        
        /* SIA THEME BANNER */
        .login-header-banner { background: url('student.jpg') no-repeat center center; background-size: cover; height: 160px; position: relative; display: flex; justify-content: center; align-items: flex-end; border-bottom: 5px solid #ffcc00;}
        .login-header-banner::before { content:''; position: absolute; top:0; left:0; width:100%; height:100%; background: linear-gradient(to top, rgba(0,51,204,0.85) 0%, rgba(0,0,0,0.2) 100%);}
        
        .school-logo { width: 80px; height: 80px; border-radius: 50%; background: white; padding: 5px; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.2); position: absolute; bottom: -40px; left: 50%; transform: translateX(-50%); z-index: 10;}
        
        .login-body { padding: 60px 30px 30px; }
        h2 { color: #2c3e50; font-size: 22px; margin-bottom: 5px; font-weight: 700; }
        p.subtitle { color: #7f8c8d; font-size: 14px; margin-bottom: 25px;}
        
        .input-group { margin-bottom: 15px; text-align: left; }
        .input-group label { display: block; font-size: 13px; color: #555; margin-bottom: 5px; font-weight: 600; }
        .input-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: 0.3s;}
        .input-group input:focus { border-color: #0033cc; box-shadow: 0 0 5px rgba(0,51,204,0.2); outline: none;}
        
        /* SIA RED BUTTON */
        .btn-login { width: 100%; padding: 15px; background: #cc0000; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.3s ease; margin-top: 15px; }
        .btn-login:hover { background: #990000; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(204,0,0,0.2);}
        
        .error-msg { color: #dc3545; font-size: 13px; margin-bottom: 15px; font-weight: 600; background: #f8d7da; padding: 10px; border-radius: 5px; border: 1px solid #f5c6cb; text-align: left; }
        .back-link { display: block; margin-top: 25px; color: #7f8c8d; text-decoration: none; font-size: 13px; font-weight: 600; }
        .back-link:hover { color: #0033cc; }

        /* Full-screen Loading Overlay */
#loader-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,1) 0%, rgba(240,242,245,1) 100%);
    display: none; /* Nakatago muna */
    justify-content: center;
    align-items: center;
    flex-direction: column;
    z-index: 9999;
}

/* Rotating SIA Logo Animation */
.loader-logo {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 5px solid #0033cc;
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
    color: #0033cc;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-size: 14px;
}

/* Animations */
@keyframes rotate-logo {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes pulse-ring {
    0% { box-shadow: 0 0 0 0 rgba(0, 51, 204, 0.4); }
    70% { box-shadow: 0 0 0 20px rgba(0, 51, 204, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 51, 204, 0); }
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

    <div class="login-card">
        <div class="login-header-banner">
            <img src="sia_logo.png" alt="SIA Logo" class="school-logo">
        </div>
        
        <div class="login-body">
            <h2>Faculty Portal</h2>
            <p class="subtitle">Please log in to view your classes.</p>
            
            <?php if($error != "") echo "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> $error</div>"; ?>

            <form action="" method="POST">
                <div class="input-group">
                    <label>Gmail Address</label>
                    <input type="email" name="email" required placeholder="example@gmail.com">
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter password">
                </div>
                <button type="submit" class="btn-login"><i class="fa-solid fa-right-to-bracket"></i> Log In</button>
            </form>
            
            <a href="dashboard.php" class="back-link">&larr; Back to Main Portal</a>
        </div>
    </div>
<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        // 1. Pigilan muna ang default submit para lumabas ang loader
        e.preventDefault();
        const form = this;

        // 2. Ipakita ang Eye-catching Loading Screen
        document.getElementById('loader-container').style.display = 'flex';

        // 3. Patagalin ng 3 seconds (3000ms) bago i-submit ang data sa server
        setTimeout(function() {
            form.submit();
        }, 3000);
    });
</script>

</body>
</html>