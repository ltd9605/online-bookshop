<?php
session_start();
require_once("../database/database.php");
require_once("../database/supplier.php");
require_once("../database/user.php");

// Check user session and permissions if needed
$userTable = new UsersTable();
$user = null;
if (isset($_SESSION["user"]) && $_SESSION["user"] != null) {
    $user = $userTable->getUserDetailsById($_SESSION["user"]);
    if ($user == null) {
        unset($_SESSION["user"]);
    }
}

// Create supplier table instance
$supplierTable = new SupplierTable();

// Handle add supplier
if (isset($_POST['add-supplier'])) {
    $name = $_POST['name'] ?? '';

    if (!empty($name)) {
        $result = $supplierTable->addSupplier($name);

        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Tên nhà cung cấp không được để trống']);
        exit();
    }
}

// Handle update supplier
if (isset($_POST['update-supplier'])) {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';

    if (!empty($name) && $id > 0) {
        $result = $supplierTable->updateSupplier($id, $name);

        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Thông tin không hợp lệ']);
        exit();
    }
}

// Handle delete supplier
if (isset($_POST['delete-supplier'])) {
    $id = $_POST['id'] ?? 0;

    if ($id > 0) {
        // Check if supplier is used in any books
        $booksWithSupplier = $supplierTable->countBooksWithSupplier($id);

        if ($booksWithSupplier > 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không thể xóa nhà cung cấp đang được sử dụng']);
            exit();
        }

        $result = $supplierTable->deleteSupplier($id);

        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID nhà cung cấp không hợp lệ']);
        exit();
    }
}

// Pagination setup
$itemPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$offset = ($currentPage - 1) * $itemPerPage;

