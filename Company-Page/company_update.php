<?php
session_start();
include '../config.php';

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['u_type']) || $_SESSION['u_type'] != 'Company') {
    header("Location: ../index.php");
    exit();
}

$u_id = $_SESSION['u_id'];
$editable = true;

// ดึงข้อมูลบริษัทจากฐานข้อมูล
$query = "SELECT * FROM Company WHERE u_id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $u_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        die("ไม่พบข้อมูลบริษัท");
    }
    $stmt->close();
} else {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลบริษัท!");
}

// ตรวจสอบว่ามีการกดปุ่มอัปเดตข้อมูลหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comp_name = trim($_POST['comp_name']);
    $comp_hr_name = trim($_POST['comp_hr_name']);
    $comp_contact = trim($_POST['comp_contact']);
    $comp_tel = trim($_POST['comp_tel']);
    $comp_num_add = trim($_POST['comp_num_add']);
    $comp_mu = trim($_POST['comp_mu']);
    $comp_road = trim($_POST['comp_road']);
    $comp_alley = trim($_POST['comp_alley']);
    $comp_sub_district = trim($_POST['comp_sub_district']);
    $comp_district = trim($_POST['comp_district']);
    $comp_province = trim($_POST['comp_province']);
    $comp_postcode = trim($_POST['comp_postcode']);

    // ตรวจสอบว่าข้อมูลครบถ้วน
    if (
        !empty($comp_name) && !empty($comp_hr_name) && !empty($comp_contact) && 
        !empty($comp_tel) && !empty($comp_num_add) && !empty($comp_mu) &&
        !empty($comp_road) && !empty($comp_sub_district) && !empty($comp_district) && 
        !empty($comp_province) && !empty($comp_postcode)
    ) {
        $query = "UPDATE Company 
                  SET comp_name=?, comp_hr_name=?, comp_contact=?, comp_tel=?, 
                      comp_num_add=?, comp_mu=?, comp_road=?, comp_alley=?, 
                      comp_sub_district=?, comp_district=?, comp_province=?, comp_postcode=?
                  WHERE u_id=?";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param(
                "ssssssssssssi", 
                $comp_name, $comp_hr_name, $comp_contact, $comp_tel, 
                $comp_num_add, $comp_mu, $comp_road, $comp_alley, 
                $comp_sub_district, $comp_district, $comp_province, $comp_postcode, 
                $u_id
            );

            if ($stmt->execute()) {
                $_SESSION['success'] = "อัปเดตข้อมูลสำเร็จ!";
                header("Location: company_profile.php");
                exit();
            } else {
                $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล!";
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL!";
        }
    } else {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน!";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลบริษัท</title>
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
            <div class="user"><?= $row['comp_name'] ?></div>
            <div class="profile-circle"><?= substr($row['comp_name'], 0, 1) ?></div>
            <div class="dropdown">
                <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-content">
                    <a href="edit_profile_company.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                    <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-edit">
        <div class="header-profile2-edit"><p>แก้ไขข้อมูลบริษัท</p></div>
        <div class="header-profile-edit"> 
            <a href="company_dashboard.php">Home</a>
            <a href="company_profile.php"> ข้อมูลบริษัท</a>
            <a class="Y-button"><img src="../Icon/i8.png"> แก้ไขข้อมูลบริษัท</a>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="edit-all-profile">
                <div class="edit-fix-info">
                    <p>ชื่อบริษัท:</p>
                    <p>ชื่อ HR:</p>
                    <p>อีเมลติดต่อ:</p>
                    <p>เบอร์โทร:</p>
                    <p>บ้านเลขที่:</p>
                    <p>หมู่:</p>
                    <p>ถนน:</p>
                    <p>ซอย:</p>
                    <p>ตำบล:</p>
                    <p>อำเภอ:</p>
                    <p>จังหวัด:</p>
                    <p>รหัสไปรษณีย์:</p>
                </div>
                <div class="info-edit">
                    <input type="text" name="comp_name" value="<?= htmlspecialchars($row['comp_name']) ?>" required>
                    <input type="text" name="comp_hr_name" value="<?= htmlspecialchars($row['comp_hr_name']) ?>" required>
                    <input type="email" name="comp_contact" value="<?= htmlspecialchars($row['comp_contact']) ?>" required>
                    <input type="text" name="comp_tel" value="<?= htmlspecialchars($row['comp_tel']) ?>" required>
                    <input type="text" name="comp_num_add" value="<?= htmlspecialchars($row['comp_num_add']) ?>" required>
                    <input type="text" name="comp_mu" value="<?= htmlspecialchars($row['comp_mu']) ?>" required>
                    <input type="text" name="comp_road" value="<?= htmlspecialchars($row['comp_road']) ?>" required>
                    <input type="text" name="comp_alley" value="<?= htmlspecialchars($row['comp_alley']) ?>">
                    <input type="text" name="comp_sub_district" value="<?= htmlspecialchars($row['comp_sub_district']) ?>" required>
                    <input type="text" name="comp_district" value="<?= htmlspecialchars($row['comp_district']) ?>" required>
                    <input type="text" name="comp_province" value="<?= htmlspecialchars($row['comp_province']) ?>" required>
                    <input type="text" name="comp_postcode" value="<?= htmlspecialchars($row['comp_postcode']) ?>" required>
                </div>
                <div class="edit-img">
                    <img src="..\Icon\i9.png">
                    <label class="custom-file-upload"><input type="file" name="profile_image" <?= !$editable ? 'disabled' : '' ?>>Choose File</label>
                    <p> *Accepted file type : .jpg</p>
                </div>
            </div>

            <div class="button-group">
                <button class="b-red"><a href="company_profile.php" class="cancel-button">ยกเลิก<img src="../Icon/i10.png"></a></button>
                <button class="b-green" type="submit" name="save">บันทึก <img src="../Icon/i8.png"></button>
            </div>
        </form>
    </div>
</body>
</html>
