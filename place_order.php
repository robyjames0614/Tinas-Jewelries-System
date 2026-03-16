<?php
session_start();
include('admin/db_conn.php');

// 1. Security Check
if (!isset($_SESSION['username'])) {
    echo "unauthorized";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kunin ang data (Huwag na gumamit ng mysqli_real_escape_string dito dahil gagamit tayo ng bind_param)
    $fullname = $_POST['fullname'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $order_items = $_POST['order_items'] ?? '';
    $total_amount = $_POST['total_amount'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? '';
    
    $status = "Pending";
    $order_date = date("Y-m-d H:i:s");

    // 2. Handle Receipt Upload
    $receipt_img = "";
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        // Gumamit ng uniqid para hindi mag-overwrite ang files
        $receipt_img = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES["receipt"]["name"]));
        move_uploaded_file($_FILES["receipt"]["tmp_name"], $target_dir . $receipt_img);
    }

    // 3. I-save gamit ang Prepared Statement (Iwas SQL Injection & Triple Entry)
    $sql = "INSERT INTO orders (fullname, phone, address, order_items, total_amount, payment_method, receipt_img, status, order_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        // "ssssdssss" -> s = string, d = double/decimal
        $stmt->bind_param("ssssdssss", $fullname, $phone, $address, $order_items, $total_amount, $payment_method, $receipt_img, $status, $order_date);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "error: " . $conn->error;
    }

    $conn->close();
}
?>