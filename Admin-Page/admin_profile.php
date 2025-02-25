<?php 
session_start();
include '..\config.php';

if (!isset($_SESSION['u_type'])) {
    header("Location: ..\index.php");
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้
if ($_SESSION['u_type'] != 'Admin') {
    header("Location: ..\unauthorized.php");
    exit();
}

// ดึงข้อมูลของนักศึกษาจากฐานข้อมูล
$u_id = $_SESSION['u_id']; // รับค่าจาก session ที่เก็บ user_id
$query = "SELECT username, u_type FROM users WHERE u_id = '$u_id'"; // คำสั่ง SQL ที่ใช้ค้นหาข้อมูลนักศึกษา
 
 // คำสั่ง SQL ที่ใช้ค้นหาข้อมูลนักศึกษา

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (mysqli_num_rows($result) > 0) {
    // ดึงข้อมูลมาเก็บในตัวแปร
    $row = mysqli_fetch_assoc($result);
    $Username = $row['username'];
    $Role = $row['u_type'];


    $firstLetter = mb_substr($row['username'], 0, 1, "UTF-8");
    
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
    <div class="container">
            <div class="header-profile2"><p>ข้อมูลส่วนตัว</p></div>
            <div class="header-profile"> 
                <a href="admin_dashboard.php">หน้าหลัก</a>
                <a class="Y-button"><img src="../Icon/i8.png""> ข้อมูลส่วนตัว</a>
            </div>
        
        <div class="in-container">
            <div class="profile-card">
                <div>
                    <img src="../Icon/icon-profile.png" alt="Profile">
                </div>
                
                <div class="profile-info">
                    <div>
                        <h2><?= $Username ?></h2>
                        <a href="edit_admin_profile.php" class="edit-link">แก้ไขข้อมูลส่วนตัว</a>
                    </div>
                </div>

            </div>

            <div class="info-list">

                <div class ="fix-text">
                    <div><p>ชื่อ:</p></div>                 
                    <div><p>ตำแหน่ง:</p></div>
                 </div>
                <div class="nonfix-text">
                    <div><p><?= $Username ?></p></div>
                    <div><p><?= $Role ?></p></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>