<?php
require '../database/database.php';

// Hàm làm sạch input
function clean($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $account = clean($_POST['loginAccount'] ?? '');
        $password = $_POST['loginPassword'] ?? '';

        // Kiểm tra người dùng có tồn tại qua SDT hoặc Email
        $stmt = $pdo->prepare("SELECT * FROM nhanvien WHERE SDT = :account OR Mail = :account LIMIT 1");
        $stmt->execute(['account' => $account]);
        $nhanvien = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($nhanvien) {
            if ($password === $nhanvien['MatKhau']) {
                session_start();
                $_SESSION['user'] = $nhanvien['IDNhanVien'];

                if ($nhanvien['ID_NhomQuyen'] == 1) {
                    header("Location: http://localhost/LTW-UD2/admin/");
                } else {
                    header("Location: http://localhost/LTW-UD2/index.php");
                }
                exit;
            } else {
                echo "<script>alert('Mật khẩu không đúng!');window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Tài khoản không tồn tại!');window.history.back();</script>";
            exit;
        }

    } elseif ($action === 'register') {
        $account = clean($_POST['registerAccount'] ?? '');
        if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
            // Là email
            $isEmail = true;
        } elseif (preg_match('/^0\d{9}$/', $account)) {
            // Là số điện thoại Việt Nam hợp lệ (bắt đầu bằng 0, tổng 10 chữ số)
            $isEmail = false;
        } else {
            echo "<script>alert('Vui lòng nhập đúng định dạng Email hoặc Số điện thoại!');window.history.back();</script>";
            exit;
        }
        $password = $_POST['registerPassword'] ?? '';
        echo "Mật khẩu nhập vào: " . $password;
        $otp = clean($_POST['otp'] ?? '');

        if ($account === '' || $password === '' || $otp === '') {
            echo "Vui lòng nhập đầy đủ thông tin đăng ký.";
            exit;
        }

        if ($otp !== '123456') {
            echo "<script>alert('Mã OTP không đúng!');window.history.back();</script>";
            exit;
        }

        // Kiểm tra tài khoản tồn tại
        $stmt = $pdo->prepare("SELECT IDNhanVien FROM nhanvien WHERE SDT = ? OR Mail = ?");
        $stmt->execute([$account, $account]);

        if ($stmt->fetch()) {
            echo "<script>alert('Tài khoản đã tồn tại!');window.history.back();</script>";
            exit;
        }
        $id_tk = rand(1,999);
        $stmt = $pdo->prepare("INSERT INTO taikhoan (ID_TK, ID_NhomQuyen ) VALUES (?, ?)");
        $stmt->execute([$id_tk,5]);
        // Thêm tài khoản vào bảng nhanvien
        if ($isEmail) {
            $mail = $account;
            $sdt = null;
        } else {
            $mail = null;
            $sdt = $account;
        }
        $stmt = $pdo->prepare("INSERT INTO nhanvien (Mail, SDT, ID_TK, MatKhau, username, ID_NhomQuyen) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$mail, $sdt, $id_tk, $password, '', 5]);



        echo "<script>alert('Đăng ký thành công! Hãy đăng nhập.'); window.history.back();</script>";
        exit;

    } else {
        echo "Hành động không hợp lệ!";
    }
}
?>


