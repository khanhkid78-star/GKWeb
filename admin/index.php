
<?php
include_once '../database.php';


function uploadImage() {
    if (!isset($_FILES['images']) ||
        $_FILES['images']['error'] == UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES['images']['error'] != UPLOAD_ERR_OK) {
        return null;
    }

    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['images']['tmp_name']);
    finfo_close($finfo);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        return null;
    }

    
    $filename = uniqid() . '.' . $allowed[$mime];

    $destination = '../uploads/' . $filename;

    if (move_uploaded_file(
        $_FILES['images']['tmp_name'],
        $destination
    )) {
        return $filename;
    }

    return null;
}

$message = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $action = $_POST['action'] ?? '';


    if ($action == 'delete') {

        $id = (int)$_POST['admin_id'];

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM Users WHERE admin_id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        header("Location: index.php");
        exit;
    }

   
    if ($action == 'add') {

        $email = trim($_POST['admin_email']);
        $password = $_POST['admin_password'];
        $image = uploadImage();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Email không hợp lệ!";
        } elseif (empty($password)) {
            $message = "Vui lòng nhập mật khẩu!";
        } else {

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO Users
                (admin_email, admin_password, images)
                VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $email,
                $password,
                $image
            );

            mysqli_stmt_execute($stmt);

            header("Location: index.php");
            exit;
        }
    }

    if ($action == 'update') {

        $id = (int)$_POST['admin_id'];
        $email = trim($_POST['admin_email']);
        $password = $_POST['admin_password'];

        $image = uploadImage();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Email không hợp lệ!";
        } else {

            if ($image !== null) {
              
                if (!empty($password)) {


                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE Users
                        SET admin_email = ?,
                            admin_password = ?,
                            images = ?
                        WHERE admin_id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "sssi",
                        $email,
                        $password,
                        $image,
                        $id
                    );

                } else {

                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE Users
                        SET admin_email = ?, images = ?
                        WHERE admin_id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssi",
                        $email,
                        $image,
                        $id
                    );
                }

            } else {

                // Không đổi ảnh
                if (!empty($password)) {


                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE Users
                        SET admin_email = ?,
                            admin_password = ?
                        WHERE admin_id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssi",
                        $email,
                        $password,
                        $id
                    );

                } else {

                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE Users
                        SET admin_email = ?
                        WHERE admin_id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "si",
                        $email,
                        $id
                    );
                }
            }

            mysqli_stmt_execute($stmt);

            header("Location: index.php");
            exit;
        }
    }
}


$editUser = null;

