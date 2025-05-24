<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}
?>
<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "ltw_ud2");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Kết nối thất bại']);
    exit;
}
/*
const texts = {
1: 'Đang xử lý',
2: 'Đang được giao',
3: 'Giao hàng thành công',
4: 'Đơn hàng đã hủy'
};
*/
$data = json_decode(file_get_contents("php://input"), true);
$idBill = intval($data['idBill']);
$status = intval($data['statusBill']);
$products = $data['products'] ?? [];
//$note = $conn->real_escape_string($data['note']);

//$update = "UPDATE hoadon SET statusBill = $status, ly_do_huy = '$note' WHERE idBill = $idBill";

if ($status === 3 && !empty($products)) {
    foreach ($products as $product) {
        $bookId = intval($product['id']);
        $quantity = intval($product['quantity']);
        $conn->query("UPDATE books SET quantitySold = quantitySold - $quantity WHERE id = $bookId");
    }
}
foreach ($products as $product) {
    $bookId = intval($product['id']);
    $quantity = intval($product['quantity']);
    if($quantity==0){
        $conn->query("UPDATE books SET isActive = 0 WHERE id = $bookId");
    }
}
$update_amount = "";
$update = "UPDATE hoadon SET statusBill = $status  WHERE idBill = $idBill";
$insertHoadon_trangthai = "INSERT INTO hoadon_trangthai (idBill, trangthai) VALUES ($idBill, $status)";


if ($conn->query($update)) {
    if ($conn->query($insertHoadon_trangthai)) {
        echo json_encode([
            "success" => true,
            "message" => "Đã cập nhật đơn hàng #MD$idBill",
            "redirect" => "../admin/gui/quanlidon.php"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Đã cập nhật đơn hàng #MD$idBill, nhưng không ghi được lịch sử trạng thái."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Lỗi khi cập nhật."
    ]);
}
$conn->close();
?>
