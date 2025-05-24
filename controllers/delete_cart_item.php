<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

if (!isset($_POST['book_id']) || !isset($_POST['cartId'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
    exit;
}

$bookId = intval($_POST['book_id']);
$cartId = intval($_POST['cartId']);

$conn = new mysqli("localhost", "root", "", "ltw_ud2");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Kết nối CSDL thất bại']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM cartitems WHERE bookId = ? AND cartId = ?");
$stmt->bind_param("ii", $bookId, $cartId);

if ($stmt->execute()) {
    $res = $conn->query("
        SELECT SUM(currentPrice * amount) as total 
        FROM cartitems 
        JOIN books ON cartitems.bookId = books.id 
        WHERE cartitems.cartId = $cartId
    ");

    $total = 0;
    if ($res && $row = $res->fetch_assoc()) {
        $total = $row['total'] ?? 0;
        $conn->query("UPDATE cart SET totalPrice = $total WHERE idCart = $cartId");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Đã xoá sản phẩm',
        'totalPrice' => $total
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Xoá thất bại']);
}

$conn->close();
?>
