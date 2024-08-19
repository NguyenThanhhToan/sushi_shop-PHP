<?php
// admin_products.php

session_start(); // Khởi tạo session
require_once __DIR__ . '/../../includes/db.php';

$hasAccess = isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'employee']);

if (!$hasAccess) {
    // Hiển thị thông báo lỗi
    echo '<p>You do not have permission to access this page.</p>';
    exit;
}

// Xử lý yêu cầu thêm, sửa, xóa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        // Thêm sản phẩm mới
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category_id = $_POST['category_id'];
        
        // Xử lý upload ảnh
        $image_path = 'assets/images/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../../' . $image_path);

        try {
            $sql = 'INSERT INTO product (name, description, price, category_id, image_path, is_active) VALUES (?, ?, ?, ?, ?, 1)';
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $description, $price, $category_id, $image_path]);
            header('Location: admin_products.php');
            exit;
        } catch (PDOException $e) {
            echo "Lỗi: " . $e->getMessage();
        }
    } elseif (isset($_POST['edit_product'])) {
        // Sửa thông tin sản phẩm
        $product_id = $_POST['product_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category_id = $_POST['category_id'];
        $image_path = $_POST['existing_image_path'];

        // Xử lý upload ảnh nếu có
        if (!empty($_FILES['image']['name'])) {
            $image_path = 'assets/images/' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../../' . $image_path);
        }

        try {
            $sql = 'UPDATE product SET name = ?, description = ?, price = ?, category_id = ?, image_path = ? WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $description, $price, $category_id, $image_path, $product_id]);
            header('Location: admin_products.php');
            exit;
        } catch (PDOException $e) {
            echo "Lỗi: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_product'])) {
        // Cập nhật is_active thành 0 thay vì xóa sản phẩm
        $product_id = $_POST['product_id'];

        try {
            $sql = 'UPDATE product SET is_active = 0 WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->execute([$product_id]);
            header('Location: admin_products.php');
            exit;
        } catch (PDOException $e) {
            echo "Lỗi: " . $e->getMessage();
        }
    }
}

try {
    // Truy vấn để lấy tất cả sản phẩm có is_active là 1
    $sql = 'SELECT * FROM product WHERE is_active = 1';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        /* Các kiểu CSS cơ bản */
        body {
            font-family: Arial, sans-serif;
        }
        h1 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .form-container {
            margin: 20px 0;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            color: white;
            font-weight: bold;
        }
        .btn-edit {
            background-color: #4CAF50; /* Màu xanh lá */
        }
        .btn-delete {
            background-color: #f44336; /* Màu đỏ */
        }
        .btn:hover {
            opacity: 0.8;
        }
        .form-container label {
            display: block;
            margin: 5px 0;
        }
        .form-container input, .form-container textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-container button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <?php include 'header_admin.php'; ?>
    <h1>Product Admin</h1>
    
    <!-- Danh sách sản phẩm -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Category ID</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($products) && !empty($products)) : ?>
                <?php foreach ($products as $product) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['price']); ?></td>
                        <td><?php echo htmlspecialchars($product['category_id']); ?></td>
                        <td><img src="../../<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="100"></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">Edit</a>
                                <button type="submit" name="delete_product" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6">No products found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Thêm sản phẩm -->
    <div class="form-container">
        <h2>Add Product</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="add_product">
            <label for="add_name">Name:</label>
            <input type="text" id="add_name" name="name" required>
            <label for="add_description">Description:</label>
            <textarea id="add_description" name="description" required></textarea>
            <label for="add_price">Price:</label>
            <input type="number" id="add_price" name="price" required>
            <label for="add_category_id">Category ID:</label>
            <input type="number" id="add_category_id" name="category_id" required>
            <label for="add_image">Image:</label>
            <input type="file" id="add_image" name="image" required>
            <button type="submit">Add Product</button>
        </form>
    </div>
</body>
</html>
