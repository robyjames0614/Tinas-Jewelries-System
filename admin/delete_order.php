<?php
session_start();
include('db_conn.php');

if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // 1. (Optional) Burahin muna ang image file sa uploads folder para hindi mapuno ang storage
    $query = "SELECT receipt_img FROM orders WHERE id = '$id'";
    $res = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($res);
    
    if (!empty($row['receipt_img'])) {
        $file_path = "../uploads/" . $row['receipt_img'];
        if (file_exists($file_path)) {
            unlink($file_path); // Binubura ang mismong file
        }
    }

    // 2. Burahin ang record sa database
    $sql = "DELETE FROM orders WHERE id = '$id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: view_orders.php?msg=Order Deleted");
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}
?>