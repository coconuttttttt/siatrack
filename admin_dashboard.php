<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
// ... yung rest ng code mo sa admin_dashboard.php ...
?>

<?php
// admin_dashboard.php
include 'db_connect.php';
date_default_timezone_set('Asia/Manila');

$today = date('Y-m-d');

// 1. Kunin ang total na estudyante
$res_students = $conn->query("SELECT COUNT(*) as total FROM students");
$total_students = $res_students->fetch_assoc()['total'];

// 2. Kunin kung ilan ang pumasok (Time In) ngayong araw
$res_present = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE DATE(time_in) = '$today'");
$total_present = $res_present->fetch_assoc()['total'];

// 3. Kunin ang total na faculty members
$res_faculty = $conn->query("SELECT COUNT(*) as total FROM faculty");
$total_faculty = $res_faculty->fetch_assoc()['total'];

// 4. Kunin ang total evaluations na na-submit
$res_evals = $conn->query("SELECT COUNT(*) as total FROM evaluations");
$total_evals = $res_evals->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIATRACK | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; color: #333; display: flex; }
        
        /* Sidebar Styling */
        .sidebar { 
            width: 260px; 
            background: linear-gradient(180deg, #0d6efd 0%, #004aab 100%); 
            color: white; 
            height: 100vh; 
            padding: 30px 20px; 
            position: fixed; 
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar h2 { text-align: center; margin-bottom: 40px; font-weight: 700; font-size: 24px; letter-spacing: 2px; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
        .sidebar a { 
            display: flex; 
            align-items: center; 
            color: rgba(255,255,255,0.8); 
            text-decoration: none; 
            padding: 15px; 
            margin-bottom: 10px; 
            border-radius: 8px; 
            transition: all 0.3s ease; 
            font-weight: 400;
        }
        .sidebar a i { margin-right: 15px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar a:hover { background-color: rgba(255,255,255,0.1); color: white; transform: translateX(5px); }
        .sidebar .active { background-color: rgba(255,255,255,0.2); color: white; font-weight: 600; border-left: 4px solid #fff; }
        .logout-btn { margin-top: auto; background: rgba(220, 53, 69, 0.8) !important; position: absolute; bottom: 30px; width: calc(100% - 40px); justify-content: center; }
        .logout-btn:hover { background: #dc3545 !important; }

        /* Main Content Styling */
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; background: white; padding: 20px 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header h1 { color: #2c3e50; font-size: 24px; font-weight: 600; }
        .header .date-badge { background: #e9ecef; padding: 8px 15px; border-radius: 20px; font-size: 14px; color: #555; font-weight: 600; }
        
        /* Dashboard Cards */
        .cards-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; }
        .card { 
            background: white; 
            padding: 25px; 
            border-radius: 15px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); 
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        
        /* Left side of card (Text) */
        .card-info h3 { font-size: 32px; color: #2c3e50; margin-bottom: 5px; font-weight: 700; }
        .card-info p { color: #7f8c8d; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
        
        /* Right side of card (Icon) */
        .card-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 28px; color: white; }
        .icon-present { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .icon-students { background: linear-gradient(135deg, #2980b9 0%, #6dd5ed 100%); }
        .icon-evals { background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%); }
        .icon-faculty { background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%); }

    </style>
</head>
<body>

    <div class="sidebar">
    <a href="admin_dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
    
    <a href="generate_link.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'generate_link.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-link"></i> Evaluation Link
    </a>

    <a href="view_evaluations.php"><i class="fa-solid fa-star"></i> Faculty Evaluations</a>
    <a href="manage_students.php"><i class="fa-solid fa-users"></i> Manage Students</a>
    <a href="manage_faculty.php"><i class="fa-solid fa-chalkboard-user"></i> Manage Faculty</a>
    <a href="logout.php" class="logout-btn"><i class="fa-solid fa-sign-out-alt"></i> Log Out</a>
</div>

    <div class="main-content">
        <div class="header">
            <div>
                <h1>Admin Overview</h1>
                <p style="color: #7f8c8d; font-size: 14px; margin-top: 5px;">Welcome back to Southern Isabela Academy Portal</p>
            </div>
            <div class="date-badge">
                <i class="fa-regular fa-calendar" style="margin-right: 5px;"></i> <?php echo date("l, F d, Y"); ?>
            </div>
        </div>

        <div class="cards-container">
            <div class="card">
                <div class="card-info">
                    <h3><?php echo $total_present; ?></h3>
                    <p>Present Today</p>
                </div>
                <div class="card-icon icon-present">
                    <i class="fa-solid fa-user-check"></i>
                </div>
            </div>
            
            <div class="card">
                <div class="card-info">
                    <h3><?php echo $total_students; ?></h3>
                    <p>Total Students</p>
                </div>
                <div class="card-icon icon-students">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            
            <div class="card">
                <div class="card-info">
                    <h3><?php echo $total_evals; ?></h3>
                    <p>Evaluations</p>
                </div>
                <div class="card-icon icon-evals">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
            </div>
            
            <div class="card">
                <div class="card-info">
                    <h3><?php echo $total_faculty; ?></h3>
                    <p>Total Faculty</p>
                </div>
                <div class="card-icon icon-faculty">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
            </div>
        </div>
        
    </div>

</body>
</html>
<?php $conn->close(); ?>