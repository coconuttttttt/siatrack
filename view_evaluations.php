<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in (i-uncomment ito kapag ready na ang login system mo)
// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: admin_login.php");
//     exit();
// }

date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');

// 1. KPI: Total Students Enrolled
$res_students = $conn->query("SELECT COUNT(*) as total FROM students");
$total_students = $res_students ? $res_students->fetch_assoc()['total'] : 0;

// 2. KPI: Daily Attendance Rate (Unique students present today)
$res_present = $conn->query("SELECT COUNT(DISTINCT student_id) as total FROM attendance WHERE DATE(time_in) = '$today'");
$total_present = $res_present ? $res_present->fetch_assoc()['total'] : 0;
$attendance_rate = ($total_students > 0) ? round(($total_present / $total_students) * 100) : 0;

// 3. KPI: Faculty Evaluation Progress
$res_faculty = $conn->query("SELECT COUNT(*) as total FROM faculty");
$total_faculty = $res_faculty ? $res_faculty->fetch_assoc()['total'] : 0;

$res_evals = $conn->query("SELECT COUNT(*) as total FROM evaluations");
$total_evals = $res_evals ? $res_evals->fetch_assoc()['total'] : 0;

// Assuming expected evaluations is 1 per student per faculty. Adjust this formula if needed.
$expected_evals = $total_students * $total_faculty; 
$evaluation_progress = ($expected_evals > 0) ? round(($total_evals / $expected_evals) * 100) : 0;
if ($evaluation_progress > 100) $evaluation_progress = 100; // Cap at 100%

// 4. KPI: Active SMS Today (Kukunin ang bilang ng mga time in log ngayong araw bilang proxy sa SMS na na-send)
$res_sms = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE DATE(time_in) = '$today'");
$total_sms = $res_sms ? $res_sms->fetch_assoc()['total'] : 0;

// 5. Real-Time Activity Feed Query
$feed_query = "
    SELECT s.first_name, s.last_name, s.course_section, a.time_in 
    FROM attendance a 
    JOIN students s ON a.student_id = s.student_id 
    WHERE DATE(a.time_in) = '$today' 
    ORDER BY a.time_in DESC 
    LIMIT 15
