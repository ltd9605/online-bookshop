<?php 
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
<?php

  $user_id=$_SESSION["user_id"];
  $sql = "
  SELECT 
    cart.totalPrice, 
    cartitems.amount, 
    books.currentPrice, 
    books.imageURL, 
    books.bookName
  FROM cart
  JOIN cartitems ON cart.idCart = cartitems.cartId
  JOIN books ON books.id = cartitems.bookId
  WHERE cart.idUser = $user_id AND cartitems.amount > 0
";
?>

<?php
  //$sql2 = "SELECT * FROM thongTinGiaoHang where id_user = ".$_SESSION["user_id"];
  //$result = $conn->query($sql2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {},
    }
  }
</script>

<style>
  @keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
  }

  .animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
  }
</style>

</head>
<body>
<?php include_once "../components/header2.php";?>

<div class="min-h-screen bg-gray-100">
  <div class="max-w-3xl mx-auto">

    <div class="bg-white p-6 border-b border-gray-200">
      <div class="border-t-4 border-dashed border-red-300 rounded-t-xl mb-4"></div>

      <div class="flex items-center gap-2 mb-3 text-red-600 font-semibold text-base">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17.657 16.657L13.414 12.414a4 4 0 10-5.657 0l-4.243 4.243a8 8 0 1011.314 0z" />
        </svg>
        Địa Chỉ Nhận Hàng
      </div>

      <?php 
      $query = "SELECT * FROM thongTinGiaoHang WHERE id_user = {$_SESSION['user_id']} AND status = 1";
      $result = $conn->query($query);

      $hasDefault = $result && $result->num_rows > 0;
      $row = $hasDefault ? $result->fetch_assoc() : [
        "id" => "",
        "tennguoinhan" => "",
        "sdt" => "",
        "diachi" => "",
        "huyen" => "",
        "quan" => "",
        "thanhpho" => "",
        "status" => ""
      ];
      ?>

      <div id="showAddressInfor" class="<?= $hasDefault ? '' : 'hidden' ?> flex flex-wrap justify-between items-start text-sm text-gray-800 font-medium">
        <div class="flex-1">
          <input type="hidden" id="submitId_Diachi" value="<?= $row['id'] ?>">
          
            <span class="font-bold text-gray-900" id="submitName"><?= htmlspecialchars($row["tennguoinhan"]) ?></span>
            <span class="text-gray-700"> SĐT: <span id="submitSDT"><?= htmlspecialchars($row["sdt"]) ?></span></span><br>

            <?php
            $parts = array_filter([
              htmlspecialchars($row["diachi"]),
              htmlspecialchars($row["huyen"]),
              htmlspecialchars($row["quan"]),
              htmlspecialchars($row["thanhpho"])
            ]);

            $fullAddress = implode(', ', $parts);
            ?>

            <span id="fullAddress"><?= $fullAddress ?></span>


          <input type="hidden" id="macdinh" name="macdinh" value="<?= $row["status"] ?>">
        </div>

        <div class="flex gap-3 items-center mt-2 sm:mt-0">
          <!-- <span class="text-xs border border-red-500 text-red-500 px-2 py-1 rounded">Mặc Định</span> -->
          <a onclick="toggleAddressPopup()" class="cursor-pointer text-blue-600 text-sm font-medium hover:underline">Thay Đổi</a>
        </div>
      </div>

      <?php if (!$hasDefault): ?>
      <div id="add-address" class="flex flex-wrap justify-between items-start text-sm text-gray-800 font-medium">
        <div>
          <a onclick="toggleAddressPopup()" class="cursor-pointer text-blue-600 text-sm font-medium hover:underline">Thêm</a>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>


  <form method="POST" >
    <div class="bg-gray-100">

      <div class="max-w-3xl mx-auto p-6 bg-white rounded-2xl shadow-md mt-10">

        <!-- PHƯƠNG THỨC THANH TOÁN -->
        <div>
          <h3
            class="text-lg font-semibold border-b pb-2 mb-4 text-gray-800 mt-4"
          >PHƯƠNG THỨC THANH TOÁN</h3>
          <div class="space-y-4">
            <label class="flex items-center space-x-3">
              <input
                type="radio"
                name="payment"
                value="Chuyen khoan"
                class="form-radio h-5 w-5 text-blue-600"
              />
              <span class="flex items-center space-x-2"><img
                  src="https://cdn.tgdd.vn/2020/04/GameApp/image-180x180.png"
                  class="w-6 h-6"
                />
                <span>Ví ZaloPay</span></span>
            </label>
            <label class="flex items-center space-x-3">
              <input
                type="radio"
                name="payment"
                value="Chuyen khoan"
                class="form-radio h-5 w-5 text-blue-600"
              />
              <span class="flex items-center space-x-2"><img
                  src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTp1v7T287-ikP1m7dEUbs2n1SbbLEqkMd1ZA&s"
                  class="w-6 h-6"
                />
                <span>VNPAY</span></span>
            </label>

            <label class="flex items-center space-x-3">
              <input
                type="radio"
                name="payment"
                value="Tien mat"
                class="form-radio h-5 w-5 text-blue-600"
                checked
              />
              <span class="flex items-center space-x-2">💵
                <span>Thanh toán bằng tiền mặt khi nhận hàng</span></span>
            </label>
          </div>
        </div>

      </div>
      <div
        class="mb-10 mt-4 max-w-3xl mx-auto p-6 bg-white shadow-md rounded-lg p-6 font-sans"
      >
        <h2 class="text-lg font-bold text-gray-800 mb-4 uppercase">Kiểm tra lại
          đơn hàng</h2>
        <?php
        $result_sql = mysqli_query($conn, $sql);
        if ($result_sql->num_rows > 0) {
          while ($row = $result_sql->fetch_assoc()) {
            

        ?>
        <div class="flex items-center justify-between border-t border-gray-200 pt-6 pb-6">
        <!-- Hình ảnh sách -->
          <div class="flex items-start gap-4">
            <div class="w-24 h-24 flex-shrink-0">
              <img
                src="<?php echo $row['imageURL']; ?>"
                alt="Sách"
                class="w-full h-full object-cover rounded shadow"
              />
          </div>

          <!-- Thông tin sách -->
          <div class="flex flex-col justify-center">
            <p class="text-gray-800 font-semibold text-base line-clamp-2 max-w-xs">
              Tên: <?php echo htmlspecialchars($row["bookName"]); ?>
            </p>
            <p class="text-sm text-red-600 font-semibold mt-1">
              Giá: <?php echo number_format($row["currentPrice"], 0, ',', '.'); ?> đ
            </p>
            <p class="text-sm text-gray-600 mt-1">
              Số lượng: <?php echo $row["amount"]; ?>
            </p>
          </div>
        </div>

        <!-- Thành tiền -->
        <div class="text-right">
          <span class="text-sm text-gray-500">Thành tiền : </span>
          <span class="text-red-600 text-lg font-bold">
            <?php echo number_format($row["amount"] * $row["currentPrice"], 0, ',', '.'); ?> đ
          </span>
        </div>
      </div>

        <?php
          }}

        ?>

      </div>

      <div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-md z-50">
        <div
          class="max-w-6xl mx-auto flex justify-between items-center px-4 py-3"
        >

          <!-- Checkbox và điều khoản -->
          <label class="flex items-center space-x-2 text-sm text-gray-600">
            <input
              type="checkbox"
              class="form-checkbox h-4 w-4 text-red-600"
              checked
            />
            <span>
              Bằng việc tiến hành Mua hàng, Bạn đã đồng ý với
              <a href="#" class="text-blue-600 hover:underline">Điều khoản & Điều
                kiện của shop</a>
            </span>
          </label>

          <!-- Nút thanh toán -->
          <button
            type="button"
            onclick="return xacNhanThanhToan()"
            class="flex items-center gap-2 px-7 py-3 bg-gradient-to-r from-pink-500 to-red-600 text-white text-lg font-bold rounded-xl shadow-lg hover:shadow-2xl hover:scale-105 active:scale-95 transition-all duration-300"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.25 6.75A2.25 2.25 0 014.5 4.5h15a2.25 2.25 0 012.25 2.25v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75zM2.25 9.75h19.5" />
            </svg>
            Xác nhận thanh toán
          </button>


        </div>

      </div>

    </div>

  </form>
</div>

<div id="addressPopup" class="  fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 hidden transition duration-300 ease-out">
  <div class="animate-fade-in max-w-xl mx-auto bg-white p-6 rounded-xl shadow-md space-y-4 font-sans">
    <h2 class="text-lg font-bold text-gray-800 mb-2">Địa Chỉ Của Tôi</h2>
    <?php 
    $query = "SELECT * FROM thongTinGiaoHang where id_user =".$_SESSION["user_id"] ." ";
    $result = $conn->query($query);
    if($result->num_rows>0){
      while($row=$result->fetch_assoc()){
    ?>
    <div class="parentDiachi flex items-start space-x-3 border-b pb-4">
      <input type="radio" name="diachi" 
      value="<?php echo $row['id'] ?>" 
      class="mt-1 text-red-600" <?php if($row["status"]) echo "checked" ?> />
      
      <div class="flex-1 space-y-1">
        <div class="flex justify-between items-center">
          <span class="showTenNguoiNhan font-semibold text-gray-800">
            <?php echo $row["tennguoinhan"]?>
          </span>
          <a onclick="openEdit(this)" class="text-blue-600 text-sm hover:underline cursor-pointer"
          data-id="<?php echo $row["id_user"]?>"
          data-name="<?php echo $row["tennguoinhan"]?>"
          data-phone="<?php echo $row["sdt"]?>"
          data-address="<?php echo $row["diachi"]?>"
          data-city="<?php echo $row["thanhpho"]?>"
          data-district="<?php echo $row["quan"]?>"
          data-ward="<?php echo $row["huyen"]?>"
          data-status="<?php echo $row["status"]?>"
          >
            Cập nhật
          </a>
        </div>
        <div class="showSDT text-sm text-gray-700">
          SDT : <?php echo $row["sdt"]?>
        </div>
        <div class="text-sm text-gray-600">
          <span><?php echo $row["diachi"]?></span>
        
        <br><?php echo $row["huyen"]?>,<?php echo $row["quan"]?>, TP. <?php echo $row["thanhpho"]?>
        </div>
        <!-- <?php if ($row["status"]==1){?>
        <span class="text-xs border border-red-500 text-red-500 px-2 py-1 rounded inline-block mt-1">Mặc định</span>
        <?php }?> -->
      </div>
    </div>
    <?php }}?>


    <!-- Thêm địa chỉ -->
    <button onclick="toggleAddressForm()" class="flex items-center gap-2 border border-gray-300 text-gray-700 rounded px-4 py-2 mt-2 hover:bg-gray-100 transition">
      <span class="text-xl">＋</span> Thêm Địa Chỉ Mới
    </button>

    <div class="flex justify-end gap-4 mt-6">
      <button type="button" onclick="toggleAddressPopup()" class="px-5 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition">Hủy</button>
      <button 
      onclick="showAddressChecked()"
      class="px-6 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition font-semibold">
        Xác nhận
      </button>
    </div>
  </div>
</div>

<!-- Popup Địa Chỉ Mới -->
<div  id="new-address-form" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 hidden ">
  <div class="bg-white w-full max-w-xl p-6 rounded-xl shadow-lg animate-fade-in space-y-4">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">🏠 Địa chỉ mới</h2>

    <!-- Họ tên + SĐT -->
    <div class="grid grid-cols-2 gap-4">
      
      <input type="text" id="tennguoinhan" placeholder="Họ và tên" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500" />
      <input type="text" id="sdt" placeholder="Số điện thoại" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500" />
    </div>

    <!-- Tỉnh / Quận / Phường -->
    <div class="grid grid-cols-3 gap-4">
      <select name="province" id="province" class="px-4 py-2 border rounded-md text-gray-700 focus:ring-2 focus:ring-blue-500">
        <option value="">Chọn Tỉnh/Thành phố</option>
      </select>
      <select name="district" id="district" class="px-4 py-2 border rounded-md text-gray-700 focus:ring-2 focus:ring-blue-500" disabled>
        <option value="">Chọn Quận/Huyện</option>
      </select>
      <select name="ward" id="ward" class="px-4 py-2 border rounded-md text-gray-700 focus:ring-2 focus:ring-blue-500" disabled>
        <option value="">Chọn Phường/Xã</option>
      </select>
    </div>


    <!-- Địa chỉ cụ thể -->
    <input type="text" id="diachi" placeholder="Địa chỉ cụ thể" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500" />
    
    <!-- Nút hành động -->
    <div class="flex justify-end gap-3 mt-6">
      <button onclick="toggleBack()" class="px-4 py-2 text-gray-600 border rounded hover:bg-gray-100">Trở Lại</button>
      <button onclick="showNewAddress()" class="px-6 py-2 bg-red-600 text-white rounded hover:bg-red-700">Hoàn thành</button>
    </div>
  </div>
</div>

