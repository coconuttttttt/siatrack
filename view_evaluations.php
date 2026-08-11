<?php
include 'db_connect.php';

$query = "SELECT s.first_name, s.last_name, f.full_name, e.engagement_score, e.effectiveness_score, e.punctuality_score, e.date_evaluated 
          FROM evaluations e 
          JOIN students s ON e.student_id = s.student_id 
          JOIN faculty f ON e.teacher_id = f.teacher_id 
          ORDER BY e.date_evaluated DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SIATRACK | Faculty Evaluations</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; display: flex; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #0d6efd 0%, #004aab 100%); color: white; height: 100vh; padding: 30px 20px; position: fixed; }
        .sidebar h2 { text-align: center; margin-bottom: 40px; font-size: 24px; }
        .sidebar a { display: flex; align-items: center; color: rgba(255,255,255,0.8); text-decoration: none; padding: 15px; margin-bottom: 10px; border-radius: 8px; transition: 0.3s; }
        .sidebar a i { margin-right: 15px; width: 20px; text-align: center; }
        .sidebar a:hover { background-color: rgba(255,255,255,0.1); color: white; }
        .sidebar .active { background-color: rgba(255,255,255,0.2); color: white; font-weight: 600; border-left: 4px solid #fff; }
        .logout-btn { position: absolute; bottom: 30px; width: calc(100% - 40px); background: rgba(220,53,69,0.8)!important; justify-content: center; }
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        .header { background: white; padding: 20px 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #0d6efd; color: white; }
        tr:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fa-solid fa-graduation-cap"></i> SIATRACK</h2>
        <a href="admin_dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="view_evaluations.php" class="active"><i class="fa-solid fa-star"></i> Faculty Evaluations</a>
        <a href="manage_students.php"><i class="fa-solid fa-users"></i> Manage Students</a>
        <a href="dashboard.php" class="logout-btn"><i class="fa-solid fa-sign-out-alt"></i> Log Out</a>
        <a href="manage_faculty.php"><i class="fa-solid fa-chalkboard-user"></i> Manage Faculty</a>
    </div>

    <div class="main-content">
        <div class="header"><h2>Faculty Evaluation Results</h2></div>
        <table>
            <thead>
                <tr>
                    <th>Teacher Evaluated</th>
                    <th>Evaluated By</th>
                    <th>Engagement (1-5)</th>
                    <th>Effectiveness (1-5)</th>
                    <th>Punctuality (1-5)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td><strong>{$row['full_name']}</strong></td>
                                <td>{$row['first_name']} {$row['last_name']}</td>
                                <td>{$row['engagement_score']}</td>
                                <td>{$row['effectiveness_score']}</td>
                                <td>{$row['punctuality_score']}</td>
                              </tr>";
                    }
                } else { 
                    echo "<tr><td colspan='5' style='text-align:center;'>No evaluations yet.</td></tr>"; 
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>