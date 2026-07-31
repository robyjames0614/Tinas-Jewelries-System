<?php
session_start();
include('../db_conn.php');

// 1. Kunin ang ID mula sa URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM products WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        die("Product not found!");
    }
} else {
    header("Location: inventory.php");
    exit();
}

// 2. Logic kapag pinindot ang Update button
if (isset($_POST['update_product'])) {
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $available_sizes = mysqli_real_escape_string($conn, $_POST['available_sizes']);

    $update_query = "UPDATE products SET 
                        item_name = '$item_name', 
                        category = '$category', 
                        price = '$price', 
                        stock = '$stock', 
                        description = '$description',
                        available_sizes = '$available_sizes' 
                        WHERE id = '$id'";

    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Updated Successfully!'); window.location.href='inventory.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product - Tina's Gold</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7f6; padding: 40px; }
        .form-container { max-width: 500px; background: white; padding: 30px; border-radius: 10px; margin: auto; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        input, select, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        .save-btn { background: #d4af37; color: #1a1a1a; border: none; padding: 12px; width: 100%; cursor: pointer; font-weight: bold; }
        .size-label { font-weight: bold; color: #d4af37; }
        .calc-box { background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Product</h2>
    <form method="POST">
        <label>Item Name:</label>
        <input type="text" name="item_name" value="<?php echo htmlspecialchars($row['item_name']); ?>" required>
        
        <label>Category:</label>
        <select name="category">
            <option value="Saudi Gold" <?php if($row['category'] == 'Saudi Gold') echo 'selected'; ?>>Saudi Gold</option>
            <option value="Japan Gold" <?php if($row['category'] == 'Japan Gold') echo 'selected'; ?>>Japan Gold</option>
            <option value="Diamonds" <?php if($row['category'] == 'Diamonds') echo 'selected'; ?>>Diamonds</option>
            <option value="Bracelets" <?php if($row['category'] == 'Bracelets') echo 'selected'; ?>>Bracelets</option>
            <option value="Earrings" <?php if($row['category'] == 'Earrings') echo 'selected'; ?>>Earrings</option>
        </select>

        <label>Price (₱):</label>
        <input type="number" step="0.01" name="price" value="<?php echo $row['price']; ?>" required>

        <label>Stock (pcs):</label>
        <input type="number" name="stock" value="<?php echo $row['stock']; ?>" required>

        <!-- Calculator Integration -->
        <div class="calc-box">
            <label style="font-size: 14px; font-weight: 600;">Size & Price Calculator:</label>
            <div style="display: flex; gap: 5px; margin-bottom: 10px;">
                <input type="text" id="calcSize" placeholder="Size">
                <input type="text" id="calcGrams" placeholder="Grams">
                <input type="text" id="calcPPG" placeholder="Price/Gram">
                <button type="button" onclick="addSize()" style="background:#1a1a1a; color:#fff; border:none; padding:5px 10px; cursor:pointer; border-radius:5px;">Add</button>
            </div>
            <small style="color: #666;">Ilagay ang info at i-click ang Add para ma-update ang field sa ibaba.</small>
        </div>

        <label class="size-label">Sizes & Prices (Format: size:price,size:price):</label>
        <input type="text" name="available_sizes" id="available_sizes" value="<?php echo htmlspecialchars($row['available_sizes']); ?>" placeholder="5:1200,6:1350" required>

        <label>Description:</label>
        <textarea name="description" rows="4"><?php echo htmlspecialchars($row['description']); ?></textarea>

        <button type="submit" name="update_product" class="save-btn">UPDATE PRODUCT</button>
        <a href="inventory.php" style="display:block; text-align:center; margin-top:10px; color:#666; text-decoration:none;">Cancel</a>
    </form>
</div>

<script>
    function addSize() {
        let size = document.getElementById('calcSize').value;
        let grams = parseFloat(document.getElementById('calcGrams').value);
        let ppg = parseFloat(document.getElementById('calcPPG').value);
        let total = grams * ppg;

        if (size && grams && ppg) {
            let pair = size + ":" + total;
            let sizeInput = document.getElementById('available_sizes');
            let current = sizeInput.value;
            
            // I-append ang bagong pair. Kung may laman na, lagyan ng comma.
            sizeInput.value = current ? current + "," + pair : pair;
            
            // Clear fields
            document.getElementById('calcSize').value = "";
            document.getElementById('calcGrams').value = "";
            document.getElementById('calcPPG').value = "";
        } else {
            alert("Pakipunan lahat ng fields sa calculator!");
        }
    }
</script>

</body>
</html>