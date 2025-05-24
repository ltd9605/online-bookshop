
<div class="bg-gray-100 min-h-screen flex items-center justify-center">
  <div class="flex max-w-6xl w-full rounded-2xl overflow-hidden gap-x-8 p-8">
    <div class="w-80 bg-white rounded-2xl shadow-md p-6">
      <div class="flex flex-col items-center">
        <div class="w-20 h-20 rounded-full border-4 border-gray-200 flex items-center justify-center">
          <?php if (!empty($user["avatar"])): ?>
            <img src="<?php echo htmlspecialchars($user["avatar"]); ?>" alt="Profile" class="w-full h-full object-cover rounded-full">
          <?php else: ?>
            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6l3 6h6l-4.5 4 1.5 6L12 18l-6 4 1.5-6L3 12h6l3-6z" />
            </svg>
          <?php endif; ?>
        </div>
        <p class="mt-2 text-lg font-medium"><?php echo htmlspecialchars($user["fullName"] ?? 'User'); ?></p>
        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($user["email"] ?? ''); ?></p>
      </div>

      <div class="border-t border-gray-200 my-5"></div>

      <nav class="space-y-4 text-sm">
        <!-- Thông tin tài khoản -->
        <div>
          <p class="text-blue-400 font-bold text-lg mb-3">Thông tin tài khoản</p>
          <div class="space-y-2">
            <div onclick="showForm('mainForm')" class="cursor-pointer bg-red-50 hover:bg-red-100 text-red-600 font-medium px-4 py-2 rounded-lg shadow-sm transition">
              Hồ sơ cá nhân
            </div>
            <div onclick="showForm('changePass')" class="cursor-pointer bg-red-50 hover:bg-red-100 text-red-600 font-medium px-4 py-2 rounded-lg shadow-sm transition">
              Đổi mật khẩu
            </div>
          </div>
        </div>

        <!-- Đơn hàng -->
        <div class="pt-4 border-t border-gray-200">
          <div class="space-y-2">
            <a href="./zui/cart.php" class="text-sm text-pink-500 block bg-gray-50 hover:bg-gray-100 text-gray-800 px-4 py-2 rounded-lg shadow-sm transition">
              Đơn hàng của tôi
            </a>
            <a href="./zui/orderHistory.php" class="text-sm text-pink-500 block bg-gray-50 hover:bg-gray-100 text-gray-800 px-4 py-2 rounded-lg shadow-sm transition">
              Lịch sử đơn hàng
            </a>
          </div>
        </div>
      </nav>
      <?php
      if (!isset($_SESSION['user_id'])) {
          header("Location: /LTW-UD2/");
          exit(); 
      }

      $user_id = intval($_SESSION['user_id']); 

      $sql = "SELECT * FROM users WHERE users.id = $user_id";
      $result = $conn->query($sql);
      $user = $result->fetch_assoc();

      // Parse birthday if it exists
      $birthDate = null;
      $birthDay = '';
      $birthMonth = '';
      $birthYear = '';
      
      if (!empty($user["dateOfBirth"])) {
          $birthDate = new DateTime($user["dateOfBirth"]);
          $birthDay = $birthDate->format('d');
          $birthMonth = $birthDate->format('m');
          $birthYear = $birthDate->format('Y');
      }
      ?>
    </div>
        
    <div class="mainForm bg-white  rounded-2xl shadow-lg w-full max-w-2xl">
      <form  action="" method="post" class="profile-form">
        <div class="bg-white rounded-2xl  p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Hồ sơ cá nhân</h2>
      
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-1">
                User name
                <span class="text-red-500">*</span>
              </label>

              <input value="<?php echo htmlspecialchars($user["userName"] ?? ''); ?>" 
              name="userName"  
              type="text" 
              placeholder="Nhập username" 
              class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
      
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-1">
                Họ và tên
                <span class="text-red-500">*</span>
              </label>

              <input value="<?php echo htmlspecialchars($user["fullName"] ?? ''); ?>" 
              name="fullName" type="text" placeholder="Nhập họ và tên" 
              class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" 
              />
            </div>
      
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-1">
                Số điện thoại 
              </label>
              <input value="<?php echo htmlspecialchars($user["phoneNumber"] ?? ''); ?>" name="phoneNumber" type="text"
              placeholder="Nhập số điện thoại"
              class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
      
            <!-- <div class="mb-6">
              <label class="block text-gray-700 font-medium mb-1">
                Ngày sinh
              </label>
              <div class="grid grid-cols-3 gap-4 mt-2">
                <input value="<?php //echo $birthDay; ?>" name="dateOfBirth" type="text" placeholder="DD" maxlength="2" 
                class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                
                <input value="<?php //echo $birthMonth; ?>" name="monthOfBirth" type="text" placeholder="MM" maxlength="2" 
                class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                
                <input value="<?php //echo $birthYear; ?>" name="yearOfBirth" type="text" placeholder="YYYY" maxlength="4" 
                class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
            </div> -->

            <div class="mb-6">
              <label class="block text-gray-700 font-medium mb-1">
                Email
              </label>
              <input value="<?php echo htmlspecialchars($user["email"] ?? ''); ?>" name="email" type="email"
              placeholder="Nhập email"
              class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
      
            <button type="submit" 
            name="updateProfile"
            class="w-full bg-red-600 text-white py-3 rounded-xl font-semibold hover:bg-red-700 transition">
            Lưu thay đổi</button>
          </div>
      </form>
    </div>

    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-2xl changePass hidden">
      <h2 class="text-xl font-semibold text-gray-800 mb-6">Đổi mật khẩu</h2>
  
      <form class="space-y-5" action="" method="post">
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Mật khẩu hiện tại <span class="text-red-500">*</span>
          </label>
          <input id="currentPassword" name="user_old_password" type="password" placeholder="Mật khẩu hiện tại" 
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          
          <span onclick="togglePassword('currentPassword', this)"
          class="absolute top-1/2 right-4 transform -translate-y-1/2 text-blue-600 text-sm cursor-pointer">
            Hiện
          </span>
        </div>
  
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Mật khẩu mới 
            <span class="text-red-500">*</span>
          </label>
          <input id="newPassword" name="user_new_password" type="password" placeholder="Mật khẩu mới" 
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          
          <span onclick="togglePassword('newPassword', this)"
          class="absolute top-1/2 right-4 transform -translate-y-1/2 text-blue-600 text-sm cursor-pointer">Hiện</span>
        </div>
  
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Nhập lại mật khẩu mới 
            <span class="text-red-500">*</span>
          </label>
          <input id="confirmNewPassword" name="user_confirm_new_password" type="password" 
          placeholder="Nhập lại mật khẩu mới" 
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          
          <span onclick="togglePassword('confirmNewPassword', this)"
          class="absolute top-1/2 right-4 transform -translate-y-1/2 text-blue-600 text-sm cursor-pointer">Hiện</span>
        </div>
  
        <div class="pt-4">
          <button type="submit"
          name="updatePassword" 
          class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded-lg transition">
            Lưu thay đổi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Fixed duplicate function issue
function togglePassword(id, el) {
  const input = document.getElementById(id);
  if (input.type === "password") {
    input.type = "text";
    el.textContent = "Ẩn";
  } else {
    input.type = "password";
    el.textContent = "Hiện";
  }
}

function showForm(formClass) {
  const mainForm = document.querySelector('.mainForm');
  const changePass = document.querySelector('.changePass');

  if (formClass === 'mainForm') {
    mainForm.classList.remove('hidden');
    changePass.classList.add('hidden');
  } else if (formClass === 'changePass') {
    changePass.classList.remove('hidden');
    mainForm.classList.add('hidden');
  }
}

// Form validation
document.querySelector('.profile-form').addEventListener('submit', function(event) {
  const userName = document.querySelector('input[name="userName"]').value.trim();
  const fullName = document.querySelector('input[name="fullName"]').value.trim();
  
  if (!userName || !fullName) {
    event.preventDefault();
    alert('Vui lòng điền đầy đủ tên đăng nhập và họ tên');
  }
});
</script>