// Get suppliers with pagination
$suppliers = $supplierTable->getSuppliers($search, $itemPerPage, $offset);
$totalItems = $supplierTable->countSuppliers($search);
$totalPages = ceil($totalItems / $itemPerPage);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Nhà Cung Cấp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <!-- Header -->
            <div class="mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-building mr-2 text-blue-600"></i>
                            Quản Lý Nhà Cung Cấp
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Quản lý thông tin các nhà cung cấp sách giáo khoa
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <button id="addSupplierBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-plus mr-2"></i>
                            Thêm nhà cung cấp
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search and filters -->
            <div class="bg-white rounded-lg p-4 shadow-sm mb-6">
                <form action="" method="get" class="flex flex-col sm:flex-row gap-3 items-center">
                    <div class="relative flex-grow">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm nhà cung cấp..." class="pl-10 pr-4 py-2 border outline-transparent rounded-lg w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 whitespace-nowrap">
                        <i class="fas fa-search mr-2"></i>Tìm kiếm
                    </button>
                    <?php if (!empty($search)) : ?>
                        <a href="supplier.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-times mr-2"></i>Xóa bộ lọc
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Main content card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-list mr-2 text-blue-600"></i>
                        Danh sách nhà cung cấp
                    </h2>
                    <span class="text-sm text-gray-500">
                        Tổng số: <span class="font-semibold"><?= $totalItems ?></span> nhà cung cấp
                    </span>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tên nhà cung cấp
                                </th>
                                                                                                             <?php if ($roleTableSidebar->isAuthorized($adminID, 13, 4) || $roleTableSidebar->isAuthorized($adminID,13,3)) { ?>

                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thao tác
                                </th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($suppliers)) : ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                        <?= empty($search) ? 'Chưa có nhà cung cấp nào' : 'Không tìm thấy nhà cung cấp phù hợp' ?>
                                    </td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($suppliers as $supplier) : ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?= $supplier['id'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($supplier['name']) ?>
                                        </td>
                                     
                                                <?php if ($roleTableSidebar->isAuthorized($adminID, 13, 4) || $roleTableSidebar->isAuthorized($adminID,13,3)) { ?>

                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <?php if ($roleTableSidebar->isAuthorized($adminID, 13, 3)) { ?>

                                                <button class="edit-supplier text-blue-600 hover:text-blue-900 mr-3"
                                                    data-id="<?= $supplier['id'] ?>"
                                                    data-name="<?= htmlspecialchars($supplier['name']) ?>">
                                                    <i class="fas fa-edit mr-1"></i>Sửa
                                                </button>
                                            <?php } ?>
                                            <?php if ($roleTableSidebar->isAuthorized($adminID, 13, 4)) { ?>

                                                <button class="delete-supplier text-red-600 hover:text-red-900" data-id="<?= $supplier['id'] ?>">
                                                    <i class="fas fa-trash-alt mr-1"></i>Xóa
                                                </button>
                                            <?php } ?>

                                        </td>
                                        <?php } ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1) : ?>
                    <nav class="border-t border-gray-200 px-4 flex items-center justify-between sm:px-6">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between py-3">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Hiển thị <span class="font-medium"><?= min(($currentPage - 1) * $itemPerPage + 1, $totalItems) ?></span> đến <span class="font-medium"><?= min($currentPage * $itemPerPage, $totalItems) ?></span> trong số <span class="font-medium"><?= $totalItems ?></span> nhà cung cấp
                                </p>
                            </div>
                            <div>
                                <div class="relative z-0 inline-flex shadow-sm -space-x-px" aria-label="Pagination">
                                    <!-- Previous page -->
                                    <?php if ($currentPage > 1) : ?>
                                        <a href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php else : ?>
                                        <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                            <i class="fas fa-chevron-left"></i>
                                        </span>
                                    <?php endif; ?>

                                    <!-- Page numbers -->
                                    <?php
                                    $startPage = max(1, $currentPage - 2);
                                    $endPage = min($totalPages, $startPage + 4);

                                    if ($startPage > 1) : ?>
                                        <a href="?page=1&search=<?= urlencode($search) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            1
                                        </a>
                                        <?php if ($startPage > 2) : ?>
                                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                                ...
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php for ($i = $startPage; $i <= $endPage; $i++) : ?>
                                        <?php if ($i == $currentPage) : ?>
                                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">
                                                <?= $i ?>
                                            </span>
                                        <?php else : ?>
                                            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                <?= $i ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endfor; ?>

                                    <?php if ($endPage < $totalPages) : ?>
                                        <?php if ($endPage < $totalPages - 1) : ?>
                                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                                ...
                                            </span>
                                        <?php endif; ?>
                                        <a href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            <?= $totalPages ?>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Next page -->
                                    <?php if ($currentPage < $totalPages) : ?>
                                        <a href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php else : ?>
                                        <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                            <i class="fas fa-chevron-right"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Add/Edit Supplier Modal -->
    <div id="supplierModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="supplierForm">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-building text-blue-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Thêm nhà cung cấp mới
                                </h3>
                                <div class="mt-4">
                                    <input type="hidden" id="supplier-id" name="id" value="">
                                    <div class="mb-4">
                                        <label for="supplier-name" class="block text-sm font-medium text-gray-700 mb-1">Tên nhà cung cấp <span class="text-red-600">*</span></label>
                                        <input type="text" id="supplier-name" name="name" class="mt-1 focus:ring-blue-500 outline-transparent focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Nhập tên nhà cung cấp" required>
                                        <div id="name-error" class="text-red-600 text-sm mt-1 hidden"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <span id="submit-text">Lưu</span>
                        </button>
                        <button type="button" id="cancelBtn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Hủy
                        </button>
                    </div>
                </form>
            </div>
        </div>
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

        $(document).ready(function() {
            // Show modal on add supplier button click
            $('#addSupplierBtn').click(function() {
                $('#modal-title').text('Thêm nhà cung cấp mới');
                $('#submit-text').text('Thêm');
                $('#supplier-id').val('');
                $('#supplier-name').val('');
                $('#name-error').addClass('hidden');
                $('#supplierModal').removeClass('hidden');
                $('#supplierForm').data('action', 'add');
            });

            // Edit supplier
            $('.edit-supplier').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');

                $('#modal-title').text('Sửa nhà cung cấp');
                $('#submit-text').text('Cập nhật');
                $('#supplier-id').val(id);
                $('#supplier-name').val(name);
                $('#name-error').addClass('hidden');
                $('#supplierModal').removeClass('hidden');
                $('#supplierForm').data('action', 'edit');
            });

            // Delete supplier
            $('.delete-supplier').click(function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Xác nhận xóa?',
                    text: "Bạn có chắc chắn muốn xóa nhà cung cấp này?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: window.location.href,
                            type: 'POST',
                            data: {
                                'delete-supplier': 1,
                                'id': id
                            },
                            success: function(response) {
                                try {
                                    const data = typeof response === 'string' ? JSON.parse(response) : response;

                                    if (data.success) {
                                        Swal.fire(
                                            'Đã xóa!',
                                            'Nhà cung cấp đã được xóa thành công.',
                                            'success'
                                        ).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire(
                                            'Lỗi!',
                                            data.message || 'Không thể xóa nhà cung cấp.',
                                            'error'
                                        );
                                    }
                                } catch (e) {
                                    Swal.fire(
                                        'Lỗi!',
                                        'Đã xảy ra lỗi khi xử lý yêu cầu.',
                                        'error'
                                    );
                                }
                            },
                            error: function() {
                                Swal.fire(
                                    'Lỗi!',
                                    'Đã xảy ra lỗi khi xử lý yêu cầu.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Close modal
            $('#cancelBtn').click(function() {
                $('#supplierModal').addClass('hidden');
            });

            // Form submit
            $('#supplierForm').submit(function(e) {
                e.preventDefault();

                const action = $(this).data('action');
                const id = $('#supplier-id').val();
                const name = $('#supplier-name').val().trim();

                // Validation
                if (!name) {
                    $('#name-error').text('Vui lòng nhập tên nhà cung cấp').removeClass('hidden');
                    return;
                }

                // Form data
                const formData = {
                    'name': name
                };

                if (action === 'edit') {
                    formData['id'] = id;
                    formData['update-supplier'] = 1;
                } else {
                    formData['add-supplier'] = 1;
                }

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;

                            if (data.success) {
                                $('#supplierModal').addClass('hidden');

                                Swal.fire({
                                    icon: 'success',
                                    title: action === 'edit' ? 'Cập nhật thành công!' : 'Thêm mới thành công!',
                                    text: action === 'edit' ? 'Nhà cung cấp đã được cập nhật.' : 'Đã thêm nhà cung cấp mới.',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Lỗi!',
                                    data.message || 'Có lỗi xảy ra khi xử lý yêu cầu.',
                                    'error'
                                );
                            }
                        } catch (e) {
                            Swal.fire(
                                'Lỗi!',
                                'Đã xảy ra lỗi khi xử lý phản hồi từ máy chủ.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Lỗi!',
                            'Đã xảy ra lỗi khi gửi yêu cầu.',
                            'error'
                        );
                    }
                });
            });
        });
    </script>
</body>

</html>