<?php
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create database connection if it doesn't exist
if (!isset($conn)) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "ltw_ud2";
    $conn = new mysqli($servername, $username, $password, $dbname, 3306);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];

  $query_count_cart = "
    SELECT COUNT(*) as total 
    FROM cart 
    JOIN cartitems ON cartitems.cartId = cart.idCart 
    WHERE cart.idUser = $user_id
  ";  

  $result = $conn->query($query_count_cart);
  $countOfCart = $result->fetch_assoc()['total'];
}

?>
<style>
  @keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
  }

  .animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
  }
</style>
<div class="relative mx-auto w-full flex justify-between py-2 px-[10%] bg-white shadow-sm">
  <div class="flex items-center gap-2">
    <a href="/LTW-UD2"><img src="/LTW-UD2/images/forHeader/logo.jpg" alt="Logo" class="h-12"></a>
  </div>
  <img src="/LTW-UD2/images/forHeader/menucontent.png" alt="" class=" h-10 animate-fade-in shadow-lg cursor-pointer" id="menuTrigger">
  <div class="flex-1 max-w-2xl mx-4">
    <form id="filterForm"  class="flex rounded border border-gray-300 overflow-hidden">
      <input type="text" name="search" placeholder="Tìm kiếm" class="flex-1 px-4 py-2 outline-none text-sm" required />
      <button type="submit" class="bg-[#D10024] px-4 text-white m-2 rounded">
        🔍
      </button>
    </form>

  </div>
  <div class="flex items-center gap-4 text-sm text-gray-600">


    <div class="flex flex-col items-center">
      <div onclick="toggleNoti()" class="cursor-pointer text-center">
        <span class="text-xl">🔔</span><br>
        <span>Thông Báo</span>
      </div>


      <div id="notificationPanel"
        class=" hidden absolute right-50 mt-12 w-80 bg-white border border-gray-200 rounded-xl shadow-lg z-30">

        <div class="flex justify-between items-center p-4 border-b border-gray-200">
          <h3 class="font-semibold text-gray-800 flex items-center gap-2 text-base">
            🔔
          
          </h3>
        </div>

        <ul class="divide-y divide-gray-200 max-h-72 overflow-y-scroll">
          <?php
          if (!empty($user_id)) {
            $query = "
            SELECT 
              hoadon.idBill,
              hoadon.statusBill,
              hoadon.create_at AS thoigianmoi,
              hoadon_trangthai.trangthai AS trangthai_cu,
              hoadon_trangthai.create_at AS thoigiancu,
              books.bookName
            FROM hoadon
            LEFT JOIN hoadon_trangthai ON hoadon_trangthai.idBill = hoadon.idBill
            JOIN chitiethoadon ON chitiethoadon.idHoadon = hoadon.idBill
            JOIN books ON books.id = chitiethoadon.idBook
            WHERE hoadon.idUser = $user_id
            ORDER BY hoadon.create_at DESC , hoadon_trangthai.create_at DESC;

            ";



            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                if (!empty($row['trangthai_cu'])) {
                  $status = $row['trangthai_cu']; // trạng thái trong hoadon_trangthai
                  $time = $row['thoigiancu'];
                  $isOld = true;
                } else {
                  $status = $row['statusBill']; // trạng thái hiện tại trong hoadon
                  $time = $row['thoigianmoi'];
                  $isOld = false;
                }
                $icons = [
                  1 => '📦',
                  2 => '🚚',
                  3 => '✅',
                  4 => '↩️',
                  6 => '❌'
                ];

                $texts = [
                  1 => 'Đang xử lý',
                  2 => 'Đang được giao',
                  3 => 'Giao hàng thành công',
                  4 => 'Đơn hàng bị hủy'
                ];
                $text = $texts[$status] ?? '❌';
                $icon = $icons[$status] ?? '❌';


                ?>

                <li class="px-4 py-3 hover:bg-gray-50 transition-all duration-200">
                  <div class="flex gap-3 items-start p-3 rounded-xl hover:bg-blue-50 transition duration-200">

                    <div class="bg-blue-100 text-blue-600 rounded-full p-2 shadow-sm">
                      <?= $icon ?>
                    </div>

                    <div class="flex-1 space-y-1">
                      <div class=" bg-gray-50 px-2 py-1 rounded-md shadow-sm text-gray-700 text-sm inline-block mb-2">
                        📅 : <?php echo $time ?>
                      </div>

                      <!-- Thông báo -->
                      <p class="text-sm text-gray-700 leading-snug">
                        <span class="font-semibold text-gray-900">Sản phẩm:</span>
                        <span class="text-gray-800"><?= htmlspecialchars($row['bookName']) ?></span><br>
                        <span class="text-gray-500">Tình trạng:</span>
                        <span class="text-blue-600 font-medium"><?= $text ?></span>
                      </p>
                    </div>
                  </div>

                </li>
                <?php
              }
            }
          } else {
            ?>
            <li class="px-4 py-3 text-center text-blue-600 hover:text-blue-800">
              <a href="/LTW-UD2/account.php">Đăng nhập </a>
            </li>
          <?php } ?>
        </ul>
      </div>

    </div>
    <!-- Giỏ hàng -->
    <?php
    if(isset($_SESSION['user_id'])){
    ?>
    <a href="/LTW-UD2/zui/cart.php">

      <div class="relative flex flex-col items-center">
        <span class="text-xl">🛒 </span>
        <span>Giỏ hàng</span>
        <span id="cart-count" class="absolute -top-1 -right-2 text-xs bg-red-600 text-white rounded-full px-1">
          <?php echo $countOfCart ?? 0 ?>
        </span>

      </div>
    </a>
    <?php }?>

    <!-- Tài khoản -->
    <div class="flex flex-col items-center cursor-pointer">
    <?php if(isset($_SESSION["user_id"])){
    ?>
      <span class="text-xl"><a href="/LTW-UD2/account.php" class="cursor-pointer">
        👤</a>
      </span>
      <a href="/LTW-UD2/account.php" class="cursor-pointer text-gray-600 hover:text-gray-800 transition duration-200">Tài khoản</a>
    <?php
    }else{?>
      <span class="text-xl">
        <a href="javascript:void(0)" 
        onclick="openLoginModal()">👤</a>
      </span>
      <a href="javascript:void(0)" onclick="openLoginModal()">Tài khoản</a>
    <?php
    }?>
    </div>
    <!-- Modal -->
    <div id="loginModal"
      class="fixed inset-0 flex  justify-center items-center z-50 opacity-0 pointer-events-none transition-opacity duration-300 bg-black bg-opacity-0"
      onclick="handleBackdropClick(event)">
      <div id="modalContent"
        class="bg-white bg-opacity-95 p-6 rounded-xl w-[400px]   relative shadow-xl transform translate-y-[-20px] transition-transform duration-300">
        <div id="loginFormContent">Đang tải...</div>
      </div>
    </div>