<!-- Popup -->
<div id="updateDiachi" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50">
  <div class="bg-white max-w-md w-full mx-4 p-6 rounded-2xl shadow-md space-y-4 animate-fade-in relative">
    <h2 class="text-xl font-semibold text-gray-800">Cập nhật địa chỉ</h2>

    <!-- Họ tên và số điện thoại -->
    <div class="grid grid-cols-2 gap-4">
      <!-- Họ và tên -->
      <div class="relative">
      <input type="hidden" id="edit_id" />
        <input type="text" id="edit_name" value=""
              class="peer w-full border border-gray-300 rounded-md pt-5 px-3 pb-2 text-sm text-gray-900 placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Họ và tên" />
        <label for="edit_name"
              class="absolute left-3 -top-2.5 bg-white px-1 text-gray-500 text-xs transition-all
                      peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400
                      peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-gray-500">
          Họ và tên
        </label>
      </div>

      <!-- Số điện thoại -->
      <div class="relative">
        <input type="text" id="edit_phone" value=""
              class="peer w-full border border-gray-300 rounded-md pt-5 px-3 pb-2 text-sm text-gray-900 placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Số điện thoại" />
        <label for="edit_phone"
              class="absolute left-3 -top-2.5 bg-white px-1 text-gray-500 text-xs transition-all
                      peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400
                      peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-gray-500">
          Số điện thoại
        </label>
      </div>
    </div>


    <div class="grid grid-cols-3 gap-4">
      <input type="hidden" id="edit_city_bk" />
      <input type="hidden" id="edit_district_bk" />
      <input type="hidden" id="edit_ward_bk" />
      <select name="province" id="edit_city" class="px-4 py-2 border rounded-md text-gray-700 focus:ring-2 focus:ring-blue-500">
        <option value="">Chọn Tỉnh/Thành phố</option>
      </select>
      <select name="district" id="edit_district" class="px-4 py-2 border rounded-md text-gray-700 focus:ring-2 focus:ring-blue-500" disabled>
        <option value="">Chọn Quận/Huyện</option>
      </select>
      <select name="ward" id="edit_ward" class="px-4 py-2 border rounded-md text-gray-700 focus:ring-2 focus:ring-blue-500" disabled>
        <option value="">Chọn Phường/Xã</option>
      </select>
    </div>

    <input type="text" id="edit_address" placeholder="Địa chỉ cụ thể" class="w-full rounded border border-gray-300 text-gray-700 px-6 py-2" value="" />

    <div class="w-full h-48 rounded-lg overflow-hidden">
      <iframe
        src="https://www.google.com/maps?q=506%2F49%2F60C%2C%20L%C3%A1c%20Long%20Qu%C3%A2n%2C%20TP.%20HCM&output=embed"
        class="w-full h-full border-0"
        allowfullscreen=""
        loading="lazy">
      </iframe>
    </div>

    <!-- Mặc định -->
    <!-- <div class="flex items-center space-x-2">
      <input type="checkbox" id="edit_status" value="" />
      <label for="default" class="text-sm text-gray-700">Đặt làm địa chỉ mặc định</label>
    </div> -->

    <!-- Nút -->
    <div class="flex justify-between pt-4">
      <button onclick="togglePopup()" class="px-4 py-2 text-gray-600 border rounded hover:bg-gray-100">Trở Lại</button>
      <button  class="px-6 py-2 bg-red-600 text-white rounded hover:bg-red-700"
      onclick="saveAddress()">
        Hoàn thành
      </button>
    </div>
  </div>
