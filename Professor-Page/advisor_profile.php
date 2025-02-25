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
// ดึงข้อมูลของนักศึกษาจากฐานข้อมูล
$u_id = $_SESSION['u_id']; // รับค่าจาก session ที่เก็บ user_id
$query = "SELECT * 
          FROM professor p 
          JOIN users u ON p.u_id = u.u_id
          WHERE p.u_id = '$u_id'";
 // คำสั่ง SQL ที่ใช้ค้นหาข้อมูลนักศึกษา

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (mysqli_num_rows($result) > 0) {
    // ดึงข้อมูลมาเก็บในตัวแปร
    $row = mysqli_fetch_assoc($result);
    $Name = $row['pf_fname'] . ' ' . $row['pf_lname']; // รวมชื่อและนามสกุล
    $pf_Fname = $row['pf_fname'];
    $pf_Lname = $row['pf_lname'];
    $pf_Id = $row['username'];
    $pf_Tel = $row['pf_tel'];
    $pf_Img = $row['pf_img'];
    $pf_Email = $row['pf_email'];
    $pf_Major = $row['pf_major'];
    $pf_type = $row['u_type'];


    $firstLetter = mb_substr($row['pf_fname'], 0, 1, "UTF-8");
    
} 
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asvisor Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style/style-advisor.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script.js" defer></script>
</head>
<body>

    <div class="header">
        <div class="hamburger-menu">
            <div class="hamburger-icon" onclick="toggleMenu()">
                <img src="../Icon/i5.png" alt="Menu Icon">
            </div> 
            <div class="menu-sidebar" id="menuSidebar">
                <a href="advisor_dashboard.php"><img src="../Icon/i1.png" alt="Home Icon"> หน้าหลัก</a>
                <a href="#"><img src="../Icon/i2.png" alt="Profile Icon"> ข้อมูลส่วนตัว</a>
                <a href="advisor_see_student.php"><img src="../Icon/co1.png" alt="student Icon"> ข้อมูลนักศึกษา</a>
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
    <div class="H">
            <h2>ข้อมูลนักศึกษา</h2>
            <nav aria-label="breadcrumb">
                <div class="btn-group btn-group-sm" role="group" aria-label="page">
                    <a class="btn btn-outline-secondary" href="advisor_dashboard.php">หน้าหลัก</a>
                </div>
                <div class="btn-group btn-group-sm" role="group" aria-label="page">
                    <button class="btn btn-warning">ข้อมูลส่วนตัว</button>
                </div>
            </nav>
        

        <div class="in-container">
            <div class="profile-card">
                <div>
                    <img src="Images-Profile-Professor/<?= $pf_Img?>.jpg" alt="">
                </div>
                
                <div class="profile-info">
                    <div>
                        <h2><?= $Name ?></h2>
                        <a href="advisor_edit_profile.php" class="edit-link">แก้ไขข้อมูลส่วนตัว</a>
                    </div>
                    <div class="in-info">
                        <p>Email address</p>
                        <p><?= $pf_Email ?></p>
                    </div>
                </div>

            </div>

            <div class="info-list">

                <div class ="fix-text">
                    <div><p>ชื่อ:</p></div>
                    <div><p>สกุล:</p></div>
                    <div><p>สาขา:</p></div>                  
                    <div><p>ตำแหน่ง:</p></div>
                    <div><p>Email:</p></div>
                    <div><p>โทรศัพท์:</p></div>
                 </div>
                <div class="nonfix-text">
                    <div><p><?= $pf_Fname ?></p></div>
                    <div><p><?= $pf_Lname ?></p></div>              
                    <div><p><?= $pf_Major ?></p></div>
                    <div><p><?= $pf_type ?></p></div>
                    <div><p><?= $pf_Email ?></p></div>
                    <div><p><?= $pf_Tel ?></p></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>