<style>
  #loginModal.show {
    opacity: 1;
    pointer-events: auto;
    background-color: rgba(0, 0, 0, 0.5);
  }

  #loginModal.show #modalContent {
    transform: translateY(0);
  }
</style>

<script>
  function openLoginModal() {
    const modal = document.getElementById('loginModal');
    modal.classList.add('show');

    fetch('./components/login2.php')
      .then(res => res.text())
      .then(html => {
        document.getElementById('loginFormContent').innerHTML = html;
      })
      .catch(() => {
        document.getElementById('loginFormContent').innerHTML = "<p class='text-red-500'>Không thể tải form.</p>";
      });
  }
  function switchTab(tab) {
  console.log(tab);  

  const loginTab = document.getElementById('loginTab');
  const registerTab = document.getElementById('registerTab');
  const formLogin = document.getElementById('formdangnhap');
  const formRegister = document.getElementById('formdangki');
  console.log(loginTab.classList);

  if (tab === 'login') {
    loginTab.classList.add('text-red-600', 'font-semibold', 'border-red-600');
    loginTab.classList.remove('text-gray-600');

    registerTab.classList.remove('text-red-600', 'font-semibold', 'border-red-600');
    registerTab.classList.add('text-gray-600');

    formLogin.classList.remove('hidden');
    formRegister.classList.add('hidden');
  } else if (tab === 'register') {
    registerTab.classList.add('text-red-600', 'font-semibold', 'border-red-600');
    registerTab.classList.remove('text-gray-600');

    loginTab.classList.remove('text-red-600', 'font-semibold', 'border-red-600');
    loginTab.classList.add('text-gray-600');

    formRegister.classList.remove('hidden');
    formLogin.classList.add('hidden');
  } else {
    console.error('Tab không hợp lệ:', tab);
  }
}

  function togglePassword(inputId = 'passwordInput', buttonId = 'toggleBtn') {
    const passwordInput = document.getElementById(inputId);
    const toggleBtn = document.getElementById(buttonId);

    if (passwordInput && toggleBtn) {
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.textContent = 'Ẩn';
      } else {
        passwordInput.type = 'password';
        toggleBtn.textContent = 'Hiện';
      }
    }
  }

  function closeLoginModal() {
    document.getElementById('loginModal').classList.remove('show');
  }

  // Đóng bằng phím ESC
  document.addEventListener('keydown', function (e) {
    if (e.key === "Escape") {
      closeLoginModal();
    }
  });

  // Đóng khi click ra ngoài modalContent
  function handleBackdropClick(event) {
    const modalContent = document.getElementById('modalContent');
    if (!modalContent.contains(event.target)) {
      closeLoginModal();
    }
  }
