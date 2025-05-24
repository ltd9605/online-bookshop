<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}
?>
<?php
$conn = new mysqli("localhost", "root", "", "ltw_ud2");
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Kết nối thất bại."]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idBill'])) {
    $idBill = intval($_POST['idBill']);
    $update = "UPDATE hoadon SET statusBill = 4 WHERE idBill = $idBill";
    $insertHoadon_trangthai = "INSERT INTO hoadon_trangthai (idBill, trangthai) VALUES ($idBill, 4)";


    if ($conn->query($update)) {
        if ($conn->query($insertHoadon_trangthai)) {
            echo json_encode([
                "success" => true,
                "message" => "Đã huỷ đơn hàng #$idBill",
                "redirect" => "../admin/gui/quanlidon.php"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Huỷ đơn hàng thành công, nhưng không ghi được lịch sử trạng thái."
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Lỗi khi huỷ đơn hàng."
        ]);
    }
}
?>
