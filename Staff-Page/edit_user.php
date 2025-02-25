<?php
session_start();
include '..\config.php';

// Authentication check
if (!isset($_SESSION['u_type']) || $_SESSION['u_type'] != 'Staff') {
    header("Location: ..\unauthorized.php");
    exit();
}

// Get staff ID from session
$staff_uid = $_SESSION['u_id'];
$comp_id = isset($_GET['comp_id']) ? (int)$_GET['comp_id'] : 0;

if (!$comp_id) {
    header("Location: staff_manage_data.php");
    exit();
}

// Initialize variables
$editable = isset($_POST['edit']);
$error_message = '';
$success_message = '';

// Fetch staff and company data in a single query
$query = "SELECT 
    s.stf_fname, s.stf_lname,
    c.*, 
    u.username, u.password
FROM staff s
JOIN users u_staff ON s.u_id = u_staff.u_id
LEFT JOIN company c ON c.comp_id = ?
LEFT JOIN users u ON c.u_id = u.u_id
WHERE s.u_id = ?";

try {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $comp_id, $staff_uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$data) {
        throw new Exception("Company not found");
    }

    // Get staff name for header display
    $Name = $data['stf_fname'] . ' ' . $data['stf_lname'];
    $firstLetter = mb_substr($data['stf_fname'], 0, 1, "UTF-8");

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    try {
        // Validate required fields
        $required_fields = ['username', 'password', 'comp_name'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
            }
        }

        mysqli_begin_transaction($conn);

        // Update company information
        $update_company = "UPDATE company SET 
            comp_name = ?, comp_contact = ?, comp_department = ?,
            comp_province = ?, comp_district = ?, comp_sub_district = ?,
            comp_num_add = ?, comp_mu = ?, comp_road = ?, comp_alley = ?,
            comp_postcode = ?, comp_tel = ?, comp_fax = ?,
            comp_hr_name = ?, comp_hr_depart = ?
            WHERE comp_id = ?";
        
        $stmt = mysqli_prepare($conn, $update_company);
        mysqli_stmt_bind_param($stmt, "sssssssssssssssi", 
            $_POST['comp_name'], $_POST['comp_contact'], $_POST['comp_department'],
            $_POST['comp_province'], $_POST['comp_district'], $_POST['comp_sub_district'],
            $_POST['comp_num_add'], $_POST['comp_mu'], $_POST['comp_road'], $_POST['comp_alley'],
            $_POST['comp_postcode'], $_POST['comp_tel'], $_POST['comp_fax'],
            $_POST['comp_hr_name'], $_POST['comp_hr_depart'], $comp_id
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Update user credentials
        $update_user = "UPDATE users SET username = ?, password = ? WHERE u_id = ?";
        $stmt = mysqli_prepare($conn, $update_user);
        mysqli_stmt_bind_param($stmt, "ssi", 
            $_POST['username'], $_POST['password'], $data['u_id']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);
        $success_message = "อัปเดตข้อมูลสำเร็จ";
        
        // Refresh page with updated data
        header("Location: edit_user.php?comp_id=" . $comp_id . "&success=1");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลบริษัท</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../Staff-Page/Staff_Style/profile.css">
    <script src="../script.js" defer></script>
</head>
<body>
    <!-- Header -->
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
                    <a href="edit_profile_staff.php"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
                    <a href="../logout.php"><img src="../Icon/i7.png" alt="Logout Icon">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-edit">
        <div class="header-profile2-edit"><p>แก้ไขข้อมูลบริษัท</p></div>

        <div class="header-profile-edit"> 
            <a href="staff_dashboard.php">Home</a>
            <a href="staff_manage.php">จัดการข้อมูล</a>
            <a class="Y-button"><img src="../Icon/i8.png"> แก้ไขข้อมูลบริษัท</a>
        </div>

        <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลบริษัท</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../Staff-Page/Staff_Style/profile.css">
    <script src="../script.js" defer></script>
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
            margin-left: 100px;
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
        input[type="password"] {
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
            width: 65%;
            max-width: 1200px;
            margin-left: 190px;
        }
    </style>
</head>
<body>
    
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="edit_user.php?comp_id=<?= $comp_id ?>">
                <!-- Username -->
                <div class="form-row">
                    <label class="form-label">Username:</label>
                    <div class="form-input">
                        <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-row">
                    <label class="form-label">Password:</label>
                    <div class="form-input">
                        <input type="password" name="password" value="<?= htmlspecialchars($data['password']) ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    </div>
                </div>

                <!-- Company Name -->
                <div class="form-row">
                    <label class="form-label">ชื่อบริษัท:</label>
                    <div class="form-input">
                        <input type="text" name="comp_name" value="<?= htmlspecialchars($data['comp_name']) ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    </div>
                </div>

                <!-- Company Name -->
                <div class="form-row">
                    <label class="form-label">แผนก:</label>
                    <div class="form-input">
                        <input type="text" name="comp_department" value="<?= htmlspecialchars($data['comp_department']) ?>" <?= !$editable ? 'disabled' : '' ?> required>
                    </div>
                </div>

                <!-- Contact -->
                <div class="form-row">
                    <label class="form-label">Email บริษัท:</label>
                    <div class="form-input">
                        <input type="text" name="comp_contact" value="<?= htmlspecialchars($data['comp_contact']) ?>" <?= !$editable ? 'disabled' : '' ?>>
                    </div>
                </div>

                <!-- Address -->
                <div class="form-row">
                    <label class="form-label">ที่อยู่บริษัท:</label>
                    <div class="form-input address-group">
                        <input type="text" name="comp_num_add" placeholder="เลขที่" value="<?= htmlspecialchars($data['comp_num_add']) ?>" <?= !$editable ? 'disabled' : '' ?>>
                        <input type="text" name="comp_mu" placeholder="หมู่" value="<?= htmlspecialchars($data['comp_mu']) ?>" <?= !$editable ? 'disabled' : '' ?>>
                        <input type="text" name="comp_road" placeholder="ถนน" value="<?= htmlspecialchars($data['comp_road']) ?>" <?= !$editable ? 'disabled' : '' ?>>
                    </div>
                </div>

                <!-- Location -->
                <div class="form-row">
                    <label class="form-label">ซอย:</label>
                    <div class="form-input address-group">
                        <input type="text" name="comp_alley" placeholder="ซอย" value="<?= htmlspecialchars($data['comp_alley']) ?>" <?= !$editable ? 'disabled' : '' ?>>
                        <input type="text" name="comp_sub_district" placeholder="ตำบล" value="<?= htmlspecialchars($data['comp_sub_district']) ?>" <?= !$editable ? 'disabled' : '' ?>>
                        <input type="text" name="comp_district" placeholder="อำเภอ" value="<?= htmlspecialchars($data['comp_district']) ?>" <?= !$editable ? 'disabled' : '' ?>>
                    </div>
                </div>

                <!-- Other fields -->
                
                <div class="form-row">
                    <label class="form-label">จังหวัด:</label>
                    <div class="form-input">
                        <input type="text" name="comp_province" value="<?= htmlspecialchars($data['comp_province']) ?>" <?= !$editable ? 'disabled' : '' ?>>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">รหัสไปรษณีย์:</label>
                    <div class="form-input">
                        <input type="text" name="comp_postcode" value="<?= htmlspecialchars($data['comp_postcode']) ?>" <?= !$editable ? 'disabled' : '' ?>>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">เบอร์โทร:</label>
                    <div class="form-input">
                        <input type="text" name="comp_tel" value="<?= htmlspecialchars($data['comp_tel']) ?>" <?= !$editable ? 'disabled' : '' ?>>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">แฟกซ์:</label>
                    <div class="form-input">
                        <input type="text" name="comp_fax" value="<?= htmlspecialchars($data['comp_fax']) ?>" <?= !$editable ? 'disabled' : '' ?>>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">ชื่อผู้ประสานงาน:</label>
                    <div class="form-input">
                        <input type="text" name="comp_hr_name" value="<?= htmlspecialchars($data['comp_hr_name']) ?>" <?= !$editable ? 'disabled' : '' ?>>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">ตำแหน่งประสานงาน:</label>
                    <div class="form-input">
                        <input type="text" name="comp_hr_depart" value="<?= htmlspecialchars($data['comp_hr_depart']) ?>" <?= !$editable ? 'disabled' : '' ?>>
                    </div>
                </div>

                <div class="button-group">
                    <?php if (!$editable): ?>
                        <button class="b-red"><a href="staff_manage.php" class="cancel-button">ยกเลิก<img src="../Icon/i10.png"></a></button>
                        <button class="b-blue" type="submit" name="edit">แก้ไข <img src="../Icon/i8.png"></button>
                    <?php else: ?>
                        <button class="b-red"><a href="<?= $_SERVER['PHP_SELF'] ?>?comp_id=<?= $comp_id ?>" class="cancel-button">ยกเลิก<img src="../Icon/i10.png"></a></button>
                        <button class="b-green" type="submit" name="save">บันทึก <img src="../Icon/i8.png"></button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</body>
</html>