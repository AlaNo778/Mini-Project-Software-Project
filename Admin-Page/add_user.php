<?php 
session_start();
include '../config.php';

// ตรวจสอบว่า login หรือไม่
if (!isset($_SESSION['u_type'])) {
    header("Location: ../index.php");
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้ (ต้องเป็น Admin เท่านั้น)
if ($_SESSION['u_type'] != 'Admin') {
    header("Location: ../unauthorized.php");
    exit();
}

// ดึงข้อมูลของนักศึกษาจากฐานข้อมูล
$u_id = $_SESSION['u_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE u_id = ?");
$stmt->bind_param("s", $u_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $Username = $row['username'];
    $Role = $row['u_type'];
    $firstLetter = mb_substr($row['username'], 0, 1, "UTF-8");
} 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // รับค่าจากฟอร์มและทำความสะอาดข้อมูล
        $username = trim($_POST['username']);
        $password = trim($_POST['password']); // ไม่เข้ารหัส (อันตราย ควรเข้ารหัสด้วย password_hash)
        $u_type = trim($_POST['u_type']);
        $fname = trim($_POST['fname']);
        $lname = trim($_POST['lname']);
        $pf_role = null;

        if ($u_type == 'Advisor' || $u_type == 'Coordinator') {
            $pf_role = $u_type;
            $u_type = 'Professor';
        }

        // ป้องกันช่องว่าง และ ตรวจสอบค่าที่ถูกต้อง
        if (empty($username) || empty($password) || empty($fname) || empty($lname)) {
            throw new Exception("กรุณากรอกข้อมูลให้ครบทุกช่อง");
        }

        if (!in_array($u_type, ['Staff', 'Student', 'Professor'])) {
            throw new Exception("ตำแหน่งที่เลือกไม่ถูกต้อง");
        }

        // ตรวจสอบว่า username ซ้ำในฐานข้อมูลหรือไม่
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            throw new Exception("ชื่อผู้ใช้งานนี้มีอยู่ในระบบแล้ว กรุณาเลือกชื่อผู้ใช้งานใหม่");
        }

        // เริ่ม Transaction
        mysqli_begin_transaction($conn);

        // เพิ่มข้อมูลลงตาราง users
        $stmt = $conn->prepare("INSERT INTO users (username, password, u_type) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $u_type);
        $stmt->execute();
        
        // ดึงค่า u_id ล่าสุด
        $last_user_id = $conn->insert_id;

        // ตรวจสอบตำแหน่งและเพิ่มข้อมูลในตารางที่เหมาะสม
        if ($u_type == 'Staff') {
            $stmt = $conn->prepare("INSERT INTO staff (stf_fname, stf_lname, u_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $fname, $lname, $last_user_id);
        } elseif ($u_type == 'Student') {
            $stmt = $conn->prepare("INSERT INTO student (std_fname, std_lname, u_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $fname, $lname, $last_user_id);
        } elseif ($u_type == 'Professor') {
            $stmt = $conn->prepare("INSERT INTO professor (pf_fname, pf_lname, u_id, pf_role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $fname, $lname, $last_user_id, $pf_role);
        }

        $stmt->execute();

        // ยืนยันการทำธุรกรรม
        mysqli_commit($conn);

        $success_message = "เพิ่มผู้ใช้สำเร็จ!";
    } catch (Exception $e) {
        // ยกเลิกการทำธุรกรรม
        mysqli_rollback($conn);
        $error_message = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../Admin-Page/Admin-Style/profile.css">
    <script src="../script.js" defer></script>
</head>
<body>

<div class="header">
        <div class="hamburger-menu">
            <div class="hamburger-icon" onclick="toggleMenu()">
                <img src="../Icon/i5.png" alt="Menu Icon">
            </div> 
            <div class="menu-sidebar" id="menuSidebar">
                <a href="admin_dashboard.php"><img src="../Icon/i1.png" alt="Home Icon"> หน้าหลัก</a>
                <a href="admin_profile.php"><img src="../Icon/i2.png" alt="Profile Icon"> ข้อมูลส่วนตัว</a>
                <a href="admin_manage_data.php"><img src="../Icon/i3.png" alt="Form Icon"> จัดการข้อมูล </a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
        <div class="user"> <?= $Username ?>  </div>
        <div class="profile-circle"><?= $firstLetter ?></div>
        <div class="dropdown">
        
            <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
            <div class="dropdown-content">
                <a href="edit_admin_profile.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
            </div>
        </div>
        </div>
    </div>

    <div class="container-edit">
            <div class="header-profile2-edit"><p>เพิ่มผู้ใช้ใหม่</p></div>

            <div class="header-profile-edit"> 
                <a href="admin_dashboard.php">Home</a>
                <a href="admin_manage_data.php">จัดการข้อมูลผู้ใช้
                <a class="Y-button"><img src="../Icon/i8.png"">เพิ่มผู้ใช้ใหม่</a>
    </div>



    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form method="POST">
    <div class="edit-all-profile">   
        <div class="edit-fix-info">
            <p>Username:</p>
            <p>Password:</p>
            <p>ชื่อ:</p>
            <p>สกุล:</p>
            <p>ตำแหน่ง:</p>         
        </div>

       <div class="info-edit">
            <input type="text" name="username" class="form-control" required>
            <input type="password" name="password" class="form-control" required>
            <input type="text" name="fname" class="form-control" required>
            <input type="text" name="lname" class="form-control" required>

            <select name="u_type" class="info-edit-dropdown" required>
            <option value="">-- เลือกตำแหน่ง --</option>
            <option value="Staff">Staff</option>
            <option value="Student">Student</option>
            <option value="Advisor">Professor-Advisor</option>
            <option value="Coordinator">Professor-Coordinator</option>
            </select>
    </div>

    </div>
    
    <div class="button-group">
    <button type="submit" class="b-green">เพิ่มผู้ใช้</button>
    </div>

</form>



</body>
</html>
