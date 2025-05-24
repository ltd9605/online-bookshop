<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ltw_ud2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 7;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

// Handle edit customer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_customer'])) {
    $id = intval($_POST['id']);
    $fullName = trim($_POST['fullName']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $email = trim($_POST['email']);
    $status_user = $_POST['status_user'];
    $avatar = $_FILES['avatar']['name'];

    // Validate input
    if (empty($fullName) || empty($phoneNumber) || empty($email)) {
        echo "<script>Swal.fire({title: 'Lỗi', text: 'Vui lòng nhập đầy đủ thông tin!', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>Swal.fire({title: 'Lỗi', text: 'Email không hợp lệ!', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
    } else {
        // Get existing avatar
        $sql = "SELECT avatar FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $current_avatar = $stmt->get_result()->fetch_assoc()['avatar'];

        // Handle avatar upload
        if (!empty($avatar)) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($avatar);
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
                echo "<script>Swal.fire({title: 'Lỗi', text: 'Không thể tải lên ảnh!', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
                $avatar = $current_avatar;
            }
        } else {
            $avatar = $current_avatar;
        }

        // Update users table
        $sql = "UPDATE users SET fullName = ?, phoneNumber = ?, email = ?, avatar = ?, status_user = ? WHERE id = ? AND role_id IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssisi", $fullName, $phoneNumber, $email, $avatar, $status_user, $id);
        if ($stmt->execute()) {
            echo "<script>Swal.fire({title: 'Thành công', text: 'Cập nhật khách hàng thành công!', icon: 'success', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
        } else {
            echo "<script>Swal.fire({title: 'Lỗi', text: 'Lỗi khi cập nhật khách hàng: " . addslashes($conn->error) . "', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
        }
    }
}

// Handle add customer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_customer'])) {
    $fullName = trim($_POST['fullName']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $email = trim($_POST['email']);
    $password = password_hash('default123', PASSWORD_DEFAULT); // Default password
    $avatar = $_FILES['avatar']['name'];
    $status_user = $_POST['status_user'];

    // Validate input
    if (empty($fullName) || empty($phoneNumber) || empty($email)) {
        echo "<script>Swal.fire({title: 'Lỗi', text: 'Vui lòng nhập đầy đủ thông tin!', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>Swal.fire({title: 'Lỗi', text: 'Email không hợp lệ!', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
    } else {
        // Handle avatar upload
        if (!empty($avatar)) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($avatar);
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
                echo "<script>Swal.fire({title: 'Lỗi', text: 'Không thể tải lên ảnh!', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
                $avatar = 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png';
            }
        } else {
            $avatar = 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png';
        }

        // Insert into users table
        $sql = "INSERT INTO users (userName, fullName, phoneNumber, email, password, avatar, status_user) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $userName = $email; // Use email as username
        $stmt->bind_param("ssssssi", $userName, $fullName, $phoneNumber, $email, $password, $avatar, $status_user);
        if ($stmt->execute()) {
            echo "<script>Swal.fire({title: 'Thành công', text: 'Thêm khách hàng thành công!', icon: 'success', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
        } else {
            echo "<script>Swal.fire({title: 'Lỗi', text: 'Lỗi khi thêm khách hàng: " . addslashes($conn->error) . "', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
        }
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $sql = "SELECT id FROM users WHERE id = ? AND role_id IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>Swal.fire({title: 'Lỗi', text: 'Không tìm thấy khách hàng hoặc khách hàng không phải người dùng thông thường!', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
    } else {
        $sql = "DELETE cthd FROM chitiethoadon cthd 
                INNER JOIN hoadon hd ON cthd.idHoadon = hd.idBill 
                WHERE hd.idUser = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $sql = "DELETE htt FROM hoadon_trangthai htt 
                INNER JOIN hoadon hd ON htt.idBill = hd.idBill 
                WHERE hd.idUser = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Delete related records in hoadon
        $sql = "DELETE FROM hoadon WHERE idUser = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $sql = "DELETE ci FROM cartitems ci 
                INNER JOIN cart c ON ci.cartId = c.idCart 
                WHERE c.idUser = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $sql = "DELETE FROM cart WHERE idUser = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $sql = "DELETE FROM review WHERE userId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $sql = "DELETE FROM thongtingiaohang WHERE id_user = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $sql = "DELETE FROM users WHERE id = ? AND role_id IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<script>Swal.fire({title: 'Thành công', text: 'Xóa khách hàng thành công!', icon: 'success', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
        } else {
            echo "<script>Swal.fire({title: 'Lỗi', text: 'Lỗi khi xóa khách hàng: " . addslashes($conn->error) . "', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
        }
    }
}

