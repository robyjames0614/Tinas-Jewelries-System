<?php
// Tiyakin na tama ang path ng db_conn.php mo
include('db_conn.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $total_amount = mysqli_real_escape_string($conn, $_POST['total_amount']);
    $order_items = mysqli_real_escape_string($conn, $_POST['order_items']);
    
    $receipt_img = ""; 

    // Handle Receipt Upload
    // FIX: Binago ang $_FILES['receipt'] sa $_FILES['receipt_img'] para tugma sa cart.js
    if (isset($_FILES['receipt_img']) && $_FILES['receipt_img']['error'] == 0) {
        
        // Siguraduhin na ang folder na 'uploads' ay nasa parehong folder ng script na ito
        // O kung gusto mo sa root, gamitin ang: $target_dir = "../uploads/";
        $target_dir = "uploads/"; 
        
        if (!is_dir($target_dir)) { 
            mkdir($target_dir, 0777, true); 
        }

        $temp_name = $_FILES["receipt_img"]["name"];
        $extension = pathinfo($temp_name, PATHINFO_EXTENSION);
        
        // Mas malinis na naming convention
        $file_name = "receipt_" . time() . "_" . rand(1000, 9999) . "." . $extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["receipt_img"]["tmp_name"], $target_file)) {
            $receipt_img = $file_name;
        }
    }

    // SQL Query
    $sql = "INSERT INTO orders (fullname, address, phone, payment_method, total_amount, order_items, status, receipt_img, order_date) 
            VALUES ('$fullname', '$address', '$phone', '$payment_method', '$total_amount', '$order_items', 'Pending', '$receipt_img', NOW())";

    if (mysqli_query($conn, $sql)) {
        echo "Success";
    } else {
        // I-output ang error para makita natin sa Console kung may mali sa DB
        echo "Error: " . mysqli_error($conn);
    }
}
mysqli_close($conn);
?>