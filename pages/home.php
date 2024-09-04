<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/home.css">
    <title>Home</title>
    <style>
        
    </style>
</head>

<body>
<nav>
        <ul>
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Các liên kết cho tất cả người dùng đã đăng nhập -->
                <li><a href="products.php">Xin chào!</a></li>
                <!-- Các liên kết dành cho admin và employee -->
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'employee'])): ?>
                    <li><a href="manage/admin_products.php">Quản lý</a></li>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Các liên kết cho người dùng chưa đăng nhập -->
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <h2 id="welcome">Welcome to <strong>Sushi</strong> Restaurant</h2>
    <h2 id="intro">People eat with their eyes and Sushi creates an easy way for customers to order when they can see beautiful photos of your food</h2>
    <div class="button-container">
        <a href="about.php" id="about-item">About</a>
        <a href="products.php" id="menu-item">Menu</a>
    </div>
</body>

</html>