</div>
<script>
    const data = {
    "Đà Nẵng": {
      "Quận Liên Chiểu": ["Hòa Khánh Bắc", "Hòa Khánh Nam", "Hòa Minh", "Hòa Hiệp Bắc", "Hòa Hiệp Nam", "Hòa Hiệp Trung"],
      "Quận Thanh Khê": ["Thanh Khê Đông", "Thanh Khê Tây", "An Khê", "Chính Gián", "Tam Thuận", "Tân Chính", "Thạc Gián", "Vĩnh Trung", "Xuân Hà"],
      "Quận Hải Châu": ["Hải Châu 1", "Hải Châu 2", "Bình Hiên", "Bình Thuận", "Hòa Cường Bắc", "Hòa Cường Nam", "Hòa Thuận Đông", "Hòa Thuận Tây", "Nam Dương", "Phước Ninh", "Thạch Thang", "Thanh Bình", "Thuận Phước"],
      "Quận Sơn Trà": ["An Hải Bắc", "An Hải Đông", "An Hải Tây", "Mân Thái", "Nại Hiên Đông", "Phước Mỹ", "Thọ Quang"],
      "Quận Ngũ Hành Sơn": ["Hòa Hải", "Hòa Quý", "Khuê Mỹ", "Mỹ An"],
      "Quận Cẩm Lệ": ["Hòa An", "Hòa Phát", "Hòa Thọ Đông", "Hòa Thọ Tây", "Hòa Xuân", "Khuê Trung"],
      "Huyện Hòa Vang": ["Hòa Bắc", "Hòa Châu", "Hòa Khương", "Hòa Liên", "Hòa Nhơn", "Hòa Ninh", "Hòa Phong", "Hòa Phú", "Hòa Sơn", "Hòa Tiến"],
      "Huyện đảo Hoàng Sa": ["Hoàng Sa"]
    },

    "Hà Nội": {
      "Quận Hoàn Kiếm": [
        "Phường Hàng Bạc", "Phường Hàng Bông", "Phường Cửa Đông", "Phường Cửa Nam",
        "Phường Đồng Xuân", "Phường Hàng Buồm", "Phường Hàng Đào", "Phường Hàng Gai",
        "Phường Hàng Mã", "Phường Lý Thái Tổ", "Phường Phan Chu Trinh", "Phường Tràng Tiền",
        "Phường Trần Hưng Đạo"
      ],
      "Quận Đống Đa": [
        "Phường Khâm Thiên", "Phường Văn Chương", "Phường Cát Linh", "Phường Hàng Bột",
        "Phường Láng Hạ", "Phường Láng Thượng", "Phường Nam Đồng", "Phường Ngã Tư Sở",
        "Phường Ô Chợ Dừa", "Phường Phương Liên", "Phường Phương Mai", "Phường Quang Trung",
        "Phường Thịnh Hào", "Phường Trung Liệt", "Phường Trung Phụng", "Phường Trung Tự"
      ],
      "Quận Ba Đình": [
        "Phường Cống Vị", "Phường Điện Biên", "Phường Đội Cấn", "Phường Giảng Võ",
        "Phường Kim Mã", "Phường Liễu Giai", "Phường Ngọc Hà", "Phường Ngọc Khánh",
        "Phường Nguyễn Trung Trực", "Phường Phúc Xá", "Phường Quán Thánh", "Phường Thành Công",
        "Phường Trúc Bạch", "Phường Vĩnh Phúc"
      ],
      "Quận Hai Bà Trưng": [
        "Phường Bạch Đằng", "Phường Bạch Mai", "Phường Cầu Dền", "Phường Đống Mác",
        "Phường Đồng Nhân", "Phường Lê Đại Hành", "Phường Minh Khai", "Phường Nguyễn Du",
        "Phường Phố Huế", "Phường Quỳnh Lôi", "Phường Quỳnh Mai", "Phường Thanh Nhàn",
        "Phường Trương Định", "Phường Vĩnh Tuy", "Phường Thanh Lương"
      ],
      "Quận Cầu Giấy": [
        "Phường Dịch Vọng", "Phường Dịch Vọng Hậu", "Phường Mai Dịch", "Phường Nghĩa Đô",
        "Phường Nghĩa Tân", "Phường Quan Hoa", "Phường Trung Hòa", "Phường Yên Hòa"
      ]
    },
    "Hồ Chí Minh": {
      "Quận 1": [
        "Bến Nghé", "Bến Thành", "Cầu Kho", "Cầu Ông Lãnh", "Đa Kao",
        "Nguyễn Cư Trinh", "Phạm Ngũ Lão", "Tân Định", "Bến Thành"
      ],
      "Quận 3": [
        "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
        "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10",
        "Phường 11", "Phường 12", "Phường 13", "Phường 14"
      ],
      "Quận 5": [
        "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
        "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10",
        "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"
      ],
      "Quận 10": [
        "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
        "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10",
        "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"
      ],
      "Quận Bình Thạnh": [
        "Phường 1", "Phường 2", "Phường 3", "Phường 5", "Phường 6",
        "Phường 7", "Phường 11", "Phường 12", "Phường 13", "Phường 14",
        "Phường 15", "Phường 17", "Phường 19", "Phường 21", "Phường 22", "Phường 24", "Phường 25", "Phường 26", "Phường 27", "Phường 28"
      ],
      "Thành phố Thủ Đức": [
        "An Khánh", "An Lợi Đông", "An Phú", "Bình Chiểu", "Bình Thọ",
        "Cát Lái", "Hiệp Bình Chánh", "Hiệp Bình Phước", "Hiệp Phú",
        "Linh Chiểu", "Linh Đông", "Linh Tây", "Linh Trung", "Linh Xuân",
        "Long Bình", "Long Phước", "Long Thạnh Mỹ", "Long Trường",
        "Phú Hữu", "Phước Bình", "Phước Long A", "Phước Long B", "Tăng Nhơn Phú A",
        "Tăng Nhơn Phú B", "Thảo Điền", "Thủ Thiêm", "Trường Thạnh", "Trường Thọ"
      ]
    },
    "Đồng Nai": {
      "Thành phố Biên Hòa": [
        "An Bình", "Bửu Long", "Bửu Hòa", "Bửu Phong", "Hiệp Hòa", "Hòa Bình",
        "Hóa An", "Hố Nai", "Long Bình", "Long Bình Tân", "Quang Vinh", "Tam Hiệp",
        "Tam Hòa", "Tân Biên", "Tân Hiệp", "Tân Hòa", "Tân Mai", "Tân Phong",
        "Tân Tiến", "Thanh Bình", "Thống Nhất", "Trảng Dài", "Trung Dũng"
      ],
      "Huyện Long Thành": [
        "An Phước", "Bình An", "Bình Sơn", "Cẩm Đường", "Lộc An", "Long An",
        "Long Đức", "Long Phước", "Long Thọ", "Phước Bình", "Phước Thái", "Tam An",
        "Tân Hiệp"
      ],
      "Huyện Nhơn Trạch": [
        "Hiệp Phước", "Long Tân", "Phước An", "Phước Khánh", "Phước Thiền",
        "Phú Đông", "Phú Hội", "Phú Hữu", "Phú Thạnh", "Vĩnh Thanh"
      ],
      "Huyện Trảng Bom": [
        "An Viễn", "Bình Minh", "Bình Sơn", "Bàu Hàm", "Cây Gáo", "Đồi 61",
        "Giang Điền", "Hố Nai 3", "Hưng Thịnh", "Quảng Tiến", "Sông Thao",
        "Sông Trầu", "Thanh Bình", "Trảng Bom", "Tây Hòa"
      ],
      "Thành phố Long Khánh": [
        "Bàu Sen", "Bàu Trâm", "Suối Tre", "Xuân An", "Xuân Bình", "Xuân Hòa",
        "Xuân Lập", "Xuân Tân", "Xuân Thanh", "Xuân Trung", "Xuân Hưng"
      ]
    },
    "Bình Dương": {
      "Thành phố Thủ Dầu Một": [
        "Phường Chánh Nghĩa", "Phường Hiệp An", "Phường Hiệp Thành", "Phường Hòa Phú",
        "Phường Phú Cường", "Phường Phú Hòa", "Phường Phú Lợi", "Phường Phú Mỹ",
        "Phường Phú Tân", "Phường Tân An", "Phường Tương Bình Hiệp"
      ],
      "Thành phố Dĩ An": [
        "Phường An Bình", "Phường Bình An", "Phường Bình Thắng", "Phường Dĩ An",
        "Phường Đông Hòa", "Phường Tân Bình", "Phường Tân Đông Hiệp"
      ],
      "Thành phố Thuận An": [
        "Phường An Phú", "Phường Bình Chuẩn", "Phường Bình Hòa", "Phường Hưng Định",
        "Phường Lái Thiêu", "Phường Thuận Giao", "Phường Vĩnh Phú"
      ],
      "Thị xã Bến Cát": [
        "Phường Chánh Phú Hòa", "Phường Hòa Lợi", "Phường Mỹ Phước", "Phường Tân Định",
        "Xã An Điền", "Xã An Tây"
      ],
      "Thị xã Tân Uyên": [
        "Phường Hội Nghĩa", "Phường Khánh Bình", "Phường Khánh Hòa", "Phường Phú Chánh",
        "Phường Tân Hiệp", "Phường Tân Phước Khánh", "Phường Thái Hòa", "Phường Thạnh Phước",
        "Phường Thạnh Hội", "Phường Uyên Hưng", "Xã Bạch Đằng", "Xã Vĩnh Tân"
      ],
      "Huyện Bàu Bàng": [
        "Xã Cây Trường II", "Xã Hưng Hòa", "Xã Lai Hưng", "Xã Lai Uyên", 
        "Xã Long Nguyên", "Xã Tân Hưng", "Xã Trừ Văn Thố"
      ],
      "Huyện Bắc Tân Uyên": [
        "Xã Bình Mỹ", "Xã Hiếu Liêm", "Xã Lạc An", "Xã Tân Bình",
        "Xã Tân Định", "Xã Tân Lập", "Xã Tân Mỹ", "Xã Thường Tân"
      ],
      "Huyện Dầu Tiếng": [
        "Thị trấn Dầu Tiếng", "Xã An Lập", "Xã Định An", "Xã Định Hiệp",
        "Xã Định Thành", "Xã Long Hòa", "Xã Long Tân", "Xã Minh Hòa", 
        "Xã Minh Tân", "Xã Minh Thạnh", "Xã Thanh An", "Xã Thanh Tuyền"
      ]
    },
    "Long An": {
      "Thành phố Tân An": [
        "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", 
        "Phường Tân Khánh", "Xã An Vĩnh Ngãi", "Xã Bình Tâm", 
        "Xã Hướng Thọ Phú", "Xã Khánh Hậu", "Xã Lợi Bình Nhơn", "Xã Nhơn Thạnh Trung"
      ],
      "Thị xã Kiến Tường": [
        "Phường 1", "Phường 2", "Phường 3", "Xã Bình Hiệp", 
        "Xã Bình Tân", "Xã Thạnh Hưng", "Xã Thạnh Trị", "Xã Tuyên Thạnh"
      ],
      "Huyện Bến Lức": [
        "Thị trấn Bến Lức", "Xã An Thạnh", "Xã Bình Đức", "Xã Lương Bình", 
        "Xã Lương Hòa", "Xã Mỹ Yên", "Xã Nhựt Chánh", "Xã Phước Lợi", 
        "Xã Tân Bửu", "Xã Tân Hòa", "Xã Thạnh Đức", "Xã Thạnh Hòa", "Xã Thanh Phú"
      ],
      "Huyện Đức Hòa": [
        "Thị trấn Hậu Nghĩa", "Thị trấn Đức Hòa", "Thị trấn Hiệp Hòa", 
        "Xã An Ninh Đông", "Xã An Ninh Tây", "Xã Đức Hòa Đông", "Xã Đức Hòa Hạ", 
        "Xã Đức Hòa Thượng", "Xã Hòa Khánh Đông", "Xã Hòa Khánh Nam", 
        "Xã Hòa Khánh Tây", "Xã Hựu Thạnh", "Xã Lộc Giang", "Xã Mỹ Hạnh Bắc", 
        "Xã Mỹ Hạnh Nam", "Xã Tân Mỹ"
      ],
      "Huyện Cần Giuộc": [
        "Thị trấn Cần Giuộc", "Xã Đông Thạnh", "Xã Long An", "Xã Long Hậu", 
        "Xã Long Phụng", "Xã Long Thượng", "Xã Mỹ Lộc", "Xã Phước Hậu", 
        "Xã Phước Lại", "Xã Phước Lâm", "Xã Phước Lý", "Xã Phước Vĩnh Đông", 
        "Xã Tân Kim", "Xã Tân Tập", "Xã Thuận Thành", "Xã Trường Bình"
      ]
    },
    "Tiền Giang": {
      "Thành phố Mỹ Tho": [
        "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", 
        "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", 
        "Phường Tân Long", "Xã Đạo Thạnh", "Xã Mỹ Phong", "Xã Phước Thạnh", 
        "Xã Tân Mỹ Chánh", "Xã Thới Sơn", "Xã Trung An", "Xã Trung Thạnh"
      ],
      "Thị xã Gò Công": [
        "Phường 1", "Phường 2", "Phường 3", "Xã Bình Đông", "Xã Bình Xuân", 
        "Xã Long Chánh", "Xã Long Hưng", "Xã Tân Trung"
      ],
      "Thị xã Cai Lậy": [
        "Phường 1", "Phường 2", "Xã Cẩm Sơn", "Xã Mỹ Hạnh Đông", "Xã Mỹ Hạnh Trung", 
        "Xã Mỹ Phước Tây", "Xã Nhị Quý", "Xã Tân Bình", "Xã Tân Hội", 
        "Xã Tân Phú", "Xã Thạnh Lộc", "Xã Thanh Hòa"
      ],
      "Huyện Châu Thành": [
        "Thị trấn Tân Hiệp", "Xã Bàn Long", "Xã Bình Đức", "Xã Dưỡng Điềm", 
        "Xã Hòa Tịnh", "Xã Kim Sơn", "Xã Long An", "Xã Long Định", "Xã Long Hưng", 
        "Xã Nhị Bình", "Xã Phú Phong", "Xã Song Thuận", "Xã Tam Hiệp", 
        "Xã Tân Hương", "Xã Tân Lý Đông", "Xã Tân Lý Tây", "Xã Thân Cửu Nghĩa", 
        "Xã Thới Sơn", "Xã Vĩnh Kim"
      ],
      "Huyện Cai Lậy": [
        "Thị trấn Cai Lậy", "Xã Cẩm Sơn", "Xã Hiệp Đức", "Xã Long Tiên", 
        "Xã Mỹ Thành Bắc", "Xã Mỹ Thành Nam", "Xã Ngũ Hiệp", "Xã Phú An", 
        "Xã Phú Cường", "Xã Phú Nhuận", "Xã Tân Hội", "Xã Tân Phong", 
        "Xã Tân Phú", "Xã Thạnh Lộc", "Xã Thanh Hòa"
      ]
    },
    "Bà Rịa - Vũng Tàu": {
      "Thành phố Vũng Tàu": [
        "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", 
        "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", 
        "Phường 12", "Phường Nguyễn An Ninh", "Phường Rạch Dừa", 
        "Phường Thắng Nhất", "Phường Thắng Nhì", "Phường Thắng Tam", 
        "Xã Long Sơn", "Xã đảo Gò Găng"
      ],
      "Thành phố Bà Rịa": [
        "Phường Long Hương", "Phường Long Tâm", "Phường Long Toàn", 
        "Phường Phước Hiệp", "Phường Phước Hưng", "Phường Phước Nguyên", 
        "Phường Phước Trung", "Xã Hòa Long", "Xã Long Phước", 
        "Xã Tân Hưng"
      ],
      "Thị xã Phú Mỹ": [
        "Phường Hắc Dịch", "Phường Mỹ Xuân", "Phường Phú Mỹ", 
        "Phường Phước Hòa", "Phường Tân Phước", "Xã Châu Pha", 
        "Xã Sông Xoài", "Xã Tân Hòa", "Xã Tân Hải", "Xã Tóc Tiên"
      ],
      "Huyện Long Điền": [
        "Thị trấn Long Điền", "Thị trấn Long Hải", "Xã An Ngãi", 
        "Xã An Nhứt", "Xã Phước Hưng", "Xã Phước Tỉnh", 
        "Xã Tam Phước"
      ],
      "Huyện Đất Đỏ": [
        "Thị trấn Đất Đỏ", "Thị trấn Phước Hải", "Xã Long Mỹ", 
        "Xã Long Tân", "Xã Láng Dài", "Xã Lộc An", 
        "Xã Phước Hội", "Xã Phước Long Thọ"
      ],
      "Huyện Xuyên Mộc": [
        "Thị trấn Phước Bửu", "Xã Bàu Lâm", "Xã Bông Trang", 
        "Xã Bưng Riềng", "Xã Hòa Bình", "Xã Hòa Hiệp", 
        "Xã Hòa Hội", "Xã Phước Tân", "Xã Phước Thuận", 
        "Xã Tân Lâm", "Xã Xuyên Mộc"
      ],
      "Huyện Châu Đức": [
        "Thị trấn Ngãi Giao", "Xã Bình Ba", "Xã Bình Giã", 
        "Xã Bình Trung", "Xã Bông Trang", "Xã Cù Bị", 
        "Xã Đá Bạc", "Xã Kim Long", "Xã Láng Lớn", 
        "Xã Nghĩa Thành", "Xã Quảng Thành", "Xã Sơn Bình", 
        "Xã Suối Nghệ", "Xã Suối Rao", "Xã Xà Bang"
      ],
      "Huyện Côn Đảo": [
        "Thị trấn Côn Đảo"
      ]
    },
    "Khánh Hòa": {
      "Thành phố Nha Trang": [
        "Phường Vĩnh Hải", "Phường Vĩnh Nguyên", "Phường Vĩnh Phước",
        "Phường Vĩnh Trường", "Phường Phước Long", "Phường Phước Hải",
        "Phường Phước Tân", "Phường Xương Huân", "Phường Tân Lập",
        "Phường Lộc Thọ", "Phường Ngọc Hiệp", "Phường Vạn Thạnh",
        "Xã Vĩnh Thái", "Xã Vĩnh Hiệp", "Xã Vĩnh Trung", "Xã Vĩnh Lương"
      ],
      "Thành phố Cam Ranh": [
        "Phường Cam Linh", "Phường Cam Lộc", "Phường Cam Lợi", "Phường Cam Nghĩa",
        "Phường Cam Phúc Bắc", "Phường Cam Phúc Nam", "Phường Cam Phú",
        "Phường Ba Ngòi", "Xã Cam Bình", "Xã Cam Thịnh Đông", "Xã Cam Thịnh Tây",
        "Xã Cam Lập", "Xã Cam Thành Nam"
      ],
      "Thị xã Ninh Hòa": [
        "Phường Ninh Hiệp", "Phường Ninh Giang", "Phường Ninh Hà", "Phường Ninh Diêm",
        "Phường Ninh Thủy", "Phường Ninh Hải", "Phường Ninh Sơn", "Phường Ninh Trung",
        "Xã Ninh Phú", "Xã Ninh An", "Xã Ninh Quang", "Xã Ninh Bình", "Xã Ninh Hưng",
        "Xã Ninh Lộc", "Xã Ninh Ích", "Xã Ninh Sim", "Xã Ninh Xuân", "Xã Ninh Thọ"
      ],
      "Huyện Cam Lâm": [
        "Thị trấn Cam Đức", "Xã Cam Thành Bắc", "Xã Cam Hải Đông", "Xã Cam Hải Tây",
        "Xã Cam Hòa", "Xã Cam Hiệp Bắc", "Xã Cam Hiệp Nam", "Xã Cam Lập",
        "Xã Cam Phước Tây", "Xã Cam Tân", "Xã Cam Thành Nam", "Xã Suối Cát",
        "Xã Suối Tân"
      ],
      "Huyện Diên Khánh": [
        "Thị trấn Diên Khánh", "Xã Diên An", "Xã Diên Bình", "Xã Diên Điền",
        "Xã Diên Đồng", "Xã Diên Lạc", "Xã Diên Lâm", "Xã Diên Phú",
        "Xã Diên Sơn", "Xã Diên Tân", "Xã Diên Thạnh", "Xã Diên Thọ",
        "Xã Diên Xuân", "Xã Suối Hiệp", "Xã Suối Tiên"
      ],
      "Huyện Vạn Ninh": [
        "Thị trấn Vạn Giã", "Xã Đại Lãnh", "Xã Vạn Bình", "Xã Vạn Hưng",
        "Xã Vạn Khánh", "Xã Vạn Long", "Xã Vạn Lương", "Xã Vạn Phú",
        "Xã Vạn Phước", "Xã Vạn Thạnh", "Xã Vạn Thắng"
      ],
      "Huyện Khánh Sơn": [
        "Thị trấn Tô Hạp", "Xã Ba Cụm Bắc", "Xã Ba Cụm Nam", "Xã Sơn Bình",
        "Xã Sơn Hiệp", "Xã Sơn Lâm", "Xã Sơn Trung", "Xã Thành Sơn"
      ],
      "Huyện Khánh Vĩnh": [
        "Thị trấn Khánh Vĩnh", "Xã Cầu Bà", "Xã Giang Ly", "Xã Khánh Bình",
        "Xã Khánh Đông", "Xã Khánh Hiệp", "Xã Khánh Nam", "Xã Khánh Phú",
        "Xã Khánh Thượng", "Xã Liên Sang", "Xã Sơn Thái", "Xã Sông Cầu"
      ],
      "Huyện Trường Sa": [
        "Thị trấn Trường Sa", "Xã Song Tử Tây", "Xã Sinh Tồn"
      ]
    },
    "Ninh Thuận": {
      "Thành phố Phan Rang-Tháp Chàm": [
        "Phường Đô Vinh", "Phường Mỹ Hải", "Phường Mỹ Đông", "Phường Tấn Tài",
        "Phường Thanh Sơn", "Phường Phủ Hà", "Phường Đông Hải", "Phường Kinh Dinh",
        "Phường Bảo An", "Xã Thành Hải", "Xã Văn Hải"
      ],
      "Huyện Ninh Hải": [
        "Thị trấn Khánh Hải", "Xã Nhơn Hải", "Xã Tân Hải", "Xã Tri Hải",
        "Xã Thanh Hải", "Xã Vĩnh Hải", "Xã Xuân Hải", "Xã Hộ Hải",
        "Xã Phương Hải", "Xã Bình Hải"
      ],
      "Huyện Ninh Phước": [
        "Thị trấn Phước Dân", "Xã An Hải", "Xã Phước Hải", "Xã Phước Hậu",
        "Xã Phước Hữu", "Xã Phước Sơn", "Xã Phước Thái", "Xã Phước Thuận",
        "Xã Phước Vinh"
      ],
      "Huyện Bác Ái": [
        "Thị trấn Tống Mao (không chính thức, trung tâm hành chính)", "Xã Phước Đại",
        "Xã Phước Chiến", "Xã Phước Thắng", "Xã Phước Thành", "Xã Phước Tân",
        "Xã Phước Tiến", "Xã Phước Trung"
      ],
      "Huyện Thuận Bắc": [
        "Xã Bắc Sơn", "Xã Bắc Phong", "Xã Công Hải", "Xã Lợi Hải",
        "Xã Phước Chiến", "Xã Phước Kháng", "Xã Phước Hà"
      ],
      "Huyện Thuận Nam": [
        "Xã Cà Ná", "Xã Phước Diêm", "Xã Phước Dinh", "Xã Phước Minh",
        "Xã Phước Nam", "Xã Phước Ninh"
      ],
      "Huyện Ninh Sơn": [
        "Thị trấn Tân Sơn", "Xã Hòa Sơn", "Xã Lâm Sơn", "Xã Lương Sơn",
        "Xã Ma Nới", "Xã Mỹ Sơn", "Xã Nhơn Sơn", "Xã Quảng Sơn"
      ]
    },
    "Ninh Bình": {
      "Thành phố Ninh Bình": [
        "Phường Đông Thành", "Phường Nam Thành", "Phường Phúc Thành", "Phường Thanh Bình",
        "Phường Vân Giang", "Phường Bích Đào", "Phường Tân Thành", "Phường Ninh Khánh",
        "Phường Ninh Phong", "Phường Ninh Sơn", "Phường Nam Bình", "Phường Nam Thành",
        "Xã Ninh Nhất", "Xã Ninh Tiến", "Xã Ninh Phúc"
      ],
      "Thành phố Tam Điệp": [
        "Phường Bắc Sơn", "Phường Trung Sơn", "Phường Nam Sơn", "Phường Tây Sơn",
        "Phường Yên Bình", "Phường Tân Bình", "Xã Quang Sơn", "Xã Yên Sơn"
      ],
      "Huyện Hoa Lư": [
        "Thị trấn Thiên Tôn", "Xã Ninh Hải", "Xã Ninh An", "Xã Ninh Giang",
        "Xã Ninh Hòa", "Xã Ninh Mỹ", "Xã Ninh Khang", "Xã Ninh Xuân",
        "Xã Ninh Vân", "Xã Trường Yên"
      ],
      "Huyện Gia Viễn": [
        "Thị trấn Me", "Xã Gia Hòa", "Xã Gia Hưng", "Xã Gia Lạc",
        "Xã Gia Minh", "Xã Gia Phú", "Xã Gia Phương", "Xã Gia Thắng",
        "Xã Gia Thanh", "Xã Gia Tường", "Xã Gia Trung", "Xã Gia Vân", 
        "Xã Gia Xuân", "Xã Liên Sơn"
      ],
      "Huyện Yên Mô": [
        "Thị trấn Yên Thịnh", "Xã Khánh Dương", "Xã Khánh Thượng", "Xã Mai Sơn",
        "Xã Yên Đồng", "Xã Yên Hòa", "Xã Yên Lâm", "Xã Yên Mạc", 
        "Xã Yên Mỹ", "Xã Yên Nhân", "Xã Yên Phong", "Xã Yên Thái", 
        "Xã Yên Thành", "Xã Yên Từ"
      ],
      "Huyện Kim Sơn": [
        "Thị trấn Phát Diệm", "Xã Chất Bình", "Xã Cồn Thoi", "Xã Định Hóa",
        "Xã Đồng Hướng", "Xã Hồi Ninh", "Xã Kim Chính", "Xã Kim Đông",
        "Xã Kim Hải", "Xã Kim Mỹ", "Xã Kim Tân", "Xã Kim Trung", 
        "Xã Lưu Phương", "Xã Như Hòa", "Xã Tân Thành"
      ],
      "Huyện Nho Quan": [
        "Thị trấn Nho Quan", "Xã Cúc Phương", "Xã Đồng Phong", "Xã Gia Lâm",
        "Xã Gia Thủy", "Xã Gia Sơn", "Xã Kỳ Phú", "Xã Lạng Phong",
        "Xã Phú Long", "Xã Phú Sơn", "Xã Phú Lộc", "Xã Quỳnh Lưu",
        "Xã Sơn Lai", "Xã Sơn Hà", "Xã Sơn Thành", "Xã Văn Phú", 
        "Xã Văn Phong", "Xã Xích Thổ", "Xã Yên Quang"
      ]
    },
    "Hà Tĩnh": {
      "Thành phố Hà Tĩnh": ["Phường Bắc Hà", "Phường Nam Hà"],
      "Huyện Hương Sơn": ["Thị trấn Phố Châu", "Xã Sơn Tây"]
    },

    "Hà Giang": {
      "Thành phố Hà Giang": ["Phường Trần Phú", "Phường Nguyễn Trãi"],
      "Huyện Đồng Văn": ["Thị trấn Đồng Văn", "Xã Lũng Cú"]
    },

    "Lào Cai": {
      "Thành phố Lào Cai": ["Phường Bắc Cường", "Phường Nam Cường"],
      "Huyện Sa Pa": ["Thị trấn Sa Pa", "Xã Tả Phìn"]
    },

    "Thái Nguyên": {
      "Thành phố Thái Nguyên": ["Phường Hoàng Văn Thụ", "Phường Tân Thịnh"],
      "Huyện Đại Từ": ["Thị trấn Hùng Sơn", "Xã Phú Lạc"]
    },
    "An Giang": {
      "Thành phố Long Xuyên": [
        "Phường Mỹ Bình", "Phường Mỹ Long", "Phường Mỹ Xuyên", "Phường Đông Xuyên",
        "Phường Bình Khánh", "Phường Mỹ Thới", "Xã Mỹ Hòa Hưng"
      ],
      "Thành phố Châu Đốc": [
        "Phường Châu Phú A", "Phường Châu Phú B", "Phường Núi Sam", "Phường Vĩnh Mỹ",
        "Xã Vĩnh Ngươn", "Xã Vĩnh Tế"
      ],
      "Thị xã Tân Châu": [
        "Phường Long Thạnh", "Phường Long Hưng", "Phường Long Phú", "Xã Tân An",
        "Xã Châu Phong", "Xã Phú Lộc"
      ],
      "Huyện An Phú": [
        "Thị trấn An Phú", "Thị trấn Long Bình", "Xã Khánh An", "Xã Khánh Bình",
        "Xã Phú Hữu", "Xã Phú Hội", "Xã Quốc Thái"
      ],
      "Huyện Châu Phú": [
        "Thị trấn Cái Dầu", "Xã Bình Chánh", "Xã Bình Long", "Xã Bình Mỹ",
        "Xã Bình Phú", "Xã Bình Thủy", "Xã Đào Hữu Cảnh", "Xã Thạnh Mỹ Tây"
      ],
      "Huyện Châu Thành": [
        "Thị trấn An Châu", "Xã An Hòa", "Xã Bình Hòa", "Xã Cần Đăng",
        "Xã Hòa Bình Thạnh", "Xã Vĩnh Bình", "Xã Vĩnh Hanh"
      ],
      "Huyện Phú Tân": [
        "Thị trấn Phú Mỹ", "Thị trấn Chợ Vàm", "Xã Phú Thạnh", "Xã Phú Hưng",
        "Xã Phú Hiệp", "Xã Long Hòa", "Xã Long Sơn"
      ],
      "Huyện Thoại Sơn": [
        "Thị trấn Núi Sập", "Thị trấn Phú Hòa", "Thị trấn Óc Eo",
        "Xã Vĩnh Trạch", "Xã Vĩnh Phú", "Xã Định Thành", "Xã Thoại Giang"
      ],
      "Huyện Tri Tôn": [
        "Thị trấn Tri Tôn", "Thị trấn Ba Chúc", "Xã Châu Lăng", "Xã Lương Phi",
        "Xã Lương An Trà", "Xã Tân Tuyến", "Xã An Tức"
      ],
      "Huyện Tịnh Biên": [
        "Thị trấn Tịnh Biên", "Thị trấn Nhà Bàng", "Xã An Cư", "Xã An Phú",
        "Xã Nhơn Hưng", "Xã Tân Lợi", "Xã Thới Sơn"
      ]
    },
    "Bạc Liêu": {
      "Thành phố Bạc Liêu": [
        "Phường 1", "Phường 2", "Phường 3", "Phường 5", "Phường 7",
        "Xã Hiệp Thành", "Xã Nhà Mát", "Xã Vĩnh Trạch", "Xã Vĩnh Trạch Đông"
      ],
      "Thị xã Giá Rai": [
        "Phường 1", "Phường Hộ Phòng", "Phường Láng Tròn",
        "Xã Phong Thạnh", "Xã Phong Thạnh A", "Xã Phong Tân",
        "Xã Tân Phong", "Xã Tân Thạnh", "Xã Phong Thạnh Tây A", "Xã Phong Thạnh Tây B"
      ],
      "Huyện Hòa Bình": [
        "Thị trấn Hòa Bình", "Xã Vĩnh Mỹ A", "Xã Vĩnh Mỹ B",
        "Xã Vĩnh Hậu", "Xã Vĩnh Hậu A", "Xã Vĩnh Thịnh", "Xã Minh Diệu"
      ],
      "Huyện Vĩnh Lợi": [
        "Thị trấn Châu Hưng", "Xã Hưng Hội", "Xã Hưng Thành",
        "Xã Long Thạnh", "Xã Vĩnh Hưng", "Xã Vĩnh Hưng A", "Xã Châu Thới"
      ],
      "Huyện Đông Hải": [
        "Thị trấn Gành Hào", "Xã An Trạch", "Xã An Trạch A", "Xã An Phúc",
        "Xã Điền Hải", "Xã Định Thành", "Xã Định Thành A", "Xã Long Điền",
        "Xã Long Điền Đông", "Xã Long Điền Đông A", "Xã Long Điền Tây"
      ],
      "Huyện Phước Long": [
        "Thị trấn Phước Long", "Xã Phong Thạnh Tây", "Xã Vĩnh Phú Đông",
        "Xã Vĩnh Phú Tây", "Xã Vĩnh Thanh", "Xã Hưng Phú", "Xã Vĩnh Phú"
      ],
      "Huyện Hồng Dân": [
        "Thị trấn Ngan Dừa", "Xã Ninh Hòa", "Xã Ninh Quới", "Xã Ninh Quới A",
        "Xã Ninh Thạnh Lợi", "Xã Ninh Thạnh Lợi A", "Xã Vĩnh Lộc", "Xã Vĩnh Lộc A"
      ]
    },
    "Bắc Kạn": {
      "Thành phố Bắc Kạn": [
        "Phường Nguyễn Thị Minh Khai", "Phường Sông Cầu",
        "Phường Đức Xuân", "Phường Phùng Chí Kiên",
        "Xã Dương Quang", "Xã Nông Thượng"
      ],
      "Huyện Bạch Thông": [
        "Thị trấn Phủ Thông", "Xã Cẩm Giàng", "Xã Quân Bình", "Xã Hà Vị",
        "Xã Vi Hương", "Xã Sỹ Bình"
      ],
      "Huyện Chợ Đồn": [
        "Thị trấn Bằng Lũng", "Xã Bằng Lãng", "Xã Ngọc Phái",
        "Xã Yên Thượng", "Xã Đại Sảo", "Xã Đông Viên"
      ],
      "Huyện Chợ Mới": [
        "Thị trấn Chợ Mới", "Xã Tân Sơn", "Xã Thanh Bình",
        "Xã Quảng Chu", "Xã Yên Hân", "Xã Hòa Mục"
      ],
      "Huyện Na Rì": [
        "Thị trấn Yến Lạc", "Xã Lương Thành", "Xã Cư Lễ",
        "Xã Văn Minh", "Xã Hảo Nghĩa", "Xã Kim Hỷ"
      ],
      "Huyện Ngân Sơn": [
        "Thị trấn Nà Phặc", "Xã Lãng Ngâm", "Xã Thuần Mang",
        "Xã Đức Vân", "Xã Vân Tùng", "Xã Thượng Quan"
      ],
      "Huyện Ba Bể": [
        "Thị trấn Chợ Rã", "Xã Quảng Khê", "Xã Cao Thượng",
        "Xã Thượng Giáo", "Xã Khang Ninh", "Xã Mỹ Phương"
      ],
      "Huyện Pác Nặm": [
        "Xã Bộc Bố", "Xã Cổ Linh", "Xã An Thắng",
        "Xã Giáo Hiệu", "Xã Nghiên Loan", "Xã Cao Tân"
      ]
    },
    "Bắc Ninh": {
      "Thành phố Bắc Ninh": [
        "Phường Suối Hoa", "Phường Tiền An", "Phường Vệ An",
        "Phường Vũ Ninh", "Phường Ninh Xá", "Phường Kinh Bắc",
        "Xã Hòa Long", "Phường Võ Cường"
      ],
      "Thành phố Từ Sơn": [
        "Phường Đông Ngàn", "Phường Đình Bảng", "Phường Tân Hồng",
        "Phường Trang Hạ", "Phường Châu Khê", "Phường Đồng Kỵ"
      ],
      "Huyện Quế Võ": [
        "Thị trấn Phố Mới", "Xã Việt Hùng", "Xã Phù Lãng",
        "Xã Phượng Mao", "Xã Phương Liễu", "Xã Yên Giả"
      ],
      "Huyện Gia Bình": [
        "Thị trấn Gia Bình", "Xã Đại Lai", "Xã Đông Cứu",
        "Xã Nhân Thắng", "Xã Quỳnh Phú", "Xã Vạn Ninh"
      ],
      "Huyện Lương Tài": [
        "Thị trấn Thứa", "Xã An Thịnh", "Xã Bình Định",
        "Xã Lâm Thao", "Xã Phú Hòa", "Xã Tân Lãng"
      ],
      "Huyện Tiên Du": [
        "Thị trấn Lim", "Xã Cảnh Hưng", "Xã Đại Đồng", 
        "Xã Hiên Vân", "Xã Lạc Vệ", "Xã Nội Duệ"
      ],
      "Huyện Thuận Thành": [
        "Thị trấn Hồ", "Xã An Bình", "Xã Gia Đông",
        "Xã Hà Mãn", "Xã Song Hồ", "Xã Xuân Lâm"
      ],
      "Huyện Yên Phong": [
        "Thị trấn Chờ", "Xã Đông Phong", "Xã Dũng Liệt",
        "Xã Long Châu", "Xã Tam Đa", "Xã Văn Môn"
      ]
    },
    "Bến Tre": {
      "Thành phố Bến Tre": [
        "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6",
        "Xã Bình Phú", "Xã Mỹ Thành", "Xã Phú Hưng", "Xã Sơn Đông"
      ],
      "Huyện Châu Thành": [
        "Xã An Hiệp", "Xã An Hóa", "Xã Giao Long", "Xã Giao Hòa",
        "Xã Phú Đức", "Xã Phú Túc", "Xã Quới Sơn"
      ],
      "Huyện Giồng Trôm": [
        "Thị trấn Giồng Trôm", "Xã Bình Thành", "Xã Hưng Phong",
        "Xã Lương Hòa", "Xã Lương Phú", "Xã Mỹ Thạnh", "Xã Tân Thanh"
      ],
      "Huyện Mỏ Cày Bắc": [
        "Xã Hưng Khánh Trung A", "Xã Khánh Thạnh Tân", "Xã Nhuận Phú Tân",
        "Xã Phú Mỹ", "Xã Phước Mỹ Trung", "Xã Tân Phú Tây"
      ],
      "Huyện Mỏ Cày Nam": [
        "Thị trấn Mỏ Cày", "Xã An Định", "Xã An Thạnh", "Xã Bình Khánh Đông",
        "Xã Định Thủy", "Xã Hương Mỹ", "Xã Tân Hội"
      ],
      "Huyện Chợ Lách": [
        "Thị trấn Chợ Lách", "Xã Hòa Nghĩa", "Xã Hưng Khánh Trung B",
        "Xã Long Thới", "Xã Phú Phụng", "Xã Sơn Định"
      ],
      "Huyện Ba Tri": [
        "Thị trấn Ba Tri", "Xã An Đức", "Xã An Hiệp", "Xã An Ngãi Trung",
        "Xã Mỹ Hòa", "Xã Phước Ngãi", "Xã Vĩnh Hòa"
      ],
      "Huyện Thạnh Phú": [
        "Thị trấn Thạnh Phú", "Xã An Nhơn", "Xã An Thuận", "Xã Bình Thạnh",
        "Xã Giao Thạnh", "Xã Hòa Lợi", "Xã Thạnh Hải"
      ]
    },
    "Bình Định": {
      "Thành phố Quy Nhơn": [
        "Phường Lê Lợi", "Phường Trần Phú", "Phường Ngô Mây",
        "Phường Ghềnh Ráng", "Phường Quang Trung", "Phường Hải Cảng",
        "Xã Nhơn Lý", "Xã Nhơn Hải", "Xã Phước Mỹ"
      ],
      "Thị xã An Nhơn": [
        "Phường Bình Định", "Phường Đập Đá", "Phường Nhơn Thành",
        "Xã Nhơn An", "Xã Nhơn Phong", "Xã Nhơn Hạnh"
      ],
      "Thị xã Hoài Nhơn": [
        "Phường Bồng Sơn", "Phường Tam Quan", "Phường Tam Quan Bắc",
        "Xã Hoài Hảo", "Xã Hoài Phú", "Xã Hoài Thanh"
      ],
      "Huyện Tuy Phước": [
        "Thị trấn Tuy Phước", "Thị trấn Diêu Trì", "Xã Phước Sơn",
        "Xã Phước Hòa", "Xã Phước Thắng"
      ],
      "Huyện Phù Cát": [
        "Thị trấn Ngô Mây", "Xã Cát Hanh", "Xã Cát Tiến",
        "Xã Cát Chánh", "Xã Cát Trinh"
      ],
      "Huyện Phù Mỹ": [
        "Thị trấn Phù Mỹ", "Thị trấn Bình Dương", "Xã Mỹ Chánh",
        "Xã Mỹ Quang", "Xã Mỹ Hòa"
      ],
      "Huyện Tây Sơn": [
        "Thị trấn Phú Phong", "Xã Bình Tường", "Xã Tây Vinh",
        "Xã Tây Giang", "Xã Tây Thuận"
      ],
      "Huyện Vĩnh Thạnh": [
        "Thị trấn Vĩnh Thạnh", "Xã Vĩnh Quang", "Xã Vĩnh Kim",
        "Xã Vĩnh Hảo"
      ],
      "Huyện Vân Canh": [
        "Thị trấn Vân Canh", "Xã Canh Hiệp", "Xã Canh Thuận",
        "Xã Canh Liên"
      ],
      "Huyện An Lão": [
        "Thị trấn An Lão", "Xã An Hưng", "Xã An Vinh",
        "Xã An Quang"
      ]
    },
    "Bình Phước": {
      "Thành phố Đồng Xoài": [
        "Phường Tân Bình", "Phường Tân Phú", "Phường Tân Đồng",
        "Phường Tân Xuân", "Phường Tiến Thành", "Xã Tiến Hưng"
      ],
      "Thị xã Phước Long": [
        "Phường Long Thủy", "Phường Thác Mơ", "Phường Sơn Giang",
        "Xã Long Giang", "Xã Phước Tín"
      ],
      "Thị xã Bình Long": [
        "Phường An Lộc", "Phường Hưng Chiến", "Phường Phú Đức",
        "Xã Thanh Phú", "Xã Thanh Lương"
      ],
      "Huyện Đồng Phú": [
        "Thị trấn Tân Phú", "Xã Tân Lập", "Xã Tân Hòa", "Xã Tân Tiến"
      ],
      "Huyện Bù Đăng": [
        "Thị trấn Đức Phong", "Xã Bom Bo", "Xã Đoàn Kết", "Xã Thọ Sơn"
      ],
      "Huyện Bù Đốp": [
        "Thị trấn Thanh Bình", "Xã Tân Tiến", "Xã Thanh Hòa", "Xã Phước Thiện"
      ],
      "Huyện Bù Gia Mập": [
        "Xã Bù Gia Mập", "Xã Đa Kia", "Xã Phú Nghĩa", "Xã Phước Minh"
      ],
      "Huyện Chơn Thành": [
        "Thị trấn Chơn Thành", "Xã Minh Hưng", "Xã Minh Long", "Xã Minh Lập"
      ],
      "Huyện Hớn Quản": [
        "Thị trấn Tân Khai", "Xã An Khương", "Xã Tân Hiệp", "Xã Thanh An"
      ],
      "Huyện Lộc Ninh": [
        "Thị trấn Lộc Ninh", "Xã Lộc Hòa", "Xã Lộc Thái", "Xã Lộc Tấn"
      ]
    },
    "Bình Thuận": {
      "Thành phố Phan Thiết": [
        "Phường Bình Hưng", "Phường Đức Long", "Phường Đức Nghĩa",
        "Phường Hàm Tiến", "Phường Lạc Đạo", "Phường Mũi Né",
        "Xã Thiện Nghiệp", "Xã Phong Nẫm", "Xã Tiến Lợi"
      ],
      "Thị xã La Gi": [
        "Phường Phước Hội", "Phường Tân An", "Phường Tân Thiện",
        "Xã Tân Hải", "Xã Tân Phước", "Xã Tân Tiến"
      ],
      "Huyện Tuy Phong": [
        "Thị trấn Liên Hương", "Thị trấn Phan Rí Cửa",
        "Xã Bình Thạnh", "Xã Hòa Minh", "Xã Chí Công"
      ],
      "Huyện Bắc Bình": [
        "Thị trấn Chợ Lầu", "Xã Hồng Thái", "Xã Sông Lũy",
        "Xã Phan Hòa", "Xã Hải Ninh"
      ],
      "Huyện Hàm Thuận Bắc": [
        "Thị trấn Ma Lâm", "Xã Hàm Chính", "Xã Hàm Thắng",
        "Xã Thuận Hòa", "Xã Thuận Minh"
      ],
      "Huyện Hàm Thuận Nam": [
        "Thị trấn Thuận Nam", "Xã Hàm Minh", "Xã Mương Mán",
        "Xã Tân Lập", "Xã Tân Thuận"
      ],
      "Huyện Hàm Tân": [
        "Thị trấn Tân Nghĩa", "Xã Sơn Mỹ", "Xã Tân Đức",
        "Xã Tân Minh", "Xã Tân Phúc"
      ],
      "Huyện Đức Linh": [
        "Thị trấn Võ Xu", "Thị trấn Đức Tài",
        "Xã Đức Hạnh", "Xã Đông Hà", "Xã Trà Tân"
      ],
      "Huyện Tánh Linh": [
        "Thị trấn Lạc Tánh", "Xã Bắc Ruộng", "Xã Đức Bình",
        "Xã Đồng Kho", "Xã Huy Khiêm"
      ],
      "Huyện Phú Quý": [
        "Xã Long Hải", "Xã Ngũ Phụng", "Xã Tam Thanh"
      ]
    },
    "Cà Mau": {
      "Thành phố Cà Mau": [
        "Phường 1", "Phường 2", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9",
        "Xã Định Bình", "Xã Hòa Tân", "Xã Hòa Thành", "Xã Lý Văn Lâm", "Xã Tắc Vân", "Xã Tân Thành"
      ],
      "Huyện Thới Bình": [
        "Thị trấn Thới Bình", "Xã Biển Bạch", "Xã Biển Bạch Đông", "Xã Hồ Thị Kỷ", "Xã Tân Bằng"
      ],
      "Huyện U Minh": [
        "Thị trấn U Minh", "Xã Khánh An", "Xã Khánh Hòa", "Xã Khánh Hội", "Xã Khánh Lâm"
      ],
      "Huyện Trần Văn Thời": [
        "Thị trấn Trần Văn Thời", "Thị trấn Sông Đốc", "Xã Khánh Bình", "Xã Khánh Bình Đông", "Xã Lợi An"
      ],
      "Huyện Cái Nước": [
        "Thị trấn Cái Nước", "Xã Đông Hưng", "Xã Đông Thới", "Xã Hòa Mỹ", "Xã Lương Thế Trân"
      ],
      "Huyện Đầm Dơi": [
        "Thị trấn Đầm Dơi", "Xã Ngọc Chánh", "Xã Tạ An Khương", "Xã Tân Duyệt", "Xã Tân Dân"
      ],
      "Huyện Phú Tân": [
        "Thị trấn Cái Đôi Vàm", "Xã Nguyễn Việt Khái", "Xã Phú Mỹ", "Xã Phú Tân", "Xã Việt Thắng"
      ],
      "Huyện Ngọc Hiển": [
        "Thị trấn Rạch Gốc", "Xã Đất Mũi", "Xã Tân Ân", "Xã Tân Ân Tây", "Xã Viên An"
      ],
      "Huyện Năm Căn": [
        "Thị trấn Năm Căn", "Xã Đất Mới", "Xã Hàm Rồng", "Xã Hàng Vịnh", "Xã Lâm Hải"
      ]
    },
    "Cao Bằng": {
      "Thành phố Cao Bằng": [
        "Phường Đề Thám", "Phường Hòa Chung", "Phường Hợp Giang",
        "Phường Ngọc Xuân", "Phường Sông Bằng", "Phường Sông Hiến",
        "Phường Tân Giang", "Xã Chu Trinh", "Xã Hưng Đạo", "Xã Vĩnh Quang"
      ],
      "Huyện Bảo Lạc": [
        "Thị trấn Bảo Lạc", "Xã Cô Ba", "Xã Hồng An", "Xã Hưng Đạo", "Xã Khánh Xuân"
      ],
      "Huyện Bảo Lâm": [
        "Thị trấn Pác Miầu", "Xã Lý Bôn", "Xã Nam Quang", "Xã Quảng Lâm", "Xã Yên Thổ"
      ],
      "Huyện Hạ Lang": [
        "Thị trấn Thanh Nhật", "Xã Cô Ngân", "Xã Đồng Loan", "Xã Lý Quốc", "Xã Thái Đức"
      ],
      "Huyện Hòa An": [
        "Thị trấn Nước Hai", "Xã Dân Chủ", "Xã Đức Long", "Xã Hồng Việt", "Xã Trương Lương"
      ],
      "Huyện Hà Quảng": [
        "Thị trấn Xuân Hòa", "Thị trấn Thông Nông", "Xã Cải Viên", "Xã Sóc Hà", "Xã Trường Hà"
      ],
      "Huyện Nguyên Bình": [
        "Thị trấn Nguyên Bình", "Thị trấn Tĩnh Túc", "Xã Hoa Thám", "Xã Thịnh Vượng", "Xã Vũ Nông"
      ],
      "Huyện Quảng Hòa": [
        "Thị trấn Hòa Thuận", "Thị trấn Quảng Uyên", "Xã Độc Lập", "Xã Hồng Quang", "Xã Phi Hải"
      ],
      "Huyện Thạch An": [
        "Thị trấn Đông Khê", "Xã Đức Long", "Xã Lê Lai", "Xã Thụy Hùng", "Xã Trọng Con"
      ],
      "Huyện Trùng Khánh": [
        "Thị trấn Trùng Khánh", "Xã Cao Chương", "Xã Đình Phong", "Xã Đoài Khôn", "Xã Khâm Thành"
      ]
    },
    "Điện Biên": {
      "Thành phố Điện Biên Phủ": [
        "Phường Him Lam", "Phường Mường Thanh", "Phường Nam Thanh",
        "Phường Noong Bua", "Phường Tân Thanh", "Phường Thanh Bình",
        "Xã Mường Phăng", "Xã Pá Khoang", "Xã Thanh Minh", "Xã Tà Lèng"
      ],
      "Thị xã Mường Lay": [
        "Phường Na Lay", "Xã Lay Nưa"
      ],
      "Huyện Điện Biên": [
        "Xã Mường Lói", "Xã Mường Nhà", "Xã Na Ư", "Xã Nà Nhạn", "Xã Nà Tấu", "Xã Noong Luống"
      ],
      "Huyện Điện Biên Đông": [
        "Thị trấn Điện Biên Đông", "Xã Keo Lôm", "Xã Mường Luân", "Xã Phình Giàng", "Xã Tìa Dình"
      ],
      "Huyện Mường Ảng": [
        "Thị trấn Mường Ảng", "Xã Ẳng Cang", "Xã Ẳng Nưa", "Xã Mường Đăng", "Xã Ngối Cáy"
      ],
      "Huyện Mường Chà": [
        "Thị trấn Mường Chà", "Xã Hừa Ngài", "Xã Mường Tùng", "Xã Nậm Nèn", "Xã Sá Tổng"
      ],
      "Huyện Mường Nhé": [
        "Xã Chung Chải", "Xã Huổi Lếch", "Xã Mường Nhé", "Xã Nậm Vì", "Xã Sín Thầu"
      ],
      "Huyện Nậm Pồ": [
        "Xã Chà Cang", "Xã Chà Nưa", "Xã Na Cô Sa", "Xã Nà Khoa", "Xã Nà Hỳ"
      ],
      "Huyện Tủa Chùa": [
        "Thị trấn Tủa Chùa", "Xã Huổi Só", "Xã Lao Xả Phình", "Xã Mường Báng", "Xã Xá Nhè"
      ],
      "Huyện Tuần Giáo": [
        "Thị trấn Tuần Giáo", "Xã Chiềng Sinh", "Xã Mùn Chung", "Xã Nà Sáy", "Xã Quài Cang"
      ]
    },
    "Đắk Nông": {
      "Thành phố Gia Nghĩa": [
        "Phường Nghĩa Đức", "Phường Nghĩa Phú", "Phường Nghĩa Thành",
        "Phường Nghĩa Tân", "Xã Đắk Nia", "Xã Quảng Thành"
      ],
      "Huyện Cư Jút": [
        "Thị trấn Ea T’ling", "Xã Cư Knia", "Xã Đắk D’rông", "Xã Đắk Wil", "Xã Ea Pô"
      ],
      "Huyện Đắk Glong": [
        "Xã Đắk Plao", "Xã Đắk R’măng", "Xã Quảng Hòa", "Xã Quảng Khê", "Xã Quảng Sơn"
      ],
      "Huyện Đắk Mil": [
        "Thị trấn Đắk Mil", "Xã Đắk Gằn", "Xã Đắk Lao", "Xã Đức Mạnh", "Xã Thuận An"
      ],
      "Huyện Đắk R’lấp": [
        "Thị trấn Kiến Đức", "Xã Đắk Ru", "Xã Đắk Wer", "Xã Kiến Thành", "Xã Nhân Cơ"
      ],
      "Huyện Krông Nô": [
        "Thị trấn Đắk Mâm", "Xã Buôn Choah", "Xã Đắk Drô", "Xã Đắk Nang", "Xã Nam Đà"
      ],
      "Huyện Tuy Đức": [
        "Xã Đắk Buk So", "Xã Đắk Ngo", "Xã Quảng Tâm", "Xã Quảng Trực", "Xã Quảng Tân"
      ]
    },
    "Đồng Tháp": {
      "Thành phố Cao Lãnh": [
        "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 6", "Phường Mỹ Phú",
        "Xã Hòa An", "Xã Mỹ Trà", "Xã Mỹ Tân", "Xã Tân Thuận Đông", "Xã Tân Thuận Tây"
      ],
      "Thành phố Sa Đéc": [
        "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường Tân Quy Đông",
        "Phường An Hòa", "Xã Tân Khánh Đông", "Xã Tân Phú Đông"
      ],
      "Thị xã Hồng Ngự": [
        "Phường An Lạc", "Phường An Bình B", "Phường An Bình A", "Xã Tân Hội",
        "Xã Bình Thạnh", "Xã Tân Bình", "Xã Tân Hội Trung"
      ],
      "Huyện Cao Lãnh": [
        "Thị trấn Mỹ Thọ", "Xã Mỹ Xương", "Xã Bình Hàng Trung", "Xã Gáo Giồng",
        "Xã Ba Sao", "Xã Phương Thịnh", "Xã Nhị Mỹ"
      ],
      "Huyện Châu Thành": [
        "Thị trấn Cái Tàu Hạ", "Xã An Hiệp", "Xã An Khánh", "Xã Tân Nhuận Đông",
        "Xã Tân Bình", "Xã Tân Phú", "Xã Tân Phú Trung"
      ],
      "Huyện Hồng Ngự": [
        "Thị trấn Thường Thới Tiền", "Xã Thường Phước 1", "Xã Thường Phước 2",
        "Xã Thường Lạc", "Xã Long Khánh A", "Xã Long Khánh B", "Xã Long Thuận"
      ],
      "Huyện Lai Vung": [
        "Thị trấn Lai Vung", "Xã Tân Dương", "Xã Tân Hòa", "Xã Tân Phước",
        "Xã Hòa Long", "Xã Hòa Thành", "Xã Long Hậu"
      ],
      "Huyện Lấp Vò": [
        "Thị trấn Lấp Vò", "Xã Mỹ An Hưng A", "Xã Mỹ An Hưng B", "Xã Tân Mỹ",
        "Xã Hội An Đông", "Xã Vĩnh Thạnh", "Xã Long Hưng A"
      ],
      "Huyện Tam Nông": [
        "Thị trấn Tràm Chim", "Xã Phú Cường", "Xã Phú Ninh", "Xã Phú Thành B",
        "Xã Tân Công Sính", "Xã Hòa Bình", "Xã An Hòa"
      ],
      "Huyện Tân Hồng": [
        "Thị trấn Sa Rài", "Xã Tân Hộ Cơ", "Xã Tân Phước", "Xã Thông Bình",
        "Xã Bình Phú", "Xã Tân Công Chí", "Xã An Phước"
      ],
      "Huyện Thanh Bình": [
        "Thị trấn Thanh Bình", "Xã Tân Quới", "Xã Tân Hòa", "Xã An Phong",
        "Xã Bình Tấn", "Xã Tân Long", "Xã Tân Huề"
      ],
      "Huyện Tháp Mười": [
        "Thị trấn Mỹ An", "Xã Đốc Binh Kiều", "Xã Mỹ Đông", "Xã Phú Điền",
        "Xã Trường Xuân", "Xã Thạnh Lợi", "Xã Thanh Mỹ"
      ]
    },
    "Gia Lai": {
      "Thành phố Pleiku": [
        "Phường Diên Hồng", "Phường Ia Kring", "Phường Hoa Lư", "Phường Phù Đổng",
        "Phường Tây Sơn", "Phường Yên Đỗ", "Phường Trà Bá", "Phường Thắng Lợi",
        "Xã Chư Á", "Xã Biển Hồ", "Xã Gào", "Xã Ia Kênh"
      ],
      "Thị xã An Khê": [
        "Phường An Bình", "Phường An Phú", "Phường Tây Sơn", "Phường Ngô Mây",
        "Xã Cửu An", "Xã Song An", "Xã Thành An"
      ],
      "Thị xã Ayun Pa": [
        "Phường Cheo Reo", "Phường Hòa Bình", "Phường Đoàn Kết", "Phường Sông Bờ",
        "Xã Ia RTô", "Xã Chư Băh"
      ],
      "Huyện Chư Păh": [
        "Thị trấn Phú Hòa", "Xã Ia Ka", "Xã Ia Nhin", "Xã Nghĩa Hòa",
        "Xã Nghĩa Hưng", "Xã Hà Tây", "Xã Chư Đăng Ya"
      ],
      "Huyện Chư Prông": [
        "Thị trấn Chư Prông", "Xã Ia Drăng", "Xã Ia Kly", "Xã Ia Phìn",
        "Xã Ia Pia", "Xã Thăng Hưng", "Xã Bàu Cạn"
      ],
      "Huyện Chư Sê": [
        "Thị trấn Chư Sê", "Xã Ia Glai", "Xã Hbông", "Xã Ia Blang",
        "Xã Bar Măih", "Xã Ayun", "Xã Kông Htok"
      ],
      "Huyện Đắk Đoa": [
        "Thị trấn Đắk Đoa", "Xã Hà Đông", "Xã Hải Yang", "Xã H’Neng",
        "Xã Glar", "Xã Trang", "Xã Tân Bình"
      ],
      "Huyện Đăk Pơ": [
        "Thị trấn Đăk Pơ", "Xã An Thành", "Xã Tân An", "Xã Yang Bắc",
        "Xã Cư An", "Xã Phú An", "Xã Kon Gang"
      ],
      "Huyện Đức Cơ": [
        "Thị trấn Chư Ty", "Xã Ia Dơk", "Xã Ia Din", "Xã Ia Kla",
        "Xã Ia Krêl", "Xã Ia Dom", "Xã Ia Lang"
      ],
      "Huyện Ia Grai": [
        "Thị trấn Ia Kha", "Xã Ia Hrung", "Xã Ia Bá", "Xã Ia Yok",
        "Xã Ia Chia", "Xã Ia Tô", "Xã Ia O"
      ],
      "Huyện Ia Pa": [
        "Thị trấn Ia K’đăm", "Xã Ia Trôk", "Xã Ia Mrơn", "Xã Ia Tul",
        "Xã Chư Răng", "Xã Kim Tân", "Xã Ia RMok"
      ],
      "Huyện Kbang": [
        "Thị trấn Kbang", "Xã Kon Pne", "Xã Đăk Roong", "Xã Krong",
        "Xã Lơ Ku", "Xã Nghĩa An", "Xã Đông"
      ],
      "Huyện Kông Chro": [
        "Thị trấn Kông Chro", "Xã Chư Krêy", "Xã Đăk Song", "Xã Đăk Pling",
        "Xã Sró", "Xã An Trung", "Xã Ya Ma"
      ],
      "Huyện Krông Pa": [
        "Thị trấn Phú Túc", "Xã Ia Rsai", "Xã Ia Rmok", "Xã Ia Mlah",
        "Xã Ia Hdreh", "Xã Krông Năng", "Xã Chư Drăng"
      ],
      "Huyện Mang Yang": [
        "Thị trấn Kon Dơng", "Xã Ayun", "Xã Đăk Jơ Ta", "Xã Đăk Ta Ley",
        "Xã Đê Ar", "Xã Kon Chiêng", "Xã Lơ Pang"
      ],
      "Huyện Phú Thiện": [
        "Thị trấn Phú Thiện", "Xã Ayun Hạ", "Xã Chrôh Pơnan", "Xã Ia Ake",
        "Xã Ia Hiao", "Xã Ia Peng", "Xã Ia Sol"
      ]
    },
    "Hải Dương": {
      "Thành phố Hải Dương": [
        "Phường Bình Hàn", "Phường Cẩm Thượng", "Phường Hải Tân", "Phường Lê Thanh Nghị",
        "Phường Nguyễn Trãi", "Phường Phạm Ngũ Lão", "Phường Tân Bình", "Xã Gia Xuyên", "Xã Liên Hồng"
      ],
      "Thành phố Chí Linh": [
        "Phường Sao Đỏ", "Phường Cộng Hòa", "Phường Văn Đức", "Phường Thái Học",
        "Xã Bắc An", "Xã Hoàng Hoa Thám", "Xã Lê Lợi", "Xã Hưng Đạo"
      ],
      "Thị xã Kinh Môn": [
        "Phường An Lưu", "Phường Hiến Thành", "Phường Minh Tân", "Phường Phú Thứ",
        "Xã Thượng Quận", "Xã Lạc Long", "Xã Hiệp Hòa"
      ],
      "Huyện Nam Sách": [
        "Thị trấn Nam Sách", "Xã An Lâm", "Xã Cộng Hòa", "Xã Hợp Tiến",
        "Xã Nam Hưng", "Xã Quốc Tuấn", "Xã Thái Tân"
      ],
      "Huyện Gia Lộc": [
        "Thị trấn Gia Lộc", "Xã Gia Hòa", "Xã Gia Khánh", "Xã Gia Xuyên",
        "Xã Nhật Tân", "Xã Phạm Trấn", "Xã Yết Kiêu"
      ],
      "Huyện Tứ Kỳ": [
        "Thị trấn Tứ Kỳ", "Xã An Thanh", "Xã Chí Minh", "Xã Đại Sơn",
        "Xã Hà Kỳ", "Xã Hưng Đạo", "Xã Quang Phục"
      ],
      "Huyện Cẩm Giàng": [
        "Thị trấn Cẩm Giàng", "Xã Cao An", "Xã Cẩm Điền", "Xã Cẩm Hưng",
        "Xã Cẩm Phúc", "Xã Cẩm Vũ", "Xã Thạch Lỗi"
      ],
      "Huyện Thanh Hà": [
        "Thị trấn Thanh Hà", "Xã Cẩm Chế", "Xã Hồng Lạc", "Xã Liên Mạc",
        "Xã Thanh An", "Xã Thanh Khê", "Xã Tân Việt"
      ],
      "Huyện Thanh Miện": [
        "Thị trấn Thanh Miện", "Xã Đoàn Kết", "Xã Hồng Phong", "Xã Lê Hồng",
        "Xã Ngô Quyền", "Xã Tân Trào", "Xã Thanh Giang"
      ],
      "Huyện Ninh Giang": [
        "Thị trấn Ninh Giang", "Xã Đồng Tâm", "Xã Hồng Đức", "Xã Hồng Phong",
        "Xã Kiến Quốc", "Xã Nghĩa An", "Xã Tân Hương"
      ],
      "Huyện Bình Giang": [
        "Thị trấn Kẻ Sặt", "Xã Bình Minh", "Xã Cổ Bì", "Xã Hùng Thắng",
        "Xã Long Xuyên", "Xã Thái Học", "Xã Vĩnh Tuy"
      ]
    },
    "Hà Nam": {
      "Thành phố Phủ Lý": [
        "Phường Minh Khai", "Phường Hai Bà Trưng", "Phường Trần Hưng Đạo",
        "Phường Lê Hồng Phong", "Phường Quang Trung", "Phường Lam Hạ",
        "Xã Phù Vân", "Xã Tiên Hiệp", "Xã Tiên Tân", "Xã Kim Bình"
      ],
      "Huyện Duy Tiên": [
        "Phường Hòa Mạc", "Phường Đồng Văn", "Xã Châu Giang", "Xã Yên Bắc",
        "Xã Mộc Nam", "Xã Mộc Bắc", "Xã Tiên Ngoại"
      ],
      "Huyện Kim Bảng": [
        "Thị trấn Ba Sao", "Thị trấn Quế", "Xã Thanh Sơn", "Xã Tân Sơn",
        "Xã Thi Sơn", "Xã Liên Sơn", "Xã Ngọc Sơn"
      ],
      "Huyện Thanh Liêm": [
        "Thị trấn Tân Thanh", "Xã Thanh Thủy", "Xã Thanh Nghị", "Xã Liêm Sơn",
        "Xã Thanh Phong", "Xã Thanh Hà", "Xã Thanh Hương"
      ],
      "Huyện Bình Lục": [
        "Thị trấn Bình Mỹ", "Xã An Đổ", "Xã An Lão", "Xã Bồ Đề",
        "Xã Tiêu Động", "Xã Tràng An", "Xã Ngọc Lũ"
      ],
      "Huyện Lý Nhân": [
        "Thị trấn Vĩnh Trụ", "Xã Chân Lý", "Xã Đức Lý", "Xã Hợp Lý",
        "Xã Bắc Lý", "Xã Trung Lý", "Xã Xuân Khê"
      ]
    },
    "Hải Dương": {
      "Thành phố Hải Dương": [
        "Phường Bình Hàn", "Phường Cẩm Thượng", "Phường Hải Tân", "Phường Lê Thanh Nghị",
        "Phường Nguyễn Trãi", "Phường Phạm Ngũ Lão", "Phường Tân Bình", "Xã Gia Xuyên", "Xã Liên Hồng"
      ],
      "Thành phố Chí Linh": [
        "Phường Sao Đỏ", "Phường Cộng Hòa", "Phường Văn Đức", "Phường Thái Học",
        "Xã Bắc An", "Xã Hoàng Hoa Thám", "Xã Lê Lợi", "Xã Hưng Đạo"
      ],
      "Thị xã Kinh Môn": [
        "Phường An Lưu", "Phường Hiến Thành", "Phường Minh Tân", "Phường Phú Thứ",
        "Xã Thượng Quận", "Xã Lạc Long", "Xã Hiệp Hòa"
      ],
      "Huyện Nam Sách": [
        "Thị trấn Nam Sách", "Xã An Lâm", "Xã Cộng Hòa", "Xã Hợp Tiến",
        "Xã Nam Hưng", "Xã Quốc Tuấn", "Xã Thái Tân"
      ],
      "Huyện Gia Lộc": [
        "Thị trấn Gia Lộc", "Xã Gia Hòa", "Xã Gia Khánh", "Xã Gia Xuyên",
        "Xã Nhật Tân", "Xã Phạm Trấn", "Xã Yết Kiêu"
      ],
      "Huyện Tứ Kỳ": [
        "Thị trấn Tứ Kỳ", "Xã An Thanh", "Xã Chí Minh", "Xã Đại Sơn",
        "Xã Hà Kỳ", "Xã Hưng Đạo", "Xã Quang Phục"
      ],
      "Huyện Cẩm Giàng": [
        "Thị trấn Cẩm Giàng", "Xã Cao An", "Xã Cẩm Điền", "Xã Cẩm Hưng",
        "Xã Cẩm Phúc", "Xã Cẩm Vũ", "Xã Thạch Lỗi"
      ],
      "Huyện Thanh Hà": [
        "Thị trấn Thanh Hà", "Xã Cẩm Chế", "Xã Hồng Lạc", "Xã Liên Mạc",
        "Xã Thanh An", "Xã Thanh Khê", "Xã Tân Việt"
      ],
      "Huyện Thanh Miện": [
        "Thị trấn Thanh Miện", "Xã Đoàn Kết", "Xã Hồng Phong", "Xã Lê Hồng",
        "Xã Ngô Quyền", "Xã Tân Trào", "Xã Thanh Giang"
      ],
      "Huyện Ninh Giang": [
        "Thị trấn Ninh Giang", "Xã Đồng Tâm", "Xã Hồng Đức", "Xã Hồng Phong",
        "Xã Kiến Quốc", "Xã Nghĩa An", "Xã Tân Hương"
      ],
      "Huyện Bình Giang": [
        "Thị trấn Kẻ Sặt", "Xã Bình Minh", "Xã Cổ Bì", "Xã Hùng Thắng",
        "Xã Long Xuyên", "Xã Thái Học", "Xã Vĩnh Tuy"
      ]
    },
    "Hưng Yên": {
      "Thành phố Hưng Yên": [
        "Phường Hiến Nam", "Phường Lê Lợi", "Phường Minh Khai", "Phường Quang Trung",
        "Xã Bảo Khê", "Xã Hồng Nam", "Xã Liên Phương", "Xã Trung Nghĩa"
      ],
      "Thị xã Mỹ Hào": [
        "Phường Bần Yên Nhân", "Phường Dị Sử", "Phường Minh Đức", "Phường Nhân Hòa",
        "Xã Cẩm Xá", "Xã Hòa Phong", "Xã Phan Đình Phùng"
      ],
      "Huyện Văn Lâm": [
        "Thị trấn Như Quỳnh", "Xã Đại Đồng", "Xã Lạc Đạo", "Xã Trưng Trắc",
        "Xã Việt Hưng", "Xã Tân Quang", "Xã Phù Ủng"
      ],
      "Huyện Văn Giang": [
        "Thị trấn Văn Giang", "Xã Cửu Cao", "Xã Long Hưng", "Xã Liên Nghĩa",
        "Xã Tân Tiến", "Xã Xuân Quan", "Xã Vĩnh Khúc"
      ],
      "Huyện Yên Mỹ": [
        "Thị trấn Yên Mỹ", "Xã Đồng Than", "Xã Giai Phạm", "Xã Nghĩa Hiệp",
        "Xã Trung Hòa", "Xã Tân Lập", "Xã Thanh Long"
      ],
      "Huyện Mỹ Hào": [
        "Xã Bạch Sam", "Xã Cẩm Xá", "Xã Dương Quang", "Xã Hưng Long",
        "Xã Phùng Chí Kiên", "Xã Nhân Hòa"
      ],
      "Huyện Khoái Châu": [
        "Thị trấn Khoái Châu", "Xã An Vĩ", "Xã Bình Minh", "Xã Dân Tiến",
        "Xã Hàm Tử", "Xã Hồng Tiến", "Xã Tứ Dân"
      ],
      "Huyện Ân Thi": [
        "Thị trấn Ân Thi", "Xã Bắc Sơn", "Xã Cẩm Ninh", "Xã Đào Dương",
        "Xã Hoàng Hoa Thám", "Xã Nguyễn Trãi", "Xã Vân Du"
      ],
      "Huyện Kim Động": [
        "Thị trấn Lương Bằng", "Xã Đồng Thanh", "Xã Hùng An", "Xã Nghĩa Dân",
        "Xã Ngọc Thanh", "Xã Song Mai", "Xã Vũ Xá"
      ],
      "Huyện Phù Cừ": [
        "Thị trấn Trần Cao", "Xã Đình Cao", "Xã Nhật Quang", "Xã Quang Hưng",
        "Xã Tam Đa", "Xã Tống Trân", "Xã Tiên Tiến"
      ]
    },
    "Kiên Giang": {
      "Thành phố Rạch Giá": [
        "Phường Vĩnh Thanh Vân", "Phường Vĩnh Thanh", "Phường Vĩnh Lạc",
        "Phường Vĩnh Quang", "Phường An Hòa", "Phường Rạch Sỏi", "Xã Vĩnh Thông"
      ],
      "Thành phố Hà Tiên": [
        "Phường Đông Hồ", "Phường Tô Châu", "Phường Bình San",
        "Phường Pháo Đài", "Xã Mỹ Đức", "Xã Thuận Yên"
      ],
      "Huyện Phú Quốc": [
        "Phường Dương Đông", "Phường An Thới", "Xã Cửa Cạn", "Xã Cửa Dương",
        "Xã Gành Dầu", "Xã Hàm Ninh", "Xã Bãi Thơm"
      ],
      "Huyện An Biên": [
        "Thị trấn Thứ Ba", "Xã Đông Yên", "Xã Tây Yên", "Xã Nam Yên",
        "Xã Nam Thái", "Xã Đông Thái", "Xã Tây Yên A"
      ],
      "Huyện An Minh": [
        "Thị trấn Thứ Mười Một", "Xã Thuận Hòa", "Xã Đông Hòa", "Xã Vân Khánh",
        "Xã Vân Khánh Tây", "Xã Tân Thạnh", "Xã Đông Thạnh"
      ],
      "Huyện Châu Thành": [
        "Thị trấn Minh Lương", "Xã Bình An", "Xã Mong Thọ", "Xã Mong Thọ B",
        "Xã Vĩnh Hòa Hiệp", "Xã Vĩnh Hòa Phú", "Xã Thạnh Lộc"
      ],
      "Huyện Giang Thành": [
        "Xã Tân Khánh Hòa", "Xã Phú Lợi", "Xã Vĩnh Điều",
        "Xã Vĩnh Phú", "Xã Vĩnh Trường"
      ],
      "Huyện Gò Quao": [
        "Thị trấn Gò Quao", "Xã Vĩnh Hòa Hưng Bắc", "Xã Vĩnh Hòa Hưng Nam",
        "Xã Thủy Liểu", "Xã Vĩnh Phước A", "Xã Vĩnh Tuy", "Xã Định An"
      ],
      "Huyện Hòn Đất": [
        "Thị trấn Hòn Đất", "Thị trấn Sóc Sơn", "Xã Bình Giang", "Xã Bình Sơn",
        "Xã Lình Huỳnh", "Xã Mỹ Hiệp Sơn", "Xã Mỹ Lâm"
      ],
      "Huyện Kiên Hải": [
        "Xã Hòn Tre", "Xã Lại Sơn", "Xã An Sơn", "Xã Nam Du"
      ],
      "Huyện Kiên Lương": [
        "Thị trấn Kiên Lương", "Xã Bình An", "Xã Dương Hòa", "Xã Hòa Điền",
        "Xã Hòn Nghệ"
      ],
      "Huyện Tân Hiệp": [
        "Thị trấn Tân Hiệp", "Xã Tân Hiệp A", "Xã Tân Hiệp B", "Xã Tân Hòa",
        "Xã Thạnh Đông", "Xã Thạnh Đông A", "Xã Thạnh Trị"
      ],
      "Huyện U Minh Thượng": [
        "Xã An Minh Bắc", "Xã Hòa Chánh", "Xã Minh Thuận",
        "Xã Thạnh Yên", "Xã Thạnh Yên A"
      ],
      "Huyện Vĩnh Thuận": [
        "Thị trấn Vĩnh Thuận", "Xã Vĩnh Bình Bắc", "Xã Vĩnh Bình Nam",
        "Xã Tân Thuận", "Xã Vĩnh Thuận", "Xã Phong Đông"
      ]
    },
    "Quảng Ngãi": {
      "Thành phố Quảng Ngãi": [
        "Phường Lê Hồng Phong", "Phường Trần Hưng Đạo", "Phường Nghĩa Chánh",
        "Phường Nghĩa Lộ", "Phường Nguyễn Nghiêm", "Xã Nghĩa Dõng", "Xã Tịnh Ấn Đông"
      ],
      "Huyện Bình Sơn": [
        "Thị trấn Châu Ổ", "Xã Bình Chánh", "Xã Bình Đông", "Xã Bình Hòa",
        "Xã Bình Long", "Xã Bình Minh", "Xã Bình Nguyên"
      ],
      "Huyện Sơn Tịnh": [
        "Thị trấn Sơn Tịnh", "Xã Tịnh Hà", "Xã Tịnh Kỳ", "Xã Tịnh Sơn",
        "Xã Tịnh Trà", "Xã Tịnh Thọ", "Xã Tịnh Ấn Tây"
      ],
      "Huyện Tư Nghĩa": [
        "Thị trấn La Hà", "Xã Nghĩa Trung", "Xã Nghĩa Hiệp", "Xã Nghĩa Kỳ",
        "Xã Nghĩa Phương", "Xã Nghĩa Thương", "Xã Nghĩa Mỹ"
      ],
      "Huyện Mộ Đức": [
        "Thị trấn Mộ Đức", "Xã Đức Lân", "Xã Đức Nhuận", "Xã Đức Chánh",
        "Xã Đức Phong", "Xã Đức Phú", "Xã Đức Thạnh"
      ],
      "Huyện Đức Phổ": [
        "Phường Nguyễn Nghiêm", "Phường Phổ Hòa", "Phường Phổ Minh",
        "Xã Phổ An", "Xã Phổ Cường", "Xã Phổ Vinh", "Xã Phổ Khánh"
      ],
      "Huyện Ba Tơ": [
        "Thị trấn Ba Tơ", "Xã Ba Vì", "Xã Ba Dinh", "Xã Ba Thành",
        "Xã Ba Bích", "Xã Ba Cung", "Xã Ba Trang"
      ],
      "Huyện Trà Bồng": [
        "Thị trấn Trà Xuân", "Xã Trà Thủy", "Xã Trà Bình", "Xã Trà Phú",
        "Xã Trà Giang", "Xã Trà Sơn", "Xã Trà Hiệp"
      ],
      "Huyện Tây Trà": [
        "Xã Trà Phong", "Xã Trà Lãnh", "Xã Trà Thanh", "Xã Trà Thọ"
      ],
      "Huyện Sơn Hà": [
        "Thị trấn Di Lăng", "Xã Sơn Thành", "Xã Sơn Tân", "Xã Sơn Bao",
        "Xã Sơn Trung", "Xã Sơn Thượng", "Xã Sơn Cao"
      ],
      "Huyện Sơn Tây": [
        "Xã Sơn Dung", "Xã Sơn Mùa", "Xã Sơn Liên", "Xã Sơn Tinh", "Xã Sơn Tân"
      ],
      "Huyện Minh Long": [
        "Xã Long Hiệp", "Xã Long Mai", "Xã Thanh An", "Xã Long Sơn", "Xã Long Môn"
      ]
    },
    "Thái Bình": {
      "Thành phố Thái Bình": [
        "Phường Bồ Xuyên", "Phường Đề Thám", "Phường Kỳ Bá",
        "Phường Lê Hồng Phong", "Phường Quang Trung", "Phường Trần Hưng Đạo",
        "Phường Trần Lãm", "Xã Đông Hòa", "Xã Phú Xuân", "Xã Vũ Chính"
      ],
      "Huyện Đông Hưng": [
        "Thị trấn Đông Hưng", "Xã Đông Á", "Xã Đông Cường", "Xã Đông Hợp",
        "Xã Đông La", "Xã Đông Phương", "Xã Đông Sơn"
      ],
      "Huyện Hưng Hà": [
        "Thị trấn Hưng Hà", "Xã Canh Tân", "Xã Dân Chủ", "Xã Hồng Lĩnh",
        "Xã Minh Tân", "Xã Tân Hòa", "Xã Văn Cẩm"
      ],
      "Huyện Kiến Xương": [
        "Thị trấn Kiến Xương", "Xã Bình Định", "Xã Hòa Bình", "Xã Hồng Thái",
        "Xã Lê Lợi", "Xã Quang Lịch", "Xã Vũ An"
      ],
      "Huyện Quỳnh Phụ": [
        "Thị trấn Quỳnh Côi", "Xã An Đồng", "Xã An Hiệp", "Xã An Tràng",
        "Xã Quỳnh Hoa", "Xã Quỳnh Minh", "Xã Quỳnh Ngọc"
      ],
      "Huyện Thái Thụy": [
        "Thị trấn Diêm Điền", "Xã Thụy An", "Xã Thụy Bình", "Xã Thụy Duyên",
        "Xã Thụy Hải", "Xã Thụy Liên", "Xã Thụy Trường"
      ],
      "Huyện Tiền Hải": [
        "Thị trấn Tiền Hải", "Xã Đông Cơ", "Xã Đông Hoàng", "Xã Nam Chính",
        "Xã Nam Hưng", "Xã Nam Hải", "Xã Tây Giang"
      ],
      "Huyện Vũ Thư": [
        "Thị trấn Vũ Thư", "Xã Dũng Nghĩa", "Xã Hiệp Hòa", "Xã Hồng Lý",
        "Xã Minh Khai", "Xã Nguyên Xá", "Xã Vũ Đoài"
      ]
    }
  };


  const provinceSelect = document.getElementById("province");
  const districtSelect = document.getElementById("district");
  const wardSelect = document.getElementById("ward");

  for (let province in data) {
    provinceSelect.innerHTML += `<option value="${province}">${province}</option>`;
  }

  provinceSelect.addEventListener("change", function () {
    const province = this.value;
    districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
    wardSelect.disabled = true;

    if (province && data[province]) {
      for (let district in data[province]) {
        districtSelect.innerHTML += `<option value="${district}">${district}</option>`;
      }
      districtSelect.disabled = false;
    } else {
      districtSelect.disabled = true;
    }
  });

  districtSelect.addEventListener("change", function () {
    const province = provinceSelect.value;
    const district = this.value;
    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';

    if (province && district && data[province][district]) {
      data[province][district].forEach(ward => {
        wardSelect.innerHTML += `<option value="${ward}">${ward}</option>`;
      });
      wardSelect.disabled = false;
    } else {
      wardSelect.disabled = true;
    }
  });
