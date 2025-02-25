<?php 
session_start();
include '../config.php';

// ตรวจสอบว่าเป็นการส่งข้อมูลจากฟอร์ม (POST) หรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ตรวจสอบว่า $_POST['student_ids'] มีข้อมูลหรือไม่
    if (isset($_POST['student_ids']) && !empty($_POST['student_ids'])) {
        var_dump($_POST['student_ids']); // แสดงข้อมูลที่ส่งมาจากฟอร์ม
        // ทำงานอื่น ๆ ต่อจากนี้ เช่น การบันทึกข้อมูลหรือการอัปเดตข้อมูล
    } else {
        echo "❌ Error: ไม่มีนักศึกษาที่เลือก"; // ถ้าไม่มีการเลือกนักศึกษา
    }
}
// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['u_type']) || $_SESSION['u_type'] != 'Professor') {
    header("Location: ../unauthorized.php");
    exit();
}

// ดึงข้อมูลของผู้ใช้
$u_id = $_SESSION['u_id'];
$stmt = $conn->prepare("SELECT pf_fname, pf_lname FROM professor WHERE u_id = ?");
$stmt->bind_param("i", $u_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ตรวจสอบว่ามีข้อมูลก่อนใช้งาน
$Name = isset($user['pf_fname']) ? $user['pf_fname'] . ' ' . $user['pf_lname'] : 'ไม่พบข้อมูล';
$firstLetter = isset($user['pf_fname']) ? mb_substr($user['pf_fname'], 0, 1, "UTF-8") : '-';

// ดึงข้อมูลอาจารย์ที่ปรึกษาพร้อมจำนวนนักศึกษา
$query = "
    SELECT p.pf_id, p.pf_fname, p.pf_lname, COUNT(s.std_id) AS student_count 
    FROM professor p
    LEFT JOIN student s ON p.pf_id = s.pf_id
    GROUP BY p.pf_id, p.pf_fname, p.pf_lname
    ORDER BY p.pf_fname ASC;
";
$result = mysqli_query($conn, $query);

// สร้างข้อมูลตาราง
$advisorData = "";
while ($row = mysqli_fetch_assoc($result)) {
    //$pf_id = isset($row['pf_id']);
    $advisorData .= '<tr>
        <td>' . htmlspecialchars($row['pf_fname'] . ' ' . $row['pf_lname']) . '</td>
        <td>' . htmlspecialchars($row['student_count']) . '</td>
        <td>
            <a href="coordinator_assign_advisorS.php?id=' . $row['pf_id'] . '">
                <button class="assign-btn">
                    <img src="../Icon/co4.png" alt="Assign Icon" class="icon-img" />
                </button>
            </a>
        </td>
    </tr>';
}
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style/style-coordinator.css">
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
                <a href="#"><img src="../Icon/i2.png" alt="Profile Icon"> ข้อมูลส่วนตัว</a>
                <a href="#"><img src="../Icon/co1.png" alt="student Icon"> ข้อมูลนักศึกษา</a>
                <a href="coordinator_regis.php"><img src="../Icon/co2.png" alt="Profile Icon"> ใบสมัครสหกิจ</a>
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
</div>

<div class="container mt-4">
    <div class="M" style="margin-left: 20%;">
            <h2>กำหนดอาจารย์ที่ปรึกษา</h2>
            <nav aria-label="breadcrumb">
                <div class="btn-group btn-group-sm" role="group" aria-label="page">
                    <a class="btn btn-outline-secondary" href="coordinator_dashboard.php">Home</a>
                </div>
                <div class="btn-group btn-group-sm" role="group" aria-label="page">
                    <a class="btn btn-warning" href="#">กำหนดอาจารย์ที่ปรึกษา</a>
                </div>
            </nav>
    </div>

    <div class="table-container">
        <table class="advisor-table">
            <thead>
                <tr>
                    <th>ชื่อ-สกุล</th>
                    <th>จำนวนนักศึกษาในที่ปรึกษา</th>
                    <th>กำหนดนักศึกษา</th>
                </tr>
            </thead>
            <tbody>
                <?= $advisorData ?>
            </tbody>
        </table>
    </div>
    
</div>

    
</body>
</html>