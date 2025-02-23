<?php 
session_start();
include '..\config.php';

if (!isset($_SESSION['u_type'])) {
    header("Location: ..\index.php");
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้
if ($_SESSION['u_type'] != 'Student') {
    header("Location: ..\unauthorized.php");
    exit();
}

// ดึงข้อมูลของนักศึกษาจากฐานข้อมูล
$u_id = $_SESSION['u_id']; // รับค่าจาก session ที่เก็บ user_id
$query = "SELECT 
            s.std_id, s.std_fname, s.std_lname, s.std_tel, 
            s.std_img, s.std_email_1, s.std_major, s.std_branch, 
            u.u_type ,u.username
          FROM student s 
          JOIN users u ON s.u_id = u.u_id
          WHERE s.u_id = '$u_id'";
 // คำสั่ง SQL ที่ใช้ค้นหาข้อมูลนักศึกษา

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (mysqli_num_rows($result) > 0) {
    // ดึงข้อมูลมาเก็บในตัวแปร
    $row = mysqli_fetch_assoc($result);
    $Name = $row['std_fname'] . ' ' . $row['std_lname']; // รวมชื่อและนามสกุล
    $firstLetter = mb_substr($row['std_fname'], 0, 1, "UTF-8");
    
} 
$std_id = $row['std_id'];


// ดึงข้อมูลไฟล์จากฐานข้อมูล
$query_doc = "SELECT d.* FROM registration r JOIN document d ON r.doc_id = d.doc_id WHERE r.std_id = '$std_id' "; 
$result2 = mysqli_query($conn, $query_doc); // ใช้ query_doc แทน query2

if (mysqli_num_rows($result2) > 0) { // เปลี่ยนจาก result1 เป็น result2
    $row2 = mysqli_fetch_assoc($result2);
    $doc_id = $row2['doc_id'];
    $doc_regis_approve = $row2['doc_regis_approve'];
    $doc_sent_approve = $row2['doc_sent_approve'];
}

$dir_regis_approve = "./../Document-file/Regis_approve/";
$dir_sent_approve = "./../Document-file/Sent_approve/";


if (empty($doc_id)){
    header("Location: student_dashboard.php");    
    exit();
}


?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style/style-student.css">
    <script src="../script.js" defer></script>
</head>
<body>

    <div class="header">
        <div class="hamburger-menu">
            <div class="hamburger-icon" onclick="toggleMenu()">
                <img src="../Icon/i5.png" alt="Menu Icon">
            </div> 
            <div class="menu-sidebar" id="menuSidebar">
                <a href="student_dashboard.php"><img src="../Icon/i1.png" alt="Home Icon"> หน้าหลัก</a>
                <a href="profile_student.php"><img src="../Icon/i2.png" alt="Profile Icon"> ข้อมูลส่วนตัว</a>
                <a href="application_form.php"><img src="../Icon/i3.png" alt="Form Icon"> กรอกใบสมัคร</a>
                <a href="status_student.php"><img src="../Icon/i4.png" alt="Status Icon"> สถานะ</a>
                <a href="file_student.php"><img src="../Icon/i3.png" alt="Status Icon"> ไฟล์เอกสาร</a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
        <div class="user"> <?= $Name ?>  </div>
        <div class="profile-circle"><?= $firstLetter ?></div>
        <div class="dropdown">
        
            <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
            <div class="dropdown-content">
                <a href="setting_student.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
            </div>
        </div>
        </div>
    </div>
    <div class="container">
            <div class="header-profile2"><p>ไฟล์เอกสาร</p></div>
            <div class="header-profile"> 
                <a href="student_dashboard.php">หน้าหลัก</a>
                <a class="Y-button"><img src="../Icon/i8.png""> ไฟล์เอกสาร</a>
            </div>

            <div class="file-student">
                <div class="file-card">
                    <h3>เอกสารขอความอนุเคราะห์</h3>
                    <a href="<?= $dir_regis_approve . $doc_regis_approve ?>" download="<?= $doc_regis_approve ?>" target="_blank">
                        ดาวน์โหลด <?= $doc_regis_approve ?>
                    </a>
                </div>

                <div class="file-card">
                    <h3>เอกสารมอบตัว</h3>
                    <a href="<?= $dir_sent_approve . $doc_sent_approve ?>" download="<?= $doc_sent_approve ?>" target="_blank">
                        ดาวน์โหลด <?= $doc_sent_approve ?>
                    </a>
                </div>
            </div>
        
        
    </div>
</body>
</html>