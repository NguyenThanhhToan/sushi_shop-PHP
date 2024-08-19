<?php
// edit_product.php

session_start();
require_once __DIR__ . '/../../includes/db.php';

$hasAccess = isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'employee']);

if (!$hasAccess) {
    echo '<p>You do not have permission to access this page.</p>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $image_path = $_POST['existing_image_path'];

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
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;

try {
    $sql = 'SELECT * FROM product WHERE id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

$categories = [];
try {
    $sql = 'SELECT * FROM category';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
        }
        .form-container label {
            margin: 10px 0 5px;
            font-weight: bold;
        }
        .form-container input, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-container textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-container img {
            display: block;
            margin: 10px 0;
            max-width: 100%;
            border-radius: 4px;
        }
        .form-container button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-container button:hover {
            background-color: #45a049;
        }
        .form-container p {
            text-align: center;
            color: #f44336;
        }
    </style>
</head>
<body>
<?php include 'header_admin.php'; ?>
    <h1>Edit Product</h1>
    <div class="form-container">
        <?php if ($product) : ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="edit_product">
                <input type="hidden" id="edit_product_id" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                <input type="hidden" id="existing_image_path" name="existing_image_path" value="<?php echo htmlspecialchars($product['image_path']); ?>">
                <label for="edit_name">Name:</label>
                <input type="text" id="edit_name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                <label for="edit_description">Description:</label>
                <textarea id="edit_description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                <label for="edit_price">Price:</label>
                <input type="number" id="edit_price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                <label for="edit_category_id">Category ID:</label>
                <input type="number" id="edit_category_id" name="category_id" value="<?php echo htmlspecialchars($product['category_id']); ?>" required>
                <label for="edit_image">Image:</label>
                <input type="file" id="edit_image" name="image">
                <img src="../../<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="100">
                <button type="submit">Update Product</button>
            </form>
        <?php else : ?>
            <p>Product not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
