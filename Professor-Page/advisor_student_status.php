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
    <title>advisor_student_status</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style/style-professor.css">
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
                <a href="#"><img src="../Icon/i3.png" alt="Student Icon"> ข้อมูลนักศึกษา</a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
        <div class="user"> <?= $Name ?>  </div>
        <div class="profile-circle"><?= $firstLetter ?></div>
        <div class="dropdown">
        
            <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
            <div class="dropdown-content">
                <a href="advisor_manage_user.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
            </div>
        </div>
        </div>
    </div>
    <h1 class = "custom-text">ข้อมูลนักศึกษา</h1>
    <div class="breadcrumb"> 
        <a href="index.php" class="breadcrumb-item">Home</a>
        <a href="student_list.php" class="breadcrumb-item">รายชื่อนักศึกษา</a>
        <span class="breadcrumb-item active">ข้อมูลนักศึกษา</span>
    </div>
    
    <div class="progress-container">
        <div class="step-container">
            <div class="step">1</div>
            <div class="step-text">แจ้งความประสงค์<br>ปฏิบัติสหกิจ</div>
        </div>
        <div class="step-container">
            <div class="step">2</div>
            <div class="step-text">อาจารย์ประสาน<br>งานอนุมัติ</div>
        </div>
        <div class="step-container">
            <div class="step">3</div>
            <div class="step-text">เจ้าหน้าที่อนุมัติ</div>
        </div>
        <div class="step-container">
            <div class="step active">4</div>
            <div class="step-text"><strong>กำลังดำเนินการจาก<br>สถานประกอบการ</strong></div>
        </div>
        <div class="step-container">
            <div class="step">5</div>
            <div class="step-text">กำลังดำเนินการจัดทำ<br>หนังสือส่งตัว</div>
        </div>
        <div class="step-container">
            <div class="step">6</div>
            <div class="step-text">หนังสือส่งตัว<br>เสร็จสมบูรณ์</div>
        </div>
    </div>
    <div><h2 class = "element">6510210123 อาทิตย์ สุวรรณ</h2></div>
   
</body>
</html>