<?php
session_start();
header('Content-Type: application/json');

// Bật debug (gỡ ra khi production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Kết nối CSDL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ltw_ud2";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Kết nối thất bại: ' . $conn->connect_error]);
    exit;
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$book_id = intval($_POST['book_id'] ?? 0);

if ($book_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
    exit;
}

$cartId = null;
$resultCart = $conn->query("SELECT idCart FROM cart WHERE idUser = $user_id LIMIT 1");

if ($resultCart && $rowCart = $resultCart->fetch_assoc()) {
    $cartId = $rowCart['idCart'];
} else {
    $conn->query("INSERT INTO cart (idUser) VALUES ($user_id)");
    $cartId = $conn->insert_id;
}

$checkItem = $conn->query("SELECT * FROM cartitems WHERE cartId = $cartId AND bookId = $book_id");
if ($checkItem && $checkItem->num_rows > 0) {
    $conn->query("UPDATE cartitems SET amount = amount + 1 WHERE cartId = $cartId AND bookId = $book_id");
} else {
    $conn->query("INSERT INTO cartitems (cartId, bookId, amount) VALUES ($cartId, $book_id, 1)");
}

// Cập nhật lại tổng tiền trong giỏ
$conn->query("
    UPDATE cart
    SET totalPrice = (
        SELECT COALESCE(SUM(ci.amount * b.currentPrice), 0)
        FROM cartitems ci
        JOIN books b ON ci.bookId = b.id
        WHERE ci.cartId = $cartId
    )
    WHERE idCart = $cartId
");

// Lấy tổng số lượng sản phẩm trong giỏ
$totalItems = 0;
$resultCount = $conn->query("SELECT count(*) as total FROM cartitems WHERE cartId = $cartId");
if ($resultCount && $rowCount = $resultCount->fetch_assoc()) {
    $totalItems = intval($rowCount['total']);
}

// Trả JSON về
echo json_encode([
    'success' => true,
    'message' => 'Đã thêm vào giỏ hàng',
    'count' => $totalItems
]);
?>
