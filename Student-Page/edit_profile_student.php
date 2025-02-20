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
 
$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL
$row = mysqli_fetch_assoc($result);
$editable = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $editable = true;

    if (isset($_POST['edit'])) {
        $editable = true;
    } elseif (isset($_POST['save'])) {
        $fname = mysqli_real_escape_string($conn, $_POST['std_fname']);
        $lname = mysqli_real_escape_string($conn, $_POST['std_lname']);
        $tel = mysqli_real_escape_string($conn, $_POST['std_tel']);
        $email = mysqli_real_escape_string($conn, $_POST['std_email_1']);
        $branch = mysqli_real_escape_string($conn, $_POST['std_branch']);
        $major = mysqli_real_escape_string($conn, $_POST['std_major']);

        // ตั้งค่าพาธสำหรับอัปโหลด
<<<<<<< HEAD
        $upload_dir = "./Images-Profile-Student/";
=======
        $upload_dir = "./Images-Profile-Company/";
>>>>>>> 3c4cd843bca25ca2b11a6ad781d94a48bce07cbd

        // ตรวจสอบการอัปโหลดไฟล์
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $file_tmp = $_FILES['profile_image']['tmp_name'];
            $new_file_name = "profile-" . $row['username'] . ".jpg"; // ตั้งชื่อไฟล์ใหม่
            $new_file_path = $upload_dir . $new_file_name;

            // ย้ายไฟล์ที่อัปโหลดไปที่โฟลเดอร์ปลายทาง
            if (move_uploaded_file($file_tmp, $new_file_path)) {
                $image_data = "profile-" . $row['username']; // เก็บชื่อไฟล์ (ไม่มี .jpg)
            } else {
                echo "<script>alert('อัปโหลดรูปภาพไม่สำเร็จ');</script>";
                $image_data = $row['std_img']; // ใช้ไฟล์เดิม
            }
        } else {
            $image_data = $row['std_img']; // ใช้ไฟล์เดิมหากไม่มีการอัปโหลด
        }

        // อัปเดตข้อมูลในฐานข้อมูล
        $update_query = "UPDATE student SET 
                            std_fname = ?, 
                            std_lname = ?, 
                            std_tel = ?, 
                            std_email_1 = ?, 
                            std_img = ? ,
                            std_major = ?,
                            std_branch = ?
                          WHERE u_id = ?";

        if ($stmt = mysqli_prepare($conn, $update_query)) {
            mysqli_stmt_bind_param($stmt, "sssssssi", $fname, $lname, $tel, $email, $image_data, $major, $branch, $u_id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: profile_student.php");
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




$Name = $row['std_fname'] . ' ' . $row['std_lname'];
$firstLetter = mb_substr($row['std_fname'], 0, 1, "UTF-8");

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
    <div class="container-edit">
            <div class="header-profile2-edit"><p>แก้ไขข้อมูลส่วนตัว</p></div>

            <div class="header-profile-edit"> 
                <a href="student_dashboard.php">Home</a>
                <a href="profile_student.php"> ข้อมูลส่วนตัว</a>
                <a class="Y-button"><img src="../Icon/i8.png""> แก้ไขข้อมูลส่วนตัว</a>
            </div>
    
    <div>
                    
                        <form method="POST" enctype="multipart/form-data" action="edit_profile_student.php">
                            <div class="edit-all-profile">
                                <div class="edit-fix-info">
                                    <p>ชื่อ:</p>
                                    <p>สกุล:</p>
                                    <p>รหัส:</p>
                                    <p>สาขา:</p>
                                    <p>หลักสูตร:</p>
                                    <p>ตำแหน่ง:</p>
                                    <p>Email:</p>
                                    <p>โทรศัพท์:</p>

                                </div>
                                <div class="info-edit">

                                    <input type="text" name="std_fname" value="<?= $row['std_fname'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                    <input type="text" name="std_lname" value="<?= $row['std_lname'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                    <input type="text" value="<?= $row['username'] ?>" disabled>
                                    <input type="text" name="std_branch"value="<?= $row['std_branch'] ?>"<?= !$editable ? 'disabled' : '' ?> required >
                                    <input type="text" name="std_major"value="<?= $row['std_major'] ?>"<?= !$editable ? 'disabled' : '' ?> required >
                                    <input type="text" name="u_type"value="<?= $row['u_type'] ?>" disabled >
                                    <input type="email" name="std_email_1" value="<?= $row['std_email_1'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                    <input type="text"  name="std_tel" value="<?= $row['std_tel'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                    
                                </div>
                                <div class="edit-img">
                                    <img src="..\Icon\i9.png">
                                    <label class="custom-file-upload"><input type="file" name="profile_image" <?= !$editable ? 'disabled' : '' ?>>Choose File</label>
                                    <p> *Accepted file type :  .jpg</p>
                                </div>
                            </div>
                            <div class="button-group">
                                <?php if (!$editable): ?>
                                    <button class="b-red"><a href="edit_profile_student.php" class="cancel-button">ยกเลิก<img src="../Icon/i10.png""></a></button>
                                    <button class="b-blue" type="submit" name="edit">แก้ไข <img src="../Icon/i8.png""></button>
                                 
                                <?php else: ?>
                                    <button class="b-red"><a href="edit_profile_student.php" class="cancel-button">ยกเลิก<img src="../Icon/i10.png""></a></button>
                                    <button class="b-green" type="submit" name="save">บันทึก <img src="../Icon/i8.png""></button>
                                <?php endif; ?>
                            </div>
                        
                    
                        </form>
                
            

    <div>

            
        
    </div>
</body>
</html>
