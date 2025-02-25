<?php 
session_start();
include '..\config.php';

if (!isset($_SESSION['u_type'])) {
    header("Location: ..\index.php");
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้
if ($_SESSION['u_type'] != 'Staff') {
    header("Location: ..\unauthorized.php");
    exit();
}

// ดึงข้อมูลของนักศึกษาจากฐานข้อมูล
$u_id = $_SESSION['u_id']; // รับค่าจาก session ที่เก็บ user_id
$query = "SELECT stf_fname, stf_lname FROM staff WHERE u_id = '$u_id'"; // คำสั่ง SQL ที่ใช้ค้นหาข้อมูลนักศึกษา

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (mysqli_num_rows($result) > 0) {
    // ดึงข้อมูลมาเก็บในตัวแปร
    $row = mysqli_fetch_assoc($result);
    $Name = $row['stf_fname'] . ' ' . $row['stf_lname']; // รวมชื่อและนามสกุล

    $firstLetter = mb_substr($row['stf_fname'], 0, 1, "UTF-8");
    
} 
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style/style-staff.css">
    <script src="../script.js" defer></script>
</head>
<body>

    <div class="header">
        <div class="hamburger-menu">
            <div class="hamburger-icon" onclick="toggleMenu()">
                <img src="../Icon/i5.png" alt="Menu Icon">
            </div> 
            <div class="menu-sidebar" id="menuSidebar">
                <a href="staff_dashboard.php"><img src="../Icon/i1.png" alt="Home Icon"> หน้าหลัก</a>
                <a href="staff_profile.php"><img src="../Icon/i2.png" alt="Profile Icon"> ข้อมูลส่วนตัว</a>
                <a href="staff_manage.php"><img src="../Icon/i3.png" alt="Form Icon"> จัดการข้อมูล</a>
                <a href="staff_regis.php"><img src="../Icon/i4.png" alt="Status Icon"> ใบสมัครสหกิจ</a>
                <a href="staff_regis_page.php"><img src="../Icon/i4.png" alt="Status Icon"> อัปโหลดเอกสาร</a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
        <div class="user"> <?= $Name ?>  </div>
        <div class="profile-circle"><?= $firstLetter ?></div>
        <div class="dropdown">
        
            <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
            <div class="dropdown-content">
                <a href="edit_staff_profile.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
            </div>
        </div>
        </div>
    </div>
    <div class="menu">
        <div class="menu-item">
        <a href="staff_profile.php" class="menu-item"><img src="../Icon/icon-profile.png" alt="ข้อมูลส่วนตัว"></a>
        <a href="staff_profile.php"><p>ข้อมูลส่วนตัว</p></a>
        </div>
        <div class="menu-item">
            <a href="staff_manage.php" class="menu-item"><img src="..\Icon\icon-manage.png" alt="จัดการข้อมูล"></a>
            <a href="staff_manage.php"><p>จัดการข้อมูล</p></a>
        </div>
        <div class="menu-item">
            <a href="staff_regis.php" class="menu-item"><img src="..\Icon\icon_regis.png" alt="ใบสมัคสหกิจ"></a>
            <a href="staff_regis.php"><p>ใบสมัคสหกิจ</p></a>
        </div>
        <div class="menu-item">
        <a href="staff_regis_page.php" class="menu-item"><img src="..\Icon\icon-upload.png" alt="อัพโหลดเอกสาร"></a>
        <a href="staff_regis_page.php"><p>อัพโหลดเอกสาร</p></a>
        </div>
    </div>
</body>
</html>