if (isset($_GET['edit'])) {

    $id = (int)$_GET['edit'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM Users WHERE admin_id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $resultEdit = mysqli_stmt_get_result($stmt);
    $editUser = mysqli_fetch_assoc($resultEdit);
}


$result = mysqli_query(
    $conn,
    "SELECT * FROM Users ORDER BY admin_id DESC"
);
?>



<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Users</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<header class="header">
    <div class="container header-content">
        <h2>Quản trị Users</h2>
        <a href="../index.php" class="btn btn-dark">
            Homepage
        </a>
    </div>
</header>

<main class="container">

    <section class="intro">
        <p class="subtitle">ADMIN PANEL</p>
    </section>

    <?php if ($message != ''): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if (!$editUser): ?>
        <div class="toolbar">
            <button type="button" class="btn btn-dark" id="btn-show-add">
                Thêm người dùng
            </button>
        </div>
    <?php endif; ?>

    <section class="admin-form <?php echo $editUser ? '' : 'is-hidden'; ?>" id="user-form">
        <h2><?php echo $editUser ? 'Cập nhật người dùng' : 'Thêm người dùng mới'; ?></h2>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $editUser ? 'update' : 'add'; ?>">

            <?php if ($editUser): ?>
                <input type="hidden" name="admin_id" value="<?php echo (int)$editUser['admin_id']; ?>">
            <?php endif; ?>

            <label>Email</label>
            <input type="email" name="admin_email" placeholder="Nhập email"
                   value="<?php echo htmlspecialchars($editUser['admin_email'] ?? ''); ?>" required>

            <label>Mật khẩu</label>
            <input type="password"
                name="admin_password"
                placeholder="<?php echo $editUser ? 'Nhập mật khẩu' : 'Nhập mật khẩu'; ?>"
                value="<?php echo htmlspecialchars($editUser['admin_password'] ?? ''); ?>"
                <?php echo $editUser ? '' : 'required'; ?>>

            <label>Ảnh đại diện</label>
            <input type="file" name="images" accept="image/jpeg,image/png,image/webp">
            <p class="preview-label is-hidden" id="new-photo-label">Ảnh đã chọn</p>
            <img class="current-photo is-hidden" id="new-photo" alt="Ảnh đã chọn">

            <?php if ($editUser && !empty($editUser['images'])): ?>
                <p style="font-size:13px;color:#6b7280;">Ảnh hiện tại</p>
                <img class="current-photo"
                     src="../uploads/<?php echo htmlspecialchars($editUser['images']); ?>"
                     alt="Ảnh hiện tại">
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-dark">
                    <?php echo $editUser ? 'Cập nhật' : 'Thêm người dùng'; ?>
                </button>
                <?php if ($editUser): ?>
                    <a href="index.php" class="btn btn-cancel">Hủy</a>
                <?php else: ?>
                    <button type="button" class="btn btn-cancel" id="btn-hide-add">Đóng</button>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="admin-list">
        <h2>Danh sách tài khoản</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Email</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td data-label="ID"><?php echo (int)$row['admin_id']; ?></td>
                        <td data-label="Ảnh">
                            <?php if (!empty($row['images'])): ?>
                                <img class="table-image" alt=""
                                     src="../uploads/<?php echo htmlspecialchars($row['images']); ?>">
                            <?php else: ?>
                                <span>Chưa có</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Email"><?php echo htmlspecialchars($row['admin_email']); ?></td>
                        <td data-label="Thao tác">
                            
                                <div class="action-buttons">
                                    <a class="btn btn-edit" href="?edit=<?php echo (int)$row['admin_id']; ?>">Sửa</a>
                                    <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="admin_id" value="<?php echo (int)$row['admin_id']; ?>">
                                        <button type="submit" class="btn btn-delete">Xóa</button>
                                    </form>
                                </div>
                            
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<script>
(function () {
    var form = document.getElementById('user-form');
    var openBtn = document.getElementById('btn-show-add');
    var closeBtn = document.getElementById('btn-hide-add');

    if (openBtn && form) {
        openBtn.addEventListener('click', function () {
            form.classList.remove('is-hidden');
            openBtn.classList.add('is-hidden');
            var first = form.querySelector('input[name="admin_email"]');
            if (first) first.focus();
        });
    }
    if (closeBtn && form && openBtn) {
        closeBtn.addEventListener('click', function () {
            form.classList.add('is-hidden');
            openBtn.classList.remove('is-hidden');
        });
    }

    document.querySelectorAll('.btn-toggle').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var menu = btn.closest('.row-menu');
            var open = menu.classList.contains('is-open');
            document.querySelectorAll('.row-menu.is-open').forEach(function (m) {
                m.classList.remove('is-open');
            });
            if (!open) menu.classList.add('is-open');
        });
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('.row-menu.is-open').forEach(function (m) {
            m.classList.remove('is-open');
        });
    });

    var fileInput = document.querySelector('#user-form input[name="images"]');
    var newPhoto = document.getElementById('new-photo');
    var newPhotoLabel = document.getElementById('new-photo-label');
    var currentPhoto = document.querySelector('#user-form .current-photo:not(#new-photo)');

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (!file) return;

            var reader = new FileReader();
            reader.onload = function (e) {
                if (currentPhoto) {
                    currentPhoto.src = e.target.result;
                    if (currentPhoto.previousElementSibling) {
                        currentPhoto.previousElementSibling.textContent = 'Ảnh đã chọn';
                    }
                } else if (newPhoto) {
                    newPhoto.src = e.target.result;
                    newPhoto.classList.remove('is-hidden');
                    newPhotoLabel.classList.remove('is-hidden');
                }
            };
            reader.readAsDataURL(file);
        });
    }
})();
</script>

</body>
</html>
