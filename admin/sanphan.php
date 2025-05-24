<?php
session_start();
require_once("../database/database.php");
require_once("../database/book.php");
require_once("../database/chitiethoadon.php");

$bookTable = new BooksTable();
$chiTietHoadonTable = new ChiTietHoadonTable();
// Handle product status updates (active/inactive)
if (isset($_POST['update-status']) && isset($_POST['id']) && isset($_POST['isActive'])) {
    $id = $_POST['id'];
    $isActive = $_POST['isActive'];

    $result = $bookTable->changeActive($id, $isActive);

    if ($result) {
        $message = $isActive == 1 ? "Sản phẩm đã được cập nhật để bán!" : "Sản phẩm đã được ngừng bán!";
        echo json_encode([
            'success' => true,
            'message' => $message,
            'newStatus' => $isActive
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Đã xảy ra lỗi khi cập nhật trạng thái sản phẩm!"
        ]);
    }
    exit();
}

// Handle product deletion
if (isset($_POST['delete-product'])) {
    $id = $_POST['id'];
    $result = $bookTable->deleteById($id);
    echo json_encode(['success' => $result]);
    exit();
}

// Pagination setup
$itemPerPage = 8;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : "";
$subjectFilter = isset($_GET['subject']) ? (int)$_GET['subject'] : 0;
$classFilter = isset($_GET['class']) ? (int)$_GET['class'] : 0;
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : "id-asc";

$offset = ($currentPage - 1) * $itemPerPage;

// Build SQL query
$sqlCount = "SELECT COUNT(*) as total FROM books WHERE status = 1";
$sqlQuery = "SELECT * FROM books WHERE status = 1";

// Apply filters
if (!empty($search)) {
    $sqlCount .= " AND bookName LIKE '%$search%'";
    $sqlQuery .= " AND bookName LIKE '%$search%'";
}

if ($subjectFilter > 0) {
    $sqlCount .= " AND subjectId = $subjectFilter";
    $sqlQuery .= " AND subjectId = $subjectFilter";
}

if ($classFilter > 0) {
    $sqlCount .= " AND classNumber = $classFilter";
    $sqlQuery .= " AND classNumber = $classFilter";
}

// Get total count for pagination
$stmt = $pdo->prepare($sqlCount);
$stmt->execute();
$totalResult = $stmt->fetch(PDO::FETCH_ASSOC);
$totalItems = $totalResult['total'];
$totalPages = ceil($totalItems / $itemPerPage);

// Apply sorting
list($sortField, $sortDirection) = explode('-', $sortBy);
$validSortFields = ['id', 'bookName', 'classNumber', 'currentPrice', 'quantitySold'];
$validSortDirections = ['asc', 'desc'];

if (in_array($sortField, $validSortFields) && in_array($sortDirection, $validSortDirections)) {
    $sqlQuery .= " ORDER BY $sortField $sortDirection";
} else {
    $sqlQuery .= " ORDER BY id ASC";
}

// Apply pagination
$sqlQuery .= " LIMIT $itemPerPage OFFSET $offset";

// Get books
$products = $bookTable->getBooksByCondition($sqlQuery);

// Get subjects for filter
$subjects = $bookTable->getAllSubject();

// Get unique class numbers
$stmt = $pdo->prepare("SELECT DISTINCT classNumber FROM books WHERE status = 1 ORDER BY classNumber");
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);