<div class="container">
    <div class="form-box">
        <!-- Tabs -->
        <div class="tab">
            <button id="loginTab" class="active" onclick="toggleTab(true)">Đăng nhập</button>
            <button id="registerTab" onclick="toggleTab(false)">Đăng ký</button>
        </div>

        <!-- Form Đăng nhập -->
        <form id="loginForm" action="Form.php" method="POST" onsubmit="return validateLoginForm()">
            <input type="hidden" name="action" value="login">

            <label for="loginAccount">Số điện thoại hoặc email</label>
            <input id="loginAccount" type="text" name="loginAccount" placeholder="Nhập số điện thoại hoặc email">

            <label for="loginPassword">Nhập mật khẩu</label>
            <div class="password-wrapper">
                <input id="loginPassword" type="password" name="loginPassword" placeholder="Nhập mật khẩu">
                <span id="toggleLoginPassword" class="toggle-password" onclick="togglePassword('loginPassword', 'toggleLoginPassword')">Hiện</span>
            </div>

            <label id="loginAccount_error" style="display: none; color: red;">Vui lòng nhập đầy đủ thông tin</label>
            <span class="forgot-password">Quên mật khẩu?</span>

            <input type="submit" value="Đăng nhập">
        </form>

        <!-- Form Đăng ký -->
        <form id="registerForm" action="Form.php" method="POST" style="display: none;" onsubmit="return validateRegisterForm()">
            <input type="hidden" name="action" value="register">

            <label for="registerAccount">Số điện thoại hoặc email</label>
            <div class="otp-wrapper">
                <input id="registerAccount" type="text" name="registerAccount" placeholder="Nhập số điện thoại hoặc email">
                <span class="send-otp" onclick="alert('Chức năng OTP đang được phát triển')">Gửi OTP</span>
            </div>

            <label for="otp">Nhập mã OTP</label>
            <input id="otp" type="text" name="otp" placeholder="Nhập mã OTP">

            <label for="registerPassword">Nhập mật khẩu</label>
            <div class="password-wrapper">
                <input id="registerPassword" type="password" name="registerPassword" placeholder="Nhập mật khẩu">
                <span id="toggleRegisterPassword" class="toggle-password" onclick="togglePassword('registerPassword', 'toggleRegisterPassword')">Hiện</span>
            </div>

            <label id="register_error" style="display: none; color: red;">Vui lòng nhập đầy đủ thông tin</label>

            <input type="submit" value="Đăng ký">
        </form>
    </div>
</div>

<style>
.container {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.form-box {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 350px;
}

.tab {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
}

.tab > button {
    width: 50%;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    background-color: #eee;
}

.tab > button.active {
    border-bottom: 2px solid red;
    background-color: white;
}

input {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.password-wrapper, .otp-wrapper {
    position: relative;
}

.toggle-password, .send-otp {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: blue;
    font-size: 14px;
}


.forgot-password {
    display: block;
    margin-top: 10px;
    color: blue;
    cursor: pointer;
}
input[type="submit"] {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border: none;
    border-radius: 10px;
    background-color: red;
    color: white;
    cursor: pointer;
}
input:focus {
    border: 1px solid red; 
    outline: none;
    box-shadow: 0 0 5px red; 
    transition: all 0.3s ease-in-out;
}

</style>
<script>
function toggleTab(isLogin) {
    document.getElementById('loginForm').style.display = isLogin ? 'block' : 'none';
    document.getElementById('registerForm').style.display = isLogin ? 'none' : 'block';

    document.getElementById('loginTab').classList.toggle('active', isLogin);
    document.getElementById('registerTab').classList.toggle('active', !isLogin);
}

function togglePassword(inputId, toggleId) {
    const input = document.getElementById(inputId);
    const toggle = document.getElementById(toggleId);
    if (input.type === "password") {
        input.type = "text";
        toggle.innerText = "Ẩn";
    } else {
        input.type = "password";
        toggle.innerText = "Hiện";
    }
}

function validateLoginForm() {
    const acc = document.getElementById('loginAccount').value.trim();
    const pass = document.getElementById('loginPassword').value.trim();
    const errorLabel = document.getElementById('loginAccount_error');

    if (acc === '' || pass === '') {
        errorLabel.style.display = 'block';
        return false;
    }

    errorLabel.style.display = 'none';
    return true;
}

function validateRegisterForm() {
    const acc = document.getElementById('registerAccount').value.trim();
    const otp = document.getElementById('otp').value.trim();
    const pass = document.getElementById('registerPassword').value.trim();
    const errorLabel = document.getElementById('register_error');

    if (acc === '' || otp === '' || pass === '') {
        errorLabel.style.display = 'block';
        return false;
    }

    errorLabel.style.display = 'none';
    return true;
}
</script>