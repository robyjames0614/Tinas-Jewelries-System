<?php
include('admin/db_conn.php'); // Siguraduhin ang tamang path sa connection file

if (isset($_POST['register'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 
    $role = 'client'; // Default role para sa lahat ng mag-regisiter sa website

    // 1. I-check muna kung existing na ang username
    $check_user = "SELECT * FROM users WHERE username='$username'";
    $run_check = mysqli_query($conn, $check_user);

    if (mysqli_num_rows($run_check) > 0) {
        echo "<script>alert('Username already taken! Choose another one.'); window.history.back();</script>";
    } else {
        // 2. I-hash ang password (BCRYPT) - ito ang pinaka-importante
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. I-save sa database
        $query = "INSERT INTO users (fullname, email, username, password, role) 
                  VALUES ('$fullname', '$email', '$username', '$hashed_password', '$role')";

        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Registration Successful! Please login.'); window.location.href='login.html';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

