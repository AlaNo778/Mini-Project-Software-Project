<?php
session_start();
include '..\config.php';

if (!isset($_SESSION['u_type'])) {
    header("Location: ..\index.php");
    exit();
}

if ($_SESSION['u_type'] != 'Staff') {
    header("Location: ..\unauthorized.php");
    exit();
}

// Get staff ID from session
$staff_uid = $_SESSION['u_id'];

// Get staff information for header
$query = "SELECT stf_fname, stf_lname 
          FROM staff 
          WHERE u_id = ?";

if ($stmt = mysqli_prepare($conn, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $staff_uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    die("Query failed: " . mysqli_error($conn));
}

// Get staff name for header display
$Name = $row['stf_fname'] . ' ' . $row['stf_lname'];
$firstLetter = mb_substr($row['stf_fname'], 0, 1, "UTF-8");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    // Collect and escape all company fields
    $name = mysqli_real_escape_string($conn, $_POST['comp_name']);
    $img = mysqli_real_escape_string($conn, $_POST['comp_img']);
    $contact = mysqli_real_escape_string($conn, $_POST['comp_contact']);
    $department = mysqli_real_escape_string($conn, $_POST['comp_department']);
    $province = mysqli_real_escape_string($conn, $_POST['comp_province']);
    $district = mysqli_real_escape_string($conn, $_POST['comp_district']);
    $sub = mysqli_real_escape_string($conn, $_POST['comp_sub_district']);
    $add = mysqli_real_escape_string($conn, $_POST['comp_num_add']);
    $mu = mysqli_real_escape_string($conn, $_POST['comp_mu']);
    $road = mysqli_real_escape_string($conn, $_POST['comp_road']);
    $alley = mysqli_real_escape_string($conn, $_POST['comp_alley']);
    $postcode = mysqli_real_escape_string($conn, $_POST['comp_postcode']);
    $tel = mysqli_real_escape_string($conn, $_POST['comp_tel']);
    $fax = mysqli_real_escape_string($conn, $_POST['comp_fax']);
    $hrname = mysqli_real_escape_string($conn, $_POST['comp_hr_name']);
    $hrdepart = mysqli_real_escape_string($conn, $_POST['comp_hr_depart']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (empty($name) || empty($username) || empty($password)) {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน');</script>";
    } else {
        mysqli_begin_transaction($conn);

        try {
            // First create user account
            $insert_user = "INSERT INTO users (username, password, u_type) VALUES (?, ?, 'Company')";
            if ($stmt1 = mysqli_prepare($conn, $insert_user)) {
                mysqli_stmt_bind_param($stmt1, "ss", $username, $password);
                if (!mysqli_stmt_execute($stmt1)) {
                    throw new Exception("Error creating user: " . mysqli_stmt_error($stmt1));
                }
                $new_user_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt1);
            }

            // Then create company record
            $insert_company = "INSERT INTO company (
                comp_name, comp_img, comp_contact, comp_department,
                comp_province, comp_district, comp_sub_district,
                comp_num_add, comp_mu, comp_road, comp_alley,
                comp_postcode, comp_tel, comp_fax,
                comp_hr_name, comp_hr_depart, u_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            if ($stmt2 = mysqli_prepare($conn, $insert_company)) {
                mysqli_stmt_bind_param($stmt2, "ssssssssssssssssi", 
                    $name, $img, $contact, $department, $province, $district, $sub,
                    $add, $mu, $road, $alley, $postcode, $tel, $fax,
                    $hrname, $hrdepart, $new_user_id);
                if (!mysqli_stmt_execute($stmt2)) {
                    throw new Exception("Error creating company: " . mysqli_stmt_error($stmt2));
                }
                mysqli_stmt_close($stmt2);
            }
        
            mysqli_commit($conn);
            echo "<script>alert('เพิ่มบริษัทสำเร็จ'); window.location.href = 'add_company.php';</script>";
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "<script>alert('เกิดข้อผิดพลาด: " . $e->getMessage() . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Company</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../Staff-Page/Staff_Style/profile.css">
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
            <div class="user"><?= $Name ?></div>
            <div class="profile-circle"><?= $firstLetter ?></div>
            <div class="dropdown">
                <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-content">
                    <a href="edit_profile_staff.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                    <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-edit">
        <div class="header-profile2-edit"><p>เพิ่มบริษัท</p></div>

        <div class="header-profile-edit"> 
            <a href="staff_dashboard.php">Home</a>
            <a href="staff_manage.php">จัดการข้อมูล</a>
            <a class="Y-button"><img src="../Icon/i8.png"> เพิ่มบริษัท</a>
        </div>

        <!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Company</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            padding: 0px;
        }

        .form-container {
            max-width: 800px;
            margin left: 100px;
        }

        .form-row {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
        }

        .form-label {
            width: 180px;
            text-align: right;
            padding-right: 20px;
        }

        .form-input {
            flex: 1;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="tel"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .address-group {
            display: flex;
            gap: 10px;
        }

        .address-input {
            width: 120px;
        }

        .button-group {
    display: flex;
    justify-content: space-between;
    width: 65%; /* ให้ปุ่มอยู่ห่างกันสุด */
    max-width: 1200px; /* ปรับขนาดตามต้องการ */
    margin-left: 190px;
}


        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }

        .btn-save {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <form method="POST" action="add_company.php">
            <div class="form-row">
                <label class="form-label">Username:</label>
                <div class="form-input">
                    <input type="text" name="username" required>
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">Password:</label>
                <div class="form-input">
                    <input type="password" name="password" required>
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">ชื่อบริษัท:</label>
                <div class="form-input">
                    <input type="text" name="comp_name" required>
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">แผนก:</label>
                <div class="form-input">
                    <input type="text" name="comp_department">
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">Email บริษัท:</label>
                <div class="form-input">
                    <input type="email" name="comp_contact">
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">ที่อยู่บริษัท:</label>
                <div class="form-input address-group">
                    <input type="text" name="comp_num_add" placeholder="เลขที่">
                    <input type="text" name="comp_mu" placeholder="หมู่">
                    <input type="text" name="comp_road" placeholder="ถนน">
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">ซอย:</label>
                <div class="form-input address-group">
                    <input type="text" name="comp_alley">
                    <input type="text" name="comp_district" placeholder="อำเภอ">
                    <input type="text" name="comp_sub_district" placeholder="ตำบล">
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">จังหวัด:</label>
                <div class="form-input">
                    <input type="text" name="comp_province">
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">รหัสไปรษณีย์:</label>
                <div class="form-input">
                    <input type="text" name="comp_postcode">
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">ชื่อผู้ประสานงาน:</label>
                <div class="form-input">
                    <input type="text" name="comp_hr_name">
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">ตำแหน่งประสานงาน:</label>
                <div class="form-input">
                    <input type="text" name="comp_hr_depart">
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">เบอร์โทร:</label>
                <div class="form-input">
                    <input type="tel" name="comp_tel">
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">แฟกซ์:</label>
                <div class="form-input">
                    <input type="tel" name="comp_fax">
                </div>
            </div>

            <div class="button-group">
                <button class="b-red"><a href="staff_manage.php" class="cancel-button">ยกเลิก<img src="../Icon/i10.png"></a></button>
                <button class="b-green" type="submit" name="save">บันทึก <img src="../Icon/i8.png"></button>
            </div>
        </form>
    </div>
</body>
</html>
    </div>
</body>
</html>

