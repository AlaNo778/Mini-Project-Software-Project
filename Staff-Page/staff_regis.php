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

// ดึงข้อมูลของอาจารย์จากฐานข้อมูล
$u_id = $_SESSION['u_id']; // รับค่าจาก session ที่เก็บ user_id
$query = "SELECT stf_id,stf_fname, stf_lname FROM staff WHERE u_id = '$u_id'"; // คำสั่ง SQL ที่ใช้ค้นหาข้อมูลอาจารย์

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (mysqli_num_rows($result) > 0) {
    // ดึงข้อมูลมาเก็บในตัวแปร
    $row = mysqli_fetch_assoc($result);
    $Name = $row['stf_fname'] . ' ' . $row['stf_lname']; // รวมชื่อและนามสกุล
    $firstLetter = mb_substr($row['stf_fname'], 0, 1, "UTF-8");
    $pf_id = $row['stf_id'];
    
} 

// ฟังก์ชันในการดึงข้อมูลนักศึกษาและแสดงในตาราง
function displayStudentData($conn, $stf_id) {
    // ดึงข้อมูลจากฐานข้อมูล
    $query = "SELECT u.username, s.std_id, s.std_fname, s.std_lname, r.reg_job, r.reg_status 
              FROM users u
              JOIN student s ON u.u_id = s.u_id
              JOIN registration r ON s.std_id = r.std_id 
              
              WHERE u.u_type = 'Student'";

    $result = mysqli_query($conn, $query);

    // ตรวจสอบผลลัพธ์
    if (mysqli_num_rows($result) > 0) {
        // เริ่มต้นแสดงตาราง
        echo '<div class="t-container">';
        echo '<table class="s-table" id="studentTable">';
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
                    <a href="staff_regis_app.php?id=' . $std_id . '">
                        <button class="assign-btn">
                            <img src="../Icon/co5.png" alt="Assign Icon" class="icon-img" />
                        </button>
                    </a>
                </td>';
                echo '<td>';
                if ($row['reg_status'] == '01') {
                    echo '<span class="waiting">รออาจารย์ตอบรับ</span>';
                } elseif ($row['reg_status'] == '02') {
                    echo '<span class="waiting">รอเจ้าหน้าที่ตอบรับ</span>';
                } elseif ($row['reg_status'] == '03') {
                    echo '<span class="waiting">รอสถานประกอบการตอบรับ</span>';
                } elseif ($row['reg_status'] == '04') {
                    echo '<span class="waiting">จัดทำหนังสือส่งตัว</span>';
                } elseif ($row['reg_status'] == '05') {
                    echo '<span class="waiting">ทำเอกสารเสร็จสิ้น</span>';
                } elseif (in_array($row['reg_status'], ['3.1', '2.1', '4.1'])) {
                    echo '<span class="reject">ปฏิเสธ</span>';
                } else {
                    echo '<span class="approved">เสร็จสิ้น</span>';
                }
                
                
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<div class="no-data">ไม่พบข้อมูลนักศึกษา</div>';
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
    <style>
        /* เพิ่ม CSS สำหรับปุ่มค้นหาในแบบเดียวกับหน้า admin */
        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            margin-top: -61px;
            margin-left: 70%;
        }
        
        .search-box {
            display: flex;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        
        .search-box input {
            border: none;
            padding: 10px;
            flex: 1;
            outline: none;
            font-size: 14px;
        }
        
        .search-box button {
            background: #f8f8f8;
            border: none;
            border-left: 1px solid #ddd;
            padding: 10px 15px;
            cursor: pointer;
        }
        
        .search-box button:hover {
            background: #f0f0f0;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 16px;
        }
    </style>
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
        <div class="user"> <?= $Name ?>  </div>
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
    <h2 class="as">ใบสมัครสหกิจ</h2>
        <nav class="breadcrumb">
            <a href="staff_dashboard.php" class="breadcrumb-item">Home</a>
            <span class="breadcrumb-item active">ใบสมัครสหกิจ</span>
        </nav>

    <!-- เพิ่มฟอร์มค้นหาในรูปแบบเดียวกับหน้า admin -->
    <div class="search-container">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="ค้นหา..." oninput="searchTable()">
            <button>🔍</button>
        </div>
    </div>
       
    <!-- เรียกฟังก์ชันแสดงข้อมูลนักศึกษา -->
    <?php displayStudentData($conn, $pf_id); ?>

    <script>
    function searchTable() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toLowerCase();
        table = document.getElementById("studentTable");
        tr = table.getElementsByTagName("tr");

        for (i = 1; i < tr.length; i++) { 
            tr[i].style.display = "none"; 
            td = tr[i].getElementsByTagName("td");
            for (var j = 0; j < td.length; j++) { 
                if (td[j]) {
                    txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        break;
                    }
                }
            }
        }
    }
    </script>

</body>
</html>