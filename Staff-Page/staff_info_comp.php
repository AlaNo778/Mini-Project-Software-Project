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

$comp_id = $_GET['comp_id'];
$sql = "
        SELECT c.*, d.doc_regis_approve, d.doc_sent_approve
        FROM company c
        LEFT JOIN registration r ON c.comp_id = r.comp_id
        LEFT JOIN document d ON r.doc_id = d.doc_id
        GROUP BY c.comp_id
        HAVING c.comp_id = '$comp_id'";

$results = mysqli_query($conn, $sql);
$comp = mysqli_fetch_assoc($results);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Information</title>
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
            <div class="user"> <?= $Name ?> </div>
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
    <div class="container">
        <h2><?= $comp['comp_name'] ?></h2>
        <div class="card">
            <p><strong>ชื่อบริษัท:</strong> <?= $comp['comp_name'] ?></p>
            <p><strong>แผนก:</strong> <?= $comp['comp_department'] ?></p>
            <p><strong>อีเมล:</strong> <?= $comp['comp_contact'] ?></p>
            <p><strong>ที่อยู่บริษัท:</strong> เลขที่ <?= htmlspecialchars($comp['comp_num_add']) ?> หมู่ <?= htmlspecialchars($comp['comp_mu']) ?> ถนน <?= htmlspecialchars($comp['comp_road']) ?> ซอย <?= htmlspecialchars($comp['comp_alley']) ?><br>
                ตำบล <?= htmlspecialchars($comp['comp_sub_district']) ?> อำเภอ <?= htmlspecialchars($comp['comp_district']) ?> จังหวัด <?= htmlspecialchars($comp['comp_province']) ?> รหัสไปรษณีย์ <?= htmlspecialchars($comp['comp_postcode']) ?></p>
            <p><strong>Fax:</strong> <?= htmlspecialchars($comp['comp_fax']) ?></p>
            <p><strong>ชื่อผู้ประสานงาน:</strong> <?= htmlspecialchars($comp['comp_hr_name']) ?></p>
            <p><strong>ตำแหน่ง:</strong> <?= htmlspecialchars($comp['comp_hr_depart']) ?></p>
            <p><strong>เบอร์โทรศัพท์:</strong> <?= htmlspecialchars($comp['comp_tel']) ?></p>
            <h4 class="mt-4">เอกสารที่เกี่ยวข้อง</h4>
            <p><strong>หนังสือขอความอนุเคราะห์:</strong>
                <?php if ($comp['doc_regis_approve']) { ?>
                    <a href="../file/register/<?= $comp['doc_regis_approve'] ?>" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-file-pdf"></i> หนังสือขอความอนุเคราะห์ (<?= htmlspecialchars($comp['comp_name']) ?>)</a>
                <?php } else { ?>
                    <a class=" btn btn-outline-danger disabled">
                        <i class="fas fa-file-pdf"></i>ไม่มีไฟล์ที่อัพโหลด
                    </a>
                <?php } ?>
            </p>
            <p><strong>หนังสือส่งตัว:</strong>
                <?php if ($comp['doc_sent_approve']) { ?>
                    <a href="../file/sent/<?=$comp['doc_sent_approve']?>" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-file-pdf"></i> หนังสือส่งตัว (<?= htmlspecialchars($comp['comp_name'])?>)</a>
                <?php } else { ?>
                    <a class=" btn btn-outline-danger disabled">
                        <i class="fas fa-file-pdf"></i>ไม่มีไฟล์ที่อัพโหลด
                    </a>
                <?php } ?>
            </p>
        </div>
    </div>

</body>

</html>