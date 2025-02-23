<?php
include '../config.php';

if (isset($_GET['comp_name'])) {
    $comp_name = $_GET['comp_name'];
    
    $query = "SELECT * FROM company WHERE comp_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $comp_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row); // ส่งข้อมูลบริษัทกลับในรูปแบบ JSON
        
    } else {
        echo json_encode(["error" => "Company not found"]);
    }
}
?>
