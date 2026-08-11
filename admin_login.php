<?php
session_start();
include 'db_connect.php';

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $admin_user = "admin";
    $admin_pass = "siatrack2026"; 

    if ($username === $admin_user && $password === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Maling Username o Password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIATRACK | Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        
        .login-container { background: white; width: 400px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden; }
        
        .admin-header { background-color: #1f2937; color: white; padding: 20px; text-align: center; border-bottom: 5px solid #ffcc00; }
        .admin-header h2 { font-size: 20px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
        .admin-header i { font-size: 24px; margin-bottom: 5px; color: #ffcc00; }

        .login-body { padding: 40px; text-align: center; }
        .login-body img { width: 90px; margin-bottom: 25px; }
        
        .input-group { position: relative; margin-bottom: 15px; text-align: left; }
        .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #888; font-size: 14px; }
        .input-group input { width: 100%; padding: 12px 15px 12px 45px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; transition: 0.3s; }
        .input-group input:focus { border-color: #1f2937; box-shadow: 0 0 10px rgba(31, 41, 55, 0.1); }
        
        button { width: 100%; padding: 12px; background-color: #1f2937; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: 0.3s; margin-top: 10px; }
        button:hover { background-color: #111827; }
        
        .error-box { background-color: #fee2e2; color: #cc0000; padding: 10px; border-radius: 8px; font-size: 12px; margin-bottom: 15px; border-left: 4px solid #cc0000; text-align: left; }
        .back-link { display: inline-block; margin-top: 20px; font-size: 12px; color: #666; text-decoration: none; transition: 0.3s; }
        .back-link:hover { color: #1f2937; text-decoration: underline; }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="admin-header">
            <i class="fa-solid fa-user-shield"></i>
            <h2>Admin Access</h2>
        </div>
        
        <div class="login-body">
            <img src="sia_logo.png" alt="SIA Logo">
            
            <?php if(!empty($error)): ?>
                <div class="error-box">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <div class="input-group">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="username" placeholder="Admin Username" required>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Admin Password" required>
                </div>
                <button type="submit" name="login">Log In to Dashboard</button>
            </form>
            
            <a href="dashboard.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Bumalik sa Portal
            </a>
        </div>
    </div>

</body>
</html>