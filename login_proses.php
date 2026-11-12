<?php
session_start();
include 'koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

$query = mysqli_query($connect, "SELECT * FROM users WHERE username='$username' AND password='$password'");

$cek = mysqli_num_rows($query);

    if($cek > 0) {
        $_SESSION['username'] = "$username";
        $_SESSION['status'] = "login";
        header("location:katalog1.php");
    } else {
        header("location:login.html?error=1");
    }
?>