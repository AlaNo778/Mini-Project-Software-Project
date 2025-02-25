<?php 
session_start();
include '../config.php';

if (!isset($_SESSION['u_type'])) {
    header("Location: ../index.php");
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้
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

$editable = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit'])) {
        $editable = true;
    } elseif (isset($_POST['save'])) {
        $name = mysqli_real_escape_string($conn, $_POST['username']);
        $pass = mysqli_real_escape_string($conn, $_POST['password']); // ไม่ใช้ password_hash()

        // อัปเดตข้อมูลในฐานข้อมูล
        $update_query = "UPDATE users SET username = ?, password = ? WHERE u_id = ?";

        if ($stmt = $conn->prepare($update_query)) {
            $stmt->bind_param("ssi", $name, $pass, $u_id);
            if ($stmt->execute()) {
                header("Location: admin_profile.php");
                exit();
            } else {
                echo "<script>alert('เกิดข้อผิดพลาดในการอัปเดตข้อมูล');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('ไม่สามารถเตรียมคำสั่ง SQL ได้');</script>";
        }
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
            <div class="header-profile2-edit"><p>แก้ไขข้อมูลส่วนตัว</p></div>

            <div class="header-profile-edit"> 
                <a href="admin_dashboard.php">Home</a>
                <a href="admin_profile.php"> ข้อมูลส่วนตัว</a>
                <a class="Y-button"><img src="../Icon/i8.png""> แก้ไขข้อมูลส่วนตัว</a>
    </div>
    
    <div>
                    
                        <form method="POST" enctype="multipart/form-data" action="edit_admin_profile.php">
                            <div class="edit-all-profile">
                                <div class="edit-fix-info">
                                    <p>ชื่อ:</p>
                                    <p>รหัสผ่าน:</p>
                                    <p>ตำแหน่ง:</p>                              

                                </div>
                                <div class="info-edit">

                                    <input type="text" name="username" value="<?= $row['username'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                    <input type="text" name="password" value="<?= $row['password'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                    <input type="text" name="u_type"value="<?= $row['u_type'] ?>" disabled >
                    
                                </div>                          
                            </div>
                            <div class="button-group">
                                <?php if (!$editable): ?>
                                    <button class="b-red"><a href="edit_admin_profile.php" class="cancel-button">ยกเลิก<img src="../Icon/i10.png""></a></button>
                                    <button class="b-blue" type="submit" name="edit">แก้ไข <img src="../Icon/i8.png""></button>
                                 
                                <?php else: ?>
                                    <button class="b-red"><a href="edit_admin_profile.php" class="cancel-button">ยกเลิก<img src="../Icon/i10.png""></a></button>
                                    <button class="b-green" type="submit" name="save">บันทึก <img src="../Icon/i8.png""></button>
                                <?php endif; ?>
                            </div>
                        
                    
                        </form>
                
            

                                </div>

            
        
    </div>
</body>
</html>
