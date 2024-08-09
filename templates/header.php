<?php
// header.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/header.css">
    <title>Header</title>
</head>
<body>
    <nav>
        <ul>
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Các liên kết cho tất cả người dùng đã đăng nhập -->
                
                
                <!-- Các liên kết dành cho admin và employee -->
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'employee'])): ?>
                    <li><a href="manage/admin_products.php">Admin Products</a></li>
                    <li><a href="manage/admin_orders.php">Admin Orders</a></li>
                <?php endif; ?>
                
                <!-- Các liên kết chỉ dành cho admin -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="manage/admin_users.php">Admin Users</a></li>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Các liên kết cho người dùng chưa đăng nhập -->
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</body>
</html>
