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
$query = "SELECT pf_id,pf_fname, pf_lname FROM professor WHERE u_id = '$u_id'"; // คำสั่ง SQL ที่ใช้ค้นหาข้อมูลอาจารย์

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (mysqli_num_rows($result) > 0) {
    // ดึงข้อมูลมาเก็บในตัวแปร
    $row = mysqli_fetch_assoc($result);
    $Name = $row['pf_fname'] . ' ' . $row['pf_lname']; // รวมชื่อและนามสกุล
    $firstLetter = mb_substr($row['pf_fname'], 0, 1, "UTF-8");
    $pf_id = $row['pf_id'];
    
} 

// ฟังก์ชันในการดึงข้อมูลนักศึกษาและแสดงในตาราง
function displayStudentData($conn,$pf_id) {
    // ดึงข้อมูลจากฐานข้อมูลโดยเชื่อมตาราง users, student, และ registration ผ่าน u_id
    $query = "SELECT u.username, s.std_id, s.std_fname, s.std_lname, r.reg_job, r.reg_status 
              FROM users u
              JOIN student s ON u.u_id = s.u_id
              JOIN registration r ON s.std_id = r.std_id 
              JOIN professor p ON r.pf_id = p.pf_id
              WHERE u.u_type = 'Student' AND r.pf_id = '$pf_id'";

    $result = mysqli_query($conn, $query);

    // ตรวจสอบผลลัพธ์
    if (mysqli_num_rows($result) > 0) {
        // เริ่มต้นแสดงตาราง
        echo '<div class="t-container">';
        echo '<table class="s-table">';
        echo '<thead><tr><th>รหัส</th><th>ชื่อ-สกุล</th><th>ตำแหน่ง</th><th>ข้อมูลนักศึกษา</th><th>สถานะ</th></tr></thead>';
        echo '<tbody>';

        // ดึงข้อมูลแต่ละแถวและแสดงผล
        while ($row = mysqli_fetch_assoc($result)) {
            // ดึงค่า std_id จากฐานข้อมูล
            $std_id = $row['std_id'];  // ใช้ std_id จากฐานข้อมูล

            echo '<tr>';
            echo '<td>' . $row['username'] . '</td>';
            echo '<td>' . $row['std_fname'] . ' ' . $row['std_lname'] . '</td>';
            echo '<td>' . $row['reg_job'] . '</td>';
            // เปลี่ยนจากลิงก์ "ดูข้อมูล" เป็นปุ่มกำหนดอาจารย์ที่ปรึกษา
            echo '<td>
                    <a href="coordinator_regis_app.php?id=' . $std_id . '">
                        <button class="assign-btn">
                            <img src="../Icon/co5.png" alt="Assign Icon" class="icon-img" />
                        </button>
                    </a>
                </td>';
                echo '<td>';
                if ($row['reg_status'] == '02') {
                    echo '<span class="approved">อนุมัติแล้ว</span>';
                } elseif ($row['reg_status'] == '2.1') {
                    echo '<span class="reject">ปฏิเสธ</span>';
                } else {
                    echo '<span class="waiting">รอตรวจสอบ</span>';
                }
                echo '</td>';
                
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo 'ไม่พบข้อมูลนักศึกษา';
    }
}


?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style/style-coordinator.css"> <!-- เรียกใช้ไฟล์ CSS แยก -->
    <script src="../script.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

    <div class="header">
        <div class="hamburger-menu">
            <div class="hamburger-icon" onclick="toggleMenu()">
                <img src="../Icon/i5.png" alt="Menu Icon">
            </div> 
            <div class="menu-sidebar" id="menuSidebar">
                <a href="coordinator_dashboard.php"><img src="../Icon/i1.png" alt="Home Icon"> หน้าหลัก</a>
                <a href="coordinator_profile.php"><img src="../Icon/i2.png" alt="Profile Icon"> ข้อมูลส่วนตัว</a>
                <a href="coordinator_see_student.php"><img src="../Icon/co1.png" alt="student Icon"> ข้อมูลนักศึกษา</a>
                <a href="coordinator_regis.php"><img src="../Icon/co2.png" alt="Profile Icon"> ใบสมัครสหกิจ</a>
                <a href="coordinator_assign_advisor.php"><img src="../Icon/co3.png" alt="student Icon"> กำหนดอาจารย์ที่ปรึกษา</a>
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
    <div class="container mt-4">
        <div class="M" style="margin-left: 20%;">
                <h2>ใบสมัครสหกิจ</h2>
                <nav aria-label="breadcrumb">
                    <div class="btn-group btn-group-sm" role="group" aria-label="page">
                        <a class="btn btn-outline-secondary" href="coordinator_dashboard.php">Home</a>
                    </div>
                    <div class="btn-group btn-group-sm" role="group" aria-label="page">
                        <a class="btn btn-warning" href="coordinator_see_student.php">ใบสมัครสหกิจ</a>
                    </div>
                </nav>
        </div>
    <!-- เรียกฟังก์ชันแสดงข้อมูลนักศึกษา -->
    <?php displayStudentData($conn,$pf_id); ?>
</div>
</body>
</html>
