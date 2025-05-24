<?php 
// filepath: c:\xampp\htdocs\LTW-UD2\zui\cart.php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: ../");
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng của bạn - Sách Giáo Khoa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <?php include_once "../components/header2.php"; ?>
    
    <div class="flex-grow container mx-auto px-4 py-10">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center sm:text-left">Giỏ hàng của bạn</h1>
        
        <?php
        if(isset($_SESSION["user_id"])) {
            $sql = "SELECT * FROM cart WHERE idUser = " . $_SESSION["user_id"];
            $result = mysqli_query($conn, $sql);
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $sql2 = "SELECT * FROM cartitems, books WHERE books.id = cartitems.bookId AND cartitems.cartId = " . $row["idCart"];
                    $result2 = mysqli_query($conn, $sql2);
                    
                    if ($result2->num_rows > 0) {
                        // Cart has items
                        ?>
                        <form  id="cartForm">
                            <div class="flex flex-col lg:flex-row gap-6 max-w-7xl mx-auto">
                                <!-- Cart Items Section -->
                                <div class="lg:w-2/3 w-full bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="p-6">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                            <i class="fas fa-shopping-cart text-pink-500"></i>
                                            Sản phẩm
                                            <span class="bg-gray-100 text-gray-700 text-sm rounded-full px-2 py-0.5 ml-2">
                                                <?php echo $result2->num_rows; ?> sản phẩm
                                            </span>
                                        </h2>
                                        
                                        <div class="divide-y divide-gray-100 space-y-5">
                                            <?php while ($row2 = $result2->fetch_assoc()) { ?>
                                                <!-- Product Item -->
                                                <div class="flex items-center py-5 first:pt-0 group transition-all duration-300 hover:bg-gray-50 rounded-lg p-2"
                                                    data-book-id="<?= $row2['bookId'] ?>" data-cart-id="<?= $row2['cartId'] ?>">
                                                    
                                                    <!-- Product Image -->
                                                    <div class="w-24 h-32 flex-shrink-0 mr-4">
                                                        <img src="<?php echo $row2['imageURL']; ?>"
                                                            class="w-full h-full object-cover rounded-lg shadow-sm"
                                                            alt="<?php echo htmlspecialchars($row2['bookName']); ?>">
                                                    </div>
                                                    
                                                    <!-- Product Info -->
                                                    <div class="flex-grow">
                                                        <h3 class="text-lg font-medium text-gray-800 mb-1 line-clamp-2">
                                                            <?php echo htmlspecialchars($row2['bookName']); ?>
                                                        </h3>
                                                        
                                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                                            <!-- Price -->
                                                            <div>
                                                                <p class="text-pink-600 font-semibold item-price" data-price="<?php echo $row2['currentPrice']; ?>">
                                                                    <?php echo number_format($row2['currentPrice'], 0, ',', '.'); ?>đ
                                                                    
                                                                    <?php if ($row2['oldPrice'] > $row2['currentPrice']) { ?>
                                                                        <span class="text-xs text-gray-400 line-through ml-2">
                                                                            <?php echo number_format($row2['oldPrice'], 0, ',', '.'); ?>đ
                                                                        </span>
                                                                        <span class="text-xs text-green-600 ml-1">
                                                                            <?php echo round(($row2['oldPrice'] - $row2['currentPrice']) / $row2['oldPrice'] * 100); ?>% giảm
                                                                        </span>
                                                                    <?php } ?>
                                                                </p>
                                                            </div>
                                                            
                                                            <!-- Quantity Controls -->
                                                            <div class="flex items-center gap-3">
                                                                <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                                                    <button type="button" class="decrease px-3 py-1 bg-gray-50 hover:bg-gray-100 text-gray-600 transition-colors duration-150 focus:outline-none">
                                                                        <i class="fas fa-minus text-xs"></i>
                                                                    </button>
                                                                    
                                                                    <span class="quantity px-3 py-1 w-10 text-center font-medium text-gray-700">
                                                                        <?php echo $row2['amount']; ?>
                                                                    </span>
                                                                    
                                                                    <button type="button" class="increase px-3 py-1 bg-gray-50 hover:bg-gray-100 text-gray-600 transition-colors duration-150 focus:outline-none">
                                                                        <i class="fas fa-plus text-xs"></i>
                                                                    </button>
                                                                </div>
                                                                
                                                                <div class="text-pink-600 font-medium text-base whitespace-nowrap item-total"
                                                                    data-price="<?= $row2['currentPrice'] * $row2['amount']; ?>"
                                                                    data-price-per-item="<?= $row2['currentPrice']; ?>">
                                                                    <?= number_format($row2['currentPrice'] * $row2['amount'], 0, ',', '.'); ?>đ
                                                                </div>
                                                                
                                                                <button type="button" class="delete-btn ml-2 text-gray-400 hover:text-red-500 transition-colors duration-150 focus:outline-none"
                                                                    onclick="xoaSanPham(this)" aria-label="Xóa sản phẩm"
                                                                    title="Xóa sản phẩm">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cart Summary Section -->
                                <div class="lg:w-1/3 w-full bg-white rounded-2xl shadow-sm border border-gray-200 h-fit sticky top-4">
                                    <div class="p-6">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                            <i class="fas fa-receipt text-pink-500"></i>
                                            Thông tin đơn hàng
                                        </h2>
                                        
                                        <div class="space-y-3">
                                            <div class="flex justify-between text-gray-600">
                                                <span>Tạm tính</span>
                                                <span class="total-amount font-medium">
                                                    <?php echo number_format($row["totalPrice"], 0, ',', '.'); ?>đ
                                                </span>
                                            </div>
                                            
                                            <div class="flex justify-between text-gray-600">
                                                <span>Phí vận chuyển</span>
                                                <span class="font-medium text-green-600">Miễn phí</span>
                                            </div>
                                            
                                            <hr class="my-4 border-gray-200" />
                                            
                                            <div class="flex justify-between font-semibold text-lg">
                                                <span>Tổng cộng</span>
                                                <input type="hidden" name="tongTien" id="tongTienInput" value="<?= $row["totalPrice"] ?>" />
                                                <span class="total-amount text-pink-600">
                                                    <?php echo number_format($row["totalPrice"], 0, ',', '.'); ?>đ
                                                </span>
                                            </div>
                                            
                                            <p class="text-xs text-gray-500 mt-2">
                                                (Đã bao gồm VAT nếu có)
                                            </p>
                                        </div>
                                        
                                        <div class="mt-6 space-y-3">
                                            <button id="submitCartBtn" type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-pink-500 to-rose-500 text-white font-medium rounded-xl shadow-sm hover:shadow-md transform transition-all duration-200 ease-in-out hover:translate-y-[-2px] focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-opacity-50">
                                                <i class="fas fa-credit-card"></i>
                                                Thanh toán ngay
                                            </button>
                                            
                                            <a href="../" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors duration-200 focus:outline-none">
                                                <i class="fas fa-arrow-left"></i>
                                                Tiếp tục mua sắm
                                            </a>
                                        </div>
                                        
                                        <div class="mt-6 bg-gray-50 rounded-lg p-3 text-sm text-gray-600">
                                            <div class="flex items-center gap-2 mb-2">
                                                <i class="fas fa-shield-alt text-green-600"></i>
                                                <span class="font-medium">Mua sắm an toàn</span>
                                            </div>
                                            <ul class="list-disc list-inside space-y-1 text-xs pl-1 text-gray-500">
                                                <li>Thanh toán bảo mật</li>
                                                <li>Giao hàng nhanh chóng</li>
                                                <li>Sản phẩm chính hãng 100%</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php
                    } else {
                        // Cart is empty
                        ?>
                        <div class="text-center py-16 max-w-md mx-auto">
                            <div class="text-gray-300 text-6xl mb-6">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h2 class="text-2xl font-medium text-gray-700 mb-4">Giỏ hàng của bạn đang trống</h2>
                            <p class="text-gray-500 mb-8">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
                            <a href="../" class="inline-flex items-center justify-center px-6 py-3 bg-pink-600 text-white font-medium rounded-xl hover:bg-pink-700 transition-colors duration-200 focus:outline-none">
                                <i class="fas fa-book mr-2"></i>
                                Khám phá sách ngay
                            </a>
                        </div>
                        <?php
                    }
                }
            } else {
                // User has no cart
                ?>
                <div class="text-center py-16 max-w-md mx-auto">
                    <div class="text-gray-300 text-6xl mb-6">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h2 class="text-2xl font-medium text-gray-700 mb-4">Giỏ hàng của bạn đang trống</h2>
                    <p class="text-gray-500 mb-8">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
                    <a href="../" class="inline-flex items-center justify-center px-6 py-3 bg-pink-600 text-white font-medium rounded-xl hover:bg-pink-700 transition-colors duration-200 focus:outline-none">
                        <i class="fas fa-book mr-2"></i>
                        Khám phá sách ngay
                    </a>
                </div>
                <?php
            }
        }
        ?>
    </div>

    <?php include_once "../components/footer.php"; ?>
    <script>
    document.getElementById('cartForm').addEventListener('submit', function(e) {
        e.preventDefault(); 
        let hasItem = false;
        const quantities = document.querySelectorAll('.quantity');

        quantities.forEach(span => {
            const amount = parseInt(span.textContent.trim());
            if (amount > 0) hasItem = true;
        });

        if (!hasItem) {
            alert("Không có sản phẩm nào trong giỏ hàng.");
            return;
        }
        const form = e.target;
        const formData = new FormData(form);
        window.location.href = "payment.php"
    });
    </script>

    <script>
        function formatCurrency(value) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'decimal',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value) + 'đ';
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.increase, .decrease').forEach((button) => {
                button.addEventListener('click', function() {
                    const parent = this.closest('[data-book-id][data-cart-id]');
                    if (!parent) return;

                    const bookId = parent.dataset.bookId;
                    const cartId = parent.dataset.cartId;
                    const action = this.classList.contains('increase') ? 'increase' : 'decrease';

                    const quantitySpan = parent.querySelector('.quantity');
                    let currentQty = parseInt(quantitySpan.textContent);

                    if (action === 'increase') {
                        currentQty++;
                    } else if (currentQty >= 1) {
                        currentQty--;
                    }

                    quantitySpan.textContent = currentQty;
                    const amount = currentQty;

                    fetch('../controllers/update_cart.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `book_id=${bookId}&cartId=${cartId}&action=${action}&amount=${amount}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            
                            document.querySelectorAll('.total-amount').forEach(el => {
                                el.textContent = formatCurrency(data.totalPrice);
                            });

                            const itemTotalEl = parent.querySelector('.item-total');
                            const unitPrice = parseFloat(itemTotalEl.dataset.pricePerItem);
                            const itemTotal = unitPrice * amount;

                            itemTotalEl.textContent = formatCurrency(itemTotal);
                            itemTotalEl.dataset.price = itemTotal;
                        } else {
                            alert(data.message || "Có lỗi xảy ra");
                        }
                    });
                });
            });
        });

        function xoaSanPham(button) {
            const productDiv = button.closest('[data-book-id][data-cart-id]');
            const bookId = productDiv.dataset.bookId;
            const cartId = productDiv.dataset.cartId;

            if (!confirm("Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?")) return;

            fetch('../controllers/delete_cart_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `book_id=${bookId}&cartId=${cartId}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    productDiv.style.opacity = '0';
                    productDiv.style.transform = 'translateX(20px)';
                    productDiv.style.transition = 'all 0.3s ease-out';
                    
                    setTimeout(() => {
                        productDiv.remove();
                        
                        const remainingItems = document.querySelectorAll('[data-book-id][data-cart-id]');
                        if (remainingItems.length === 0) {
                            location.reload();
                        }
                    }, 300);

                    document.querySelectorAll('.total-amount').forEach(el => {
                        el.textContent = formatCurrency(data.totalPrice);
                    });
                } else {
                    alert(data.message || 'Xóa sản phẩm thất bại!');
                }
            })
            .catch(err => {
                console.error("Lỗi xoá:", err);
                alert("Có lỗi xảy ra khi xoá sản phẩm.");
            });
        }
    </script>
</body>
</html>