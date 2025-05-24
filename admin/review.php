<?php
session_start();
require_once "../database/database.php";
require_once "../database/review.php";
require_once "../database/book.php";
require_once "../database/user.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize tables
$reviewTable = new ReviewTable();
$bookTable = new BooksTable();
$userTable = new UsersTable();

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$ratingFilter = isset($_GET['rating']) ? intval($_GET['rating']) : 0;

// Handle actions
if (isset($_POST['action'])) {
    $reviewId = isset($_POST['reviewId']) ? intval($_POST['reviewId']) : 0;

    if ($_POST['action'] === 'delete' && $reviewId > 0) {
        $reviewTable->deleteReview($reviewId);
        $_SESSION['message'] = "Đánh giá đã được xóa thành công";
    }

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?search=$search&page=$page&rating=$ratingFilter");
    exit;
}

// Get reviews based on filters
$reviews = $reviewTable->getFilteredReviews(
    search: $search,
    ratingFilter: $ratingFilter,
    page: $page,
    perPage: $perPage
);
$totalReviews = $reviewTable->countFilteredReviews($search, $ratingFilter);
$totalPages = ceil($totalReviews / $perPage);

// Get statistics
$stats = $reviewTable->getReviewStatistics();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đánh giá - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <main class="flex flex-row h-screen">
        <?php include_once './gui/sidebar.php' ?>
        <div class="flex flex-col w-full h-screen">
            <div class="h-screen">
                <div class="bg-white shadow-lg overflow-y-scroll h-screen border border-gray-300 rounded-lg p-6 w-full">
                    <div class="review-container">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="title text-2xl font-semibold text-gray-800">Quản lý đánh giá</h2>
                            <div class="flex gap-4">
                                <div class="stat-card bg-blue-50 border border-blue-200 rounded p-3 text-center">
                                    <span class="block text-2xl font-bold text-blue-600"><?php echo $stats['total']; ?></span>
                                    <span class="text-sm text-blue-600">Tổng số</span>
                                </div>
                                <div class="stat-card bg-yellow-50 border border-yellow-200 rounded p-3 text-center">
                                    <span class="block text-2xl font-bold text-yellow-600"><?php echo $stats['avgRating']; ?></span>
                                    <span class="text-sm text-yellow-600">Đánh giá TB</span>
                                </div>
                                <div class="stat-card bg-green-50 border border-green-200 rounded p-3 text-center">
                                    <span class="block text-2xl font-bold text-green-600"><?php echo $stats['highRatings']; ?></span>
                                    <span class="text-sm text-green-600">Đánh giá tốt (4-5★)</span>
                                </div>
                                <div class="stat-card bg-red-50 border border-red-200 rounded p-3 text-center">
                                    <span class="block text-2xl font-bold text-red-600"><?php echo $stats['lowRatings']; ?></span>
                                    <span class="text-sm text-red-600">Đánh giá thấp (1-2★)</span>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                                <?php echo $_SESSION['message']; ?>
                                <?php unset($_SESSION['message']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex justify-between mb-4 flex-wrap gap-2">
                            <!-- Search Form -->
                            <div class="search">
                                <form method="GET" action="" class="flex items-center gap-2">
                                    <input type="hidden" name="rating" value="<?php echo $ratingFilter; ?>">
                                    <input type="text" name="search" id="searchInput" placeholder="Tìm kiếm theo tên sách, người dùng hoặc nội dung"
                                        class="search-bar px-4 py-2 border border-gray-300 rounded-md w-80"
                                        value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                </form>
                            </div>

                            <!-- Rating Filter -->
                            <div class="rating-filter">
                                <form method="GET" class="flex items-center gap-2">
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                    <select name="rating" class="px-4 py-2 border border-gray-300 rounded-md" onchange="this.form.submit()">
                                        <option value="0" <?php echo $ratingFilter == 0 ? 'selected' : ''; ?>>Tất cả đánh giá</option>
                                        <option value="5" <?php echo $ratingFilter == 5 ? 'selected' : ''; ?>>5 sao</option>
                                        <option value="4" <?php echo $ratingFilter == 4 ? 'selected' : ''; ?>>4 sao</option>
                                        <option value="3" <?php echo $ratingFilter == 3 ? 'selected' : ''; ?>>3 sao</option>
                                        <option value="2" <?php echo $ratingFilter == 2 ? 'selected' : ''; ?>>2 sao</option>
                                        <option value="1" <?php echo $ratingFilter == 1 ? 'selected' : ''; ?>>1 sao</option>
                                    </select>
                                </form>
                            </div>
                        </div>

                        <div class="table-container overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-3 px-4 border-b text-left">ID</th>
                                        <th class="py-3 px-4 border-b text-left">Sách</th>
                                        <th class="py-3 px-4 border-b text-left">Người dùng</th>
                                        <th class="py-3 px-4 border-b text-left">Đánh giá</th>
                                        <th class="py-3 px-4 border-b text-left">Nhận xét</th>
                                        <th class="py-3 px-4 border-b text-left">Ngày đăng</th>
                                        <?php if ($roleTableSidebar->isAuthorized($adminID, 9, 4)) { ?>

                                            <th class="py-3 px-4 border-b text-center">Thao tác</th>
                                        <?php } ?>
                                    </tr>

                                </thead>
                                <tbody>
                                    <?php if (count($reviews) > 0): ?>
                                        <?php foreach ($reviews as $review): ?>
                                            <?php
                                            $book = $bookTable->getBookById($review['bookId']);
                                            $user = $userTable->getUserDetailsById($review['userId']);
                                            ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="py-3 px-4 border-b"><?php echo $review['id']; ?></td>
                                                <td class="py-3 px-4 border-b">
                                                    <div class="flex items-center">
                                                        <img src="<?php echo htmlspecialchars($book['imageURL']); ?>" alt="<?php echo htmlspecialchars($book['bookName']); ?>"
                                                            class="w-10 h-10 object-cover mr-2">
                                                        <div>
                                                            <a href="../book/index.php?bookId=<?php echo $book['id']; ?>" target="_blank"
                                                                class="text-blue-600 hover:underline">
                                                                <?php echo htmlspecialchars($book['bookName']); ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 border-b">
                                                    <?php echo htmlspecialchars($user['userName']); ?><br>
                                                    <span class="text-xs text-gray-500"><?php echo htmlspecialchars($user['email']); ?></span>
                                                </td>
                                                <td class="py-3 px-4 border-b">
                                                    <div class="flex">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <?php if ($i <= $review['rating']): ?>
                                                                <i class="fas fa-star text-yellow-500"></i>
                                                            <?php else: ?>
                                                                <i class="far fa-star text-yellow-500"></i>
                                                            <?php endif; ?>
                                                        <?php endfor; ?>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 border-b">
                                                    <div class="max-w-xs overflow-hidden">
                                                        <div class="line-clamp-2"><?php echo htmlspecialchars($review['review']); ?></div>
                                                        <?php if (strlen($review['review']) > 100): ?>
                                                            <button class="text-blue-600 text-xs hover:underline view-more"
                                                                data-review="<?php echo htmlspecialchars($review['review']); ?>">
                                                                Xem thêm
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 border-b">
                                                    <?php echo date('d/m/Y', strtotime($review['create_at'])); ?>
                                                </td>
                                                <?php if ($roleTableSidebar->isAuthorized($adminID, 9, 4)) { ?>

                                                    <td class="py-3 px-4 border-b">
                                                        <div class="flex justify-center gap-2">

                                                            <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này không?');">
                                                                <input type="hidden" name="reviewId" value="<?php echo $review['id']; ?>">
                                                                <button type="submit" name="action" value="delete"
                                                                    class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="py-4 text-center text-gray-500">
                                                Không tìm thấy đánh giá nào.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="flex justify-center mt-6">
                                <div class="flex border border-gray-300 rounded-md overflow-hidden">
                                    <!-- Previous page button -->
                                    <?php if ($page > 1): ?>
                                        <a href="?search=<?php echo urlencode($search); ?>&rating=<?php echo $ratingFilter; ?>&page=<?php echo $page - 1; ?>"
                                            class="px-3 py-1 border-r border-gray-300 bg-white hover:bg-gray-100">
                                            &laquo;
                                        </a>
                                    <?php else: ?>
                                        <span class="px-3 py-1 border-r border-gray-300 bg-gray-100 text-gray-400">&laquo;</span>
                                    <?php endif; ?>

                                    <!-- Page numbers -->
                                    <?php
                                    $startPage = max(1, min($page - 2, $totalPages - 4));
                                    $endPage = min($totalPages, max(5, $page + 2));

                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <a href="?search=<?php echo urlencode($search); ?>&rating=<?php echo $ratingFilter; ?>&page=<?php echo $i; ?>"
                                            class="px-3 py-1 border-r border-gray-300 <?php echo $i == $page ? 'bg-blue-500 text-white' : 'bg-white hover:bg-gray-100'; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>

                                    <!-- Next page button -->
                                    <?php if ($page < $totalPages): ?>
                                        <a href="?search=<?php echo urlencode($search); ?>&rating=<?php echo $ratingFilter; ?>&page=<?php echo $page + 1; ?>"
                                            class="px-3 py-1 bg-white hover:bg-gray-100">
                                            &raquo;
                                        </a>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-gray-100 text-gray-400">&raquo;</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Review Detail Modal -->
    <div id="reviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full mx-4">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold">Chi tiết đánh giá</h3>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-4">
                <p id="reviewContent" class="text-gray-700"></p>
            </div>
            <div class="p-4 border-t border-gray-200 flex justify-end">
                <button id="closeModalBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View more functionality for review text
            const viewMoreButtons = document.querySelectorAll('.view-more');
            const reviewModal = document.getElementById('reviewModal');
            const reviewContent = document.getElementById('reviewContent');
            const closeModal = document.getElementById('closeModal');
            const closeModalBtn = document.getElementById('closeModalBtn');

            viewMoreButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const review = this.getAttribute('data-review');
                    reviewContent.textContent = review;
                    reviewModal.classList.remove('hidden');
                });
            });

            [closeModal, closeModalBtn].forEach(elem => {
                elem.addEventListener('click', function() {
                    reviewModal.classList.add('hidden');
                });
            });

            // Close modal on outside click
            reviewModal.addEventListener('click', function(e) {
                if (e.target === reviewModal) {
                    reviewModal.classList.add('hidden');
                }
            });
        });
    </script>
</body>

</html>