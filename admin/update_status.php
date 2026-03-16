<?php
session_start();
include('db_conn.php');

// Security check: Siguraduhing naka-login ang admin
if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];

    // 1. Kunin ang lumang status at ang listahan ng items gamit ang Prepared Statement
    $get_order = $conn->prepare("SELECT status, order_items FROM orders WHERE id = ?");
    $get_order->bind_param("i", $order_id);
    $get_order->execute();
    $order_data = $get_order->get_result()->fetch_assoc();
    
    if (!$order_data) {
        die("Order not found.");
    }

    $old_status = $order_data['status'];
    $items_string = $order_data['order_items'];

    // 2. I-update ang status sa orders table
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        
        /**
         * LOGIC PARA SA STOCK ADJUSTMENT
         * Magbabawas lang tayo kung ang status ay naging 'Paid', 'Shipped', o 'Delivered'
         * AT dapat ang dating status ay 'Pending' para maiwasan ang double deduction.
         */
        $status_to_deduct = ['Paid', 'Shipped', 'Delivered'];
        
        if (in_array($new_status, $status_to_deduct) && $old_status == 'Pending') {
            
            // Hatiin ang items (halimbawa: "Saudi Gold (1), Japan Gold (2)")
            $items_array = explode(',', $items_string);
            
            foreach ($items_array as $item) {
                $item = trim($item);
                $qty = 1; // Default quantity
                $item_name = $item;

                // Regex para makuha ang quantity sa loob ng parenthesis (e.g., "Item Name (2)")
                if (preg_match('/^(.*)\s\((?:x)?(\d+)\)$/', $item, $matches)) {
                    $item_name = trim($matches[1]);
                    $qty = (int)$matches[2];
                }

                // 3. ACTUAL UPDATE - Binabawasan ang stock pero hindi bababa sa zero (0)
                // Ang GREATEST(0, stock - ?) ay SQL function para iwas negative inventory
                $update_stock = $conn->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE item_name = ?");
                $update_stock->bind_param("is", $qty, $item_name);
                $update_stock->execute();
                $update_stock->close();
            }
        }
        
        // Success redirect
        header("Location: view_orders.php?msg=Status updated and stock adjusted");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
} else {
    // Redirect kung hindi POST request
    header("Location: view_orders.php");
    exit();
}
?>