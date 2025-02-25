<?php 
session_start();
include '../config.php';

// Security checks
if (!isset($_SESSION['u_type'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['u_type'] != 'Staff') {
    header("Location: ../unauthorized.php");
    exit();
}

$u_id = $_SESSION['u_id'];

$query_staff = "SELECT stf_fname, stf_lname FROM staff WHERE u_id = ?";
$stmt_staff = mysqli_prepare($conn, $query_staff);
mysqli_stmt_bind_param($stmt_staff, "s", $u_id);
mysqli_stmt_execute($stmt_staff);
$result_staff = mysqli_stmt_get_result($stmt_staff);

if ($row_staff = mysqli_fetch_assoc($result_staff)) {
    $Name = $row_staff['stf_fname'] . ' ' . $row_staff['stf_lname'];
    $firstLetter = mb_substr($row_staff['stf_fname'], 0, 1, "UTF-8");
} else {
    $Name = "ไม่พบข้อมูล";
    $firstLetter = "?";
}

$query_company = "SELECT comp_id, comp_name FROM company";
$result_company = mysqli_query($conn, $query_company);
if (!$result_company) {
    die("Query failed: " . mysqli_error($conn));
}


?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../Staff-Page/Staff_Style/manage.css">
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
            <div class="user"><?= htmlspecialchars($Name) ?></div>
            <div class="profile-circle"><?= htmlspecialchars($firstLetter) ?></div>
            <div class="dropdown">
                <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-content">
                    <a href="edit_staff_profile.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                    <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </div>
<div1>
    <h2 class="as">จัดการข้อมูล</h2>
    <nav class="breadcrumb">
        <a href="staff_dashboard.php" class="breadcrumb-item">หน้าหลัก</a>
        <span class="breadcrumb-item active">จัดการข้อมูล</span>
    </nav>
    
    <div class="add-user-container">
    <a href="add_company.php">
        <button class="add-user-btn">➕ เพิ่มผู้ใช้ใหม่</button>
    </a>
</div>

    <style>
   .add-user-container {
    text-align: left; /* จัดตำแหน่งเนื้อหาใน div ไปทางซ้าย */
    margin-left: 16%;
}


    </style>

</div>
</div1>
    <div class="table-container">
        <table class="advisor-table" >
            <thead>
                <tr>
                    <th>ชื่อสถานประกอบการ</th>
                    <th>แก้ไข</th>         
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_company)) { ?>
                <tr>
                        <td><?= htmlspecialchars($row['comp_name']) ?></td>
                    <td>
                        <a href="edit_user.php?comp_id=<?= htmlspecialchars($row['comp_id']) ?>">แก้ไข</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>

        </table>
    </div>    
</body>
</html>