</script>
<script>
  function openEdit(element) {
    const popup = document.getElementById("updateDiachi");
    popup.classList.toggle("hidden");
    document.getElementById("addressPopup").classList.toggle("hidden");
    document.getElementById("new-address-form").classList.add("hidden");

    const id = element.dataset.id;
    const name = element.dataset.name;
    const phone = element.dataset.phone;
    const address = element.dataset.address;
    const city = element.dataset.city;
    const district = element.dataset.district;
    const ward = element.dataset.ward;
    const status = element.dataset.status;


    document.getElementById("edit_id").value = id;
    document.getElementById("edit_name").value = name;
    document.getElementById("edit_phone").value = phone;
    document.getElementById("edit_address").value = address;
    // document.getElementById("edit_status").value = status;
    // if(status == 1) {
    //   document.getElementById("edit_status").checked = true;
    // } else {
    //   document.getElementById("edit_status").checked = false;
    // }

    document.getElementById("edit_city_bk").value = city;
    document.getElementById("edit_ward_bk").value = ward;
    document.getElementById("edit_district_bk").value = district;

    // Reset dropdowns
    const citySelect = document.getElementById("edit_city");
    const districtSelect = document.getElementById("edit_district");
    const wardSelect = document.getElementById("edit_ward");

    citySelect.innerHTML = '<option value="">Chọn Tỉnh/Thành phố</option>';
    for (let p in data) {
      citySelect.innerHTML += `<option value="${p}">${p}</option>`;
    }

    citySelect.value = city;
    districtSelect.disabled = false;
    districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
    for (let d in data[city]) {
      districtSelect.innerHTML += `<option value="${d}">${d}</option>`;
    }

    districtSelect.value = district;
    wardSelect.disabled = false;
    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
    
    if (data[city] && data[city][district]) {
      data[city][district].forEach(w => {
        wardSelect.innerHTML += `<option value="${w}">${w}</option>`;
      });
      wardSelect.disabled = false;
    } else {
      console.warn("Không tìm thấy dữ liệu phường cho:", city, district);
      wardSelect.disabled = true;
    }


    wardSelect.value = ward;

    popup.classList.remove("hidden");
  }


  function saveAddress() {
    // const status = document.getElementById("edit_status").checked ? 1 : 0;
    const id = document.getElementById("edit_id").value;
    const name = document.getElementById("edit_name").value;
    const phone = document.getElementById("edit_phone").value;
    const address = document.getElementById("edit_address").value;
    const city = document.getElementById("edit_city").value || document.getElementById("edit_city_bk").value;
    const district = document.getElementById("edit_district").value || document.getElementById("edit_district_bk").value;
    const ward = document.getElementById("edit_ward").value || document.getElementById("edit_ward_bk").value;

    console.log(id, name, phone, address, city, district,"ward", ward);
    fetch('../controllers/update_dia_chi.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        id,
        name,
        phone,
        address,
        city,
        district,
        ward,
        //status
      })
    })
    .then(res => res.json())
    .then(result => {
      if (result.success) {
        alert('Cập nhật thành công!');
        document.getElementById("updateDiachi").classList.add("hidden");
        location.reload(); 
      } else {
        alert('Cập nhật thất bại: ' + result.message);
      }
    })
    .catch(err => {
      alert('Lỗi khi gửi yêu cầu: ' + err);
    });
  }