";
$feed_result = $conn->query($feed_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIATRACK | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { background-color: #ffffff; color: #000; display: flex; overflow-x: hidden; }
        
        /* SIDEBAR STYLES */
        .sidebar { 
            width: 260px; 
            background: #ffffff; 
            height: 100vh; 
            padding: 20px; 
            position: fixed; 
            border-right: 2px solid #b30000;
            z-index: 100;
        }
        
        .logo-area { text-align: center; margin-bottom: 40px; position: relative; }
        .logo-area h2 { font-size: 26px; font-weight: 700; display: flex; justify-content: center; align-items: center; gap: 5px; }
        .logo-area h2 span:first-child { color: #b30000; }
        .logo-area h2 span:last-child { color: #000; }
        .logo-area p { font-size: 11px; color: #555; margin-top: -5px; }
        .logo-area i { position: absolute; right: 5px; top: 5px; color: #000; font-size: 20px; }

        .nav-list { list-style: none; }
        .nav-list li { margin-bottom: 15px; }
        .nav-list a { 
            display: flex; 
            align-items: center; 
            color: #000; 
            text-decoration: none; 
            padding: 12px 15px; 
            border-radius: 8px; 
            transition: 0.3s; 
            font-size: 14px;
            font-weight: 500;
            border: 1px solid transparent;
        }
        .nav-list a i { width: 25px; text-align: center; font-size: 16px; margin-right: 10px; }
        .nav-list a:hover { background-color: #f8f9fa; border-color: #ddd; }
        
        .nav-list a.active { 
            background-color: #f28b8b; 
            border: 1px solid #b30000; 
            font-weight: 600;
        }

        /* MAIN CONTENT STYLES */
        .main-content { margin-left: 260px; padding: 30px 40px; width: calc(100% - 260px); min-height: 100vh; }
        
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding-bottom: 20px; 
            border-bottom: 2px solid #b30000; 
            margin-bottom: 30px; 
        }
        .header h1 { font-size: 24px; font-weight: 700; color: #000; }
        
        .header-profile { display: flex; align-items: center; gap: 15px; font-weight: 600; font-size: 15px; cursor: pointer; }
        .header-profile i { font-size: 22px; }

        .section-title { font-size: 16px; font-weight: 700; margin-bottom: 20px; }

        /* KPI CARDS */
        .kpi-container { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
        
        .kpi-card { 
            background: #fff; 
            border: 1px solid #b30000; 
            border-radius: 8px; 
            padding: 25px 15px 15px 15px; 
            text-align: center; 
            position: relative; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        
        .kpi-tab {
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 10px;
            background: #fff;
            border: 1px solid #b30000;
            border-bottom: none;
            border-radius: 4px 4px 0 0;
        }

        .kpi-top { display: flex; justify-content: center; align-items: center; gap: 15px; margin-bottom: 15px; }
        .kpi-icon { font-size: 30px; }
        .kpi-icon.orange { color: #fd7e14; }
        .kpi-icon.grey { color: #6c757d; }
        .kpi-icon.red { color: #dc3545; }
        
        .kpi-value { font-size: 28px; font-weight: 700; color: #000; line-height: 1; text-align: left; }
        .kpi-value span { display: block; font-size: 11px; font-weight: 500; color: #000; margin-top: 4px; }
        
        .kpi-label { font-size: 12px; font-weight: 500; color: #555; border-top: 1px solid #ddd; padding-top: 12px; }

        /* TABLE SECTION */
        .table-section {
            border: 1px solid #b30000;
            border-radius: 8px;
            padding: 25px;
            position: relative;
            background: #fff;
        }

        .table-tab {
            position: absolute;
            top: -10px;
            left: 50px;
            width: 150px;
            height: 12px;
            background: #b30000;
            clip-path: polygon(0 0, 90% 0, 100% 100%, 0% 100%);
        }

        .table-section h3 { font-size: 15px; font-weight: 600; margin-bottom: 15px; color: #000; }

        .data-table { width: 100%; border-collapse: collapse; border: 1px solid #b30000; border-radius: 8px; overflow: hidden; }
        .data-table th, .data-table td { padding: 12px 15px; text-align: center; font-size: 13px; border: 1px solid #b30000; }
        .data-table th { background-color: #d9d9d9; font-weight: 600; color: #000; }
        .data-table tr:nth-child(even) { background-color: #f9f9f9; }
        .data-table tr:nth-child(odd) { background-color: #fff; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo-area">
            <h2><span>SIA</span><span>TRACK</span></h2>
            <p>Southern Isabela Academy</p>
            <i class="fa-solid fa-wifi"></i>
        </div>

        <ul class="nav-list">
            <li>
                <a href="admin_dashboard.php" class="active">
                    <i class="fa-solid fa-border-all"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="manage_users.php">
                    <i class="fa-solid fa-users"></i> User Management
                </a>
            </li>
            <li>
                <a href="attendance_records.php">
                    <i class="fa-solid fa-user-check"></i> Attendance Records
                </a>
            </li>
            <li>
                <a href="manage_evaluations.php">
                    <i class="fa-solid fa-building"></i> Faculty Evaluation Mgmt
                </a>
            </li>
            <li>
                <a href="report_generation.php">
                    <i class="fa-solid fa-file-invoice"></i> Report Generation
                </a>
            </li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <div class="header">
            <h1>Admin Dashboard</h1>
            <div class="header-profile">
                Admin Profile
                <i class="fa-regular fa-bell"></i>
            </div>
        </div>

        <h2 class="section-title">KPI Summary Cards</h2>

        <div class="kpi-container">
            <!-- Card 1 -->
            <div class="kpi-card">
                <div class="kpi-tab"></div>
                <div class="kpi-top">
                    <div class="kpi-icon orange"><i class="fa-solid fa-circle-user"></i></div>
                    <div class="kpi-value"><?php echo number_format($total_students); ?></div>
                </div>
                <div class="kpi-label">Total Students Enrolled</div>
            </div>

            <!-- Card 2 -->
            <div class="kpi-card">
                <div class="kpi-tab"></div>
                <div class="kpi-top">
                    <div class="kpi-icon grey"><i class="fa-regular fa-calendar"></i></div>
                    <div class="kpi-value"><?php echo $attendance_rate; ?>%</div>
                </div>
                <div class="kpi-label">Daily Attendance Rate</div>
            </div>

            <!-- Card 3 -->
            <div class="kpi-card">
                <div class="kpi-tab"></div>
                <div class="kpi-top">
                    <div class="kpi-icon"><i class="fa-solid fa-user-tie" style="color:#e06666;"></i></div>
                    <div class="kpi-value"><?php echo $evaluation_progress; ?>% <span>Completed</span></div>
                </div>
                <div class="kpi-label">Faculty Evaluation Progress</div>
            </div>

            <!-- Card 4 -->
            <div class="kpi-card">
                <div class="kpi-tab"></div>
                <div class="kpi-top">
                    <div class="kpi-icon red"><i class="fa-solid fa-comment-dots"></i></div>
                    <div class="kpi-value"><?php echo number_format($total_sms); ?></div>
                </div>
                <div class="kpi-label">Active SMS Today</div>
            </div>
        </div>

        <!-- REAL-TIME ACTIVITY FEED TABLE -->
        <div class="table-section">
            <div class="table-tab"></div>
            <h3>Real-Time Activity Feed: Live Attendance Stream</h3>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Grade/Section</th>
                        <th>Time In</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($feed_result && $feed_result->num_rows > 0) {
                        while ($row = $feed_result->fetch_assoc()) {
                            // Formatting the time
                            $time_in_formatted = date("h:i A", strtotime($row['time_in']));
                            
                            // Determining Status (Example Logic: If Time in is after 7:30 AM, mark as Late)
                            $time_only = date("H:i:s", strtotime($row['time_in']));
                            $status = ($time_only <= "07:30:00") ? "On-Time" : "Late";
                            $status_color = ($status == "On-Time") ? "#198754" : "#dc3545"; // Green for On-Time, Red for Late

                            echo "<tr>
                                    <td>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</td>
                                    <td>" . htmlspecialchars($row['course_section']) . "</td>
                                    <td>{$time_in_formatted}</td>
                                    <td style='color: {$status_color}; font-weight: 600;'>{$status}</td>
                                  </tr>";
                        }
                    } else {
                        // Kung walang record ngayong araw
                        echo "<tr>
                                <td colspan='4' style='text-align: center; color: #777; padding: 20px;'>No attendance records yet for today.</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>