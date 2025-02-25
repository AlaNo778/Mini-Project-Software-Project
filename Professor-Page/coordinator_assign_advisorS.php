<?php 
session_start();
include '..\config.php';

function getStudentData($conn) {
    $query = "
        SELECT 
            u.username AS student_id,
            s.std_id,
            s.std_fname, 
            s.std_lname, 
            r.reg_job
        FROM registration r 
        JOIN student s ON r.std_id = s.std_id
        JOIN users u ON s.u_id = u.u_id
        WHERE s.pf_id IS NULL";  //  เงื่อนไขนี้จะดึงเฉพาะนักศึกษาที่ยังไม่มีที่ปรึกษา

    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Query Failed: " . mysqli_error($conn)); 
    }

    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    if (empty($rows)) {
        return "<tr><td colspan='4' style='text-align:center;'>ไม่มีข้อมูลนักศึกษา</td></tr>";
    }

    $tableData = ""; // กำหนดตัวแปรเก็บข้อมูล HTML

    // วนลูปผ่านข้อมูลนักศึกษาใน $rows
    foreach ($rows as $row) {
        // สร้าง HTML สำหรับแต่ละแถว
        $tableData .= "<tr>
            <td style='text-align: center;'>
                <input type='checkbox' name='student_ids[]' value='" . $row['std_id'] . "'>
            </td>
            <td>" . htmlspecialchars($row['student_id']) . "</td>
            <td>" . htmlspecialchars($row['std_fname'] . " " . $row['std_lname']) . "</td>
            <td>" . htmlspecialchars($row['reg_job']) . "</td>
        </tr>";
    }

    // คืนค่าข้อมูล HTML ที่สร้างขึ้น
    return $tableData;

}






if (!isset($_SESSION['u_type'])) {
    header("Location: ..\index.php");
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้
if ($_SESSION['u_type'] != 'Professor') {
    header("Location: ..\unauthorized.php");
    exit();
}

// ดึงข้อมูลของนักศึกษาจากฐานข้อมูล
$u_id = $_SESSION['u_id']; // รับค่าจาก session ที่เก็บ user_id
$query = "SELECT pf_fname, pf_lname FROM professor WHERE u_id = '$u_id'"; // คำสั่ง SQL ที่ใช้ค้นหาข้อมูลนักศึกษา

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (mysqli_num_rows($result) > 0) {
    // ดึงข้อมูลมาเก็บในตัวแปร
    $row = mysqli_fetch_assoc($result);
    $Name = $row['pf_fname'] . ' ' . $row['pf_lname']; // รวมชื่อและนามสกุล

    $firstLetter = mb_substr($row['pf_fname'], 0, 1, "UTF-8");
    
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
                <a href="#"><img src="../Icon/i1.png" alt="Home Icon"> หน้าหลัก</a>
                <a href="#"><img src="../Icon/i2.png" alt="Profile Icon"> ข้อมูลส่วนตัว</a>
                <a href="#"><img src="../Icon/co1.png" alt="student Icon"> ข้อมูลนักศึกษา</a>
                <a href="#"><img src="../Icon/co2.png" alt="Profile Icon"> ใบสมัครสหกิจ</a>
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
                <h2>กำหนดอาจารย์ที่ปรึกษา</h2>
                <nav aria-label="breadcrumb">
                    <div class="btn-group btn-group-sm" role="group" aria-label="page">
                        <a class="btn btn-outline-secondary" href="coordinator_dashboard.php">Home</a>
                    </div>
                    <div class="btn-group btn-group-sm" role="group" aria-label="page">
                        <a class="btn btn-outline-secondary" href="coordinator_assign_advisor.php">กำหนดอาจารย์ที่ปรึกษา</a>
                    </div>
                    <div class="btn-group btn-group-sm" role="group" aria-label="page">
                        <a class="btn btn-warning" href="#">กำหนดนักศึกษา</a>
                    </div>
                </nav>
        </div>





        <form action="coordinator_save_students.php" method="post">
            <input type="hidden" name="pf_id" value="<?= $_GET['id'] ?>">  <!-- ส่ง pf_id ไปด้วย -->

            <table class="student-table">
                <thead>
                    <tr>
                        <th>เลือก</th>
                        <th>รหัส</th>
                        <th>ชื่อ-สกุล</th>
                        <th>ตำแหน่ง</th>
                    </tr>
                </thead>
                <tbody>
                    <?= getStudentData($conn) ?>
                </tbody>
            </table>

            <div align ="center">
            <div class="button-container">
                <button type="button" class="cancel-btn" onclick="window.location.href='coordinator_assign_advisor.php'">❌ ยกเลิก</button>
                <button type="submit" class="confirm-btn">✔ ยืนยัน</button>
            </div>
            </div>
        </form>
        <script>
            // เมื่อฟอร์มถูกส่ง
                    document.querySelector('form').onsubmit = function(event) {
                // หาค่า checkbox ที่ถูกเลือก
                var checkboxes = document.querySelectorAll('input[name="student_ids[]"]:checked');
                
                // ถ้าไม่มี checkbox ถูกเลือก
                if (checkboxes.length === 0) {
                    alert("กรุณาเลือกนักศึกษาก่อนยืนยัน");
                    event.preventDefault();  // หยุดการส่งฟอร์ม
                }
            };
        </script>
</div>


</body>
</html>