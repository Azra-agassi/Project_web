<?php
    include 'koneksi.php';
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $name = $_POST['name'];

        $query = "INSERT INTO users (username, password, name) VALUES ('$username', '$password', '$name')";
        $result = mysqli_query($connect, $query);
        if ($result) {
            header('Location: login.html');
            exit;
        } else {
            echo "Terjadi kesalahan: " . mysqli_error($connect);
        }
    }
?>