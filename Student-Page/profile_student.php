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
    $Std_Fname = $row['std_fname'];
    $Std_Lname = $row['std_lname'];
    $Std_Id = $row['username'];
    $Std_Tel = $row['std_tel'];
    $Std_Img = $row['std_img'];
    $Std_Email = $row['std_email_1'];
    $Std_Major = $row['std_major'];
    $Std_Branch = $row['std_branch'];
    $Std_type = $row['u_type'];


    $firstLetter = mb_substr($row['std_fname'], 0, 1, "UTF-8");
    
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
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
        <div class="user"> <?= $Name ?>  </div>
        <div class="profile-circle"><?= $firstLetter ?></div>
        <div class="dropdown">
        
            <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
            <div class="dropdown-content">
                <a href="edit_profile_student.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
            </div>
        </div>
        </div>
    </div>
    <div class="container">
            <div class="header-profile2"><p>ข้อมูลส่วนตัว</p></div>
            <div class="header-profile"> 
                <a href="student_dashboard.php">หน้าหลัก</a>
                <a class="Y-button"><img src="../Icon/i8.png""> ข้อมูลส่วนตัว</a>
            </div>
        
        <div class="in-container">
            <div class="profile-card">
                <div>
                    <img src="Images-Profile-Student/<?= $Std_Img?>.jpg" alt="Profile">
                </div>
                
                <div class="profile-info">
                    <div>
                        <h2><?= $Name ?></h2>
                        <a href="edit_profile_student.php" class="edit-link">แก้ไขข้อมูลส่วนตัว</a>
                    </div>
                    <div class="in-info">
                        <p>Email address</p>
                        <p><?= $Std_Email ?></p>
                    </div>
                </div>

            </div>

            <div class="info-list">

                <div class ="fix-text">
                    <div><p>ชื่อ:</p></div>
                    <div><p>สกุล:</p></div>
                    <div><p>รหัส:</p></div>
                    <div><p>สาขา:</p></div>
                    <div><p>หลักสูตร:</p></div>
                    <div><p>ตำแหน่ง:</p></div>
                    <div><p>Email:</p></div>
                    <div><p>โทรศัพท์:</p></div>
                 </div>
                <div class="nonfix-text">
                    <div><p><?= $Std_Fname ?></p></div>
                    <div><p><?= $Std_Lname ?></p></div>
                    <div><p><?= $Std_Id ?></p></div>
                    <div><p><?= $Std_Major ?></p></div>
                    <div><p><?= $Std_Branch ?></p></div>
                    <div><p><?= $Std_type ?></p></div>
                    <div><p><?= $Std_Email ?></p></div>
                    <div><p><?= $Std_Tel ?></p></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>