$productSales = $chiTietHoadonTable->getAllProductSales();
$totalRevenue = 0;
$totalItemsSold = 0;
foreach ($productSales as $product) {
    $totalRevenue += $product['total_revenue'];
    $totalItemsSold += $product['quantity_sold'];
};
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sách Giáo Khoa</title>
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
            <div class="mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-book mr-2 text-blue-600"></i>
                            Quản lý Sách Giáo Khoa
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Xem, tìm kiếm và quản lý danh sách sách giáo khoa
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
                        <?php if ($roleTableSidebar->isAuthorized($adminID, 7, 2)) { ?>

                            <a href="themsanpham.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-plus mr-2"></i>
                                Thêm sách mới
                            </a>
                        <?php } ?>
                        <?php if ($roleTableSidebar->isAuthorized($adminID, 12, 2)) { ?>

                            <a href="nhapsanpham.php" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-file-import mr-2"></i>
                                Nhập sách
                            </a>
                        <?php } ?>
                        <?php if ($roleTableSidebar->isAuthorized($adminID, 12, 1)) { ?>

                            <a href="lichsunhap.php" class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                                <i class="fas fa-history mr-2"></i>
                                Lịch sử nhập
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <?php
                // Get statistics
                $totalBooks = $totalItems;

                $stmt = $pdo->prepare("SELECT SUM(quantitySold) as total FROM books WHERE status = 1");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalSold = $result['total'] ?? 0;

                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM books WHERE isActive = 1 AND status = 1");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $activeBooks = $result['total'] ?? 0;


                ?>

                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500 stats-card">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Tổng số sách</p>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo number_format($totalBooks); ?></p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 text-blue-500">
                            <i class="fas fa-book text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500 stats-card">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Số lượng đã bán</p>
                            <p class="text-2xl font-semibold text-gray-800">
                                <?php echo number_format($totalItemsSold, 0, ',', '.'); ?>

                            </p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-500">
                            <i class="fas fa-shopping-cart text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-amber-500 stats-card">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Sách đang bán</p>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo number_format($activeBooks); ?></p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-amber-100 text-amber-500">
                            <i class="fas fa-tag text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500 stats-card">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Doanh thu bán sách</p>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo number_format($totalRevenue, 0, ',', '.'); ?>đ</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-purple-100 text-purple-500">
                            <i class="fas fa-dollar-sign text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                <div class="p-4 bg-gray-50 border-b">
                    <h2 class="text-lg font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-search mr-2 text-blue-600"></i>
                        Tìm kiếm và lọc sách
                    </h2>
                </div>
                <div class="p-4">
                    <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tên sách</label>
                            <div class="relative">
                                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                                    placeholder="Tìm kiếm sách..."
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 pl-10 pr-3 py-2 border">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Môn học</label>
                            <select name="subject" class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 border">
                                <option value="0">Tất cả môn học</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo $subject['id']; ?>" <?php echo $subjectFilter == $subject['id'] ? 'selected' : ''; ?>>
                                        <?php echo $subject['subjectName']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lớp</label>
                            <select name="class" class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 border">
                                <option value="0">Tất cả các lớp</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['classNumber']; ?>" <?php echo $classFilter == $class['classNumber'] ? 'selected' : ''; ?>>
                                        Lớp <?php echo $class['classNumber']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sắp xếp theo</label>
                            <select name="sort" class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 border">
                                <option value="id-asc" <?php echo $sortBy == 'id-asc' ? 'selected' : ''; ?>>ID (tăng dần)</option>
                                <option value="id-desc" <?php echo $sortBy == 'id-desc' ? 'selected' : ''; ?>>ID (giảm dần)</option>
                                <option value="bookName-asc" <?php echo $sortBy == 'bookName-asc' ? 'selected' : ''; ?>>Tên sách (A-Z)</option>
                                <option value="bookName-desc" <?php echo $sortBy == 'bookName-desc' ? 'selected' : ''; ?>>Tên sách (Z-A)</option>
                                <option value="currentPrice-asc" <?php echo $sortBy == 'currentPrice-asc' ? 'selected' : ''; ?>>Giá (thấp đến cao)</option>
                                <option value="currentPrice-desc" <?php echo $sortBy == 'currentPrice-desc' ? 'selected' : ''; ?>>Giá (cao đến thấp)</option>
                                <option value="quantitySold-desc" <?php echo $sortBy == 'quantitySold-desc' ? 'selected' : ''; ?>>Bán chạy nhất</option>
                                <option value="quantitySold-asc" <?php echo $sortBy == 'quantitySold-asc' ? 'selected' : ''; ?>>Bán chậm nhất</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-filter mr-2"></i>
                                Lọc kết quả
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="flex justify-between items-center p-4 border-b">
                    <h2 class="text-lg font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-th-list mr-2 text-blue-600"></i>
                        Danh sách (<?php echo $totalItems; ?> cuốn)
                    </h2>

                    <?php if ($totalItems > 0): ?>
                        <span class="text-sm text-gray-500">
                            Hiển thị <?php echo min($itemPerPage, $totalItems); ?> trên <?php echo $totalItems; ?> cuốn
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (count($products) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-hover">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Thông tin sách
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Lớp
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Giá
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Còn kho </th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Trạng thái
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Thao tác
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($products as $product): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-16 w-16 rounded overflow-hidden bg-gray-100">
                                                    <?php if (!empty($product['imageURL'])): ?>
                                                        <img class="h-16 w-16 object-cover" src="<?php echo htmlspecialchars($product['imageURL']); ?>" alt="<?php echo htmlspecialchars($product['bookName']); ?>">
                                                    <?php else: ?>
                                                        <div class="h-16 w-16 flex items-center justify-center bg-gray-200">
                                                            <i class="fas fa-book text-gray-400 text-3xl"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($product['bookName']); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Môn: <?php echo htmlspecialchars($bookTable->getSubjectNameById($product['subjectId'])); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-0.5 truncate max-w-xs">
                                                        <?php echo !empty($product['description']) ? htmlspecialchars(substr($product['description'], 0, 50)) . '...' : 'Không có mô tả'; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <?php echo $product['classNumber']; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo number_format($product['currentPrice'], 0, ',', '.'); ?>đ
                                            </div>
                                            <?php if ($product['currentPrice'] < $product['oldPrice']): ?>
                                                <div class="text-xs text-gray-500 line-through">
                                                    <?php echo number_format($product['oldPrice'], 0, ',', '.'); ?>đ
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo number_format($product['quantitySold']); ?>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <?php if ($product['isActive'] == 1): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i> Đang bán
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    <i class="fas fa-ban mr-1"></i> Ngừng bán
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($roleTableSidebar->isAuthorized($adminID, 7, 3) || $roleTableSidebar->isAuthorized($adminID, 7, 4)) { ?>

                                            <td class="px-4 py-3 text-center">
                                                <?php if ($roleTableSidebar->isAuthorized($adminID, 7, 3)) { ?>
                                                    <button class="text-blue-600 hover:text-blue-900 update-icon" data-id="<?php echo $product['id']; ?>" title="Sửa sách">
                                                        <i class="fas fa-edit text-lg"></i>
                                                    </button>


                                                    <button class="text-<?php echo $product['isActive'] == 1 ? 'green' : 'red'; ?>-600 hover:text-<?php echo $product['isActive'] == 1 ? 'green' : 'red'; ?>-900 check-icon"
                                                        data-id="<?php echo $product['id']; ?>"
                                                        data-active="<?php echo $product['isActive']; ?>"
                                                        title="<?php echo $product['isActive'] == 1 ? 'Đang bán' : 'Ngừng bán'; ?>">
                                                        <i class="fas fa-toggle-<?php echo $product['isActive'] == 1 ? 'on' : 'off'; ?> text-xl"></i>
                                                    </button>
                                                <?php } ?>
                                                <?php if ($roleTableSidebar->isAuthorized($adminID, 7, 4)) { ?>

                                                    <button class="text-red-600 hover:text-red-900 delete-icon" data-id="<?php echo $product['id']; ?>" title="Xóa sách">
                                                        <i class="fas fa-trash-alt text-lg"></i>
                                                    </button>
                                                <?php } ?>
                    </div>
                <?php } ?>

                </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex justify-center">
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php if ($currentPage > 1): ?>
                                <a href="?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>&subject=<?php echo $subjectFilter; ?>&class=<?php echo $classFilter; ?>&sort=<?php echo $sortBy; ?>"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&subject=<?php echo $subjectFilter; ?>&class=<?php echo $classFilter; ?>&sort=<?php echo $sortBy; ?>"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i == $currentPage ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>&subject=<?php echo $subjectFilter; ?>&class=<?php echo $classFilter; ?>&sort=<?php echo $sortBy; ?>"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="p-8 text-center">
                <div class="inline-block p-4 rounded-full bg-blue-100 text-blue-500 mb-4">
                    <i class="fas fa-search text-4xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Không tìm thấy sách</h3>
                <p class="text-gray-500 mb-6">Không có sách nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                <a href="sanphan.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-redo mr-2"></i>
                    Xem tất cả sách
                </a>
            </div>
        <?php endif; ?>
        </div>
        </div>
    </main>

    <!-- Book Edit Modal (placeholder, would be shown via JavaScript) -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
            <div class="modal-content">
                <!-- Content will be loaded via AJAX -->
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

        // Handle book status toggle
        document.querySelectorAll('.check-icon').forEach(function(button) {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const isCurrentlyActive = this.getAttribute('data-active') === '1';
                const newStatus = isCurrentlyActive ? 0 : 1;

                Swal.fire({
                    title: isCurrentlyActive ? 'Ngừng bán sách?' : 'Bắt đầu bán sách?',
                    text: isCurrentlyActive ? 'Sách sẽ không còn được hiển thị để bán' : 'Sách sẽ được hiển thị để bán',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'sanphan.php',
                            method: 'POST',
                            data: {
                                'update-status': true,
                                'id': id,
                                'isActive': newStatus
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Thành công!',
                                        text: response.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Lỗi!',
                                        text: response.message,
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Lỗi!',
                                    text: 'Đã xảy ra lỗi khi cập nhật trạng thái.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });
        });

        // Handle book deletion
        document.querySelectorAll('.delete-icon').forEach(function(button) {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');

                Swal.fire({
                    title: 'Xóa sách?',
                    text: 'Bạn sẽ không thể khôi phục lại sách sau khi xóa!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'sanphan.php',
                            method: 'POST',
                            data: {
                                'delete-product': true,
                                'id': id
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Đã xóa!',
                                        text: 'Sách đã được xóa thành công.',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Lỗi!',
                                        text: 'Không thể xóa sách.',
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Lỗi!',
                                    text: 'Đã xảy ra lỗi khi xóa sách.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });
        });

        // Handle book update/edit
        document.querySelectorAll('.update-icon').forEach(function(button) {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                window.location.href = `suasanpham.php?id=${id}`;
            });
        });
    </script>

    <style>
        .stats-card {
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .table-hover tr:hover {
            background-color: #f9fafb;
        }
    </style>
</body>

</html>