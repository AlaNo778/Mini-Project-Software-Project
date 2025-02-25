<?php
session_start();
include 'config.php';  // เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    
    // ป้องกันการ SQL Injection
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // เตรียมคำสั่ง SQL เพื่อตรวจสอบชื่อผู้ใช้
    $sql = "SELECT users.*, professor.pf_role 
            FROM users 
            LEFT JOIN professor ON users.u_id = professor.u_id 
            WHERE users.username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt === false) {
        die("Query Preparation Failed: " . mysqli_error($conn)); 
    }

    // ผูกตัวแปร username เพื่อใช้ใน SQL Query
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    
    // ดึงผลลัพธ์จากการ query
    $result = mysqli_stmt_get_result($stmt);

    // ตรวจสอบว่ามีผู้ใช้ในฐานข้อมูลหรือไม่
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result); 
        
        // ตรวจสอบรหัสผ่าน
        if ($password == $row['password'])  {
            // ตั้งค่าตัวแปร session สำหรับผู้ใช้ที่เข้าสู่ระบบ
            $_SESSION['u_id'] = $username;
            $_SESSION['u_type'] = $row['u_type']; 
            $_SESSION['u_id'] = $row['u_id']; 

            // เปลี่ยนเส้นทางไปยังหน้าที่แตกต่างกันตามประเภทผู้ใช้
             // ตรวจสอบประเภทผู้ใช้และเปลี่ยนเส้นทาง
             if ($row['u_type'] === 'Professor') {
                if ($row['pf_role'] === 'Advisor') {
                    header("Location: Professor-Page/advisor_dashboard.php");
                } elseif ($row['pf_role'] === 'Coordinator') {
                    header("Location: Professor-Page/coordinator_dashboard.php");
                } else {
                    header("Location: Professor-Page/default_dashboard.php");
                }
            } else {
                switch ($row['u_type']) {
                    case 'Student':
                        header("Location: Student-Page/student_dashboard.php");
                        break;
                    case 'Staff':
                        header("Location: Staff-Page/staff_dashboard.php");
                        break;
                    case 'Company':
                        header("Location: company_dashboard.php");
                        break;
                    case 'Admin':
                        header("Location: admin_dashboard.php");
                        break;
                    default:
                        header("Location: welcome.php");
                        break;
                }

            switch ($row['u_type']) {
                case 'Student':
                    header("Location: Student-Page\student_dashboard.php");
                    break;
                case 'Staff':
                    header("Location: Staff-Page\staff_dashboard.php");
                    break;
                case 'Professor':
                    header("Location: Professor-Page\professor_dashboard.php");
                    break;
                case 'Company':
                    header("Location: Company-Page\company_dashboard.php");
                    break;
                case 'Admin':
                    header("Location: Admin-Page\admin_dashboard.php");
                    break;
                default:
                    header("Location: welcome.php");
                    break;
            }
                
            }
            exit();
        } else {
            // ถ้ารหัสผ่านไม่ถูกต้อง
            echo "<script>alert('รหัสผ่านไม่ถูกต้อง!'); window.location.href='index.php';</script>";
        }
    } else {
        // ถ้าไม่พบชื่อผู้ใช้ในฐานข้อมูล
        echo "<script>alert('ไม่พบชื่อผู้ใช้!'); window.location.href='index.php';</script>";
    }

    // ปิดการใช้งาน prepared statement
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Login</title>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <form action="" method="POST">
                <div class="field input">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div class="field input">
                    <input type="submit" class="btn" name="submit" value="Login">
                </div>

                <div class="logo">
                    <img src="psu_logo.png" alt="PSU Logo">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
