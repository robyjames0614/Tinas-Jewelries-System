<?php
session_start();
include('db_conn.php');

// AUTOMATIC CREATE 'cart' TABLE KUNG WALA PA (INCLUDES selected_size)
$create_table_sql = "CREATE TABLE IF NOT EXISTS `cart` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(100) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `selected_size` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

mysqli_query($conn, $create_table_sql);

// CHECK & ADD 'selected_size' COLUMN IF NEEDED
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM `cart` LIKE 'selected_size'");
if (mysqli_num_rows($check_col) == 0) {
    mysqli_query($conn, "ALTER TABLE `cart` ADD `selected_size` VARCHAR(50) DEFAULT NULL");
}

// UPDATE SIZE AND PRICE VIA AJAX OR POST
if (isset($_POST['update_size'])) {
    $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
    $new_size = mysqli_real_escape_string($conn, $_POST['size']);
    $new_price = isset($_POST['price']) ? (float)$_POST['price'] : null;

    if ($new_price !== null) {
        mysqli_query($conn, "UPDATE `cart` SET selected_size = '$new_size', price = '$new_price' WHERE id = '$cart_id'");
    } else {
        mysqli_query($conn, "UPDATE `cart` SET selected_size = '$new_size' WHERE id = '$cart_id'");
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
        exit();
    }
    header('Location: cart.php');
    exit();
}

// KAPAG CLINICK ANG 'ADD TO CART'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_to_cart']) || isset($_POST['product_name']))) {
    $product_name  = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);
    $selected_size = isset($_POST['selected_size']) ? mysqli_real_escape_string($conn, $_POST['selected_size']) : null;
    $product_qty   = 1;

    $user_id = isset($_SESSION['username']) ? $_SESSION['username'] : session_id();

    $check_sql = "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'";
    if ($selected_size !== null) {
        $check_sql .= " AND selected_size = '$selected_size'";
    }
    $check_cart = mysqli_query($conn, $check_sql);
    
    if ($check_cart && mysqli_num_rows($check_cart) > 0) {
        $row_item = mysqli_fetch_assoc($check_cart);
        $item_id = $row_item['id'];
        mysqli_query($conn, "UPDATE `cart` SET quantity = quantity + 1 WHERE id = '$item_id'");
    } else {
        mysqli_query($conn, "INSERT INTO `cart` (user_id, name, price, image, quantity, selected_size) VALUES ('$user_id', '$product_name', '$product_price', '$product_image', '$product_qty', '$selected_size')");
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $get_all_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'");
        $cart_items = array();
        $total_count = 0;
        $grand_total = 0;

        while($row = mysqli_fetch_assoc($get_all_cart)) {
            $cart_items[] = $row;
            $total_count += (int)$row['quantity'];
            $grand_total += ((float)$row['price'] * (int)$row['quantity']);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status'      => 'success',
            'cart_count'  => $total_count,
            'grand_total' => number_format($grand_total, 2),
            'cart_items'  => $cart_items
        ]);
        exit();
    }

    header('Location: cart.php');
    exit();
}

// UPDATE QUANTITY LOGIC
if (isset($_POST['update_qty'])) {
    $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
    $new_qty = (int)$_POST['quantity'];
    if ($new_qty > 0) {
        mysqli_query($conn, "UPDATE `cart` SET quantity = '$new_qty' WHERE id = '$cart_id'");
    }
    header('Location: cart.php');
    exit();
}

// REMOVE ITEM LOGIC
if (isset($_GET['remove'])) {
    $remove_id = mysqli_real_escape_string($conn, $_GET['remove']);
    mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$remove_id'");
    header('Location: cart.php');
    exit();
}

