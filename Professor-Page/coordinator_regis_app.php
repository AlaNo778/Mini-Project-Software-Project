<?php
session_start();
include '..\config.php';

if (!isset($_SESSION['u_type'])) {
    header("Location: ..\index.php");
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้
if ($_SESSION['u_type'] != 'Professor') {
    header("Location: ..\unauthorized.php");
    exit();
}

// ดึงข้อมูลของอาจารย์จากฐานข้อมูล
$u_id = $_SESSION['u_id']; // รับค่าจาก session ที่เก็บ user_id
$query = "SELECT pf_fname, pf_lname FROM professor WHERE u_id = '$u_id'"; // คำสั่ง SQL ที่ใช้ค้นหาข้อมูลอาจารย์

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (mysqli_num_rows($result) > 0) {
    // ดึงข้อมูลมาเก็บในตัวแปร
    $row = mysqli_fetch_assoc($result);
    $Name = $row['pf_fname'] . ' ' . $row['pf_lname']; // รวมชื่อและนามสกุล
    $firstLetter = mb_substr($row['pf_fname'], 0, 1, "UTF-8");
}

// ดึงข้อมูลนักศึกษา
// รับค่า std_id จาก URL
$id = $_GET['id'];
$sql = "
    SELECT s.*, u.username, r.*, c.*
    FROM student AS s
    JOIN users AS u ON s.u_id = u.u_id
    JOIN registration AS r ON s.std_id = r.std_id
    JOIN company AS c ON r.comp_id = c.comp_id
    WHERE s.std_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "ไม่พบข้อมูล";
    exit;
}

// **ดึง reg_status จาก registration**
$sql = "SELECT reg_status FROM registration WHERE std_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$rowStatus = $result->fetch_assoc();

// ตรวจสอบว่ามีค่า reg_status หรือไม่
if ($rowStatus) {
    $reg_status = $rowStatus['reg_status'];
} else {
    $reg_status = null; // ตั้งค่าเป็น null กรณีไม่พบข้อมูล
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reg_status'])) {
    $status = $_POST['reg_status'];

    // เรียกใช้ฟังก์ชันเพื่ออัปเดตสถานะ
    UpdateStatus($conn, $status,$id);
}

function UpdateStatus($conn, $status, $sid)
{
    $sql = "
            UPDATE registration SET reg_status = ?   WHERE std_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $status, $id);


    if ($stmt->execute()) {
        if ($status == "02") {
            echo '<script>alert("อนุมัติคำขอการปฏิบัติสหกิจสำเร็จ");</script>';
            echo '<script>window.location = "coordinator_regis.php";</script>';
        } else {
            echo '<script>alert("ปฏิเสธคำขอการปฏิบัติสหกิจสำเร็จ");</script>';
            echo '<script>window.location = "coordinator_regis.php";</script>';
        }
    } else {
        echo '<script>alert("ไม่สามารถอัพเดทสถานะได้");</script>';
    }

    $stmt->close();
}




function dateTH($date)
{
    $thaiMonths = [
        "ม.ค.",
        "ก.พ.",
        "มี.ค.",
        "เม.ย.",
        "พ.ค.",
        "มิ.ย.",
        "ก.ค.",
        "ส.ค.",
        "ก.ย.",
        "ต.ค.",
        "พ.ย.",
        "ธ.ค."
    ];
    $date = new DateTime($date);
    $year = $date->format("Y") + 543;
    $month = (int)$date->format("m");
    $day = $date->format("d");

    return "$day {$thaiMonths[$month - 1]} $year";
}


