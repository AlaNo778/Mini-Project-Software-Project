<?php
session_start();
include '../config.php';  // ปรับให้แน่ใจว่า path ถูกต้อง

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['u_type'])) {
    header("Location: ../index.php");
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้ (ต้องเป็น Company เท่านั้น)
if ($_SESSION['u_type'] != 'Company') {
    header("Location: ../unauthorized.php");
    exit();
}

// ดึงข้อมูลบริษัทจากฐานข้อมูล
$u_id = $_SESSION['u_id']; // รับค่าจาก session ที่เก็บ user_id
$query = "SELECT 
            c.comp_name, c.comp_hr_name, c.comp_hr_depart, c.comp_contact, c.comp_tel,
            c.comp_num_add, c.comp_mu, c.comp_road, c.comp_alley, c.comp_sub_district, 
            c.comp_district, c.comp_province, c.comp_postcode, c.comp_img,
            u.username, u.u_type
          FROM Company c 
          JOIN users u ON c.u_id = u.u_id
          WHERE c.u_id = '$u_id'";

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

$upload_dir = "Images-Profile-Company/";

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $Comp_Name = $row['comp_name']; 
    $Comp_HR_Name = $row['comp_hr_name']; 
    $Comp_HR_Depart = $row['comp_hr_depart']; 
    $Comp_Contact = $row['comp_contact']; 
    $Comp_Tel = $row['comp_tel']; 
    $Comp_Num_Add = $row['comp_num_add']; 
    $Comp_Mu = $row['comp_mu']; 
    $Comp_Road = $row['comp_road']; 
    $Comp_Alley = $row['comp_alley']; 
    $Comp_Sub_District = $row['comp_sub_district']; 
    $Comp_District = $row['comp_district']; 
    $Comp_Province = $row['comp_province']; 
    $Comp_Postcode = $row['comp_postcode']; 
    $Comp_Img = $row['comp_img'];
    $Username = $row['username']; 
    $User_Type = $row['u_type'];

    // ดึงตัวอักษรตัวแรกของชื่อบริษัท
    $firstLetter = mb_substr($Comp_Name, 0, 1, "UTF-8");

    // ตรวจสอบรูปโปรไฟล์บริษัท
    if (!empty($Comp_Img) && file_exists($upload_dir . $Comp_Img)) {
        $profile_image_path = $upload_dir . $Comp_Img;
    } else {
        $profile_image_path = "../Icon/default-profile.png";
    }
} else {
    die("No company data found.");
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style/style-company.css">
    <script src="../script.js" defer></script>
</head>
<body>
    <div class="header">
        <div class="hamburger-menu">
            <div class="hamburger-icon" onclick="toggleMenu()">
                <img src="../Icon/i5.png" alt="Menu Icon">
            </div> 
            <div class="menu-sidebar" id="menuSidebar">
                <a href="company_dashboard.php"><img src="../Icon/i1.png" alt="Home Icon"> หน้าหลัก</a>
                <a href="company_profile.php"><img src="../Icon/i2.png" alt="Profile Icon"> ข้อมูลส่วนตัว</a>
                <a href="Company_Intern.php"><img src="../Icon/i3.png" alt="Form Icon"> ใบสหกิจ</a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
            <div class="user"><?= $Comp_Name ?></div>
            <div class="profile-circle"><?= $firstLetter ?></div>
            <div class="dropdown">
                <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>

                <div class="dropdown-content">
                    <a href="company_update.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                    <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="header-profile2"><p>ข้อมูลส่วนตัว</p></div>
        <div class="header-profile"> 
            <a href="Company_dashboard.php">หน้าหลัก</a>
            <a class="Y-button"><img src="../Icon/i8.png"> ข้อมูลส่วนตัว</a>
        </div>
        
    <div class="in-container">
        <div class="profile-card">
            <div>        
                 <img src="Images-Profile-Company<?= $Comp_Img ?>.jpg" alt="Profile">
            </div>

    <div class="profile-info">
        
    <div>
                        <h2><?= $Comp_Name ?></h2>
                        <a href="company_update.php" class="edit-link">แก้ไขข้อมูลส่วนตัว</a>
                    </div>
                    <div class="in-info">
                        <p>Email address</p>
                        <p><?= $Comp_Contact ?></p>
                    </div>
                </div>

</div>

            <div class="info-list">
                <div class="fix-text">
                    <p>บริษัท:</p>
                    <p>ชื่อ(HR):</p>
                    <p>ตำแหน่ง:</p>
                    <p>Email:</p>
                    <p>โทรศัพท์:</p>
                    <p>ที่อยู่:</p>
                </div>
                <div class="nonfix-text">
                    <p><?= $Comp_Name ?></p>
                    <p><?= $Comp_HR_Name ?></p>
                    <p><?= $Comp_HR_Depart ?></p>
                    <p><?= $Comp_Contact ?></p>
                    <p><?= $Comp_Tel ?></p>
                    <p><?= $Comp_Num_Add ?> ม.<?= $Comp_Mu ?> ถนน <?= $Comp_Road ?> ซอย <?= $Comp_Alley ?> ต.<?= $Comp_Sub_District ?> อ.<?= $Comp_District ?> จ.<?= $Comp_Province ?> <?= $Comp_Postcode ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>