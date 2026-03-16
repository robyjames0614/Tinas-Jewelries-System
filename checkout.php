<?php
session_start();
include('admin/db_conn.php');

// 1. Security Check - Dapat naka-login ang user
if (!isset($_SESSION['username'])) {
    echo "unauthorized";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Kunin ang data mula sa Fetch request sa cart.js
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $order_items = mysqli_real_escape_string($conn, $_POST['order_items']);
    $total_amount = mysqli_real_escape_string($conn, $_POST['total_amount']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    // Default values
    $status = "Pending";
    $order_date = date("Y-m-d H:i:s");

    // 3. Handle Receipt Upload para sa GCash
    $receipt_img = "";
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
        $target_dir = "uploads/";
        // Siguraduhin na exist ang uploads folder
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $receipt_img = time() . "_" . basename($_FILES["receipt"]["name"]);
        move_uploaded_file($_FILES["receipt"]["tmp_name"], $target_dir . $receipt_img);
    }

    // 4. I-save sa 'orders' table
    $sql = "INSERT INTO orders (fullname, phone, address, order_items, total_amount, payment_method, receipt_img, status, order_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssdssss", $fullname, $phone, $address, $order_items, $total_amount, $payment_method, $receipt_img, $status, $order_date);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>