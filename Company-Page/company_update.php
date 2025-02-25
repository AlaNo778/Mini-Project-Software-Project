<?php 
session_start();
include '../config.php';

if (!isset($_SESSION['u_type'])) {
    header("Location: ../index.php");
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้
if ($_SESSION['u_type'] != 'Company') {
    header("Location: ../unauthorized.php");
    exit();
}

// ดึงข้อมูลของบริษัทจากฐานข้อมูล
$u_id = $_SESSION['u_id']; 
$query = "SELECT c.comp_name, c.comp_hr_name, c.comp_hr_depart, c.comp_contact, c.comp_tel,
         c.comp_num_add, c.comp_mu, c.comp_road, c.comp_alley, c.comp_sub_district, 
         c.comp_district, c.comp_province, c.comp_postcode, c.comp_img,
         u.username, u.u_type
       FROM Company c 
       JOIN users u ON c.u_id = u.u_id
       WHERE c.u_id = ?";
       
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $u_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$editable = false;
$firstLetter = mb_substr($row['comp_name'], 0, 1, "UTF-8"); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit'])) {
        $editable = true;
    } elseif (isset($_POST['save'])) {
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
        $comp_hr_depart = trim($_POST['comp_hr_depart']);

        // ตั้งค่าพาธสำหรับอัปโหลด
        $upload_dir = "./Images-Profile-company/";

        // ตรวจสอบการอัปโหลดไฟล์
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $file_tmp = $_FILES['profile_image']['tmp_name'];
            $file_name = $_FILES['profile_image']['name'];
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // ตรวจสอบว่าเป็นไฟล์ .jpg เท่านั้น
            if ($file_extension == 'jpg' || $file_extension == 'jpeg') {
                $new_file_name = "profile-" . $row['username'] . ".jpg"; // ตั้งชื่อไฟล์ใหม่
                $new_file_path = $upload_dir . $new_file_name;

                // ย้ายไฟล์ที่อัปโหลดไปที่โฟลเดอร์ปลายทาง
                if (move_uploaded_file($file_tmp, $new_file_path)) {
                    $image_data = "profile-" . $row['username']; // เก็บชื่อไฟล์
                } else {
                    echo "<script>alert('อัปโหลดรูปภาพไม่สำเร็จ');</script>";
                    $image_data = $row['comp_img']; // ใช้ไฟล์เดิม
                }
            } else {
                echo "<script>alert('โปรดเลือกไฟล์ .jpg หรือ .jpeg เท่านั้น');</script>";
                $image_data = $row['comp_img']; // ใช้ไฟล์เดิมหากไฟล์ไม่ตรงตามเงื่อนไข
            }
        } else {
            $image_data = $row['comp_img']; // ใช้ไฟล์เดิมหากไม่มีการอัปโหลด
        }

        // อัปเดตข้อมูลในฐานข้อมูล
        $update_query = "UPDATE Company SET 
                            comp_name=?, comp_hr_name=?, comp_contact=?, comp_tel=?, 
                            comp_num_add=?, comp_mu=?, comp_road=?, comp_alley=?, 
                            comp_sub_district=?, comp_district=?, comp_province=?, comp_postcode=?,
                            comp_img=?, comp_hr_depart=?
                         WHERE u_id=?";

        if ($stmt = mysqli_prepare($conn, $update_query)) {
            mysqli_stmt_bind_param($stmt, "ssssssssssssssi", $comp_name, $comp_hr_name, $comp_contact, $comp_tel, 
            $comp_num_add, $comp_mu, $comp_road, $comp_alley, 
            $comp_sub_district, $comp_district, $comp_province, $comp_postcode, 
            $image_data, $comp_hr_depart, $u_id);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: company_profile.php");
                exit();
            } else {
                echo "<script>alert('เกิดข้อผิดพลาดในการอัปเดตข้อมูล');</script>";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "<script>alert('ไม่สามารถเตรียมคำสั่ง SQL ได้');</script>";
        }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                <a href="company_Intern.php"><img src="../Icon/i3.png" alt="Form Icon"> ใบสหกิจ</a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
            <div class="user"><?= $row['comp_name'] ?></div>
            <div class="profile-circle"><?= $firstLetter ?></div>
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
                    <p>ตำแหน่ง:</p>
                    <p>อีเมลติดต่อ:</p>
                    <p>เบอร์โทร:</p>
                    <p>บ้านเลขที่:</p>
                    <p>หมู่:</p>
                    <p>ถนน:</p>
                    <p>ซอย</p>
                    <p>ตำบล:</p>
                    <p>อำเภอ:</p>
                    <p>จังหวัด:</p>
                    <p>รหัสไปรษณีย์:</p>
                </div>
                                <div class="info-edit">
                                <input type="text" name="comp_name" value="<?= htmlspecialchars($row['comp_name']) ?>" required>
                    <input type="text" name="comp_hr_name" value="<?= htmlspecialchars($row['comp_hr_name']) ?>" required>
                    <input type="text" name="comp_hr_depart" value="<?= htmlspecialchars($row['comp_hr_depart']) ?>" required>
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
                                    <p> *Accepted file type :  .jpg</p>
                                </div>
                            </div>
                            <div class="button-group">
                                <?php if (!$editable): ?>
                                    <button class="b-red"><a href="Company_update.php" class="cancel-button">ยกเลิก<img src="../Icon/i10.png""></a></button>
                                    <button class="b-blue" type="submit" name="edit">แก้ไข <img src="../Icon/i8.png""></button>
                                 
                                <?php else: ?>
                                    <button class="b-red"><a href="Company_update.php" class="cancel-button">ยกเลิก<img src="../Icon/i10.png""></a></button>
                                    <button class="b-green" type="submit" name="save">บันทึก <img src="../Icon/i8.png""></button>
                                <?php endif; ?>
                            </div>
                        
                    
                        </form>
                
            

    <div>

        
    </div>
</body>
</html>