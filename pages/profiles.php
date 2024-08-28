<?php

session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Chuyển hướng đến trang đăng nhập
    exit();
}
// Khởi tạo biến $cartItemCount
$cartItemCount = 0;

// Tính tổng số lượng sản phẩm trong giỏ hàng
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
        $cartItemCount += $quantity;
    }
}

// Kiểm tra giỏ hàng có tồn tại không
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$productIds = array_keys($_SESSION['cart']);
if (empty($productIds)) {
    $products = [];
} else {
    $sql = 'SELECT * FROM product WHERE id IN (' . implode(',', array_fill(0, count($productIds), '?')) . ')';
    $stmt = $conn->prepare($sql);
    $stmt->execute($productIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy thông tin người dùng
$userId = $_SESSION['user_id']; // Lấy ID người dùng từ session
$sql = 'SELECT * FROM users WHERE id = ?'; // Thay đổi nếu bảng người dùng có tên khác
$stmt = $conn->prepare($sql);
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Lấy đơn hàng của người dùng
$sql = 'SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC';
$stmt = $conn->prepare($sql);
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <title>Profile</title>
</head>
<body>
    <?php include '../templates/header.php'; ?>
    <h1 class="title">Trang thông tin cá nhân</h1>
    <div class="container">
        <div id="cart-sidebar" class="sidebar">
            <a href="page1.html" class="sidebar-item">
                <img src="../assets/images/icon_11.svg" alt="">
            </a>
            <a href="home.php" class="sidebar-item">
                <img src="../assets/images/icon_21.svg" alt="">
            </a>
            <a href="products.php" class="sidebar-item">
                <img src="../assets/images/icon_31.svg" alt="">
            </a>
            <a href="cart.php" class="sidebar-item">
                <img src="../assets/images/icon_41.svg" alt="">
                <!-- Nút giỏ hàng hiển thị số lượng sản phẩm -->
                <a href="cart.php" class="count-button">
                    <i class="fa fa-shopping-cart"></i> <!-- Biểu tượng giỏ hàng -->
                    <span class="cart-count"><?php echo $cartItemCount; ?></span>
                </a>
            </a>
            <a href="profiles.php" class="sidebar-item">
                <img src="../assets/images/icon_51.svg" alt="">
            </a>
            <a href="logout.php" class="sidebar-item">
                <img src="../assets/images/icon_61.svg" alt="">
            </a>
        </div>
        <div class="profile-container">
            <!-- Thông tin người dùng -->
            <div class="profile table-style">
                <h2>Thông tin cá nhân</h2>
                <?php if ($user) : ?>
                    <p>Tên đăng nhập: <?php echo htmlspecialchars($user['username']); ?></p>
                    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                    <a href="edit_profile.php" class="btn btn-primary">Chỉnh sửa thông tin</a>
                <?php else : ?>
                    <p>User information not found.</p>
                <?php endif; ?>
            </div>

            <!-- Lịch sử đơn hàng -->
            <div class="order-history table-style">
                <h2>Lịch sử đặt hàng</h2>
                <?php if (empty($orders)) : ?>
                    <p>No orders yet.</p>
                <?php else : ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Tổng thanh toán</th>
                                <th>Ngày đặt hàng</th>
                                <th>Trạng thái</th>
                                <th>Thanh toán</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['total_amount']); ?> đ</td>
                                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                                    <td>
                                        <?php
                                        // Kiểm tra giá trị payment_method và hiển thị văn bản tương ứng
                                        if ($order['payment_method'] === 'prepaid') {
                                            echo 'Đã thanh toán';
                                        } elseif ($order['payment_method'] === 'cod') {
                                            echo 'Chưa thanh toán';
                                        } else {
                                            echo 'Chưa xác định'; // Để xử lý các trường hợp không mong muốn
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
