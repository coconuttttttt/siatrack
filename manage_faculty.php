<?php
// manage_faculty.php
include 'db_connect.php';

// 1. DELETE FACULTY LOGIC
if(isset($_GET['delete'])){
    $del_id = $_GET['delete'];
    // Burahin muna ang assignments para walang foreign key error
    $conn->query("DELETE FROM classes WHERE teacher_id = $del_id");
    $delete_query = $conn->prepare("DELETE FROM faculty WHERE teacher_id = ?");
    $delete_query->bind_param("i", $del_id);
    if($delete_query->execute()){
        echo "<script>alert('Faculty account permanently removed!'); window.location.href='manage_faculty.php';</script>";
    }
    $delete_query->close();
}

// 2. CREATE / ADD SUBJECT LOGIC (Anti-Duplicate Account)
if(isset($_POST["add_faculty"])){
    $full_name = $_POST["full_name"];
    $email = $_POST["email"]; 
    $department = $_POST["department"];
    $subject_name = $_POST["subject_name"];
    $password = $_POST["password"];

    // I-check muna kung may existing account na gamit ang email
    $check_email = $conn->prepare("SELECT teacher_id FROM faculty WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $res_email = $check_email->get_result();

    if($res_email->num_rows > 0) {
        // MAY ACCOUNT NA: Kunin ang teacher_id at dagdagan lang ang subject assignment
        $row = $res_email->fetch_assoc();
        $existing_id = $row['teacher_id'];

        // I-check kung naka-assign na yung subject na 'yun sa kanya para sa section na 'yun
        $check_class = $conn->prepare("SELECT class_id FROM classes WHERE teacher_id = ? AND subject_name = ? AND course_section = ?");
        $check_class->bind_param("iss", $existing_id, $subject_name, $department);
        $check_class->execute();
        
        if($check_class->get_result()->num_rows > 0) {
            echo "<script>alert('Error: This teacher is already assigned to this subject and section!');</script>";
        } else {
            // I-add lang ang bagong subject assignment sa classes table
            $stmt_class = $conn->prepare("INSERT INTO classes (teacher_id, subject_name, course_section) VALUES (?, ?, ?)");
            $stmt_class->bind_param("iss", $existing_id, $subject_name, $department);
            $stmt_class->execute();
            echo "<script>alert('Notice: Account already exists. New subject has been successfully added to this teacher.'); window.location.href='manage_faculty.php';</script>";
        }
    } else {
        // WALA PANG ACCOUNT: Gawa ng bago (Faculty Table + Classes Table)
        $stmt = $conn->prepare("INSERT INTO faculty (full_name, email, department, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $department, $password);
        
        if($stmt->execute()) {
            $new_teacher_id = $conn->insert_id;
            $stmt_class = $conn->prepare("INSERT INTO classes (teacher_id, subject_name, course_section) VALUES (?, ?, ?)");
            $stmt_class->bind_param("iss", $new_teacher_id, $subject_name, $department);
            $stmt_class->execute();
            echo "<script>alert('Success! New Teacher Account and Subject created.'); window.location.href='manage_faculty.php';</script>";
        } else {
            echo "<script>alert('Database Error!');</script>";
        }
    }
}

// 3. UPDATE FACULTY LOGIC
if(isset($_POST["update_faculty"])){
    $t_id = $_POST["edit_teacher_id"];
    $f_name = $_POST["edit_full_name"];
    $email = $_POST["edit_email"];
    $dept = $_POST["edit_department"];
    $pass = $_POST["edit_password"];
    $update_stmt = $conn->prepare("UPDATE faculty SET full_name=?, email=?, department=?, password=? WHERE teacher_id=?");
    $update_stmt->bind_param("ssssi", $f_name, $email, $dept, $pass, $t_id);
    if($update_stmt->execute()){
        echo "<script>alert('Account successfully updated!'); window.location.href='manage_faculty.php';</script>";
    }
    $update_stmt->close();
}

// Query para makuha ang listahan ng faculty kasama ang huling subject na in-add sa kanila
$query = "SELECT f.*, c.subject_name FROM faculty f 
          LEFT JOIN (SELECT teacher_id, MAX(subject_name) as subject_name FROM classes GROUP BY teacher_id) c 
          ON f.teacher_id = c.teacher_id 
          ORDER BY f.teacher_id ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SIATRACK | Manage Faculty</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; display: flex; }
        
        .sidebar { width: 260px; background: linear-gradient(180deg, #cc0000 0%, #8b0000 100%); color: white; height: 100vh; padding: 30px 20px; position: fixed; box-shadow: 4px 0 10px rgba(0,0,0,0.1);}
        .sidebar a { display: flex; align-items: center; color: rgba(255,255,255,0.85); text-decoration: none; padding: 15px; margin-bottom: 10px; border-radius: 8px; transition: 0.3s; }
        .sidebar .active { background-color: rgba(255,255,255,0.2); color: #ffcc00; font-weight: 600; border-left: 5px solid #ffcc00; }
        
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        .header-banner { background: url('student.jpg') no-repeat center 30%; background-size: cover; height: 140px; border-radius: 12px; margin-bottom: 30px; position: relative; display: flex; align-items: center; padding: 0 30px; overflow: hidden; border-left: 8px solid #ffcc00; }
        .header-banner::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(90deg, rgba(0,51,204,0.9) 0%, rgba(0,0,0,0.4) 100%); z-index: 1; }
        .header-banner h2 { position: relative; z-index: 2; color: white; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }
        
        .card-box { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .create-form { border-left: 5px solid #0033cc; display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;}
        
        .input-group { flex: 1; min-width: 180px; }
        .input-group label { display: block; font-size: 12px; color: #555; margin-bottom: 5px; font-weight: 600; }
        .input-group input, .input-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; font-family: 'Poppins', sans-serif; }
        
        .btn-add { background: #0033cc; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; height: 42px; transition: 0.3s; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        th { background-color: #0033cc; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .btn-edit { background: #ffcc00; color: #00; padding: 6px 12px; border-radius: 5px; border: none; cursor: pointer; font-weight: 600; font-size: 12px; }
        .btn-delete { background: #cc0000; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-weight: 600; font-size: 12px; margin-left: 5px;}

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); justify-content: center; align-items: center; backdrop-filter: blur(3px);}
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 450px; position: relative; border-top: 5px solid #0033cc;}
        .close-btn { position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #888; }
    </style>
</head>
<body>

   <div class="sidebar">
    <div style="text-align: center; margin-bottom: 30px;">
        <img src="sia_logo.png" alt="SIA Logo" style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid white; background: white; object-fit: cover;">
        <h2 style="margin-top: 10px; font-size: 22px;">SIATRACK</h2>
    </div>

    <a href="admin_dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-chart-pie"></i> Dashboard
    </a>
    
    <a href="evaluate_faculty.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'evaluate_faculty.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-star"></i> Faculty Evaluations
    </a>

    <a href="manage_students.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_students.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-users"></i> Manage Students
    </a>

    <a href="manage_faculty.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_faculty.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-chalkboard-user"></i> Manage Faculty
    </a>
    
    <a href="dashboard.php" class="logout-btn" style="margin-top: 30px; background: rgba(220, 53, 69, 0.2) !important;">
        <i class="fa-solid fa-sign-out-alt"></i> Log Out
    </a>
</div>

    <div class="main-content">
        <div class="header-banner">
            <h2><i class="fa-solid fa-chalkboard-user" style="margin-right: 15px;"></i> Manage Faculty Accounts</h2>
        </div>
        
        <div class="card-box">
            <form action="" method="post" class="create-form">
                <div style="width: 100%; margin-bottom: 10px;"><h4 style="color:#0033cc;">Add Faculty or New Subject</h4></div>
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required placeholder="Mr./Ms. Name">
                </div>
                <div class="input-group">
                    <label>Gmail Address</label>
                    <input type="email" name="email" required placeholder="username@gmail.com">
                </div>
                <div class="input-group">
                    <label>Assigned Strand</label>
                    <select name="department" required>
                        <option value="" disabled selected>Select Assignment</option>
                        <option value="Grade 12 - STEM (Section A)">Grade 12 - STEM (Section A)</option>
                        <option value="Grade 12 - ABM (Section A)">Grade 12 - ABM (Section A)</option>
                        <option value="Grade 12 - HUMSS (Section A)">Grade 12 - HUMSS (Section A)</option>
                        <option value="Grade 12 - GAS (Section A)">Grade 12 - GAS (Section A)</option>
                        <option value="Grade 12 - TVL (Section A)">Grade 12 - TVL (Section A)</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Assigned Subject</label>
                    <input type="text" name="subject_name" required placeholder="e.g. Empowerment Tech">
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="text" name="password" required placeholder="Password">
                </div>
                <button type="submit" name="add_faculty" class="btn-add">+ Create/Add</button>
            </form>
        </div>

        <div class="card-box" style="border-left: 5px solid #ffcc00;">
            <label style="font-weight:700; font-size:14px; color:#333;"><i class="fa-solid fa-magnifying-glass"></i> Search Faculty</label>
            <input type="text" id="facultySearch" onkeyup="filterFaculty()" style="width:100%; margin-top:10px; padding:12px; border-radius:8px; border:1px solid #ddd;" placeholder="Search names, emails, or subjects...">
        </div>

        <table id="facultyTable">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Gmail (Username)</th>
                    <th>Last Assigned Strand</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo $row['full_name']; ?></strong></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['department']; ?></td>
                    <td style="white-space: nowrap;">
                        <button class="btn-edit" onclick="openEditModal(<?php echo $row['teacher_id']; ?>, '<?php echo $row['full_name']; ?>', '<?php echo $row['email']; ?>', '<?php echo $row['department']; ?>', '<?php echo $row['password']; ?>')">Edit Info</button>
                        <a href="manage_faculty.php?delete=<?php echo $row['teacher_id']; ?>" class="btn-delete" onclick="return confirm('Delete this account and all assignments?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
            <h3 style="margin-bottom:20px; color:#0033cc;">Update Basic Info</h3>
            <form action="" method="post">
                <input type="hidden" name="edit_teacher_id" id="modal_id">
                <div class="input-group" style="margin-bottom:15px;"><label>Full Name</label><input type="text" name="edit_full_name" id="modal_name" required></div>
                <div class="input-group" style="margin-bottom:15px;"><label>Gmail Address</label><input type="email" name="edit_email" id="modal_email" required></div>
                <div class="input-group" style="margin-bottom:15px;"><label>Last Assigned Class</label>
                    <select name="edit_department" id="modal_dept" required>
                        <option value="Grade 12 - STEM (Section A)">Grade 12 - STEM (Section A)</option>
                        <option value="Grade 12 - ABM (Section A)">Grade 12 - ABM (Section A)</option>
                        <option value="Grade 12 - HUMSS (Section A)">Grade 12 - HUMSS (Section A)</option>
                        <option value="Grade 12 - GAS (Section A)">Grade 12 - GAS (Section A)</option>
                        <option value="Grade 12 - TVL (Section A)">Grade 12 - TVL (Section A)</option>
                    </select>
                </div>
                <div class="input-group" style="margin-bottom:25px;"><label>Password</label><input type="text" name="edit_password" id="modal_pass" required></div>
                <button type="submit" name="update_faculty" class="btn-add" style="width: 100%;">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name, email, dept, pass) {
            document.getElementById('modal_id').value = id;
            document.getElementById('modal_name').value = name;
            document.getElementById('modal_email').value = email;
            document.getElementById('modal_dept').value = dept;
            document.getElementById('modal_pass').value = pass;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        function filterFaculty() {
            let input = document.getElementById("facultySearch").value.toLowerCase();
            let rows = document.querySelectorAll("#facultyTable tbody tr");
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(input) ? "" : "none";
            });
        }

        window.onclick = function(e) { if(e.target.classList.contains('modal')) closeModal(e.target.id); }
    </script>
</body>
</html>