if (isset($_GET['lock'])) {
    $id = intval($_GET['lock']);

    $sql = "SELECT id FROM users WHERE id = ? AND role_id IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>Swal.fire({title: 'Lỗi', text: 'Không tìm thấy khách hàng!', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
    } else {
        $sql = "UPDATE users SET status_user = 0 WHERE id = ? AND role_id IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<script>Swal.fire({title: 'Thành công', text: 'Khóa tài khoản thành công!', icon: 'success', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
        } else {
            echo "<script>Swal.fire({title: 'Lỗi', text: 'Lỗi khi khóa tài khoản: " . addslashes($conn->error) . "', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
        }
    }
}
if (isset($_GET['unlock'])) {
    $id = intval($_GET['unlock']);

    $sql = "SELECT id FROM users WHERE id = ? AND role_id IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>Swal.fire({title: 'Lỗi', text: 'Không tìm thấy khách hàng!', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
    } else {
        $sql = "UPDATE users SET status_user = 1 WHERE id = ? AND role_id IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<script>Swal.fire({title: 'Thành công', text: 'Mở tài khoản thành công!', icon: 'success', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
        } else {
            echo "<script>Swal.fire({title: 'Lỗi', text: 'Lỗi khi mở tài khoản: " . addslashes($conn->error) . "', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
        }
    }
}

$edit_customer = null;
if ($edit_id > 0) {
    $sql = "SELECT id, fullName, phoneNumber, email, avatar, status_user FROM users WHERE id = ? AND role_id IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_customer = $stmt->get_result()->fetch_assoc();
    if (!$edit_customer) {
        echo "<script>Swal.fire({title: 'Lỗi', text: 'Không tìm thấy khách hàng để chỉnh sửa!', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
    }
}

