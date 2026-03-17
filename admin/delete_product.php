<?php
session_start();
include('db_conn.php');

if (isset($_GET['id'])) {
    // Nagdagdag lang ako ng 'mysqli_real_escape_string' para safe sa SQL injection
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Burahin ang record base sa ID
    $sql = "DELETE FROM products WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Deleted Successfully!'); window.location.href='inventory.php';</script>";
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
} else {
    header("Location: inventory.php");
}
?>