function isValidPhoneNumber(phone) {
  const regex = /^0\d{9}$/;
  return regex.test(phone);
}

function isValidPassword(password) {
  const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
  return regex.test(password);
}

function validateRegisterForm() {
  const phone = document.querySelector('#formdangki input[name="newuser_telephone"]').value.trim();
  const password = document.querySelector('#formdangki input[name="user_password"]').value.trim();
  const confirmPassword = document.querySelector('#formdangki input[name="user_comfirm_password"]').value.trim();

  if (/[a-zA-Z]/.test(phone)) {
    alert("Số điện thoại không được chứa chữ cái.");
    return false;
  }

  if (!isValidPhoneNumber(phone)) {
    alert("Số điện thoại không hợp lệ. Phải có 10 số và bắt đầu bằng 0.");
    return false;
  }

  if (!isValidPassword(password)) {
    alert("Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.");
    return false;
  }

  if (password !== confirmPassword) {
    alert("Mật khẩu xác nhận không khớp.");
    return false;
  }

  return true;
}

// Gắn kiểm tra khi submit
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formdangki");
  if (form) {
    form.addEventListener("submit", function (e) {
      if (!validateRegisterForm()) {
        e.preventDefault(); // chặn submit nếu không hợp lệ
      }
    });
  }
});
</script>
<?php
if (isset($_POST['login_submit'])) {
    $phone = trim($_POST['user_telephone'] ?? '');
    $password = $_POST['user_password'] ?? '';

    if (empty($phone) || empty($password)) {
        echo "<script>alert('Vui lòng nhập đầy đủ thông tin.');</script>";
    } else {
        // Chuẩn bị truy vấn lấy thông tin người dùng
        $stmt = $conn->prepare("SELECT id, password, status_user FROM users WHERE phoneNumber = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            if ((int)$user['status_user'] === 0) {
                // Nếu tài khoản bị khóa
                echo "<script>alert('Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');</script>";
            } elseif (password_verify($password, $user['password'])) {
                // Đăng nhập thành công
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION["user_id"] = $user["id"];
                echo "<script>alert('Đăng nhập thành công!'); window.location.href='index.php';</script>";
                exit;
            } else {
                // Mật khẩu sai
                echo "<script>alert('Sai số điện thoại hoặc mật khẩu!');</script>";
            }
        } else {
            // Không tìm thấy người dùng
            echo "<script>alert('Sai số điện thoại hoặc mật khẩu!');</script>";
        }

        $stmt->close();
    }
}


