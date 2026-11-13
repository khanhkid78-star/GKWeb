
<?php
include_once 'database.php';


$sql = "SELECT * FROM Users ORDER BY admin_id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách người dùng</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
    <div class="container header-content">
        <h2>QLNhanSu</h2>
        <a href="admin/index.php" class="btn btn-dark">
            Quản trị
        </a>
    </div>
</header>

<main class="container">
    <section class="intro">
        <p class="subtitle">HỆ THỐNG QUẢN LÝ NHÂN SỰ</p>
        <h1>Danh sách người dùng</h1>
        <p>Thông tin các tài khoản đang có trong hệ thống.</p>
    </section>

    <section class="user-grid">

        <?php if ($result && mysqli_num_rows($result) > 0): ?>

            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                <div class="user-card">

                    <?php if (!empty($row['images'])): ?>
                        <img
                            src="uploads/<?php echo htmlspecialchars($row['images']); ?>"
                            alt="Ảnh người dùng"
                            class="user-image"
                        >
                    <?php else: ?>
                        <div class="user-placeholder">
                            <span>?</span>
                        </div>
                    <?php endif; ?>

                    <div class="user-info">
                        <span class="user-id">
                            USER #<?php echo $row['admin_id']; ?>
                        </span>

                        <h3>
                            <?php echo htmlspecialchars($row['admin_email']); ?>
                        </h3>

                        <p>Tài khoản người dùng</p>
                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p class="empty-message">
                Chưa có dữ liệu người dùng.
            </p>

        <?php endif; ?>

    </section>
</main>

<footer>
    <p>© 2026 QLNhanSu - User Management System</p>
</footer>

</body>
</html>
