<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}
$id = $_POST['id'];

$id = $_POST['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID đơn hàng']);
    exit;
}
?>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "ltw_ud2");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Kết nối thất bại']);
    exit;
}

$idBill = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($idBill <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID hóa đơn không hợp lệ']);
    exit;
}

$sql = "SELECT books.id AS book_id, books.bookName, chitiethoadon.amount AS quantity, books.currentPrice AS price
        FROM chitiethoadon 
        JOIN books ON books.id = chitiethoadon.idBook 
        WHERE chitiethoadon.idHoadon = $idBill";

$result = $conn->query($sql);
$products = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['book_id'],
            'name' => $row['bookName'],
            'quantity' => (int)$row['quantity'],
            'price' => (float)$row['price']
        ];
    }

    echo json_encode(['success' => true, 'products' => $products]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể truy vấn CSDL']);
}

$conn->close();
?>