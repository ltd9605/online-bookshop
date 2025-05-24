<?php
// Kết nối CSDL
$conn = new mysqli("localhost", "root", "", "ltw_ud2", );
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Xử lý sửa (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $vai_tro = $_POST['vai_tro'];
    $ten = $_POST['ten_nhan_vien'];
    $luong = $_POST['muc_luong'];
    $ngay = date('Y-m-d');

    $stmt = $conn->prepare("UPDATE NhanVien SET vai_tro=?, ten_nhan_vien=?, muc_luong=?, ngay_chinh_sua=? WHERE id=?");
    $stmt->bind_param("ssdsi", $vai_tro, $ten, $luong, $ngay, $id);
    $stmt->execute();
    echo "Sửa thành công!";
    exit;
}

// Xử lý xoá (GET)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM NhanVien WHERE id = $id");
    echo "Xoá thành công!";
    exit;
}

// Lấy dữ liệu nhân viên
$result = $conn->query("SELECT * FROM NhanVien");
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <meta charset="UTF-8">
    <title>Quản lý nhân viên</title>
    <style>
        .popup, .overlay { display: none; }
        .popup {
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: #fff; padding: 20px; border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0,0,0,0.3); z-index: 1001;
        }
        .overlay {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 1000;
        }
        body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f5f6fa;
    padding: 40px;
}

.table-container {
    background: white;
    border-radius: 12px;
    padding: 20px;
    width: 90%;
    margin: 0 auto;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
}

.table-container h2 {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 20px;
    color: #2f3640;
    padding-left: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
    border-radius: 100px;
}

th, td {
    text-align: left;
    padding: 12px 16px;
}

th {
    background-color: #ffffff;
    color: #2f3640;
    border-bottom: 1px solid #ccc;
}
.tht{
    background-color:rgb(13, 80, 124);
    color:rgb(221, 225, 231);
    border-bottom: 1px solid #ccc;
}
tr:nth-child(even) {
    background-color: #f0f0f0;
}

tr:nth-child(odd) {
    background-color: #ffffff;
}

td .action-icon {
    cursor: pointer;
    margin-right: 10px;
    font-size: 18px;
}

.action-delete {
    color: red;
}

.action-edit {
    color: blue;
}

.action-view {
    color: green;
}

td .action-icon:hover {
    opacity: 0.7;
}
/* Overlay mờ nền */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* mờ đen */
    z-index: 999;
    display: none;
}

/* Popup chỉnh sửa */
.popup {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #ffffff;
    padding: 30px 25px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    width: 90%;
    max-width: 400px;
    display: none;
    animation: popupFadeIn 0.3s ease-out;
}

/* Animation */
@keyframes popupFadeIn {
    from { opacity: 0; transform: translate(-50%, -45%); }
    to   { opacity: 1; transform: translate(-50%, -50%); }
}

/* Tiêu đề popup */
.popup h3 {
    margin-top: 0;
    font-size: 20px;
    color: #2f3640;
    text-align: center;
}

/* Form trong popup */
.popup form {
    display: flex;
    flex-direction: column;
}

/* Các trường nhập */
.popup input[type="text"],
.popup input[type="number"],
.popup select {
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #dcdde1;
    border-radius: 8px;
    font-size: 14px;
}

/* Nút Lưu và Hủy */
.popup input[type="submit"],
.popup button {
    padding: 10px;
    font-size: 15px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 5px;
    transition: background 0.3s ease;
}

.popup input[type="submit"] {
    background-color: #27ae60;
    color: white;
}

.popup input[type="submit"]:hover {
    background-color: #219150;
}

.popup button {
    background-color: #bdc3c7;
    color: #2c3e50;
}

.popup button:hover {
    background-color: #95a5a6;
}

    </style>
</head>
<body>
            <div id="sidebar" class="hidden md:block md:w-64 bg-white shadow-md">
            <?php include_once '../admin/gui/sidebar.php' ?>
        </div>
    <h2>Danh sách nhân viên</h2>
    <table border="1" cellpadding="8">
        <thead>
            <tr >
                <th class="tht">VAI TRÒ</th>
                <th class="tht">TÊN NHÂN VIÊN</th>
                <th class="tht">NGÀY TẠO</th>
                <th class="tht">NGÀY CHỈNH SỬA</th>
                <th class="tht">MỨC LƯƠNG</th>
                <th class="tht">THAO TÁC</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr id="row-<?= $row['id'] ?>">
                    <td><?= htmlspecialchars($row["vai_tro"]) ?></td>
                    <td><?= htmlspecialchars($row["ten_nhan_vien"]) ?></td>
                    <td><?= $row["ngay_tao"] ?></td>
                    <td><?= $row["ngay_chinh_sua"] ?></td>
                    <td><?= number_format($row["muc_luong"], 0, ',', '.') ?> đ</td>
                    <td>
                        <button onclick='openEditPopup(<?= json_encode($row) ?>)'>Sửa</button>
                        <button onclick='deleteNhanVien(<?= $row["id"] ?>)'>Xoá</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Popup Sửa -->
    <div class="overlay" id="overlay" onclick="closePopup()"></div>
    <div class="popup" id="editPopup">
        <h3>Sửa thông tin nhân viên</h3>
        <form id="editForm">
            <input type="hidden" name="id" id="edit-id">
            <input type="hidden" name="action" value="edit">
            Vai trò:
            <select name="vai_tro" id="edit-vai-tro">
            <option value="Quản lý">Quản lý</option>
            <option value="Nhân viên bán hàng">Nhân viên bán hàng</option>
            <option value="Kế toán">Kế toán</option>
            <option value="Bảo vệ">Bảo vệ</option>
            <option value="Tạp vụ">Tạp vụ</option>
            <option value="Nhân viên kho">Nhân viên kho</option>
            <option value="Thủ kho">Thủ kho</option>
            <option value="Lễ tân">Lễ tân</option>
            <option value="IT support">IT support</option>
            </select><br><br>
            Tên nhân viên: <input type="text" name="ten_nhan_vien" id="edit-ten"><br><br>
            Mức lương: <input type="number" name="muc_luong" id="edit-luong"><br><br>
            <input type="submit" value="Lưu">
            <button type="button" onclick="closePopup()">Hủy</button>
        </form>
    </div>

    <script>
        function openEditPopup(data) {
            document.getElementById("edit-id").value = data.id;
            document.getElementById("edit-vai-tro").value = data.vai_tro;
            document.getElementById("edit-ten").value = data.ten_nhan_vien;
            document.getElementById("edit-luong").value = data.muc_luong;
            document.getElementById("overlay").style.display = "block";
            document.getElementById("editPopup").style.display = "block";
        }

        function closePopup() {
            document.getElementById("overlay").style.display = "none";
            document.getElementById("editPopup").style.display = "none";
        }

        document.getElementById("editForm").onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch("nhanvien.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.text())
            .then(msg => {
                alert(msg);
                location.reload();
            });
        };

        function deleteNhanVien(id) {
            if (!confirm("Bạn có chắc muốn xoá?")) return;
            fetch("nhanvien.php?delete=" + id)
                .then(res => res.text())
                .then(msg => {
                    alert(msg);
                    document.getElementById("row-" + id).remove();
                });
        }
    </script>
</body>
</html>
