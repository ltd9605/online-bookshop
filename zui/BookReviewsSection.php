<?php
// Check if session is not already started before starting it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../database/review.php";
require_once "../database/user.php";

// Get bookId from URL parameter
$bookId = isset($_GET['bookId']) ? intval($_GET['bookId']) : 0;
if (!$bookId) {
    // Fallback or error handling if no bookId provided
    echo "No book selected";
    exit;
}

// Initialize review table class
$reviewTable = new ReviewTable();

// Handle review submission - MOVED TO TOP BEFORE ANY OUTPUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_id'] != null) {
    $userId = $_SESSION['user_id'];
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    // Check if user already has review
    $existingReview = $reviewTable->checkUserReview($userId, $bookId);
    $hasExistingReview = $existingReview ? true : false;

    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        if ($hasExistingReview) {
            // Update existing review
            $reviewTable->updateReview($existingReview['id'], $rating, $comment);
        } else {
            // Add new review
            $reviewTable->addReview($bookId, $userId, $rating, $comment);
        }

        // Use JavaScript redirect instead of header() to avoid "headers already sent" error
        echo "<script>window.location.href = '" . htmlspecialchars($_SERVER['REQUEST_URI']) . "';</script>";
        exit;
    }
}

// Get book reviews data
$reviews = $reviewTable->getreviewByBookId($bookId);
$reviewCount = count($reviews);
$avgRating = $reviewTable->getAverageRatingByBookId($bookId);
$ratingDistribution = $reviewTable->getRatingDistributionByBookId($bookId);

