<?php 
session_start();
require_once "../database/database.php";
require_once "../database/phieunhap.php";
require_once "../database/chitietphieunhap.php";
require_once "../database/user.php";

$phieunhap = new PhieuNhap($pdo);
$userTable = new UsersTable();

// Check user session and permissions if needed
$user = null;
if (isset($_SESSION["user"]) && $_SESSION["user"] != null) {
    $user = $userTable->getUserDetailsById($_SESSION["user"]);
    if ($user == null) {
        unset($_SESSION["user"]);
    }
}

// Xử lý xóa phiếu nhập
if (isset($_POST['delete-product'])) {
    $id = $_POST['id'];
    $result = $phieunhap->deleteById($id);
    echo json_encode(['success' => $result]);
    exit();
}

// Xử lý xuất dữ liệu
if (isset($_POST['export-data'])) {
    $format = $_POST['format'] ?? 'csv';
    $data = $phieunhap->getAllPhieuNhap();
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="phieu-nhap-export-'.date('Y-m-d').'.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('ID', 'Người Nhập', 'Tổng Tiền', 'Ngày Nhập', 'Trạng Thái'));
        
        foreach ($data as $row) {
            $status = ($row['status'] == 1) ? 'Hoạt động' : 'Đã hủy';
            fputcsv($output, array(
                $row['id'],
                $row['ten_nguoi_nhap'] ?? $row['idNguoiNhap'],
                $row['tongtien'],
                $row['date'],
                $status
            ));
        }
        fclose($output);
        exit();
    }
    
    // Add more export formats if needed (Excel, PDF, etc.)
}

// Phân trang và tìm kiếm
$itemPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : "";
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : "";
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : "";
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : "date-desc";

$currentPage = max(1, $currentPage);
$offset = ($currentPage - 1) * $itemPerPage;

// Get statistics
$stats = $phieunhap->getStatistics();

// Get filtered data
$totalItems = $phieunhap->countAllPhieuNhap($search, $startDate, $endDate);
$totalPages = ceil($totalItems / $itemPerPage);
$currentPage = min($currentPage, max(1, $totalPages));

