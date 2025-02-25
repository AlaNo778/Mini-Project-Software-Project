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

$comp_id = $_GET["comp_id"];
$doc_id = $_GET["doc_id"];
$file_type = $_GET["file_type"];
?>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["upload_file"])) {
    $comp_id = $_POST["comp_id"];
    $doc_id = $_POST["doc_id"];
    $file_type = $_POST["file_type"];

    if ($_FILES["upload_file"]["type"] !== "application/pdf") {
        echo '<script>alert("กรุณาอัพโหลดไฟล์ PDF เท่านั้น");</script>';
        exit;
    }

    $target_dir = ($file_type == 'doc_regis_approve') ? "../File/File_regis_ap/" : "../File/File_sent_ap/";

    if (empty($doc_id)) {
        $insertRegSQL = "INSERT INTO document (doc_regis_approve, doc_sent_approve) VALUES (NULL, NULL)";
        if (mysqli_query($conn, $insertRegSQL)) {
            $doc_id = mysqli_insert_id($conn);
        } else {
            echo '<script>alert("เกิดข้อผิดพลาด"); window.history.back();</script>';
            exit;
        }

        // doc_id 
        $updateRegSQL = "UPDATE registration SET doc_id = '$doc_id' WHERE comp_id = '$comp_id'";
        mysqli_query($conn, $updateRegSQL);
    }

    $file_name = $doc_id . "_" . basename($_FILES["upload_file"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["upload_file"]["tmp_name"], $target_file)) {
        $sql = "UPDATE document SET $file_type = '$file_name' WHERE doc_id = '$doc_id'";
        mysqli_query($conn, $sql);

        // ตรวจสอบว่ามีไฟล์ครบสองไฟล์หรือยัง
        $checkSQL = "SELECT doc_regis_approve, doc_sent_approve FROM document WHERE doc_id = '$doc_id'";
        $result = mysqli_query($conn, $checkSQL);
        $row = mysqli_fetch_assoc($result);
        if ($row["doc_regis_approve"] && $row["doc_sent_approve"]) {
            $updateStatusSQL = "UPDATE registration SET reg_status = '06' WHERE doc_id = '$doc_id'";
            mysqli_query($conn, $updateStatusSQL);
        }

        echo '<script>alert("อัปโหลดไฟล์สำเร็จแล้ว"); window.location = "staff_regis_page.php";</script>';
    } else {
        echo '<script>alert("เกิดข้อผิดพลาดในการอัพโหลดไฟล์");</script>';
    }
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Upload</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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

    <style>
        .down{box-shadow:0px 0px 10px rgba(0, 0, 0, 0.1);
        width: 30%;
    margin-left: 35%;}
        .container{margin-top:45%;}

    </style>

     <div class="down">
    <div class="container">
            <h2 class="mb-4">อัปโหลดเอกสาร</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="comp_id" value="<?= htmlspecialchars($comp_id) ?>">
                <input type="hidden" name="doc_id" value="<?= htmlspecialchars($doc_id) ?>">
                <input type="hidden" name="file_type" value="<?= htmlspecialchars($file_type) ?>">

                <div class="mb-3">
                    <label class="form-label">เลือกไฟล์</label>
                    <input type="file" name="upload_file" class="form-control" accept=".pdf" required>
                </div>

                <button type="submit" class="btn btn-primary">อัปโหลด</button>
                <a href="staff_regis_page.php" class="btn btn-secondary">ยกเลิก</a>
            </form>
        </div>
        </div>   
    </body>

</html>