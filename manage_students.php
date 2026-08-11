<?php
// manage_students.php
include 'db_connect.php';

// 1. DELETE STUDENT 
if(isset($_GET['delete'])){
    $del_id = $_GET['delete'];
    $conn->query("DELETE FROM attendance WHERE student_id = $del_id");
    $conn->query("DELETE FROM evaluations WHERE student_id = $del_id");
    $conn->query("DELETE FROM students WHERE student_id = $del_id");
    echo "<script>alert('Student completely removed from the system!'); window.location.href='manage_students.php';</script>";
}

// 2. CREATE STUDENT (Manual Add)
if(isset($_POST['add_student'])){
    $nfc = $_POST['nfc_uid'];
    $fname = $_POST['first_name'];
    $mname = $_POST['middle_name']; 
    $lname = $_POST['last_name'];
    $suffix = $_POST['suffix'];     
    $course = $_POST['course_section'];
    $contact = $_POST['parent_contact'];
    $pass = $_POST['password'];
    
    $stmt = $conn->prepare("INSERT INTO students (nfc_uid, first_name, middle_name, last_name, suffix, course_section, parent_contact, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $nfc, $fname, $mname, $lname, $suffix, $course, $contact, $pass);
    if($stmt->execute()){
        echo "<script>alert('New student added successfully!'); window.location.href='manage_students.php';</script>";
    } else {
        echo "<script>alert('Error adding student. NFC UID might already exist.');</script>";
    }
    $stmt->close();
}

// 3. UPDATE STUDENT (Edit Logic)
if(isset($_POST['update_student'])){
    $id = $_POST['edit_id'];
    $nfc = $_POST['edit_nfc'];
    $fname = $_POST['edit_fname'];
    $mname = $_POST['edit_mname'];   
    $lname = $_POST['edit_lname'];
    $suffix = $_POST['edit_suffix']; 
    $course = $_POST['edit_course'];
    $contact = $_POST['edit_contact'];
    $pass = $_POST['edit_password']; 
    
    $update = $conn->prepare("UPDATE students SET nfc_uid=?, first_name=?, middle_name=?, last_name=?, suffix=?, course_section=?, parent_contact=?, password=? WHERE student_id=?");
    $update->bind_param("ssssssssi", $nfc, $fname, $mname, $lname, $suffix, $course, $contact, $pass, $id);
    if($update->execute()){
        echo "<script>alert('Student record updated successfully!'); window.location.href='manage_students.php';</script>";
    }
    $update->close();
}

// 4. CREATE STUDENT (CSV Import)
if(isset($_POST["import"])){
    $fileName = $_FILES["file"]["tmp_name"];
    if($_FILES["file"]["size"] > 0){
        $file = fopen($fileName, "r");
        fgetcsv($file); 
        $success_count = 0;
        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
            $stmt = $conn->prepare("INSERT IGNORE INTO students (nfc_uid, first_name, middle_name, last_name, suffix, course_section, parent_contact, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $column[0], $column[1], $column[2], $column[3], $column[4], $column[5], $column[6], $column[7]);
            if($stmt->execute() && $stmt->affected_rows > 0) { $success_count++; }
        }
        echo "<script>alert('{$success_count} students successfully imported!'); window.location.href='manage_students.php';</script>";
    }
}

// Kunin ang students at i-grugrupo (Strand -> Section -> Students)
$query = "SELECT * FROM students ORDER BY course_section ASC, last_name ASC";
$result = $conn->query($query);

$students_grouped = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $course = empty($row['course_section']) ? "Unassigned Students" : $row['course_section'];
        if (preg_match('/Grade 12 - (.*?) \((.*?)\)/', $course, $matches)) {
            $strand = $matches[1]; 
            $section = $matches[2]; 
        } else {
            $strand = "Others";
            $section = $course;
        }
        $students_grouped[$strand][$section][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SIATRACK | Manage Students</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
       * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; display: flex; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #cc0000 0%, #8b0000 100%); color: white; height: 100vh; padding: 30px 20px; position: fixed; overflow-y: auto; box-shadow: 4px 0 10px rgba(0,0,0,0.1);}
        .sidebar a { display: flex; align-items: center; color: rgba(255,255,255,0.85); text-decoration: none; padding: 15px; margin-bottom: 10px; border-radius: 8px; transition: 0.3s; }
        .sidebar a i { margin-right: 15px; width: 20px; text-align: center; }
        .sidebar a:hover { background-color: rgba(255,255,255,0.15); color: white; transform: translateX(5px); }
        .sidebar .active { background-color: rgba(255,255,255,0.2); color: #ffcc00; font-weight: 600; border-left: 5px solid #ffcc00; }
        .logout-btn { position: absolute; bottom: 30px; width: calc(100% - 40px); background: rgba(0,0,0,0.3)!important; justify-content: center; }
        
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        .header-banner { background: url('student.jpg') no-repeat center 30%; background-size: cover; height: 140px; border-radius: 12px; margin-bottom: 30px; position: relative; display: flex; align-items: center; padding: 0 30px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.15); border-left: 8px solid #ffcc00; }
        .header-banner::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(90deg, rgba(0,51,204,0.9) 0%, rgba(0,0,0,0.4) 100%); z-index: 1; }
        .header-banner h2 { position: relative; z-index: 2; color: white; font-size: 28px; margin: 0; font-weight: 700; letter-spacing: 1px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);}

        .action-container { display: flex; gap: 20px; margin-bottom: 25px; }
        .card-box { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); flex: 1; }
        
        .btn-upload { background: #198754; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 12px; } 
        .btn-add-new { background: #0033cc; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; height: 100%; width: 100%; font-size: 16px; transition: 0.3s;} 
        .btn-add-new:hover { background: #002299; transform: translateY(-2px); }
        
        .search-input { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 14px; transition: 0.3s; }
        .search-input:focus { border-color: #0033cc; outline: none; box-shadow: 0 0 5px rgba(0,51,204,0.2); }

        .strands-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .strand-box { background: white; border: 2px solid #0033cc; border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .strand-box:hover { background: #f0f4ff; transform: translateY(-3px); }
        .strand-box.active-strand { background: #0033cc; color: white; }
        .strand-box.active-strand i { color: #ffcc00 !important; }

        .strand-content-area { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 30px;}
        .section-box { background: #f8f9fa; border-left: 5px solid #ffcc00; padding: 15px 20px; border-radius: 8px; margin-bottom: 10px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: 600; color: #0033cc; }
        .section-table-area { margin-bottom: 20px; border: 1px solid #eee; border-radius: 8px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px;}
        th { background-color: #f0f2f5; font-weight: 600; }
        
        .btn-edit { background: #ffcc00; color: #000; padding: 6px 10px; border-radius: 4px; border: none; cursor: pointer; font-weight: 600; transition: 0.3s;} 
        .btn-delete { background: #cc0000; color: white; padding: 6px 10px; border-radius: 4px; text-decoration: none; font-weight: 600; transition: 0.3s;} 
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); justify-content: center; align-items: center; backdrop-filter: blur(3px);}
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 100%; max-width: 550px; position: relative; border-top: 5px solid #0033cc;}
        .close-btn { position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #888; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; font-size: 13px; color: #555; margin-bottom: 5px; font-weight: 600; }
        .input-group input, .input-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-family: 'Poppins', sans-serif; }
        .form-row { display: flex; gap: 15px; }
        .form-row .input-group { flex: 1; }
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
            <h2><i class="fa-solid fa-users-gear" style="margin-right: 10px;"></i> Manage Students</h2>
        </div>
        
        <div class="action-container">
            <div class="card-box" style="border-left: 5px solid #0033cc;">
                <button onclick="openModal('addModal')" class="btn-add-new"><i class="fa-solid fa-user-plus"></i> Add New Student Manually</button>
            </div>
            
            <div class="card-box" style="border-left: 5px solid #198754;">
                <h4 style="margin-bottom: 10px; font-size: 14px;"><i class="fa-solid fa-file-excel"></i> Excel Tools (CSV)</h4>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <form action="" method="post" enctype="multipart/form-data" style="display: flex; gap: 5px;">
                        <input type="file" name="file" accept=".csv" required style="font-size: 11px; width: 140px;">
                        <button type="submit" name="import" class="btn-upload">Import</button>
                    </form>
                    <a href="export_students.php" class="btn-upload" style="background: #0033cc; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-file-export"></i> Export All
                    </a>
                </div>
            </div>
        </div>

        <div class="card-box" style="border-left: 5px solid #ffcc00; margin-bottom: 25px;">
            <h4 style="margin-bottom: 10px; font-size: 15px; color: #333;"><i class="fa-solid fa-magnifying-glass"></i> Search Student</h4>
            <input type="text" id="searchInput" onkeyup="searchStudent()" class="search-input" placeholder="Type name or UID...">
        </div>

        <?php if(empty($students_grouped)): ?>
            <div class="card-box" style="text-align: center; color: #555;">No students found.</div>
        <?php else: ?>
            
            <div class="strands-grid">
                <?php foreach($students_grouped as $strand => $sections): 
                    $strand_id = md5($strand);
                ?>
                    <div id="box-<?php echo $strand_id; ?>" class="strand-box" onclick="showStrand('<?php echo $strand_id; ?>')">
                        <i class="fa-solid fa-graduation-cap" style="font-size: 28px; margin-bottom: 10px; color: #0033cc;"></i><br>
                        <strong><?php echo htmlspecialchars($strand); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php foreach($students_grouped as $strand => $sections): 
                $strand_id = md5($strand);
            ?>
                <div id="strand-<?php echo $strand_id; ?>" class="strand-content-area" style="display:none;">
                    <h3 style="color: #0033cc; margin-bottom: 20px; border-bottom: 2px solid #f0f2f5;">
                        Grade 12 - <?php echo htmlspecialchars($strand); ?>
                    </h3>
                    <?php foreach($sections as $section => $students): 
                        $section_id = md5($strand . $section);
                    ?>
                        <div class="section-box" onclick="toggleSection('sec-<?php echo $section_id; ?>')">
                            <span><i class="fa-solid fa-users"></i> <?php echo htmlspecialchars($section); ?></span>
                            <span style="background: #0033cc; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px;"><?php echo count($students); ?> Students</span>
                        </div>
                        <div id="sec-<?php echo $section_id; ?>" class="section-table-area" style="display:none;">
                            <table>
                                <thead>
                                    <tr><th>NFC UID</th><th>Full Name</th><th>Contact</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($students as $row): 
                                        $full_name = "{$row['first_name']} " . (!empty($row['middle_name']) ? $row['middle_name']." " : "") . "{$row['last_name']} {$row['suffix']}";
                                    ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($row['nfc_uid']); ?></code></td>
                                            <td><strong><?php echo htmlspecialchars($full_name); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['parent_contact']); ?></td>
                                            <td>
                                                <button class='btn-edit' onclick="openEditModal('<?php echo $row['student_id']; ?>', '<?php echo $row['nfc_uid']; ?>', '<?php echo $row['first_name']; ?>', '<?php echo $row['middle_name']; ?>', '<?php echo $row['last_name']; ?>', '<?php echo $row['suffix']; ?>', '<?php echo $row['course_section']; ?>', '<?php echo $row['parent_contact']; ?>', '<?php echo $row['password']; ?>')"><i class='fa-solid fa-pen'></i></button>
                                                <a href='manage_students.php?delete=<?php echo $row['student_id']; ?>' class='btn-delete' onclick="return confirm('Delete this student?');"><i class='fa-solid fa-trash'></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('addModal')">&times;</span>
            <h3 style="margin-bottom: 20px; color: #0033cc;">Add Student</h3>
            <form action="" method="post">
                <div class="input-group"><label>NFC UID</label><input type="text" name="nfc_uid" required></div>
                <div class="form-row">
                    <div class="input-group"><label>First Name</label><input type="text" name="first_name" required></div>
                    <div class="input-group"><label>Middle Name</label><input type="text" name="middle_name"></div>
                </div>
                <div class="form-row">
                    <div class="input-group"><label>Last Name</label><input type="text" name="last_name" required></div>
                    <div class="input-group"><label>Suffix</label><input type="text" name="suffix"></div>
                </div>
                <div class="form-row">
                    <div class="input-group"><label>Strand & Section</label>
                        <select name="course_section" required>
                            <option value="Grade 12 - STEM (Section A)">Grade 12 - STEM (Section A)</option>
                            <option value="Grade 12 - ABM (Section A)">Grade 12 - ABM (Section A)</option>
                            <option value="Grade 12 - HUMSS (Section A)">Grade 12 - HUMSS (Section A)</option>
                            <option value="Grade 12 - GAS (Section A)">Grade 12 - GAS (Section A)</option>
                        </select>
                    </div>
                    <div class="input-group"><label>Parent Contact</label><input type="text" name="parent_contact" required></div>
                </div>
                <input type="hidden" name="password" value="123456">
                <button type="submit" name="add_student" class="btn-add-new" style="background:#0033cc; height: 45px;">Save Student</button>
            </form>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
            <h3 style="margin-bottom: 20px; color: #ffcc00;">Edit Student Record</h3>
            <form action="" method="post">
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="input-group"><label>NFC UID</label><input type="text" name="edit_nfc" id="edit_nfc" required></div>
                <div class="form-row">
                    <div class="input-group"><label>First Name</label><input type="text" name="edit_fname" id="edit_fname" required></div>
                    <div class="input-group"><label>Middle Name</label><input type="text" name="edit_mname" id="edit_mname"></div>
                </div>
                <div class="form-row">
                    <div class="input-group"><label>Last Name</label><input type="text" name="edit_lname" id="edit_lname" required></div>
                    <div class="input-group"><label>Suffix</label><input type="text" name="edit_suffix" id="edit_suffix"></div>
                </div>
                <div class="form-row">
                    <div class="input-group"><label>Strand & Section</label>
                        <select name="edit_course" id="edit_course" required>
                            <option value="Grade 12 - STEM (Section A)">Grade 12 - STEM (Section A)</option>
                            <option value="Grade 12 - ABM (Section A)">Grade 12 - ABM (Section A)</option>
                            <option value="Grade 12 - HUMSS (Section A)">Grade 12 - HUMSS (Section A)</option>
                            <option value="Grade 12 - GAS (Section A)">Grade 12 - GAS (Section A)</option>
                        </select>
                    </div>
                    <div class="input-group"><label>Parent Contact</label><input type="text" name="edit_contact" id="edit_contact" required></div>
                </div>
                <div class="input-group"><label>Login Password</label><input type="text" name="edit_password" id="edit_password" required></div>
                <button type="submit" name="update_student" class="btn-add-new" style="background:#ffcc00; color: black; height: 45px;">Update Student Record</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        
        function openEditModal(id, nfc, fname, mname, lname, suffix, course, contact, pass) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nfc').value = nfc;
            document.getElementById('edit_fname').value = fname;
            document.getElementById('edit_mname').value = mname;
            document.getElementById('edit_lname').value = lname;
            document.getElementById('edit_suffix').value = suffix;
            document.getElementById('edit_course').value = course;
            document.getElementById('edit_contact').value = contact;
            document.getElementById('edit_password').value = pass;
            openModal('editModal');
        }

        function showStrand(id) {
            document.querySelectorAll('.strand-content-area').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.strand-box').forEach(el => el.classList.remove('active-strand'));
            document.getElementById('strand-' + id).style.display = 'block';
            document.getElementById('box-' + id).classList.add('active-strand');
        }

        function toggleSection(id) {
            let el = document.getElementById(id);
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
        }

        function searchStudent() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            if (input.length > 0) {
                document.querySelectorAll('.strand-content-area, .section-table-area').forEach(el => el.style.display = 'block');
            } else {
                document.querySelectorAll('.strand-content-area, .section-table-area').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.strand-box').forEach(el => el.classList.remove('active-strand'));
            }
            document.querySelectorAll("tbody tr").forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(input) ? "" : "none";
            });
        }
    </script>
</body>
</html>