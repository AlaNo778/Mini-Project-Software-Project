<?php
session_start();
include '..\config.php';

if (!isset($_SESSION['u_id'])) {
    header("Location: ..\index.php");
    exit();
}

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
$query = "SELECT std_fname, std_lname FROM student WHERE u_id = '$u_id'"; // คำสั่ง SQL ที่ใช้ค้นหาข้อมูลนักศึกษา

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (mysqli_num_rows($result) > 0) {
    // ดึงข้อมูลมาเก็บในตัวแปร
    $row = mysqli_fetch_assoc($result);
    $Name = $row['std_fname'] . ' ' . $row['std_lname']; // รวมชื่อและนามสกุล
    $firstLetter = mb_substr($row['std_fname'], 0, 1, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $current_password = $_POST['password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "<script>alert('การยืนยันรหัสผ่านไม่ถูกต้อง!');</script>";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($db_password);
            $stmt->fetch();

            if ($current_password == $db_password) { // ตรวจสอบรหัสผ่านเดิม
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                $update_stmt->bind_param("ss", $new_password, $username);

                if ($update_stmt->execute()) {
                    echo "<script>alert('เปลี่ยนรหัสผ่านสำเร็จ!'); window.location='student_dashboard.php';</script>";
                } else {
                    echo "<script>alert('เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน!');</script>";
                }

                $update_stmt->close();
            }

            $stmt->close();
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style/style-professor.css">
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
                <a href="student_dashboard.php"><img src="../Icon/i1.png" alt="Home Icon"> หน้าหลัก</a>
                <a href="profile_student.php"><img src="../Icon/i2.png" alt="Profile Icon"> ข้อมูลส่วนตัว</a>
                <a href="application_form.php"><img src="../Icon/i3.png" alt="Form Icon"> กรอกใบสมัคร</a>
                <a href="status_student.php"><img src="../Icon/i4.png" alt="Status Icon"> สถานะ</a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
            <div class="user"> <?= $Name ?> </div>
            <div class="profile-circle"><?= $firstLetter ?></div>
            <div class="dropdown">

                <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-content">
                    <a href="setting_student.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                    <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <br>
        <h2>จัดการบัญชี</h2>
        <form method="POST" onsubmit="return validatePassword()">
            <?php
            $query = "SELECT username,password FROM users WHERE u_id = '$u_id'";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_array($result);
            ?>
            <label>Username</label>
            <input type="text" name="username" value=<?= $row['username'] ?> readonly><br>

            <label>Password</label>
            <input type="password" name="password" value=<?= $row['password'] ?> readonly><br>

            <label>New Password</label>
            <input type="password" id="new_password" name="new_password" required><br>

            <label>Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br>

            <button type="button" class="btn btn-danger" onclick="window.location.href='student_dashboard.php'">
                <i class=" fas fa-times"></i> ยกเลิก
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check"></i> บันทึก
            </button>

        </form>
    </div>
</body>

</html>

<script>
    function validatePassword() {
        let newPassword = document.getElementById("new_password").value;
        let confirmPassword = document.getElementById("confirm_password").value;
        let errors = {
            confirm: document.getElementById("confirm_password_error")
        };

        errors.confirm.style.display = newPassword && confirmPassword && newPassword !== confirmPassword ? "block" : "none";

        return newPassword === confirmPassword;
    }

    ["new_password", "confirm_password"].forEach(id =>
        document.getElementById(id).addEventListener("input", validatePassword)
    );
</script>