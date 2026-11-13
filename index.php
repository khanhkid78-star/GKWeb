
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
    <link rel="stylesheet" href="style.css">
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
        <h1>Danh sách người dùng</h1>
        <p>Thông tin các tài khoản đang có trong hệ thống.</p>
    </section>

    <section class="admin-list">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td data-label="ID"><?php echo (int)$row['admin_id']; ?></td>
                            <td data-label="Ảnh">
                                <?php if (!empty($row['images'])): ?>
                                    <img
                                        class="table-image"
                                        src="uploads/<?php echo htmlspecialchars($row['images']); ?>"
                                        alt=""
                                    >
                                <?php else: ?>
                                    <span class="table-image table-fallback">?</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Email"><?php echo htmlspecialchars($row['admin_email']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Chưa có dữ liệu người dùng.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<footer>
    <p>© 2026 QLNhanSu - User Management System</p>
</footer>

</body>
</html>
