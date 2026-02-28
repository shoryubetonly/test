<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['fullname'] = $row['fullname'];
        header("Location: index.php");
        exit();
    } else {
        echo "<script>alert('ชื่อผู้ใช้ หรือ รหัสผ่านไม่ถูกต้อง!'); window.location='login.php';</script>";
    }
} else {
    header("Location: login.php");
    exit();
}
?>