<?php 
session_start();
include '..\config.php';

if (!isset($_SESSION['u_type'])) {
    header("Location: ..\index.php");
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้
if ($_SESSION['u_type'] != 'Professor') {
    header("Location: ..\unauthorized.php");
    exit();
}

// ดึงข้อมูลของนักศึกษาจากฐานข้อมูล
$u_id = $_SESSION['u_id']; // รับค่าจาก session ที่เก็บ user_id
$query = "SELECT pf_fname, pf_lname FROM professor WHERE u_id = '$u_id'"; // คำสั่ง SQL ที่ใช้ค้นหาข้อมูลนักศึกษา

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (mysqli_num_rows($result) > 0) {
    // ดึงข้อมูลมาเก็บในตัวแปร
    $row = mysqli_fetch_assoc($result);
    $Name = $row['pf_fname'] . ' ' . $row['pf_lname']; // รวมชื่อและนามสกุล

    $firstLetter = mb_substr($row['pf_fname'], 0, 1, "UTF-8");
    
} 
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style/style-coordinator.css">
    <script src="../script.js" defer></script>
</head>
<body>

    <div class="header">
        <div class="hamburger-menu">
            <div class="hamburger-icon" onclick="toggleMenu()">
                <img src="../Icon/i5.png" alt="Menu Icon">
            </div> 
            <div class="menu-sidebar" id="menuSidebar">
                <a href="#"><img src="../Icon/i1.png" alt="Home Icon"> หน้าหลัก</a>
                <a href="#"><img src="../Icon/i2.png" alt="Profile Icon"> ข้อมูลส่วนตัว</a>
                <a href="#"><img src="../Icon/co1.png" alt="student Icon"> ข้อมูลนักศึกษา</a>
                <a href="coordinator_regis.php"><img src="../Icon/co2.png" alt="Profile Icon"> ใบสมัครสหกิจ</a>
                <a href="coordinator_assign_advisor.php"><img src="../Icon/co3.png" alt="student Icon"> กำหนดอาจารย์ที่ปรึกษา</a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
        <div class="user"> <?= $Name ?>  </div>
        <div class="profile-circle"><?= $firstLetter ?></div>
        <div class="dropdown">
        
            <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
            <div class="dropdown-content">
                <a href="#"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
            </div>
        </div>
        </div>
    </div>
    <div class="menu">
        <div class="menu-item">
            <img src="..\Icon\icon-profile.png" alt="ข้อมูลส่วนตัว">
            <p>ข้อมูลส่วนตัว</p>
        </div>
        <div class="menu-item">
            <img src="..\Icon\info_student.png" alt="ข้อมูลนักศึกษา">
            <p>ข้อมูลนักศึกษา</p>
        </div>
        <div class="menu-item">
            <a href="coordinator_regis.php"><img src="..\Icon\regis.png" alt="ใบสมัครสหกิจ"></a>
            <p>ใบสมัครสหกิจ</p>
        </div>
        <div class="menu-item">
            <a href="coordinator_assign_advisor.php"><img src="..\Icon\define.png" alt="กำหนดอาจารย์ที่ปรึกษา"></a>
            <p>กำหนดอาจารย์ที่ปรึกษา</p>
        </div>
    </div>
</body>
</html>