// Process rating distribution for percentage calculation
$ratingPercentages = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
if ($reviewCount > 0) {
    foreach ($ratingDistribution as $rating) {
        $ratingPercentages[$rating['rating']] = round(($rating['count'] / $reviewCount) * 100);
    }
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['user_id'] != null;
$userId = $isLoggedIn ? $_SESSION['user_id'] : 0;

// Check if user has already reviewed this book
$userReview = $isLoggedIn ? $reviewTable->checkUserReview($userId, $bookId) : null;
$hasReviewed = $userReview ? true : false;

// Get the correct review text field based on database structure
$reviewText = '';
if ($hasReviewed && isset($userReview['review'])) {
    $reviewText = $userReview['review'];
} elseif ($hasReviewed && isset($userReview['comment'])) {
    $reviewText = $userReview['comment'];
}
?>


<section class="bg-white rounded-lg shadow-md p-4 sm:p-6 w-full">
    <h2 class="text-xl sm:text-2xl font-bold mb-4">Đánh giá sản phẩm</h2>

    <!-- Rating Summary Section -->
    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
        <!-- Overall Rating -->
        <div class="flex flex-col items-center">
            <div class="text-3xl"><span class="text-5xl sm:text-6xl font-bold"><?php echo $avgRating; ?></span>/5</div>
            <div class="flex flex-row my-2">
                <?php
                // Display stars based on average rating
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= round($avgRating)) {
                        echo '<img src="../images/yellow-star.png" class="w-4 h-4 sm:w-5 sm:h-5" alt="Star">';
                    } else {
                        echo '<img src="../images/grey-star.png" class="w-4 h-4 sm:w-5 sm:h-5" alt="Empty Star">';
                    }
                }
                ?>
            </div>
            <p class="text-gray-500">(<?php echo $reviewCount; ?> đánh giá)</p>
        </div>

        <!-- Rating Distribution -->
        <div class="flex flex-col w-full md:w-auto max-w-sm">
            <?php for ($star = 5; $star >= 1; $star--) {
                $percentage = $ratingPercentages[$star];
            ?>
                <div class="flex flex-row items-center gap-2 sm:gap-4 mb-1">
                    <span class="text-sm sm:text-base whitespace-nowrap w-8"><?php echo $star; ?> sao</span>
                    <div class="w-[150px] sm:w-[200px] relative h-2">
                        <div class="absolute left-0 w-full h-full bg-[#E3E5E5] rounded-full"></div>
                        <div class="absolute left-0 h-full bg-[#F6A500] rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                    <span class="text-xs sm:text-sm w-8 text-right"><?php echo $percentage; ?>%</span>
                </div>
            <?php } ?>
        </div>

        <!-- Review CTA -->
        <div class="flex flex-row items-center flex-1 justify-center mt-4 md:mt-0">
            <?php if (!$isLoggedIn): ?>
                <p class="text-sm sm:text-base text-center">

                    Chỉ có thành viên mới có thể viết nhận xét. Vui lòng
                    <a class="text-blue-500 hover:underline" href="../login.php">đăng nhập</a> hoặc
                    <a class="text-blue-500 hover:underline" href="../login.php">đăng ký</a>.
                </p>
            <?php elseif ($hasReviewed): ?>
                <p class="text-sm sm:text-base text-center">Bạn đã đánh giá sản phẩm này.
                    <button id="editReviewBtn" class="text-blue-500 hover:underline">Sửa đánh giá</button>
                </p>
            <?php else: ?>
                <button id="writeReviewBtn" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded transition">
                    Viết đánh giá
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Review Form (hidden by default) -->
    <?php if ($isLoggedIn): ?>
        <div id="reviewFormContainer" class="hidden mt-6 p-4 border border-gray-200 rounded-lg">
            <form method="POST" action="">
                <h3 class="text-lg font-medium mb-2">
                    <?php echo $hasReviewed ? 'Sửa đánh giá của bạn' : 'Đánh giá mới'; ?>
                </h3>

                <div class="mb-4">
                    <label class="block mb-1">Đánh giá của bạn:</label>
                    <div class="flex items-center space-x-1" id="ratingStars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="cursor-pointer text-2xl" data-rating="<?php echo $i; ?>">☆</span>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="<?php echo $hasReviewed ? $userReview['rating'] : '5'; ?>">
                </div>

                <div class="mb-4">



                    <label for="comment" class="block mb-1">Nhận xét của bạn:</label>
                    <textarea name="comment" id="comment" rows="4" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-blue-400"><?php echo htmlspecialchars($reviewText); ?></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="button" id="cancelReviewBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded mr-2">
                        Hủy
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded">
                        <?php echo $hasReviewed ? 'Cập nhật' : 'Gửi đánh giá'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="w-full h-[1px] bg-gray-200 my-6"></div>

    <!-- Review List -->
    <div class="flex flex-col space-y-6">
        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="flex flex-col sm:flex-row pb-4 border-b border-gray-100">
                    <div class="flex flex-col sm:w-48 mb-2 sm:mb-0">
                        <p class="font-medium"><?php echo htmlspecialchars($review['userName']); ?></p>
                        <p class="text-sm text-gray-500">
                            <?php echo date('d/m/Y', strtotime($review['create_at'])); ?>
                        </p>
                    </div>
                    <div class="flex flex-col space-y-1 flex-1">
                        <div class="flex flex-row mb-1">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $review['rating']) {
                                    echo '<img src="../images/yellow-star.png" class="w-3 h-3 sm:w-4 sm:h-4" alt="Star">';
                                } else {
                                    echo '<img src="../images/grey-star.png" class="w-3 h-3 sm:w-4 sm:h-4" alt="Empty Star">';
                                }
                            }
                            ?>
                        </div>
                        <p class="text-sm sm:text-base break-words"><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-8 text-gray-500">
                <p>Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    // Star rating functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Review form toggle
        const writeReviewBtn = document.getElementById('writeReviewBtn');
        const editReviewBtn = document.getElementById('editReviewBtn');
        const cancelReviewBtn = document.getElementById('cancelReviewBtn');
        const reviewFormContainer = document.getElementById('reviewFormContainer');

        if (writeReviewBtn) {
            writeReviewBtn.addEventListener('click', function() {
                reviewFormContainer.classList.remove('hidden');
                writeReviewBtn.classList.add('hidden');
            });
        }

        if (editReviewBtn) {
            editReviewBtn.addEventListener('click', function() {
                reviewFormContainer.classList.remove('hidden');
            });
        }

        if (cancelReviewBtn) {
            cancelReviewBtn.addEventListener('click', function() {
                reviewFormContainer.classList.add('hidden');
                if (writeReviewBtn) writeReviewBtn.classList.remove('hidden');
            });
        }

        // Star rating system
        const stars = document.querySelectorAll('#ratingStars span');
        const ratingInput = document.getElementById('ratingInput');

        // Initialize stars based on input value
        if (stars.length > 0) {
            updateStars(parseInt(ratingInput.value) || 0);

            // Add click events to stars
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = parseInt(this.dataset.rating);
                    ratingInput.value = rating;
                    updateStars(rating);
                });

                // Add hover effects
                star.addEventListener('mouseover', function() {
                    const rating = parseInt(this.dataset.rating);
                    hoverStars(rating);
                });
            });

            // Reset stars on mouse out
            document.getElementById('ratingStars').addEventListener('mouseout', function() {
                updateStars(parseInt(ratingInput.value) || 0);
            });
        }

        function updateStars(count) {
            stars.forEach((star, index) => {
                if (index < count) {
                    star.textContent = '★';
                    star.classList.add('text-yellow-400');
                } else {
                    star.textContent = '☆';
                    star.classList.remove('text-yellow-400');
                }
            });
        }

        function hoverStars(count) {
            stars.forEach((star, index) => {
                if (index < count) {
                    star.textContent = '★';
                    star.classList.add('text-yellow-400');
                } else {
                    star.textContent = '☆';
                    star.classList.remove('text-yellow-400');
                }
            });
        }
    });
</script>