<?php
session_start();
require_once("../database/database.php");
require_once("../database/book.php");
require_once("../database/user.php");

$bookTable = new BooksTable();
$conn = new mysqli("localhost", "root", "", "ltw_ud2");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize session for storing temporary import list
if (!isset($_SESSION['import_list'])) {
    $_SESSION['import_list'] = [];
}

$books = $bookTable->getAllBook();

$suppliers_query = "SELECT id, name FROM nhacungcap";
$suppliers_result = $conn->query($suppliers_query);
$suppliers = $suppliers_result->fetch_all(MYSQLI_ASSOC);

// Get some statistics for the dashboard
$total_books = count($books);
$total_import_value = array_sum(array_column($_SESSION['import_list'], 'total'));
$total_import_items = array_sum(array_column($_SESSION['import_list'], 'quantity'));
$import_count = count($_SESSION['import_list']);

// Handle form submission for adding to import list
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_list'])) {
    $book_id = intval($_POST['book_id']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $image = $_FILES['image']['name'] ?? '';

    // Find selected book
    $selected_book = null;
    foreach ($books as $book) {
        if ($book['id'] == $book_id) {
            $selected_book = $book;
            break;
        }
    }

    // Validate input
    if (!$selected_book) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Vui lòng chọn sách!'
            });
        </script>";
    } elseif ($price <= 0) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Giá nhập phải lớn hơn 0!'
            });
        </script>";
    } elseif ($quantity <= 0) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Số lượng phải lớn hơn 0!'
            });
        </script>";
    } else {
        // Handle image upload
        if (!empty($image)) {
            $target_dir = "../images/Products/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($image);
            move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
            $image_path = "/LTW-UD2/images/Products/" . basename($image);
        } else {
            $image_path = $selected_book['imageURL'];
        }

        // Calculate total
        $total = $price * $quantity;

        // Add to session import list
        $_SESSION['import_list'][] = [
            'book_id' => $book_id,
            'book_name' => $selected_book['bookName'],
            'class_number' => $selected_book['classNumber'],
            'price' => $price,
            'quantity' => $quantity,
            'total' => $total,
            'image' => $image_path
        ];

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Thành công',
                text: 'Thêm sách vào danh sách thành công!',
                timer: 1500,
                showConfirmButton: false
            });
        </script>";

        // Refresh the statistics
        $total_import_value = array_sum(array_column($_SESSION['import_list'], 'total'));
        $total_import_items = array_sum(array_column($_SESSION['import_list'], 'quantity'));
        $import_count = count($_SESSION['import_list']);
    }
}

// Handle form submission for final import
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['import'])) {
    $import_date = $_POST['import_date'];
    $supplier_id = intval($_POST['supplier']);
    $idNguoiNhap = isset($_SESSION["user"]) ? $_SESSION["user"] : 1;
    $total = array_sum(array_column($_SESSION['import_list'], 'total'));

    if (empty($supplier_id)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Vui lòng chọn nhà cung cấp!'
            });
        </script>";
    } elseif (empty($_SESSION['import_list'])) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Danh sách nhập đang rỗng!'
            });
        </script>";
    } else {
        // Insert into hoadonnhap table
        $sql = "INSERT INTO hoadonnhap (tongtien, idNguoiNhap, date, status) VALUES (?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dis", $total, $idNguoiNhap, $import_date);
        $stmt->execute();

        $import_id = $conn->insert_id;

        // Insert into chitietphieunhap table
        foreach ($_SESSION['import_list'] as $item) {
            $sql = "INSERT INTO chitietphieunhap (idPhieuNhap, idBook, idCungCap, soluong, gianhap) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiidd", $import_id, $item['book_id'], $supplier_id, $item['quantity'], $item['price']);
            $stmt->execute();

            // Update books quantity
            $sql = "UPDATE books SET quantitySold = quantitySold + ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $item['quantity'], $item['book_id']);
            $stmt->execute();
        }

        // Clear import list after successful import
        $_SESSION['import_list'] = [];

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Thành công',
                text: 'Nhập hàng thành công!',
                timer: 1500,
                showConfirmButton: false
            }).then(function() {
                window.location.href = 'nhapSanPham.php';
            });
        </script>";
    }
}

