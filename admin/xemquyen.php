<?php
session_start();
require_once("../database/database.php");
require_once("../database/user.php");
require_once("../database/role.php"); // Include the role manager

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

// Get data
$selectedRoleId = isset($_GET['role_id']) ? intval($_GET['role_id']) : null;
$roles = $roleManager->getAllRoles();
$functions = $roleManager->getAllFunctions();
$manageOperations = $roleManager->getAllManageOperations();
$userCounts = $roleManager->getUserCountByRole();

// If no role is selected, default to the first one
if ($selectedRoleId === null && !empty($roles)) {
    $selectedRoleId = $roles[0]['role_id'];
}

// Get the selected role's details
$selectedRole = $selectedRoleId ? $roleManager->getRoleById($selectedRoleId) : null;

// Get users with this role if a role is selected
$usersWithRole = [];
$totalUsers = 0;
if ($selectedRole) {
    $usersWithRole = $roleManager->getUsersByRole($selectedRoleId, 10, 0);
    $totalUsers = $roleManager->getUserCountForRole($selectedRoleId);
}

// Handle search functionality
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phân quyền</title>
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

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-info {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .role-card.active {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }

        .permission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }

        @media (max-width: 640px) {
            .permission-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }

        .table-hover tr:hover {
            background-color: #f9fafb;
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
                            <i class="fas fa-user-shield mr-2 text-blue-600"></i>
                            Quản lý phân quyền hệ thống
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Xem và quản lý thông tin về vai trò và quyền hạn trong hệ thống
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <?php if ($roleTableSidebar->isAuthorized($adminID, 10, 2)) { ?>

                            <a href="themquyen.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-plus mr-2"></i>
                                Thêm mới vai trò
                            </a>
                        <?php } ?>

                    </div>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="stats-card bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Tổng vai trò</p>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo count($roles); ?></p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 text-blue-500">
                            <i class="fas fa-id-badge text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stats-card bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Tổng chức năng</p>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo count($functions); ?></p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-500">
                            <i class="fas fa-cogs text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stats-card bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Tổng người dùng có vai trò</p>
                            <?php
                            $totalRoleUsers = 0;
                            foreach ($userCounts as $count) {
                                $totalRoleUsers += $count['user_count'];
                            }
                            ?>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo $totalRoleUsers; ?></p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-purple-100 text-purple-500">
                            <i class="fas fa-users-cog text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-lg border border-gray-200 rounded-lg overflow-hidden mb-6">
                <div class="bg-gray-50 p-4 border-b">
                    <h2 class="text-lg font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-id-badge mr-2 text-blue-600"></i>
                        Danh sách vai trò
                    </h2>
                </div>

                <div class="p-4">
                    <!-- Search and filters -->
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
                        <div class="relative mb-4 sm:mb-0">
                            <form method="GET" action="" class="flex">
                                <input type="hidden" name="role_id" value="<?php echo $selectedRoleId; ?>">
                                <div class="relative">
                                    <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>"
                                        placeholder="Tìm kiếm vai trò..."
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:w-64 pl-10 pr-3 py-2 border border-gray-300 rounded-md">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                </div>
                                <button type="submit" class="ml-2 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md">
                                    Tìm
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Role cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                        <?php foreach ($roles as $role):
                            // Find the user count for this role
                            $userCount = 0;
                            foreach ($userCounts as $count) {
                                if ($count['role_id'] == $role['role_id']) {
                                    $userCount = $count['user_count'];
                                    break;
                                }
                            }

                            // Add active class if this is the selected role
                            $isActive = $selectedRoleId == $role['role_id'];

                            // Skip if searching and no match
                            if ($searchTerm && stripos($role['role_name'], $searchTerm) === false) {
                                continue;
                            }
                        ?>
                            <a href="?role_id=<?php echo $role['role_id']; ?>"
                                class="role-card stats-card border rounded-lg p-4 flex flex-col
                                  <?php echo $isActive ? 'active' : ''; ?>">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <?php if ($role['role_id'] == 1): ?>
                                                <i class="fas fa-crown text-amber-500"></i>
                                            <?php else: ?>
                                                <span class="text-blue-600 font-bold"><?php echo $role['role_id']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-lg font-semibold text-gray-800"><?php echo $role['role_name']; ?></h3>
                                        </div>
                                    </div>
                                    <div class="text-<?php echo $isActive ? 'blue' : 'gray'; ?>-500">
                                        <i class="fas fa-<?php echo $isActive ? 'check-circle' : 'circle'; ?> text-xl"></i>
                                    </div>
                                </div>

                                <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between">
                                    <span class="inline-flex items-center text-sm text-gray-500">
                                        <i class="fas fa-users mr-1"></i>
                                        <?php echo $userCount; ?> người dùng
                                    </span>

                                    <span class="badge badge-info">
                                        <?php
                                        if ($role['role_id'] == 1) echo "Quản trị viên";
                                        elseif ($role['role_id'] == 2) echo "Nhân viên";
                                        elseif ($role['role_id'] == 3) echo "Quản lý kho";
                                        else echo "Vai trò #" . $role['role_id'];
                                        ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if ($selectedRole): ?>
                <!-- Selected Role Details -->
                <div class="bg-white shadow-lg border border-gray-200 rounded-lg overflow-hidden mb-6">
                    <div class="bg-gray-50 p-4 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-id-card mr-2 text-blue-600"></i>
                                <?php echo $selectedRole['role_name']; ?>
                            </h2>
                            <p class="text-sm text-gray-600">Chi tiết quyền hạn và chức năng</p>
                        </div>
                        <div class="mt-2 sm:mt-0 flex">
                            <?php if ($roleTableSidebar->isAuthorized($adminID, 10, 3)) { ?>

                                <a href="suaquyen.php?role_id=<?php echo $selectedRole['role_id']; ?>" class="mr-2 inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200">
                                    <i class="fas fa-edit mr-1"></i> Sửa
                                </a>
                            <?php } ?>
                            <?php if ($selectedRole['role_id'] > 3): // Prevent deletion of core roles 
                            ?>
                                <button onclick="confirmDelete(<?php echo $selectedRole['role_id']; ?>)" class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200">
                                    <i class="fas fa-trash-alt mr-1"></i> Xóa
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Permission Matrix -->
                    <div class="p-4">
                        <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Chức năng
                                        </th>
                                        <?php foreach ($manageOperations as $operation): ?>
                                            <th scope="col" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <?php echo $operation['manage_name']; ?>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 table-hover">
                                    <?php foreach ($functions as $function): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
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
                                                                                case 11:
                                                                                    echo 'plug';
                                                                                    break;
                                                                                case 12:
                                                                                    echo 'truck-ramp-box';
                                                                                    break;
                                                                                case 13:
                                                                                    echo 'industry';
                                                                                    break;
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
                                                $hasPermission = $roleManager->hasPermission(
                                                    $selectedRole['role_id'],
                                                    $function['id'],
                                                    $operation['id']
                                                );

                                                // Determine the operation type for styling
                                                $opClass = '';
                                                $opIcon = '';
                                                switch ($operation['id']) {
                                                    case 1: // Xem
                                                        $opClass = 'text-blue-600';
                                                        $opIcon = 'fa-eye';
                                                        break;
                                                    case 2: // Thêm
                                                        $opClass = 'text-green-600';
                                                        $opIcon = 'fa-plus';
                                                        break;
                                                    case 3: // Sửa
                                                        $opClass = 'text-amber-600';
                                                        $opIcon = 'fa-pencil-alt';
                                                        break;
                                                    case 4: // Xóa
                                                        $opClass = 'text-red-600';
                                                        $opIcon = 'fa-trash-alt';
                                                        break;
                                                    default:
                                                        $opClass = 'text-gray-600';
                                                        $opIcon = 'fa-check';
                                                }
                                            ?>
                                                <td class="px-2 py-4 whitespace-nowrap text-center">
                                                    <?php if ($hasPermission): ?>
                                                        <span class="<?php echo $opClass; ?>">
                                                            <i class="fas <?php echo $opIcon; ?>"></i>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-gray-300">
                                                            <i class="fas fa-times"></i>
                                                        </span>
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

                <!-- Users with this role -->
                <div class="bg-white shadow-lg border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-users mr-2 text-blue-600"></i>
                            Người dùng với vai trò <?php echo $selectedRole['role_name']; ?>
                            <span class="ml-2 text-sm bg-blue-100 text-blue-800 py-1 px-2 rounded-full">
                                <?php echo $totalUsers; ?>
                            </span>
                        </h3>


                    </div>

                    <?php if (count($usersWithRole) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 table-hover">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            #ID
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên đăng nhập
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Trạng thái
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($usersWithRole as $userItem): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $userItem['id']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8 rounded-full overflow-hidden">
                                                        <?php if (!empty($userItem['avatar'])): ?>
                                                            <img class="h-8 w-8 rounded-full" src="<?php echo $userItem['avatar']; ?>" alt="Avatar">
                                                        <?php else: ?>
                                                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                                <span class="text-sm font-medium text-blue-600">
                                                                    <?php echo strtoupper(substr($userItem['username'], 0, 1)); ?>
                                                                </span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?php echo $userItem['username']; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?php echo $userItem['email']; ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if (isset($userItem['status_user']) && $userItem['status_user'] == 1): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle mr-1"></i> Hoạt động
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-ban mr-1"></i> Đã khóa
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($totalUsers > 10): ?>
                            <div class="px-6 py-3 bg-gray-50 text-center text-sm text-gray-500">
                                Hiển thị 10 trên tổng số <?php echo $totalUsers; ?> người dùng
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-user-slash text-4xl mb-3"></i>
                            <p>Không có người dùng nào với vai trò này.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Không tìm thấy thông tin vai trò. Vui lòng chọn một vai trò từ danh sách.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

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

        // Confirmation dialog for role deletion
        function confirmDelete(roleId) {
            if (confirm('Bạn có chắc chắn muốn xóa vai trò này? Hành động này không thể hoàn tác.')) {
                window.location.href = 'xoaquyen.php?role_id=' + roleId;
            }
        }
    </script>
</body>

</html>