<?php

require_once("./database/database.php");
$conn = mysqli_connect("localhost", "root", "", "ltw_ud2");

if (mysqli_connect_errno()) {
    echo '<div class="p-4 mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-md shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                <span>Kết nối cơ sở dữ liệu thất bại: ' . mysqli_connect_error() . '</span>
            </div>
          </div>';
    exit();
}

if (!isset($_GET['Class']) || !is_numeric($_GET['Class'])) {
    echo '<div class="p-4 mb-4 bg-amber-50 border-l-4 border-amber-500 text-amber-700 rounded-md shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                <span>Vui lòng chọn lớp học hợp lệ</span>
            </div>
          </div>';
    exit();
}

$class = intval($_GET['Class']);

$subjects_query = "SELECT DISTINCT s.id, s.subjectName
                  FROM books b
                  JOIN subjects s ON b.subjectId = s.id
                  WHERE b.classNumber = ?
                  ORDER BY s.subjectName";

$stmt = $conn->prepare($subjects_query);
$stmt->bind_param("i", $class);
$stmt->execute();
$subject_results = $stmt->get_result();

if ($subject_results->num_rows > 0) {
?>
<div class="subject-menu space-y-6 animate-fadeIn">
    <div class="flex items-center justify-between mb-6 border-b border-gray-200 pb-3">
        <h2 class="flex items-center text-2xl font-bold text-gray-800">
            <span class="inline-block text-center pt-[6px] w-8 h-8 flex items-center justify-center bg-pink-600 text-white rounded-full mr-3 text-sm">
                <?= $class ?>
            </span>
            Tài liệu lớp <?= $class ?>
        </h2>
        <span class="text-sm text-gray-500 bg-gray-100 py-1 px-3 rounded-full">
            <?= $subject_results->num_rows ?> môn học
        </span>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <?php while ($subject = $subject_results->fetch_assoc()): ?>
        <div class="subject-group bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 hover:border-pink-100">
            <div class="flex items-center mb-3 pb-2 border-b border-gray-50">
                <div class="text-pink-500 mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <a href="/LTW-UD2/searchPage.php?subject=<?= $subject["id"] ?>" 
                   class="text-pink-600 font-semibold text-lg hover:text-pink-800 transition-colors group">
                    <?= htmlspecialchars($subject["subjectName"]) ?>
                    <span class="inline-block transition-transform duration-300 group-hover:translate-x-1 ml-1">→</span>
                </a>
            </div>
            
            <div class="pl-3 space-y-2 mt-3">
                <?php
                $types_query = "SELECT DISTINCT type, COUNT(*) as book_count 
                              FROM books 
                              WHERE subjectId = ? AND classNumber = ?
                              GROUP BY type 
                              ORDER BY type";
                              
                $type_stmt = $conn->prepare($types_query);
                $type_stmt->bind_param("ii", $subject["id"], $class);
                $type_stmt->execute();
                $type_results = $type_stmt->get_result();
                
                if ($type_results->num_rows > 0):
                    while ($type = $type_results->fetch_assoc()):
                ?>
                    <div class="type-item group">
                        <a href="/LTW-UD2/searchPage.php?subject=<?= $subject["id"] ?>&type=<?= urlencode($type["type"]) ?>&class=<?= urlencode($class) ?>"
                           class="flex items-center justify-between text-blue-600 hover:text-blue-800 py-1 px-2 hover:bg-blue-50 rounded-md transition-colors">
                            <div class="flex items-center">
                                <span class="text-blue-400 mr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                                <span><?= htmlspecialchars($type["type"]) ?></span>
                            </div>
                            <span class="text-xs bg-gray-100 text-gray-600 py-1 px-2 rounded-full">
                                <?= $type["book_count"] ?>
                            </span>
                        </a>
                    </div>
                <?php 
                    endwhile;
                else: 
                ?>
                    <div class="text-gray-500 text-sm italic py-2 px-3 bg-gray-50 rounded-md flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Không có loại sách
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.4s ease-out;
    }
    .subject-group:hover {
        transform: translateY(-3px);
    }
</style>
<?php
} else {
    echo '<div class="p-6 text-gray-600 bg-gray-50 rounded-xl border border-gray-200 flex flex-col items-center justify-center space-y-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>Không tìm thấy tài liệu cho lớp ' . $class . '</p>
            <a href="/LTW-UD2/" class="text-blue-600 hover:text-blue-800 font-medium flex items-center mt-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Quay lại trang chủ
            </a>
          </div>';
}

$stmt->close();
if (isset($type_stmt)) {
    $type_stmt->close();
}
$conn->close();
?>