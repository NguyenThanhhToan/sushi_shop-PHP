<?php
session_start();
require_once '../includes/db.php';

// Xử lý thêm sản phẩm vào giỏ hàng
if (isset($_GET['action']) && $_GET['action'] === 'add_to_cart' && isset($_GET['id'])) {
    $productId = $_GET['id'];
    $returnUrl = isset($_GET['return_url']) ? $_GET['return_url'] : 'products.php';

    // Nếu sản phẩm đã có trong giỏ hàng, tăng số lượng lên 1
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]++;
    } else {
        // Nếu sản phẩm chưa có trong giỏ hàng, thêm sản phẩm với số lượng là 1
        $_SESSION['cart'][$productId] = 1;
    }

    // Chuyển hướng lại trang trước đó
    header('Location: ' . $returnUrl);
    exit;
}

// Lấy danh sách các danh mục
$sql = 'SELECT * FROM category';
$stmt = $conn->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý khi người dùng chọn danh mục
$selectedCategoryId = isset($_GET['category_id']) ? $_GET['category_id'] : 1; // Mặc định là danh mục có id = 1

// Lấy sản phẩm theo danh mục đã chọn và có is_active = 1
$sql = 'SELECT * FROM product WHERE category_id = ? AND is_active = 1';
$stmt = $conn->prepare($sql);
$stmt->execute([$selectedCategoryId]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy tên danh mục để hiển thị tiêu đề
$sql = 'SELECT name FROM category WHERE id = ?';
$stmt = $conn->prepare($sql);
$stmt->execute([$selectedCategoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Product List</title>
</head>
<body>
    <?php include '../templates/header.php'; ?>
    <h1 class="title"><?php echo htmlspecialchars($category['name']); ?></h1>
    <form method="GET" action="products.php" class="category-selector">
        <select id="category_id" name="category_id" onchange="this.form.submit()">
            <?php foreach ($categories as $cat) : ?>
                <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo $cat['id'] == $selectedCategoryId ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <div class="container">
        <div class="sidebar">
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
        <div class="image-list">
            <?php foreach ($products as $product) : ?>
                <figure>
                    <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                        <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="170">
                    </a>
                    <figcaption>
                        <div class="overlay">
                            <b><?php echo htmlspecialchars($product['name']); ?></b><br>
                            <span class="price"><?php echo htmlspecialchars($product['price']); ?></span>
                        </div>
                    </figcaption>
                    <a class="add-to-cart" href="products.php?action=add_to_cart&id=<?php echo $product['id']; ?>&return_url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">
                        <strong>Add to Cart</strong>
                    </a>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="../assets/js/product.js"></script>
</body>
</html>
