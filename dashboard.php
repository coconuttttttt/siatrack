<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIATRACK | Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { 
            background-color: #dbe0e4;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
        }
        
        #loader-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .loader-logo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid #0d6efd;
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
            color: #0d6efd;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-size: 14px;
        }

        @keyframes rotate-logo {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
            70% { box-shadow: 0 0 0 20px rgba(13, 110, 253, 0); }
            100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
        }

        .container { 
            background: #fbfbfb; 
            padding: 70px 40px 40px 40px;
            border-radius: 20px; 
            box-shadow: 0px 20px 40px rgba(0,0,0,0.15); 
            text-align: center; 
            width: 100%; 
            max-width: 420px; 
            position: relative;
            opacity: 0; 
            transform: translateY(20px);
            transition: all 0.8s ease;
        }
        
        .container.show { opacity: 1; transform: translateY(0); }

        .logo-wrapper {
            position: absolute;
            top: -55px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border-radius: 50%;
            padding: 5px;
            box-shadow: 0 10px 20px rgba(220, 53, 69, 0.2);
        }

        .school-logo { 
            width: 100px; 
            height: 100px; 
            border-radius: 50%; 
            display: block;
            object-fit: contain;
        }

        h2 { 
            color: #1a1a1a; 
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 2px; 
        }
        p.subtitle { 
            color: #999; 
            font-size: 13px; 
            font-weight: 400;
            margin-bottom: 30px; 
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
            color: #333; 
            font-size: 13px;
            font-weight: 500;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #222;
        }
        
        .divider:not(:empty)::before { margin-right: 15px; }
        .divider:not(:empty)::after { margin-left: 15px; }

        .btn { 
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            width: 100%; 
            padding: 16px; 
            margin-bottom: 15px; 
            text-decoration: none; 
            border-radius: 12px; 
            font-size: 16px;
            font-weight: 600; 
            transition: all 0.3s ease; 
            border: none; 
            cursor: pointer; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .btn i {
            font-size: 20px;
        }

        .btn-faculty { 
            background: linear-gradient(90deg, #51b5e4 0%, #c8d8e6 100%);
            color: #fff;
        }
        .btn-faculty i { color: #1a1a1a; }
        .btn-faculty:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(81, 181, 228, 0.3); }

        .btn-admin { 
            background-color: #232323; 
            color: #fff;
        }
        .btn-admin i { color: #555; }
        .btn-admin:hover { background-color: #111; transform: translateY(-2px); }

        .btn-student { 
            background: linear-gradient(180deg, #4ed35f 0%, #359842 100%);
            color: #fff; 
        }
        .btn-student i { color: #115c1e; }
        .btn-student:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(78, 211, 95, 0.3); }
    </style>
</head>
<body>

    <div id="loader-container">
        <div class="loader-logo">
            <img src="sia_logo.png" alt="SIA Logo">
        </div>
        <div class="loader-text">SIATRACK PORTAL LOADING...</div>
    </div>

    <div class="container" id="portalContainer">
        
        <div class="logo-wrapper">
            <img src="sia_logo.png" alt="Southern Isabela Academy Logo" class="school-logo">
        </div>
        
        <h2>SIATRACK Portal</h2>
        <p class="subtitle">Southern Isabela Academy</p>
        
        <div class="divider">Admin & Faculty Access</div>
        
        <a href="faculty_login.php" class="btn btn-faculty">
            <i class="fa-solid fa-arrow-right-to-bracket"></i> Faculty Login
        </a>
        
        <a href="admin_dashboard.php" class="btn btn-admin">
            <i class="fa-solid fa-user-gear"></i> Admin Access
        </a>
       
        <div class="divider">Student Access</div>
        
        <a href="student_login.php" class="btn btn-student">
            <i class="fa-solid fa-check-to-slot"></i> Evaluate Faculty
        </a>
    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                const loader = document.getElementById('loader-container');
                loader.style.opacity = '0';
                setTimeout(() => { loader.style.display = 'none'; }, 500);

                const container = document.getElementById('portalContainer');
                container.classList.add('show');
            }, 3000);
        });
    </script>
</body>
</html>