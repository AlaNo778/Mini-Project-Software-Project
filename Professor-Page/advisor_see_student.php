<?php
session_start();
include '../config.php';
if (!isset($_SESSION['u_type'])) {
    header("Location: ..\index.php");
    exit();
}
// ตรวจสอบสิทธิ์ผู้ใช้
if ($_SESSION['u_type'] != 'Professor') {
    header("Location: ..\unauthorized.php");
    exit();
}

$u_id = $_SESSION['u_id']; // รับค่าจาก session ที่เก็บ user_id
$query = "SELECT pf_fname, pf_lname FROM professor WHERE u_id = '$u_id'";

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
    <title>Student Table</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style/style-advisor.css">
    <!-- เชื่อมโยงไฟล์ CSS ที่แยกออกมา -->
    <link rel="stylesheet" href="../style/style-table.css">
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
                <a href="advisor_dashboard.php"><img src="../Icon/i1.png" alt="Home Icon"> หน้าหลัก</a>
                <a href="#"><img src="../Icon/i2.png" alt="Profile Icon"> ข้อมูลส่วนตัว</a>
                <a href="#"><img src="../Icon/i3.png" alt="student Icon"> ข้อมูลนักศึกษา</a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
            <div class="user"> <?= $Name ?> </div>
            <div class="profile-circle"><?= $firstLetter ?></div>
            <div class="dropdown">
                <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-content">
                    <a href="advisor_manage_user.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                    <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="M">
            <h2>ข้อมูลนักศึกษา</h2>
            <nav aria-label="breadcrumb">
                <div class="btn-group btn-group-sm" role="group" aria-label="page">
                    <a class="btn btn-outline-secondary" href="advisor_dashboard.php">หน้าหลัก</a>
                </div>
                <div class="btn-group btn-group-sm" role="group" aria-label="page">
                    <button class="btn btn-warning">รายชื่อนักศึกษา</button>
                </div>
            </nav>
        </div>
        <table class="table table-hover mt-4">
            <thead>
                <tr align="center">
                    <th scope="col">รหัสนักศึกษา</th>
                    <th scope="col">ชื่อ - สกุล</th>
                    <th scope="col">ตำแหน่ง</th>
                    <th scope="col">ข้อมูลนักศึกษา</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "
                    SELECT s.*, u.username, u.u_type, p.pf_id, p.pf_role, r.*
                    FROM student AS s, users AS u, professor AS p, registration AS r
                    WHERE u.u_id = s.u_id AND p.pf_id = s.pf_id AND s.std_id = r.std_id AND p.u_id = '$u_id'
                ";
                $results = mysqli_query($conn, $sql);

                while ($row = mysqli_fetch_array($results)) {
                ?>
                    <tr>
                        <td><?= $row["username"] ?></td>
                        <td><?= $row["std_fname"] ?> <?= $row["std_lname"] ?></td>
                        <td><?= $row["reg_job"] ?></td>
                        <div class="I">
                            <td>
                                <a href="advisor_see_studentS.php?std_id=<?= $row['std_id'] ?>">
                                    <button class="assign-btn">
                                        <img src="../Icon/co5.png" alt="Assign Icon" class="icon-img" />
                                    </button>
                                </a>
                            </td>
                        </div>
                    </tr>

                <?php
                }
                mysqli_close($conn); // close DB connection
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>