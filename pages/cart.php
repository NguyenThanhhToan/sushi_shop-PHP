<?php
// cart.php

session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Chuyển hướng đến trang đăng nhập
    exit();
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

// Lấy đơn hàng của người dùng
$userId = $_SESSION['user_id']; // Lấy ID người dùng từ session
$sql = 'SELECT * FROM orders WHERE user_id = ?';
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
    <title>Cart</title>
</head>
<body>
    <?php include '../templates/header.php'; ?>
    <h1 class="title">Your Cart</h1>
    <div class="container">
        <div id="cart-sidebar" class="sidebar">
            <a href="about.php" class="sidebar-item">
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
            </a>
            <a href="profiles.php" class="sidebar-item">
                <img src="../assets/images/icon_51.svg" alt="">
            </a>
            <a href="logout.php" class="sidebar-item">
                <img src="../assets/images/icon_61.svg" alt="">
            </a>
        </div>
        <div class="cart-content">
            <div class="cart-table">
                <?php if (empty($products)): ?>
                    <p>Your cart is empty.</p>
                <?php else: ?>
                    <div class="product-list">
                        <h2>Selected Products</h2>
                        <?php foreach ($products as $product): ?>
                            <div class="product-item">
                                <img class="cart-image" src="../<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="item-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="quantity-buttons">
                                    <button class="quantity-button">-</button>
                                    <input type="number" class="quantity-input" value="<?php echo htmlspecialchars($_SESSION['cart'][$product['id']]); ?>" min="1" data-price="<?php echo htmlspecialchars($product['price']); ?>" data-product-id="<?php echo htmlspecialchars($product['id']); ?>">
                                    <button class="quantity-button">+</button>
                                </div>
                                <button class="delete-button">X</button>
                                <div class="item-price" data-price="<?php echo htmlspecialchars($product['price'] * $_SESSION['cart'][$product['id']]); ?>">
                                    $<?php echo htmlspecialchars($product['price'] * $_SESSION['cart'][$product['id']]); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="total">
                    <h2>Total Amount</h2>
                    <p id="total-amount">$<?php
                        $total = 0;
                        foreach ($products as $product) {
                            $total += $product['price'] * $_SESSION['cart'][$product['id']];
                        }
                        echo htmlspecialchars($total);
                    ?></p>
                    <form id="confirm-order-form" method="POST">
                        <input type="hidden" name="action" value="confirm_order">
                        <input type="hidden" name="total_amount" id="hidden-total-amount" value="<?php echo htmlspecialchars($total); ?>">
                        <button type="button" id="confirm-order" class="apply-button">Confirm Order</button>
                    </form>
                </div>
                <div class="discount-code">
                    <form id="discount-code-form" method="POST">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required><br><br>
                        
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" placeholder="Enter your address" required><br><br>

                        <input type="hidden" name="action" value="confirm_order">
                        <input type="hidden" name="total_amount" id="hidden-total-amount" value="<?php echo htmlspecialchars($total); ?>">
                    </form>
                </div>
                <script src="../assets/js/cart.js"></script>

</body>
</html>
