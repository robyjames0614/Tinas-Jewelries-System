<?php
session_start();
include('db_conn.php');

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

    // Update query gamit ang tamang columns mo
    $update_query = "UPDATE products SET 
                    item_name = '$item_name', 
                    category = '$category', 
                    price = '$price', 
                    stock = '$stock', 
                    description = '$description' 
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
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7f6; padding: 40px; }
        .form-container { max-width: 500px; background: white; padding: 30px; border-radius: 10px; margin: auto; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        input, select, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        .save-btn { background: #d4af37; color: #1a1a1a; border: none; padding: 12px; width: 100%; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Product</h2>
    <form method="POST">
        <label>Item Name:</label>
        <input type="text" name="item_name" value="<?php echo $row['item_name']; ?>" required>
        
        <label>Category:</label>
        <select name="category">
            <option value="Necklace" <?php if($row['category'] == 'Necklace') echo 'selected'; ?>>Necklace</option>
            <option value="Ring" <?php if($row['category'] == 'Ring') echo 'selected'; ?>>Ring</option>
            <option value="Bracelet" <?php if($row['category'] == 'Bracelet') echo 'selected'; ?>>Bracelet</option>
        </select>

        <label>Price (₱):</label>
        <input type="number" name="price" value="<?php echo $row['price']; ?>" required>

        <label>Stock (pcs):</label>
        <input type="number" name="stock" value="<?php echo $row['stock']; ?>" required>

        <label>Description:</label>
        <textarea name="description" rows="4"><?php echo $row['description']; ?></textarea>

        <button type="submit" name="update_product" class="save-btn">UPDATE PRODUCT</button>
        <a href="inventory.php" style="display:block; text-align:center; margin-top:10px; color:#666;">Cancel</a>
    </form>
</div>

</body>
</html>