<?php
session_start();
include '../config.php';

// ตรวจสอบว่าแอดมินล็อกอินหรือยัง
if (!isset($_SESSION['u_type'])) {
    header("Location: ../index.php");
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้ (ต้องเป็น Admin)
if ($_SESSION['u_type'] != 'Admin') {
    header("Location: ../unauthorized.php");
    exit();
}

$editable = false;


// ตรวจสอบว่ามีการกดปุ่มแก้ไขหรือไม่
if (isset($_POST['edit'])) {
    $editable = true;
}

// ดึงข้อมูล u_id ของแอดมินจาก session
$admin_u_id = $_SESSION['u_id'];

// ดึงข้อมูลผู้ใช้แอดมินเพื่อแสดง username ที่มุมขวาบน
$query_admin = "SELECT username FROM users WHERE u_id = ?";
$stmt_admin = $conn->prepare($query_admin);
$stmt_admin->bind_param("i", $admin_u_id);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();
$admin_row = $result_admin->fetch_assoc();
$admin_username = $admin_row['username'];
$firstLetter = mb_substr($admin_username, 0, 1, "UTF-8");

// ตรวจสอบว่า u_id ของผู้ใช้ที่ต้องการแก้ไขถูกส่งมาใน URL หรือไม่
if (isset($_GET['u_id'])) {
    $u_id = $_GET['u_id'];

    // ดึงข้อมูลผู้ใช้ที่ต้องการแก้ไขจากฐานข้อมูล
    $query_user = "
    SELECT 
        users.u_id, users.username, users.password, users.u_type,
        COALESCE(student.std_email_1, staff.stf_email, professor.pf_email, company.comp_contact) AS email,
        COALESCE(student.std_fname, staff.stf_fname, professor.pf_fname, company.comp_name) AS first_name,
        COALESCE(student.std_lname, staff.stf_lname, professor.pf_lname, NULL) AS last_name,
        COALESCE(professor.pf_role, users.u_type) AS position
    FROM users
    LEFT JOIN student ON users.u_id = student.u_id
    LEFT JOIN professor ON users.u_id = professor.u_id
    LEFT JOIN company ON users.u_id = company.u_id
    LEFT JOIN staff ON users.u_id = staff.u_id
    WHERE users.u_id = ?";

    $stmt_user = $conn->prepare($query_user);
    $stmt_user->bind_param("i", $u_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    // ตรวจสอบว่าพบข้อมูลผู้ใช้หรือไม่
    if ($row = $result_user->fetch_assoc()) {
        $username = $row['username'];
        $current_password = $row['password'];
        $email = $row['email'];
        $first_name = $row['first_name'];
        $last_name = $row['last_name'] ?? '';
        $position = $row['position'];
        $u_type = $row['u_type'];
    } else {
        die("ไม่พบข้อมูลผู้ใช้");
    }
} else {
    die("ข้อมูลผู้ใช้ไม่ถูกต้อง");
}

// ถ้ามีการส่งข้อมูล POST และกดปุ่มบันทึก
if (isset($_POST['save'])) {
    try {
        // รับข้อมูลจากฟอร์ม
        $username = trim($_POST['username']);
        $password = !empty($_POST['password']) ? trim($_POST['password']) : $current_password;
        $fname = trim($_POST['fname']);
        $lname = trim($_POST['lname']);
        $u_type = trim($_POST['u_type']);
        
        // ตรวจสอบข้อมูล
        if (empty($username) || empty($fname) || empty($lname)) {
            throw new Exception("กรุณากรอกข้อมูลให้ครบทุกช่อง");
        }

        // เริ่มต้นการทำธุรกรรม
        mysqli_begin_transaction($conn);

        // อัพเดตข้อมูลในตาราง users
        $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, u_type = ? WHERE u_id = ?");
        $stmt->bind_param("sssi", $username, $password, $u_type, $u_id);
        $stmt->execute();

        // อัพเดตข้อมูลในตารางที่เกี่ยวข้อง
        if ($u_type == 'Staff') {
            $stmt = $conn->prepare("UPDATE staff SET stf_fname = ?, stf_lname = ? WHERE u_id = ?");
        } elseif ($u_type == 'Student') {
            $stmt = $conn->prepare("UPDATE student SET std_fname = ?, std_lname = ? WHERE u_id = ?");
        } elseif ($u_type == 'Professor') {
            $stmt = $conn->prepare("UPDATE professor SET pf_fname = ?, pf_lname = ? WHERE u_id = ?");
        }

        $stmt->bind_param("ssi", $fname, $lname, $u_id);
        $stmt->execute();

        // ยืนยันการทำธุรกรรม
        mysqli_commit($conn);

        
        $editable = false;  // กลับไปสู่โหมดแสดงผลหลังจากบันทึก
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
    <title>แก้ไขข้อมูลผู้ใช้</title>
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
                <a href="admin_manage_data.php"><img src="../Icon/i3.png" alt="Form Icon"> จัดการข้อมูล</a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
            <div class="user"><?= htmlspecialchars($admin_username) ?></div>
            <div class="profile-circle"><?= htmlspecialchars($firstLetter) ?></div>
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
        <div class="header-profile2-edit"><p>แก้ไขข้อมูลส่วนตัว</p></div>

        <div class="header-profile-edit"> 
            <a href="admin_dashboard.php">Home</a>
            <a href="admin_manage_data.php">จัดการข้อมูลผู้ใช้</a>
            <a class="Y-button"><img src="../Icon/i8.png"> แก้ไขข้อมูลผู้ใช้</a>
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
            <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" <?= !$editable ? 'disabled' : '' ?> required>
            <input type="password" name="password" placeholder="" <?= !$editable ? 'disabled' : '' ?>>
            <input type="text" name="fname" value="<?= htmlspecialchars($first_name) ?>" <?= !$editable ? 'disabled' : '' ?> required>
            <input type="text" name="lname" value="<?= htmlspecialchars($last_name) ?>" <?= !$editable ? 'disabled' : '' ?> required>
            <select name="u_type" class="info-edit-dropdown" <?= !$editable ? 'disabled' : '' ?> required>
                <option value="Staff" <?= $u_type == 'Staff' ? 'selected' : '' ?>>Staff</option>
                <option value="Student" <?= $u_type == 'Student' ? 'selected' : '' ?>>Student</option>
                <option value="Professor" <?= $u_type == 'Professor' ? 'selected' : '' ?>>Professor</option>
            </select>
        </div>
    </div>
            
    <div class="button-group">
        <?php if (!$editable): ?>
            <button class="b-red"><a href="admin_dashboard.php" class="cancel-button">ยกเลิก<img src="../Icon/i10.png"></a></button>
            <button class="b-blue" type="submit" name="edit">แก้ไข <img src="../Icon/i8.png"></button>
        <?php else: ?>
            <button class="b-red"><a href="admin_dashboard.php" class="cancel-button">ยกเลิก<img src="../Icon/i10.png"></a></button>
            <button class="b-green" type="submit" name="save">บันทึก <img src="../Icon/i8.png"></button>
        <?php endif; ?>
    </div>
</form>
    </div>
</body>
</html>