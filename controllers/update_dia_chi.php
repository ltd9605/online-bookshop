<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ltw_ud2";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối DB']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id = $conn->real_escape_string($data['id']);
$name = $conn->real_escape_string($data['name']);
$phone = $conn->real_escape_string($data['phone']);
$address = $conn->real_escape_string($data['address']);
$city = $conn->real_escape_string($data['city']);
$district = $conn->real_escape_string($data['district']);
$ward = $conn->real_escape_string($data['ward']);
$status = $conn->real_escape_string($data['status']);

if (!$phone ) {
    echo json_encode(['success' => false, 'message' => 'Nhập số điện thoại']);
    exit;
}
if (!$name ) {
    echo json_encode(['success' => false, 'message' => 'Nhập tên người nhận']);
    exit;
}
if (!$city || !$district || !$ward) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin giao hàng $city, $district, $ward']);
    exit;
}

// Cập nhật thông tin
$sql = "UPDATE thongTinGiaoHang 
        SET tennguoinhan='$name', sdt='$phone', diachi='$address', 
            thanhpho='$city', quan='$district', huyen='$ward',status='$status' 
        WHERE id_user = " . $_SESSION['user_id'] . " AND id = '$id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>
