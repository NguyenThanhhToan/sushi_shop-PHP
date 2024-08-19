<?php
// product_detail.php

session_start();
require_once '../includes/db.php';

if (!isset($_GET['id'])) {
    echo "Invalid product ID.";
    exit;
}

// Xử lý thêm sản phẩm vào giỏ hàng
if (isset($_GET['action']) && $_GET['action'] === 'add_to_cart' && isset($_GET['id'])) {
    $productId = $_GET['id'];
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
    $returnUrl = isset($_GET['return_url']) ? $_GET['return_url'] : 'products.php';

    // Nếu sản phẩm đã có trong giỏ hàng, tăng số lượng lên 1
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        // Nếu sản phẩm chưa có trong giỏ hàng, thêm sản phẩm với số lượng là quantity
        $_SESSION['cart'][$productId] = $quantity;
    }

    // Chuyển hướng lại trang trước đó
    header('Location: ' . $returnUrl);
    exit;
}

$productId = $_GET['id'];

// Lấy thông tin chi tiết sản phẩm
$sql = 'SELECT * FROM product WHERE id = ?';
$stmt = $conn->prepare($sql);
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Product not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/product_detail.css">
    <title><?php echo htmlspecialchars($product['name']); ?> - Details</title>
</head>

<body>
    <?php include '../templates/header.php'; ?>
    <h1 class="title"><?php echo htmlspecialchars($product['name']); ?></h1>
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
    
        <div class="product-detail">
            <div class="image-and-price">
                <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                <h2 class="product-price"><strong>Price: <?php echo htmlspecialchars($product['price']); ?></strong></h2>
                <div class="quantity-buttons">
                    <button id="decrease-quantity" class="quantity-button">-</button>
                    <input type="number" id="quantity-input" class="quantity-input" value="1" min="1">
                    <button id="increase-quantity" class="quantity-button">+</button>
                </div>
                <a id="addtocart" class="addtocart" href="#" data-product-id="<?php echo $product['id']; ?>" data-return-url="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>"><strong>Add to Cart</strong></a>
            </div>
            <div class="product-description">
                <h2><strong>Mô tả sản phẩm:</strong></h2>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
            </div>
        </div>
    <script src="../assets/js/product_detail.js"></script>
</body>

</html>
