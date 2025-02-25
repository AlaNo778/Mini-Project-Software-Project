<?php 
session_start();
include '../config.php'; // ตรวจสอบให้แน่ใจว่าไฟล์นี้มีการสร้างการเชื่อมต่อฐานข้อมูลและกำหนดตัวแปร $conn

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
$u_id = $_SESSION['u_id']; // รับค่าจาก session ที่เก็บ user_id
$query = "SELECT comp_name FROM Company WHERE u_id = '$u_id'";  // คำสั่ง SQL ที่ใช้ค้นหาข้อมูลบริษัท

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $firstLetter = mb_substr($row['comp_name'], 0, 1, "UTF-8");
    $Name = $row['comp_name'];
} else {
    $firstLetter = '';
    $Name = '';
}
?>

<?php 
// ฟังก์ชันในการดึงข้อมูลนักศึกษาและแสดงในตาราง
function displayStudentData($conn, $comp_id) {
    $query = "SELECT u.username, s.std_id, s.std_fname, s.std_lname, r.reg_job, r.reg_status 
              FROM users u
              JOIN student s ON u.u_id = s.u_id 
              JOIN registration r ON s.std_id = r.std_id 
              WHERE u.u_type = 'Student' AND r.reg_status in ('03','04','4.1','06') AND r.comp_id = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $comp_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo '<div class="t-container">';
        echo '<table class="s-table">';
        echo '<thead><tr><th>รหัส</th><th>ชื่อ-สกุล</th><th>ตำแหน่ง</th><th>ข้อมูลนักศึกษา</th><th>สถานะ</th></tr></thead>';
        echo '<tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            $std_id = $row['std_id']; 

            echo '<tr>';
            echo '<td>' . $row['username'] . '</td>';
            echo '<td>' . $row['std_fname'] . ' ' . $row['std_lname'] . '</td>';
            echo '<td>' . $row['reg_job'] . '</td>';
            echo '<td>
                    <a href="company_Intern_info.php?id=' . $std_id . '">
                        <button class="assign-btn">
                            <img src="../Icon/co5.png" alt="Assign Icon" class="icon-img" />
                        </button>
                    </a>
                  </td>';
            echo '<td>';
            if ($row['reg_status'] == '04') {
                echo '<span class="approved">อนุมัติแล้ว</span>';
            } elseif ($row['reg_status'] == '4.1') {
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

// ดึง comp_id ของบริษัทที่ล็อกอินอยู่
$comp_id_query = "SELECT comp_id FROM Company WHERE u_id = ?";
$stmt = mysqli_prepare($conn, $comp_id_query);
mysqli_stmt_bind_param($stmt, "i", $u_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$comp_id = mysqli_fetch_assoc($result)['comp_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>company_Intern</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style/style-coordinator.css"> <!-- เรียกใช้ไฟล์ CSS แยก -->
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
            <div class="user"> <?= $Name ?> </div>
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
    <h2 class="as">ใบสมัครสหกิจ</h2>
    <nav class="breadcrumb">
        <a href="company_dashboard.php" class="breadcrumb-item">Home</a>
        <span class="breadcrumb-item active">ใบสมัครสหกิจ</span>
    </nav>

    <!-- แสดงข้อมูลนักศึกษาที่สมัครใน comp_id ของบริษัท -->
    <?php 
        if ($comp_id) {
            displayStudentData($conn, $comp_id);
        } else {
            echo "ไม่พบข้อมูลบริษัทของคุณ";
        }
    ?>

</body>
</html>
