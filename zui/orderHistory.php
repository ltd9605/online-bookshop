<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: ../");
  exit();
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ltw_ud2";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$user_id = $_SESSION["user_id"];

?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>lịch sử mua hàng</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
  <?php include_once "../components/header2.php";
  ?>
  <!--  1 => 'Đang xử lý',
        2 => 'Đang được giao',
        3 => 'Giao hàng thành công',
        4 => 'Đơn hàng đã hủy' -->
  <div class="bg-gray-50 p-4 ">
    <div class="max-w-4xl mx-auto mb-4 ">
      <?php $currentStatus = isset($_GET['status']) ? (int)$_GET['status'] : null; ?>

      <div class="flex justify-between border-b">
        <a href="orderHistory.php" class="tab-button px-4 py-2 <?php if (!$currentStatus) echo 'text-red-500 font-semibold'; ?>">
          Tất cả
        </a>
        <a href="orderHistory.php?status=1" class="tab-button px-4 py-2 <?php if ($currentStatus == 1) echo 'text-red-500 font-semibold'; ?>">
          Đang chờ xác nhận
        </a>
        <a href="orderHistory.php?status=2" class="tab-button px-4 py-2 <?php if ($currentStatus == 2) echo 'text-red-500 font-semibold'; ?>">
          Chờ giao hàng
        </a>
        <a href="orderHistory.php?status=3" class="tab-button px-4 py-2 <?php if ($currentStatus == 3) echo 'text-red-500 font-semibold'; ?>">
          Hoàn thành
        </a>
        <a href="orderHistory.php?status=4" class="tab-button px-4 py-2 <?php if ($currentStatus == 4) echo 'text-red-500 font-semibold'; ?>">
          Đơn bị hủy
        </a>
      </div>


      <div class="mt-2 bg-gray-100 rounded flex items-center px-4 py-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1110.5 3a7.5 7.5 0 016.15 13.65z" />
        </svg>
        <input type="text" placeholder="Bạn có thể tìm kiếm theo tên Shop, ID đơn hàng hoặc Tên Sản phẩm" class="w-full bg-gray-100 focus:outline-none text-sm text-gray-700">
      </div>
    </div>
    <?php
    $query = "
    select *
    from hoadon
    where hoadon.idUser=
    " . $_SESSION["user_id"];
    if ($currentStatus) {
      $query .= " AND hoadon.statusBill = $currentStatus";
    }
    $result = mysqli_query($conn, $query);
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
    ?>
        <!-- Order Card -->
        <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-xl p-4 mt-4">
          <div class="bg-gray-50 px-4 py-2 rounded-md shadow-sm text-gray-700 text-lg inline-block mb-2">
            <p>

              #MD
              <?php
              echo $row["idBill"];
              ?>
            </p>
            📅 Ngày đặt hàng: <?php echo $row["create_at"] ?>
          </div>


          <div class="flex justify-between items-center  mb-4">
            <div class="flex items-center gap-3">
              <a href="/LTW-UD2" class="border border-gray-300 text-base px-3 py-1 rounded hover:bg-gray-100 inline-block">
                🏪 Xem Shop
              </a>

            </div>

            <div class="flex items-center gap-3">
              <span class="text-green-500 flex items-center gap-1">
                <span class="{{TrangThaiColor}}"><?php echo $row["statusBill"] ?></span>

              </span>
            </div>
          </div>

          <?php
          $query2 = "SELECT * FROM chitiethoadon
      JOIN hoadon ON hoadon.idBill = chitiethoadon.idHoadon
      LEFT JOIN hoadon_trangthai ON hoadon_trangthai.idBill = hoadon.idBill
      JOIN books ON books.id = chitiethoadon.idBook
      WHERE chitiethoadon.idHoadon = {$row['idBill']}
        AND hoadon.idUser = $user_id 
      ";
          if ($currentStatus) {
            $query2 .= " AND hoadon.statusBill = $currentStatus";
          }
          $query2 .= " ORDER BY hoadon.ngay_cap_nhat DESC";

          $result2 = mysqli_query($conn, $query2);
          if ($result2->num_rows > 0) {
            while ($row2 = $result2->fetch_assoc()) {
          ?>
              <div class="flex justify-between  pb-4 mb-4 border-t pt-4 mt-4">
                <div class="flex items-start gap-4">
                  <img src="<?php echo $row2["imageURL"] ?>" alt="math" class="w-24 h-24 object-cover rounded border">
                  <div>
                    <h2 class="font-semibold text-gray-700">Tên : <?php echo $row2["bookName"] ?></h2>

                    <p class="text-red-500 font-semibold mt-2">Giá : <?php echo number_format($row2["currentPrice"], 0, ',', '.'); ?> đ </p>
                    <p class="font-semibold mt-2">Số lượng : <?php echo $row2["amount"] ?></p>
                  </div>
                </div>

                <div class="flex flex-col items-end justify-between">
                  <div>
                    <span class="text-lg font-medium text-gray-700">Thành tiền:</span>

                    <span class="text-xl font-bold text-red-600 k"><?php echo number_format($row2["amount"] * ($row2["currentPrice"]), 0, ',', '.'); ?> đ</span>
                  </div>
                  <div class="flex gap-2 mt-4">
                    <form method="POST" action="" style="display: inline;">
                      <button onclick="themVaoGio(<?= $row2['id'] ?>)" style="background-color: #70b0fb;" class="hover:bg-red-600 text-white px-4 py-2 rounded-xl font-medium ">
                        Mua Lại 🛒
                      </button>
                    </form>


                  </div>
                </div>
              </div>

          <?php }
          } ?>
          <?php
          $choPhepHuy = false;
          if ($row["statusBill"] == 2) {
            $createTime = strtotime($row["create_at"]);
            $now = time();
            $diffHours = ($now - $createTime) / 3600;
            if ($diffHours <= 4) {
              $choPhepHuy = true;
            }
          } elseif ($row["statusBill"] == 1) {
            $choPhepHuy = true;
          }
          ?>

          <div class="flex items-center justify-between border-t pt-4 mt-4">
            <!-- Tổng tiền -->
            <div>
              <span class="text-lg font-medium text-gray-700 mr-4">Tổng :</span>
              <span class="text-xl font-bold text-red-600">
                <?= number_format($row["totalBill"], 0, ',', '.'); ?> đ
              </span>
            </div>

            <!-- Nút hủy -->
            <!-- <?php if ($choPhepHuy): ?>
          <form action="../controllers/huydon.php" method="POST">
            <input type="hidden" name="IDHoaDonXuat" value="<?= htmlspecialchars($row['idBill']) ?>">
            <button type="submit"
              class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-red-600 bg-red-50 border border-red-300 rounded-xl shadow-sm hover:bg-red-100 hover:text-red-700 transition-all duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12" />
              </svg>
              Hủy đơn
            </button>
          </form>
        <?php endif; ?> -->
          </div>


        </div>
  </div>

