<?php
session_start();
include('../db_conn.php');

if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $available_sizes = mysqli_real_escape_string($conn, $_POST['available_sizes']);

    $target_dir = "../uploads/";
    $image_name = time() . "_" . basename($_FILES["product_image"]["name"]);
    $target_file = $target_dir . $image_name;

    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO products (item_name, category, price, stock, description, image_path, available_sizes) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdisss", $item_name, $category, $price, $stock, $description, $image_name, $available_sizes);

        if ($stmt->execute()) {
            header("Location: inventory.php?msg=Product Added Successfully");
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - Tina's Jewelry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; background: #f4f7f6; }
        .sidebar { width: 250px; height: 100vh; background: #1a1a1a; color: #fff; position: fixed; padding: 20px; }
        .sidebar h2 { color: #d4af37; text-align: center; margin-bottom: 30px; font-family: 'Playfair Display', serif; }
        .sidebar a { display: block; color: #bbb; padding: 15px; text-decoration: none; border-radius: 5px; margin-bottom: 5px; }
        .sidebar a.active { background: #d4af37; color: #1a1a1a; }
        .main-content { margin-left: 250px; width: calc(100% - 250px); padding: 30px; }
        .form-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); max-width: 700px; margin: auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .save-btn { background: #1a1a1a; color: #d4af37; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-weight: bold; font-size: 16px; margin-top: 10px; }
        .save-btn:hover { background: #d4af37; color: #1a1a1a; }
        .calc-box { background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Tina's Gold</h2>
    <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="view_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
    <a href="inventory.php" class="active"><i class="fas fa-gem"></i> Inventory</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <div class="form-container">
        <h2 style="margin-bottom: 20px; color: #1a1a1a;">Add New Jewelry Item</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Item Name</label>
                <input type="text" name="item_name" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="Saudi Gold">Saudi Gold</option>
                    <option value="Saudi Gold">Earrings</option>
                    <option value="Japan Gold">Japan Gold</option>
                    <option value="Diamonds">Diamonds</option>
                    <option value="Bracelets">Bracelets</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;">
                    <label>Base Price (₱)</label>
                    <input type="number" step="0.01" name="price" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock" required>
                </div>
            </div>

            <div class="calc-box">
                <label style="color: #d4af37;">Size & Price Calculator</label>
                <div style="display: flex; gap: 5px; margin-bottom: 10px;">
                    <input type="text" id="calcSize" placeholder="Size (e.g., 7)">
                    <input type="text" id="calcGrams" placeholder="Grams (e.g., 2.0)">
                    <input type="text" id="calcPPG" placeholder="Price/Gram (e.g., 8500)">
                    <button type="button" onclick="addSize()" style="background:#d4af37; border:none; padding:5px 10px; cursor:pointer; border-radius:5px;">Add</button>
                </div>
                <label>Final Sizes (Copy to DB):</label>
                <input type="text" name="available_sizes" id="finalSizes" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="product_image" accept="image/*" required>
            </div>
            <button type="submit" class="save-btn">Upload to Inventory</button>
        </form>
    </div>
</div>

<script>
    function addSize() {
        let size = document.getElementById('calcSize').value;
        let grams = parseFloat(document.getElementById('calcGrams').value);
        let ppg = parseFloat(document.getElementById('calcPPG').value);
        let total = grams * ppg;

        if (size && grams && ppg) {
            let pair = size + ":" + total;
            let current = document.getElementById('finalSizes').value;
            document.getElementById('finalSizes').value = current ? current + "," + pair : pair;
            
            // Clear inputs
            document.getElementById('calcSize').value = "";
            document.getElementById('calcGrams').value = "";
            document.getElementById('calcPPG').value = "";
        }
    }
</script>
</body>
</html>