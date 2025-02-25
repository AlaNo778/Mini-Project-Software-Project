<?php
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_ids']) && isset($_POST['pf_id'])) {
    var_dump($_POST);  // ดูค่าที่ส่งมาจากฟอร์ม

    $pf_id = intval($_POST['pf_id']);  // แปลงเป็นตัวเลข
    $student_ids = $_POST['student_ids'];  // รับค่าจากฟอร์ม

    // แปลง array ของ student_ids เป็นตัวเลข
    $student_ids = array_map('intval', $student_ids); 
    
    //var_dump($student_ids);  // ตรวจสอบค่าหลังจากแปลง

    // ตรวจสอบว่า pf_id เป็น 0 หรือไม่
    if ($pf_id == 0) {
        echo "❌ Error: กรุณาเลือกอาจารย์ที่ปรึกษา";
        exit();
    }

    // ตรวจสอบว่า student_ids มีค่า
    if (empty($student_ids)) {
        echo "❌ Error: กรุณาเลือกนักศึกษาก่อนยืนยัน";
        exit();
    }

    // อัปเดต pf_id ของนักศึกษา
    $studentIdsStr = implode(",", $student_ids);
    $query = "UPDATE student SET pf_id = $pf_id WHERE std_id IN ($studentIdsStr)";
    $stmt = $conn->prepare($query);
    if (!$stmt->execute()) {
        echo "❌ Error: " . $stmt->error;
        exit();
    }

    // กลับไปหน้าหลัก
    header("Location: coordinator_assign_advisor.php");
    exit();
} else {
    echo "กรุณาเลือกนักศึกษา!";
}
?>
