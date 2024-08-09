<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($productId > 0) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = $quantity;
            echo json_encode(['status' => 'success', 'message' => 'Cart updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Product not in cart']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
