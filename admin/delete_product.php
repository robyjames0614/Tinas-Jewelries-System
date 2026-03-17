<?php
session_start();
include('db_conn.php');

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // 1. Kunin muna ang image name bago i-delete ang record
    $query = "SELECT image FROM products WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $image_name = $row['image'];

    // 2. Burahin ang record sa database
    $sql = "DELETE FROM products WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        // 3. Kapag success sa database, burahin din ang file sa folder
        // Siguraduhin na tama ang path papunta sa folder ng images mo
        $path = "../uploads/" . $image_name; 
        
        if (file_exists($path) && !empty($image_name)) {
            unlink($path); // Ito ang nagbubura ng actual file
        }

        echo "<script>alert('Deleted Successfully and File Removed!'); window.location.href='inventory.php';</script>";
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
} else {
    header("Location: inventory.php");
}
?>