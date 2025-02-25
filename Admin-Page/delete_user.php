<?php
session_start();
include '../config.php';

// Check if user is logged in and is Admin
if (!isset($_SESSION['u_type']) || $_SESSION['u_type'] != 'Admin') {
    header("Location: ../unauthorized.php");
    exit();
}

// Get user ID from URL parameter
$u_id = isset($_GET['u_id']) ? intval($_GET['u_id']) : 0;

if ($u_id <= 0) {
    $_SESSION['error'] = "Invalid user ID";
    header("Location: admin_manage_data.php");
    exit();
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Delete from student table
    $query = "DELETE FROM student WHERE u_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $u_id);
    mysqli_stmt_execute($stmt);

    // Delete from professor table
    $query = "DELETE FROM professor WHERE u_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $u_id);
    mysqli_stmt_execute($stmt);

    // Delete from company table
    $query = "DELETE FROM company WHERE u_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $u_id);
    mysqli_stmt_execute($stmt);

    // Delete from staff table
    $query = "DELETE FROM staff WHERE u_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $u_id);
    mysqli_stmt_execute($stmt);

    // Finally, delete from users table
    $query = "DELETE FROM users WHERE u_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $u_id);
    mysqli_stmt_execute($stmt);

    // Commit transaction
    mysqli_commit($conn);
    
    $_SESSION['success'] = "ลบผู้ใช้เรียบร้อยแล้ว";
    header("Location: admin_manage_data.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบผู้ใช้: " . $e->getMessage();
    header("Location: admin_manage_data.php");
    exit();
}
?>