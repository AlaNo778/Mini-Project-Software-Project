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

// ดึงข้อมูลของอาจารย์จากฐานข้อมูล
$u_id = $_SESSION['u_id']; // รับค่าจาก session ที่เก็บ user_id
$query = "SELECT * 
          FROM professor p 
          JOIN users u ON p.u_id = u.u_id
          WHERE p.u_id = '$u_id'";

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (mysqli_num_rows($result) > 0) {
    // ดึงข้อมูลมาเก็บในตัวแปร
    $row = mysqli_fetch_assoc($result);
} else {
    // หากไม่มีข้อมูลในฐานข้อมูล
    echo "<script>alert('ไม่พบข้อมูล');</script>";
    exit();
}

$editable = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $editable = true;

    if (isset($_POST['edit'])) {
        $editable = true;
    } elseif (isset($_POST['save'])) {
        $fname = mysqli_real_escape_string($conn, $_POST['pf_fname']);
        $lname = mysqli_real_escape_string($conn, $_POST['pf_lname']);
        $tel = mysqli_real_escape_string($conn, $_POST['pf_tel']);
        $email = mysqli_real_escape_string($conn, $_POST['pf_email']);
        $major = mysqli_real_escape_string($conn, $_POST['pf_major']);

        // ตั้งค่าพาธสำหรับอัปโหลด
        $upload_dir = "./Images-Profile-Professor/";

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
                    $image_data = $row['pf_img']; // ใช้ไฟล์เดิม
                }
            } else {
                echo "<script>alert('โปรดเลือกไฟล์ .jpg หรือ .jpeg เท่านั้น');</script>";
                $image_data = $row['pf_img']; // ใช้ไฟล์เดิมหากไฟล์ไม่ตรงตามเงื่อนไข
            }
        } else {
            $image_data = $row['pf_img']; // ใช้ไฟล์เดิมหากไม่มีการอัปโหลด
        }

        // อัปเดตข้อมูลในฐานข้อมูล
        $update_query = "UPDATE professor SET 
                            pf_fname = ?, 
                            pf_lname = ?, 
                            pf_tel = ?, 
                            pf_email = ?, 
                            pf_img = ?,
                            pf_major = ? 
                        WHERE u_id = ?";

        if ($stmt = mysqli_prepare($conn, $update_query)) {
            mysqli_stmt_bind_param($stmt, "ssssssi", $fname, $lname, $tel, $email, $image_data, $major, $u_id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: coordinator_profile.php");
                exit(); // ป้องกันไม่ให้โค้ดทำงานต่อ
            } else {
                echo "<script>alert('เกิดข้อผิดพลาดในการอัปเดตข้อมูล');</script>";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "<script>alert('ไม่สามารถเตรียมคำสั่ง SQL ได้');</script>";
        }
    }
}

// กำหนดชื่อและตัวอักษรแรกของอาจารย์
$Name = $row['pf_fname'] . ' ' . $row['pf_lname'];
$firstLetter = mb_substr($row['pf_fname'], 0, 1, "UTF-8");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coorditor_edit_profile</title>
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
                <a href="#"><img src="../Icon/i1.png" alt="Home Icon"> หน้าหลัก</a>
                <a href="#"><img src="../Icon/i2.png" alt="Profile Icon"> ข้อมูลส่วนตัว</a>
                <a href="#"><img src="../Icon/co1.png" alt="student Icon"> ข้อมูลนักศึกษา</a>
                <a href="#"><img src="../Icon/co2.png" alt="Profile Icon"> ใบสมัครสหกิจ</a>
                <a href="#"><img src="../Icon/co3.png" alt="student Icon"> กำหนดอาจารย์ที่ปรึกษา</a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
        <div class="user"> <?= $Name ?>  </div>
        <div class="profile-circle"><?= $firstLetter ?></div>
        <div class="dropdown">
        
            <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
            <div class="dropdown-content">
                <a href="professor_manage_user.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
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
                    <a class="btn btn-outline-secondary" href="advisor_dashboard.php">ข้อมูลส่วนตัว</a>
                </div>
                <div class="btn-group btn-group-sm" role="group" aria-label="page">
                    <button class="btn btn-warning">เเก้ไขข้อมูลส่วนตัว</button>
                </div>
            </nav>
            <form method="POST" enctype="multipart/form-data" action="coordinator_edit_profile.php">
                            <div class="edit-all-profile">
                                <div class="edit-fix-info">
                                    <p>ชื่อ:</p>
                                    <p>สกุล:</p>
                                    <p>สาขา:</p>
                                    <p>ตำแหน่ง:</p>
                                    <p>Email:</p>
                                    <p>โทรศัพท์:</p>

                                </div>
                                <div class="info-edit">

                                    <input type="text" name="pf_fname" value="<?= $row['pf_fname'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                    <input type="text" name="pf_lname" value="<?= $row['pf_lname'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                    
                                  
                                    <input type="text" name="pf_major"value="<?= $row['pf_major'] ?>"<?= !$editable ? 'disabled' : '' ?> required >
                                    <input type="text" name="u_type"value="<?= $row['u_type'] ?>" disabled >
                                    <input type="email" name="pf_email" value="<?= $row['pf_email'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                    <input type="text"  name="pf_tel" value="<?= $row['pf_tel'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                  
                                </div>
                                <div class="edit-img">
                                    <img src="..\Icon\asp.png">
                                    <label class="custom-file-upload"><input type="file" name="profile_image" <?= !$editable ? 'disabled' : '' ?>>Choose File</label>
                                    <p> *Accepted file type :  .jpg</p>
                                </div>
                            </div>
                            <div class="button-group">
                                <?php if (!$editable): ?>
                                    <button class="b-red"><a href="coordinator_profile.php" class="cancel-button">❌ ยกเลิก</a></button>
                                    <button class="b-blue" type="submit" name="edit">✏️ แก้ไข</button>
                                 
                                <?php else: ?>
                                    <button class="b-red"><a href="coordinator_profile.php" class="cancel-button">❌ ยกเลิก</a></button>
                                    <button class="b-green" type="submit" name="save">✔️ บันทึก</button>
                                <?php endif; ?>
                            </div>
                        
                    
                        </form>
                    

        
    </div>
</body>
</html>
