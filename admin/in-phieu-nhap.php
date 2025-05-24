<?php
// filepath: c:\xampp\htdocs\LTW-UD2\admin\in-phieu-nhap.php
session_start();
require_once "../database/database.php";
require_once "../database/phieunhap.php";
require_once "../database/chitietphieunhap.php";
require_once "../database/book.php";
require_once "../database/supplier.php";
require_once "../database/user.php";

// Check if we have a receipt ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div style='text-align: center; padding: 50px;'>";
    echo "<h1 style='color: #e53e3e;'>Error</h1>";
    echo "<p>No receipt ID provided. Please select a valid import receipt.</p>";
    echo "<a href='thongTinPhieuNhap.php' style='display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 5px;'>Return to Receipt List</a>";
    echo "</div>";
    exit;
}

// Initialize database connections
$phieunhapDB = new PhieuNhap($pdo);
$chitietDB = new ChiTietPhieuNhap($pdo);
$bookDB = new BooksTable(); 
$supplierTable = new SupplierTable();
$userTable = new UsersTable();

// Get receipt ID from URL
$id = (int)$_GET['id'];

// Get receipt details
$receipt = $phieunhapDB->getById($id);
if (!$receipt) {
    echo "<div style='text-align: center; padding: 50px;'>";
    echo "<h1 style='color: #e53e3e;'>Error</h1>";
    echo "<p>Import receipt #$id not found or has been deleted.</p>";
    echo "<a href='thongTinPhieuNhap.php' style='display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 5px;'>Return to Receipt List</a>";
    echo "</div>";
    exit;
}

// Get receipt line items
$items = $chitietDB->getByPhieuNhapId($id);

// Get user info of the person who created the receipt
$nguoiNhap = $userTable->getUserDetailsById($receipt['idNguoiNhap']);

// Generate receipt number
$receiptNumber = sprintf("PN%06d", $receipt['id']);

// Calculate some totals
$totalQuantity = 0;
$totalValue = $receipt['tongtien'];
foreach ($items as $item) {
    $totalQuantity += $item['soluong'];
}