if (isset($_POST['submit_register'])) {
    $phone = trim($_POST['newuser_telephone'] ?? '');
    $password = $_POST['user_password'] ?? '';
    $confirm = $_POST['user_comfirm_password'] ?? '';

    // PHP kiểm tra thêm (phòng trường hợp bypass JS)
    if (!preg_match('/^0\d{9}$/', $phone)) {
        echo "<script>alert('Số điện thoại không hợp lệ. Phải có 10 số và bắt đầu bằng 0.');</script>";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/', $password)) {
        echo "<script>alert('Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.');</script>";
    } elseif ($password !== $confirm) {
        echo "<script>alert('Mật khẩu nhập lại không khớp.');</script>";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE phoneNumber = ?");
        $check->bind_param("s", $phone);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>alert('Số điện thoại đã tồn tại!');</script>";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $fullName = "Người dùng mới";
            $userName = 'user_' . rand(1000, 9999);
            $created_at = date('Y-m-d H:i:s');
            $email = NULL;
            $avatar = 'default.png';
            $status_user = 1;

            $stmt = $conn->prepare("INSERT INTO users 
                (phoneNumber, password, fullName, userName, email, avatar, status_user, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "ssssssis",
                $phone,
                $hashedPassword,
                $fullName,
                $userName,
                $email,
                $avatar,
                $status_user,
                $created_at
            );
            if ($stmt->execute()) {
                echo "<script>alert('Đăng ký thành công! Vui lòng đăng nhập.');</script>";
            } else {
                echo "<script>alert('Đăng ký thất bại! " . $stmt->error . "');</script>";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>


    <!-- Quốc kỳ -->
    <div id="vietNam">
      <img src="/LTW-UD2/images/forHeader/vietNam.png" alt="">

    </div>
    <?php
    if (isset($_SESSION['user_id'])) {
    
    ?>
    <a href="./components/logout.php"
      class="inline-flex items-center justify-center gap-2 px-6 py-2 rounded-lg bg-gradient-to-r from-red-500 to-pink-500 text-white font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 ease-in-out">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-white" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
      </svg>
    </a>
    <?php
    }
    ?>
  </div>


  <div id="menuContent"
    class="menuContent animate-fade-in hidden absolute top-full left-10 bg-white shadow-lg z-50 w-[90vw] rounded-xl overflow-hidden ">

    <div class="flex w-[90vw] min-h-[300px]">

      <div class="min-w-60 bg-white border-r border-gray-200 shadow-lg">
        <?php for ($i = 6; $i < 13; $i++) { ?>
          <div
            class="tablinks px-4 py-3 hover:bg-gray-100 cursor-pointer text-sm font-medium border-l-4 border-transparent hover:border-pink-500 transition-all"
            data-id="<?php echo $i; ?>">
            Lớp <?php echo $i ?>
          </div>
        <?php } ?>
        <script>
          document.querySelectorAll(".tablinks").forEach(tab => {
            tab.addEventListener("mouseenter", function () {
              let Class = this.dataset.id;
              openTab(this, Class);
            })
          })
          function openTab(tab, Class) {
            const Tablinks = document.querySelectorAll(".tablinks");
            for (let i = 0; i < Tablinks.length; i++) {
              Tablinks[i].className = Tablinks[i].className.replace(" onTab", "");
            }
            tab.classList.add("onTab");
          }
        </script>
      </div>

      <div class="flex-1 p-6 overflow-y-scroll">
        <div class="flex items-center gap-2 mb-4">
          <img src="/LTW-UD2/images/forHeader/menuBook.png" alt="" class="w-5 h-5">
          <span class="font-bold text-sm uppercase">Sách trong nước</span>
        </div>

        <div class="contentMenu gap-6 text-sm text-gray-700">
          <!-- div*3 -->
        </div>
        <script>
          const contentMenu = document.querySelector(".contentMenu");
          const tablinks = document.querySelectorAll(".tablinks");
          tablinks.forEach(tab => {
            tab.addEventListener("mouseenter", function () {
              const Class = this.dataset.id;
              fetch(`/LTW-UD2/contentMenu.php/?Class=${Class}`).
                then(response => response.text()).
                then(data => {
                  contentMenu.innerHTML = data;
                })
            })
          })
        </script>
      </div>
    </div>
  </div>

</div>
</div>


<script>
  const menuTrigger = document.getElementById('menuTrigger');
  const menuContent = document.getElementById('menuContent');

  menuTrigger.addEventListener('click', () => {
    menuContent.classList.toggle('hidden');
  });

  document.addEventListener('click', (e) => {
    if (!menuTrigger.contains(e.target) && !menuContent.contains(e.target)) {
      menuContent.classList.add('hidden');
    }
  });
</script>


<script>
  function showNoti() {
    clearTimeout(timeout);
    notiPanel.classList.remove('hidden');
  }

  function hideNoti() {
    timeout = setTimeout(() => {
      notiPanel.classList.add('hidden');
    }, 200);
  }                     
</script>

<script>
  const notiPanel = document.getElementById('notificationPanel');

  function toggleNoti() {
    notiPanel.classList.toggle('hidden');
  }

  document.addEventListener('click', function (e) {
    const trigger = e.target.closest('[onclick="toggleNoti()"]');
    if (!trigger && !notiPanel.contains(e.target)) {
      notiPanel.classList.add('hidden');
    }
  });
</script>

<script>
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
</script>


