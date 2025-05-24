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
    die("Kết nối thất bại: " . $conn->connect_error);
}


if (isset($_POST['approve']) && isset($_POST['idBill'])) {
    $idBill = intval($_POST['idBill']);
    if ($idBill > 0) {
        $sql = "UPDATE hoadon SET statusBill = 2 WHERE idBill = $idBill";
        if ($conn->query($sql)) {
            echo "<script>
                alert('✅ Đã duyệt đơn hàng mã #$idBill thành công!');
                window.location.href = '../admin/gui/quanlidon.php';
            </script>";
        } else {
            echo "Lỗi: " . $conn->error;
        }
    } else {
        echo "<script>alert('idBill không hợp lệ'); history.back();</script>";
    }

}
?>
