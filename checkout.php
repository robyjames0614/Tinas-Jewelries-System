<?php
// Turn on error reporting para sa debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('db_conn.php');

// Security Check: Siguraduhing naka-login ang user gamit ang SweetAlert2
if (!isset($_SESSION['username'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Required - Tina's Jewelries Gold Trading</title>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
        <!-- SweetAlert2 CDN -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            body { 
                background-color: #fffaf5; 
                font-family: 'Poppins', sans-serif; 
            }
            .swal2-title-custom {
                font-family: 'Playfair Display', serif !important;
                color: #1a1a1a !important;
            }
            .swal2-btn-custom {
                font-family: 'Poppins', sans-serif !important;
                font-weight: 600 !important;
                border-radius: 25px !important;
                padding: 10px 28px !important;
                letter-spacing: 1px !important;
            }
        </style>
    </head>
    <body>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Login Required',
                    text: 'Mangyaring mag-login muna bago mag-checkout.',
                    icon: 'warning',
                    iconColor: '#d4af37',
                    confirmButtonText: 'MAG-LOGIN NA',
                    confirmButtonColor: '#1a1a1a',
                    background: '#ffffff',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        title: 'swal2-title-custom',
                        confirmButton: 'swal2-btn-custom'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.php';
                    }
                });
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}

$user_id = $_SESSION['username'];

// Kunin ang laman ng cart mula sa database
$get_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'");

$cart_items = [];
$subtotal = 0;

if ($get_cart && mysqli_num_rows($get_cart) > 0) {
    while ($row = mysqli_fetch_assoc($get_cart)) {
        $cart_items[] = $row;
        $subtotal += ((float)$row['price'] * (int)$row['quantity']);
    }
}

