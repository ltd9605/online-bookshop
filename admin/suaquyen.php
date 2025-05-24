<?php
// filepath: c:\xampp\htdocs\LTW-UD2\admin\suaquyen.php
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

// Validate role_id parameter
if (!isset($_GET['role_id']) || !is_numeric($_GET['role_id'])) {
    header("Location: xemquyen.php");
    exit;
}

$roleId = intval($_GET['role_id']);
$role = $roleManager->getRoleById($roleId);

// Redirect if role not found
if (!$role) {
    header("Location: xemquyen.php");
    exit;
}

// Fetch all functions and operations for the permission matrix
$functions = $roleManager->getAllFunctions();
$manageOperations = $roleManager->getAllManageOperations();

// Initialize variables
$roleName = $role['role_name'];
$success = false;
$error = '';

// Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleName = trim($_POST['role_name'] ?? '');

    // Validate role name
    if (empty($roleName)) {
        $error = 'Tên vai trò không được để trống';
    } else {
        // Update role name if changed
        if ($roleName !== $role['role_name']) {
            $roleManager->updateRoleName($roleId, $roleName);
        }

        // Process permissions
        $permissions = [];
        foreach ($functions as $function) {
            foreach ($manageOperations as $operation) {
                $checkboxName = "permission_{$function['id']}_{$operation['id']}";
                if (isset($_POST[$checkboxName])) {
                    $permissions[] = [$function['id'], $operation['id']];
                }
            }
        }

        // Set permissions for the role (this will overwrite existing permissions)
        if (!empty($permissions)) {
            $result = $roleManager->setRolePermissions($roleId, $permissions);
            if ($result) {
                $success = true;
            } else {
                $error = 'Có lỗi xảy ra khi cập nhật quyền';
            }
        } else {
            $error = 'Vui lòng chọn ít nhất một quyền cho vai trò này';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa vai trò - <?php echo htmlspecialchars($roleName); ?></title>
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

        .checkbox-wrapper {
            position: relative;
            display: inline-block;
            width: 20px;
            height: 20px;
        }

        .custom-checkbox {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            border-radius: 5px;
            border: 2px solid #e5e7eb;
            background-color: white;
            transition: all 0.2s ease;
        }

        .custom-checkbox:checked~.checkmark {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .custom-checkbox:checked~.checkmark:after {
            display: block;
        }

        .checkbox-wrapper .checkmark:after {
            left: 6px;
            top: 2px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .table-hover tr:hover {
            background-color: #f9fafb;
        }

        .permission-status {
            position: relative;
        }

        .permission-status:before {
            content: "";
            position: absolute;
            height: 100%;
            width: 3px;
            left: 0;
            top: 0;
        }

        .permission-active:before {
            background-color: #10b981;
        }

        .permission-inactive:before {
            background-color: #d1d5db;
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
                            <i class="fas fa-edit mr-2 text-blue-600"></i>
                            Chỉnh sửa vai trò: <?php echo htmlspecialchars($roleName); ?>
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Điều chỉnh thông tin và quyền hạn cho vai trò này
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <a href="xemquyen.php?role_id=<?php echo $roleId; ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Quay lại
                        </a>
                    </div>
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
                                Đã cập nhật vai trò thành công!
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

            <!-- Role Summary Card -->
            <div class="bg-white shadow-md rounded-lg p-4 mb-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                            <?php if ($roleId == 1): ?>
                                <i class="fas fa-crown text-amber-500 text-xl"></i>
                            <?php else: ?>
                                <i class="fas fa-user-shield text-blue-600 text-xl"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-semibold text-gray-800">
                            <?php echo htmlspecialchars($roleName); ?>
                        </h2>
                        <div class="mt-1 flex items-center">
                            <span class="text-sm text-gray-500 flex items-center">
                                <i class="fas fa-id-card-alt mr-1"></i>
                                ID: <?php echo $roleId; ?>
                            </span>
                            <span class="mx-2 text-gray-300">|</span>
                            <span class="text-sm text-gray-500 flex items-center">
                                <i class="fas fa-users mr-1"></i>
                                <?php
                                $userCount = $roleManager->getUserCountForRole($roleId);
                                echo $userCount . ' người dùng';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <?php if ($roleId <= 3): ?>
                    <div class="mt-4 p-3 bg-amber-50 rounded-md border border-amber-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-0.5">
                                <i class="fas fa-exclamation-triangle text-amber-500"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-amber-800">Lưu ý về vai trò hệ thống</h3>
                                <div class="mt-1 text-sm text-amber-700">
                                    <p>Đây là vai trò hệ thống quan trọng. Việc thay đổi quyền hạn có thể ảnh hưởng đến hoạt động của hệ thống.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <form method="POST" action="">
                <div class="bg-white shadow-lg border border-gray-200 rounded-lg overflow-hidden mb-6">
                    <div class="bg-gray-50 p-4 border-b">
                        <h2 class="text-lg font-semibold text-gray-700">
                            <i class="fas fa-id-card mr-2 text-blue-600"></i>
                            Thông tin vai trò
                        </h2>
                    </div>

                    <div class="p-6">
                        <div class="mb-6">
                            <label for="role_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Tên vai trò <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="role_name" name="role_name"
                                value="<?php echo htmlspecialchars($roleName); ?>"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập tên vai trò..."
                                required>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-key mr-2 text-blue-600"></i>
                                Phân quyền
                                <span class="ml-2 text-xs bg-blue-100 text-blue-800 py-1 px-2 rounded-full">
                                    Hiện tại: <?php
                                                $permissionCount = 0;
                                                foreach ($functions as $function) {
                                                    foreach ($manageOperations as $operation) {
                                                        if ($roleManager->hasPermission($roleId, $function['id'], $operation['id'])) {
                                                            $permissionCount++;
                                                        }
                                                    }
                                                }
                                                echo $permissionCount;
                                                ?> quyền
                                </span>
                            </h3>

                            <!-- Select all controls -->
                            <div class="flex flex-wrap gap-2 mb-4">
                                <button type="button" id="selectAllView" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 text-sm">
                                    <i class="fas fa-eye mr-1"></i> Chọn tất cả Xem
                                </button>
                                <button type="button" id="selectAllAdd" class="px-3 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200 text-sm">
                                    <i class="fas fa-plus mr-1"></i> Chọn tất cả Thêm
                                </button>
                                <button type="button" id="selectAllEdit" class="px-3 py-1 bg-amber-100 text-amber-700 rounded-md hover:bg-amber-200 text-sm">
                                    <i class="fas fa-pencil-alt mr-1"></i> Chọn tất cả Sửa
                                </button>
                                <button type="button" id="selectAllDelete" class="px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 text-sm">
                                    <i class="fas fa-trash mr-1"></i> Chọn tất cả Xóa
                                </button>
                                <button type="button" id="selectAll" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm">
                                    <i class="fas fa-check-double mr-1"></i> Chọn tất cả
                                </button>
                                <button type="button" id="clearAll" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm">
                                    <i class="fas fa-times mr-1"></i> Bỏ chọn tất cả
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 table-hover">
                                    <thead class="bg-gray-100">
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
                                                    $checkboxName = "permission_{$function['id']}_{$operation['id']}";
                                                    $hasPermission = $roleManager->hasPermission($roleId, $function['id'], $operation['id']);

                                                    // Determine the operation type for styling
                                                    $opClass = '';
                                                    switch ($operation['id']) {
                                                        case 1: // Xem
                                                            $opClass = 'view-permission';
                                                            break;
                                                        case 2: // Thêm
                                                            $opClass = 'add-permission';
                                                            break;
                                                        case 3: // Sửa
                                                            $opClass = 'edit-permission';
                                                            break;
                                                        case 4: // Xóa
                                                            $opClass = 'delete-permission';
                                                            break;
                                                    }
                                                ?>
                                                    <td class="px-3 py-4 whitespace-nowrap text-center">
                                                        <label class="checkbox-wrapper">
                                                            <input type="checkbox"
                                                                name="<?php echo $checkboxName; ?>"
                                                                class="custom-checkbox <?php echo $opClass; ?> function-<?php echo $function['id']; ?>"
                                                                <?php echo $hasPermission ? 'checked' : ''; ?>>
                                                            <span class="checkmark"></span>
                                                        </label>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                        <a href="xemquyen.php?role_id=<?php echo $roleId; ?>" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Hủy
                        </a>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-1"></i> Lưu thay đổi
                        </button>
                    </div>
                </div>
            </form>
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

        // Select all checkboxes
        document.getElementById('selectAll').addEventListener('click', function() {
            document.querySelectorAll('.custom-checkbox').forEach(function(checkbox) {
                checkbox.checked = true;
            });
        });

        // Clear all checkboxes
        document.getElementById('clearAll').addEventListener('click', function() {
            document.querySelectorAll('.custom-checkbox').forEach(function(checkbox) {
                checkbox.checked = false;
            });
        });

        // Select all view permissions
        document.getElementById('selectAllView').addEventListener('click', function() {
            document.querySelectorAll('.view-permission').forEach(function(checkbox) {
                checkbox.checked = true;
            });
        });

        // Select all add permissions
        document.getElementById('selectAllAdd').addEventListener('click', function() {
            document.querySelectorAll('.add-permission').forEach(function(checkbox) {
                checkbox.checked = true;
            });
        });

        // Select all edit permissions
        document.getElementById('selectAllEdit').addEventListener('click', function() {
            document.querySelectorAll('.edit-permission').forEach(function(checkbox) {
                checkbox.checked = true;
            });
        });

        // Select all delete permissions
        document.getElementById('selectAllDelete').addEventListener('click', function() {
            document.querySelectorAll('.delete-permission').forEach(function(checkbox) {
                checkbox.checked = true;
            });
        });
    </script>
</body>

</html>