// Handle item deletion from import list
if (isset($_GET['delete'])) {
    $index = intval($_GET['delete']);
    if (isset($_SESSION['import_list'][$index])) {
        unset($_SESSION['import_list'][$index]);
        $_SESSION['import_list'] = array_values($_SESSION['import_list']);
        header("Location: nhapSanPham.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhập hàng</title>
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
                            <i class="fas fa-boxes mr-2 text-blue-600"></i>
                            Quản Lý Nhập Hàng
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Thêm sản phẩm vào kho, cập nhật số lượng và giá nhập
                        </p>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total Books Card -->
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Tổng số sách</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_books; ?></h3>
                        </div>
                        <div class="rounded-full bg-blue-100 p-3">
                            <i class="fas fa-book text-blue-500 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Items in Import List -->
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Sản phẩm trong danh sách</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo $import_count; ?></h3>
                        </div>
                        <div class="rounded-full bg-green-100 p-3">
                            <i class="fas fa-list text-green-500 text-xl"></i>
                        </div>
                    </div>
                </div>

             
                <!-- Total Quantity -->
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Tổng số lượng nhập</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_import_items; ?></h3>
                        </div>
                        <div class="rounded-full bg-yellow-100 p-3">
                            <i class="fas fa-boxes-stacked text-yellow-500 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Tổng giá trị</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($total_import_value, 0, ',', '.'); ?>đ</h3>
                        </div>
                        <div class="rounded-full bg-purple-100 p-3">
                            <i class="fas fa-money-bill-wave text-purple-500 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b flex items-center">
                            <i class="fas fa-plus-circle text-blue-500 mr-2"></i>
                            <h2 class="text-lg font-semibold text-gray-700">Thêm sách vào danh sách</h2>
                        </div>

                        <div class="p-5">
                            <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                                <div class="form-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên sách</label>
                                    <select name="book_id" id="book-select" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                                        <option value="">-- Chọn sách --</option>
                                        <?php foreach ($books as $book): ?>
                                            <option value="<?php echo $book['id']; ?>" data-class="<?php echo $book['classNumber']; ?>" data-price="<?php echo $book['currentPrice']; ?>" data-stock="<?php echo $book['quantitySold']; ?>">
                                                <?php echo htmlspecialchars($book['bookName']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="form-group">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Lớp</label>
                                        <input type="text" id="class-display" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm outline-transparent sm:text-sm" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tồn kho</label>
                                        <input type="text" id="stock-display" class="mt-1 block w-full outline-transparent rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm" disabled>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="form-group">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Giá nhập</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" name="price" id="price-input" placeholder="Giá nhập" step="1000" min="0" class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 outline-transparent sm:text-sm pr-10" required>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">đ</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                                        <input type="number" name="quantity" placeholder="Số lượng nhập" min="1" value="1" class="mt-1 block w-full outline-transparent rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                                    </div>
                                </div>



                                <div class="mt-4 flex justify-end">
                                    <button type="submit" name="add_to_list" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-plus mr-2"></i>
                                        Thêm vào danh sách
                                    </button>
                                </div>
                            </form>

                            <div class="mt-6 border-t pt-6">
                                <form method="POST" action="" class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">

                                        <div class="form-group">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày nhập</label>
                                            <input type="date" name="import_date" value="<?php echo date('Y-m-d'); ?>" class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 outline-transparent sm:text-sm" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nhà cung cấp</label>
                                        <select name="supplier" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                                            <option value="">-- Chọn nhà cung cấp --</option>
                                            <?php foreach ($suppliers as $supplier): ?>
                                                <option value="<?php echo $supplier['id']; ?>">
                                                    <?php echo htmlspecialchars($supplier['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tổng giá trị</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="text" value="<?php echo number_format($total_import_value, 0, ',', '.'); ?>đ" class="mt-1 outline-transparent block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm" disabled>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" name="import" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500" <?php echo empty($_SESSION['import_list']) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-save mr-2"></i>
                                            Hoàn tất nhập hàng
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Import List -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b flex justify-between items-center">
                            <div class="flex items-center">
                                <i class="fas fa-clipboard-list text-blue-500 mr-2"></i>
                                <h2 class="text-lg font-semibold text-gray-700">Danh sách sách nhập</h2>
                            </div>
                            <span class="text-sm text-gray-500">
                                Tổng: <span class="font-semibold"><?php echo count($_SESSION['import_list']); ?></span> mặt hàng
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hình ảnh</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên sách</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lớp</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá nhập</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng tiền</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (empty($_SESSION['import_list'])): ?>
                                        <tr>
                                            <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                                <div class="flex flex-col items-center">
                                                    <i class="fas fa-inbox text-gray-300 text-5xl mb-3"></i>
                                                    Chưa có mặt hàng nào trong danh sách nhập
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($_SESSION['import_list'] as $index => $item): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" class="h-12 w-12 rounded-md object-cover" alt="Book Image">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($item['book_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    Lớp <?php echo htmlspecialchars($item['class_number']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo number_format($item['price'], 0, ',', '.'); ?>đ
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        <?php echo $item['quantity']; ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo number_format($item['total'], 0, ',', '.'); ?>đ
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="?delete=<?php echo $index; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Bạn có chắc muốn xóa mặt hàng này?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($_SESSION['import_list'])): ?>
                            <div class="bg-gray-50 px-6 py-3 flex justify-between items-center border-t">
                                <span class="text-sm text-gray-500">
                                    Tổng số lượng: <span class="font-semibold"><?php echo $total_import_items; ?></span>
                                </span>
                                <span class="text-sm font-medium text-gray-900">
                                    Tổng giá trị: <span class="font-semibold"><?php echo number_format($total_import_value, 0, ',', '.'); ?>đ</span>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
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

        // Book select change handler
        document.getElementById('book-select').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById('class-display').value = selectedOption.dataset.class ? 'Lớp ' + selectedOption.dataset.class : '';
            document.getElementById('stock-display').value = selectedOption.dataset.stock || '0';

            // Suggest import price (80% of current price)
            const currentPrice = parseFloat(selectedOption.dataset.price);
            if (!isNaN(currentPrice)) {
                const suggestedPrice = Math.round(currentPrice * 0.8 / 1000) * 1000;
                document.getElementById('price-input').value = suggestedPrice;
            }
        });
    </script>
</body>

</html>