$search_query = $search ? "AND (fullName LIKE ? OR phoneNumber LIKE ?)" : "";
$sql = "SELECT id, fullName, phoneNumber, avatar, status_user FROM users WHERE role_id IS NULL $search_query LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
if ($search) {
    $search_term = "%$search%";
    $stmt->bind_param("ssii", $search_term, $search_term, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$customers = $result->fetch_all(MYSQLI_ASSOC);

$sql_count = "SELECT COUNT(*) as total FROM users WHERE role_id IS NULL $search_query";
$stmt_count = $conn->prepare($sql_count);
if ($stmt_count === false) {
    die("Prepare count failed: " . $conn->error);
}
if ($search) {
    $stmt_count->bind_param("ss", $search_term, $search_term);
}
$stmt_count->execute();
$total_customers = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_customers / $limit);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Khách Hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
    <main class="flex flex-row min-h-screen" style="max-height: 100vh; padding:0; margin:0;">
        <?php
        if (file_exists('./gui/sidebar.php')) {
            include_once './gui/sidebar.php';
        } else {
            echo "<script>Swal.fire({title: 'Lỗi', text: 'Không tìm thấy file sidebar.php!', icon: 'error', timer: 1500, showConfirmButton: false}).then(() => { window.location.href='thongTinKhachHang.php'; });</script>";
        }
        ?>
        <div class="concon" style=" width: 100%; overflow-y: scroll; display: flex; align-items: center;">
            <div class="flex-1 container py-8" style="max-height: 100vh; width:100vh; margin:40px;">
                <div class="card">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Quản Lý Khách Hàng</h2>
                        <?php if ($roleTableSidebar->isAuthorized($adminID, 5, 2)) {
                        ?>
                            <button onclick="openAddModal()" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Thêm Khách Hàng
                            </button>
                        <?php } ?>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-600">Hiển thị</label>
                            <input type="number" id="kh-show-number" value="<?php echo $limit; ?>" min="1"
                                class="w-16 p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                onchange="updateLimit()">
                            <span class="text-sm font-medium text-gray-600">dòng</span>
                        </div>
                        <div class="flex-1 sm:ml-auto relative">
                            <div class="input-icon">
                                <i class="fas fa-search"></i>
                                <input type="text" id="kh-search-input" value="<?php echo htmlspecialchars($search); ?>"
                                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="Tìm theo tên hoặc số điện thoại"
                                    onkeyup="if(event.keyCode==13) searchCustomers()">
                                <div id="search-spinner" class="spinner"></div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tên</th>
                                    <th>Số Điện Thoại</th>
                                    <th>Avatar</th>
                                    <th>Trạng Thái</th>
                                    <?php if ($roleTableSidebar->isAuthorized($adminID, 5, 3) || $roleTableSidebar->isAuthorized($adminID, 5, 4)) {
                                    ?>
                                        <th>Hành Động</th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($customers)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-6 text-gray-500 font-medium">
                                            Không tìm thấy khách hàng
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($customer['fullName']); ?></td>
                                            <td><?php echo htmlspecialchars($customer['phoneNumber']); ?></td>
                                            <td>
                                                <img src="<?php echo htmlspecialchars($customer['avatar']); ?>"
                                                    alt="Avatar" class="avatar">
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $customer['status_user'] ? 'badge-active' : 'badge-inactive'; ?>">
                                                    <?php echo $customer['status_user'] ? 'Đang hoạt động' : 'Ngừng hoạt động'; ?>
                                                </span>
                                            </td>
                                            <?php if ($roleTableSidebar->isAuthorized($adminID, 5, 3) || $roleTableSidebar->isAuthorized($adminID, 5, 4)) {
                                            ?>
                                                <td>
                                                    <div class="flex gap-2">
                                                        <?php if ($roleTableSidebar->isAuthorized($adminID, 5, 3)) {
                                                        ?>
                                                            <a href="#" onclick="openEditModal(<?php echo $customer['id']; ?>, '<?php echo addslashes($customer['fullName']); ?>', '<?php echo addslashes($customer['phoneNumber']); ?>', '<?php echo addslashes($edit_customer && $edit_customer['id'] == $customer['id'] ? $edit_customer['email'] : ''); ?>', '<?php echo addslashes($customer['avatar']); ?>', <?php echo $customer['status_user']; ?>)"
                                                                class="btn btn-edit">
                                                                <i class="fas fa-edit"></i> Sửa
                                                            </a>
                                                            <?php if ($customer['status_user']): ?>
                                                                <a href="#" onclick="confirmLock(<?php echo $customer['id']; ?>)"
                                                                    class="btn btn-lock">
                                                                    <i class="fas fa-lock"></i> Khóa
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="#" onclick="confirmUnlock(<?php echo $customer['id']; ?>)"
                                                                    class="btn btn-unlock">
                                                                    <i class="fas fa-unlock"></i> Mở
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php } ?>
                                                        <?php if ($roleTableSidebar->isAuthorized($adminID, 5, 4)) {
                                                        ?>
                                                            <a href="#" onclick="confirmDelete(<?php echo $customer['id']; ?>)"
                                                                class="btn btn-delete">
                                                                <i class="fas fa-trash"></i> Xóa
                                                            </a>
                                                        <?php } ?>

                                                    </div>
                                                </td>
                                            <?php } ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-between items-center mt-6">
                        <div class="text-sm text-gray-600">
                            Trang <?php echo $page; ?> / <?php echo $total_pages; ?> (Tổng <?php echo $total_customers; ?> khách hàng)
                        </div>
                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                                <a href="thongTinKhachHang.php?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>"
                                    class="btn btn-primary">
                                    <i class="fas fa-chevron-left"></i> Trước
                                </a>
                            <?php else: ?>
                                <button class="btn btn-primary opacity-50 cursor-not-allowed" disabled>
                                    <i class="fas fa-chevron-left"></i> Trước
                                </button>
                            <?php endif; ?>
                            <?php if ($page < $total_pages): ?>
                                <a href="thongTinKhachHang.php?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>"
                                    class="btn btn-primary">
                                    Sau <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-primary opacity-50 cursor-not-allowed" disabled>
                                    Sau <i class="fas fa-chevron-right"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Thêm Khách Hàng</h3>
            <form id="addForm" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Họ và Tên</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="fullName" placeholder="Nhập họ và tên" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Số Điện Thoại</label>
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="phoneNumber" placeholder="Nhập số điện thoại" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Nhập email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Avatar</label>
                    <input type="file" name="avatar" accept="image/*" class="border-none">
                </div>
                <div class="form-group">
                    <label>Trạng Thái</label>
                    <select name="status_user" required>
                        <option value="1">Đang hoạt động</option>
                        <option value="0">Ngừng hoạt động</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeAddModal()" class="btn btn-delete">Hủy</button>
                    <button type="submit" name="add_customer" class="btn btn-primary">
                        <i class="fas fa-save"></i> Thêm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Sửa Khách Hàng</h3>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <label>Họ và Tên</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="fullName" id="editFullName" placeholder="Nhập họ và tên" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Số Điện Thoại</label>
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="phoneNumber" id="editPhoneNumber" placeholder="Nhập số điện thoại" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="editEmail" placeholder="Nhập email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Avatar</label>
                    <input type="file" name="avatar" accept="image/*" class="border-none">
                    <img id="editAvatar" class="avatar mt-3" alt="Avatar">
                </div>
                <div class="form-group">
                    <label>Trạng Thái</label>
                    <select name="status_user" id="editStatus" required>
                        <option value="1">Đang hoạt động</option>
                        <option value="0">Ngừng hoạt động</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeEditModal()" class="btn btn-delete">Hủy</button>
                    <button type="submit" name="edit_customer" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
            document.getElementById('addForm').reset();
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        function openEditModal(id, fullName, phoneNumber, email, avatar, status) {
            document.getElementById('editId').value = id;
            document.getElementById('editFullName').value = fullName;
            document.getElementById('editPhoneNumber').value = phoneNumber;
            document.getElementById('editEmail').value = email;
            document.getElementById('editAvatar').src = avatar;
            document.getElementById('editStatus').value = status;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('editForm').reset();
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Bạn có chắc muốn xóa?',
                text: "Hành động này không thể hoàn tác!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'thongTinKhachHang.php?delete=' + id;
                }
            });
        }

        function confirmLock(id) {
            Swal.fire({
                title: 'Bạn có chắc muốn khóa tài khoản?',
                text: "Tài khoản sẽ bị vô hiệu hóa!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Khóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'thongTinKhachHang.php?lock=' + id;
                }
            });
        }

        function confirmUnlock(id) {
            Swal.fire({
                title: 'Bạn có chắc muốn mở tài khoản?',
                text: "Tài khoản sẽ được kích hoạt lại!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#06b6d4',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Mở',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'thongTinKhachHang.php?unlock=' + id;
                }
            });
        }

        function updateLimit() {
            const limit = document.getElementById('kh-show-number').value;
            if (limit < 1) return;
            document.getElementById('search-spinner').style.display = 'block';
            window.location.href = 'thongTinKhachHang.php?page=1&limit=' + limit + '&search=<?php echo urlencode($search); ?>';
        }

        function searchCustomers() {
            const search = document.getElementById('kh-search-input').value;
            document.getElementById('search-spinner').style.display = 'block';
            window.location.href = 'thongTinKhachHang.php?page=1&limit=<?php echo $limit; ?>&search=' + encodeURIComponent(search);
        }

        <?php if ($edit_customer): ?>
            openEditModal(
                <?php echo $edit_customer['id']; ?>,
                '<?php echo addslashes($edit_customer['fullName']); ?>',
                '<?php echo addslashes($edit_customer['phoneNumber']); ?>',
                '<?php echo addslashes($edit_customer['email']); ?>',
                '<?php echo addslashes($edit_customer['avatar']); ?>',
                <?php echo $edit_customer['status_user']; ?>
            );
        <?php endif; ?>
    </script>

    <?php
    $conn->close();
    ?>
