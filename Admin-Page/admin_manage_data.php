<?php 
session_start();
include '../config.php';

if (!isset($_SESSION['u_type'])) {
    header("Location: ..\index.php");
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้
if ($_SESSION['u_type'] != 'Admin') {
    header("Location: ..\unauthorized.php");
    exit();
}

// ดึงข้อมูลทั้งหมดจากฐานข้อมูล
$query = "
SELECT 
    users.u_id, users.username, users.u_type,
    COALESCE(student.std_email_1, staff.stf_email, professor.pf_email, company.comp_contact) AS email,
    COALESCE(student.std_fname, staff.stf_fname, professor.pf_fname, company.comp_name) AS first_name,
    COALESCE(student.std_lname, staff.stf_lname, professor.pf_lname, NULL) AS last_name,
    COALESCE(professor.pf_role, users.u_type) AS position
FROM users
LEFT JOIN student ON users.u_id = student.u_id
LEFT JOIN professor ON users.u_id = professor.u_id
LEFT JOIN company ON users.u_id = company.u_id
LEFT JOIN staff ON users.u_id = staff.u_id
WHERE users.u_type != 'Admin'
ORDER BY users.u_id ASC";


$result = mysqli_query($conn, $query);

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (isset($_SESSION['username'])) {
    $Name = "admin";
    $firstLetter = "a";
} else {
    $Name = "admin";
    $firstLetter = "a";
}


?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../Admin-Page/Admin-Style/manage.css">
    <script src="../script.js" defer></script>
</head>
<body>

    <div class="header">
        <div class="hamburger-menu">
            <div class="hamburger-icon" onclick="toggleMenu()">
                <img src="../Icon/i5.png" alt="Menu Icon">
            </div> 
            <div class="menu-sidebar" id="menuSidebar">
                <a href="admin_dashboard.php"><img src="../Icon/i1.png" alt="Home Icon"> หน้าหลัก</a>
                <a href="admin_profile.php"><img src="../Icon/i2.png" alt="Profile Icon"> ข้อมูลส่วนตัว</a>
                <a href="admin_manage_data.php"><img src="../Icon/i3.png" alt="Form Icon"> กรอกใบสมัคร</a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
        <div class="user"> <?= $Name ?>  </div>
        <div class="profile-circle"><?= $firstLetter ?></div>
        <div class="dropdown">
        
            <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
            <div class="dropdown-content">
                <a href="edit_admin_profile.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
            </div>
        </div>
        </div>
    </div>

    <h2 class = "as">จัดการข้อมูลผู้ใช้</h2>
    <nav class="breadcrumb">
    <a href="admin_dashboard.php" class="breadcrumb-item">หน้าหลัก</a>
    <span class="breadcrumb-item active">จัดการข้อมูลผู้ใช้</span>
    </nav>

    <div class="search-container">
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="" oninput="searchTable()">
        <button>🔍</button>
    </div>
    <a href="add_user.php"> <button class="add-user-btn">➕ เพิ่มผู้ใช้ใหม่</button></a>
    </div>





<div class="table-container">
    <table class="advisor-table">
    <thead>
        <tr>
            <th>ไอดี</th>
            <th>อีเมลล์</th>
            <th>ชื่อ</th>
            <th>นามสกุล</th>
            <th>ตำแหน่ง</th>
            <th>แก้ไข</th>
            <th>ลบ</th>
        </tr>
    </thead>

    <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?= $row['u_id'] ?></td>
            <td><?= $row['email'] ?></td>
            <td><?= $row['first_name'] ?></td>
            <td><?= $row['last_name'] ?? '-' ?></td>
            <td><?= $row['position'] ?></td>
            <td>
                <a href="edit_user.php?u_id=<?= $row['u_id'] ?>">แก้ไข</a>
            </td>
            <td>
                <a href="#" onclick="confirmDelete(<?= $row['u_id'] ?>)">🗑️</a>
            </td>
        </tr>
    <?php } ?>
    </tbody>
    </table>
</div>    

<script>
function confirmDelete(u_id) {
    if (confirm("คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้คนนี้?")) {
        window.location.href = "delete_user.php?u_id=" + u_id;
    }
}
</script>

<script>
function searchTable() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("searchInput");
    filter = input.value.toLowerCase();
    table = document.querySelector(".advisor-table");
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
