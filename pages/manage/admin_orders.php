<?php
// admin_orders.php

session_start(); // Khởi động session để truy cập thông tin người dùng
require_once __DIR__ . '/../../includes/db.php';

// Kiểm tra xem người dùng đã đăng nhập và có vai trò hợp lệ
$hasAccess = isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'employee']);

if (!$hasAccess) {
    // Hiển thị thông báo lỗi
    echo '<p>You do not have permission to access this page.</p>';
    exit;
}

// Xử lý yêu cầu POST để cập nhật tình trạng đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    try {
        $sql = 'UPDATE orders SET status = ? WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->execute([$new_status, $order_id]);

        // Reload page to reflect changes
        header('Location: admin_orders.php');
        exit;
    } catch (PDOException $e) {
        echo "Lỗi: " . $e->getMessage();
    }
}

try {
    // Truy vấn để lấy tất cả đơn hàng và thông tin người dùng liên quan
    $sql = 'SELECT orders.id, orders.user_id, orders.order_date, orders.status, orders.numberphone, orders.address 
            FROM orders
            ORDER BY orders.order_date DESC
            LIMIT 30';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order List</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        .dropdown {
            display: inline-block;
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 100%;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
    </style>
</head>
<body>
<?php include 'header_admin.php'; ?>
<h1>Order List</h1>
<table>
    <thead>
        <tr>
            <th>Order ID</th>
            <th>User ID</th>
            <th>Order Date</th>
            <th>Phone Number</th>
            <th>Address</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($orders) && !empty($orders)) : ?>
            <?php foreach ($orders as $order) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo htmlspecialchars($order['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                    <td><?php echo htmlspecialchars($order['numberphone']); ?></td>
                    <td><?php echo htmlspecialchars($order['address']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($order['status']); ?> <!-- Hiển thị trạng thái hiện tại -->
                        
                    </td>
                    <td>
                    <div class="dropdown">
                            <button>Edit</button>
                            <div class="dropdown-content">
                                <form method="post">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="Chờ xử lý" <?php echo $order['status'] === 'Chờ xử lý' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                        <option value="Đang giao" <?php echo $order['status'] === 'Đang Giao' ? 'selected' : ''; ?>>Đang giao</option>
                                        <option value="Đã giao" <?php echo $order['status'] === 'Đã giao' ? 'selected' : ''; ?>>Đã giao</option>
                                        <option value="Hủy" <?php echo $order['status'] === 'Hủy' ? 'selected' : ''; ?>>Hủy</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="7">No orders found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</body>
</html>
