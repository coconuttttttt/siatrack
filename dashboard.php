<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIATRACK | Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #eef2f3; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        
        /* --- LOADING SCREEN STYLES --- */
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

        /* --- PORTAL STYLES --- */
        .container { 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0px 10px 30px rgba(0,0,0,0.1); 
            text-align: center; 
            width: 100%; 
            max-width: 450px; 
            opacity: 0; /* Nakatago muna hanggang matapos ang loader */
            transform: translateY(20px);
            transition: all 0.8s ease;
        }
        
        .container.show { opacity: 1; transform: translateY(0); }

        .school-logo { width: 100px; height: auto; margin-bottom: 15px; border-radius: 50%; }
        h2 { color: #333; margin-bottom: 5px; }
        p { color: #777; font-size: 14px; margin-bottom: 25px; }
        hr { border: 0; height: 1px; background: #ddd; margin-bottom: 25px; }
        .section-title { font-size: 14px; color: #555; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; margin-top: 20px; text-align: left; }
        .btn { display: block; width: 100%; padding: 15px; margin-bottom: 15px; text-decoration: none; color: white; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn-admin { background-color: #0d6efd; }
        .btn-admin:hover { background-color: #0b5ed7; transform: translateY(-2px); }
        .btn-student { background-color: #198754; }
        .btn-student:hover { background-color: #157347; transform: translateY(-2px); }
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
        <img src="sia_logo.png" alt="Southern Isabela Academy Logo" class="school-logo">
        
        <h2>SIATRACK Portal</h2>
        <p>Southern Isabela Academy</p>
        <hr>
        
        <div class="section-title">Admin & Faculty Access</div>
        <a href="faculty_login.php" class="btn btn-admin">Faculty Login</a>
        <a href="admin_dashboard.php" class="btn btn-admin" style="background-color: #2b3035;">Admin Access</a>
       
        <div class="section-title">Student Access</div>
        <a href="student_login.php" class="btn btn-student">Evaluate Faculty</a>
    </div>

    <script>
        // Pagbukas ng page, mag-load ng 3 seconds
        window.addEventListener('load', function() {
            setTimeout(function() {
                // I-hide ang loader
                const loader = document.getElementById('loader-container');
                loader.style.opacity = '0';
                setTimeout(() => { loader.style.display = 'none'; }, 500);

                // Ipakita ang portal content
                const container = document.getElementById('portalContainer');
                container.classList.add('show');
            }, 3000); // 3 seconds delay
        });
    </script>
</body>
</html>