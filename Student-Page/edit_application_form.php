<?php 
session_start();
include '..\config.php';

if (!isset($_SESSION['u_type'])) {
    header("Location: ..\index.php");
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้
if ($_SESSION['u_type'] != 'Student') {
    header("Location: ..\unauthorized.php");
    exit();
}

$u_id = $_SESSION['u_id']; 

$query_student = "SELECT 
            s.*,u.username 
          FROM student s 
          JOIN users u ON s.u_id = u.u_id
          WHERE s.u_id = '$u_id'";
 
$result_student = mysqli_query($conn, $query_student); 
$row_student = mysqli_fetch_assoc($result_student);

$query_professor = "SELECT 
            pf_id, pf_fname, pf_lname
          FROM professor  
          WHERE pf_role = 'Coordinator'";
$result_professor = mysqli_query($conn, $query_professor); 

$Name = $row_student['std_fname'] . ' ' . $row_student['std_lname'];
$firstLetter = mb_substr($row_student['std_fname'], 0, 1, "UTF-8");
$std_id = (int)$row_student['std_id'];

$query_company = "SELECT * FROM company";
$result_company = mysqli_query($conn, $query_company); 


$query_regis = "SELECT  * FROM registration 
          WHERE std_id = '$std_id'";
 
$result_regis = mysqli_query($conn, $query_regis); 
$row_regis = mysqli_fetch_assoc($result_regis);

$editable = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $editable = true;
    if (isset($_POST['save'])) {
        $email2 = mysqli_real_escape_string($conn, $_POST['email-2'] ?? '');
        $facebook = mysqli_real_escape_string($conn, $_POST['std_facebook'] ?? '');
        $line = mysqli_real_escape_string($conn, $_POST['std_line'] ?? '');
        $gpax = mysqli_real_escape_string($conn, $_POST['std_gpax'] ?? '');
        $start = mysqli_real_escape_string($conn, $_POST['start'] ?? '');
        $date_start = date('Y-m-d', strtotime($start) ?? '');
        $end = mysqli_real_escape_string($conn, $_POST['end'] ?? '');
        $date_end = date('Y-m-d', strtotime($end) ?? '');
        $semester = mysqli_real_escape_string($conn, $_POST['semester'] ?? '');
        $sub_semester = mysqli_real_escape_string($conn, $_POST['sub_semester'] ?? '');
        $pf_id = (int)mysqli_real_escape_string($conn, $_POST['pf_id'] ?? '');
        $job = mysqli_real_escape_string($conn, $_POST['reg_job'] ?? '');
        $status = 01;

        $reg_company_id = (int)mysqli_real_escape_string($conn, $_POST['reg_company_id'] ?? '');
        $company_name = mysqli_real_escape_string($conn, $_POST['manual_comp_name'] ?? '');
        $company_department = mysqli_real_escape_string($conn, $_POST['comp_department'] ?? '');
        $company_num_add = mysqli_real_escape_string($conn, $_POST['comp_num_add'] ?? '');
        $company_comp_mu = mysqli_real_escape_string($conn, $_POST['comp_mu'] ?? '');
        $company_road = mysqli_real_escape_string($conn, $_POST['comp_road'] ?? '');
        $company_alley = mysqli_real_escape_string($conn, $_POST['comp_alley'] ?? '');

        $company_sub_district = mysqli_real_escape_string($conn, $_POST['comp_sub_district'] ?? '');
        $company_district = mysqli_real_escape_string($conn, $_POST['comp_district'] ?? '');
        $company_province = mysqli_real_escape_string($conn, $_POST['comp_province'] ?? '');
        $company_postcode = mysqli_real_escape_string($conn, $_POST['comp_postcode'] ?? '');

        $company_hr_name = mysqli_real_escape_string($conn, $_POST['comp_hr_name'] ?? '');
        $company_hr_depart = mysqli_real_escape_string($conn, $_POST['comp_hr_depart'] ?? '');
        $company_tel = mysqli_real_escape_string($conn, $_POST['comp_tel'] ?? '');
        $company_contact = mysqli_real_escape_string($conn, $_POST['comp_contact'] ?? '');
        $company_fax = mysqli_real_escape_string($conn, $_POST['comp_fax'] ?? '');
        $company_check = mysqli_real_escape_string($conn, $_POST['checkbox-form'] ?? '');

        $company_new = implode(',', [
            $company_name,
            $company_department,
            $company_num_add,
            $company_comp_mu,
            $company_road,
            $company_alley,
            $company_sub_district,
            $company_district,
            $company_province,
            $company_postcode,
            $company_hr_name,
            $company_hr_depart,
            $company_tel,
            $company_contact,
            $company_fax
        ]);

        $upload_dir_Application = "./../File/File_paper/";
        $upload_dir_Transcript = "./../File/File_transcipt/";
        $upload_dir_Resume = "./../File/File_resume/";
        // Check for file uploads
        if (
            empty($_FILES['application-form']['name']) ||
            empty($_FILES['transcript-form']['name']) ||
            empty($_FILES['resume-form']['name'])
        ) {
            echo "<script>alert('กรุณาอัปโหลดไฟล์ให้ครบทั้ง 3 ไฟล์');</script>";
            echo "<meta http-equiv='refresh' content='0;url=edit_application_form.php'>";
            exit();
        }

        if (isset($_FILES['application-form']) && $_FILES['application-form']['error'] == 0) {
            $file_tmp1 = $_FILES['application-form']['tmp_name'];
            $file_ext1 = pathinfo($_FILES['application-form']['name'], PATHINFO_EXTENSION); // ดึงนามสกุลไฟล์
            $new_file_name1 = "application -" . $row_student['username'] . "." . $file_ext1; // ตั้งชื่อไฟล์ใหม่
            $new_file_path1 = $upload_dir_Application . $new_file_name1;
        
            if (move_uploaded_file($file_tmp1, $new_file_path1)) {
                $file_application = $new_file_name1; // เก็บชื่อไฟล์
            } else {
                echo "<script>alert('อัปโหลดไฟล์ใบสมัครไม่สำเร็จ');</script>"; 
            }
        }
        
        if (isset($_FILES['transcript-form']) && $_FILES['transcript-form']['error'] == 0) {
            $file_tmp2 = $_FILES['transcript-form']['tmp_name'];
            $file_ext2 = pathinfo($_FILES['transcript-form']['name'], PATHINFO_EXTENSION); // ดึงนามสกุลไฟล์
            $new_file_name2 = "transcript -" . $row_student['username'] . "." . $file_ext2; // ตั้งชื่อไฟล์ใหม่
            $new_file_path2 = $upload_dir_Transcript . $new_file_name2;
        
            if (move_uploaded_file($file_tmp2, $new_file_path2)) {
                $file_transcript = $new_file_name2; // เก็บชื่อไฟล์
            } else {
                echo "<script>alert('อัปโหลดไฟล์ทรานสคริปต์ไม่สำเร็จ');</script>"; 
            }
        }

        if (isset($_FILES['resume-form']) && $_FILES['resume-form']['error'] == 0) {
            $file_tmp3 = $_FILES['resume-form']['tmp_name'];
            $file_ext3 = pathinfo($_FILES['resume-form']['name'], PATHINFO_EXTENSION); // ดึงนามสกุลไฟล์
            $new_file_name3 = "resume -" . $row_student['username'] . "." . $file_ext3; // ตั้งชื่อไฟล์ใหม่
            $new_file_path3 = $upload_dir_Resume . $new_file_name3;
        
            if (move_uploaded_file($file_tmp3, $new_file_path3)) {
                $file_resume = $new_file_name3; // เก็บชื่อไฟล์
            } else {
                echo "<script>alert('อัปโหลดไฟล์เรซูเม่ไม่สำเร็จ');</script>"; 
            }
        }

        $update_query1 = "UPDATE student SET std_facebook = ?, std_id_line = ?, std_email_2 = ? WHERE u_id = ?";
        if ($stmt1 = mysqli_prepare($conn, $update_query1)) {
            mysqli_stmt_bind_param($stmt1, "sssi", $facebook, $line, $email2, $u_id);
            mysqli_stmt_execute($stmt1);
            mysqli_stmt_close($stmt1);
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการอัปเดตข้อมูล');</script>";  
        }

        // Registration logic
        if (!empty($_POST['checkbox-form']) && $_POST['checkbox-form'] == '1') {
            // Insert registration if checkbox is checked
            $update_query3 = "
                INSERT INTO registration (
                reg_job,
                reg_start_date,
                reg_end_date,
                reg_semester,
                reg_sub_intern,
                reg_transcript,
                reg_paper,
                reg_resume,
                reg_gpax,
                reg_comment,
                std_id,
                pf_id
                )
                VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";
            if ($stmt3 = mysqli_prepare($conn, $update_query3)) {
                mysqli_stmt_bind_param(
                    $stmt3,
                    "ssssssssssii",  
                    $job,
                    $date_start,
                    $date_end,
                    $semester,
                    $sub_semester,
                    $file_transcript,
                    $file_application,
                    $file_resume,
                    $gpax,
                    $company_new,
                    $std_id,
                    $pf_id
                );
                if (mysqli_stmt_execute($stmt3)) {
                    echo "<script>alert('รบกวนกลับมากรอกฟอร์มอีกครั้งเมื่อเจ้าหน้าที่ตรวจสอบ ข้อมูลสถานประกอบการ'); window.location.href = 'student_dashboard.php'; </script>";   
                    exit();
                } else {
                    echo "<script>alert('เกิดข้อผิดพลาดในการอัปเดตข้อมูล');</script>";
                }
                mysqli_stmt_close($stmt3);
            } else {
                echo "<script>alert('ไม่สามารถเตรียมคำสั่ง SQL ได้');</script>";
            }
        } else {
            $company_new = null ;
            $update_query2 = "
                UPDATE registration 
                SET 
                    reg_job = ?, 
                    reg_start_date = ?, 
                    reg_end_date = ?, 
                    reg_semester = ?, 
                    reg_sub_intern = ?, 
                    reg_transcript = ?, 
                    reg_paper = ?, 
                    reg_resume = ?, 
                    reg_gpax = ?,
                    reg_comment = ?, 
                    reg_status = ?, 
                    comp_id = ?, 
                    pf_id = ?
                WHERE std_id = '$std_id'";
            
            if ($stmt2 = mysqli_prepare($conn, $update_query2)) {
                mysqli_stmt_bind_param(
                    $stmt2,
                    "ssssssssssiii", 
                    $job,
                    $date_start,
                    $date_end,
                    $semester,
                    $sub_semester,
                    $file_transcript,
                    $file_application,
                    $file_resume,
                    $gpax,
                    $company_new,
                    $status,
                    $reg_company_id,
                    $pf_id
                    
                );
                if (!$stmt2) {
                    echo "Error: " . mysqli_error($conn);
                }
                if (mysqli_stmt_execute($stmt2)) {
                    echo "<script>alert('การอัปเดตข้อมูลสำเร็จ');</script>";
                    header("Location: student_dashboard.php");    
                    exit();
                } else {
                    echo "<script>alert('เกิดข้อผิดพลาดในการอัปเดตข้อมูล');</script>";
                };
                mysqli_stmt_close($stmt2);
                if (!$stmt2) {
                    echo "Error: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>









<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style/style-student.css">
    <script src="../script.js" defer></script>

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
        <div class="user"> <?= $Name ?>  </div>
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
    <div class="container-form">
            <div class="header-profile2-form"><p>แก้ไขใบสมัคร</p></div>

            <div class="header-profile-form"> 
                <a href="student_dashboard.php">Home</a>
                <a class="Y-button"><img src="../Icon/i8.png""> กรอกใบสมัคร</a>
            </div>
    
    <div>
                    
                        <form method="POST" enctype="multipart/form-data" action="edit_application_form.php">
                            <div class="sent-form">
                                <div class="form1">
                                    <div><p>ข้อมูลส่วนตัวนักศึกษา :</p></div>
                                </div>
                                <div class="form2">
                                    <p>ชื่อ</p>
                                    <input type="text" name="std_fname" value="<?= $row_student['std_fname'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                    <p>สกุล</p>
                                    <input type="text" name="std_lname" value="<?= $row_student['std_lname'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                    <p>รหัสนักศึกษา</p>
                                    <input type="text" name="username" value="<?= $row_student['username'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                </div>
                                <div class="form3">
                                    <p>สาขา</p>
                                    <input type="text" name="std_major" value="<?= $row_student['std_major'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                    <p>หลักสูตร</p>
                                    <input type="text" name="std_branch" value="<?= $row_student['std_branch'] ?>" <?= !$editable ? 'disabled' : '' ?> required>
                                </div>
                                <div class="form4">
                                    <p>ระดับคะแนนเฉลี่ย</p>
                                    <input id="input1" type="text" name="std_gpax" value="<?= $row_regis['reg_gpax'] ?>" <?= $editable ? 'disabled' : '' ?> require >
                                    <p>เบอร์โทรศัพท์</p>
                                    <input id="input2" type="tel" name="phone" pattern="^0[689]\d{8}$" value="<?= $row_student['std_tel'] ?>" <?= !$editable ? 'disabled' : '' ?>  require >
                                </div>
                                <div class="form5">
                                    <p>Email @email.psu.ac.th</p>
                                    <input type="email" name="email-1" value="<?= $row_student['std_email_1'] ?>" <?= !$editable ? 'disabled' : '' ?> require >
                                    <p>Email อื่นๆ</p>
                                    <input type="email" name="email-2" value="<?= $row_student['std_email_2'] ?>" <?= $editable ? 'disabled' : '' ?> require>
                                </div>
                                <div class="form6">
                                    <p>Facebook</p>
                                    <input id="input3" name="std_facebook" type="text" value="<?= $row_student['std_facebook'] ?>" <?= $editable ? 'disabled' : '' ?> require>
                                    <p>ID.Line</p>
                                    <input id="input4" name="std_line" type="text" value="<?= $row_student['std_id_line'] ?>" <?= $editable ? 'disabled' : '' ?>  require>
                                </div>
                                <div class="form29">
                                    <p>วันที่เริ่มฝึก</p>
                                    <input type="date" name="start" value="<?= $row_regis['reg_start_date'] ?>" <?= $editable ? 'disabled' : '' ?> require>
                                    <p>วันที่สิ้นสุด</p>
                                    <input type="date" name="end" value="<?= $row_regis['reg_end_date'] ?>" <?= $editable ? 'disabled' : '' ?> require>
                                </div>
                                <div class="form7">
                                    <p>ภาคการศึกษาที่จะออกปฏิบัติสหกิจศึกษา</p>
                                    <input type="text" name="semester" placeholder="1/2568" value="<?= $row_regis['reg_semester'] ?>" <?= $editable ? 'disabled' : '' ?> require>
                                </div>
                                <div class="form8">
                                    <p>ภาคการศึกษาที่ลงทะเบียนรายวิชาปฏิบัติสหกิจศึกษา</p>
                                    <input type="text" name="sub_semester" placeholder="1/2568" value="<?= $row_regis['reg_sub_intern'] ?>" <?= $editable ? 'disabled' : '' ?> require>
                                </div>
                                <div class="form9">
                                    <p>ชื่ออาจารย์ผู้ประสานงาน</p>
                                    <select class="professor-name" name="pf_id" <?= $editable ? 'disabled' : '' ?> required>
                                    <?php 
                                        // แสดงรายชื่ออาจารย์
                                        while ($row_professor = mysqli_fetch_assoc($result_professor)) {
                                            echo "<option value='" . $row_professor['pf_id'] . "'>" . $row_professor['pf_fname'] . " " . $row_professor['pf_lname'] . "</option>";
                                        }
                                    ?>
                                    </select>
                                </div>
                                <div class="form10">
                                    <p>ข้อมูลสถานประกอบการ</p>
                                </div>
                                <div class="form11">
                                    <p>ชื่อสถานประกอบการ</p>
                                    <input type="text" id="comp_name" name="manual_comp_name" style="display:none;" require>
                                    <input type="search" id="company-search" name="comp_name" autocomplete="off" list="company-list" onchange="fetchCompanyData()" require>
                                    <datalist id="company-list">
                                        <?php 
                                        while ($row_company = mysqli_fetch_assoc($result_company)){
                                            echo "<option value='" . $row_company['comp_name'] . "' data-id='" . $row_company['comp_id'] . "'></option>";
                                        }
                                        ?>
                                    </datalist>
                                    <input type="hidden" name="reg_company_id" id="comp-id">
                                    
                                    
                                    
                                    <input id="input5" type="checkbox" value=1 name="checkbox-form" onclick="toggleCompanyInput()">
                                    
                                    <img src="../Icon/i11.png">
                                    <p>หน่วยงาน</p>
                                    <input id="input6"type="text" name="comp_department" disabled require>
                                </div>
                                <div class="form12">
                                    <p>ที่อยู่บริษัท</p>
                                </div>
                                <div class="form13">
                                    <p>เลขที่</p>
                                    <input id="input7" type="text" name="comp_num_add" disabled require>
                                    <p>หมู่ที่</p>
                                    <input id="input8" type="text" name="comp_mu" disabled require>
                                    <p>ถนน</p>
                                    <input id="input9" type="text" name="comp_road" disabled require>
                                    <p>ซอย</p>
                                    <input id="input10" type="text" name="comp_alley" disabled require>
                                </div>
                                <div class="form14">
                                    <p>ตำบล</p>
                                    <input id="input11" type="text" name="comp_sub_district" disabled require>
                                    <p>อำเภอ</p>
                                    <input id="input12" type="text" name="comp_district" disabled require>
                                    <p>จังหวัด</p>
                                    <input id="input13" type="text" name="comp_province" disabled require>
                                    <p>รหัสไปรษณีย์</p>
                                    <input id="input14" type="text" name="comp_postcode" disabled require>
                                </div>
                                <div class="form15">
                                    <p>ตำแหน่งงาน</p>
                                    <input type="text" name="reg_job"  value="<?= $row_regis['reg_job'] ?>" <?= $editable ? 'disabled' : '' ?> require>
                                </div>
                                <div class="form16">
                                    <p>ชื่อผู้ประสานงานสถานประกอบการ</p>
                                    <input id="input15" type="text" name="comp_hr_name" disabled require>
                                    <p>ตำแหน่ง</p>
                                    <input id="input16" type="text" name="comp_hr_depart" disabled require>
                                </div>
                                <div class="form17">
                                    <p>เบอร์โทรศัพท์สถานประกอบการ</p>
                                    <input id="input17" type="text" name="comp_tel" disabled require>
                                    <p>Email</p>
                                    <input id="input18" type="text" name="comp_contact" disabled require>
                                </div>
                                <div class="form27">
                                    <p>แฟกซ์</p>
                                    <input id="input19" type="text" name="comp_fax" disabled require>
                                </div>
                                <div class="form18">
                                    <p>นักศึกษาเคยติดต่อสถานประกอบการไว้แล้วหรือไม่</p>
                                    <input type="radio" name="radio1-form" id="ok-form1">
                                    <label for="ok-form">ติดต่อแล้ว</label>
                                    <input type="radio" name="radio1-form" id="no-ok-form1">
                                    <label for="no-ok-form">ยังไม่ได้ติดต่อ</label>
                                </div>
                                <div class="form19">
                                    <p>นักศึกษาปรึกษาอาจารย์ผู้ประสานงานสหกิจศึกษาก่อนกรอกฟอร์มแล้วหรือไม่</p>
                                </div>
                                <div class="form20">
                                    <input type="radio" name="radio2-form" id="ok-form2">
                                    <label for="ok-form">ปรึกษาอาจารย์ผู้ประสานงานเรียบร้อย</label>
                                    <input type="radio" name="radio2-form" id="no-ok-form2">
                                    <label for="no-ok-form">ยังไม่ได้ปรึกษาอาจารย์ที่ปรึกษา</label>
                                </div>
                                <div class="form21">
                                    <p>นักศึกษาต้องไม่เคยติดต่อสถานประกอบการอื่น ระหว่างการแจ้งความประสงค์ปฏิบัติสหกิจศึกษา</p>
                                </div>
                                <div class="form22">
                                    <input type="radio" name="confirm" id="confirm-form">
                                    <label for="confirm-form">รับทราบ และไม่เคยติดต่อสถานประกอบการอื่นนอกจากสถานประกอบการที่ยื่นมา</label>
                                </div>
                                <div class="form23">
                                    <p>เอกสารประกอบการปฏิบัติงานสหกิจ :</p>
                                </div>

                                <div class="form24">
                                    <p>เอกสารแนบ 1. ใบสมัครเข้าร่วมปฏิบัติฝึกงาน *  :</p>
                                    <label class="custom-file-upload" id="application-upload-button">
                                        <input type="file" name="application-form" id="application-form" <?= $editable ? 'disabled' : '' ?> >
                                        เลือกไฟล์
                                    </label>
                                    <span id="application-file-name">ยังไม่ได้เลือกไฟล์</span> <!-- แสดงชื่อไฟล์ที่เลือก -->
                                </div>

                                <div class="form25">
                                    <p>เอกสารแนบ 2.ผลการเรียนของนักศึกษา *  :</p>
                                    <label class="custom-file-upload" id="transcript-upload-button">
                                        <input type="file" name="transcript-form" id="transcript-form" <?= $editable ? 'disabled' : '' ?> >
                                        เลือกไฟล์
                                    </label>
                                    <span id="transcript-file-name">ยังไม่ได้เลือกไฟล์</span> <!-- แสดงชื่อไฟล์ที่เลือก -->
                                </div>

                                <div class="form28">
                                    <p>เอกสารแนบ 3.เรซูเม่ *  :</p>
                                    <label class="custom-file-upload" id="resume-upload-button">
                                        <input type="file" name="resume-form" id="resume-form" <?= $editable ? 'disabled' : '' ?> >
                                        เลือกไฟล์
                                    </label>
                                    <span id="resume-file-name">ยังไม่ได้เลือกไฟล์</span> <!-- แสดงชื่อไฟล์ที่เลือก -->
                                </div>


                                <div class="form26">
                                    <?php if (!$editable): ?>
                                        <button class="b-red-form"><a href="student_dashboard.php" class="cancel-button">ยกเลิก</a></button>
                                        <button class="b-blue-form" type="submit" name="save">บันทึก</button>
                                    <?php else: ?>
                                        <button class="b-red-form"><a href="edit_application_form.php" class="cancel-button">ยกเลิก</a></button>
                                        <button class="b-blue-form" type="submit" name="save">บันทึก</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                    
                        </form>
                
            

    <div>

            
        
    </div>
</body>
</html>
