<?php
// filepath: c:\xampp\htdocs\LTW-UD2\admin\nhanvien.php
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
$selectedRole = isset($_GET['role_id']) ? intval($_GET['role_id']) : 0;

// Handle role change if form submitted
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_role') {
    $userId = intval($_POST['user_id']);
    $roleId = !empty($_POST['role_id']) ? intval($_POST['role_id']) : null;

    // Update user's role
    $result = $userTable->updateUserRole($userId, $roleId);
    if ($result) {
        $success = true;
    } else {
        $error = 'Có lỗi xảy ra khi cập nhật vai trò';
    }
}

// Get all employees (users with role_id)
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$offset = ($currentPage - 1) * $perPage;

// Get employees based on filters
$employees = $userTable->getEmployees($searchTerm, $selectedRole, $perPage, $offset);
$totalEmployees = $userTable->countEmployees($searchTerm, $selectedRole);
$totalPages = ceil($totalEmployees / $perPage);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhân viên</title>
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

        .badge-purple {
            background-color: #f5f3ff;
            color: #6d28d9;
        }

        .badge-amber {
            background-color: #fef3c7;
            color: #b45309;
        }

        .badge-gray {
            background-color: #f3f4f6;
            color: #374151;
        }

        .table-hover tr:hover {
            background-color: #f9fafb;
        }

        /* Modal animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .animate-fade {
            animation: fadeIn 0.3s ease forwards;
        }

        .animate-slide {
            animation: slideIn 0.3s ease forwards;
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
                            <i class="fas fa-user-tie mr-2 text-blue-600"></i>
                            Quản lý nhân viên
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Xem và quản lý thông tin nhân viên hệ thống
                        </p>
                    </div>
                    <?php if ($roleTableSidebar->isAuthorized($adminID, 8, 2)) {
                    ?>
                        <div class="mt-4 md:mt-0">
                            <a href="themnhanvien.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-user-plus mr-2"></i>
                                Thêm nhân viên mới
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                Cập nhật vai trò nhân viên thành công!
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <?php echo $error; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="stats-card bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Tổng nhân viên</p>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo $totalEmployees; ?></p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 text-blue-500">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stats-card bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Số vai trò quản lý</p>
                            <?php
                            // Count admin roles (you might need to adjust this logic)
                            $adminCount = 0;
                            foreach ($employees as $emp) {
                                if ($emp['role_id'] == 1) {
                                    $adminCount++;
                                }
                            }
                            ?>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo $adminCount; ?></p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-500">
                            <i class="fas fa-user-shield text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stats-card bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Vai trò hệ thống</p>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo count($roles); ?></p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-purple-100 text-purple-500">
                            <i class="fas fa-user-tag text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter and Search Section -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <form action="" method="GET" class="flex flex-col md:flex-row md:items-end space-y-3 md:space-y-0 md:space-x-3">
                    <div class="flex-1">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input
                                type="text"
                                name="search"
                                id="search"
                                class="focus:ring-blue-500 outline-transparent focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                                placeholder="Tìm theo tên, email..."
                                value="<?php echo htmlspecialchars($searchTerm); ?>">
                        </div>
                    </div>

                    <div>
                        <label for="role_filter" class="block text-sm font-medium text-gray-700 mb-1">Vai trò</label>
                        <select
                            id="role_filter"
                            name="role_id"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="0">Tất cả vai trò</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['role_id']; ?>" <?php echo ($selectedRole == $role['role_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-filter mr-2"></i>
                            Lọc
                        </button>
                    </div>
                </form>
            </div>

            <!-- Employee Table -->
            <div class="bg-white shadow-lg border border-gray-200 rounded-lg overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 table-hover">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-id-badge mr-1"></i> ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-user mr-1"></i> Người dùng
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-at mr-1"></i> Email
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-user-tag mr-1"></i> Vai trò
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-toggle-on mr-1"></i> Trạng thái
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-cogs mr-1"></i> Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($employees)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-user-slash text-4xl text-gray-300 mb-3"></i>
                                            <p class="text-gray-500">Không tìm thấy nhân viên nào</p>
                                            <?php if (!empty($searchTerm) || $selectedRole > 0): ?>
                                                <a href="nhanvien.php" class="mt-2 text-blue-500 hover:underline">
                                                    <i class="fas fa-undo mr-1"></i> Xóa bộ lọc
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $employee['id']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full overflow-hidden">
                                                    <?php if (!empty($employee['avatar'])): ?>
                                                        <img src="<?php echo htmlspecialchars($employee['avatar']); ?>" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                                                    <?php else: ?>
                                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-blue-600">
                                                                <?php echo strtoupper(substr($employee['userName'], 0, 1)); ?>
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($employee['userName']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo htmlspecialchars($employee['email']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $roleBadgeClass = 'badge-gray';
                                            $roleName = 'Chưa phân vai trò';

                                            if (!empty($employee['role_id'])) {
                                                foreach ($roles as $role) {
                                                    if ($role['role_id'] == $employee['role_id']) {
                                                        $roleName = $role['role_name'];

                                                        // Assign badge class based on role
                                                        switch ($employee['role_id']) {
                                                            case 1:
                                                                $roleBadgeClass = 'badge-blue';
                                                                break;
                                                            case 2:
                                                                $roleBadgeClass = 'badge-green';
                                                                break;
                                                            case 3:
                                                                $roleBadgeClass = 'badge-amber';
                                                                break;
                                                            default:
                                                                $roleBadgeClass = 'badge-purple';
                                                        }
                                                        break;
                                                    }
                                                }
                                            }
                                            ?>
                                            <span class="badge <?php echo $roleBadgeClass; ?>">
                                                <?php echo htmlspecialchars($roleName); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (isset($employee['status_user']) && $employee['status_user'] == 1): ?>
                                                <span class="badge badge-green">
                                                    <i class="fas fa-check-circle mr-1"></i> Hoạt động
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-red-100 text-red-800">
                                                    <i class="fas fa-ban mr-1"></i> Đã khóa
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <?php if ($roleTableSidebar->isAuthorized($adminID, 8, 3)) {
                                            ?>
                                                <button onclick="openChangeRoleModal(<?php echo $employee['id']; ?>, '<?php echo htmlspecialchars($employee['userName']); ?>', <?php echo $employee['role_id'] ?? 'null'; ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                                    <i class="fas fa-user-tag"></i> Đổi vai trò
                                                </button>
                                            <?php } ?>
                                            <a href="xemnhanvien.php?id=<?php echo $employee['id']; ?>" class="text-gray-600 hover:text-gray-900">
                                                <i class="fas fa-eye"></i> Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Hiển thị <span class="font-medium"><?php echo count($employees); ?></span> trong số
                                    <span class="font-medium"><?php echo $totalEmployees; ?></span> nhân viên
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <!-- Previous Page -->
                                    <?php if ($currentPage > 1): ?>
                                        <a href="?page=<?php echo ($currentPage - 1); ?>&search=<?php echo urlencode($searchTerm); ?>&role_id=<?php echo $selectedRole; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Previous</span>
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                            <span class="sr-only">Previous</span>
                                            <i class="fas fa-chevron-left"></i>
                                        </span>
                                    <?php endif; ?>

                                    <!-- Page Numbers -->
                                    <?php
                                    $startPage = max(1, $currentPage - 2);
                                    $endPage = min($totalPages, $startPage + 4);

                                    if ($startPage > 1) {
                                        echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                    }

                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($searchTerm); ?>&role_id=<?php echo $selectedRole; ?>"
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 <?php echo ($i == $currentPage) ? 'bg-blue-50 text-blue-600' : 'bg-white text-gray-700'; ?> text-sm font-medium hover:bg-gray-50">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php
                                    endfor;

                                    if ($endPage < $totalPages) {
                                        echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                    }
                                    ?>

                                    <!-- Next Page -->
                                    <?php if ($currentPage < $totalPages): ?>
                                        <a href="?page=<?php echo ($currentPage + 1); ?>&search=<?php echo urlencode($searchTerm); ?>&role_id=<?php echo $selectedRole; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Next</span>
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                            <span class="sr-only">Next</span>
                                            <i class="fas fa-chevron-right"></i>
                                        </span>
                                    <?php endif; ?>
                                </nav>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Change Role Modal -->
    <div id="overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden animate-fade"></div>

    <div id="changeRoleModal" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-2xl z-50 w-full max-w-md hidden animate-slide">
        <div class="p-5 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800" id="modalTitle">Đổi vai trò nhân viên</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <form id="changeRoleForm" method="POST" action="" class="p-5">
            <input type="hidden" name="action" value="change_role">
            <input type="hidden" name="user_id" id="user_id" value="">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2" for="employee_name">
                    Nhân viên
                </label>
                <div class="border border-gray-300 rounded-md px-3 py-2 bg-gray-50 text-gray-700" id="employee_name">
                    <!-- Filled by JavaScript -->
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2" for="role_id">
                    Vai trò
                </label>
                <select
                    name="role_id"
                    id="role_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required>
                    <option value="">-- Chọn vai trò --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['role_id']; ?>">
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-sm text-gray-500">
                    Chọn vai trò để gán cho nhân viên này
                </p>
            </div>

            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    onclick="closeModal()"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Hủy
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-save mr-1"></i>
                    Cập nhật vai trò
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

        // Change role modal functions
        function openChangeRoleModal(userId, userName, roleId) {
            document.getElementById("user_id").value = userId;
            document.getElementById("employee_name").textContent = userName;

            const roleSelect = document.getElementById("role_id");
            if (roleId) {
                roleSelect.value = roleId;
            } else {
                roleSelect.value = "";
            }

            document.getElementById("overlay").style.display = "block";
            document.getElementById("changeRoleModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("overlay").style.display = "none";
            document.getElementById("changeRoleModal").style.display = "none";
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Close modal when clicking outside
        document.getElementById('overlay').addEventListener('click', function() {
            closeModal();
        });
    </script>
</body>

</html>