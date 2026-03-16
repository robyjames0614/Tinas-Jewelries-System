<?php
session_start();
include('db_conn.php');

// Kunin ang huling order para i-test
$query = mysqli_query($conn, "SELECT id, order_items FROM orders ORDER BY id DESC LIMIT 1");
$row = mysqli_fetch_assoc($query);

if ($row) {
    $order_id = $row['id'];
    $items_string = $row['order_items'];
    
    echo "<h3>Debugging Order #$order_id</h3>";
    echo "Raw String from Database: <b>'$items_string'</b><br><hr>";

    $items_array = explode(",", $items_string);
    foreach ($items_array as $item) {
        $item = trim($item);
        
        // Gagamit tayo ng mas simpleng Regex para siguradong mahuli
        if (preg_match('/^(.*)\s\(x(\d+)\)$/', $item, $matches)) {
            $name = trim($matches[1]);
            $qty = (int)$matches[2];

            echo "Found Item Name: <b>'$name'</b> | Quantity: <b>$qty</b><br>";

            // I-check kung exist ba sa products table
            $check = mysqli_query($conn, "SELECT item_name, stock FROM products WHERE item_name = '$name'");
            if (mysqli_num_rows($check) > 0) {
                $p = mysqli_fetch_assoc($check);
                echo "<span style='color:green;'>✅ Success: Match found! Current stock: " . $p['stock'] . "</span><br>";
            } else {
                echo "<span style='color:red;'>❌ Error: '$name' NOT FOUND in products table.</span><br>";
                echo "<i>Tip: Check mo kung may extra space sa dulo ng pangalan sa inventory.</i><br>";
            }
        } else {
            echo "<span style='color:orange;'>⚠️ Regex Failed: Hindi ma-parse ang format ng '$item'</span><br>";
        }
        echo "<br>";
    }
} else {
    echo "No orders found to debug.";
}
?>