</script>
<script>
  function togglePopup() {
    const popup = document.getElementById("updateDiachi");
    popup.classList.toggle("hidden");
    document.getElementById("addressPopup").classList.toggle("hidden");
    document.getElementById("new-address-form").classList.add("hidden");
  }
</script>
<script>
// function submitAddress() {
//   const data = {
//     tennguoinhan: document.getElementById("tennguoinhan").value,
//     sdt: document.getElementById("sdt").value,
//     phuong: document.getElementById("ward").value,
//     district: document.getElementById("district").value,
//     thanhpho: document.getElementById("province").value,

//     diachi: document.getElementById("diachi").value,
//     macdinh: document.getElementById("macdinh").checked
//   };

//   fetch("../controllers/them_dia_chi.php", {
//     method: "POST",
//     headers: {
//       "Content-Type": "application/json"
//     },
//     body: JSON.stringify(data)
//   })
//   .then(res => res.json())
//   .then(result => {
//     if (result.success) {
//       alert("Thêm địa chỉ thành công!");
//       toggleBack(); // Ẩn form nếu bạn có hàm này
//     } else {
//       alert("Thêm thất bại: " + result.message);
//     }
//   })
//   .catch(err => {
//     alert("Lỗi kết nối server.");
//     console.error(err);
//   });
// }
</script>


