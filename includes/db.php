<?php
// db.php

// Thay đổi các thông số kết nối dựa trên cấu hình của bạn
$host = 'localhost';
$dbName = 'Sushi_shop';
$username = 'root';
$password = '';

// Kết nối đến cơ sở dữ liệu
$conn = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>