<?php
// confirm_order.php

session_start();
require_once '../includes/db.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to confirm the order.']);
    exit();
}

// Kiểm tra xem giỏ hàng có tồn tại và không rỗng không
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
//    echo json_encode(['status' => 'error', 'message' => 'Your cart is empty.']);
    exit();
}

// Kiểm tra và lấy tổng số tiền từ POST
if (!isset($_POST['total_amount']) || !isset($_POST['phone']) || !isset($_POST['address'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit();
}
$totalAmount = $_POST['total_amount'];
$phone = $_POST['phone'];
$address = $_POST['address'];

if (empty($phone) || empty($address)) {
    echo json_encode(['status' => 'error', 'message' => 'Phone number and address are required.']);
    exit();
}

try {
    $conn->beginTransaction(); // Bắt đầu giao dịch

    // Tạo đơn hàng mới
    $user_id = $_SESSION['user_id'];
    $sql = 'INSERT INTO orders (user_id, total_amount, numberphone, address, order_date) VALUES (?, ?, ?, ?, NOW())';
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $totalAmount, $phone, $address]);
    $order_id = $conn->lastInsertId(); // Lấy ID của đơn hàng vừa tạo

    // Thêm các mục vào đơn hàng
    $sql = 'INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)';
    $stmt = $conn->prepare($sql);

    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        // Lấy thông tin sản phẩm
        $sqlProduct = 'SELECT price FROM product WHERE id = ?';
        $stmtProduct = $conn->prepare($sqlProduct);
        $stmtProduct->execute([$product_id]);
        $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $price = $product['price'];
            $stmt->execute([$order_id, $product_id, $quantity, $price]);
        } else {
            // Nếu sản phẩm không tồn tại trong cơ sở dữ liệu, báo lỗi và hủy giao dịch
            $conn->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Product not found: ' . $product_id]);
            exit();
        }
    }

    // Xóa giỏ hàng sau khi đặt hàng
    unset($_SESSION['cart']);

    $conn->commit(); // Cam kết giao dịch

    echo json_encode(['status' => 'success', 'message' => 'Order confirmed successfully!']);
} catch (PDOException $e) {
    $conn->rollBack(); // Hủy giao dịch nếu có lỗi
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