<script>
  function toggleAddressForm() {
    const form = document.getElementById("new-address-form");
    form.classList.toggle("hidden");
    document.getElementById("addressPopup").classList.toggle("hidden");
    document.getElementById("updateDiachi").classList.add("hidden");
  }
</script>
<script>
  function toggleAddressPopup() {
    const popup = document.getElementById("addressPopup");
    popup.classList.toggle("hidden");
  }
</script>
<script>
  function toggleBack() {
    document.getElementById("new-address-form").classList.add("hidden");
    document.getElementById("addressPopup").classList.remove("hidden");
  }
</script>

<script>
// function addNewAddress() {
//   const ten = document.getElementById("tennguoinhan").value.trim();
//   const sdt = document.getElementById("sdt").value.trim();
//   const diachi = document.getElementById("diachi").value.trim();
//   const ward = document.getElementById("ward").value.trim();
//   const district = document.getElementById("district").value.trim();
//   const province = document.getElementById("province").value.trim();

//   if (!ten || !sdt || !diachi || !ward || !district || !province) {
//     alert("Vui lòng nhập đầy đủ thông tin địa chỉ!");
//     return;
//   }

//   const phoneRegex = /^0\d{9}$/;
//   if (!phoneRegex.test(sdt)) {
//     alert("Số điện thoại không hợp lệ. Vui lòng nhập đúng định dạng 10 số bắt đầu bằng 0.");
//     return;
//   }


