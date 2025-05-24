<?php
session_start();
require_once "database/database.php";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$loginError = '';
$registerSuccess = '';

// Handle login form submission
if (isset($_POST['login_submit'])) {
    $phone = $_POST['user_telephone'];
    $password = $_POST['user_password'];
    
    $query = "SELECT * FROM users WHERE phoneNumber = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$phone]);
    $user = $stmt->fetch();
    
    if ($stmt->rowCount() > 0) {
        if (password_verify($password, $user['password'])) {
            $_SESSION["user_id"] = $user["id"];
            
            // Redirect to previous page or home
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header("Location: " . $redirect);
            exit;
        } else {
            $loginError = "Sai mật khẩu!";
        }
    } else {
        $loginError = "Sai số điện thoại!";
    }
}

// Handle register form submission
if (isset($_POST['submit_register'])) {
    $phone = $_POST['newuser_telephone'] ?? '';
    $password = $_POST['user_password'] ?? '';
    $confirmPassword = $_POST['user_comfirm_password'] ?? '';
    
    // Validate phone number
    if (!preg_match("/^0\d{9}$/", $phone)) {
        $registerError = "Số điện thoại không hợp lệ. Phải có 10 số và bắt đầu bằng 0.";
    } 
    // Validate password
    elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        $registerError = "Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.";
    }
    // Check if passwords match
    elseif ($password !== $confirmPassword) {
        $registerError = "Mật khẩu xác nhận không khớp.";
    } 
    else {
        // Check if phone exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phoneNumber = ?");
        $stmt->execute([$phone]);
        
        if ($stmt->rowCount() > 0) {
            $registerError = "Số điện thoại đã tồn tại.";
        } else {
            // Create new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $fullName = 'New user'; // Default name
            
            $stmt = $pdo->prepare("INSERT INTO users (phoneNumber, password, fullName) VALUES (?, ?, ?)");
            $stmt->execute([$phone, $hashedPassword, $fullName]);
            
            $registerSuccess = "Đăng ký thành công! Vui lòng đăng nhập.";
            $activeTab = 'login'; // Switch to login tab after successful registration
        }
    }
}