// Format the date
$receiptDate = date('d/m/Y H:i', strtotime($receipt['date']));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Nhập #<?php echo $id; ?></title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .print-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            margin: 0;
        }
        .company-info {
            font-size: 10pt;
            margin: 5px 0;
        }
        .receipt-title {
            font-size: 20pt;
            font-weight: bold;
            margin: 20px 0;
        }
        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .info-group {
            flex: 1;
        }
        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 10pt;
        }
        .info-value {
            font-size: 12pt;
            margin-bottom: 10px;
        }
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .receipt-table th {
            background-color: #f9f9f9;
            border-bottom: 2px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 10pt;
            text-transform: uppercase;
        }
        .receipt-table td {
            border-bottom: 1px solid #eee;
            padding: 12px 10px;
            vertical-align: top;
        }
        .receipt-table .item-code {
            width: 15%;
        }
        .receipt-table .item-name {
            width: 40%;
        }
        .receipt-table .item-price {
            width: 15%;
            text-align: right;
        }
        .receipt-table .item-quantity {
            width: 15%;
            text-align: center;
        }
        .receipt-table .item-total {
            width: 15%;
            text-align: right;
        }
        .receipt-summary {
            margin-top: 20px;
            text-align: right;
        }
        .summary-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 5px;
        }
        .summary-label {
            width: 200px;
            text-align: right;
            padding-right: 20px;
        }
        .summary-value {
            width: 150px;
            text-align: right;
            font-weight: bold;
        }
        .grand-total {
            font-size: 14pt;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }
        .receipt-footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }
        .signature-block {
            flex: 1;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 50px;
        }
        .signature-name {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-style: italic;
            margin-top: 70px;
        }
        .receipt-notes {
            margin-top: 30px;
            font-size: 10pt;
            border-top: 1px dashed #ddd;
            padding-top: 20px;
            font-style: italic;
            color: #666;
        }
        .print-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
        }
        .print-button i {
            margin-right: 8px;
        }
        .return-button {
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 10px 20px;
            background-color: #6b7280;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            text-decoration: none;
        }
        .return-button i {
            margin-right: 8px;
        }
        @media print {
            .print-button, .return-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="print-header">
            <h1 class="company-name">CÔNG TY SÁCH GIÁO KHOA</h1>
            <p class="company-info">Địa chỉ: 123 Đường Giáo Dục, Quận Học Tập, TP. Hồ Chí Minh</p>
            <p class="company-info">Điện thoại: (028) 1234 5678 | Email: info@sachgiaokhoavn.com</p>
        </div>

        <h2 class="receipt-title">PHIẾU NHẬP KHO</h2>

        <div class="receipt-info">
            <div class="info-group">
                <div class="info-label">Số phiếu nhập:</div>
                <div class="info-value"><?php echo $receiptNumber; ?></div>
                
                <div class="info-label">Ngày nhập:</div>
                <div class="info-value"><?php echo $receiptDate; ?></div>
            </div>
            
            <div class="info-group">
                <div class="info-label">Người nhập:</div>
                <div class="info-value">
                    <?php echo htmlspecialchars($nguoiNhap['fullName'] ?? $nguoiNhap['userName'] ?? "ID: ".$receipt['idNguoiNhap']); ?>
                </div>

                <div class="info-label">Tổng số mặt hàng:</div>
                <div class="info-value"><?php echo count($items); ?> mặt hàng</div>
            </div>
        </div>

        <table class="receipt-table">
            <thead>
                <tr>
                    <th class="item-code">Mã sách</th>
                    <th class="item-name">Tên sách</th>
                    <th class="item-price">Giá nhập</th>
                    <th class="item-quantity">Số lượng</th>
                    <th class="item-total">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $item): 
                    $book = $bookDB->getBookById($item['idBook']);
                    $supplier = $supplierTable->getSupplierById($item['idCungCap']);
                    $subtotal = $item['soluong'] * $item['gianhap'];
                ?>
                <tr>
                    <td class="item-code">
                        <?php echo htmlspecialchars($book['id'] ?? '-'); ?>
                        <div style="font-size: 8pt; color: #666;">
                            <?php echo htmlspecialchars($supplier['name'] ?? '-'); ?>
                        </div>
                    </td>
                    <td class="item-name">
                        <?php echo htmlspecialchars($book['bookName'] ?? 'Unknown Book'); ?>
                        <?php if (!empty($book['classNumber'])): ?>
                        <div style="font-size: 9pt; color: #666;">Lớp <?php echo htmlspecialchars($book['classNumber']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="item-price">
                        <?php echo number_format($item['gianhap'], 0, ',', '.'); ?>₫
                    </td>
                    <td class="item-quantity">
                        <?php echo number_format($item['soluong']); ?>
                    </td>
                    <td class="item-total">
                        <?php echo number_format($subtotal, 0, ',', '.'); ?>₫
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($items)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px 0;">
                        <em>Không có mặt hàng nào trong phiếu nhập này.</em>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="receipt-summary">
            <div class="summary-row">
                <div class="summary-label">Tổng số lượng:</div>
                <div class="summary-value"><?php echo number_format($totalQuantity); ?> quyển</div>
            </div>
            
            <div class="summary-row grand-total">
                <div class="summary-label">Tổng giá trị:</div>
                <div class="summary-value"><?php echo number_format($totalValue, 0, ',', '.'); ?>₫</div>
            </div>
        </div>

        <div class="receipt-footer">
            <div class="signature-block">
                <div class="signature-title">Người lập phiếu</div>
                <div class="signature-name">
                    <?php echo htmlspecialchars($nguoiNhap['fullName'] ?? $nguoiNhap['userName'] ?? ''); ?>
                </div>
            </div>
            
            <div class="signature-block">
                <div class="signature-title">Người giao hàng</div>
                <div class="signature-name"></div>
            </div>
            
            <div class="signature-block">
                <div class="signature-title">Thủ kho</div>
                <div class="signature-name"></div>
            </div>
        </div>

        <div class="receipt-notes">
            <p>Ghi chú: Phiếu này được in tự động từ hệ thống quản lý kho hàng. Mọi thông tin chi tiết vui lòng liên hệ phòng kế toán.</p>
            <p>Ngày in: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>

    <a href="thongTinPhieuNhap.php" class="return-button">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
    
    <button onclick="window.print()" class="print-button">
        <i class="fas fa-print"></i> In Phiếu Nhập
    </button>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        // Auto print when page loads (optional, uncomment if needed)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>