//   const data = {
//     tennguoinhan: ten,
//     sdt,
//     diachi,
//     thanhpho: province,
//     district,
//     ward
//   };

//   fetch("../controllers/them_dia_chi.php", {
//     method: "POST",
//     headers: {
//       "Content-Type": "application/json"
//     },
//     body: JSON.stringify(data)
//   })
//   .then(res => res.json())
//   .then(result => {
//     if (result.success) {
//       alert("Thêm địa chỉ thành công!");
//       location.reload();
//     } else {
//       alert("Thêm thất bại: " + result.message);
//     }
//   })
//   .catch(err => {
//     alert("Lỗi kết nối server.");
//     console.error(err);
//   });
//   document.getElementById("submitName").innerText = ten;
//   document.getElementById("submitSDT").innerText = sdt;
//   document.getElementById("submitDiachi").innerText = diachi;
//   document.getElementById("submitWard").innerText = ward;
//   document.getElementById("submitDistrict").innerText = district;
//   document.getElementById("submitCity").innerText = province;
//   document.getElementById("new-address-form").classList.add("hidden");
// }
function showNewAddress() {
  const ten = document.getElementById("tennguoinhan")?.value.trim();
  const sdt = document.getElementById("sdt")?.value.trim();
  const diachi = document.getElementById("diachi")?.value.trim();
  const ward = document.getElementById("ward")?.value.trim();
  const district = document.getElementById("district")?.value.trim();
  const province = document.getElementById("province")?.value.trim();

  if (!ten || !sdt || !diachi || !ward || !district || !province) {
    alert("Vui lòng nhập đầy đủ thông tin địa chỉ!");
    return;
  }

  const phoneRegex = /^0\d{9}$/;
  if (!phoneRegex.test(sdt)) {
    alert("Số điện thoại không hợp lệ. Vui lòng nhập đúng định dạng 10 số bắt đầu bằng 0.");
    return;
  }

  const fullAddress = [diachi, ward, district, province]
    .filter(part => part !== "")
    .join(", ");

  const nameEl = document.getElementById("submitName");
  const sdtEl = document.getElementById("submitSDT");
  const addressEl = document.getElementById("fullAddress");
  const idEl = document.getElementById("submitId_Diachi");

  if (!nameEl || !sdtEl || !addressEl || !idEl) {
    console.warn("❌ Một số phần tử hiển thị không tồn tại trong DOM.");
    return;
  }

  nameEl.innerText = ten;
  sdtEl.innerText = sdt;
  addressEl.innerText = fullAddress;
  idEl.value = "0";

  const form = document.getElementById("new-address-form");
  if (form) form.classList.add("hidden");

  const showInfo = document.getElementById("showAddressInfor");
  const addAddress = document.getElementById("add-address");

  if (showInfo) showInfo.classList.remove("hidden");
  if (addAddress) addAddress.classList.add("hidden");
}

