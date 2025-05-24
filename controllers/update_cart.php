

<?php
session_start();
header('Content-Type: application/json');

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
    echo json_encode(['success' => false, 'message' => 'Kết nối thất bại']);
    exit;
}

$user_id = $_SESSION['user_id'];
$book_id = intval($_POST['book_id'] ?? 0);
$new_amount = intval($_POST['amount'] ?? 0);

if ($book_id <= 0 ) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

$cart_query = $conn->query("SELECT idCart FROM cart WHERE idUser = $user_id LIMIT 1");
if ($cart_query->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Giỏ hàng không tồn tại']);
    exit;
}
$cart_id = $cart_query->fetch_assoc()['idCart'];


if (!$conn->query("UPDATE cartitems SET amount = $new_amount WHERE cartId = $cart_id AND bookId = $book_id")) {
    echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật amount', 'error' => $conn->error]);
    exit;
}


$update_total = $conn->query("
    UPDATE cart
    SET totalPrice = (
        SELECT COALESCE(SUM(ci.amount * b.currentPrice), 0)
        FROM cartitems ci
        JOIN books b ON ci.bookId = b.id
        WHERE ci.cartId = $cart_id
    )
    WHERE idCart = $cart_id");


if (!($total_price_result=$conn->query("SELECT totalPrice FROM cart WHERE idCart = $cart_id"))) {
    echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật totalPrice', 'error' => $conn->error]);
    exit;
}
$total_price = $total_price_result->fetch_assoc()['totalPrice'];


echo json_encode([
    'success' => true,
    'message' => 'Cập nhật thành công',
    'totalPrice' => $total_price,
    'cartId' => $cart_id
]);

?>
