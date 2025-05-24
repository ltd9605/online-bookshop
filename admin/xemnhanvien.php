<?php
// filepath: c:\xampp\htdocs\LTW-UD2\admin\xemnhanvien.php
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

// Get all roles for reference
$roles = $roleManager->getAllRoles();

// Validate employee ID parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: nhanvien.php");
    exit;
}

$employeeId = intval($_GET['id']);
$employee = $userTable->getUserDetailsById($employeeId);

// Redirect if employee not found or is not an employee (doesn't have a role)
if (!$employee || empty($employee['role_id'])) {
    header("Location: nhanvien.php");
    exit;
}

// Get employee's role details
$roleName = "Unknown Role";
$roleColor = "gray";
$roleBadgeClass = "badge-gray";
$roleId = $employee['role_id'];

foreach ($roles as $role) {
    if ($role['role_id'] == $roleId) {
        $roleName = $role['role_name'];

        // Assign badge class based on role
        switch ($roleId) {
            case 1:
                $roleBadgeClass = 'badge-blue';
                $roleColor = "blue";
                break;
            case 2:
                $roleBadgeClass = 'badge-green';
                $roleColor = "green";
                break;
            case 3:
                $roleBadgeClass = 'badge-amber';
                $roleColor = "amber";
                break;
            default:
                $roleBadgeClass = 'badge-purple';
                $roleColor = "purple";
        }
        break;
    }
}