<?php }
    } ?>
<?php include_once "../components/footer.php"; ?>
<script>
  function formatCurrencyVND(amount) {
    if (typeof amount !== 'number') {
      amount = parseFloat(amount);
    }

    return amount.toLocaleString('vi-VN', {
      maximumFractionDigits: 0
    }) + ' đ';
  }

  document.addEventListener("DOMContentLoaded", function() {
    const tabs = document.querySelectorAll(".tab-button");
    const activeClass = "text-red-500 border-b-2 border-red-500 font-medium";

    tabs.forEach(tab => {
      tab.addEventListener("click", function() {
        tabs.forEach(t => t.classList.remove(...activeClass.split(" ")));
        this.classList.add(...activeClass.split(" "));
      });
    });
  });
</script>

<script>
  function themVaoGio(bookId) {
    fetch('../controllers/add_to_cart.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'book_id=' + bookId
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.message);
          // 👉 Update số lượng
          const cartCountSpan = document.getElementById('cart-count');
          if (cartCountSpan) {
            cartCountSpan.innerText = data.count;
            cartCountSpan.style.display = data.count > 0 ? 'inline-block' : 'none';
          }
        } else {
          alert("❌ " + data.message);
        }
      })
      .catch(err => {
        console.error("Lỗi khi gửi request:", err);
        alert("❌ Có lỗi khi thêm vào giỏ hàng.");
      });
  }
</script>

</body>

</html>