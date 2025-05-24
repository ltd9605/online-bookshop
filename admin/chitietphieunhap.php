<?php
session_start();
include_once "../database/database.php";
include_once "../database/phieunhap.php";
include_once "../database/chitietphieunhap.php";

$ctpn = new ChiTietPhieuNhap($pdo);
$phieunhap = new PhieuNhap($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $pn = $phieunhap->getById($id);
    $allDetail = $ctpn->getAllDetailById($pn['id']);
    $fullname = $phieunhap->getFullNameById($pn['idNguoiNhap']);
    $tongtien = $pn['tongtien'];
    $thoigian = $pn['date'];
?>
<div class="modal-header">
    <h1 class="modal-title fs-5">Thông tin phiếu nhập</h1>
</div>
<div class="modal-body container-fluid row" style="margin: auto;">
    <div class="info col-12 row">
        <div class="receiver col-12 mb-3">
            <h5 class="text-danger">Người nhập:
                <span class="text-capitalize text-black">
                    <?php echo $fullname; ?>
                </span>
            </h5>
        </div>
        <div class="totalBill col-12 mb-3">
            <h5 class="text-danger">Tổng tiền phiếu nhập:
                <span class="text-capitalize text-black">
                    <?php echo number_format($tongtien, 0, ',', '.') . 'đ'; ?>
                </span>
            </h5>
        </div>
        <div class="order-time col-12 mb-3">
            <h5 class="text-danger">Thời gian nhập:
                <span class="text-black">
                    <?php echo $thoigian; ?>
                </span>
            </h5>
        </div>
    </div>

    <div class="list-product col-12" style="border-top: 1px solid black;"><br>
        <h3 class="text-center text-uppercase mt-3 mb-3">Danh sách sản phẩm:</h3>
        <!-- <br> -->
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th style="width: 27% !important;">Tên sản phẩm</th>
                    <th style="width: 28% !important;">Nhà cung cấp</th>
                    <th style="width: 15% !important;">Giá nhập</th>
                    <th style="width: 15% !important;">Số lượng</th>
                    <th style="width: 15% !important;">Tổng tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allDetail as $item): 
                    $bookname = $ctpn->getBookNameById($item['idBook']);
                    $nccName = $ctpn->getNCCNameById($item['idCungCap']);
                ?>
                <tr>
                    <td><?php echo $bookname; ?></td>
                    <td><?php echo $nccName; ?></td>
                    <td><?php echo number_format($item['gianhap'], 0, ',', '.'); ?>đ</td>
                    <td><?php echo $item['soluong']; ?></td>
                    <td>
                        <?php 
                            $tongTien = $item['soluong'] * $item['gianhap'];
                            echo number_format($tongTien, 0, ',', '.') . 'đ';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
}
?>