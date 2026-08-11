<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: faculty_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];

// --- DYNAMIC STRAND SELECTION ---
// Pinili nating gamitin ang URL parameter 'strand' para malaman kung anong klase ang tinitingnan
$selected_strand = isset($_GET['strand']) ? $_GET['strand'] : "";
$active_subject = isset($_GET['subject']) ? $_GET['subject'] : "";

// Profile picture logic
$res_teacher = $conn->query("SELECT profile_pic FROM faculty WHERE teacher_id = $teacher_id");
if ($res_teacher && $res_teacher->num_rows > 0) {
    $t_data = $res_teacher->fetch_assoc();
    $teacher_pic = ($t_data['profile_pic']) ? $t_data['profile_pic'] : 'default_avatar.png';
} else {
    $teacher_pic = 'default_avatar.png';
}

// 1. Kunin ang mga subjects base sa teacher_id at sa piniling strand (kung meron na)
$subjects_result = null;
if ($selected_strand) {
    $subject_query = "SELECT DISTINCT subject_name FROM classes WHERE teacher_id = ? AND course_section = ?";
    $stmt_sub = $conn->prepare($subject_query);
    $stmt_sub->bind_param("is", $teacher_id, $selected_strand);
    $stmt_sub->execute();
    $subjects_result = $stmt_sub->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SIATRACK | Faculty Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; display: flex; }
        
        .sidebar { width: 280px; background: linear-gradient(180deg, #cc0000 0%, #8b0000 100%); color: white; height: 100vh; padding: 20px; position: fixed; overflow-y: auto; box-shadow: 4px 0 10px rgba(0,0,0,0.1); z-index: 100; }
        .sidebar h2 { font-size: 22px; text-align: center; margin-bottom: 5px; font-weight: 700; letter-spacing: 1px; }
        .teacher-badge { text-align: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .teacher-profile-pic { width: 80px; height: 80px; border-radius: 50%; border: 3px solid white; background: white; object-fit: cover; }
        .cam-icon { position: absolute; bottom: 0; right: 0; background: #ffcc00; color: #333; width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; cursor: pointer; border: 2px solid white; }
        .teacher-badge p { font-size: 13px; color: #ffcc00; font-weight: 600; }

        .nav-label { font-size: 10px; text-transform: uppercase; opacity: 0.8; margin-top: 20px; display: block; letter-spacing: 1.5px; font-weight: 700; color: #eee; }
        .subject-item a { display: block; padding: 12px 15px; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 13px; transition: 0.3s; border-radius: 8px; margin-top: 5px; }
        .subject-item a:hover { background: rgba(255,255,255,0.1); color: white; }
        .subject-item a.active-sub { background: rgba(0,0,0,0.3); color: #ffcc00; font-weight: 600; border-left: 4px solid #ffcc00; }

        .import-section { margin-top: 15px; padding: 12px; background: rgba(255, 255, 255, 0.1); border-radius: 10px; border: 1px dashed rgba(255, 255, 255, 0.3); text-align: center; }
        .import-section h4 { font-size: 11px; color: #ffcc00; margin-bottom: 8px; text-transform: uppercase; }
        .sidebar-upload-btn { width: 100%; background: #198754; color: white; border: none; padding: 8px; border-radius: 5px; font-size: 11px; font-weight: 600; cursor: pointer; margin-top: 8px; transition: 0.3s; }

        .main-content { margin-left: 280px; padding: 40px; width: calc(100% - 280px); }
        .monitoring-grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 25px; margin-top: 20px; }
        .monitor-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); min-height: 550px; }
        .monitor-card h4 { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f2f5; display: flex; justify-content: space-between; align-items: center; }

        .student-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px; border: 1px solid #eee; transition: 0.2s; }
        .present-row { border-left: 5px solid #198754; background: #f0fff4; }
        .btn-action { padding: 8px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 11px; font-weight: 700; color: white; display: inline-flex; align-items: center; gap: 5px; }
        .btn-in { background: #198754; }
        .btn-out { background: #dc3545; }
        .btn-reset { background: #ffc107; color: #212529; }

        /* STRAND CARD STYLES */
        .strand-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-top: 30px; }
        .strand-card { background: white; padding: 30px; border-radius: 15px; text-align: center; cursor: pointer; transition: 0.3s; border: 2px solid transparent; box-shadow: 0 5px 15px rgba(0,0,0,0.05); text-decoration: none; display: block; }
        .strand-card:hover { border-color: #0033cc; transform: translateY(-5px); }
        .strand-card i { font-size: 40px; color: #0033cc; margin-bottom: 15px; }
        .strand-card h3 { color: #333; font-size: 18px; font-weight: 700; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; backdrop-filter: blur(4px); }
        .modal-content { background: white; padding: 30px; border-radius: 15px; width: 90%; max-width: 600px; position: relative; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-height: 85vh; overflow-y: auto; }
        .close-btn { position: absolute; top: 15px; right: 20px; font-size: 28px; cursor: pointer; color: #888; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>SIATRACK</h2>
        <div class="teacher-badge">
            <div class="profile-img-container">
                <img src="uploads/<?php echo $teacher_pic; ?>" class="teacher-profile-pic">
                <label for="teacherPicUpload" class="cam-icon"><i class="fa-solid fa-camera"></i></label>
                <input type="file" id="teacherPicUpload" style="display:none;" onchange="uploadProfilePic(this, 'teacher')">
            </div>
            <p><?php echo htmlspecialchars($teacher_name); ?></p>
        </div>

        <a href="faculty_dashboard.php" style="color:white; text-decoration:none; font-size:12px; display:block; text-align:center; background:rgba(255,255,255,0.1); padding:8px; border-radius:5px; margin-bottom:10px;">
            <i class="fa-solid fa-house"></i> Home (Change Strand)
        </a>

        <span class="nav-label"><?php echo $selected_strand ? $selected_strand : "Select Strand First"; ?></span>
        <ul style="list-style:none;">
            <?php 
            if ($subjects_result && $subjects_result->num_rows > 0):
                while($sub = $subjects_result->fetch_assoc()): ?>
                    <li class="subject-item">
                        <a href="?strand=<?php echo urlencode($selected_strand); ?>&subject=<?php echo urlencode($sub['subject_name']); ?>" class="<?php echo ($active_subject == $sub['subject_name']) ? 'active-sub' : ''; ?>">
                            <i class="fa-solid fa-book-bookmark"></i> <?php echo htmlspecialchars($sub['subject_name']); ?>
                        </a>
                    </li>
                <?php endwhile; 
            endif; ?>
        </ul>

        <?php if($active_subject): ?>
        <span class="nav-label">Attendance Tools</span>
        <div class="import-section" style="border-color: #ffcc00; margin-bottom: 15px;">
            <h4><i class="fa-solid fa-clipboard-user"></i> Manual Add</h4>
            <button onclick="openManualModal()" class="sidebar-upload-btn" style="background: #ffcc00; color: #333;">
                <i class="fa-solid fa-magnifying-glass-plus"></i> Open Student List
            </button>
        </div>
        <?php endif; ?>

        <a href="faculty_logout.php" class="nav-label" style="text-decoration:none; margin-top:30px; background:rgba(0,0,0,0.2); padding:10px; border-radius:5px; text-align:center;"><i class="fa-solid fa-right-from-bracket"></i> LOGOUT</a>
    </div>

    <div class="main-content">
        <?php if($active_subject): ?>
            <div style="background:#0033cc; color:white; padding:20px; border-radius:12px; margin-bottom:25px; border-left: 8px solid #ffcc00;">
                <h2 style="font-size:24px;"><?php echo htmlspecialchars($active_subject); ?></h2>
                <p style="font-size:12px; opacity:0.8;">Monitoring for <?php echo date('l, F d, Y'); ?> | <?php echo htmlspecialchars($selected_strand); ?></p>
            </div>

            <div class="monitoring-grid">
                <div class="monitor-card">
                    <h4 style="color: #198754;">
                        <span><i class="fa-solid fa-circle-check"></i> Present</span>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn-action btn-reset" onclick="resetAttendance()"><i class="fa-solid fa-rotate-left"></i> RESET</button>
                            <a href="export_attendance.php?subject=<?php echo urlencode($active_subject); ?>" class="btn-action" style="background:#0033cc; text-decoration:none;"><i class="fa-solid fa-download"></i> EXPORT</a>
                        </div>
                    </h4>
                    <div id="presentList">
                        <?php
                        $p_query = "SELECT s.first_name, s.last_name, a.time_in, a.time_out, a.student_id 
                                   FROM attendance a 
                                   JOIN students s ON a.student_id = s.student_id 
                                   WHERE a.subject = ? AND a.date = CURDATE() AND a.teacher_id = ?
                                   ORDER BY s.last_name ASC";
                        $stmt_p = $conn->prepare($p_query);
                        $stmt_p->bind_param("si", $active_subject, $teacher_id);
                        $stmt_p->execute();
                        $p_res = $stmt_p->get_result();

                        if($p_res->num_rows > 0) {
                            while($p = $p_res->fetch_assoc()) {
                                $t_in = date("h:i A", strtotime($p['time_in']));
                                $t_out = ($p['time_out'] && $p['time_out'] != '00:00:00') ? date("h:i A", strtotime($p['time_out'])) : '---';
                                echo "<div class='student-row present-row'>
                                        <div class='name-info'>
                                            <b>".strtoupper($p['last_name']).", {$p['first_name']}</b>
                                            <span>In: $t_in | Out: $t_out</span>
                                        </div>
                                        <button class='btn-action btn-out' onclick=\"recordTime('{$p['student_id']}', 'OUT')\"><i class='fa-solid fa-sign-out-alt'></i> OUT</button>
                                      </div>";
                            }
                        } else { echo "<p style='text-align:center; color:#999; margin-top:50px;'>No logs recorded yet.</p>"; }
                        ?>
                    </div>
                </div>

                <div class="monitor-card">
                    <h4 style="color: #444;"><i class="fa-solid fa-users"></i> Class Enrollment</h4>
                    <div id="enrollmentList">
                        <?php
                        $s_query = "SELECT student_id, first_name, last_name, nfc_uid 
                                   FROM students 
                                   WHERE course_section = ? 
                                   AND student_id NOT IN (SELECT student_id FROM attendance WHERE subject = ? AND date = CURDATE() AND teacher_id = ?)
                                   ORDER BY last_name ASC";
                        $stmt_s = $conn->prepare($s_query);
                        $stmt_s->bind_param("ssi", $selected_strand, $active_subject, $teacher_id);
                        $stmt_s->execute();
                        $s_res = $stmt_s->get_result();

                        if($s_res->num_rows > 0) {
                            while($s = $s_res->fetch_assoc()) {
                                echo "<div class='student-row'>
                                        <div class='name-info'>
                                            <b>".strtoupper($s['last_name']).", {$s['first_name']}</b>
                                            <span>UID: {$s['nfc_uid']}</span>
                                        </div>
                                        <button class='btn-action btn-in' onclick=\"recordTime('{$s['student_id']}', 'IN')\"><i class='fa-solid fa-sign-in-alt'></i> IN</button>
                                      </div>";
                            }
                        } else { echo "<p style='text-align:center; color:#198754; margin-top:50px;'>Perfect Attendance!</p>"; }
                        ?>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div style="text-align:center; margin-top:50px;">
                <h1 style="color: #333; margin-bottom: 10px;">Teacher's Portal</h1>
                <p style="color: #666;">Pumili ng Strand para makita ang iyong mga Subjects.</p>
                
                <div class="strand-grid">
                    <a href="?strand=Grade 12 - ABM (Section A)" class="strand-card">
                        <i class="fa-solid fa-chart-line"></i>
                        <h3>ABM</h3>
                    </a>
                    <a href="?strand=Grade 12 - GAS (Section A)" class="strand-card">
                        <i class="fa-solid fa-book"></i>
                        <h3>GAS</h3>
                    </a>
                    <a href="?strand=Grade 12 - HUMSS (Section A)" class="strand-card">
                        <i class="fa-solid fa-users"></i>
                        <h3>HUMSS</h3>
                    </a>
                    <a href="?strand=Grade 12 - STEM (Section A)" class="strand-card">
                        <i class="fa-solid fa-flask"></i>
                        <h3>STEM</h3>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="manualModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeManualModal()">&times;</span>
            <h3 style="color: #0033cc; margin-bottom: 20px;"><i class="fa-solid fa-users-rectangle"></i> Student List</h3>
            <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Strand: <b><?php echo $selected_strand; ?></b></p>
            <div id="modalEnrollmentList">
                <?php
                if ($selected_strand) {
                    $stmt_s->execute();
                    $m_res = $stmt_s->get_result();
                    if($m_res->num_rows > 0):
                        while($m = $m_res->fetch_assoc()): ?>
                            <div class="student-row" style="background:#fdfdfd; border:1px solid #eee;">
                                <div class="name-info">
                                    <b><?php echo strtoupper($m['last_name']) . ", " . $m['first_name']; ?></b>
                                    <span style="color:#0033cc;"><?php echo $selected_strand; ?></span>
                                </div>
                                <button class="btn-action btn-in" onclick="recordTime('<?php echo $m['student_id']; ?>', 'IN')"><i class="fa-solid fa-plus"></i> Add</button>
                            </div>
                        <?php endwhile;
                    endif;
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        function recordTime(studentId, type) {
            const subject = "<?php echo $active_subject; ?>";
            fetch('record_attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `student_id=${studentId}&type=${type}&subject=${encodeURIComponent(subject)}`
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') { location.reload(); } 
                else { alert(data.message); }
            });
        }
        function openManualModal() { document.getElementById('manualModal').style.display = 'flex'; }
        function closeManualModal() { document.getElementById('manualModal').style.display = 'none'; }
        function resetAttendance() {
            const subject = "<?php echo $active_subject; ?>";
            if(confirm("Sigurado ka ba? Mabubura lahat ng logs sa subject na ito ngayon.")) {
                fetch('reset_attendance.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `subject=${encodeURIComponent(subject)}`
                })
                .then(res => res.json())
                .then(data => { if(data.status === 'success') location.reload(); });
            }
        }
        function uploadProfilePic(input, type) {
            let formData = new FormData();
            formData.append('file', input.files[0]);
            formData.append('user_type', type);
            fetch('upload_profile_pic.php', { method: 'POST', body: formData })
            .then(res => res.text()).then(data => { if(data.trim()==="Success") location.reload(); });
        }
    </script>
</body>
</html>