<?php
session_start();
include '../config.php';
if (!isset($_SESSION['u_type'])) {
    header("Location: ..\index.php");
    exit();
}
// ตรวจสอบสิทธิ์ผู้ใช้
if ($_SESSION['u_type'] != 'Student') {
    header("Location: ..\unauthorized.php");
    exit();
}

$u_id = $_SESSION['u_id']; // รับค่าจาก session ที่เก็บ user_id
$query = "SELECT std_id,std_fname, std_lname FROM student WHERE u_id = '$u_id'";

$result = mysqli_query($conn, $query); // ดำเนินการคำสั่ง SQL

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (mysqli_num_rows($result) > 0) {
    // ดึงข้อมูลมาเก็บในตัวแปร
    $row = mysqli_fetch_assoc($result);
    $std_id =$row['std_id'];
    $Name = $row['std_fname'] . ' ' . $row['std_lname']; // รวมชื่อและนามสกุล
    $firstLetter = mb_substr($row['std_fname'], 0, 1, "UTF-8");
}

$query_reg_id = "SELECT reg_id FROM registration WHERE std_id = '$std_id' ";
 
$result_reg_id = mysqli_query($conn, $query_reg_id); 
$row_reg_id = mysqli_fetch_assoc($result_reg_id);

if (empty($row_reg_id)){
    header("Location: student_dashboard.php");    
    exit();
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
                <a href="file_student.php"><img src="../Icon/i3.png" alt="Status Icon"> ไฟล์เอกสาร</a>
            </div>
        </div>
        <div class="logo-psu"><img src="../Icon/icon-psu.png" alt="PSU Logo"></div>
        <div class="bar-user">
            <div class="user"> <?= $Name ?> </div>
            <div class="profile-circle"><?= $firstLetter ?></div>
            <div class="dropdown">

                <button class="dropbtn"><i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-content">
                    <a href="#"><img src="../Icon/i6.png" alt="EditProfile Icon">จัดการบัญชี</a>
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
                <a class="btn btn-outline-secondary" href="student_dashboard.php">หน้าหลัก</a>
            </div>
            <div class="btn-group btn-group-sm" role="group" aria-label="page">
                <a class="btn btn-warning" href="#">ข้อมูลนักศึกษา</a> 
            </div>
        </nav>
    </div>

        <br>

        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress">
                <div class="progress-bar"></div>
            </div>

            <!-- วงกลมในแต่ละขั้นตอน -->
            <?php
        
        $sql = "
                SELECT s.*,r.reg_status
                FROM student AS s
                JOIN registration AS r ON s.std_id = r.std_id
                WHERE s.std_id = '$std_id' ";

        $results = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($results);
        $status = $row['reg_status'];
        $rejected_message = '';  // Initialize the rejected message variable

        if ($status == '01') {
            $step = 1; // แจ้งความประสงค์
        } elseif ($status == '02') {
            $step = 2; // อาจารย์อนุมัติ
        } elseif ($status == '03') {
            $step = 3; // เจ้าหน้าที่อนุมัติ
        } elseif ($status == '04') {
            $step = 4; // สถานประกอบการ
        } elseif ($status == '05') {
            $step = 5; // ทำหนังสือส่งตัว
        } elseif ($status == '06') {
            $step = 6; // เสร็จสมบูรณ์
        } elseif (in_array($status, ['2.1', '3.1', '4.1'])) {
            $step = (int)$status; // ถูกปฏิเสธ
            $rejected_message = "❌ สถานะถูกปฏิเสธ";  // Set the rejected message
        } else {
            $step = 0; // ไม่พบสถานะ
        }
        ?>
        <div class="d-flex justify-content-between position-relative">
            <div class="text-center" style="width: 20%;">
                <div class="step-circle <?= ($step >= 1) ? 'active' : '' ?>">1</div>
                <div class="step-label">แจ้งความประสงค์</div>
            </div>
            <div class="text-center" style="width: 20%;">
                <div class="step-circle <?= ($step >= 2) ? (($status == '2.1') ? 'step-rejected' : 'active') : '' ?>">2</div>
                <div class=" step-label">อาจารย์อนุมัติ</div>
            </div>
            <div class="text-center" style="width: 20%;">
                <div class="step-circle <?= ($step >= 3) ? (($status == '3.1') ? 'step-rejected' : 'active') : '' ?>">3</div>
                <div class="step-label">เจ้าหน้าที่อนุมัติ</div>
            </div>
            <div class="text-center" style="width: 20%;">
                <div class="step-circle <?= ($step >= 4) ? (($status == '4.1') ? 'step-rejected' : 'active') : '' ?>">4</div>
                <div class=" step-label">สถานประกอบการอนุมัติ</div>
            </div>
            <div class="text-center" style="width: 20%;">
                <div class="step-circle <?= ($step >= 5) ? 'active' : '' ?>">5</div>
                <div class="step-label">จัดทำหนังสือส่งตัว</div>
            </div>
            <div class="text-center" style="width: 20%;">
                <div class="step-circle <?= ($step >= 6) ? 'active' : '' ?>">6</div>
                <div class="step-label">เสร็จสมบูรณ์🎉</div>
            </div>
        </div>
        <?php if (!empty($rejected_message)): ?>
            <div class="rejected-message">
                <?= $rejected_message; ?>
            </div>
        <?php endif; ?>

        </div>
        <br>

        <?php
        
        $sql = "
                SELECT s.*, u.username, r.*, c.*
                FROM student AS s
                JOIN users AS u ON s.u_id = u.u_id
                JOIN registration AS r ON s.std_id = r.std_id
                JOIN company AS c ON r.comp_id = c.comp_id
                WHERE s.std_id = '$std_id' ";
        $results = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($results);

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
            // แยกปี เดือน วัน
            $date = new DateTime($date);
            $year = $date->format("Y") + 543;
            $month = (int)$date->format("m");
            $day = $date->format("d");

            return "$day {$thaiMonths[$month - 1]} $year";
        }
        ?>
        <div class="student-info mt-4">
            <h4><?= $row["username"] ?> <?= $row["std_fname"] ?> <?= $row["std_lname"] ?></h4>
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#nav-info" type="button" role="tab" aria-controls="nav-home" aria-selected="true">ข้อมูลส่วนตัว</button>
                    <button class="nav-link" id="comp-tab" data-bs-toggle="tab" data-bs-target="#nav-comp" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">ข้อมูลสถานประกอบการ</button>
                    <button class="nav-link" id="file-tab" data-bs-toggle="tab" data-bs-target="#nav-file" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">เอกสารที่อัปโหลด</button>
                </div>
            </nav>
            <div class="card">
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-info" role="tabpanel" aria-labelledby="nav-info" tabindex="0">
                        <div class="card-body">
                            <p><strong>ชื่อ-สกุล:</strong> <?= $row["std_fname"] ?> <?= $row["std_lname"] ?></p>
                            <p><strong>รหัสนักศึกษา:</strong> <?= $row["username"] ?></p>
                            <p><strong>หลักสูตร:</strong> <?= $row["std_major"] ?></p>
                            <p><strong>เกรดเฉลี่ย:</strong> <?= $row["reg_gpax"] ?></p>
                            <p><strong>เบอร์โทรศัพท์:</strong> <?= $row["std_tel"] ?></p>
                            <p><strong>Email:</strong> <?= $row["std_email_1"] ?></p>
                            <p><strong>Email:</strong> <?= $row["std_email_2"] ?></p>
                            <p><strong>Facebook:</strong> <?= $row["std_facebook"] ?></p>
                            <p><strong>LINE ID:</strong> <?= $row["std_id_line"] ?></p>
                            <!--<hr>-->
                            <p><strong>ภาคการศึกษาที่ลงทะเบียนรายวิชาปฏิบัติสหกิจศึกษา:</strong>
                                ภาคการศึกษาที่ <?= $row["reg_sub_intern"] ?></p>
                            <p><strong>ภาคการศึกษาที่จะออกปฏิบัติสหกิจศึกษา:</strong>
                                ภาคการศึกษาที่ <?= $row["reg_semester"] ?>
                                (<?= dateTH($row["reg_start_date"]) ?> - <?= dateTH($row["reg_end_date"]) ?>)
                            </p>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-comp" role="tabpanel" aria-labelledby="nav-comp" tabindex="0">
                        <div class="card-body">
                            <p><strong>ตำแหน่งงานที่ปฏิบัติสหกิจ:</strong> <?= $row["reg_job"] ?></p>
                            <p><strong>ชื่อสถานประกอบการ:</strong> <?= $row["comp_name"] ?></p>
                            <p><strong>แผนก:</strong> <?= $row["comp_department"] ?></p>
                            <p><strong>ที่อยู่ปัจจุบันสถานประกอบการ:</strong>
                                เลขที่ <?= $row["comp_num_add"] ?> หมู่ <?= $row["comp_mu"] ?> ถนน <?= $row["comp_road"] ?>
                                ตำบล/แขวง <?= $row["comp_sub_district"] ?> อำเภอ/เขต <?= $row["comp_district"] ?>
                                จังหวัด <?= $row["comp_province"] ?> รหัสไปรษณี <?= $row["comp_postcode"] ?>
                            </p>
                            <p><strong>ชื่อผู้ประสานงานสถานประกอบการ:</strong> <?= $row["comp_hr_name"] ?></p>
                            <p><strong>ตำแหน่ง:</strong> <?= $row["comp_hr_depart"] ?></p>
                            <p><strong>เบอร์โทรศัพท์:</strong> <?= $row["comp_tel"] ?></p>
                            <p><strong>Email: </strong> <?= $row["comp_contact"] ?></p>
                        </div>
                    </div>
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

                <br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>