?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลการสมัคร</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style/style-coordinator.css"> <!-- เรียกใช้ไฟล์ CSS -->
    <script src="../script.js" defer></script>
    <!-- เพิ่มการใช้งาน Bootstrap 5 -->
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
                <a href="#"><img src="../Icon/co2.png" alt="Profile Icon"> ใบสมัครสหกิจ</a>
                <a href="coordinator_assign_advisor.php"><img src="../Icon/co3.png" alt="student Icon"> กำหนดอาจารย์ที่ปรึกษา</a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
            <div class="user"> <?= $Name ?> </div>
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
                <h2>ข้อมูลการสมัคร</h2>
                <nav aria-label="breadcrumb">
                    <div class="btn-group btn-group-sm" role="group" aria-label="page">
                        <a class="btn btn-outline-secondary" href="coordinator_dashboard.php">Home</a>
                    </div>
                    <div class="btn-group btn-group-sm" role="group" aria-label="page">
                        <a class="btn btn-outline-secondary" href="coordinator_regis.php">รายชื่อนักศึกษา</a>
                    </div>
                    <div class="btn-group btn-group-sm" role="group" aria-label="page">
                        <a class="btn btn-warning" href="#">ข้อมูลการสมัคร</a>
                    </div>
                </nav>
        </div>


    <div class="student-info mt-4">
        <h4>รหัสนักศึกษา: <?= $row["username"] ?> <?= $row["std_fname"] ?> <?= $row["std_lname"] ?></h4>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#nav-info" type="button" role="tab" aria-controls="nav-home" aria-selected="true">ข้อมูลส่วนตัว</button>
                <button class="nav-link" id="comp-tab" data-bs-toggle="tab" data-bs-target="#nav-comp" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">ข้อมูลสถานประกอบการ</button>
                <button class="nav-link" id="file-tab" data-bs-toggle="tab" data-bs-target="#nav-file" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">เอกสารที่อัปโหลด</button>
            </div>
        </nav>

        <div class="card">
            <div class="tab-content" id="nav-tabContent">
                <!-- ข้อมูลส่วนตัว -->
                <div class="tab-pane fade show active" id="nav-info" role="tabpanel" aria-labelledby="nav-info" tabindex="0">
                    <div class="card-body">
                        <p><strong>ชื่อ-สกุล:</strong> <?= $row["std_fname"] ?> <?= $row["std_lname"] ?></p>
                        <p><strong>รหัสนักศึกษา:</strong> <?= $row["username"] ?></p>
                        <p><strong>หลักสูตร:</strong> <?= $row["std_major"] ?></p>
                        <p><strong>เกรดเฉลี่ย:</strong> <?= $row["reg_gpax"] ?></p>
                        <p><strong>เบอร์โทรศัพท์:</strong> <?= $row["std_tel"] ?></p>
                        <p><strong>Email:</strong> <?= $row["std_email_1"] ?></p>
                        <p><strong>Facebook:</strong> <?= $row["std_facebook"] ?></p>
                        <p><strong>LINE ID:</strong> <?= $row["std_id_line"] ?></p>
                        <p><strong>ภาคการศึกษาที่ลงทะเบียนปฏิบัติสหกิจ:</strong> <?= $row["reg_sub_intern"] ?></p>
                        <p><strong>ภาคการศึกษาที่จะออกปฏิบัติสหกิจศึกษา:</strong> <?= $row["reg_semester"] ?> (<?= dateTH($row["reg_start_date"]) ?> - <?= dateTH($row["reg_end_date"]) ?>)</p>
                    </div>
                </div>

                <!-- ข้อมูลสถานประกอบการ -->
                <div class="tab-pane fade" id="nav-comp" role="tabpanel" aria-labelledby="nav-comp" tabindex="0">
                    <div class="card-body">
                        <p><strong>ตำแหน่งงานที่ปฏิบัติสหกิจ:</strong> <?= $row["reg_job"] ?></p>
                        <p><strong>ชื่อสถานประกอบการ:</strong> <?= $row["comp_name"] ?></p>
                        <p><strong>แผนก:</strong> <?= $row["comp_department"] ?></p>
                        <p><strong>ที่อยู่สถานประกอบการ:</strong></p>
                        <?php
                        // ตรวจสอบข้อมูลแต่ละคอลัมน์ว่ามีข้อมูลหรือไม่
                        if (isset($row['comp_num_add']) && !empty($row['comp_num_add']) && isset($row['comp_mu']) && !empty($row['comp_mu']) && isset($row['comp_road']) && !empty($row['comp_road']) && isset($row['comp_alley']) && !empty($row['comp_alley']) && isset($row['comp_sub_district']) && !empty($row['comp_sub_district']) && isset($row['comp_district']) && !empty($row['comp_district']) && isset($row['comp_province']) && !empty($row['comp_province']) && isset($row['comp_postcode']) && !empty($row['comp_postcode'])):
                            echo $row['comp_num_add'] . " หมู่ " . $row['comp_mu'] . " ถนน " . $row['comp_road'] . " " . $row['comp_alley'] . " ตำบล/แขวง " . $row['comp_sub_district'] . " อำเภอ/เขต " . $row['comp_district'] . " จังหวัด " . $row['comp_province'] . " รหัสไปรษณี " . $row['comp_postcode'];
                        else:
                            echo "ไม่มีข้อมูลที่อยู่สถานประกอบการ";
                        endif;
                        ?>
                    </div>
                </div>

                <!-- เอกสารที่อัปโหลด -->
                <div class="tab-pane fade" id="nav-file" role="tabpanel" aria-labelledby="nav-file" tabindex="0">
                    <div class="card-body">
                        <p><strong>ใบสมัครเข้าร่วมปฏิบัติฝึกงาน:</strong>
                            <?php if (!empty($row['reg_paper'])): ?>
                                <a href="../File/File_paper/<?= $row['reg_paper'] ?>" target="_blank" class="btn btn-outline-primary">
                                    <i class="fas fa-file-pdf"></i> ใบสมัครสก.01 (<?= htmlspecialchars($row["username"]) ?>)</a>
                            <?php else: ?>
                                <a class=" btn btn-outline-danger disabled">
                                    <i class="fas fa-file-pdf"></i> ใบสมัครสก.01 (<?= htmlspecialchars($row["username"]) ?>)
                                </a>
                            <?php endif; ?>
                        <p><strong>ผลการเรียนของนักศึกษา:</strong>
                            <?php if (!empty($row['reg_transcript'])): ?>
                                <a href="../File/File_transcipt/<?= $row['reg_transcript'] ?>" target="_blank" class="btn btn-outline-primary">
                                    <i class="fas fa-file-pdf"></i> ผลการเรียน (<?= htmlspecialchars($row["username"]) ?>)</a>
                            <?php else: ?>
                                <a class=" btn btn-outline-danger disabled">
                                    <i class="fas fa-file-pdf"></i> ผลการเรียน (<?= htmlspecialchars($row["username"]) ?>)
                                </a>
                            <?php endif; ?>
                        <p><strong>ข้อมูลเพิ่มเติม:</strong>
                            <?php if (!empty($row['reg_resume'])): ?>
                                <a href="../File/File_resume/<?= $row['reg_resume'] ?>" target="_blank" class="btn btn-outline-primary">
                                    <i class="fas fa-file-pdf"></i>เรซูเม่ (<?= htmlspecialchars($row["username"]) ?>)</a><br>
                            <?php else: ?> -<?php endif; ?>
                    </div>
                </div>
                    
            </div>
        </div>
    </div>
        <br>
        <div align="center">
            <!-- ตรวจสอบสถานะก่อนเปิดใช้งานปุ่ม -->
            <?php $disabled = ($row["reg_status"] !== "01") ? "disabled" : ""; ?>
            <form method="POST">
                <button type="submit" name="reg_status" value="2.1" class="btn btn-danger"
                    onclick="return confirmReject();"<?= $disabled ?>>ปฏิเสธ</button>
                <button type="submit" name="reg_status" value="02" class="btn btn-success" <?= $disabled ?>>อนุมัติ</button>
            </form>

        </div>
    </div>

</body>

</html>
<script>
    function confirmReject() {
        return confirm("ยืนยันการปฏิเสธหรือไม่?");
    }
</script>