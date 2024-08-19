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
$selectedCategoryId = isset($_GET['category_id']) ? $_GET['category_id'] : 'all';

// Xử lý khi người dùng tìm kiếm sản phẩm
$searchKeyword = isset($_GET['search_keyword']) ? $_GET['search_keyword'] : '';

// Số sản phẩm hiển thị trên mỗi trang
$limit = 8;

// Trang hiện tại, mặc định là 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Tính toán vị trí bắt đầu
$start = ($page - 1) * $limit;

if (!empty($searchKeyword)) {
    // Tìm kiếm sản phẩm theo từ khóa
    $sql = 'SELECT * FROM product WHERE is_active = 1 AND name LIKE :search_keyword LIMIT :start, :limit';
    $stmt = $conn->prepare($sql);
    $searchParam = '%' . $searchKeyword . '%';
    $stmt->bindParam(':search_keyword', $searchParam, PDO::PARAM_STR);
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tính tổng số sản phẩm tìm được
    $sql = 'SELECT COUNT(*) FROM product WHERE is_active = 1 AND name LIKE :search_keyword';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':search_keyword', $searchParam, PDO::PARAM_STR);
    $stmt->execute();
    $total_products = $stmt->fetchColumn();

    // Hiển thị tiêu đề kết quả tìm kiếm
    $category['name'] = '"' . htmlspecialchars($searchKeyword) . '"';
} elseif ($selectedCategoryId === 'all') {
    // Lấy tất cả sản phẩm có is_active = 1 với giới hạn
    $sql = 'SELECT * FROM product WHERE is_active = 1 LIMIT :start, :limit';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy tổng số sản phẩm để tính tổng số trang
    $sql = 'SELECT COUNT(*) FROM product WHERE is_active = 1';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $total_products = $stmt->fetchColumn();

    // Hiển thị tiêu đề "All Products"
    $category['name'] = 'All Products';
} else {
    // Lấy sản phẩm theo danh mục đã chọn và có is_active = 1 với giới hạn
    $sql = 'SELECT * FROM product WHERE category_id = :category_id AND is_active = 1 LIMIT :start, :limit';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':category_id', $selectedCategoryId, PDO::PARAM_INT);
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy tổng số sản phẩm để tính tổng số trang
    $sql = 'SELECT COUNT(*) FROM product WHERE category_id = :category_id AND is_active = 1';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':category_id', $selectedCategoryId, PDO::PARAM_INT);
    $stmt->execute();
    $total_products = $stmt->fetchColumn();

    // Lấy tên danh mục để hiển thị tiêu đề
    $sql = 'SELECT name FROM category WHERE id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->execute([$selectedCategoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Tính toán tổng số trang
$total_pages = ceil($total_products / $limit);
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
    <!-- Thanh tìm kiếm sản phẩm -->
    <form method="GET" action="products.php" class="search-form">
        <input type="text" name="search_keyword" placeholder="Tên sản phẩm ..." value="<?php echo htmlspecialchars($searchKeyword); ?>">
        <button type="submit">Tìm kiếm</button>
        <div class="select-wrapper">
            <form method="GET" action="products.php" class="category-selector">
                <select id="category_id" name="category_id" onchange="this.form.submit()">
                    <option value="all" <?php echo $selectedCategoryId === 'all' ? 'selected' : ''; ?>>All</option>
                    <?php foreach ($categories as $cat) : ?>
                        <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo $cat['id'] == $selectedCategoryId ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

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
                <?php if ($product['is_active'] == 1): ?>
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
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
        <!-- Phân trang -->
        <div class="pagination-container">
            <ul class="pagination">
                <!-- Nút trang trước -->
                <?php if ($page > 1): ?>
                    <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">&laquo; Trang trước</a></li>
                <?php endif; ?>

                <!-- Liệt kê số trang -->
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="<?php echo $i == $page ? 'active' : ''; ?>">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Nút trang kế tiếp -->
                <?php if ($page < $total_pages): ?>
                    <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Trang tiếp &raquo;</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>

