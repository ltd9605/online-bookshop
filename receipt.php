<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: ./");
  exit();
}
$servername="localhost";
$username="root";
$password="";
$dbname="ltw_ud2";
$conn=new mysqli($servername,$username,$password,$dbname);
if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user information
$user_id = $_SESSION["user_id"];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = $user_result->fetch_assoc();

// Generate receipt number
$orderNumber = "OD" . date('Ymd') . str_pad($user_id, 4, '0', STR_PAD_LEFT);

// Check for hoadonID from session storage or query the latest order
$hoadonID = null;
$sql = "SELECT hoadon.idBill, hoadon.create_at, hoadon.totalBill  
        FROM hoadon 
        WHERE hoadon.idUser = $user_id 
        ORDER BY hoadon.create_at DESC 
        LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hoadonID = $row["idBill"];
    $orderDate = $row["create_at"];
    $totalAmount = $row["totalBill"];
} else {
    // Fallback to cart details if no order found
    $cart_sql = "SELECT * FROM cart WHERE idUser = $user_id";
    $cart_result = mysqli_query($conn, $cart_sql);
    $cartRow = $cart_result->fetch_assoc();
    $orderDate = date('Y-m-d H:i:s');
    $totalAmount = 0;
}

// Get order items
$order_items = [];
if ($hoadonID) {
    $items_sql = "SELECT chitiethoadon.*, books.bookName, books.imageURL, books.currentPrice 
                  FROM chitiethoadon 
                  JOIN books ON books.id = chitiethoadon.idBook 
                  WHERE chitiethoadon.idHoadon = $hoadonID";
    $items_result = mysqli_query($conn, $items_sql);
    
    if ($items_result && $items_result->num_rows > 0) {
        while ($item = $items_result->fetch_assoc()) {
            $order_items[] = $item;
        }
    }
} else {
    // Fallback to cart items
    $items_sql = "SELECT cartitems.*, books.bookName, books.imageURL, books.currentPrice
                  FROM cart
                  JOIN cartitems ON cart.idCart = cartitems.cartId
                  JOIN books ON books.id = cartitems.bookId
                  WHERE cart.idUser = $user_id AND cartitems.amount > 0";
    $items_result = mysqli_query($conn, $items_sql);
    
    if ($items_result && $items_result->num_rows > 0) {
        while ($item = $items_result->fetch_assoc()) {
            $order_items[] = $item;
            $totalAmount += ($item["amount"] * $item["pricePerItem"]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng - Sách Giáo Khoa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        @media print {
            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                font-size: 12pt;
                line-height: 1.5;
                color: #333;
            }
            .no-print {
                display: none;
            }
            .print-container {
                display: block;
            }
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
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
            text-align: center;
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
            width: 10%;
        }
        .receipt-table .item-name {
            width: 45%;
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
            text-align: center;
        }
        .receipt-notes {
            margin-top: 30px;
            font-size: 10pt;
            border-top: 1px dashed #ddd;
            padding-top: 20px;
            font-style: italic;
            color: #666;
        }
        .action-buttons {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <div class="no-print">
        <?php include_once "./components/header2.php"; ?>
    </div>
    
    <div class="flex-grow container mx-auto px-4 py-10">
        <div class="print-container">
            <div class="print-header">
                <h1 class="company-name">SÁCH GIÁO KHOA</h1>
                <p class="company-info">Địa chỉ: 123 Đường Giáo Dục, Quận Học Tập, TP. Hồ Chí Minh</p>
                <p class="company-info">Điện thoại: (028) 1234 5678 | Email: info@sachgiaokhoavn.com</p>
            </div>

            <h2 class="receipt-title">PHIẾU XÁC NHẬN ĐƠN HÀNG</h2>

            <div class="receipt-info">
                <div class="info-group">
                    <div class="info-label">Số đơn hàng:</div>
                    <div class="info-value"><?php echo $orderNumber; ?></div>
                    
                    <div class="info-label">Ngày đặt:</div>
                    <div class="info-value"><?php echo isset($orderDate) ? date('d/m/Y H:i', strtotime($orderDate)) : date('d/m/Y H:i'); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Khách hàng:</div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($user['fullName'] ?? $user['userName'] ?? "ID: ".$user_id); ?>
                    </div>

                    <?php if (!empty($user['phone'])): ?>
                    <div class="info-label">Số điện thoại:</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['phone']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (count($order_items) > 0): ?>
                <table class="receipt-table">
                    <thead>
                        <tr>
                            <th class="item-code">Mã sách</th>
                            <th class="item-name">Tên sách</th>
                            <th class="item-price">Đơn giá</th>
                            <th class="item-quantity">Số lượng</th>
                            <th class="item-total">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalItems = 0;
                        foreach ($order_items as $item): 
                            $bookId = $item["bookId"] ?? $item["idBook"] ?? 0;
                            $amount = $item["amount"] ?? 0;
                            $price = $item["pricePerItem"] ?? $item["currentPrice"] ?? 0;
                            $itemTotal = $amount * $price;
                            $totalItems += $amount;
                        ?>
                        <tr>
                            <td class="item-code"><?php echo $bookId; ?></td>
                            <td class="item-name">
                                <?php echo htmlspecialchars($item["bookName"]); ?>
                            </td>
                            <td class="item-price">
                                <?php echo number_format($price, 0, ',', '.'); ?>đ
                            </td>
                            <td class="item-quantity">
                                <?php echo number_format($amount); ?>
                            </td>
                            <td class="item-total">
                                <?php echo number_format($itemTotal, 0, ',', '.'); ?>đ
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="receipt-summary">
                    <div class="summary-row">
                        <div class="summary-label">Tổng số lượng:</div>
                        <div class="summary-value"><?php echo number_format($totalItems); ?> quyển</div>
                    </div>
                    
                    <div class="summary-row">
                        <div class="summary-label">Phí vận chuyển:</div>
                        <div class="summary-value">Miễn phí</div>
                    </div>
                    
                    <div class="summary-row grand-total">
                        <div class="summary-label">Tổng thanh toán:</div>
                        <div class="summary-value"><?php echo number_format($totalAmount, 0, ',', '.'); ?>đ</div>
                    </div>
                </div>

                <div class="receipt-notes">
                    <p><strong>Chú ý:</strong> Đây là xác nhận đơn hàng của bạn. Vui lòng kiểm tra lại thông tin đơn hàng trước khi tiếp tục thanh toán.</p>
                    <p>Thời gian in: <?php echo date('d/m/Y H:i:s'); ?></p>
                </div>

                <div class="action-buttons no-print">
                    <a href="./zui/cart.php" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors duration-200 focus:outline-none">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại giỏ hàng
                    </a>
                    
                    <button onclick="window.print()" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-500 text-white font-medium rounded-xl hover:bg-gray-600 transition-colors duration-200 focus:outline-none">
                        <i class="fas fa-print"></i>
                        In phiếu xác nhận
                    </button>
                    
                    <a href="./zui/responseOrder.php" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-pink-500 to-rose-500 text-white font-medium rounded-xl shadow-sm hover:shadow-md transform transition-all duration-200 ease-in-out hover:translate-y-[-2px] focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-opacity-50">
                        <i class="fas fa-credit-card"></i>
                        Tiếp tục
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center py-16 max-w-md mx-auto">
                    <div class="text-gray-300 text-6xl mb-6">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h2 class="text-2xl font-medium text-gray-700 mb-4">Không có sản phẩm để thanh toán</h2>
                    <p class="text-gray-500 mb-8">Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm vào giỏ hàng.</p>
                    <a href="./" class="inline-flex items-center justify-center px-6 py-3 bg-pink-600 text-white font-medium rounded-xl hover:bg-pink-700 transition-colors duration-200 focus:outline-none">
                        <i class="fas fa-book mr-2"></i>
                        Khám phá sách ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="no-print">
        <?php include_once "./components/footer.php"; ?>
    </div>

    <script>
        // Check for hoadonID in session storage (from payment.php)
        window.onload = function() {
            document.querySelector('button[onclick="window.print()"]')?.focus();
            
            // Retrieve the hoadonID if it was stored in sessionStorage
            const storedHoadonID = sessionStorage.getItem("hoadonID");
            if (storedHoadonID && !<?php echo $hoadonID ? 'true' : 'false'; ?>) {
                window.location.reload(); // Reload to get fresh data if needed
            }
        };
    </script>
</body>
</html>