<?php 
session_start();
include '../config.php';

if (!isset($_SESSION['u_type']) || $_SESSION['u_type'] != 'Company') {
    header("Location: ../unauthorized.php");
    exit();
}

$u_id = $_SESSION['u_id'];
$query = "SELECT * FROM Company WHERE u_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $u_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$editable = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $editable = true;

    if (isset($_POST['editable'])) {
        $editable = true;
    } elseif (isset($_POST['save'])) {
        $comp_name = mysqli_real_escape_string($conn, $_POST['comp_name']);
        $comp_hr_name = mysqli_real_escape_string($conn, $_POST['comp_hr_name']);
        $comp_contact = mysqli_real_escape_string($conn, $_POST['comp_contact']);
        $comp_tel = mysqli_real_escape_string($conn, $_POST['comp_tel']);
        $comp_num_add = mysqli_real_escape_string($conn, $_POST['comp_num_add']);
        $comp_mu = mysqli_real_escape_string($conn, $_POST['comp_mu']);
        $comp_road = mysqli_real_escape_string($conn, $_POST['comp_road']);
        $comp_alley = mysqli_real_escape_string($conn, $_POST['comp_alley']);
        $comp_sub_district = mysqli_real_escape_string($conn, $_POST['comp_sub_district']);
        $comp_district = mysqli_real_escape_string($conn, $_POST['comp_district']);
        $comp_province = mysqli_real_escape_string($conn, $_POST['comp_province']);
        $comp_postcode = mysqli_real_escape_string($conn, $_POST['comp_postcode']);
        // ไม่ใช้ comp_img เพราะไม่มี input นี้
        // $comp_img = mysqli_real_escape_string($conn, $_POST['comp_img']);

        $upload_dir = "./Images-Profile-Company/";
        $image_data = $row['comp_img'];  // ใช้คอลัมน์ comp_img จากฐานข้อมูล

        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $file_tmp = $_FILES['profile_image']['tmp_name'];
            $new_file_name = "profile-" . $u_id . ".jpg";
            $new_file_path = $upload_dir . $new_file_name;
            if (move_uploaded_file($file_tmp, $new_file_path)) {
                $image_data = $new_file_name;
            } else {
                echo "<script>alert('อัปโหลดรูปภาพไม่สำเร็จ');</script>";
            }
        }

        $update_query = "UPDATE Company SET comp_name=?, comp_hr_name=?, comp_contact=?, comp_tel=?, comp_num_add=?, comp_mu=?, comp_road=?, comp_alley=?, comp_sub_district=?, comp_district=?, comp_province=?, comp_postcode=?, comp_img=? WHERE u_id=?";
        if ($stmt = mysqli_prepare($conn, $update_query)) {
            mysqli_stmt_bind_param($stmt, "sssssssssssssi", $comp_name, $comp_hr_name, $comp_contact, $comp_tel, $comp_num_add, $comp_mu, $comp_road, $comp_alley, $comp_sub_district, $comp_district, $comp_province, $comp_postcode, $image_data, $u_id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: company_profile.php");
                exit();
            } else {
                echo "<script>alert('เกิดข้อผิดพลาดในการอัปเดตข้อมูล');</script>";
            }
            mysqli_stmt_close($stmt);
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
                    <input type="text" name="comp_name" value="<?= $row['comp_name'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    <input type="text" name="comp_hr_name" value="<?= $row['comp_hr_name'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    <input type="email" name="comp_contact" value="<?= $row['comp_contact'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    <input type="text" name="comp_tel" value="<?= $row['comp_tel'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    <input type="text" name="comp_num_add" value="<?= $row['comp_num_add'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    <input type="text" name="comp_mu" value="<?= $row['comp_mu'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    <input type="text" name="comp_road" value="<?= $row['comp_road'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    <input type="text" name="comp_alley" value="<?= $row['comp_alley'] ?>" <?= !$editable ? 'disabled' : '' ?>>
                    <input type="text" name="comp_sub_district" value="<?= $row['comp_sub_district'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    <input type="text" name="comp_district" value="<?= $row['comp_district'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    <input type="text" name="comp_province" value="<?= $row['comp_province'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    <input type="text" name="comp_postcode" value="<?= $row['comp_postcode'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                </div>
                <div class="edit-img">
                    <img src="../Images-Profile-Company/<?= $row['comp_img'] ?>" alt="Logo">
                    <label class="custom-file-upload">
                        <input type="file" name="profile_image" <?= !$editable ? 'disabled' : '' ?>
                               accept="image/jpeg" 
                               onchange="displayFileName(this)">
                        Choose File
                    </label>
                    <span id="fileName"></span>
                    <p> </p>
                </div>
            </div>

            <div class="button-group">
                <?php if (!$editable): ?>
                    <button class="b-red"><a href="company_profile.php" class="cancel-button">ยกเลิก<img src="../Icon/i10.png"></a></button>
                    <button class="b-blue" type="submit" name="edit">แก้ไข <img src="../Icon/i8.png"></button>
                <?php else: ?>
                    <button class="b-red"><a href="company_profile.php" class="cancel-button">ยกเลิก<img src="../Icon/i10.png"></a></button>
                    <button class="b-green" type="submit" name="save">บันทึก <img src="../Icon/i8.png"></button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</body>
</html>
