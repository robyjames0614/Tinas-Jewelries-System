<?php
include('../db_conn.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. I-match ang mga variable sa pinasa ng cart.js
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $total_amount = mysqli_real_escape_string($conn, $_POST['total_amount']);
    $order_items = mysqli_real_escape_string($conn, $_POST['order_items']);
    
    $receipt_img = ""; 

    if (isset($_FILES['receipt_img']) && $_FILES['receipt_img']['error'] == 0) {
        // Tiyaking tama ang path. Kung ang script ay nasa 'admin/' folder,
        // ang 'uploads/' folder ay dapat nasa labas (root) o nasa loob ng admin.
        // I-adjust base sa file structure mo.
        $target_dir = "../uploads/"; 
        
        if (!is_dir($target_dir)) { 
            mkdir($target_dir, 0777, true); 
        }

        $extension = pathinfo($_FILES["receipt_img"]["name"], PATHINFO_EXTENSION);
        $file_name = "receipt_" . time() . "_" . rand(1000, 9999) . "." . $extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["receipt_img"]["tmp_name"], $target_file)) {
            $receipt_img = $file_name;
        }
    }

    // 2. I-update ang SQL Query para gumamit ng tamang variables
    $sql = "INSERT INTO orders (fullname, address, phone, payment_method, total_amount, order_items, status, receipt_img, order_date) 
            VALUES ('$fullname', '$address', '$phone', '$payment_method', '$total_amount', '$order_items', 'Pending', '$receipt_img', NOW())";

    if (mysqli_query($conn, $sql)) {
        // I-echo ang "success" para ma-trigger ang redirect sa cart.js
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
mysqli_close($conn);
?>