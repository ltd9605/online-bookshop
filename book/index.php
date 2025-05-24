<?php
require_once "../database/database.php";
session_start();
require_once "../database/subject.php";
require_once "../database/book.php";
require_once "../database/cart.php";

// Get book ID from URL parameter
$bookId = isset($_GET["bookId"]) ? $_GET["bookId"] : null;
if ($bookId == null) {
  header("Location: ../index.php");
  exit;
} else {
  $bookTable = new BooksTable();
  $book = $bookTable->getBookById($bookId);
  if ($book == null) {
    header("Location: ../index.php");
    exit;
  }
  $subjectTable = new SubjectsTable();
  $subject = $subjectTable->getSubjectById($book["subjectId"]);
}
// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if (!isset($_SESSION['user_id'])) {
    // Redirect to login if user is not logged in
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    $_SESSION['cart_message'] = "Vui lòng đăng nhập để tiếp tục";
    header("Location: ../login.php");
    exit;
  }
  
  $userId = $_SESSION['user_id'];
  $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
  if ($quantity < 1) $quantity = 1;
  
  $cartTable = new CartTable();
  
  if ($_POST['action'] === 'add_to_cart') {
    if ($cartTable->addItemToCart($userId, $bookId, $quantity)) {
      $_SESSION['cart_message'] = "Đã thêm sản phẩm vào giỏ hàng";
    } else {
      $_SESSION['cart_message'] = "Có lỗi xảy ra khi thêm vào giỏ hàng";
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
  } else if ($_POST['action'] === 'buy_now') {
    // Automatically add item to cart before redirecting to payment
    if ($cartTable->addItemToCart($userId, $bookId, $quantity)) {
      $_SESSION['cart_message'] = "Đã thêm sản phẩm vào giỏ hàng, chuyển đến trang thanh toán";
      header("Location: ../zui/payment.php");
      exit;
    } else {
      $_SESSION['cart_message'] = "Có lỗi xảy ra khi thêm vào giỏ hàng";
      header("Location: " . $_SERVER['REQUEST_URI']);
      exit;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($book['bookName']); ?> - Sách Giáo Khoa</title>
  <link rel="stylesheet" href="../global.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" type='text/css'>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
<?php include_once "../components/header2.php";?>

  <?php if (isset($_SESSION['cart_message'])): ?>
    <div id="cart-message" class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
      <?php echo $_SESSION['cart_message']; ?>
    </div>
    <script>
      setTimeout(() => {
        const message = document.getElementById('cart-message');
        if (message) {
          message.style.opacity = '0';
          message.style.transition = 'opacity 0.5s ease';
          setTimeout(() => message.remove(), 500);
        }
      }, 3000);
    </script>
    <?php unset($_SESSION['cart_message']); ?>
  <?php endif; ?>

  <div class="bg-[#fff1f2] gap-6 flex flex-col p-[7%] items-center">
    <div class="flex flex-col sm:flex-row justify-center w-full gap-6">
      <!-- Book Image Section -->
      <section class="bg-white rounded-lg p-6 flex-1">
        <img src="<?php echo htmlspecialchars($book["imageURL"]); ?>" class="max-h-[600px] w-full object-contain" alt="<?php echo htmlspecialchars($book['bookName']); ?>" />
      </section>

      <!-- Book Information Section -->
      <div class="gap-4 flex flex-col flex-1">
        <section class="bg-white rounded-lg p-6">
          <h1 class="text-2xl font-semibold"><?php echo htmlspecialchars($book['bookName']); ?></h1>
          <div class="mt-2 text-sm text-gray-700">
            <div class="flex flex-row justify-between mr-10">
              <div class="flex flex-col gap-2.5">
                <p>
                  <span class="font-medium">Đã bán:</span>
                  <span class="font-bold"><?php echo $book['quantitySold']; ?></span>
                </p>
                <p>
                  <span class="font-medium">Môn:</span>
                  <span class="font-bold"><?php echo htmlspecialchars($subject["subjectName"]); ?></span>
                </p>
              </div>
              <div class="flex flex-col gap-2.5">
                <p>
                  <span class="font-medium">Đánh giá:</span>
                  <span class="font-bold">
                    <?php 
                    // Add review average rating here if available
                    require_once "../database/review.php";
                    $reviewTable = new ReviewTable();
                    $avgRating = $reviewTable->getAverageRatingByBookId($bookId);
                    if ($avgRating) {
                      echo number_format($avgRating, 1) . ' <i class="fas fa-star text-yellow-500"></i>';
                    } else {
                      echo "Chưa có đánh giá";
                    }
                    ?>
                  </span>
                </p>
                <p>
                  <span class="font-medium">Lớp:</span>
                  <span class="font-bold"><?php echo htmlspecialchars($book["classNumber"]); ?></span>
                </p>
              </div>
            </div>
          </div>
          
          <!-- Price Section -->
          <div class="flex flex-row gap-2.5 items-center mt-4">
            <p class="text-[#c92127] font-bold text-[32px]"><?php echo number_format($book["currentPrice"]); ?> đ</p>
            <p class="old-price line-through"><?php echo number_format($book["oldPrice"]); ?> đ</p>
            <div class="discount-percent bg-red-100 text-red-700 px-2 py-1 rounded text-sm">
              <?php 
                $percent = 100 - ($book["currentPrice"] / $book["oldPrice"] * 100);
                echo -floor($percent) . "%"; 
              ?>
            </div>
          </div>
        </section>

        <!-- Purchase Section -->
        <section class="bg-white rounded-lg p-6">
          <h2 class="text-lg font-semibold mb-4">Thông tin đặt hàng</h2>
          
          <form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?bookId=' . $bookId; ?>" class="flex flex-col gap-4">
            <!-- Quantity Selector -->
            <div class="flex flex-row items-center">
              <span class="text-gray-700 mr-4">Số lượng:</span>
              <div class="flex flex-row border border-gray-300 rounded-md overflow-hidden">
                <button type="button" class="decrement px-3 py-1 bg-gray-100 hover:bg-gray-200 transition">
                  <i class="fas fa-minus text-gray-600"></i>
                </button>
                <span class="counter px-4 py-1 border-x border-gray-300">1</span>
                <input type="hidden" name="quantity" value="1" class="quantity-input">
                <button type="button" class="increment px-3 py-1 bg-gray-100 hover:bg-gray-200 transition">
                  <i class="fas fa-plus text-gray-600"></i>
                </button>
              </div>
            </div>
            
            <!-- Stock Information -->
            <div class="text-sm">
              <span class="font-medium">Tình trạng:</span>
              <span class="text-green-600">
                <?php echo ($book['quantitySold'] > 0) ? "Còn hàng (" . $book['quantitySold'] . ")" : "Hết hàng"; ?>
              </span>
            </div>
            
            <!-- Cart Buttons -->
            <div class="flex gap-2 mt-2">
              <button type="submit" name="action" value="add_to_cart" class="flex-1 border border-red-500 text-red-500 py-3 rounded-lg flex items-center justify-center gap-2 hover:bg-red-50 transition">
                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
              </button>
              <button type="submit" name="action" value="buy_now" class="flex-1 bg-red-500 text-white py-3 rounded-lg hover:bg-red-600 transition">
                Mua ngay
              </button>
            </div>
            
            <!-- Additional Information -->
            <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-gray-600">
              <div class="flex items-center gap-2">
                <i class="fas fa-check-circle text-green-500"></i>
                <span>Chính hãng, đảm bảo chất lượng</span>
              </div>
              <div class="flex items-center gap-2">
                <i class="fas fa-truck text-blue-500"></i>
                <span>Giao hàng toàn quốc</span>
              </div>
              <div class="flex items-center gap-2">
                <i class="fas fa-undo text-orange-500"></i>
                <span>Đổi trả trong 7 ngày</span>
              </div>
              <div class="flex items-center gap-2">
                <i class="fas fa-shield-alt text-purple-500"></i>
                <span>Bảo hành theo quy định</span>
              </div>
            </div>
          </form>
        </section>
        
        <!-- Book Details Section -->
        <section class="bg-white rounded-lg p-6">
          <h2 class="text-lg font-semibold mb-2">Thông tin chi tiết</h2>
          <div class="grid grid-cols-2 gap-2 text-sm">
            <div class="flex flex-col gap-2">
              <p>
                <span class="font-medium">Miêu tả:</span>
                <span><?php echo htmlspecialchars($book["description"] ?? "Không có thông tin"); ?></span>
              </p>
              <p>
                <span class="font-medium">Loại:</span>
                <span><?php echo htmlspecialchars($book["type"] ?? "Không có thông tin"); ?></span>
              </p>
            </div>
            <div class="flex flex-col gap-2">
             
              <p>
                <span class="font-medium">Mã sách:</span>
                <span><?php echo htmlspecialchars($book["isbn"] ?? $book["id"]); ?></span>
              </p>
            </div>
          </div>
        </section>
      </div>
    </div>
    
    <!-- Book Reviews Section -->
    <?php include_once "../zui/BookReviewsSection.php"; ?>
  </div>

  <?php include_once "../components/footer.php"; ?>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Quantity counter functionality
      const decrementBtn = document.querySelector('.decrement');
      const incrementBtn = document.querySelector('.increment');
      const counterEl = document.querySelector('.counter');
      const quantityInput = document.querySelector('.quantity-input');
      
      let quantity = 1;
      
      decrementBtn.addEventListener('click', function() {
        if (quantity > 1) {
          quantity--;
          updateQuantity();
        }
      });
      
      incrementBtn.addEventListener('click', function() {
        // You can add an upper limit if needed
        const maxStock = <?php echo $book['stock'] ?? 100; ?>;
        if (quantity < maxStock) {
          quantity++;
          updateQuantity();
        }
      });
      
      function updateQuantity() {
        counterEl.textContent = quantity;
        quantityInput.value = quantity;
      }
    });
  </script>
</body>
</html>