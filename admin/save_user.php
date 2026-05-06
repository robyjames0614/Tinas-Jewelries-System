<?php
include('db_conn.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Ito ang nag-e-encrypt
    $role = $_POST['role'];

    $sql = "INSERT INTO users (fullname, username, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $fullname, $username, $password, $role);

    if ($stmt->execute()) {
        header("Location: users.php?success=user_added");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>