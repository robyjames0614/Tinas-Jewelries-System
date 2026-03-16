<?php
session_start();
include('admin/db_conn.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kinukuha ang data mula sa form
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Insert query sa bagong inquiries table
    $sql = "INSERT INTO inquiries (fullname, email, message) VALUES ('$fullname', '$email', '$message')";

    if (mysqli_query($conn, $sql)) {
        // Kapag success, mag-pop up at babalik sa contact page
        echo "<script>
                alert('Salamat! Na-receive namin ang iyong mensahe.');
                window.location.href='contact.html';
              </script>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
?>