</script>




<script>
function showAddressChecked() {
  const selected = document.querySelector('input[name="diachi"]:checked');
  if (!selected) {
    alert("Vui lòng chọn một địa chỉ!");
    return;
  }

  const parent = selected.closest(".parentDiachi");
  const ten = parent.querySelector(".showTenNguoiNhan")?.innerText.trim() || "";
  const sdtFull = parent.querySelector(".showSDT")?.innerText.trim() || "";
  const sdt = sdtFull.replace("SĐT :", "").trim();

  const addressEl = parent.querySelector(".text-sm.text-gray-600");
  const fullAddress = addressEl?.innerText.trim() || "";

  const id = selected.value;

  const nameEl = document.getElementById("submitName");
  const sdtEl = document.getElementById("submitSDT");
  const fullAddressEl = document.getElementById("fullAddress");
  const idEl = document.getElementById("submitId_Diachi");

  if (!nameEl || !sdtEl || !fullAddressEl || !idEl) {
    console.warn("⛔ Thiếu phần tử DOM để cập nhật địa chỉ.");
    return;
  }

  nameEl.innerText = ten;
  sdtEl.innerText = sdt;
  fullAddressEl.innerText = fullAddress;
  idEl.value = id;

  // Ẩn popup, hiển thị phần địa chỉ chính
  const popup = document.getElementById("addressPopup");
  if (popup) popup.classList.add("hidden");

  const showInfo = document.getElementById("showAddressInfor");
  const addAddress = document.getElementById("add-address");

  if (addAddress && !addAddress.classList.contains("hidden")) {
    if (showInfo) showInfo.classList.remove("hidden");
    addAddress.classList.add("hidden");
  }
}



</script>

<script>async function xacNhanThanhToan() {
  const tennguoinhan = document.getElementById("submitName")?.innerText.trim() || "";
  const sdt = document.getElementById("submitSDT")?.innerText.trim() || "";
  const fullAddress = document.getElementById("fullAddress")?.innerText.trim() || "";
  const selectedPayment = document.querySelector('input[name="payment"]:checked');
  const selected = document.getElementById("submitId_Diachi")?.value || "";

  if (!tennguoinhan || !sdt || !fullAddress) {
    alert("Vui lòng điền đầy đủ thông tin địa chỉ và người nhận.");
    return;
  }

  if (!selectedPayment) {
    alert("Vui lòng chọn phương thức thanh toán.");
    return;
  }

  console.log("selected:", selected);
  console.log("tennguoinhan:", tennguoinhan);
  console.log("sdt:", sdt);
  console.log("fullAddress:", fullAddress);

  let addressId = null;

  // Nếu là địa chỉ mới
  if (!selected || selected === "0") {
    // Phân tách địa chỉ từ fullAddress
    const [diachi = "", huyen = "", quan = "", thanhpho = ""] = fullAddress.split(',').map(part => part.trim());

    const newAddress = {
      tennguoinhan,
      sdt,
      ward: huyen,
      quan: quan,
      thanhpho: thanhpho,
      diachi: diachi
    };

    try {
      const res = await fetch("../controllers/them_dia_chi.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(newAddress)
      });

      const result = await res.json();

      if (!result.success) {
        alert("Không thể thêm địa chỉ mới: " + result.message);
        return;
      }

      if (!result.address_id) {
        alert("Thêm địa chỉ thành công nhưng thiếu ID trả về.");
        return;
      }

      addressId = result.address_id;

    } catch (err) {
      console.error(err);
      alert("Lỗi khi thêm địa chỉ mới.");
      return;
    }

  } else {
    addressId = selected;
  }

  const paymentMethod = selectedPayment.value;

  fetch("../controllers/thanhtoan.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: `address_id=${encodeURIComponent(addressId)}&payment_method=${encodeURIComponent(paymentMethod)}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Store the hoadonID in the session storage to use it on the receipt page if needed
      if (data.hoadonID) {
        sessionStorage.setItem("hoadonID", data.hoadonID);
      }
      // Redirect to receipt.php instead of responseOrder.php
      window.location.href = "/LTW-UD2/receipt.php";
    } else {
      alert(data.message);
    }
  })
  .catch(error => {
    console.error("Lỗi khi thanh toán:", error);
    alert("Thanh toán thất bại!");
  });
}
</script>

</body>
</html>
