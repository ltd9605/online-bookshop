<?php
// filepath: c:\xampp\htdocs\LTW-UD2\admin\login.php
session_start();
require_once "../database/user.php";
$usersTable = new UsersTable();
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $admin = $usersTable->adminLogin($username, $password);

        if ($admin) {
            // Login successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['user_name'] = $admin['userName'] ?? '';
            $_SESSION['user_avatar'] = $admin['avatar'] ?? '';

            // Redirect with a small delay to show success message
            header("Refresh: 0.8; URL=index.php");
            $success = true;
        } else {
            // Login failed
            $error = "Invalid username or password.";
        }
    }
}

// Check if user is already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Book Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .form-input:focus+.input-icon {
            color: #2563eb;
        }

        .login-animation {
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-checkmark {
            animation: scaleUp 0.5s ease-out;
        }

        @keyframes scaleUp {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="1" d="M0,288L60,288C120,288,240,288,360,272C480,256,600,224,720,213.3C840,203,960,213,1080,208C1200,203,1320,181,1380,170.7L1440,160L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path></svg>') no-repeat;
            background-size: cover;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-primary-700 to-primary-900 min-h-screen flex items-center justify-center p-4 relative">
    <!-- Decorative elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute top-10 left-10 w-20 h-20 bg-white opacity-10 rounded-full"></div>
        <div class="absolute top-40 right-20 w-32 h-32 bg-white opacity-5 rounded-full"></div>
        <div class="absolute bottom-40 left-1/4 w-24 h-24 bg-white opacity-10 rounded-full"></div>
        <div class="wave"></div>
    </div>

    <div class="login-animation relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <?php if (isset($success)): ?>
            <!-- Success message -->
            <div class="flex flex-col items-center justify-center p-12 min-h-[400px]">
                <div class="success-checkmark bg-green-100 text-green-500 rounded-full p-4 mb-6">
                    <i class="fas fa-check-circle text-5xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Login Successful!</h2>
                <p class="text-gray-600 text-center mb-6">Redirecting to dashboard...</p>
                <div class="w-full max-w-[200px] h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="bg-green-500 h-full animate-pulse-slow"></div>
                </div>
            </div>
        <?php else: ?>
            <!-- Header section -->
            <div class="bg-primary-700 p-6 relative">
                <div class="flex justify-center">
                    <div class="h-20 w-20 flex items-center justify-center rounded-full bg-white bg-opacity-20 mb-3">
                        <i class="fas fa-book text-4xl text-white"></i>
                    </div>
                </div>
                <h1 class="text-white text-2xl font-bold text-center">Admin Portal</h1>
                <p class="text-primary-200 text-center mt-1">Book Management System</p>
            </div>

            <!-- Form section -->
            <div class="p-8">
                <h2 class="text-gray-700 text-xl font-semibold mb-6">Sign in to your account</h2>

                <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded flex items-start">
                        <div class="mr-3 mt-0.5">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div>
                            <p class="font-medium">Authentication Error</p>
                            <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-6" id="loginForm">
                    <!-- Username field -->
                    <div class="relative">
                        <label for="username" class="text-sm font-medium text-gray-700 block mb-2">Username</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 input-icon">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="username" id="username"
                                class="form-input block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-300 focus:border-primary-500 focus:outline-none transition-all"
                                placeholder="Enter your username" required>
                        </div>
                    </div>

                    <!-- Password field -->
                    <div class="relative">
                        <div class="flex justify-between mb-2">
                            <label for="password" class="text-sm font-medium text-gray-700 block">Password</label>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 input-icon">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" id="password"
                                class="form-input block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-300 focus:border-primary-500 focus:outline-none transition-all"
                                placeholder="••••••••" required>
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center">
                        <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                    </div>

                    <!-- Login button -->
                    <button type="submit" id="loginBtn" class="w-full flex items-center justify-center bg-primary-600 text-white font-medium py-2.5 px-4 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-4 focus:ring-primary-300 shadow-md transition-all">
                        <span id="loginText">Sign In</span>
                        <span id="loginLoading" class="hidden">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Signing in...</span>
                        </span>
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="px-8 py-4 bg-gray-50 border-t">
                <p class="text-center text-xs text-gray-500">
                    &copy; <?php echo date("Y"); ?> Book Management System. All Rights Reserved.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    // Toggle icon
                    togglePassword.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
                });
            }

            // Show loading state on form submission
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const loginText = document.getElementById('loginText');
            const loginLoading = document.getElementById('loginLoading');

            if (loginForm && loginBtn && loginText && loginLoading) {
                loginForm.addEventListener('submit', function() {
                    if (this.checkValidity()) {
                        loginText.classList.add('hidden');
                        loginLoading.classList.remove('hidden');
                        loginBtn.disabled = true;
                    }
                });
            }

            // Focus username field on load
            const usernameInput = document.getElementById('username');
            if (usernameInput) {
                usernameInput.focus();
            }
        });
    </script>
</body>

</html>