</body>

</html>

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f3f4f6;
    }

    .container {
        max-width: 1400px;
        /* margin: 0 auto; */
        /* padding: 0 1rem; */
    }

    .card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        padding: 2rem;
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table th {
        background: #f9fafb;
        padding: 1rem;
        text-align: left;
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        border-bottom: 2px solid #e5e7eb;
    }

    .table td {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.9rem;
        color: #4b5563;
    }

    .table tr:hover {
        background: #f9fafb;
        transition: background 0.2s;
    }

    .avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e5e7eb;
    }

    .badge {
        padding: 0.35rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .badge-active {
        background: #dcfce7;
        color: #15803d;
    }

    .badge-inactive {
        background: #fee2e2;
        color: #b91c1c;
    }

    .btn {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background: #2563eb;
    }

    .btn-edit {
        background: #22c55e;
        color: white;
    }

    .btn-edit:hover {
        background: #16a34a;
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .btn-delete:hover {
        background: #dc2626;
    }

    .btn-lock {
        background: #f59e0b;
        color: white;
    }

    .btn-lock:hover {
        background: #d97706;
    }

    .btn-unlock {
        background: #06b6d4;
        color: white;
    }

    .btn-unlock:hover {
        background: #0891b2;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal-content {
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        width: 100%;
        max-width: 32rem;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        animation: slideIn 0.3s ease-out;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group label {
        display: block;
        font-size: 0.9rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.9rem;
        color: #374151;
        transition: border-color 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .input-icon {
        position: relative;
    }

    .input-icon i {
        position: absolute;
        top: 50%;
        left: 0.75rem;
        transform: translateY(-50%);
        color: #6b7280;
    }

    .input-icon input {
        padding-left: 2.5rem;
    }

    .spinner {
        display: none;
        border: 4px solid #f3f4f6;
        border-top: 4px solid #3b82f6;
        border-radius: 50%;
        width: 1.5rem;
        height: 1.5rem;
        animation: spin 1s linear infinite;
        position: absolute;
        top: 50%;
        right: 1rem;
        transform: translateY(-50%);
    }

    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 640px) {
        .card {
            padding: 1rem;
        }

        .table th,
        .table td {
            font-size: 0.8rem;
            padding: 0.75rem;
        }

        .btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        .avatar {
            width: 2rem;
            height: 2rem;
        }

        .modal-content {
            margin: 1rem;
            padding: 1.5rem;
        }
    }
</style>