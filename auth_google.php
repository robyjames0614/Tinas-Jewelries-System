<?php
session_start();
include('db_conn.php'); // Siguraduhin na ito ang pangalan ng file mo na may database connection

// Kunin ang data mula sa Javascript (fetch)
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { exit; }

$google_id = $data['google_id'];
$email = $data['email'];
$fullname = $data['fullname'];

// 1. I-check kung existing na ang user sa database
$query = "SELECT * FROM users WHERE google_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $google_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Kung existing na, i-login siya
    $user = $result->fetch_assoc();
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $role = $user['role'];
} else {
    // Kung wala pa, i-insert siya bilang bagong user (default role: client)
    $role = 'client';
    $insert = "INSERT INTO users (username, email, google_id, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("ssss", $fullname, $email, $google_id, $role);
    $stmt->execute();
    
    $_SESSION['admin_id'] = $conn->insert_id;
    $_SESSION['username'] = $fullname;
    $_SESSION['role'] = $role;
}

echo json_encode(['success' => true, 'role' => $role]);
?>