$shipping_fee = $subtotal > 0 ? 150 : 0;
$grand_total = $subtotal + $shipping_fee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Tina's Jewelries Gold Trading</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fffaf5; color: #1a1a1a; margin: 0; padding: 20px; }
        .checkout-container { max-width: 1000px; margin: 0 auto; display: flex; flex-wrap: wrap; gap: 30px; }
        .form-section { flex: 1 1 500px; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .summary-section { flex: 1 1 350px; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); height: fit-content; border: 1px solid #d4af37; }
        
        h2 { font-family: 'Playfair Display', serif; color: #1a1a1a; margin-top: 0; border-bottom: 2px solid #d4af37; padding-bottom: 10px; font-size: 1.5rem; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; color: #444; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: inherit; }
        
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }

        .order-item { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #eee; padding: 10px 0; }
        .order-item img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; margin-right: 10px; }
        .item-info { display: flex; align-items: center; }

        .total-row { display: flex; justify-content: space-between; margin-top: 15px; font-weight: 600; font-size: 1.1rem; }
        .grand-total { color: #d4af37; font-size: 1.4rem; font-weight: 700; }

        .submit-btn { width: 100%; padding: 15px; background: #1a1a1a; color: #fff; border: none; font-weight: 600; font-size: 1rem; cursor: pointer; border-radius: 4px; transition: 0.3s; margin-top: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .submit-btn:hover { background: #d4af37; color: #1a1a1a; }
        
        .back-link { display: inline-block; margin-bottom: 20px; color: #666; text-decoration: none; font-size: 0.9rem; }
        
        /* GCash QR Styling */
        .gcash-qr-box { background: #fdfbf7; padding: 15px; border: 1px dashed #d4af37; border-radius: 6px; margin-bottom: 15px; text-align: center; }
        .gcash-qr-box img { max-width: 220px; width: 100%; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin: 10px 0; }

        /* SweetAlert Custom Classes */
        .swal2-title-custom {
            font-family: 'Playfair Display', serif !important;
            color: #1a1a1a !important;
        }
        .swal2-btn-custom {
            font-family: 'Poppins', sans-serif !important;
            font-weight: 600 !important;
            border-radius: 25px !important;
            padding: 10px 28px !important;
            letter-spacing: 1px !important;
        }
    </style>
</head>
<body>

<div style="max-width: 1000px; margin: 0 auto;">
    <a href="cart.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Shopping Cart</a>
</div>

<div class="checkout-container">
    
    <!-- LEFT: SHIPPING & PAYMENT FORM -->
    <div class="form-section">
        <h2>Shipping Information</h2>
        
        <!-- NAGSU-SUBMIT PAPUNTANG place_order.php -->
        <form id="checkoutForm" action="place_order.php" method="POST" enctype="multipart/form-data" onsubmit="handlePlaceOrder(event)">
            
            <input type="hidden" name="total_amount" value="<?php echo $grand_total; ?>">
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="Juan Dela Cruz" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="juan@gmail.com" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" placeholder="09123456789" required>
                </div>
            </div>

            <div class="form-group">
                <label>Complete Delivery Address</label>
                <textarea name="address" rows="3" placeholder="House/Unit No., Street, Barangay, City, Province" required></textarea>
            </div>

            <h2>Payment Method</h2>
            
            <div class="form-group">
                <label>Select Payment</label>
                <select name="payment_method" id="payment_method" onchange="togglePaymentFields()" required>
                    <option value="GCash">GCash / Online Transfer</option>
                    <option value="COD">Cash on Delivery (COD)</option>
                </select>
            </div>

            <!-- GCASH QR CONTAINER -->
            <div id="gcash_container" class="gcash-qr-box">
                <p style="margin: 0; font-weight: 600; font-size: 0.9rem; color: #1a1a1a;">
                    Scan QR Code or Transfer via GCash Number
                </p>
                
                <img src="img/gcash-qr.png" alt="GCash QR Code" 
                     onerror="this.onerror=null; this.src='uploads/gcash-qr.png';">
                
                <div style="font-size: 0.9rem; color: #444; margin-top: 5px;">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 5px;">
                        <strong>GCash No:</strong> 
                        <span id="gcash_num" style="font-weight: 600; color: #1a1a1a;">09238261476</span>
                        
                        <!-- COPY BUTTON -->
                        <button type="button" onclick="copyGCashNumber()" style="background: #1a1a1a; color: #d4af37; border: none; padding: 3px 10px; border-radius: 4px; cursor: pointer; font-size: 0.75rem; font-weight: 600; transition: 0.2s;">
                            <i class="fas fa-copy"></i> <span id="copy_btn_text">Copy</span>
                        </button>
                    </div>
                    <strong>Account Name:</strong> LITO R.
                </div>
            </div>

            <!-- RECEIPT UPLOAD CONTAINER -->
            <div class="form-group" id="receipt_container">
                <label>Upload Payment Receipt</label>
                <input type="file" name="receipt" id="receipt_input" accept="image/*" required>
            </div>

            <button type="submit" class="submit-btn">Place Order Now</button>
        </form>
    </div>

    <!-- RIGHT: ORDER SUMMARY -->
    <div class="summary-section">
        <h2>Order Summary</h2>
        
        <?php if(!empty($cart_items)): ?>
            <?php foreach($cart_items as $item): ?>
                <div class="order-item">
                    <div class="item-info">
                        <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" onerror="this.src='image/<?php echo htmlspecialchars($item['image']); ?>';">
                        <div>
                            <div style="font-weight:600; font-size:0.9rem;"><?php echo htmlspecialchars($item['name']); ?></div>
                            
                            <!-- DISPLAY SELECTED SIZE -->
                            <?php if(!empty($item['selected_size'])): ?>
                                <div style="font-size:0.8rem; color:#d4af37; font-weight: 600; margin-top: 2px;">
                                    Size: <?php echo htmlspecialchars($item['selected_size']); ?>
                                </div>
                            <?php endif; ?>

                            <div style="font-size:0.8rem; color:#666;">Qty: <?php echo $item['quantity']; ?></div>
                        </div>
                    </div>
                    <div>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:#888;">Walang laman ang iyong cart.</p>
        <?php endif; ?>

        <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;">
            <div class="total-row">
                <span>Subtotal</span>
                <span>₱<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="total-row">
                <span>Shipping Fee</span>
                <span>₱<?php echo number_format($shipping_fee, 2); ?></span>
            </div>
            <div class="total-row" style="margin-top: 15px; border-top: 2px dashed #ddd; padding-top: 10px;">
                <span>Total Amount</span>
                <span class="grand-total">₱<?php echo number_format($grand_total, 2); ?></span>
            </div>
        </div>
    </div>

</div>

<script>
function togglePaymentFields() {
    var method = document.getElementById('payment_method').value;
    var gcashContainer = document.getElementById('gcash_container');
    var receiptContainer = document.getElementById('receipt_container');
    var receiptInput = document.getElementById('receipt_input');

    if (method === 'COD') {
        gcashContainer.style.display = 'none';
        receiptContainer.style.display = 'none';
        receiptInput.removeAttribute('required');
    } else {
        gcashContainer.style.display = 'block';
        receiptContainer.style.display = 'block';
        receiptInput.setAttribute('required', 'required');
    }
}

function copyGCashNumber() {
    var numberText = document.getElementById("gcash_num").innerText;
    
    navigator.clipboard.writeText(numberText).then(function() {
        var btnText = document.getElementById("copy_btn_text");
        btnText.innerText = "Copied!";
        
        setTimeout(function() {
            btnText.innerText = "Copy";
        }, 2000);
    }).catch(function(err) {
        alert("Failed to copy number: " + err);
    });
}

function handlePlaceOrder(event) {
    event.preventDefault(); // Pigilan muna ang agarang pag-submit para lumabas ang SweetAlert
    
    Swal.fire({
        title: 'Order Placed Successfully!',
        text: 'THANKYOU IVERIFY PO MUNA YUNG ORDER AT YUNG PAYMENT NINYO MORE ORDER TO COME ',
        icon: 'success',
        iconColor: '#d4af37',
        confirmButtonText: 'OK',
        confirmButtonColor: '#1a1a1a',
        background: '#ffffff',
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            title: 'swal2-title-custom',
            confirmButton: 'swal2-btn-custom'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // I-submit na ang form papuntang place_order.php pagka-click ng OK
            document.getElementById('checkoutForm').submit();
        }
    });
}
</script>

</body>
</html>