// Get employee's permissions through their role
$functions = $roleManager->getAllFunctions();
$manageOperations = $roleManager->getAllManageOperations();
$rolePermissions = $roleManager->getRolePermissions($roleId);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin nhân viên - <?php echo htmlspecialchars($employee['userName']); ?></title>
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

        .permission-check {
            color: #10b981;
        }

        .permission-x {
            color: #ef4444;
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
                            <i class="fas fa-user-circle mr-2 text-<?php echo $roleColor; ?>-600"></i>
                            Thông tin nhân viên
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Xem thông tin chi tiết về nhân viên
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <a href="nhanvien.php" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Quay lại danh sách
                        </a>
                    </div>
                </div>
            </div>

            <!-- Employee Profile Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6 border border-gray-200">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                    <div class="flex flex-wrap items-center">
                        <!-- Avatar Section -->
                        <div class="w-full md:w-auto flex justify-center md:justify-start mb-4 md:mb-0">
                            <div class="relative">
                                <?php if (!empty($employee['avatar'])): ?>
                                    <div class="h-24 w-24 rounded-full overflow-hidden border-4 border-white shadow-lg">
                                        <img src="<?php echo htmlspecialchars($employee['avatar']); ?>" alt="Avatar" class="h-full w-full object-cover">
                                    </div>
                                <?php else: ?>
                                    <div class="h-24 w-24 rounded-full bg-<?php echo $roleColor; ?>-100 flex items-center justify-center border-4 border-white shadow-lg">
                                        <span class="text-2xl font-bold text-<?php echo $roleColor; ?>-600">
                                            <?php echo strtoupper(substr($employee['userName'], 0, 1)); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($employee['status_user']) && $employee['status_user'] == 1): ?>
                                    <div class="absolute bottom-0 right-0 h-6 w-6 rounded-full bg-green-500 border-2 border-white"></div>
                                <?php else: ?>
                                    <div class="absolute bottom-0 right-0 h-6 w-6 rounded-full bg-red-500 border-2 border-white"></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Basic Info Section -->
                        <div class="w-full md:w-auto md:flex-1 ml-0 md:ml-6 text-center md:text-left">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($employee['userName']); ?></h2>
                                    <p class="text-gray-600 mt-1">
                                        <i class="fas fa-envelope mr-1"></i>
                                        <?php echo htmlspecialchars($employee['email']); ?>
                                    </p>
                                </div>
                                <div class="mt-3 md:mt-0 flex flex-col items-center md:items-end">
                                    <span class="badge <?php echo $roleBadgeClass; ?> text-sm mb-2">
                                        <i class="fas fa-user-tag mr-1"></i>
                                        <?php echo htmlspecialchars($roleName); ?>
                                    </span>
                                    <?php if (isset($employee['status_user']) && $employee['status_user'] == 1): ?>
                                        <span class="badge badge-green">
                                            <i class="fas fa-check-circle mr-1"></i> Hoạt động
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-red-100 text-red-800">
                                            <i class="fas fa-ban mr-1"></i> Đã khóa
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Details -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Column 1 -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                                <i class="fas fa-id-card mr-2 text-<?php echo $roleColor; ?>-600"></i>
                                Thông tin cá nhân
                            </h3>

                            <div class="space-y-3">
                                <div class="flex border-b border-gray-200 pb-2">
                                    <div class="w-1/3 font-medium text-gray-500">ID</div>
                                    <div class="w-2/3 text-gray-800"><?php echo $employee['id']; ?></div>
                                </div>

                                <div class="flex border-b border-gray-200 pb-2">
                                    <div class="w-1/3 font-medium text-gray-500">Tên đăng nhập</div>
                                    <div class="w-2/3 text-gray-800"><?php echo htmlspecialchars($employee['userName']); ?></div>
                                </div>

                                <div class="flex border-b border-gray-200 pb-2">
                                    <div class="w-1/3 font-medium text-gray-500">Email</div>
                                    <div class="w-2/3 text-gray-800"><?php echo htmlspecialchars($employee['email']); ?></div>
                                </div>

                                <div class="flex border-b border-gray-200 pb-2">
                                    <div class="w-1/3 font-medium text-gray-500">Họ và tên</div>
                                    <div class="w-2/3 text-gray-800"><?php echo !empty($employee['fullName']) ? htmlspecialchars($employee['fullName']) : '<span class="text-gray-400">Chưa cập nhật</span>'; ?></div>
                                </div>


                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                                <i class="fas fa-user-shield mr-2 text-<?php echo $roleColor; ?>-600"></i>
                                Thông tin tài khoản
                            </h3>

                            <div class="space-y-3">
                                <div class="flex border-b border-gray-200 pb-2">
                                    <div class="w-1/3 font-medium text-gray-500">Vai trò</div>
                                    <div class="w-2/3">
                                        <span class="badge <?php echo $roleBadgeClass; ?>">
                                            <?php echo htmlspecialchars($roleName); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="flex border-b border-gray-200 pb-2">
                                    <div class="w-1/3 font-medium text-gray-500">Trạng thái</div>
                                    <div class="w-2/3">
                                        <?php if (isset($employee['status_user']) && $employee['status_user'] == 1): ?>
                                            <span class="badge badge-green">
                                                <i class="fas fa-check-circle mr-1"></i> Hoạt động
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-red-100 text-red-800">
                                                <i class="fas fa-ban mr-1"></i> Đã khóa
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="flex border-b border-gray-200 pb-2">
                                    <div class="w-1/3 font-medium text-gray-500">Ngày tham gia</div>
                                    <div class="w-2/3 text-gray-800">
                                        <?php
                                        if (!empty($employee['created_at'])) {
                                            echo date('d/m/Y H:i', strtotime($employee['created_at']));
                                        } else {
                                            echo '<span class="text-gray-400">Không có thông tin</span>';
                                        }
                                        ?>
                                    </div>
                                </div>



                                <div class="flex border-b border-gray-200 pb-2">
                                    <div class="w-1/3 font-medium text-gray-500">Số điện thoại</div>
                                    <div class="w-2/3 text-gray-800"><?php echo !empty($employee['phone']) ? htmlspecialchars($employee['phone']) : '<span class="text-gray-400">Chưa cập nhật</span>'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
            <?php if ($roleTableSidebar->isAuthorized($adminID, 8, 3)) { ?>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-end space-x-3">
                        <button
                            onclick="openChangeRoleModal(<?php echo $employee['id']; ?>, '<?php echo htmlspecialchars($employee['userName']); ?>', <?php echo $employee['role_id']; ?>)"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-user-tag mr-2"></i>
                            Đổi vai trò
                        </button>
                    </div>
                </div>
            <?php } ?>
            </div>

            <!-- Permissions Section -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6 border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-key mr-2 text-<?php echo $roleColor; ?>-600"></i>
                        Quyền hạn theo vai trò
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Danh sách các quyền mà nhân viên có theo vai trò <?php echo htmlspecialchars($roleName); ?>
                    </p>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Chức năng
                                    </th>
                                    <?php foreach ($manageOperations as $operation): ?>
                                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <?php echo $operation['manage_name']; ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($functions as $function): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-<?php echo $roleColor; ?>-100 text-<?php echo $roleColor; ?>-600 flex items-center justify-center">
                                                    <i class="fas fa-<?php
                                                                        // Choose an icon based on function name
                                                                        switch ($function['id']) {
                                                                            case 5:
                                                                                echo 'users';
                                                                                break; // Quản lý khách hàng
                                                                            case 6:
                                                                                echo 'chart-line';
                                                                                break; // Thống kê
                                                                            case 7:
                                                                                echo 'box';
                                                                                break; // Quản lý sản phẩm
                                                                            case 8:
                                                                                echo 'user-tie';
                                                                                break; // Quản lý nhân viên
                                                                            case 9:
                                                                                echo 'star';
                                                                                break; // Đánh giá
                                                                            case 10:
                                                                                echo 'shopping-cart';
                                                                                break; // Quản lý đơn hàng
                                                                            default:
                                                                                echo 'cog';
                                                                        }
                                                                        ?>"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo $function['name']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <?php foreach ($manageOperations as $operation):
                                            $hasPermission = false;
                                            foreach ($rolePermissions as $permission) {
                                                if ($permission['chucnang_id'] == $function['id'] && $permission['manage_id'] == $operation['id']) {
                                                    $hasPermission = true;
                                                    break;
                                                }
                                            }
                                        ?>
                                            <td class="px-3 py-4 whitespace-nowrap text-center">
                                                <?php if ($hasPermission): ?>
                                                    <i class="fas fa-check permission-check"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times permission-x"></i>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Change Role Modal -->
    <div id="overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden"></div>

    <div id="changeRoleModal" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-2xl z-50 w-full max-w-md hidden">
        <div class="p-5 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Đổi vai trò nhân viên</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <form id="changeRoleForm" method="POST" action="nhanvien.php" class="p-5">
            <input type="hidden" name="action" value="change_role">
            <input type="hidden" name="user_id" id="user_id" value="">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">
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