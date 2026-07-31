<?php
// Turn on error reporting para sa debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('db_conn.php');

// 1. Security Check
if (!isset($_SESSION['username'])) {
    die("Unauthorized: Walang session. Mangyaring mag-login muna.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_username = $_SESSION['username'];
    
    // 2. Kunin ang User ID mula sa `users` table
    $user_id = 0;
    $stmt_u = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    if ($stmt_u) {
        $stmt_u->bind_param("s", $current_username);
        $stmt_u->execute();
        $res_u = $stmt_u->get_result();
        if ($row_u = $res_u->fetch_assoc()) {
            $user_id = $row_u['id'];
        }
        $stmt_u->close();
    }

    // 3. Ihanda at I-format ang Form Inputs
    $fullname = trim(($_POST['first_name'] ?? '') . ' ' . ($_POST['last_name'] ?? ''));
    if (empty($fullname) && isset($_POST['fullname'])) {
        $fullname = trim($_POST['fullname']);
    }

    $full_address = trim(
        ($_POST['address'] ?? '') . ', ' . 
        (!empty($_POST['apartment']) ? $_POST['apartment'] . ', ' : '') . 
        ($_POST['barangay'] ?? '') . ', ' . 
        ($_POST['city'] ?? '') . ', ' . 
        ($_POST['province'] ?? '')
    );
    if (empty(trim($full_address, ', ')) && isset($_POST['address'])) {
        $full_address = trim($_POST['address']);
    }

    $phone = $_POST['phone'] ?? '';
    $email = $_POST['contact'] ?? $_POST['email'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'COD';
    $total_amount = $_POST['total_amount'] ?? 0;
    $order_items = $_POST['order_items'] ?? '';
    $google_id = $_SESSION['google_id'] ?? 'N/A';
    $status = "Pending";

    // Kung walang ipinasang order_items mula sa HTML, kunin ito sa DB `cart`
    if (empty($order_items)) {
        $items_array = [];
        $calculated_subtotal = 0;
        
        $get_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$current_username'");
        if ($get_cart) {
            while ($c = mysqli_fetch_assoc($get_cart)) {
                $size_str = !empty($c['selected_size']) ? " (Size " . $c['selected_size'] . ")" : "";
                $items_array[] = $c['name'] . $size_str . " (x" . $c['quantity'] . ")";
                
                $calculated_subtotal += ((float)$c['price'] * (int)$c['quantity']);
            }
            $order_items = implode(", ", $items_array);
            
            if ($total_amount == 0 && $calculated_subtotal > 0) {
                $total_amount = $calculated_subtotal + 150;
            }
        }
    }

    // 4. Handle Receipt Upload
    $receipt_img = "";
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $receipt_img = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES["receipt"]["name"]));
        move_uploaded_file($_FILES["receipt"]["tmp_name"], $target_dir . $receipt_img);
    }

    // 5. Save Order Gamit ang Prepared Statement
    $sql = "INSERT INTO orders (fullname, phone, address, email, order_items, total_amount, payment_method, receipt_img, status, order_date, user_id, google_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW(), ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssdssis", 
            $fullname, 
            $phone, 
            $full_address, 
            $email, 
            $order_items, 
            $total_amount, 
            $payment_method, 
            $receipt_img, 
            $user_id, 
            $google_id
        );

        if ($stmt->execute()) {
            // Burahin ang laman ng DB cart para sa user kapag successful ang order
            mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$current_username'");

            // DIRECT REDIRECT NA LANG SA track_order.php (Wala nang lumang alert pop-up)
            header("Location: track_order.php");
            exit();
            
        } else {
            echo "Error executing query: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing query: " . $conn->error;
    }

    $conn->close();
}
?>