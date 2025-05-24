<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="./css/login.css">
<link rel="stylesheet" href="./css/header.css">
</head>
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ltw_ud2";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();
?>

<body class="bg-gray-100 text-gray-800">
  <?php
  include './components/header2.php'
  ?>

  <div class="container mx-auto px-4 py-6">
    <div class="grid grid-cols-12 gap-6">
      <form id="filterForm" onsubmit="return false;" method="GET" class="col-span-3 bg-white rounded-2xl shadow p-4">
        <aside class="bg-white rounded-2xl shadow p-4">
            <h2 class="text-xl font-semibold mb-4">LỌC THEO</h2>

            <div class="mb-6">
              <?php if (!empty($_GET["subject"])) { ?>
              <!-- <h3 class="font-semibold mb-2">MÔN HỌC : -->
              <input name="subject" type="hidden" value="<?php echo htmlspecialchars($_GET["subject"] ?? '') ?>" >
              </h3>
               <?php  } ?>
              <?php if (!empty($_GET["class"])) { ?>
              <!-- <h3 class="font-semibold mb-2">LỚP : -->
              <input name="class" type="hidden" value="<?php echo htmlspecialchars($_GET["class"] ?? '') ?>" >
              </h3>
               <?php  } ?>
              <?php if (!empty($_GET["type"])) { ?>
              <h3 class="font-semibold mb-2">THỂ LOẠI : 
              <input  name="type" type="text" value="<?php echo htmlspecialchars($_GET["type"]); ?>">
              </h3>
              <?php } ?>

              <div class="accent-blue-500">
                <?php if (!empty($search)): ?>
                  Từ khóa tìm kiếm : <?= htmlspecialchars($search); ?><br>
                <?php endif; ?>
                <input type="hidden" name="search" value="<?php if (!empty($_GET["search"])) echo htmlspecialchars($_GET["search"]); ?>">
                <?php if (!empty($subject)): ?>
                  Môn: <?= htmlspecialchars($subject); ?><br>
                <?php endif; ?>

                <?php if (!empty($type)): ?>
                  Thể loại: <?= htmlspecialchars($type); ?><br>
                <?php endif; ?>

              </div>
            </div>

            <div class="mb-6">
              <h3 class="font-semibold mb-2">GIÁ</h3>
              <div class="space-y-1">
                <label class="flex items-center gap-2">
                <input type="checkbox" <?php if(isset($_GET["Optioncost"])){if($_GET["Optioncost"]==1) echo "checked";}?> name="Optioncost" value="1" class="accent-blue-500" >
                0đ - 50,000đ
                </label>
                <label class="flex items-center gap-2">
                <input type="checkbox" <?php if(isset($_GET["Optioncost"])){if($_GET["Optioncost"]==2) echo "checked";}?> name="Optioncost" value="2" class="accent-blue-500">
                50,000đ - 100,000đ
                </label>
                <label class="flex items-center gap-2">
                <input type="checkbox" <?php if(isset($_GET["Optioncost"])){if($_GET["Optioncost"]==3) echo "checked";}?> name="Optioncost" value="3" class="accent-blue-500">
                100,000đ - 200,000đ
                </label>
              </div>
              <!-- <div class="mt-4">
                <input name="min_cost" type="number" placeholder="0" class="w-1/4 border rounded p-1 text-sm"
                    value="<?php //echo $_GET['min_cost'] ?? ''; ?>"> - 
                <input name="max_cost" type="number" placeholder="0" class="w-1/4 border rounded p-1 text-sm"
                    value="<?php //echo $_GET['max_cost'] ?? ''; ?>">
              </div> -->
            </div>

            <!-- Lứa tuổi -->
            <!-- <div class="mb-6">
              <h3 class="font-semibold mb-2">Nhập lớp</h3>
              <label class="flex items-center gap-2">
                
                <input type="number" name="min_class" placeholder="Từ" class="w-1/6 border rounded p-1 text-sm"
                    value="<?php //echo $_GET['min_class'] ?? ''; ?>"> -
                <input type="number" name="max_class" placeholder="Đến" class="w-1/6 border rounded p-1 text-sm"
                    value="<?php //echo $_GET['max_class'] ?? ''; ?>">
              </label>
              <label class="flex items-center gap-2 mt-2">
                Nhập lớp : 
                <input type="text" name="class" value="<?php //echo $_GET['class'] ?? ''; ?>" class="w-1/6 border rounded p-1 text-sm">
              </label>
            </div> -->

            <div class="flex gap-2">
              <select name="sort" class="border rounded p-1 text-sm " onchange="document.getElementById('filterForm').submit();">
                <option>Sắp xếp theo</option>
                <option value="asc">Giá tăng dần</option>
                <option value="desc">Giá giảm dần</option>
              </select>
            </div>

            <button type="submit" class="bg-[#D10024] px-4 text-white m-2 rounded">Tìm kiếm</button>
        </aside>
      </form>


      <!-- Main Content -->
      <main class="col-span-9">
        <!-- Products Grid -->
        <div class="bg-white rounded-2xl shadow p-4 min-h-full">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">KẾT QUẢ TÌM KIẾM: <span class="text-blue-500" id="search-summary"> </span></h2>
          </div>
          <div id="booksContainer"  >


          </div>
        </div>

      </main>
      
    </div>

    
  </div>


  <?php include_once "./components/footer.php";?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
  const classInput = document.querySelector('input[name="class"]');
  const minClassInput = document.querySelector('input[name="min_class"]');
  const maxClassInput = document.querySelector('input[name="max_class"]');

  classInput.addEventListener('input', function () {
    if (this.value !== '') {
      minClassInput.disabled = true;
      maxClassInput.disabled = true;
      minClassInput.value = '';
      maxClassInput.value = '';
    } else {
      minClassInput.disabled = false;
      maxClassInput.disabled = false;
    }
  });

  minClassInput.addEventListener('input', maxClassInput.addEventListener('input', function () {
    if (minClassInput.value !== '' || maxClassInput.value !== '') {
      classInput.disabled = true;
      classInput.value = '';
    } else {
      classInput.disabled = false;
    }
  }));
});
</script>
<script>
  function themVaoGio(bookId) {
  fetch('controllers/add_to_cart.php', {
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

<script>
function getFormData(form) {
  const formData = new FormData(form);
  return new URLSearchParams(formData).toString();
}
document.addEventListener('DOMContentLoaded', function () {
  fetchBooks(); 
});
function fetchBooks() {
  const form = document.getElementById('filterForm');
  const params = getFormData(form);

  fetch('./controllers/search.php?' + params)
    .then(response => response.text())
    .then(html => {
      document.getElementById('booksContainer').innerHTML = html;
      const summaryEl = document.querySelector('.search-summary');
      if (summaryEl) {
        document.getElementById('search-summary').innerHTML = summaryEl.innerHTML;
        summaryEl.remove(); // Xoá bản tạm sau khi chèn lên trên
      }
    })
    .catch(error => {
      console.error("Lỗi AJAX:", error);
    });
}

document.getElementById('filterForm').addEventListener('change', fetchBooks);
document.getElementById('filterForm').addEventListener('submit', function (e) {
  e.preventDefault();
  fetchBooks();
});
</script>
<script>
  function changePage(page) {
  const form = document.getElementById('filterForm');
  const formData = new FormData(form);
  formData.set('page', page);

  const params = new URLSearchParams(formData).toString();

  fetch('./controllers/search.php?' + params)
    .then(response => response.text())
    .then(html => {
      document.getElementById('booksContainer').innerHTML = html;
    })
    .catch(error => {
      console.error("Lỗi phân trang AJAX:", error);
    });
  }

</script>
</body>
</html>