$data = $phieunhap->getPhieuNhapPagination($offset, $itemPerPage, $search, $startDate, $endDate, $sortBy);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phiếu nhập</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
        }
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table th {
            background-color: #f9fafb;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            letter-spacing: 0.05em;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        .table td {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        .table tr:hover {
            background-color: #f9fafb;
        }
        .table .actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }
        .table .actions a, .table .actions button {
            width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        .view {
            background-color: #dbeafe;
            color: #3b82f6;
        }
        .view:hover {
            background-color: #bfdbfe;
        }
        .edit {
            background-color: #d1fae5;
            color: #10b981;
        }
        .edit:hover {
            background-color: #a7f3d0;
        }
        .delete {
            background-color: #fee2e2;
            color: #ef4444;
            border: none;
            cursor: pointer;
        }
        .delete:hover {
            background-color: #fecaca;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.2s;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            width: 90%;
            max-width: 900px;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            animation: slideIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .close {
            color: #9ca3af;
            float: right;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
        }
        .close:hover {
            color: #ef4444;
            background-color: #fee2e2;
        }
        .date-input {
            position: relative;
        }
        .date-input input {
            padding-left: 2.5rem;
        }
        .date-input i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
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
            <!-- Header -->
            <div class="mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-file-invoice-dollar mr-2 text-blue-600"></i>
                            Quản Lý Phiếu Nhập
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Xem và quản lý các phiếu nhập hàng
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-2">
                        <button id="exportBtn" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <i class="fas fa-file-export mr-2"></i>
                            Xuất dữ liệu
                        </button>
                                                                                                <?php if ($roleTableSidebar->isAuthorized($adminID, 12, 2)) { ?>

                        <a href="nhapSanPham.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-plus mr-2"></i>
                            Tạo phiếu nhập
                        </a>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total Receipts -->
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Tổng phiếu nhập</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= number_format($stats['total_receipts'] ?? 0) ?></h3>
                        </div>
                        <div class="rounded-full bg-blue-100 p-3">
                            <i class="fas fa-receipt text-blue-500 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Value -->
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Tổng giá trị</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= number_format($stats['total_value'] ?? 0, 0, ',', '.') ?>₫</h3>
                        </div>
                        <div class="rounded-full bg-green-100 p-3">
                            <i class="fas fa-money-bill-wave text-green-500 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Items -->
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Tổng số mặt hàng</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= number_format($stats['total_items'] ?? 0) ?></h3>
                        </div>
                        <div class="rounded-full bg-yellow-100 p-3">
                            <i class="fas fa-boxes-stacked text-yellow-500 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Recent Receipts -->
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Phiếu nhập tháng này</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= number_format($stats['receipts_this_month'] ?? 0) ?></h3>
                        </div>
                        <div class="rounded-full bg-purple-100 p-3">
                            <i class="fas fa-calendar-alt text-purple-500 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart & Filter Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Chart Card -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b flex items-center">
                        <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                        <h2 class="text-lg font-semibold text-gray-700">Giá trị nhập theo thời gian</h2>
                    </div>
                    <div class="p-4">
                        <canvas id="importChart" height="250"></canvas>
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b flex items-center">
                        <i class="fas fa-filter text-blue-500 mr-2"></i>
                        <h2 class="text-lg font-semibold text-gray-700">Bộ lọc</h2>
                    </div>
                    
                    <div class="p-4">
                        <form method="GET" action="" class="space-y-4">
                            <!-- Search Input -->
                            <div class="relative">
                                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm người nhập..." 
                                    class="pl-10 pr-4 py-2 w-full border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                            
                            <!-- Date Range -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="date-input">
                                    <i class="fas fa-calendar"></i>
                                    <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" 
                                        class="w-full border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 py-2">
                                </div>
                                <div class="date-input">
                                    <i class="fas fa-calendar"></i>
                                    <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" 
                                        class="w-full border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 py-2">
                                </div>
                            </div>
                            
                            <!-- Sort By -->
                            <div>
                                <select name="sort" class="w-full border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 py-2 px-3">
                                    <option value="date-desc" <?= $sortBy === 'date-desc' ? 'selected' : '' ?>>Ngày nhập - Mới nhất</option>
                                    <option value="date-asc" <?= $sortBy === 'date-asc' ? 'selected' : '' ?>>Ngày nhập - Cũ nhất</option>
                                    <option value="tongtien-desc" <?= $sortBy === 'tongtien-desc' ? 'selected' : '' ?>>Giá trị - Cao đến thấp</option>
                                    <option value="tongtien-asc" <?= $sortBy === 'tongtien-asc' ? 'selected' : '' ?>>Giá trị - Thấp đến cao</option>
                                </select>
                            </div>
                            
                            <!-- Filter Buttons -->
                            <div class="flex space-x-2">
                                <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-filter mr-2"></i> Lọc
                                </button>
                                <a href="?clear=1" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-times mr-2"></i> Xóa lọc
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Main Table Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                    <div class="flex items-center">
                        <i class="fas fa-list text-blue-500 mr-2"></i>
                        <h2 class="text-lg font-semibold text-gray-700">Danh sách phiếu nhập</h2>
                    </div>
                    <span class="text-sm text-gray-500">
                        Hiển thị <span class="font-semibold"><?= count($data) ?></span> / <span class="font-semibold"><?= $totalItems ?></span> phiếu nhập
                    </span>
                </div>
                
                <!-- Bảng dữ liệu -->
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-center w-[5%]">ID</th>
                                <th class="w-[23%]">Người nhập</th>
                                <th class="text-right w-[23%]">Tổng tiền</th>
                                <th class="w-[23%]">Ngày nhập</th>
                                <th class="text-center w-[23%]">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-8">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-file-alt text-gray-300 text-5xl mb-3"></i>
                                            <p class="text-gray-500 text-lg">Không tìm thấy phiếu nhập</p>
                                            <p class="text-gray-400 text-sm mt-1">Hãy thử thay đổi điều kiện tìm kiếm</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data as $pn): ?>
                                    <tr>
                                        <td class="text-center font-medium"><?= $pn['id'] ?></td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium"><?= htmlspecialchars($pn['ten_nguoi_nhap'] ?? 'User #'.$pn['idNguoiNhap']) ?></div>
                                                    <div class="text-xs text-gray-500">ID: <?= $pn['idNguoiNhap'] ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-left font-medium">
                                            <?= number_format($pn['tongtien'], 0, ',', '.') ?>₫
                                        </td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center mr-3">
                                                    <i class="fas fa-calendar"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium">
                                                        <?= date('d/m/Y', strtotime($pn['date'])) ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?= date('H:i:s', strtotime($pn['date'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                      
                                        <td>
                                            <div class="actions">
                                                
                                                <a href="#" class="view" data-id="<?= $pn['id']; ?>" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="in-phieu-nhap.php?id=<?= $pn['id']; ?>" class="edit" title="In phiếu nhập">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                                                                <?php if ($roleTableSidebar->isAuthorized($adminID, 12, 4)) { ?>

                                                <button onclick="deleteProduct(<?= $pn['id'] ?>)" class="delete" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="py-4 px-6 border-t">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Hiển thị <?= min(($currentPage - 1) * $itemPerPage + 1, $totalItems) ?> đến <?= min($currentPage * $itemPerPage, $totalItems) ?> của <?= $totalItems ?> phiếu nhập
                        </div>
                        <nav class="flex items-center space-x-1">
                            <?php if ($currentPage > 1): ?>
                                <a href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&sort=<?= urlencode($sortBy) ?>" 
                                   class="px-3 py-1 rounded border hover:bg-gray-100 transition">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            
                            if ($startPage > 1) {
                                echo '<a href="?page=1&search='.urlencode($search).'&start_date='.urlencode($startDate).'&end_date='.urlencode($endDate).'&sort='.urlencode($sortBy).'" class="px-3 py-1 rounded border hover:bg-gray-100 transition">1</a>';
                                if ($startPage > 2) {
                                    echo '<span class="px-3 py-1">...</span>';
                                }
                            }
                            
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                $activeClass = ($i == $currentPage) 
                                    ? 'bg-blue-500 text-white' 
                                    : 'hover:bg-gray-100 border';
                                echo '<a href="?page='.$i.'&search='.urlencode($search).'&start_date='.urlencode($startDate).'&end_date='.urlencode($endDate).'&sort='.urlencode($sortBy).'" class="px-3 py-1 rounded '.$activeClass.' transition">'.$i.'</a>';
                            }
                            
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<span class="px-3 py-1">...</span>';
                                }
                                echo '<a href="?page='.$totalPages.'&search='.urlencode($search).'&start_date='.urlencode($startDate).'&end_date='.urlencode($endDate).'&sort='.urlencode($sortBy).'" class="px-3 py-1 rounded border hover:bg-gray-100 transition">'.$totalPages.'</a>';
                            }
                            ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&sort=<?= urlencode($sortBy) ?>" 
                                   class="px-3 py-1 rounded border hover:bg-gray-100 transition">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal hiển thị chi tiết -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    Chi tiết phiếu nhập
                </h2>
                <span class="close">&times;</span>
            </div>
            <div id="modalBody" class="max-h-[70vh] overflow-auto"></div>
            <div class="mt-6 flex justify-end border-t pt-4">
                <button id="printDetail" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 mr-3">
                    <i class="fas fa-print mr-2"></i> In phiếu
                </button>
                <button id="closeModal" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    <i class="fas fa-times mr-2"></i> Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- Modal xuất dữ liệu -->
    <div id="exportModal" class="modal">
        <div class="modal-content max-w-md">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-file-export text-green-500 mr-2"></i>
                    Xuất dữ liệu
                </h2>
                <span class="close">&times;</span>
            </div>
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Định dạng</label>
                    <select name="format" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="csv">CSV</option>
                        <option value="excel" disabled>Excel (Sắp có)</option>
                        <option value="pdf" disabled>PDF (Sắp có)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bao gồm dữ liệu</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="include_all" checked class="rounded text-green-500 focus:ring-green-500 mr-2">
                            <span>Tất cả phiếu nhập</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="include_details" class="rounded text-green-500 focus:ring-green-500 mr-2">
                            <span>Chi tiết từng phiếu</span>
                        </label>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end border-t pt-4">
                    <button type="submit" name="export-data" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        <i class="fas fa-download mr-2"></i> Tải xuống
                    </button>
                </div>
            </form>
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

        // Initialize chart
       document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('importChart').getContext('2d');
    
    // Get data from PHP statistics
    const monthlyData = <?php echo json_encode($stats['monthly_data'] ?? []); ?>;
    
    // Process data for chart
    const labels = [];
    const values = [];
    
    if (monthlyData && monthlyData.length > 0) {
        monthlyData.forEach(item => {
            labels.push(item.month);
            values.push(item.value);
        });
    } else {
        // Fallback if no data
        labels.push('No Data');
        values.push(0);
    }
    
    const chartData = {
        labels: labels,
        datasets: [{
            label: 'Giá trị nhập (VNĐ)',
            data: values,
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 2,
            tension: 0.3,
            pointBackgroundColor: 'rgba(59, 130, 246, 1)',
            fill: true
        }]
    };
    
    const config = {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN', {
                                style: 'currency',
                                currency: 'VND',
                                maximumFractionDigits: 0
                            }).format(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('vi-VN', {
                                    style: 'currency',
                                    currency: 'VND',
                                    maximumFractionDigits: 0
                                }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                },
                legend: {
                    position: 'top',
                    labels: {
                        padding: 20,
                        font: {
                            family: "'Quicksand', sans-serif",
                            size: 12
                        }
                    }
                }
            }
        }
    };
    
    new Chart(ctx, config);
});
        
        // Modal handling
        $(document).ready(function() {
            // Chi tiết phiếu nhập
            $(document).on('click', '.view', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                
                $.ajax({
                    url: './chitietphieunhap.php',
                    type: 'GET',
                    data: { id: id },
                    beforeSend: function() {
                        $('#modalBody').html('<div class="flex justify-center items-center p-10"><i class="fas fa-circle-notch fa-spin text-blue-500 text-3xl"></i></div>');
                        $('#detailModal').show();
                    },
                    success: function(response) {
                        $('#modalBody').html(response);
                    },
                    error: function() {
                        $('#modalBody').html('<div class="text-center p-5 text-red-500"><i class="fas fa-exclamation-circle mr-2"></i> Không thể tải dữ liệu chi tiết</div>');
                    }
                });
            });
            
            // Đóng modal chi tiết
            $('.close, #closeModal').click(function() {
                $('#detailModal').hide();
            });
            
            // In phiếu
            $('#printDetail').click(function() {
                var printContents = document.getElementById('modalBody').innerHTML;
                var originalContents = document.body.innerHTML;
                
                document.body.innerHTML = `
                    <div style="padding: 20px;">
                        <h1 style="text-align: center; margin-bottom: 20px; font-size: 24px;">CHI TIẾT PHIẾU NHẬP</h1>
                        ${printContents}
                    </div>`;
                
                window.print();
                document.body.innerHTML = originalContents;
                location.reload();
            });
            
            // Export modal
            $('#exportBtn').click(function() {
                $('#exportModal').show();
            });
            
            $('#exportModal .close').click(function() {
                $('#exportModal').hide();
            });
            
            // Click outside to close modals
            $(window).click(function(event) {
                if (event.target == document.getElementById('detailModal')) {
                    $('#detailModal').hide();
                }
                if (event.target == document.getElementById('exportModal')) {
                    $('#exportModal').hide();
                }
            });
        });
        
        // Delete confirmation
        function deleteProduct(id) {
            Swal.fire({
                title: 'Bạn có chắc chắn?',
                text: "Xóa phiếu nhập này sẽ xóa toàn bộ chi tiết phiếu nhập liên quan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '',
                        type: 'POST',
                        data: {
                            'delete-product': 1,
                            'id': id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Đã xóa!',
                                    text: 'Phiếu nhập đã được xóa thành công.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Lỗi!',
                                    'Không thể xóa phiếu nhập.',
                                    'error'
                                );
                            }
                        },
                        error: function() {
                            Swal.fire(
                                'Lỗi!',
                                'Đã xảy ra lỗi trong quá trình xóa.',
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>