// Set default active tab
$activeTab = isset($activeTab) ? $activeTab : (isset($_GET['tab']) ? $_GET['tab'] : 'login');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống sách</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-6 bg-white rounded-xl shadow-md">
        <div class="mb-6 text-center">
            <a href="index.php" class="inline-block">
                <img src="images/forHeader/logo.jpg" alt="Logo" class="h-16 mx-auto">
            </a>
            <h2 class="text-2xl font-bold text-gray-800 mt-4">Chào mừng bạn đến với hệ thống</h2>
            <p class="text-gray-600">Vui lòng đăng nhập hoặc đăng ký</p>
        </div>
        
        <!-- Tab Navigation -->
        <div class="flex justify-center mb-6">
            <div id="loginTab"
                class="<?= $activeTab == 'login' ? 'text-red-600 border-red-600' : 'text-gray-600 border-transparent'; ?> text-lg font-semibold cursor-pointer border-b-2 mr-8"
                onclick="switchTab('login')">Đăng nhập</div>
            <div id="registerTab"
                class="<?= $activeTab == 'register' ? 'text-red-600 border-red-600' : 'text-gray-600 border-transparent'; ?> text-lg font-medium border-b-2 hover:border-gray-400 cursor-pointer"
                onclick="switchTab('register')">Đăng ký</div>
        </div>

        <!-- Login Form -->
        <form id="formdangnhap" name="login" action="" method="POST" class="space-y-4 <?= $activeTab == 'login' ? '' : 'hidden'; ?>">
            <?php if ($loginError): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= $loginError ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($registerSuccess): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= $registerSuccess ?></span>
                </div>
            <?php endif; ?>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                <input name="user_telephone" type="tel" placeholder="Nhập số điện thoại"
                    class="w-full px-4 py-2 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 mt-1">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                <div class="relative mt-1">
                    <input name="user_password" type="password" id="passwordInput" placeholder="Nhập mật khẩu"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none">
                    <button type="button" onclick="togglePassword()" id="toggleBtn"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-blue-600 text-sm font-medium">Hiện</button>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">Ghi nhớ</label>
                </div>
                <a href="#" class="text-sm text-blue-600 hover:text-blue-800">Quên mật khẩu?</a>
            </div>

            <button type="submit"
                name="login_submit"
                class="w-full transition bg-red-500 hover:bg-red-600 text-white font-semibold py-2 rounded-lg">
                Đăng nhập
            </button>
            
            <div class="mt-4 text-center text-sm text-gray-600">
                Chưa có tài khoản? 
                <a href="javascript:void(0)" onclick="switchTab('register')" class="text-blue-600 hover:underline">Đăng ký ngay</a>
            </div>
        </form>

        <!-- Register Form -->
        <form id="formdangki" onsubmit="return validateRegisterForm()" class="space-y-4 <?= $activeTab == 'register' ? '' : 'hidden'; ?>" action="" method="post">
            <?php if (isset($registerError)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= $registerError ?></span>
                </div>
            <?php endif; ?>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                <input name="newuser_telephone" type="tel" placeholder="Nhập số điện thoại"
                    class="w-full px-4 py-2 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 mt-1">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                <div class="relative mt-1">
                    <input name="user_password" type="password" id="password" placeholder="Nhập mật khẩu"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none">
                    <button type="button" onclick="togglePassword('password', 'togglePasswordBtn')" id="togglePasswordBtn"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-blue-600 text-sm font-medium">Hiện</button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Nhập lại mật khẩu</label>
                <div class="relative mt-1">
                    <input name="user_comfirm_password" type="password" id="confirmPassword" placeholder="Nhập lại mật khẩu"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none">
                    <button type="button" onclick="togglePassword('confirmPassword', 'toggleConfirmBtn')" id="toggleConfirmBtn"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-blue-600 text-sm font-medium">Hiện</button>
                </div>
            </div>

            <div class="text-xs text-gray-600 mt-2">
                <p>Mật khẩu phải bao gồm:</p>
                <ul class="list-disc pl-5 mt-1 space-y-1">
                    <li>Ít nhất 8 ký tự</li>
                    <li>Có ít nhất một chữ hoa</li>
                    <li>Có ít nhất một chữ thường</li>
                    <li>Có ít nhất một số</li>
                    <li>Có ít nhất một ký tự đặc biệt (@$!%*?&)</li>
                </ul>
            </div>

            <button type="submit"
                name="submit_register"
                class="w-full transition bg-red-500 hover:bg-red-600 text-white font-semibold py-2 rounded-lg">
                Đăng ký
            </button>
            
            <div class="mt-4 text-center text-sm text-gray-600">
                Đã có tài khoản? 
                <a href="javascript:void(0)" onclick="switchTab('login')" class="text-blue-600 hover:underline">Đăng nhập</a>
            </div>
        </form>
    </div>

    <script>
        function switchTab(tab) {
            const loginTab = document.getElementById('loginTab');
            const registerTab = document.getElementById('registerTab');
            const formLogin = document.getElementById('formdangnhap');
            const formRegister = document.getElementById('formdangki');

            if (tab === 'login') {
                loginTab.classList.add('text-red-600', 'font-semibold', 'border-red-600');
                loginTab.classList.remove('text-gray-600', 'border-transparent');

                registerTab.classList.remove('text-red-600', 'font-semibold', 'border-red-600');
                registerTab.classList.add('text-gray-600', 'border-transparent');

                formLogin.classList.remove('hidden');
                formRegister.classList.add('hidden');
                
                // Update URL to remember the tab
                history.replaceState(null, null, '?tab=login');
            } else if (tab === 'register') {
                registerTab.classList.add('text-red-600', 'font-semibold', 'border-red-600');
                registerTab.classList.remove('text-gray-600', 'border-transparent');

                loginTab.classList.remove('text-red-600', 'font-semibold', 'border-red-600');
                loginTab.classList.add('text-gray-600', 'border-transparent');

                formRegister.classList.remove('hidden');
                formLogin.classList.add('hidden');
                
                // Update URL to remember the tab
                history.replaceState(null, null, '?tab=register');
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
    </script>
</body>
</html>