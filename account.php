<?php
session_start();
require_once "./database/database.php";
require_once "./database/user.php";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ltw_ud2";
$conn = new mysqli($servername, $username, $password, $dbname);

$userTable = new UsersTable();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $phone = trim($_POST['user_telephone'] ?? '');
    $password = $_POST['user_password'] ?? '';

    if (empty($phone) || empty($password)) {
        $errorMessage = "Vui lòng nhập số điện thoại và mật khẩu.";
    } else {
        $query = "SELECT * FROM users WHERE phoneNumber = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($result && $result->num_rows > 0) {
            if (password_verify($password, $user['password'])) {
                $_SESSION["user_id"] = $user["id"];
                header("Location: index.php");
                exit;
            } else {
                $errorMessage = "Sai mật khẩu! Vui lòng kiểm tra lại.";
            }
        } else {
            $errorMessage = "Không tìm thấy tài khoản với số điện thoại này.";
        }
    }
}

$user = null;
$user_id = $_SESSION["user_id"] ?? null;

if ($user_id) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
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
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updatePassword'])) {
    $old_pass = $_POST['user_old_password'] ?? '';
    $new_pass = $_POST['user_new_password'] ?? '';
    $confirm_pass = $_POST['user_confirm_new_password'] ?? '';
    
    if (empty($old_pass) || empty($new_pass) || empty($confirm_pass)) {
        $errorMessage = "Vui lòng điền đầy đủ thông tin mật khẩu.";
    } elseif ($new_pass !== $confirm_pass) {
        $errorMessage = "Mật khẩu mới không khớp với xác nhận.";
    } elseif (strlen($new_pass) < 6) {
        $errorMessage = "Mật khẩu mới phải có ít nhất 6 ký tự.";
    } else {
        if ($userTable->verifyUserPassword($user_id, $old_pass)) {
            if ($userTable->updateUserPassword($user_id, $new_pass)) {
                $successMessage = "Đổi mật khẩu thành công!";
            } else {
                $errorMessage = "Lỗi khi cập nhật mật khẩu. Vui lòng thử lại.";
            }
        } else {
            $errorMessage = "Mật khẩu hiện tại không đúng!";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateProfile'])) {
    $userData = [];
    
    $userName = trim($_POST['userName'] ?? '');
    if (!empty($userName)) {
        $userData['userName'] = $userName;
    }
    
    $fullName = trim($_POST['fullName'] ?? '');
    if (!empty($fullName)) {
        $userData['fullName'] = $fullName;
    }
    
    $phoneNumber = trim($_POST['phoneNumber'] ?? '');
    if (!empty($phoneNumber)) {
        if (preg_match('/^[0-9]{9,15}$/', $phoneNumber)) {
            $userData['phoneNumber'] = $phoneNumber;
            $sql = "SELECT id FROM users WHERE phoneNumber = ? AND id != ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $phoneNumber, $user_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errorMessage = "Số điện thoại đã được sử dụng bởi người dùng khác.";
                unset($userData['phoneNumber']); 
            }
            $stmt->close();
        } else {
            $errorMessage = "Số điện thoại không hợp lệ.";
        }
    }

    
    $email = trim($_POST['email'] ?? '');
    if (!empty($email)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $userData['email'] = $email;
        } else {
            $errorMessage = "Email không hợp lệ.";
        }
    }
    
    $date = trim($_POST['dateOfBirth'] ?? '');
    $month = trim($_POST['monthOfBirth'] ?? '');
    $year = trim($_POST['yearOfBirth'] ?? '');
    
    if (!empty($date) && !empty($month) && !empty($year)) {
        if (checkdate((int)$month, (int)$date, (int)$year)) {
            $dob = sprintf("%04d-%02d-%02d", intval($year), intval($month), intval($date));
            $userData['dateOfBirth'] = $dob;
        } else {
            $errorMessage = "Ngày sinh không hợp lệ.";
        }
    }
    
    if (empty($errorMessage) && !empty($userData)) {
        if ($userTable->updateUserProfile($user_id, $userData)) {
            $successMessage = "Cập nhật thông tin thành công!";
            
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            if (!empty($user["dateOfBirth"])) {
                $birthDate = new DateTime($user["dateOfBirth"]);
                $birthDay = $birthDate->format('d');
                $birthMonth = $birthDate->format('m');
                $birthYear = $birthDate->format('Y');
            }
        } else {
            $errorMessage = "Lỗi khi cập nhật thông tin. Vui lòng thử lại.";
        }
    } elseif (empty($userData) && empty($errorMessage)) {
        $errorMessage = "Không có thông tin nào được cập nhật.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài Khoản</title>

    <!-- fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Tailwindcss -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <?php include_once "./components/header2.php"; ?>

    <!-- Notification Messages -->
    <?php if (!empty($errorMessage)): ?>
    <div id="errorAlert" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-auto my-4 max-w-6xl" role="alert">
        <strong class="font-bold">Lỗi! </strong>
        <span class="block sm:inline"><?php echo htmlspecialchars($errorMessage); ?></span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
            <svg onclick="document.getElementById('errorAlert').style.display = 'none'" class="fill-current h-6 w-6 text-red-500 cursor-pointer" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <title>Close</title>
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
            </svg>
        </span>
    </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
    <div id="successAlert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-auto my-4 max-w-6xl" role="alert">
        <strong class="font-bold">Thành công! </strong>
        <span class="block sm:inline"><?php echo htmlspecialchars($successMessage); ?></span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
            <svg onclick="document.getElementById('successAlert').style.display = 'none'" class="fill-current h-6 w-6 text-green-500 cursor-pointer" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <title>Close</title>
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
            </svg>
        </span>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <?php
    if (isset($_SESSION["user_id"])) {
        include_once "./components/changeInforUser.php";
    } else {
        include_once "./components/login2.php";
    }
    ?>

    <?php include_once "./components/footer.php"; ?>
    
    <!-- JavaScript -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const errorAlert = document.getElementById('errorAlert');
            const successAlert = document.getElementById('successAlert');
            
            if (errorAlert) {
                errorAlert.style.display = 'none';
            }
            
            if (successAlert) {
                successAlert.style.display = 'none';
            }
        }, 5000);
        
        // Form toggle functionality
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
        document.addEventListener('DOMContentLoaded', function() {
            // Profile form validation
            const profileForm = document.querySelector('.profile-form');
            if (profileForm) {
                profileForm.addEventListener('submit', function(e) {
                    const userName = document.querySelector('input[name="userName"]')?.value.trim();
                    const fullName = document.querySelector('input[name="fullName"]')?.value.trim();
                    
                    if (!userName || !fullName) {
                        e.preventDefault();
                        alert('Vui lòng nhập đầy đủ tên đăng nhập và họ tên');
                        return false;
                    }
                });
            }
            
            // Password form validation
            const passwordForm = document.querySelector('form:has(input[name="user_old_password"])');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    const oldPass = document.getElementById('currentPassword')?.value.trim();
                    const newPass = document.getElementById('newPassword')?.value.trim();
                    const confirmPass = document.getElementById('confirmNewPassword')?.value.trim();
                    
                    if (!oldPass || !newPass || !confirmPass) {
                        e.preventDefault();
                        alert('Vui lòng nhập đầy đủ thông tin mật khẩu');
                        return false;
                    }
                    
                    if (newPass !== confirmPass) {
                        e.preventDefault();
                        alert('Mật khẩu mới và xác nhận không khớp');
                        return false;
                    }
                    
                    if (newPass.length < 6) {
                        e.preventDefault();
                        alert('Mật khẩu mới phải có ít nhất 6 ký tự');
                        return false;
                    }
                });
            }
        });
    </script>
</body>

</html>