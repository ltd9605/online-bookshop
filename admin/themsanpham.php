<?php
session_start();
require_once("../database/database.php");
require_once("../database/book.php");
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

$bookTypes = [
    'Giáo Khoa Cơ Bản',
    'Bài Tập',
    'Giáo Khoa Nâng Cao',
    'Ôn Thi'
];

$bookTable = new BooksTable();
$subjects = $bookTable->getAllSubject();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $imageTmpName = $_FILES['imageFile']['tmp_name'];
        $imageName = uniqid() . '_' . basename($_FILES['imageFile']['name']);
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/LTW-UD2/images/Products/';
        $imagePath = $uploadDir . $imageName;

        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($imageTmpName, $imagePath)) {
            throw new Exception('Lỗi khi tải ảnh lên');
        }

        $dbImagePath = '/LTW-UD2/images/Products/' . $imageName;

        // Handle additional fields
        $name = $_POST['name'];
        $subject = $_POST['subject'];
        $class = $_POST['class'];
        $description = $_POST['description'];
        $originalPrice = isset($_POST['originalPrice']) ? $_POST['originalPrice'] : 0;
        $currentPrice = isset($_POST['currentPrice']) ? $_POST['currentPrice'] : 0;
        $bookType = isset($_POST['bookType']) ? $_POST['bookType'] : 'Giáo Khoa Cơ Bản';
        $publishNow = isset($_POST['isActive']) && $_POST['isActive'] == '1' ? 1 : 0;

        // Call addBook method with available parameters
        $result = $bookTable->addBook(
            $name,
            $subject,
            $class,
            $dbImagePath,
            $description,
            $originalPrice,
            $currentPrice,
            $bookType,
            $publishNow
        );

        if (!$result) {
            throw new Exception('Lỗi khi thêm vào database');
        }

        // Return success response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Sách đã được thêm thành công']);
        exit;
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sách Giáo Khoa Mới</title>
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
                            <i class="fas fa-plus-circle mr-2 text-blue-600"></i>
                            Thêm Sách Giáo Khoa Mới
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Điền đầy đủ thông tin để thêm sách mới vào hệ thống
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <a href="sanphan.php" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Quay lại danh sách
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-4 bg-gray-50 border-b">
                    <h2 class="text-lg font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-book mr-2 text-blue-600"></i>
                        Thông tin sách
                    </h2>
                </div>

                <div class="p-6">
                    <form id="addProductForm" enctype="multipart/form-data" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Image Upload Section -->
                            <div class="col-span-1">
                                <div class="flex flex-col items-center space-y-4">
                                    <div class="w-full aspect-square bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center overflow-hidden relative">
                                        <img id="previewImg" src="" alt="Preview" class="max-h-full max-w-full object-contain absolute inset-0 m-auto hidden">
                                        <div id="uploadPlaceholder" class="flex flex-col items-center justify-center p-6 text-center">
                                            <i class="fas fa-cloud-upload-alt text-gray-400 text-4xl mb-2"></i>
                                            <p class="text-gray-500 font-medium">Kéo thả ảnh hoặc nhấn để chọn</p>
                                            <p class="text-gray-400 text-sm">PNG, JPG up to 5MB</p>
                                        </div>
                                    </div>

                                    <label for="imageFile" class="cursor-pointer py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 w-full text-center transition-colors">
                                        <i class="fas fa-image mr-2"></i>
                                        Chọn ảnh sách
                                    </label>
                                    <input type="file" id="imageFile" name="imageFile" accept="image/*" class="sr-only">

                                    <p id="fileNameDisplay" class="text-sm text-gray-500 hidden"></p>
                                </div>
                            </div>

                            <!-- Book Details Section -->
                            <div class="col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Basic Information -->
                                <div class="md:col-span-2">
                                    <label for="book-name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Tên sách <span class="text-red-600">*</span>
                                    </label>
                                    <input type="text" id="book-name" name="name" placeholder="Nhập tên sách"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 px-3 border">
                                </div>

                                <div>
                                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                                        Môn học <span class="text-red-600">*</span>
                                    </label>
                                    <select id="subject" name="subject"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 px-3 border">
                                        <option value="">-- Chọn môn học --</option>
                                        <?php foreach ($subjects as $sub): ?>
                                            <option value="<?php echo $sub['id']; ?>"><?php echo $sub['subjectName']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label for="class" class="block text-sm font-medium text-gray-700 mb-1">
                                        Lớp <span class="text-red-600">*</span>
                                    </label>
                                    <select id="class" name="class"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 px-3 border">
                                        <option value="">-- Chọn lớp --</option>
                                        <?php for ($i = 6; $i <= 12; $i++): ?>
                                            <option value="<?php echo $i; ?>">Lớp <?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <!-- Price Information -->
                                

                                <div>
                                    <label for="currentPrice" class="block text-sm font-medium text-gray-700 mb-1">
                                        Giá gốc <span class="text-red-600">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" id="currentPrice" name="currentPrice" min="0" step="1000" placeholder="VD: 18000"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 pl-3 pr-10 border">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span class="text-gray-500">VNĐ</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="originalPrice" class="block text-sm font-medium text-gray-700 mb-1">
                                        Giá bán <span class="text-red-600">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" id="originalPrice" name="originalPrice" min="0" step="1000" placeholder="VD: 20000"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 pl-3 pr-10 border">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span class="text-gray-500">VNĐ</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="bookType" class="block text-sm font-medium text-gray-700 mb-1">
                                        Loại sách <span class="text-red-600">*</span>
                                    </label>
                                    <select id="bookType" name="bookType"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 px-3 border">
                                        <?php foreach ($bookTypes as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                        Mô tả sách <span class="text-red-600">*</span>
                                    </label>
                                    <textarea id="description" name="description" rows="4" placeholder="Mô tả chi tiết về sách..."
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 px-3 border"></textarea>
                                </div>

                                <!-- Publish settings -->
                                <!-- <div class="md:col-span-2 flex items-center space-x-2">
                                    <input type="checkbox" id="publishNow" name="publishNow" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <label for="publishNow" class="text-sm font-medium text-gray-700">
                                        Đăng bán ngay sau khi tạo
                                    </label>
                                </div> -->
                            </div>
                        </div>

                        <div class="border-t pt-5 flex justify-end gap-3">
                            <button type="button" id="resetForm" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-undo mr-2"></i>
                                Xóa form
                            </button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i>
                                Thêm sách
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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

        // Image preview functionality
        $(document).ready(function() {
            // Image upload and preview
            $('#imageFile').change(function(e) {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#previewImg').attr('src', e.target.result).removeClass('hidden');
                        $('#uploadPlaceholder').addClass('hidden');
                        $('#fileNameDisplay').text(file.name).removeClass('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Handle drag and drop for image upload
            const dropArea = document.querySelector('.border-dashed');

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                dropArea.classList.add('border-blue-500', 'bg-blue-50');
            }

            function unhighlight() {
                dropArea.classList.remove('border-blue-500', 'bg-blue-50');
            }

            dropArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const file = dt.files[0];

                if (file && file.type.startsWith('image/')) {
                    const fileInput = document.getElementById('imageFile');
                    fileInput.files = dt.files;

                    // Trigger change event manually
                    const event = new Event('change', {
                        bubbles: true
                    });
                    fileInput.dispatchEvent(event);
                }
            }

            // Reset form button
            $('#resetForm').click(function() {
                $('#addProductForm')[0].reset();
                $('#previewImg').addClass('hidden').attr('src', '');
                $('#uploadPlaceholder').removeClass('hidden');
                $('#fileNameDisplay').addClass('hidden').text('');

                // Clear any validation styling
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            });

            // Price auto-calculation suggestion
            $('#originalPrice').on('input', function() {
                if (!$('#currentPrice').val()) {
                    const suggestedPrice = Math.round(parseFloat($(this).val()) * 0.9 / 1000) * 1000;
                    if (!isNaN(suggestedPrice) && suggestedPrice > 0) {
                        $('#currentPrice').val(suggestedPrice);
                    }
                }
            });

            // Form validation and submission
            $('#addProductForm').submit(function(e) {
                e.preventDefault();

                // Get form values
                const name = $('#book-name').val().trim();
                const subject = $('#subject').val();
                const classVal = $('#class').val();
                const description = $('#description').val().trim();
                const imageFile = $('#imageFile')[0].files[0];
                const originalPrice = $('#originalPrice').val().trim();
                const currentPrice = $('#currentPrice').val().trim();
                const bookType = $('#bookType').val();
                
                // Clear previous validation
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // Validation
                let hasError = false;

                // Required fields validation
                if (!name) {
                    markInvalid('#book-name', 'Vui lòng nhập tên sách');
                    hasError = true;
                }

                if (!subject) {
                    markInvalid('#subject', 'Vui lòng chọn môn học');
                    hasError = true;
                }

                if (!classVal) {
                    markInvalid('#class', 'Vui lòng chọn lớp');
                    hasError = true;
                }

                if (!description) {
                    markInvalid('#description', 'Vui lòng nhập mô tả sách');
                    hasError = true;
                }

                if (!originalPrice) {
                    markInvalid('#originalPrice', 'Vui lòng nhập giá gốc');
                    hasError = true;
                }

                if (!currentPrice) {
                    markInvalid('#currentPrice', 'Vui lòng nhập giá bán');
                    hasError = true;
                }

                if (!imageFile) {
                    showError('Vui lòng chọn ảnh sách');
                    hasError = true;
                }

                // Additional validations
                if (parseFloat(currentPrice) > parseFloat(originalPrice)) {
                    markInvalid('#currentPrice', 'Giá bán phải cao hơn giá gốc');
                    hasError = true;
                }

                function markInvalid(selector, message) {
                    $(selector).addClass('is-invalid border-red-500');
                    $(selector).after(`<div class="invalid-feedback text-sm text-red-600 mt-1">${message}</div>`);
                }

                function showError(message) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: message,
                        icon: 'error'
                    });
                }

                if (hasError) {
                    // Scroll to first error
                    const firstError = $('.is-invalid').first();
                    if (firstError.length) {
                        $('html, body').animate({
                            scrollTop: firstError.offset().top - 100
                        }, 500);
                    }
                    return;
                }

                // If validation passes, confirm and submit
                Swal.fire({
                    title: 'Xác nhận thêm sách?',
                    text: "Bạn có chắc muốn thêm sách mới?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData(this);
                        
                        // Add the book type to formData (moved here where formData is defined)
                        formData.append('bookType', bookType);
                        
                        // Add publish now value
                        const publishNow = $('#publishNow').is(':checked');
                        formData.append('isActive', publishNow ? '1' : '0');

                        $.ajax({
                            url: window.location.href,
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Thành công!',
                                        text: response.message || 'Đã thêm sách thành công',
                                        icon: 'success',
                                        showCancelButton: true,
                                        confirmButtonColor: '#3085d6',
                                        cancelButtonColor: '#2563eb',
                                        confirmButtonText: 'Thêm sách khác',
                                        cancelButtonText: 'Về trang danh sách'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // Reset form
                                            $('#resetForm').click();
                                        } else {
                                            // Redirect to book list page
                                            window.location.href = 'sanphan.php';
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Lỗi!',
                                        text: response.message || 'Có lỗi xảy ra khi thêm sách',
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function(xhr) {
                                let errorMessage = 'Có lỗi xảy ra';

                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.message) {
                                        errorMessage = response.message;
                                    }
                                } catch (e) {
                                    errorMessage = xhr.responseText || errorMessage;
                                }

                                Swal.fire({
                                    title: 'Lỗi!',
                                    text: errorMessage,
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>

    <style>
        /* Dynamic form styles */
        .is-invalid {
            border-color: #ef4444 !important;
        }

        /* For TailwindCSS styles that might need additional customization */
        @media (max-width: 640px) {
            .grid-cols-1 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }
    </style>
</body>

</html>