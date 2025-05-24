<?php
// filepath: c:\xampp\htdocs\LTW-UD2\admin\themnhanvien.php
session_start();
require_once("../database/database.php");
require_once("../database/user.php");
require_once("../database/role.php");

// Initialize
$roleManager = new RoleManager();
$userTable = new UsersTable();

// Get user information
$user = null;
if (isset($_SESSION["user"]) && $_SESSION["user"] != null) {
    $user = $userTable->getUserDetailsById($_SESSION["user"]);
    if ($user == null) {
        unset($_SESSION["user"]);
    }
}

// Get all roles for selection
$roles = $roleManager->getAllRoles();

// Handle search functionality
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$results = [];
$searchPerformed = false;
$totalResults = 0;

// Handle role assignment if form submitted
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign_role') {
    $userId = intval($_POST['user_id']);
    $roleId = intval($_POST['role_id']);
    
    // Check if user exists and does not already have a role
    $userToUpdate = $userTable->getUserDetailsById($userId);
    
    if (!$userToUpdate) {
        $error = 'Không tìm thấy người dùng với ID đã chọn';
    } elseif (!empty($userToUpdate['role_id'])) {
        $error = 'Người dùng này đã có vai trò, vui lòng chỉnh sửa từ trang quản lý nhân viên';
    } else {
        // Update user's role
        $result = $userTable->updateUserRole($userId, $roleId);
        if ($result) {
            $success = true;
            $userDetails = $userTable->getUserDetailsById($userId);
            $roleName = '';
            foreach ($roles as $role) {
                if ($role['role_id'] == $roleId) {
                    $roleName = $role['role_name'];
                    break;
                }
            }
        } else {
            $error = 'Có lỗi xảy ra khi cập nhật vai trò';
        }
    }
}

// Handle user search
if (isset($_GET['search']) || isset($_GET['search_type'])) {
    $searchType = isset($_GET['search_type']) ? $_GET['search_type'] : 'all';
    $searchPerformed = true;
    
    // Pagination
    $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $perPage = 10;
    $offset = ($currentPage - 1) * $perPage;
    
    // Get results based on search type and term
    if ($searchType === 'id' && is_numeric($searchTerm)) {
        // Search by specific ID
        $user = $userTable->getUserDetailsById(intval($searchTerm));
        if ($user) {
            $results = [$user];
            $totalResults = 1;
        }
    } else {
        // Search by username, email, or all fields
        $results = $userTable->searchNonEmployeeUsers($searchTerm, $searchType, $perPage, $offset);
        $totalResults = $userTable->countNonEmployeeUsers($searchTerm, $searchType);
    }
}

// Calculate total pages for pagination
// $totalPages = ceil($totalResults / $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm nhân viên mới</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .stats-card {
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            line-height: 1;
        }
        .badge-blue {
            background-color: #ebf5ff;
            color: #0d507c;
        }
        .badge-green {
            background-color: #d1fae5;
            color: #047857;
        }
        .badge-gray {
            background-color: #f3f4f6;
            color: #374151;
        }
        .badge-amber {
            background-color: #fef3c7;
            color: #b45309;
        }
        .badge-purple {
            background-color: #f5f3ff;
            color: #6d28d9;
        }
        .table-hover tr:hover {
            background-color: #f9fafb;
        }
        /* Modal animation */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .animate-fade {
            animation: fadeIn 0.3s ease forwards;
        }
        .animate-slide {
            animation: slideIn 0.3s ease forwards;
        }
        .search-highlight {
            background-color: #fdf6b2;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
        }
    </style>
</head>