// COUNT CART ITEMS FOR BADGE
$user_id_check = isset($_SESSION['username']) ? $_SESSION['username'] : session_id();
$cart_count_query = mysqli_query($conn, "SELECT SUM(quantity) as total FROM `cart` WHERE user_id = '$user_id_check'");
$cart_count_data = mysqli_fetch_assoc($cart_count_query);
$total_cart_items = $cart_count_data['total'] ? $cart_count_data['total'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart - Tina's Jewelries</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="product.css">

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fafafa; color: #1a1a1a; margin: 0; padding: 0; overflow-x: hidden; }
        
        /* ITAGO ANG MOBILE CONTROLS SA DESKTOP VIEW */
        .nav-right-controls { display: none; align-items: center; gap: 15px; }

        .user-nav-badge {
            padding: 6px 14px; background: rgba(212, 175, 55, 0.1); border-radius: 20px; 
            border: 1px solid rgba(212, 175, 55, 0.3); font-size: 12px;
            display: flex; align-items: center; gap: 8px; margin-left: 10px;
        }
        .user-nav-badge span { font-size: 11px; color: #444; font-weight: 600; }
        .logout-link { color: #ff4d4d !important; font-size: 11px; font-weight: bold; text-decoration: none; }

        .cart-container { max-width: 1000px; margin: 30px auto; padding: 15px; }
        .cart-header { font-family: 'Playfair Display', serif; font-size: 2rem; border-bottom: 1px solid #d4af37; padding-bottom: 15px; margin-bottom: 25px; text-align: center; }
        
        .table-wrapper { width: 100%; overflow-x: auto; background: #fff; box-shadow: 0 5px 20px rgba(0,0,0,0.03); border-radius: 8px; }
        .cart-table { width: 100%; border-collapse: collapse; min-width: 600px; }
        .cart-table th { text-align: left; padding: 15px; background-color: #1a1a1a; color: #d4af37; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; }
        .cart-table td { padding: 15px; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        
        .product-info { display: flex; align-items: center; gap: 15px; }
        .product-info img { width: 60px; height: 60px; object-fit: cover; border: 1px solid #eee; border-radius: 4px; }
        .cart-size-select { font-family: 'Poppins', sans-serif; font-size: 12px; padding: 3px 6px; border: 1px solid #d4af37; border-radius: 4px; background: #fff; margin-top: 5px; outline: none; cursor: pointer; }
        .qty-input { width: 50px; padding: 5px; text-align: center; border: 1px solid #ddd; border-radius: 4px; }
        .remove-btn { color: #ff4d4d; cursor: pointer; text-decoration: none; font-size: 0.75rem; font-weight: 600; }

        .cart-summary { margin-top: 30px; background: #fff; padding: 25px; width: 100%; max-width: 400px; margin-left: auto; border: 1px solid #d4af37; border-radius: 8px; box-sizing: border-box; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .total-price { font-size: 1.4rem; font-weight: 600; color: #d4af37; }
        
        .checkout-btn { display: block; width: 100%; padding: 15px; background-color: #1a1a1a; color: #d4af37; text-align: center; text-decoration: none; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; border: 1px solid #1a1a1a; cursor: pointer; border-radius: 6px; box-sizing: border-box; }
        .checkout-btn:hover { background-color: #d4af37; color: #1a1a1a; border-color: #d4af37; }
        .checkout-btn.disabled { background-color: #ccc; border-color: #ccc; color: #888; cursor: not-allowed; pointer-events: none; }

        @media (max-width: 768px) {
            .nav-right-controls { display: flex !important; }
            .nav-links .cart-nav-item { display: none !important; }
            .user-nav-badge { margin: 10px auto; width: fit-content; }
            .cart-header { font-size: 1.8rem; }
            .cart-summary { max-width: 100%; }
            .cart-container { margin: 15px auto; }
        }
    </style>
</head>
<body>

<header>
    <nav class="navbar">
        <a href="index.php" class="logo" style="text-decoration:none;">TINAS JEWELRIES</a>

        <!-- RIGHT ACTION BUTTONS (Lalabas lang sa Mobile View) -->
        <div class="nav-right-controls">
            <a href="cart.php" class="cart-icon-btn" title="View Cart">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge"><?php echo $total_cart_items; ?></span>
            </a>
            <div class="mobile-menu-btn" onclick="toggleNav()">
                <i class="fas fa-bars"></i>
            </div>
        </div>

        <ul class="nav-links" id="navLinks">
            <li><a href="index.php">Home</a></li>
            <li><a href="product.php">Product</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="track_order.php">Track Order</a></li>
            <li class="cart-nav-item">
                <a href="cart.php" class="active">
                    <i class="fas fa-shopping-cart"></i> Cart 
                    <span class="cart-badge"><?php echo $total_cart_items; ?></span>
                </a>
            </li>
            <?php if(isset($_SESSION['username'])): ?>
                <li class="user-nav-badge">
                    <span>Hi, <?php echo htmlspecialchars(strtoupper($_SESSION['username'])); ?>!</span>
                    <a href="admin/logout.php" class="logout-link">Logout</a>
                </li>
            <?php else: ?>
                <li><a href="login.php" class="btn-login-nav">Login/Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div class="cart-container">
    <h1 class="cart-header">Your Selection</h1>
    
    <div class="table-wrapper">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $user_id = isset($_SESSION['username']) ? $_SESSION['username'] : session_id();
                
                $select_cart = mysqli_query($conn, "
                    SELECT c.*, p.available_sizes, p.price as default_price
                    FROM `cart` c 
                    LEFT JOIN `products` p ON c.name = p.item_name 
                    WHERE c.user_id = '$user_id'
                ");
                
                $subtotal = 0;
                $has_items = false;

                if ($select_cart && mysqli_num_rows($select_cart) > 0) {
                    $has_items = true;
                    while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                        $item_total = (float)$fetch_cart['price'] * (int)$fetch_cart['quantity'];
                        $subtotal += $item_total;
                        $img_path = !empty($fetch_cart['image']) ? $fetch_cart['image'] : 'default.png';
                        
                        // Check kung Ring ang item
                        $clean_name = strtolower($fetch_cart['name']);
                        $is_ring = (strpos($clean_name, 'ring') !== false) && (strpos($clean_name, 'earring') === false);
                        
                        $available_sizes = $fetch_cart['available_sizes'] ?? '';
                        $current_size = $fetch_cart['selected_size'] ?? '';
                        $base_price = !empty($fetch_cart['default_price']) ? $fetch_cart['default_price'] : $fetch_cart['price'];
                ?>
                <tr id="cart-row-<?php echo $fetch_cart['id']; ?>">
                    <td class="product-info">
                        <img src="uploads/<?php echo htmlspecialchars($img_path); ?>" 
                             alt="<?php echo htmlspecialchars($fetch_cart['name']); ?>"
                             onerror="this.onerror=null; this.src='image/<?php echo htmlspecialchars($img_path); ?>';">
                        <div>
                            <span><?php echo htmlspecialchars($fetch_cart['name']); ?></span>
                            
                            <!-- SIZE DROPDOWN (KAPAG RING LANG) -->
                            <?php if ($is_ring): ?>
                                <div style="margin-top: 3px;">
                                    <label style="font-size: 11px; color: #666;">Size: </label>
                                    <select class="cart-size-select" onchange="updateCartSize(<?php echo $fetch_cart['id']; ?>, this)">
                                        <?php 
                                        if (!empty($available_sizes) && strpos($available_sizes, ':') !== false) {
                                            $pairs = explode(',', $available_sizes);
                                            foreach ($pairs as $p) {
                                                $parts = explode(':', $p);
                                                $s_val = trim($parts[0]);
                                                $p_val = isset($parts[1]) ? trim($parts[1]) : $base_price;
                                                $selected = ($current_size == $s_val) ? 'selected' : '';
                                                echo "<option value='{$s_val}' data-price='{$p_val}' {$selected}>Size {$s_val}</option>";
                                            }
                                        } else {
                                            // Standard fallback options
                                            for ($s = 5; $s <= 10; $s++) {
                                                $selected = ($current_size == (string)$s) ? 'selected' : '';
                                                echo "<option value='{$s}' data-price='{$base_price}' {$selected}>Size {$s}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="item-unit-price">₱<span class="unit-price-val"><?php echo number_format((float)$fetch_cart['price'], 2); ?></span></td>
                    <td>
                        <form action="cart.php" method="POST" style="margin:0;">
                            <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['id']; ?>">
                            <input type="number" name="quantity" class="qty-input" value="<?php echo $fetch_cart['quantity']; ?>" min="1" onchange="this.form.submit()">
                            <input type="hidden" name="update_qty" value="1">
                        </form>
                    </td>
                    <td class="item-row-total" data-qty="<?php echo $fetch_cart['quantity']; ?>">₱<span class="row-total-val"><?php echo number_format($item_total, 2); ?></span></td>
                    <td><a href="cart.php?remove=<?php echo $fetch_cart['id']; ?>" class="remove-btn" onclick="return confirm('Remove this item?');">Remove</a></td>
                </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="5" style="text-align:center; padding: 30px; color:#888;">Your cart is empty.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="cart-summary">
        <div class="summary-row"><span>Subtotal</span><span id="subtotal-val">₱<?php echo number_format($subtotal, 2); ?></span></div>
        <div class="summary-row"><span>Shipping</span><span id="shipping-val">₱<?php echo number_format($subtotal > 0 ? 100 : 0, 2); ?></span></div>
        <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 15px;">
        <div class="summary-row"><span style="font-weight: 600;">ESTIMATED TOTAL</span><span class="total-price" id="cart-total">₱<?php echo number_format($subtotal > 0 ? $subtotal + 100 : 0, 2); ?></span></div>
        
        <?php if ($has_items): ?>
            <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        <?php else: ?>
            <button class="checkout-btn disabled" disabled>Proceed to Checkout</button>
        <?php endif; ?>
    </div>
    <a href="product.php" style="text-decoration:none; color:#666; margin-top:20px; display:inline-block; font-size: 0.9rem;">← Continue Shopping</a>
</div>

<script>
function toggleNav() {
    document.getElementById('navLinks').classList.toggle('active');
}

function updateCartSize(cartId, selectElement) {
    let selectedOption = selectElement.options[selectElement.selectedIndex];
    let newSize = selectedOption.value;
    let newPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;

    let row = document.getElementById('cart-row-' + cartId);
    let qty = parseInt(row.querySelector('.item-row-total').getAttribute('data-qty')) || 1;
    let newRowTotal = newPrice * qty;

    // Update row DOM elements
    row.querySelector('.unit-price-val').innerText = newPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    row.querySelector('.row-total-val').innerText = newRowTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    // Recalculate Subtotal & Grand Total
    recalculateCartTotals();

    // Send AJAX update to Server/Database
    let formData = new FormData();
    formData.append('update_size', '1');
    formData.append('cart_id', cartId);
    formData.append('size', newSize);
    formData.append('price', newPrice);

    fetch('cart.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            console.log('Size and price updated successfully!');
        }
    })
    .catch(err => console.error('Error updating size/price:', err));
}

function recalculateCartTotals() {
    let allRowTotals = document.querySelectorAll('.row-total-val');
    let subtotal = 0;

    allRowTotals.forEach(el => {
        let val = parseFloat(el.innerText.replace(/,/g, '')) || 0;
        subtotal += val;
    });

    let shipping = subtotal > 0 ? 100 : 0;
    let grandTotal = subtotal + shipping;

    document.getElementById('subtotal-val').innerText = '₱' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('shipping-val').innerText = '₱' + shipping.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('cart-total').innerText = '₱' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
</script>
</body>
</html>