<body class="bg-gray-100">
    <main class="flex flex-col md:flex-row min-h-screen">
        <!-- Mobile sidebar toggle button -->
        <div class="md:hidden p-4 bg-white border-b">
            <button id="mobileSidebarToggle" class="text-gray-500 focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
        
        <!-- Sidebar - hidden on mobile by default -->
        <div id="sidebar" class="hidden md:block md:w-64 bg-white shadow-md">
            <?php include_once './gui/sidebar.php' ?>
        </div>
        
        <div class="flex-1 p-3 sm:p-4 md:p-6 h-screen overflow-auto">
            <div class="mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-user-plus mr-2 text-blue-600"></i>
                            Thêm nhân viên mới
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Tìm kiếm người dùng và gán vai trò để thêm họ vào hệ thống nhân viên
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <a href="nhanvien.php" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Quay lại danh sách nhân viên
                        </a>
                    </div>
                </div>
            </div>
            
            <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 animate-slide">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            <strong>Thành công!</strong> Người dùng đã được thêm vào hệ thống nhân viên với vai trò 
                            <strong><?php echo htmlspecialchars($roleName); ?></strong>.
                        </p>
                        <div class="mt-2">
                            <div class="flex space-x-3">
                                <a href="nhanvien.php" class="text-sm font-medium text-green-700 hover:text-green-600">
                                    <i class="fas fa-users mr-1"></i> Xem danh sách nhân viên
                                </a>
                                <button type="button" onclick="document.querySelector('.bg-green-50').style.display = 'none'" class="text-sm font-medium text-green-700 hover:text-green-600">
                                    <i class="fas fa-times mr-1"></i> Đóng
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 animate-slide">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            <strong>Lỗi!</strong> <?php echo $error; ?>
                        </p>
                    </div>
                    <button type="button" onclick="this.parentElement.parentElement.style.display = 'none'" class="ml-auto text-red-400 hover:text-red-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Search Card -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-search text-blue-600 mr-2"></i>
                    Tìm kiếm người dùng
                </h2>
                
                <form action="" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Từ khóa tìm kiếm</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input 
                                    type="text" 
                                    name="search" 
                                    id="search" 
                                    class="focus:ring-blue-500 focus:border-blue-500 block outline-transparent w-full pl-10 sm:text-sm border-gray-300 rounded-md shadow-sm" 
                                    placeholder="Nhập ID, tên đăng nhập hoặc email của người dùng..."
                                    value="<?php echo htmlspecialchars($searchTerm); ?>"
                                >
                            </div>
                        </div>
                        
                        <div>
                            <label for="search_type" class="block text-sm font-medium text-gray-700 mb-1">Tìm theo</label>
                            <select 
                                id="search_type" 
                                name="search_type" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm"
                            >
                                <option value="all" <?php echo (!isset($_GET['search_type']) || $_GET['search_type'] === 'all') ? 'selected' : ''; ?>>
                                    Tất cả trường
                                </option>
                                <option value="id" <?php echo (isset($_GET['search_type']) && $_GET['search_type'] === 'id') ? 'selected' : ''; ?>>
                                    ID người dùng
                                </option>
                                <option value="username" <?php echo (isset($_GET['search_type']) && $_GET['search_type'] === 'username') ? 'selected' : ''; ?>>
                                    Tên đăng nhập
                                </option>
                                <option value="email" <?php echo (isset($_GET['search_type']) && $_GET['search_type'] === 'email') ? 'selected' : ''; ?>>
                                    Email
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-search mr-2"></i>
                            Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Quick Help Card -->
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Hướng dẫn thêm nhân viên</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Tìm kiếm người dùng bằng ID, tên đăng nhập hoặc email</li>
                                <li>Từ kết quả tìm kiếm, chọn người dùng và gán vai trò</li>
                                <li>Chỉ những người dùng chưa có vai trò (role_id = NULL) mới có thể được thêm làm nhân viên</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Search Results -->
            <?php if ($searchPerformed): ?>
                <div class="bg-white shadow-lg border border-gray-200 rounded-lg overflow-hidden mb-6">
                    <div class="bg-gray-50 p-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-search text-blue-600 mr-2"></i>
                                Kết quả tìm kiếm
                            </h2>
                            <span class="text-gray-500 text-sm">
                                Tìm thấy <?php echo $totalResults; ?> người dùng
                            </span>
                        </div>
                    </div>
                    
                    <?php if (empty($results)): ?>
                        <div class="p-8 text-center">
                            <div class="inline-flex items-center justify-center bg-gray-100 rounded-full p-4 mb-3">
                                <i class="fas fa-search text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Không tìm thấy kết quả</h3>
                            <p class="text-gray-500 max-w-md mx-auto">
                                Không tìm thấy người dùng nào phù hợp với từ khóa tìm kiếm. Vui lòng thử lại với từ khóa khác.
                            </p>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500">Gợi ý:</p>
                                <ul class="text-sm text-gray-500 mt-1 space-y-1">
                                    <li>- Kiểm tra lại chính tả của từ khóa</li>
                                    <li>- Thử tìm kiếm với ID hoặc email chính xác</li>
                                    <li>- Thử sử dụng từ khóa ngắn hơn</li>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 table-hover">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-id-badge mr-1"></i> ID
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-user mr-1"></i> Tên đăng nhập
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-at mr-1"></i> Email
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-cogs mr-1"></i> Thao tác
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($results as $result): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo $result['id']; ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 rounded-full overflow-hidden">
                                                        <?php if (!empty($result['avatar'])): ?>
                                                            <img src="<?php echo htmlspecialchars($result['avatar']); ?>" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                                                        <?php else: ?>
                                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                                <span class="text-sm font-medium text-blue-600">
                                                                    <?php echo strtoupper(substr($result['userName'], 0, 1)); ?>
                                                                </span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?php 
                                                            if (!empty($searchTerm) && strtolower($_GET['search_type']) === 'username') {
                                                                $highlightedName = preg_replace('/(' . preg_quote($searchTerm, '/') . ')/i', '<span class="search-highlight">$1</span>', htmlspecialchars($result['userName']));
                                                                echo $highlightedName;
                                                            } else {
                                                                echo htmlspecialchars($result['userName']);
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php 
                                                    if (!empty($searchTerm) && strtolower($_GET['search_type']) === 'email') {
                                                        $highlightedEmail = preg_replace('/(' . preg_quote($searchTerm, '/') . ')/i', '<span class="search-highlight">$1</span>', htmlspecialchars($result['email']));
                                                        echo $highlightedEmail;
                                                    } else {
                                                        echo htmlspecialchars($result['email']);
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <?php if (empty($result['role_id'])): ?>
                                                    <button 
                                                        onclick="openAssignRoleModal(<?php echo $result['id']; ?>, '<?php echo htmlspecialchars($result['userName']); ?>')" 
                                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none"
                                                    >
                                                        <i class="fas fa-user-tag mr-1"></i> Gán vai trò
                                                    </button>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-md">
                                                        <i class="fas fa-user-check mr-1"></i> Đã là nhân viên
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                      
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- No search performed yet -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6 text-center py-12">
                    <div class="mx-auto w-24 h-24 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                        <i class="fas fa-search text-blue-500 text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Tìm kiếm người dùng</h3>
                    <p class="text-gray-600 max-w-lg mx-auto">
                        Sử dụng form tìm kiếm phía trên để tìm người dùng. 
                        Bạn có thể tìm theo ID, tên đăng nhập, hoặc email.
                    </p>
                </div>
            <?php endif; ?>
            
        </div>
    </main>

    <!-- Assign Role Modal -->
    <div id="overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden animate-fade"></div>
    
    <div id="assignRoleModal" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-2xl z-50 w-full max-w-md hidden animate-slide">
        <div class="p-5 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Gán vai trò cho người dùng</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <form id="assignRoleForm" method="POST" action="" class="p-5">
            <input type="hidden" name="action" value="assign_role">
            <input type="hidden" name="user_id" id="userId" value="">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">
                    Người dùng
                </label>
                <div class="flex items-center border border-gray-300 rounded-md px-3 py-2 bg-gray-50">
                    <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                        <span id="userInitial" class="text-blue-600 font-medium"></span>
                    </div>
                    <div id="userName" class="text-gray-800 font-medium">
                        <!-- Filled by JavaScript -->
                    </div>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2" for="role_id">
                    Chọn vai trò <span class="text-red-500">*</span>
                </label>
                <select 
                    name="role_id" 
                    id="role_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                >
                    <option value="">-- Chọn vai trò --</option>
                    <?php foreach($roles as $role): ?>
                        <option value="<?php echo $role['role_id']; ?>">
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-sm text-gray-500">
                    Vai trò xác định quyền hạn của người dùng trong hệ thống
                </p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button 
                    type="button" 
                    onclick="closeModal()" 
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Hủy
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <i class="fas fa-save mr-1"></i>
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>

    <script>
        // Mobile sidebar toggle
        document.getElementById('mobileSidebarToggle')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('hidden');
        });

        // Handle responsiveness on window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth >= 768) { // md breakpoint
                sidebar.classList.remove('hidden');
            } else {
                sidebar.classList.add('hidden');
            }
        });
        
        // Assign role modal functions
        function openAssignRoleModal(userId, userName) {
            document.getElementById('userId').value = userId;
            document.getElementById('userName').textContent = userName;
            document.getElementById('userInitial').textContent = userName.charAt(0).toUpperCase();
            
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('assignRoleModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('assignRoleModal').style.display = 'none';
        }
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Close modal when clicking outside
        document.getElementById('overlay')?.addEventListener('click', function() {
            closeModal();
        });

        // Focus search input on page load
        window.addEventListener('load', function() {
            const searchInput = document.getElementById('search');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>