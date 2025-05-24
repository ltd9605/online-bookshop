<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ltw_ud2");
if ($conn->connect_error) {
  die("Kết nối thất bại: " . $conn->connect_error);
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status = isset($_GET['status']) ? (int)$_GET['status'] : 0;
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$limit = 10;
$offset = ($page - 1) * $limit;

$count_params = [];
$count_types = "";

$count_sql = "SELECT COUNT(*) AS total FROM hoadon 
              JOIN users ON hoadon.idUser = users.id
              JOIN thongTinGiaoHang AS gh ON hoadon.id_diachi = gh.id 
              WHERE 1=1";

if (!empty($search)) {
  $count_sql .= " AND users.phoneNumber LIKE ?";
  $count_params[] = "%$search%";
  $count_types .= "s";
}

if ($filter_type === 'verification') {
  $count_sql .= " AND hoadon.statusBill = 1";
} elseif ($filter_type === 'delivering') {
  $count_sql .= " AND hoadon.statusBill = 2";
} elseif ($filter_type === 'completed') {
  $count_sql .= " AND hoadon.statusBill = 3";
} elseif ($filter_type === 'cancelled') {
  $count_sql .= " AND hoadon.statusBill = 4";
} elseif (!empty($status)) {
  $count_sql .= " AND hoadon.statusBill = ?";
  $count_params[] = $status;
  $count_types .= "i";
}

if (isset($_GET['province']) && $_GET['province'] != '') {
  $count_sql .= " AND gh.thanhpho = ?";
  $count_params[] = $_GET['province'];
  $count_types .= "s";
}

if (isset($_GET['district']) && $_GET['district'] != '') {
  $count_sql .= " AND gh.quan = ?";
  $count_params[] = $_GET['district'];
  $count_types .= "s";
}

if (isset($_GET['from_date']) && $_GET['from_date'] != '') {
  $count_sql .= " AND hoadon.create_at >= ?";
  $count_params[] = $_GET['from_date'] . ' 00:00:00';
  $count_types .= "s";
}

if (isset($_GET['to_date']) && $_GET['to_date'] != '') {
  $count_sql .= " AND hoadon.create_at <= ?";
  $count_params[] = $_GET['to_date'] . ' 23:59:59'; // Include the entire day
  $count_types .= "s";
}

if (isset($_GET['madon']) && $_GET['madon'] != '') {
  $count_sql .= " AND hoadon.idBill = ?";
  $count_params[] = $_GET['madon'];
  $count_types .= "i";
}

// Prepare and execute count query
$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
  $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Get counts for each status type for the filter badges
$status_counts = [];
$status_types = [1, 2, 3, 4];
foreach ($status_types as $status_type) {
  $status_count_sql = "SELECT COUNT(*) as count FROM hoadon WHERE statusBill = ?";
  $status_stmt = $conn->prepare($status_count_sql);
  $status_stmt->bind_param('i', $status_type);
  $status_stmt->execute();
  $status_result = $status_stmt->get_result();
  $status_counts[$status_type] = $status_result->fetch_assoc()['count'];
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Quản lý đơn hàng</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#f0f9ff',
              100: '#e0f2fe',
              500: '#3b82f6',
              600: '#2563eb',
              700: '#1d4ed8',
            }
          }
        }
      }
    }
  </script>
  <style>
    @keyframes fade-in {
      from {
        opacity: 0;
        transform: scale(0.95);
      }

      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    .animate-fade-in {
      animation: fade-in 0.3s ease-out forwards;
    }

    .status-badge {
      @apply px-3 py-1 rounded-full text-xs font-medium;
    }

    .status-pending {
      @apply bg-yellow-100 text-yellow-800 border border-yellow-200;
    }

    .status-shipping {
      @apply bg-blue-100 text-blue-800 border border-blue-200;
    }

    .status-completed {
      @apply bg-green-100 text-green-800 border border-green-200;
    }

    .status-cancelled {
      @apply bg-red-100 text-red-800 border border-red-200;
    }

    .btn-filter {
      @apply inline-flex items-center px-3 py-1.5 rounded-lg text-sm transition-colors;
    }

    .btn-filter.active {
      @apply bg-indigo-100 text-indigo-700 font-medium border-indigo-300;
    }

    .quick-action {
      @apply transition-transform hover:scale-110 focus:outline-none;
    }
  </style>
</head>

<body class="bg-gray-50">

  <main class="flex flex-row">
    <?php include_once './gui/sidebar.php' ?>
    <div class="flex items-center w-full h-screen justify-center">
      <div class="bg-white shadow-lg border border-gray-200 rounded-lg overflow-hidden p-6 h-[90%] w-[90%] " style="overflow-y: scroll;">
        <div class="w-full bg-white rounded-2xl">
          <header class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Quản lý đơn hàng</h2>
            <p class="text-gray-600 text-sm">Theo dõi, cập nhật và quản lý tất cả đơn hàng trong hệ thống</p>
          </header>

          <!-- Quick filters and search bar -->
          <div class="flex flex-wrap gap-3 mb-6 items-center justify-between">
            <div class="flex flex-wrap gap-2 overflow-x-auto pb-2">
              <a href="?filter_type=" class="btn-filter <?= empty($filter_type) ? 'active' : 'bg-gray-100 text-gray-700 border border-gray-200' ?>">
                <i class="fas fa-list-ul mr-1.5"></i> Tất cả
                <span class="ml-1 px-1.5 py-0.5 bg-gray-200 text-gray-700 rounded-full text-xs"><?= $total_rows ?></span>
              </a>
              <a href="?filter_type=verification" class="btn-filter <?= $filter_type === 'verification' ? 'active' : 'bg-yellow-50 text-yellow-700 border border-yellow-200' ?>">
                <i class="fas fa-clock mr-1.5"></i> Chờ xác nhận
                <span class="ml-1 px-1.5 py-0.5 bg-yellow-200 text-yellow-800 rounded-full text-xs"><?= $status_counts[1] ?></span>
              </a>
              <a href="?filter_type=delivering" class="btn-filter <?= $filter_type === 'delivering' ? 'active' : 'bg-blue-50 text-blue-700 border border-blue-200' ?>">
                <i class="fas fa-truck mr-1.5"></i> Đang giao
                <span class="ml-1 px-1.5 py-0.5 bg-blue-200 text-blue-800 rounded-full text-xs"><?= $status_counts[2] ?></span>
              </a>
              <a href="?filter_type=completed" class="btn-filter <?= $filter_type === 'completed' ? 'active' : 'bg-green-50 text-green-700 border border-green-200' ?>">
                <i class="fas fa-check-circle mr-1.5"></i> Hoàn thành
                <span class="ml-1 px-1.5 py-0.5 bg-green-200 text-green-800 rounded-full text-xs"><?= $status_counts[3] ?></span>
              </a>
              <a href="?filter_type=cancelled" class="btn-filter <?= $filter_type === 'cancelled' ? 'active' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                <i class="fas fa-times-circle mr-1.5"></i> Đã hủy
                <span class="ml-1 px-1.5 py-0.5 bg-red-200 text-red-800 rounded-full text-xs"><?= $status_counts[4] ?></span>
              </a>
              <button onclick="toggleFilterModal()" class="btn-filter bg-indigo-50 text-indigo-700 border border-indigo-200">
                <i class="fas fa-filter mr-1.5"></i> Lọc nâng cao
              </button>
            </div>

            <form method="GET" action="" class="relative flex items-center min-w-[280px]">
              <input type="hidden" name="filter_type" value="<?= htmlspecialchars($filter_type) ?>">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
              <input
                type="text"
                name="search"
                placeholder="Tìm kiếm theo số điện thoại"
                value="<?= htmlspecialchars($search) ?>"
                class="block w-full pl-10 pr-12 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-300 focus:border-blue-500 text-sm">
              <button type="submit" class="absolute right-2 px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm">Tìm</button>
            </form>
          </div>

          <!-- Orders table -->
          <div class="overflow-auto rounded-lg border border-gray-200 shadow-sm mb-6">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã đơn</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày đặt</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SĐT</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng tiền</th>
                  <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                  <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <!-- Dòng dữ liệu -->
                <?php
                $sql =
                  "SELECT 
                  users.userName,
                  gh.sdt,
                  gh.diachi,
                  gh.quan,
                  gh.huyen,
                  gh.thanhpho,
                  hoadon.idBill,
                  totalBill,
                  statusBill,
                  fullName,
                  hoadon.create_at,
                  phoneNumber,
                  paymentMethod,
                  gh.tennguoinhan
                FROM hoadon 
                JOIN users ON hoadon.idUser = users.id 
                JOIN thongTinGiaoHang AS gh ON hoadon.id_diachi = gh.id
                WHERE 1=1 
                ";

                // Add search and filter conditions
                $params = [];
                $types = "";

                if (isset($_GET['search']) && $_GET['search'] != '') {
                  $search = $_GET['search'];
                  $sql .= " AND users.phoneNumber LIKE ?";
                  $params[] = "%$search%";
                  $types .= "s";
                }

                if (isset($_GET['filter_type']) && $_GET['filter_type'] == 'verification') {
                  $sql .= " AND hoadon.statusBill = 1";
                } elseif (isset($_GET['filter_type']) && $_GET['filter_type'] == 'completed') {
                  $sql .= " AND hoadon.statusBill = 3";
                } elseif (isset($_GET['filter_type']) && $_GET['filter_type'] == 'delivering') {
                  $sql .= " AND hoadon.statusBill = 2";
                } elseif (isset($_GET['filter_type']) && $_GET['filter_type'] == 'cancelled') {
                  $sql .= " AND hoadon.statusBill = 4";
                } elseif (isset($_GET['status']) && $_GET['status'] != '') {
                  $status = $_GET['status'];
                  $sql .= " AND hoadon.statusBill = ?";
                  $params[] = $status;
                  $types .= "i";
                }

                if (isset($_GET['province']) && $_GET['province'] != '') {
                  $province = $_GET['province'];
                  $sql .= " AND gh.thanhpho = ?";
                  $params[] = $province;
                  $types .= "s";
                }

                if (isset($_GET['district']) && $_GET['district'] != '') {
                  $district = $_GET['district'];
                  $sql .= " AND gh.quan = ?";
                  $params[] = $district;
                  $types .= "s";
                }

                if (isset($_GET['from_date']) && $_GET['from_date'] != '') {
                  $from_date = $_GET['from_date'];
                  $sql .= " AND hoadon.create_at >= ?";
                  $params[] = $from_date;
                  $types .= "s";
                }

                if (isset($_GET['to_date']) && $_GET['to_date'] != '') {
                  $to_date = $_GET['to_date'];
                  $sql .= " AND hoadon.create_at <= ?";
                  $params[] = $to_date;
                  $types .= "s";
                }

                if (isset($_GET['madon']) && $_GET['madon'] != '') {
                  $madon = $_GET['madon'];
                  $sql .= " AND hoadon.idBill = ?";
                  $params[] = $madon;
                  $types .= "i";
                }

                $sql .= " ORDER BY hoadon.create_at DESC LIMIT ?, ?";
                $params[] = $offset;
                $params[] = $limit;
                $types .= "ii";

                $stmt = $conn->prepare($sql);
                if (!empty($params)) {
                  $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();

                $texts = [
                  1 => 'Đang xử lý',
                  2 => 'Đang được giao',
                  3 => 'Giao hàng thành công',
                  4 => 'Đơn hàng đã hủy'
                ];

                $status_classes = [
                  1 => 'status-badge status-pending',
                  2 => 'status-badge status-shipping',
                  3 => 'status-badge status-completed',
                  4 => 'status-badge status-cancelled'
                ];

                if ($result->num_rows === 0) {
                  echo '<tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">Không có đơn hàng nào phù hợp với tiêu chí tìm kiếm</td></tr>';
                }

                while ($row = $result->fetch_assoc()) {
                  $text = $texts[$row['statusBill']] ?? 'Không xác định';
                  $status_class = $status_classes[$row['statusBill']] ?? '';
                ?>
                  <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      <div class="flex items-center">
                        <span class="font-mono">#MD<?= htmlspecialchars($row['idBill']) ?></span>
                        <?php if ($row['statusBill'] == 1): ?>
                          <span class="ml-2 flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                          </span>
                        <?php endif; ?>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="inline-flex items-center bg-gray-50 px-2.5 py-1.5 rounded-md shadow-sm text-gray-700 text-sm">
                        <i class="far fa-calendar-alt mr-2 text-gray-400"></i>
                        <?= htmlspecialchars(date('d/m/Y H:i', strtotime($row['create_at']))) ?>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($row['fullName']) ?></div>
                      <div class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($row['thanhpho']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <?= htmlspecialchars($row['phoneNumber']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-semibold text-blue-600"><?= number_format($row['totalBill'], 0, ',', '.') ?>đ</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <span class="<?= $status_class ?>">
                        <?php if ($row['statusBill'] == 1): ?>
                          <i class="fas fa-clock mr-1"></i>
                        <?php elseif ($row['statusBill'] == 2): ?>
                          <i class="fas fa-truck mr-1"></i>
                        <?php elseif ($row['statusBill'] == 3): ?>
                          <i class="fas fa-check-circle mr-1"></i>
                        <?php elseif ($row['statusBill'] == 4): ?>
                          <i class="fas fa-times-circle mr-1"></i>
                        <?php endif; ?>
                        <?= $text ?>
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <div class="flex items-center justify-center space-x-3">
                        <?php if ($roleTableSidebar->isAuthorized($adminID, 10, 3)) { ?>

                          <button
                            class="quick-action text-purple-500 hover:text-purple-700"
                            onclick="openUpdateModal(this)"
                            data-id="<?php echo $row['idBill'] ?>"
                            data-name="<?php echo $row['tennguoinhan'] ?>"
                            data-date="<?php echo $row['create_at'] ?>"
                            data-total="<?php echo $row['totalBill'] ?>"
                            data-district="<?= $row['quan'] ?>"
                            data-ward="<?= $row['huyen'] ?>"
                            data-address="<?= $row['diachi'] ?>"
                            data-city="<?= $row['thanhpho'] ?>"
                            data-status="<?php echo $row['statusBill'] ?>"
                            title="Cập nhật trạng thái">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                          </button>
                        <?php } ?>
                        <button
                          class="quick-action text-indigo-500 hover:text-indigo-700"
                          onclick="showOrderDetail(this)"
                          data-id="<?= $row['idBill'] ?>"
                          data-name="<?= $row['tennguoinhan'] ?>"
                          data-date="<?= $row['create_at'] ?>"
                          data-district="<?= $row['quan'] ?>"
                          data-ward="<?= $row['huyen'] ?>"
                          data-address="<?= $row['diachi'] ?>"
                          data-city="<?= $row['thanhpho'] ?>"
                          data-phone="<?= $row['sdt'] ?>"
                          data-payment="<?= $row['paymentMethod'] ?>"
                          data-status="<?= htmlspecialchars($row['statusBill']) ?>"
                          title="Xem chi tiết đơn hàng">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          </svg>
                        </button>

                        <?php if ($row['statusBill'] != 4): ?>
                          <?php if ($roleTableSidebar->isAuthorized($adminID, 10, 4)) { ?>

                            <button
                              class="quick-action text-red-500 hover:text-red-700"
                              data-id="<?= $row['idBill'] ?>"
                              onclick="huyDon(<?= $row['idBill'] ?>)"
                              title="Hủy đơn hàng">
                              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                              </svg>
                            </button>
                          <?php } ?>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($total_pages > 1): ?>
            <div class="flex justify-center mt-6">
              <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <?php if ($page > 1): ?>
                  <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&filter_type=<?= urlencode($filter_type) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <span class="sr-only">Previous</span>
                    <i class="fas fa-chevron-left w-5 h-5"></i>
                  </a>
                <?php endif; ?>

                <?php
                // Calculate range of page numbers to show
                $range = 2; // Number of pages to show on each side of current page
                $start_page = max(1, $page - $range);
                $end_page = min($total_pages, $page + $range);

                // Always show first page
                if ($start_page > 1) {
                  echo '<a href="?page=1&search=' . urlencode($search) . '&filter_type=' . urlencode($filter_type) . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>';
                  if ($start_page > 2) {
                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                  }
                }

                // Show page numbers
                for ($i = $start_page; $i <= $end_page; $i++) {
                  $is_current = $i === $page;
                  echo '<a href="?page=' . $i . '&search=' . urlencode($search) . '&filter_type=' . urlencode($filter_type) . '" class="relative inline-flex items-center px-4 py-2 border ' .
                    ($is_current ? 'border-indigo-500 bg-indigo-50 text-indigo-600 z-10' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50') .
                    ' text-sm font-medium">' . $i . '</a>';
                }

                // Always show last page
                if ($end_page < $total_pages) {
                  if ($end_page < $total_pages - 1) {
                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                  }
                  echo '<a href="?page=' . $total_pages . '&search=' . urlencode($search) . '&filter_type=' . urlencode($filter_type) . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">' . $total_pages . '</a>';
                }
                ?>

                <?php if ($page < $total_pages): ?>
                  <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&filter_type=<?= urlencode($filter_type) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <span class="sr-only">Next</span>
                    <i class="fas fa-chevron-right w-5 h-5"></i>
                  </a>
                <?php endif; ?>
              </nav>
            </div>
          <?php endif; ?>

        </div>

        <!-- Advanced Filter Modal -->
        <div id="filterModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
          <div class="animate-fade-in bg-white w-full max-w-md rounded-2xl p-6 shadow-xl relative">
            <button onclick="toggleFilterModal()" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-xl font-bold">&times;</button>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Bộ lọc nâng cao</h3>

            <form action="" method="GET" class="space-y-4 text-sm text-gray-700">
              <input type="hidden" name="filter_type" value="<?= htmlspecialchars($filter_type) ?>">

              <div class="grid grid-cols-2 gap-4">
                <select name="province" id="province" class="px-4 py-2 border rounded-lg text-gray-700 focus:ring-2 focus:ring-blue-500">
                  <option value="">Chọn Tỉnh/Thành phố</option>
                  <?php if (!empty($_GET['province'])): ?>
                    <option selected><?= htmlspecialchars($_GET['province']) ?></option>
                  <?php endif; ?>
                </select>

                <select name="district" id="district" class="px-4 py-2 border rounded-lg text-gray-700 focus:ring-2 focus:ring-blue-500" <?= empty($_GET['province']) ? 'disabled' : '' ?>>
                  <option value="">Chọn Quận/Huyện</option>
                  <?php if (!empty($_GET['district'])): ?>
                    <option selected><?= htmlspecialchars($_GET['district']) ?></option>
                  <?php endif; ?>
                </select>
              </div>

              <div>
                <label class="block font-medium mb-1">Ngày đặt từ:</label>
                <input type="date" name="from_date" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" value="<?= htmlspecialchars($_GET['from_date'] ?? '') ?>">
              </div>

              <div>
                <label class="block font-medium mb-1">Đến ngày:</label>
                <input type="date" name="to_date" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" value="<?= htmlspecialchars($_GET['to_date'] ?? '') ?>">
              </div>

              <div>
                <label class="block font-medium mb-1">MÃ ĐƠN:</label>
                <input type="number" name="madon" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" value="<?= htmlspecialchars($_GET['madon'] ?? '') ?>">
              </div>

              <div>
                <label class="block font-medium mb-1">Trạng thái:</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                  <option value="">Tất cả</option>
                  <option value="1" <?= (isset($_GET['status']) && $_GET['status'] == 1) ? 'selected' : '' ?>>Đang xử lý</option>
                  <option value="2" <?= (isset($_GET['status']) && $_GET['status'] == 2) ? 'selected' : '' ?>>Đang giao</option>
                  <option value="3" <?= (isset($_GET['status']) && $_GET['status'] == 3) ? 'selected' : '' ?>>Hoàn thành</option>
                  <option value="4" <?= (isset($_GET['status']) && $_GET['status'] == 4) ? 'selected' : '' ?>>Đã hủy</option>
                </select>
              </div>

              <div class="pt-3 flex gap-3">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg font-medium text-sm shadow transition-colors">
                  <i class="fas fa-filter mr-2"></i> Áp dụng
                </button>
                <a href="?filter_type=<?= htmlspecialchars($filter_type) ?>" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2.5 rounded-lg font-medium text-sm shadow text-center">
                  <i class="fas fa-undo mr-2"></i> Đặt lại
                </a>
              </div>
            </form>
          </div>
        </div>

        <!-- Update Status Modal -->
        <div id="updateModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center hidden">
          <div class="animate-fade-in bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4 relative">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-red-500 transition text-xl font-semibold">&times;</button>
            <h2 class="text-lg font-bold text-gray-800">
              Cập nhật trạng thái đơn hàng <span id="modalOrderId" class="text-indigo-600">#MD<?php ?></span>
            </h2>

            <div class="space-y-1 text-sm text-gray-700">
              <p><span class="font-semibold">Khách hàng:</span> <span id="modalCustomer"></span></p>
              <div class="bg-gray-50 px-2 py-1 rounded-md shadow-sm text-gray-700 text-sm inline-block mb-2">
                <i class="far fa-calendar-alt mr-2 text-gray-400"></i> <span id="modalDate"></span>
              </div>
              <p><span class="font-semibold">Tổng tiền:</span> <span id="modalTotal" class="text-blue-600 font-medium"></span></p>
              <p>
                <span class="font-semibold">Địa chỉ: </span>
                <span id="modalAddress"></span>,
                <span id="modalDistrict"></span>,
                <span id="modalCity"></span>,
                <span id="modalWard"></span>
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái:</label>
              <select id="modalStatus"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="1">Đang xử lý</option>
                <option value="2">Đang giao hàng</option>
                <option value="3">Giao hàng thành công</option>
                <option value="4">Đã hủy</option>
              </select>
            </div>

            <div class="pt-4">
              <button id="saveStatusBtn" type="button"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-lg transition shadow-md">
                <i class="fas fa-save mr-2"></i> Lưu thay đổi
              </button>
            </div>
          </div>
        </div>

        <!-- Order Detail Modal -->
        <div id="orderDetailModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center hidden">
          <div class="transition duration-300 ease-out animate-fade-in bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 space-y-5 relative text-sm text-gray-800">
            <button onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-500 hover:text-red-500 transition text-xl font-bold">&times;</button>

            <h2 class="text-xl font-bold text-gray-900">Chi tiết đơn hàng <span id="orderId" class="text-indigo-600"></span></h2>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <div class="flex items-center">
                  <i class="fas fa-user-circle text-gray-400 mr-2 w-5"></i>
                  <span><strong>Khách hàng:</strong> <span id="idCustomer"></span></span>
                </div>
                <div class="flex items-center">
                  <i class="fas fa-phone text-gray-400 mr-2 w-5"></i>
                  <span><strong>SĐT:</strong> <span id="orderPhone"></span></span>
                </div>
                <div class="flex items-center">
                  <i class="fas fa-map-marker-alt text-gray-400 mr-2 w-5"></i>
                  <span><strong>Địa chỉ:</strong>
                    <span id="orderAddress"></span>,
                    <span id="orderDistrict"></span>,
                    <span id="orderWard"></span>,
                    <span id="orderCity"></span>
                  </span>
                </div>
              </div>

              <div class="space-y-2">
                <div class="flex items-center">
                  <i class="far fa-calendar-alt text-gray-400 mr-2 w-5"></i>
                  <span><strong>Ngày đặt:</strong> <span id="orderDate"></span></span>
                </div>
                <div class="flex items-center">
                  <i class="fas fa-credit-card text-gray-400 mr-2 w-5"></i>
                  <span><strong>Phương thức:</strong> <span id="orderPayment"></span></span>
                </div>
                <div class="flex items-center">
                  <i class="fas fa-tag text-gray-400 mr-2 w-5"></i>
                  <span><strong>Trạng thái:</strong> <span id="orderStatus" class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold ml-1"></span></span>
                </div>
              </div>
            </div>

            <div>
              <h3 class="font-semibold mb-3 flex items-center">
                <i class="fas fa-shopping-basket text-indigo-500 mr-2"></i>Chi tiết sản phẩm:
              </h3>
              <div class="max-h-64 overflow-y-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm" id="orderProducts">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-2 text-left font-medium text-gray-500">Mã SP</th>
                      <th class="px-4 py-2 text-left font-medium text-gray-500">Sản phẩm</th>
                      <th class="px-4 py-2 text-center font-medium text-gray-500">SL</th>
                      <th class="px-4 py-2 text-right font-medium text-gray-500">Đơn giá</th>
                      <th class="px-4 py-2 text-right font-medium text-gray-500">Thành tiền</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                      <td colspan="5" class="px-4 py-3 text-center text-gray-500">
                        <div class="flex justify-center">
                          <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                          </svg>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div>
              <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex justify-between mb-2">
                  <span class="font-medium">Tổng cộng:</span>
                  <span id="orderSummary" class="text-indigo-700 text-lg font-bold"></span>
                </div>
              </div>
            </div>

            <div class="pt-4">
              <button onclick="closeDetailModal()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition shadow-sm">
                <i class="fas fa-times mr-2"></i> Đóng
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    function huyDon(idBill) {
      if (!confirm(`Bạn có chắc muốn huỷ đơn hàng #MD${idBill}?`)) return;

      const btn = document.querySelector(`button[data-id="${idBill}"]`);
      if (btn) {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        btn.disabled = true;
      }

      fetch('../controllers/huydon.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `idBill=${idBill}`
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 z-50 animate-fade-in';
            notification.innerHTML = `<div class="flex"><div class="flex-shrink-0"><i class="fas fa-check-circle text-green-500 mr-2"></i></div><div>${data.message}</div></div>`;
            document.body.appendChild(notification);

            setTimeout(() => {
              location.reload();
            }, 1500);
          } else {
            alert('❌ ' + data.message);
            if (btn) {
              btn.innerHTML = originalHTML;
              btn.disabled = false;
            }
          }
        })
        .catch(err => {
          console.error(err);
          alert('Lỗi kết nối đến máy chủ.');
          if (btn) {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
          }
        });
    }

    function toggleFilterModal() {
      const modal = document.getElementById("filterModal");
      modal.classList.toggle("hidden");
    }

    function formatCurrency(n) {
      return new Intl.NumberFormat('vi-VN').format(n) + 'đ';
    }

    function showOrderDetail(btn) {
      if (!btn) return;
      const texts = {
        1: 'Đang xử lý',
        2: 'Đang được giao',
        3: 'Giao hàng thành công',
        4: 'Đơn hàng đã hủy'
      };

      const statusClasses = {
        1: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
        2: 'bg-blue-100 text-blue-800 border border-blue-200',
        3: 'bg-green-100 text-green-800 border border-green-200',
        4: 'bg-red-100 text-red-800 border border-red-200'
      };

      document.getElementById("orderDetailModal").classList.remove("hidden");
      document.getElementById("orderId").textContent = "#MD" + btn.dataset.id;
      document.getElementById("idCustomer").textContent = btn.dataset.name;
      document.getElementById("orderDate").textContent = new Date(btn.dataset.date).toLocaleString('vi-VN');
      document.getElementById("orderAddress").textContent = btn.dataset.address;
      document.getElementById("orderDistrict").textContent = btn.dataset.district;
      document.getElementById("orderWard").textContent = btn.dataset.ward;
      document.getElementById("orderCity").textContent = btn.dataset.city;
      document.getElementById("orderPhone").textContent = btn.dataset.phone;
      document.getElementById("orderPayment").textContent = btn.dataset.payment;

      const statusEl = document.getElementById("orderStatus");
      statusEl.textContent = texts[btn.dataset.status] || "Không xác định";
      statusEl.className = 'inline-block px-2 py-0.5 rounded-full text-xs font-semibold ml-1 ' +
        (statusClasses[btn.dataset.status] || 'bg-gray-100 text-gray-800');

      const tbody = document.querySelector("#orderProducts tbody");
      tbody.innerHTML = `
    <tr>
      <td colspan="5" class="px-4 py-8 text-center text-gray-500">
        <div class="flex justify-center">
          <svg class="animate-spin h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
      </td>
    </tr>
  `;
      console.log(btn.dataset.id)

  fetch("../controllers/get_order_detail.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: "id=" + encodeURIComponent(btn.dataset.id)

    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        console.log("✅ Danh sách sản phẩm:", data.products);
        let subtotal = 0;
        tbody.innerHTML = '';
        if (data.products.length === 0) {
          tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Không có sản phẩm nào</td></tr>`;
        } else {
          data.products.forEach((item, i) => {
            const total = item.price * item.quantity;
            subtotal += total;
            tbody.innerHTML += `
        <tr class="${i % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">
          <td class="px-4 py-3 whitespace-nowrap font-mono text-xs">${item.id}</td>
          <td class="px-4 py-3 whitespace-nowrap">${item.name}</td>
          <td class="px-4 py-3 text-center">${item.quantity}</td>
          <td class="px-4 py-3 text-right">${formatCurrency(item.price)}</td>
          <td class="px-4 py-3 text-right font-medium">${formatCurrency(total)}</td>
        </tr>
        `;
          });
        }
        document.getElementById("orderSummary").innerHTML = formatCurrency(subtotal);
      } else {
        tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-4 text-center text-red-500">❌ Không tìm thấy sản phẩm</td></tr>`;
        document.getElementById("orderSummary").innerHTML = '0đ';
      }
    })
    .catch(err => {
      console.error(err);
      tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-4 text-center text-red-500">❌ Lỗi khi tải sản phẩm</td></tr>`;
      document.getElementById("orderSummary").innerHTML = '0đ';
    });
  }

    function closeDetailModal() {
      document.getElementById("orderDetailModal").classList.add("hidden");
    }

    function openUpdateModal(button) {
      document.getElementById("modalOrderId").textContent = "#MD" + button.dataset.id;
      document.getElementById("modalCustomer").textContent = button.dataset.name;
      document.getElementById("modalDate").textContent = new Date(button.dataset.date).toLocaleString('vi-VN');
      document.getElementById("modalTotal").textContent = formatCurrency(button.dataset.total);
      document.getElementById("modalDistrict").textContent = button.dataset.district;
      document.getElementById("modalWard").textContent = button.dataset.ward;
      document.getElementById("modalAddress").textContent = button.dataset.address;
      document.getElementById("modalCity").textContent = button.dataset.city;
      document.getElementById("saveStatusBtn").dataset.id = button.dataset.id;


      const select = document.getElementById("modalStatus");
      select.value = button.dataset.status;


      if (parseInt(button.dataset.status) === 3 || parseInt(button.dataset.status) === 4) {
        select.disabled = true;
        select.classList.add("opacity-50", "cursor-not-allowed");
      } else {
        select.disabled = false;
        select.classList.remove("opacity-50", "cursor-not-allowed");
      }
        console.log("dataaaaaaaaaaaaaa",button.dataset.status)
      console.log("dataaaaaaaaaaaaaa",select)


      document.getElementById("updateModal").classList.remove("hidden");
    }

    function closeModal() {
      document.getElementById("updateModal").classList.add("hidden");
    }

    document.getElementById('saveStatusBtn').addEventListener('click', async function() {
      const idText = document.getElementById('modalOrderId').textContent;
      const idBill = idText.replace('#MD', '');
      const status = document.getElementById('modalStatus').value;

      const btn = document.getElementById('saveStatusBtn');
      const originalText = btn.innerHTML;
      btn.innerHTML = '<svg class="inline-block animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Đang cập nhật...';
      btn.disabled = true;
      let products = [];
      try {
        const res = await fetch("../controllers/get_order_detail.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: "id=" + btn.dataset.id
        });

        const data = await res.json();

        if (data.success) {
          products = data.products;
          console.log("✅ Sản phẩm đã lấy về:", products);
        } else {
          alert("Không lấy được sản phẩm!");
          return;
        }
      } catch (err) {
        console.error("Lỗi khi lấy sản phẩm:", err);
      }
      fetch('../controllers/capnhat_trangthai_don.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            idBill: idBill,
            statusBill: status,
            products: products
          })
        })
        .then(res => res.json())
        .then(data => {
          const notification = document.createElement('div');
          notification.className = 'fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 z-50 animate-fade-in';
          notification.innerHTML = `<div class="flex"><div class="flex-shrink-0"><i class="fas fa-check-circle text-green-500 mr-2"></i></div><div>${data.message || 'Cập nhật thành công'}</div></div>`;
          document.body.appendChild(notification);

          setTimeout(() => {
            location.reload();
          }, 1500);
        })
        .catch(err => {
          console.error('Lỗi:', err);
          alert('Có lỗi xảy ra khi cập nhật trạng thái');
          btn.innerHTML = originalText;
          btn.disabled = false;
        });
    });

    const data = {
      "Đà Nẵng": {
        "Quận Liên Chiểu": ["Hòa Khánh Bắc", "Hòa Khánh Nam", "Hòa Minh", "Hòa Hiệp Bắc", "Hòa Hiệp Nam", "Hòa Hiệp Trung"],
        "Quận Thanh Khê": ["Thanh Khê Đông", "Thanh Khê Tây", "An Khê", "Chính Gián", "Tam Thuận", "Tân Chính", "Thạc Gián", "Vĩnh Trung", "Xuân Hà"],
        "Quận Hải Châu": ["Hải Châu 1", "Hải Châu 2", "Bình Hiên", "Bình Thuận", "Hòa Cường Bắc", "Hòa Cường Nam", "Hòa Thuận Đông", "Hòa Thuận Tây", "Nam Dương", "Phước Ninh", "Thạch Thang", "Thanh Bình", "Thuận Phước"],
        "Quận Sơn Trà": ["An Hải Bắc", "An Hải Đông", "An Hải Tây", "Mân Thái", "Nại Hiên Đông", "Phước Mỹ", "Thọ Quang"],
        "Quận Ngũ Hành Sơn": ["Hòa Hải", "Hòa Quý", "Khuê Mỹ", "Mỹ An"],
        "Quận Cẩm Lệ": ["Hòa An", "Hòa Phát", "Hòa Thọ Đông", "Hòa Thọ Tây", "Hòa Xuân", "Khuê Trung"],
        "Huyện Hòa Vang": ["Hòa Bắc", "Hòa Châu", "Hòa Khương", "Hòa Liên", "Hòa Nhơn", "Hòa Ninh", "Hòa Phong", "Hòa Phú", "Hòa Sơn", "Hòa Tiến"],
        "Huyện đảo Hoàng Sa": ["Hoàng Sa"]
      },

      "Hà Nội": {
        "Quận Hoàn Kiếm": [
          "Phường Hàng Bạc", "Phường Hàng Bông", "Phường Cửa Đông", "Phường Cửa Nam",
          "Phường Đồng Xuân", "Phường Hàng Buồm", "Phường Hàng Đào", "Phường Hàng Gai",
          "Phường Hàng Mã", "Phường Lý Thái Tổ", "Phường Phan Chu Trinh", "Phường Tràng Tiền",
          "Phường Trần Hưng Đạo"
        ],
        "Quận Đống Đa": [
          "Phường Khâm Thiên", "Phường Văn Chương", "Phường Cát Linh", "Phường Hàng Bột",
          "Phường Láng Hạ", "Phường Láng Thượng", "Phường Nam Đồng", "Phường Ngã Tư Sở",
          "Phường Ô Chợ Dừa", "Phường Phương Liên", "Phường Phương Mai", "Phường Quang Trung",
          "Phường Thịnh Hào", "Phường Trung Liệt", "Phường Trung Phụng", "Phường Trung Tự"
        ],
        "Quận Ba Đình": [
          "Phường Cống Vị", "Phường Điện Biên", "Phường Đội Cấn", "Phường Giảng Võ",
          "Phường Kim Mã", "Phường Liễu Giai", "Phường Ngọc Hà", "Phường Ngọc Khánh",
          "Phường Nguyễn Trung Trực", "Phường Phúc Xá", "Phường Quán Thánh", "Phường Thành Công",
          "Phường Trúc Bạch", "Phường Vĩnh Phúc"
        ],
        "Quận Hai Bà Trưng": [
          "Phường Bạch Đằng", "Phường Bạch Mai", "Phường Cầu Dền", "Phường Đống Mác",
          "Phường Đồng Nhân", "Phường Lê Đại Hành", "Phường Minh Khai", "Phường Nguyễn Du",
          "Phường Phố Huế", "Phường Quỳnh Lôi", "Phường Quỳnh Mai", "Phường Thanh Nhàn",
          "Phường Trương Định", "Phường Vĩnh Tuy", "Phường Thanh Lương"
        ],
        "Quận Cầu Giấy": [
          "Phường Dịch Vọng", "Phường Dịch Vọng Hậu", "Phường Mai Dịch", "Phường Nghĩa Đô",
          "Phường Nghĩa Tân", "Phường Quan Hoa", "Phường Trung Hòa", "Phường Yên Hòa"
        ]
      },
      "Hồ Chí Minh": {
        "Quận 1": [
          "Bến Nghé", "Bến Thành", "Cầu Kho", "Cầu Ông Lãnh", "Đa Kao",
          "Nguyễn Cư Trinh", "Phạm Ngũ Lão", "Tân Định"
        ],
        "Quận 3": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
          "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10",
          "Phường 11", "Phường 12", "Phường 13", "Phường 14"
        ],
        "Quận 4": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
          "Phường 6", "Phường 8", "Phường 9", "Phường 10", "Phường 12",
          "Phường 13", "Phường 14", "Phường 15", "Phường 16", "Phường 18"
        ],
        "Quận 5": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
          "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10",
          "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"
        ],
        "Quận 6": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
          "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10",
          "Phường 11", "Phường 12", "Phường 13", "Phường 14"
        ],
        "Quận 7": [
          "Phường Tân Thuận Đông", "Phường Tân Thuận Tây", "Phường Tân Kiểng",
          "Phường Tân Hưng", "Phường Bình Thuận", "Phường Tân Quy", "Phường Phú Thuận",
          "Phường Tân Phú", "Phường Tân Phong", "Phường Phú Mỹ"
        ],
        "Quận 8": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
          "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10",
          "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16"
        ],
        "Quận 10": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
          "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10",
          "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"
        ],
        "Quận 11": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
          "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10",
          "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16"
        ],
        "Quận 12": [
          "Phường An Phú Đông", "Phường Đông Hưng Thuận", "Phường Hiệp Thành", "Phường Tân Chánh Hiệp",
          "Phường Tân Hưng Thuận", "Phường Tân Thới Hiệp", "Phường Tân Thới Nhất", "Phường Thạnh Lộc",
          "Phường Thạnh Xuân", "Phường Thới An", "Phường Trung Mỹ Tây"
        ],
        "Quận Bình Thạnh": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 5", "Phường 6",
          "Phường 7", "Phường 11", "Phường 12", "Phường 13", "Phường 14",
          "Phường 15", "Phường 17", "Phường 19", "Phường 21", "Phường 22",
          "Phường 24", "Phường 25", "Phường 26", "Phường 27", "Phường 28"
        ],
        "Quận Bình Tân": [
          "Phường An Lạc", "Phường An Lạc A", "Phường Bình Hưng Hòa", "Phường Bình Hưng Hòa A",
          "Phường Bình Hưng Hòa B", "Phường Bình Trị Đông", "Phường Bình Trị Đông A",
          "Phường Bình Trị Đông B", "Phường Tân Tạo", "Phường Tân Tạo A"
        ],
        "Quận Gò Vấp": [
          "Phường 1", "Phường 3", "Phường 4", "Phường 5", "Phường 6",
          "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11",
          "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16", "Phường 17"
        ],
        "Quận Phú Nhuận": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
          "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11",
          "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 17"
        ],
        "Quận Tân Bình": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
          "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10",
          "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"
        ],
        "Quận Tân Phú": [
          "Phường Hiệp Tân", "Phường Hòa Thạnh", "Phường Phú Thạnh", "Phường Phú Thọ Hòa",
          "Phường Phú Trung", "Phường Sơn Kỳ", "Phường Tân Quý", "Phường Tân Sơn Nhì",
          "Phường Tân Thành", "Phường Tân Thới Hòa", "Phường Tây Thạnh"
        ],
        "Thành phố Thủ Đức": [
          "Phường An Khánh", "Phường An Lợi Đông", "Phường An Phú", "Phường Bình Chiểu", "Phường Bình Thọ",
          "Phường Cát Lái", "Phường Hiệp Bình Chánh", "Phường Hiệp Bình Phước", "Phường Hiệp Phú",
          "Phường Linh Chiểu", "Phường Linh Đông", "Phường Linh Tây", "Phường Linh Trung", "Phường Linh Xuân",
          "Phường Long Bình", "Phường Long Phước", "Phường Long Thạnh Mỹ", "Phường Long Trường",
          "Phường Phú Hữu", "Phường Phước Bình", "Phường Phước Long A", "Phường Phước Long B",
          "Phường Tăng Nhơn Phú A", "Phường Tăng Nhơn Phú B", "Phường Thảo Điền", "Phường Thủ Thiêm",
          "Phường Trường Thạnh", "Phường Trường Thọ", "Phường Tam Phú", "Phường Tam Bình",
          "Phường Hiệp Phú", "Phường Bình Chiểu"
        ],
        "Huyện Bình Chánh": [
          "Thị trấn Tân Túc", "Xã An Phú Tây", "Xã Bình Chánh", "Xã Bình Hưng",
          "Xã Bình Lợi", "Xã Đa Phước", "Xã Hưng Long", "Xã Lê Minh Xuân",
          "Xã Phạm Văn Hai", "Xã Phong Phú", "Xã Quy Đức", "Xã Tân Kiên",
          "Xã Tân Nhựt", "Xã Tân Quý Tây", "Xã Vĩnh Lộc A", "Xã Vĩnh Lộc B"
        ],
        "Huyện Cần Giờ": [
          "Thị trấn Cần Thạnh", "Xã An Thới Đông", "Xã Bình Khánh",
          "Xã Long Hòa", "Xã Lý Nhơn", "Xã Tam Thôn Hiệp", "Xã Thạnh An"
        ],
        "Huyện Củ Chi": [
          "Thị trấn Củ Chi", "Xã An Nhơn Tây", "Xã An Phú", "Xã Bình Mỹ",
          "Xã Hòa Phú", "Xã Nhuận Đức", "Xã Phạm Văn Cội", "Xã Phú Hòa Đông",
          "Xã Phú Mỹ Hưng", "Xã Phước Hiệp", "Xã Phước Thạnh", "Xã Tân An Hội",
          "Xã Tân Phú Trung", "Xã Tân Thạnh Đông", "Xã Tân Thạnh Tây",
          "Xã Tân Thông Hội", "Xã Thái Mỹ", "Xã Trung An", "Xã Trung Lập Hạ",
          "Xã Trung Lập Thượng"
        ],
        "Huyện Hóc Môn": [
          "Thị trấn Hóc Môn", "Xã Bà Điểm", "Xã Đông Thạnh", "Xã Nhị Bình",
          "Xã Tân Hiệp", "Xã Tân Thới Nhì", "Xã Tân Xuân",
          "Xã Thới Tam Thôn", "Xã Trung Chánh", "Xã Xuân Thới Đông",
          "Xã Xuân Thới Sơn", "Xã Xuân Thới Thượng"
        ],
        "Huyện Nhà Bè": [
          "Thị trấn Nhà Bè", "Xã Hiệp Phước", "Xã Long Thới",
          "Xã Nhơn Đức", "Xã Phú Xuân", "Xã Phước Kiển", "Xã Phước Lộc"
        ]
      },
      "Đồng Nai": {
        "Thành phố Biên Hòa": [
          "An Bình", "Bửu Long", "Bửu Hòa", "Bửu Phong", "Hiệp Hòa", "Hòa Bình",
          "Hóa An", "Hố Nai", "Long Bình", "Long Bình Tân", "Quang Vinh", "Tam Hiệp",
          "Tam Hòa", "Tân Biên", "Tân Hiệp", "Tân Hòa", "Tân Mai", "Tân Phong",
          "Tân Tiến", "Thanh Bình", "Thống Nhất", "Trảng Dài", "Trung Dũng"
        ],
        "Huyện Long Thành": [
          "An Phước", "Bình An", "Bình Sơn", "Cẩm Đường", "Lộc An", "Long An",
          "Long Đức", "Long Phước", "Long Thọ", "Phước Bình", "Phước Thái", "Tam An",
          "Tân Hiệp"
        ],
        "Huyện Nhơn Trạch": [
          "Hiệp Phước", "Long Tân", "Phước An", "Phước Khánh", "Phước Thiền",
          "Phú Đông", "Phú Hội", "Phú Hữu", "Phú Thạnh", "Vĩnh Thanh"
        ],
        "Huyện Trảng Bom": [
          "An Viễn", "Bình Minh", "Bình Sơn", "Bàu Hàm", "Cây Gáo", "Đồi 61",
          "Giang Điền", "Hố Nai 3", "Hưng Thịnh", "Quảng Tiến", "Sông Thao",
          "Sông Trầu", "Thanh Bình", "Trảng Bom", "Tây Hòa"
        ],
        "Thành phố Long Khánh": [
          "Bàu Sen", "Bàu Trâm", "Suối Tre", "Xuân An", "Xuân Bình", "Xuân Hòa",
          "Xuân Lập", "Xuân Tân", "Xuân Thanh", "Xuân Trung", "Xuân Hưng"
        ]
      },
      "Bình Dương": {
        "Thành phố Thủ Dầu Một": [
          "Phường Chánh Nghĩa", "Phường Hiệp An", "Phường Hiệp Thành", "Phường Hòa Phú",
          "Phường Phú Cường", "Phường Phú Hòa", "Phường Phú Lợi", "Phường Phú Mỹ",
          "Phường Phú Tân", "Phường Tân An", "Phường Tương Bình Hiệp"
        ],
        "Thành phố Dĩ An": [
          "Phường An Bình", "Phường Bình An", "Phường Bình Thắng", "Phường Dĩ An",
          "Phường Đông Hòa", "Phường Tân Bình", "Phường Tân Đông Hiệp"
        ],
        "Thành phố Thuận An": [
          "Phường An Phú", "Phường Bình Chuẩn", "Phường Bình Hòa", "Phường Hưng Định",
          "Phường Lái Thiêu", "Phường Thuận Giao", "Phường Vĩnh Phú"
        ],
        "Thị xã Bến Cát": [
          "Phường Chánh Phú Hòa", "Phường Hòa Lợi", "Phường Mỹ Phước", "Phường Tân Định",
          "Xã An Điền", "Xã An Tây"
        ],
        "Thị xã Tân Uyên": [
          "Phường Hội Nghĩa", "Phường Khánh Bình", "Phường Khánh Hòa", "Phường Phú Chánh",
          "Phường Tân Hiệp", "Phường Tân Phước Khánh", "Phường Thái Hòa", "Phường Thạnh Phước",
          "Phường Thạnh Hội", "Phường Uyên Hưng", "Xã Bạch Đằng", "Xã Vĩnh Tân"
        ],
        "Huyện Bàu Bàng": [
          "Xã Cây Trường II", "Xã Hưng Hòa", "Xã Lai Hưng", "Xã Lai Uyên",
          "Xã Long Nguyên", "Xã Tân Hưng", "Xã Trừ Văn Thố"
        ],
        "Huyện Bắc Tân Uyên": [
          "Xã Bình Mỹ", "Xã Hiếu Liêm", "Xã Lạc An", "Xã Tân Bình",
          "Xã Tân Định", "Xã Tân Lập", "Xã Tân Mỹ", "Xã Thường Tân"
        ],
        "Huyện Dầu Tiếng": [
          "Thị trấn Dầu Tiếng", "Xã An Lập", "Xã Định An", "Xã Định Hiệp",
          "Xã Định Thành", "Xã Long Hòa", "Xã Long Tân", "Xã Minh Hòa",
          "Xã Minh Tân", "Xã Minh Thạnh", "Xã Thanh An", "Xã Thanh Tuyền"
        ]
      },
      "Long An": {
        "Thành phố Tân An": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
          "Phường Tân Khánh", "Xã An Vĩnh Ngãi", "Xã Bình Tâm",
          "Xã Hướng Thọ Phú", "Xã Khánh Hậu", "Xã Lợi Bình Nhơn", "Xã Nhơn Thạnh Trung"
        ],
        "Thị xã Kiến Tường": [
          "Phường 1", "Phường 2", "Phường 3", "Xã Bình Hiệp",
          "Xã Bình Tân", "Xã Thạnh Hưng", "Xã Thạnh Trị", "Xã Tuyên Thạnh"
        ],
        "Huyện Bến Lức": [
          "Thị trấn Bến Lức", "Xã An Thạnh", "Xã Bình Đức", "Xã Lương Bình",
          "Xã Lương Hòa", "Xã Mỹ Yên", "Xã Nhựt Chánh", "Xã Phước Lợi",
          "Xã Tân Bửu", "Xã Tân Hòa", "Xã Thạnh Đức", "Xã Thạnh Hòa", "Xã Thanh Phú"
        ],
        "Huyện Đức Hòa": [
          "Thị trấn Hậu Nghĩa", "Thị trấn Đức Hòa", "Thị trấn Hiệp Hòa",
          "Xã An Ninh Đông", "Xã An Ninh Tây", "Xã Đức Hòa Đông", "Xã Đức Hòa Hạ",
          "Xã Đức Hòa Thượng", "Xã Hòa Khánh Đông", "Xã Hòa Khánh Nam",
          "Xã Hòa Khánh Tây", "Xã Hựu Thạnh", "Xã Lộc Giang", "Xã Mỹ Hạnh Bắc",
          "Xã Mỹ Hạnh Nam", "Xã Tân Mỹ"
        ],
        "Huyện Cần Giuộc": [
          "Thị trấn Cần Giuộc", "Xã Đông Thạnh", "Xã Long An", "Xã Long Hậu",
          "Xã Long Phụng", "Xã Long Thượng", "Xã Mỹ Lộc", "Xã Phước Hậu",
          "Xã Phước Lại", "Xã Phước Lâm", "Xã Phước Lý", "Xã Phước Vĩnh Đông",
          "Xã Tân Kim", "Xã Tân Tập", "Xã Thuận Thành", "Xã Trường Bình"
        ]
      },
      "Tiền Giang": {
        "Thành phố Mỹ Tho": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
          "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10",
          "Phường Tân Long", "Xã Đạo Thạnh", "Xã Mỹ Phong", "Xã Phước Thạnh",
          "Xã Tân Mỹ Chánh", "Xã Thới Sơn", "Xã Trung An", "Xã Trung Thạnh"
        ],
        "Thị xã Gò Công": [
          "Phường 1", "Phường 2", "Phường 3", "Xã Bình Đông", "Xã Bình Xuân",
          "Xã Long Chánh", "Xã Long Hưng", "Xã Tân Trung"
        ],
        "Thị xã Cai Lậy": [
          "Phường 1", "Phường 2", "Xã Cẩm Sơn", "Xã Mỹ Hạnh Đông", "Xã Mỹ Hạnh Trung",
          "Xã Mỹ Phước Tây", "Xã Nhị Quý", "Xã Tân Bình", "Xã Tân Hội",
          "Xã Tân Phú", "Xã Thạnh Lộc", "Xã Thanh Hòa"
        ],
        "Huyện Châu Thành": [
          "Thị trấn Tân Hiệp", "Xã Bàn Long", "Xã Bình Đức", "Xã Dưỡng Điềm",
          "Xã Hòa Tịnh", "Xã Kim Sơn", "Xã Long An", "Xã Long Định", "Xã Long Hưng",
          "Xã Nhị Bình", "Xã Phú Phong", "Xã Song Thuận", "Xã Tam Hiệp",
          "Xã Tân Hương", "Xã Tân Lý Đông", "Xã Tân Lý Tây", "Xã Thân Cửu Nghĩa",
          "Xã Thới Sơn", "Xã Vĩnh Kim"
        ],
        "Huyện Cai Lậy": [
          "Thị trấn Cai Lậy", "Xã Cẩm Sơn", "Xã Hiệp Đức", "Xã Long Tiên",
          "Xã Mỹ Thành Bắc", "Xã Mỹ Thành Nam", "Xã Ngũ Hiệp", "Xã Phú An",
          "Xã Phú Cường", "Xã Phú Nhuận", "Xã Tân Hội", "Xã Tân Phong",
          "Xã Tân Phú", "Xã Thạnh Lộc", "Xã Thanh Hòa"
        ]
      },
      "Bà Rịa - Vũng Tàu": {
        "Thành phố Vũng Tàu": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5",
          "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11",
          "Phường 12", "Phường Nguyễn An Ninh", "Phường Rạch Dừa",
          "Phường Thắng Nhất", "Phường Thắng Nhì", "Phường Thắng Tam",
          "Xã Long Sơn", "Xã đảo Gò Găng"
        ],
        "Thành phố Bà Rịa": [
          "Phường Long Hương", "Phường Long Tâm", "Phường Long Toàn",
          "Phường Phước Hiệp", "Phường Phước Hưng", "Phường Phước Nguyên",
          "Phường Phước Trung", "Xã Hòa Long", "Xã Long Phước",
          "Xã Tân Hưng"
        ],
        "Thị xã Phú Mỹ": [
          "Phường Hắc Dịch", "Phường Mỹ Xuân", "Phường Phú Mỹ",
          "Phường Phước Hòa", "Phường Tân Phước", "Xã Châu Pha",
          "Xã Sông Xoài", "Xã Tân Hòa", "Xã Tân Hải", "Xã Tóc Tiên"
        ],
        "Huyện Long Điền": [
          "Thị trấn Long Điền", "Thị trấn Long Hải", "Xã An Ngãi",
          "Xã An Nhứt", "Xã Phước Hưng", "Xã Phước Tỉnh",
          "Xã Tam Phước"
        ],
        "Huyện Đất Đỏ": [
          "Thị trấn Đất Đỏ", "Thị trấn Phước Hải", "Xã Long Mỹ",
          "Xã Long Tân", "Xã Láng Dài", "Xã Lộc An",
          "Xã Phước Hội", "Xã Phước Long Thọ"
        ],
        "Huyện Xuyên Mộc": [
          "Thị trấn Phước Bửu", "Xã Bàu Lâm", "Xã Bông Trang",
          "Xã Bưng Riềng", "Xã Hòa Bình", "Xã Hòa Hiệp",
          "Xã Hòa Hội", "Xã Phước Tân", "Xã Phước Thuận",
          "Xã Tân Lâm", "Xã Xuyên Mộc"
        ],
        "Huyện Châu Đức": [
          "Thị trấn Ngãi Giao", "Xã Bình Ba", "Xã Bình Giã",
          "Xã Bình Trung", "Xã Bông Trang", "Xã Cù Bị",
          "Xã Đá Bạc", "Xã Kim Long", "Xã Láng Lớn",
          "Xã Nghĩa Thành", "Xã Quảng Thành", "Xã Sơn Bình",
          "Xã Suối Nghệ", "Xã Suối Rao", "Xã Xà Bang"
        ],
        "Huyện Côn Đảo": [
          "Thị trấn Côn Đảo"
        ]
      },
      "Khánh Hòa": {
        "Thành phố Nha Trang": [
          "Phường Vĩnh Hải", "Phường Vĩnh Nguyên", "Phường Vĩnh Phước",
          "Phường Vĩnh Trường", "Phường Phước Long", "Phường Phước Hải",
          "Phường Phước Tân", "Phường Xương Huân", "Phường Tân Lập",
          "Phường Lộc Thọ", "Phường Ngọc Hiệp", "Phường Vạn Thạnh",
          "Xã Vĩnh Thái", "Xã Vĩnh Hiệp", "Xã Vĩnh Trung", "Xã Vĩnh Lương"
        ],
        "Thành phố Cam Ranh": [
          "Phường Cam Linh", "Phường Cam Lộc", "Phường Cam Lợi", "Phường Cam Nghĩa",
          "Phường Cam Phúc Bắc", "Phường Cam Phúc Nam", "Phường Cam Phú",
          "Phường Ba Ngòi", "Xã Cam Bình", "Xã Cam Thịnh Đông", "Xã Cam Thịnh Tây",
          "Xã Cam Lập", "Xã Cam Thành Nam"
        ],
        "Thị xã Ninh Hòa": [
          "Phường Ninh Hiệp", "Phường Ninh Giang", "Phường Ninh Hà", "Phường Ninh Diêm",
          "Phường Ninh Thủy", "Phường Ninh Hải", "Phường Ninh Sơn", "Phường Ninh Trung",
          "Xã Ninh Phú", "Xã Ninh An", "Xã Ninh Quang", "Xã Ninh Bình", "Xã Ninh Hưng",
          "Xã Ninh Lộc", "Xã Ninh Ích", "Xã Ninh Sim", "Xã Ninh Xuân", "Xã Ninh Thọ"
        ],
        "Huyện Cam Lâm": [
          "Thị trấn Cam Đức", "Xã Cam Thành Bắc", "Xã Cam Hải Đông", "Xã Cam Hải Tây",
          "Xã Cam Hòa", "Xã Cam Hiệp Bắc", "Xã Cam Hiệp Nam", "Xã Cam Lập",
          "Xã Cam Phước Tây", "Xã Cam Tân", "Xã Cam Thành Nam", "Xã Suối Cát",
          "Xã Suối Tân"
        ],
        "Huyện Diên Khánh": [
          "Thị trấn Diên Khánh", "Xã Diên An", "Xã Diên Bình", "Xã Diên Điền",
          "Xã Diên Đồng", "Xã Diên Lạc", "Xã Diên Lâm", "Xã Diên Phú",
          "Xã Diên Sơn", "Xã Diên Tân", "Xã Diên Thạnh", "Xã Diên Thọ",
          "Xã Diên Xuân", "Xã Suối Hiệp", "Xã Suối Tiên"
        ],
        "Huyện Vạn Ninh": [
          "Thị trấn Vạn Giã", "Xã Đại Lãnh", "Xã Vạn Bình", "Xã Vạn Hưng",
          "Xã Vạn Khánh", "Xã Vạn Long", "Xã Vạn Lương", "Xã Vạn Phú",
          "Xã Vạn Phước", "Xã Vạn Thạnh", "Xã Vạn Thắng"
        ],
        "Huyện Khánh Sơn": [
          "Thị trấn Tô Hạp", "Xã Ba Cụm Bắc", "Xã Ba Cụm Nam", "Xã Sơn Bình",
          "Xã Sơn Hiệp", "Xã Sơn Lâm", "Xã Sơn Trung", "Xã Thành Sơn"
        ],
        "Huyện Khánh Vĩnh": [
          "Thị trấn Khánh Vĩnh", "Xã Cầu Bà", "Xã Giang Ly", "Xã Khánh Bình",
          "Xã Khánh Đông", "Xã Khánh Hiệp", "Xã Khánh Nam", "Xã Khánh Phú",
          "Xã Khánh Thượng", "Xã Liên Sang", "Xã Sơn Thái", "Xã Sông Cầu"
        ],
        "Huyện Trường Sa": [
          "Thị trấn Trường Sa", "Xã Song Tử Tây", "Xã Sinh Tồn"
        ]
      },
      "Ninh Thuận": {
        "Thành phố Phan Rang-Tháp Chàm": [
          "Phường Đô Vinh", "Phường Mỹ Hải", "Phường Mỹ Đông", "Phường Tấn Tài",
          "Phường Thanh Sơn", "Phường Phủ Hà", "Phường Đông Hải", "Phường Kinh Dinh",
          "Phường Bảo An", "Xã Thành Hải", "Xã Văn Hải"
        ],
        "Huyện Ninh Hải": [
          "Thị trấn Khánh Hải", "Xã Nhơn Hải", "Xã Tân Hải", "Xã Tri Hải",
          "Xã Thanh Hải", "Xã Vĩnh Hải", "Xã Xuân Hải", "Xã Hộ Hải",
          "Xã Phương Hải", "Xã Bình Hải"
        ],
        "Huyện Ninh Phước": [
          "Thị trấn Phước Dân", "Xã An Hải", "Xã Phước Hải", "Xã Phước Hậu",
          "Xã Phước Hữu", "Xã Phước Sơn", "Xã Phước Thái", "Xã Phước Thuận",
          "Xã Phước Vinh"
        ],
        "Huyện Bác Ái": [
          "Thị trấn Tống Mao (không chính thức, trung tâm hành chính)", "Xã Phước Đại",
          "Xã Phước Chiến", "Xã Phước Thắng", "Xã Phước Thành", "Xã Phước Tân",
          "Xã Phước Tiến", "Xã Phước Trung"
        ],
        "Huyện Thuận Bắc": [
          "Xã Bắc Sơn", "Xã Bắc Phong", "Xã Công Hải", "Xã Lợi Hải",
          "Xã Phước Chiến", "Xã Phước Kháng", "Xã Phước Hà"
        ],
        "Huyện Thuận Nam": [
          "Xã Cà Ná", "Xã Phước Diêm", "Xã Phước Dinh", "Xã Phước Minh",
          "Xã Phước Nam", "Xã Phước Ninh"
        ],
        "Huyện Ninh Sơn": [
          "Thị trấn Tân Sơn", "Xã Hòa Sơn", "Xã Lâm Sơn", "Xã Lương Sơn",
          "Xã Ma Nới", "Xã Mỹ Sơn", "Xã Nhơn Sơn", "Xã Quảng Sơn"
        ]
      },
      "Ninh Bình": {
        "Thành phố Ninh Bình": [
          "Phường Đông Thành", "Phường Nam Thành", "Phường Phúc Thành", "Phường Thanh Bình",
          "Phường Vân Giang", "Phường Bích Đào", "Phường Tân Thành", "Phường Ninh Khánh",
          "Phường Ninh Phong", "Phường Ninh Sơn", "Phường Nam Bình", "Phường Nam Thành",
          "Xã Ninh Nhất", "Xã Ninh Tiến", "Xã Ninh Phúc"
        ],
        "Thành phố Tam Điệp": [
          "Phường Bắc Sơn", "Phường Trung Sơn", "Phường Nam Sơn", "Phường Tây Sơn",
          "Phường Yên Bình", "Phường Tân Bình", "Xã Quang Sơn", "Xã Yên Sơn"
        ],
        "Huyện Hoa Lư": [
          "Thị trấn Thiên Tôn", "Xã Ninh Hải", "Xã Ninh An", "Xã Ninh Giang",
          "Xã Ninh Hòa", "Xã Ninh Mỹ", "Xã Ninh Khang", "Xã Ninh Xuân",
          "Xã Ninh Vân", "Xã Trường Yên"
        ],
        "Huyện Gia Viễn": [
          "Thị trấn Me", "Xã Gia Hòa", "Xã Gia Hưng", "Xã Gia Lạc",
          "Xã Gia Minh", "Xã Gia Phú", "Xã Gia Phương", "Xã Gia Thắng",
          "Xã Gia Thanh", "Xã Gia Tường", "Xã Gia Trung", "Xã Gia Vân",
          "Xã Gia Xuân", "Xã Liên Sơn"
        ],
        "Huyện Yên Mô": [
          "Thị trấn Yên Thịnh", "Xã Khánh Dương", "Xã Khánh Thượng", "Xã Mai Sơn",
          "Xã Yên Đồng", "Xã Yên Hòa", "Xã Yên Lâm", "Xã Yên Mạc",
          "Xã Yên Mỹ", "Xã Yên Nhân", "Xã Yên Phong", "Xã Yên Thái",
          "Xã Yên Thành", "Xã Yên Từ"
        ],
        "Huyện Kim Sơn": [
          "Thị trấn Phát Diệm", "Xã Chất Bình", "Xã Cồn Thoi", "Xã Định Hóa",
          "Xã Đồng Hướng", "Xã Hồi Ninh", "Xã Kim Chính", "Xã Kim Đông",
          "Xã Kim Hải", "Xã Kim Mỹ", "Xã Kim Tân", "Xã Kim Trung",
          "Xã Lưu Phương", "Xã Như Hòa", "Xã Tân Thành"
        ],
        "Huyện Nho Quan": [
          "Thị trấn Nho Quan", "Xã Cúc Phương", "Xã Đồng Phong", "Xã Gia Lâm",
          "Xã Gia Thủy", "Xã Gia Sơn", "Xã Kỳ Phú", "Xã Lạng Phong",
          "Xã Phú Long", "Xã Phú Sơn", "Xã Phú Lộc", "Xã Quỳnh Lưu",
          "Xã Sơn Lai", "Xã Sơn Hà", "Xã Sơn Thành", "Xã Văn Phú",
          "Xã Văn Phong", "Xã Xích Thổ", "Xã Yên Quang"
        ]
      },
      "Hà Tĩnh": {
        "Thành phố Hà Tĩnh": ["Phường Bắc Hà", "Phường Nam Hà"],
        "Huyện Hương Sơn": ["Thị trấn Phố Châu", "Xã Sơn Tây"]
      },

      "Hà Giang": {
        "Thành phố Hà Giang": ["Phường Trần Phú", "Phường Nguyễn Trãi"],
        "Huyện Đồng Văn": ["Thị trấn Đồng Văn", "Xã Lũng Cú"]
      },

      "Lào Cai": {
        "Thành phố Lào Cai": ["Phường Bắc Cường", "Phường Nam Cường"],
        "Huyện Sa Pa": ["Thị trấn Sa Pa", "Xã Tả Phìn"]
      },

      "Thái Nguyên": {
        "Thành phố Thái Nguyên": ["Phường Hoàng Văn Thụ", "Phường Tân Thịnh"],
        "Huyện Đại Từ": ["Thị trấn Hùng Sơn", "Xã Phú Lạc"]
      },
      "An Giang": {
        "Thành phố Long Xuyên": [
          "Phường Mỹ Bình", "Phường Mỹ Long", "Phường Mỹ Xuyên", "Phường Đông Xuyên",
          "Phường Bình Khánh", "Phường Mỹ Thới", "Xã Mỹ Hòa Hưng"
        ],
        "Thành phố Châu Đốc": [
          "Phường Châu Phú A", "Phường Châu Phú B", "Phường Núi Sam", "Phường Vĩnh Mỹ",
          "Xã Vĩnh Ngươn", "Xã Vĩnh Tế"
        ],
        "Thị xã Tân Châu": [
          "Phường Long Thạnh", "Phường Long Hưng", "Phường Long Phú", "Xã Tân An",
          "Xã Châu Phong", "Xã Phú Lộc"
        ],
        "Huyện An Phú": [
          "Thị trấn An Phú", "Thị trấn Long Bình", "Xã Khánh An", "Xã Khánh Bình",
          "Xã Phú Hữu", "Xã Phú Hội", "Xã Quốc Thái"
        ],
        "Huyện Châu Phú": [
          "Thị trấn Cái Dầu", "Xã Bình Chánh", "Xã Bình Long", "Xã Bình Mỹ",
          "Xã Bình Phú", "Xã Bình Thủy", "Xã Đào Hữu Cảnh", "Xã Thạnh Mỹ Tây"
        ],
        "Huyện Châu Thành": [
          "Thị trấn An Châu", "Xã An Hòa", "Xã Bình Hòa", "Xã Cần Đăng",
          "Xã Hòa Bình Thạnh", "Xã Vĩnh Bình", "Xã Vĩnh Hanh"
        ],
        "Huyện Phú Tân": [
          "Thị trấn Phú Mỹ", "Thị trấn Chợ Vàm", "Xã Phú Thạnh", "Xã Phú Hưng",
          "Xã Phú Hiệp", "Xã Long Hòa", "Xã Long Sơn"
        ],
        "Huyện Thoại Sơn": [
          "Thị trấn Núi Sập", "Thị trấn Phú Hòa", "Thị trấn Óc Eo",
          "Xã Vĩnh Trạch", "Xã Vĩnh Phú", "Xã Định Thành", "Xã Thoại Giang"
        ],
        "Huyện Tri Tôn": [
          "Thị trấn Tri Tôn", "Thị trấn Ba Chúc", "Xã Châu Lăng", "Xã Lương Phi",
          "Xã Lương An Trà", "Xã Tân Tuyến", "Xã An Tức"
        ],
        "Huyện Tịnh Biên": [
          "Thị trấn Tịnh Biên", "Thị trấn Nhà Bàng", "Xã An Cư", "Xã An Phú",
          "Xã Nhơn Hưng", "Xã Tân Lợi", "Xã Thới Sơn"
        ]
      },
      "Bạc Liêu": {
        "Thành phố Bạc Liêu": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 5", "Phường 7",
          "Xã Hiệp Thành", "Xã Nhà Mát", "Xã Vĩnh Trạch", "Xã Vĩnh Trạch Đông"
        ],
        "Thị xã Giá Rai": [
          "Phường 1", "Phường Hộ Phòng", "Phường Láng Tròn",
          "Xã Phong Thạnh", "Xã Phong Thạnh A", "Xã Phong Tân",
          "Xã Tân Phong", "Xã Tân Thạnh", "Xã Phong Thạnh Tây A", "Xã Phong Thạnh Tây B"
        ],
        "Huyện Hòa Bình": [
          "Thị trấn Hòa Bình", "Xã Vĩnh Mỹ A", "Xã Vĩnh Mỹ B",
          "Xã Vĩnh Hậu", "Xã Vĩnh Hậu A", "Xã Vĩnh Thịnh", "Xã Minh Diệu"
        ],
        "Huyện Vĩnh Lợi": [
          "Thị trấn Châu Hưng", "Xã Hưng Hội", "Xã Hưng Thành",
          "Xã Long Thạnh", "Xã Vĩnh Hưng", "Xã Vĩnh Hưng A", "Xã Châu Thới"
        ],
        "Huyện Đông Hải": [
          "Thị trấn Gành Hào", "Xã An Trạch", "Xã An Trạch A", "Xã An Phúc",
          "Xã Điền Hải", "Xã Định Thành", "Xã Định Thành A", "Xã Long Điền",
          "Xã Long Điền Đông", "Xã Long Điền Đông A", "Xã Long Điền Tây"
        ],
        "Huyện Phước Long": [
          "Thị trấn Phước Long", "Xã Phong Thạnh Tây", "Xã Vĩnh Phú Đông",
          "Xã Vĩnh Phú Tây", "Xã Vĩnh Thanh", "Xã Hưng Phú", "Xã Vĩnh Phú"
        ],
        "Huyện Hồng Dân": [
          "Thị trấn Ngan Dừa", "Xã Ninh Hòa", "Xã Ninh Quới", "Xã Ninh Quới A",
          "Xã Ninh Thạnh Lợi", "Xã Ninh Thạnh Lợi A", "Xã Vĩnh Lộc", "Xã Vĩnh Lộc A"
        ]
      },
      "Bắc Kạn": {
        "Thành phố Bắc Kạn": [
          "Phường Nguyễn Thị Minh Khai", "Phường Sông Cầu",
          "Phường Đức Xuân", "Phường Phùng Chí Kiên",
          "Xã Dương Quang", "Xã Nông Thượng"
        ],
        "Huyện Bạch Thông": [
          "Thị trấn Phủ Thông", "Xã Cẩm Giàng", "Xã Quân Bình", "Xã Hà Vị",
          "Xã Vi Hương", "Xã Sỹ Bình"
        ],
        "Huyện Chợ Đồn": [
          "Thị trấn Bằng Lũng", "Xã Bằng Lãng", "Xã Ngọc Phái",
          "Xã Yên Thượng", "Xã Đại Sảo", "Xã Đông Viên"
        ],
        "Huyện Chợ Mới": [
          "Thị trấn Chợ Mới", "Xã Tân Sơn", "Xã Thanh Bình",
          "Xã Quảng Chu", "Xã Yên Hân", "Xã Hòa Mục"
        ],
        "Huyện Na Rì": [
          "Thị trấn Yến Lạc", "Xã Lương Thành", "Xã Cư Lễ",
          "Xã Văn Minh", "Xã Hảo Nghĩa", "Xã Kim Hỷ"
        ],
        "Huyện Ngân Sơn": [
          "Thị trấn Nà Phặc", "Xã Lãng Ngâm", "Xã Thuần Mang",
          "Xã Đức Vân", "Xã Vân Tùng", "Xã Thượng Quan"
        ],
        "Huyện Ba Bể": [
          "Thị trấn Chợ Rã", "Xã Quảng Khê", "Xã Cao Thượng",
          "Xã Thượng Giáo", "Xã Khang Ninh", "Xã Mỹ Phương"
        ],
        "Huyện Pác Nặm": [
          "Xã Bộc Bố", "Xã Cổ Linh", "Xã An Thắng",
          "Xã Giáo Hiệu", "Xã Nghiên Loan", "Xã Cao Tân"
        ]
      },
      "Bắc Ninh": {
        "Thành phố Bắc Ninh": [
          "Phường Suối Hoa", "Phường Tiền An", "Phường Vệ An",
          "Phường Vũ Ninh", "Phường Ninh Xá", "Phường Kinh Bắc",
          "Xã Hòa Long", "Phường Võ Cường"
        ],
        "Thành phố Từ Sơn": [
          "Phường Đông Ngàn", "Phường Đình Bảng", "Phường Tân Hồng",
          "Phường Trang Hạ", "Phường Châu Khê", "Phường Đồng Kỵ"
        ],
        "Huyện Quế Võ": [
          "Thị trấn Phố Mới", "Xã Việt Hùng", "Xã Phù Lãng",
          "Xã Phượng Mao", "Xã Phương Liễu", "Xã Yên Giả"
        ],
        "Huyện Gia Bình": [
          "Thị trấn Gia Bình", "Xã Đại Lai", "Xã Đông Cứu",
          "Xã Nhân Thắng", "Xã Quỳnh Phú", "Xã Vạn Ninh"
        ],
        "Huyện Lương Tài": [
          "Thị trấn Thứa", "Xã An Thịnh", "Xã Bình Định",
          "Xã Lâm Thao", "Xã Phú Hòa", "Xã Tân Lãng"
        ],
        "Huyện Tiên Du": [
          "Thị trấn Lim", "Xã Cảnh Hưng", "Xã Đại Đồng",
          "Xã Hiên Vân", "Xã Lạc Vệ", "Xã Nội Duệ"
        ],
        "Huyện Thuận Thành": [
          "Thị trấn Hồ", "Xã An Bình", "Xã Gia Đông",
          "Xã Hà Mãn", "Xã Song Hồ", "Xã Xuân Lâm"
        ],
        "Huyện Yên Phong": [
          "Thị trấn Chờ", "Xã Đông Phong", "Xã Dũng Liệt",
          "Xã Long Châu", "Xã Tam Đa", "Xã Văn Môn"
        ]
      },
      "Bến Tre": {
        "Thành phố Bến Tre": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6",
          "Xã Bình Phú", "Xã Mỹ Thành", "Xã Phú Hưng", "Xã Sơn Đông"
        ],
        "Huyện Châu Thành": [
          "Xã An Hiệp", "Xã An Hóa", "Xã Giao Long", "Xã Giao Hòa",
          "Xã Phú Đức", "Xã Phú Túc", "Xã Quới Sơn"
        ],
        "Huyện Giồng Trôm": [
          "Thị trấn Giồng Trôm", "Xã Bình Thành", "Xã Hưng Phong",
          "Xã Lương Hòa", "Xã Lương Phú", "Xã Mỹ Thạnh", "Xã Tân Thanh"
        ],
        "Huyện Mỏ Cày Bắc": [
          "Xã Hưng Khánh Trung A", "Xã Khánh Thạnh Tân", "Xã Nhuận Phú Tân",
          "Xã Phú Mỹ", "Xã Phước Mỹ Trung", "Xã Tân Phú Tây"
        ],
        "Huyện Mỏ Cày Nam": [
          "Thị trấn Mỏ Cày", "Xã An Định", "Xã An Thạnh", "Xã Bình Khánh Đông",
          "Xã Định Thủy", "Xã Hương Mỹ", "Xã Tân Hội"
        ],
        "Huyện Chợ Lách": [
          "Thị trấn Chợ Lách", "Xã Hòa Nghĩa", "Xã Hưng Khánh Trung B",
          "Xã Long Thới", "Xã Phú Phụng", "Xã Sơn Định"
        ],
        "Huyện Ba Tri": [
          "Thị trấn Ba Tri", "Xã An Đức", "Xã An Hiệp", "Xã An Ngãi Trung",
          "Xã Mỹ Hòa", "Xã Phước Ngãi", "Xã Vĩnh Hòa"
        ],
        "Huyện Thạnh Phú": [
          "Thị trấn Thạnh Phú", "Xã An Nhơn", "Xã An Thuận", "Xã Bình Thạnh",
          "Xã Giao Thạnh", "Xã Hòa Lợi", "Xã Thạnh Hải"
        ]
      },
      "Bình Định": {
        "Thành phố Quy Nhơn": [
          "Phường Lê Lợi", "Phường Trần Phú", "Phường Ngô Mây",
          "Phường Ghềnh Ráng", "Phường Quang Trung", "Phường Hải Cảng",
          "Xã Nhơn Lý", "Xã Nhơn Hải", "Xã Phước Mỹ"
        ],
        "Thị xã An Nhơn": [
          "Phường Bình Định", "Phường Đập Đá", "Phường Nhơn Thành",
          "Xã Nhơn An", "Xã Nhơn Phong", "Xã Nhơn Hạnh"
        ],
        "Thị xã Hoài Nhơn": [
          "Phường Bồng Sơn", "Phường Tam Quan", "Phường Tam Quan Bắc",
          "Xã Hoài Hảo", "Xã Hoài Phú", "Xã Hoài Thanh"
        ],
        "Huyện Tuy Phước": [
          "Thị trấn Tuy Phước", "Thị trấn Diêu Trì", "Xã Phước Sơn",
          "Xã Phước Hòa", "Xã Phước Thắng"
        ],
        "Huyện Phù Cát": [
          "Thị trấn Ngô Mây", "Xã Cát Hanh", "Xã Cát Tiến",
          "Xã Cát Chánh", "Xã Cát Trinh"
        ],
        "Huyện Phù Mỹ": [
          "Thị trấn Phù Mỹ", "Thị trấn Bình Dương", "Xã Mỹ Chánh",
          "Xã Mỹ Quang", "Xã Mỹ Hòa"
        ],
        "Huyện Tây Sơn": [
          "Thị trấn Phú Phong", "Xã Bình Tường", "Xã Tây Vinh",
          "Xã Tây Giang", "Xã Tây Thuận"
        ],
        "Huyện Vĩnh Thạnh": [
          "Thị trấn Vĩnh Thạnh", "Xã Vĩnh Quang", "Xã Vĩnh Kim",
          "Xã Vĩnh Hảo"
        ],
        "Huyện Vân Canh": [
          "Thị trấn Vân Canh", "Xã Canh Hiệp", "Xã Canh Thuận",
          "Xã Canh Liên"
        ],
        "Huyện An Lão": [
          "Thị trấn An Lão", "Xã An Hưng", "Xã An Vinh",
          "Xã An Quang"
        ]
      },
      "Bình Phước": {
        "Thành phố Đồng Xoài": [
          "Phường Tân Bình", "Phường Tân Phú", "Phường Tân Đồng",
          "Phường Tân Xuân", "Phường Tiến Thành", "Xã Tiến Hưng"
        ],
        "Thị xã Phước Long": [
          "Phường Long Thủy", "Phường Thác Mơ", "Phường Sơn Giang",
          "Xã Long Giang", "Xã Phước Tín"
        ],
        "Thị xã Bình Long": [
          "Phường An Lộc", "Phường Hưng Chiến", "Phường Phú Đức",
          "Xã Thanh Phú", "Xã Thanh Lương"
        ],
        "Huyện Đồng Phú": [
          "Thị trấn Tân Phú", "Xã Tân Lập", "Xã Tân Hòa", "Xã Tân Tiến"
        ],
        "Huyện Bù Đăng": [
          "Thị trấn Đức Phong", "Xã Bom Bo", "Xã Đoàn Kết", "Xã Thọ Sơn"
        ],
        "Huyện Bù Đốp": [
          "Thị trấn Thanh Bình", "Xã Tân Tiến", "Xã Thanh Hòa", "Xã Phước Thiện"
        ],
        "Huyện Bù Gia Mập": [
          "Xã Bù Gia Mập", "Xã Đa Kia", "Xã Phú Nghĩa", "Xã Phước Minh"
        ],
        "Huyện Chơn Thành": [
          "Thị trấn Chơn Thành", "Xã Minh Hưng", "Xã Minh Long", "Xã Minh Lập"
        ],
        "Huyện Hớn Quản": [
          "Thị trấn Tân Khai", "Xã An Khương", "Xã Tân Hiệp", "Xã Thanh An"
        ],
        "Huyện Lộc Ninh": [
          "Thị trấn Lộc Ninh", "Xã Lộc Hòa", "Xã Lộc Thái", "Xã Lộc Tấn"
        ]
      },
      "Bình Thuận": {
        "Thành phố Phan Thiết": [
          "Phường Bình Hưng", "Phường Đức Long", "Phường Đức Nghĩa",
          "Phường Hàm Tiến", "Phường Lạc Đạo", "Phường Mũi Né",
          "Xã Thiện Nghiệp", "Xã Phong Nẫm", "Xã Tiến Lợi"
        ],
        "Thị xã La Gi": [
          "Phường Phước Hội", "Phường Tân An", "Phường Tân Thiện",
          "Xã Tân Hải", "Xã Tân Phước", "Xã Tân Tiến"
        ],
        "Huyện Tuy Phong": [
          "Thị trấn Liên Hương", "Thị trấn Phan Rí Cửa",
          "Xã Bình Thạnh", "Xã Hòa Minh", "Xã Chí Công"
        ],
        "Huyện Bắc Bình": [
          "Thị trấn Chợ Lầu", "Xã Hồng Thái", "Xã Sông Lũy",
          "Xã Phan Hòa", "Xã Hải Ninh"
        ],
        "Huyện Hàm Thuận Bắc": [
          "Thị trấn Ma Lâm", "Xã Hàm Chính", "Xã Hàm Thắng",
          "Xã Thuận Hòa", "Xã Thuận Minh"
        ],
        "Huyện Hàm Thuận Nam": [
          "Thị trấn Thuận Nam", "Xã Hàm Minh", "Xã Mương Mán",
          "Xã Tân Lập", "Xã Tân Thuận"
        ],
        "Huyện Hàm Tân": [
          "Thị trấn Tân Nghĩa", "Xã Sơn Mỹ", "Xã Tân Đức",
          "Xã Tân Minh", "Xã Tân Phúc"
        ],
        "Huyện Đức Linh": [
          "Thị trấn Võ Xu", "Thị trấn Đức Tài",
          "Xã Đức Hạnh", "Xã Đông Hà", "Xã Trà Tân"
        ],
        "Huyện Tánh Linh": [
          "Thị trấn Lạc Tánh", "Xã Bắc Ruộng", "Xã Đức Bình",
          "Xã Đồng Kho", "Xã Huy Khiêm"
        ],
        "Huyện Phú Quý": [
          "Xã Long Hải", "Xã Ngũ Phụng", "Xã Tam Thanh"
        ]
      },
      "Cà Mau": {
        "Thành phố Cà Mau": [
          "Phường 1", "Phường 2", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9",
          "Xã Định Bình", "Xã Hòa Tân", "Xã Hòa Thành", "Xã Lý Văn Lâm", "Xã Tắc Vân", "Xã Tân Thành"
        ],
        "Huyện Thới Bình": [
          "Thị trấn Thới Bình", "Xã Biển Bạch", "Xã Biển Bạch Đông", "Xã Hồ Thị Kỷ", "Xã Tân Bằng"
        ],
        "Huyện U Minh": [
          "Thị trấn U Minh", "Xã Khánh An", "Xã Khánh Hòa", "Xã Khánh Hội", "Xã Khánh Lâm"
        ],
        "Huyện Trần Văn Thời": [
          "Thị trấn Trần Văn Thời", "Thị trấn Sông Đốc", "Xã Khánh Bình", "Xã Khánh Bình Đông", "Xã Lợi An"
        ],
        "Huyện Cái Nước": [
          "Thị trấn Cái Nước", "Xã Đông Hưng", "Xã Đông Thới", "Xã Hòa Mỹ", "Xã Lương Thế Trân"
        ],
        "Huyện Đầm Dơi": [
          "Thị trấn Đầm Dơi", "Xã Ngọc Chánh", "Xã Tạ An Khương", "Xã Tân Duyệt", "Xã Tân Dân"
        ],
        "Huyện Phú Tân": [
          "Thị trấn Cái Đôi Vàm", "Xã Nguyễn Việt Khái", "Xã Phú Mỹ", "Xã Phú Tân", "Xã Việt Thắng"
        ],
        "Huyện Ngọc Hiển": [
          "Thị trấn Rạch Gốc", "Xã Đất Mũi", "Xã Tân Ân", "Xã Tân Ân Tây", "Xã Viên An"
        ],
        "Huyện Năm Căn": [
          "Thị trấn Năm Căn", "Xã Đất Mới", "Xã Hàm Rồng", "Xã Hàng Vịnh", "Xã Lâm Hải"
        ]
      },
      "Cao Bằng": {
        "Thành phố Cao Bằng": [
          "Phường Đề Thám", "Phường Hòa Chung", "Phường Hợp Giang",
          "Phường Ngọc Xuân", "Phường Sông Bằng", "Phường Sông Hiến",
          "Phường Tân Giang", "Xã Chu Trinh", "Xã Hưng Đạo", "Xã Vĩnh Quang"
        ],
        "Huyện Bảo Lạc": [
          "Thị trấn Bảo Lạc", "Xã Cô Ba", "Xã Hồng An", "Xã Hưng Đạo", "Xã Khánh Xuân"
        ],
        "Huyện Bảo Lâm": [
          "Thị trấn Pác Miầu", "Xã Lý Bôn", "Xã Nam Quang", "Xã Quảng Lâm", "Xã Yên Thổ"
        ],
        "Huyện Hạ Lang": [
          "Thị trấn Thanh Nhật", "Xã Cô Ngân", "Xã Đồng Loan", "Xã Lý Quốc", "Xã Thái Đức"
        ],
        "Huyện Hòa An": [
          "Thị trấn Nước Hai", "Xã Dân Chủ", "Xã Đức Long", "Xã Hồng Việt", "Xã Trương Lương"
        ],
        "Huyện Hà Quảng": [
          "Thị trấn Xuân Hòa", "Thị trấn Thông Nông", "Xã Cải Viên", "Xã Sóc Hà", "Xã Trường Hà"
        ],
        "Huyện Nguyên Bình": [
          "Thị trấn Nguyên Bình", "Thị trấn Tĩnh Túc", "Xã Hoa Thám", "Xã Thịnh Vượng", "Xã Vũ Nông"
        ],
        "Huyện Quảng Hòa": [
          "Thị trấn Hòa Thuận", "Thị trấn Quảng Uyên", "Xã Độc Lập", "Xã Hồng Quang", "Xã Phi Hải"
        ],
        "Huyện Thạch An": [
          "Thị trấn Đông Khê", "Xã Đức Long", "Xã Lê Lai", "Xã Thụy Hùng", "Xã Trọng Con"
        ],
        "Huyện Trùng Khánh": [
          "Thị trấn Trùng Khánh", "Xã Cao Chương", "Xã Đình Phong", "Xã Đoài Khôn", "Xã Khâm Thành"
        ]
      },
      "Điện Biên": {
        "Thành phố Điện Biên Phủ": [
          "Phường Him Lam", "Phường Mường Thanh", "Phường Nam Thanh",
          "Phường Noong Bua", "Phường Tân Thanh", "Phường Thanh Bình",
          "Xã Mường Phăng", "Xã Pá Khoang", "Xã Thanh Minh", "Xã Tà Lèng"
        ],
        "Thị xã Mường Lay": [
          "Phường Na Lay", "Xã Lay Nưa"
        ],
        "Huyện Điện Biên": [
          "Xã Mường Lói", "Xã Mường Nhà", "Xã Na Ư", "Xã Nà Nhạn", "Xã Nà Tấu", "Xã Noong Luống"
        ],
        "Huyện Điện Biên Đông": [
          "Thị trấn Điện Biên Đông", "Xã Keo Lôm", "Xã Mường Luân", "Xã Phình Giàng", "Xã Tìa Dình"
        ],
        "Huyện Mường Ảng": [
          "Thị trấn Mường Ảng", "Xã Ẳng Cang", "Xã Ẳng Nưa", "Xã Mường Đăng", "Xã Ngối Cáy"
        ],
        "Huyện Mường Chà": [
          "Thị trấn Mường Chà", "Xã Hừa Ngài", "Xã Mường Tùng", "Xã Nậm Nèn", "Xã Sá Tổng"
        ],
        "Huyện Mường Nhé": [
          "Xã Chung Chải", "Xã Huổi Lếch", "Xã Mường Nhé", "Xã Nậm Vì", "Xã Sín Thầu"
        ],
        "Huyện Nậm Pồ": [
          "Xã Chà Cang", "Xã Chà Nưa", "Xã Na Cô Sa", "Xã Nà Khoa", "Xã Nà Hỳ"
        ],
        "Huyện Tủa Chùa": [
          "Thị trấn Tủa Chùa", "Xã Huổi Só", "Xã Lao Xả Phình", "Xã Mường Báng", "Xã Xá Nhè"
        ],
        "Huyện Tuần Giáo": [
          "Thị trấn Tuần Giáo", "Xã Chiềng Sinh", "Xã Mùn Chung", "Xã Nà Sáy", "Xã Quài Cang"
        ]
      },
      "Đắk Nông": {
        "Thành phố Gia Nghĩa": [
          "Phường Nghĩa Đức", "Phường Nghĩa Phú", "Phường Nghĩa Thành",
          "Phường Nghĩa Tân", "Xã Đắk Nia", "Xã Quảng Thành"
        ],
        "Huyện Cư Jút": [
          "Thị trấn Ea T’ling", "Xã Cư Knia", "Xã Đắk D’rông", "Xã Đắk Wil", "Xã Ea Pô"
        ],
        "Huyện Đắk Glong": [
          "Xã Đắk Plao", "Xã Đắk R’măng", "Xã Quảng Hòa", "Xã Quảng Khê", "Xã Quảng Sơn"
        ],
        "Huyện Đắk Mil": [
          "Thị trấn Đắk Mil", "Xã Đắk Gằn", "Xã Đắk Lao", "Xã Đức Mạnh", "Xã Thuận An"
        ],
        "Huyện Đắk R’lấp": [
          "Thị trấn Kiến Đức", "Xã Đắk Ru", "Xã Đắk Wer", "Xã Kiến Thành", "Xã Nhân Cơ"
        ],
        "Huyện Krông Nô": [
          "Thị trấn Đắk Mâm", "Xã Buôn Choah", "Xã Đắk Drô", "Xã Đắk Nang", "Xã Nam Đà"
        ],
        "Huyện Tuy Đức": [
          "Xã Đắk Buk So", "Xã Đắk Ngo", "Xã Quảng Tâm", "Xã Quảng Trực", "Xã Quảng Tân"
        ]
      },
      "Đồng Tháp": {
        "Thành phố Cao Lãnh": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 6", "Phường Mỹ Phú",
          "Xã Hòa An", "Xã Mỹ Trà", "Xã Mỹ Tân", "Xã Tân Thuận Đông", "Xã Tân Thuận Tây"
        ],
        "Thành phố Sa Đéc": [
          "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường Tân Quy Đông",
          "Phường An Hòa", "Xã Tân Khánh Đông", "Xã Tân Phú Đông"
        ],
        "Thị xã Hồng Ngự": [
          "Phường An Lạc", "Phường An Bình B", "Phường An Bình A", "Xã Tân Hội",
          "Xã Bình Thạnh", "Xã Tân Bình", "Xã Tân Hội Trung"
        ],
        "Huyện Cao Lãnh": [
          "Thị trấn Mỹ Thọ", "Xã Mỹ Xương", "Xã Bình Hàng Trung", "Xã Gáo Giồng",
          "Xã Ba Sao", "Xã Phương Thịnh", "Xã Nhị Mỹ"
        ],
        "Huyện Châu Thành": [
          "Thị trấn Cái Tàu Hạ", "Xã An Hiệp", "Xã An Khánh", "Xã Tân Nhuận Đông",
          "Xã Tân Bình", "Xã Tân Phú", "Xã Tân Phú Trung"
        ],
        "Huyện Hồng Ngự": [
          "Thị trấn Thường Thới Tiền", "Xã Thường Phước 1", "Xã Thường Phước 2",
          "Xã Thường Lạc", "Xã Long Khánh A", "Xã Long Khánh B", "Xã Long Thuận"
        ],
        "Huyện Lai Vung": [
          "Thị trấn Lai Vung", "Xã Tân Dương", "Xã Tân Hòa", "Xã Tân Phước",
          "Xã Hòa Long", "Xã Hòa Thành", "Xã Long Hậu"
        ],
        "Huyện Lấp Vò": [
          "Thị trấn Lấp Vò", "Xã Mỹ An Hưng A", "Xã Mỹ An Hưng B", "Xã Tân Mỹ",
          "Xã Hội An Đông", "Xã Vĩnh Thạnh", "Xã Long Hưng A"
        ],
        "Huyện Tam Nông": [
          "Thị trấn Tràm Chim", "Xã Phú Cường", "Xã Phú Ninh", "Xã Phú Thành B",
          "Xã Tân Công Sính", "Xã Hòa Bình", "Xã An Hòa"
        ],
        "Huyện Tân Hồng": [
          "Thị trấn Sa Rài", "Xã Tân Hộ Cơ", "Xã Tân Phước", "Xã Thông Bình",
          "Xã Bình Phú", "Xã Tân Công Chí", "Xã An Phước"
        ],
        "Huyện Thanh Bình": [
          "Thị trấn Thanh Bình", "Xã Tân Quới", "Xã Tân Hòa", "Xã An Phong",
          "Xã Bình Tấn", "Xã Tân Long", "Xã Tân Huề"
        ],
        "Huyện Tháp Mười": [
          "Thị trấn Mỹ An", "Xã Đốc Binh Kiều", "Xã Mỹ Đông", "Xã Phú Điền",
          "Xã Trường Xuân", "Xã Thạnh Lợi", "Xã Thanh Mỹ"
        ]
      },
      "Gia Lai": {
        "Thành phố Pleiku": [
          "Phường Diên Hồng", "Phường Ia Kring", "Phường Hoa Lư", "Phường Phù Đổng",
          "Phường Tây Sơn", "Phường Yên Đỗ", "Phường Trà Bá", "Phường Thắng Lợi",
          "Xã Chư Á", "Xã Biển Hồ", "Xã Gào", "Xã Ia Kênh"
        ],
        "Thị xã An Khê": [
          "Phường An Bình", "Phường An Phú", "Phường Tây Sơn", "Phường Ngô Mây",
          "Xã Cửu An", "Xã Song An", "Xã Thành An"
        ],
        "Thị xã Ayun Pa": [
          "Phường Cheo Reo", "Phường Hòa Bình", "Phường Đoàn Kết", "Phường Sông Bờ",
          "Xã Ia RTô", "Xã Chư Băh"
        ],
        "Huyện Chư Păh": [
          "Thị trấn Phú Hòa", "Xã Ia Ka", "Xã Ia Nhin", "Xã Nghĩa Hòa",
          "Xã Nghĩa Hưng", "Xã Hà Tây", "Xã Chư Đăng Ya"
        ],
        "Huyện Chư Prông": [
          "Thị trấn Chư Prông", "Xã Ia Drăng", "Xã Ia Kly", "Xã Ia Phìn",
          "Xã Ia Pia", "Xã Thăng Hưng", "Xã Bàu Cạn"
        ],
        "Huyện Chư Sê": [
          "Thị trấn Chư Sê", "Xã Ia Glai", "Xã Hbông", "Xã Ia Blang",
          "Xã Bar Măih", "Xã Ayun", "Xã Kông Htok"
        ],
        "Huyện Đắk Đoa": [
          "Thị trấn Đắk Đoa", "Xã Hà Đông", "Xã Hải Yang", "Xã H’Neng",
          "Xã Glar", "Xã Trang", "Xã Tân Bình"
        ],
        "Huyện Đăk Pơ": [
          "Thị trấn Đăk Pơ", "Xã An Thành", "Xã Tân An", "Xã Yang Bắc",
          "Xã Cư An", "Xã Phú An", "Xã Kon Gang"
        ],
        "Huyện Đức Cơ": [
          "Thị trấn Chư Ty", "Xã Ia Dơk", "Xã Ia Din", "Xã Ia Kla",
          "Xã Ia Krêl", "Xã Ia Dom", "Xã Ia Lang"
        ],
        "Huyện Ia Grai": [
          "Thị trấn Ia Kha", "Xã Ia Hrung", "Xã Ia Bá", "Xã Ia Yok",
          "Xã Ia Chia", "Xã Ia Tô", "Xã Ia O"
        ],
        "Huyện Ia Pa": [
          "Thị trấn Ia K’đăm", "Xã Ia Trôk", "Xã Ia Mrơn", "Xã Ia Tul",
          "Xã Chư Răng", "Xã Kim Tân", "Xã Ia RMok"
        ],
        "Huyện Kbang": [
          "Thị trấn Kbang", "Xã Kon Pne", "Xã Đăk Roong", "Xã Krong",
          "Xã Lơ Ku", "Xã Nghĩa An", "Xã Đông"
        ],
        "Huyện Kông Chro": [
          "Thị trấn Kông Chro", "Xã Chư Krêy", "Xã Đăk Song", "Xã Đăk Pling",
          "Xã Sró", "Xã An Trung", "Xã Ya Ma"
        ],
        "Huyện Krông Pa": [
          "Thị trấn Phú Túc", "Xã Ia Rsai", "Xã Ia Rmok", "Xã Ia Mlah",
          "Xã Ia Hdreh", "Xã Krông Năng", "Xã Chư Drăng"
        ],
        "Huyện Mang Yang": [
          "Thị trấn Kon Dơng", "Xã Ayun", "Xã Đăk Jơ Ta", "Xã Đăk Ta Ley",
          "Xã Đê Ar", "Xã Kon Chiêng", "Xã Lơ Pang"
        ],
        "Huyện Phú Thiện": [
          "Thị trấn Phú Thiện", "Xã Ayun Hạ", "Xã Chrôh Pơnan", "Xã Ia Ake",
          "Xã Ia Hiao", "Xã Ia Peng", "Xã Ia Sol"
        ]
      },
      "Hải Dương": {
        "Thành phố Hải Dương": [
          "Phường Bình Hàn", "Phường Cẩm Thượng", "Phường Hải Tân", "Phường Lê Thanh Nghị",
          "Phường Nguyễn Trãi", "Phường Phạm Ngũ Lão", "Phường Tân Bình", "Xã Gia Xuyên", "Xã Liên Hồng"
        ],
        "Thành phố Chí Linh": [
          "Phường Sao Đỏ", "Phường Cộng Hòa", "Phường Văn Đức", "Phường Thái Học",
          "Xã Bắc An", "Xã Hoàng Hoa Thám", "Xã Lê Lợi", "Xã Hưng Đạo"
        ],
        "Thị xã Kinh Môn": [
          "Phường An Lưu", "Phường Hiến Thành", "Phường Minh Tân", "Phường Phú Thứ",
          "Xã Thượng Quận", "Xã Lạc Long", "Xã Hiệp Hòa"
        ],
        "Huyện Nam Sách": [
          "Thị trấn Nam Sách", "Xã An Lâm", "Xã Cộng Hòa", "Xã Hợp Tiến",
          "Xã Nam Hưng", "Xã Quốc Tuấn", "Xã Thái Tân"
        ],
        "Huyện Gia Lộc": [
          "Thị trấn Gia Lộc", "Xã Gia Hòa", "Xã Gia Khánh", "Xã Gia Xuyên",
          "Xã Nhật Tân", "Xã Phạm Trấn", "Xã Yết Kiêu"
        ],
        "Huyện Tứ Kỳ": [
          "Thị trấn Tứ Kỳ", "Xã An Thanh", "Xã Chí Minh", "Xã Đại Sơn",
          "Xã Hà Kỳ", "Xã Hưng Đạo", "Xã Quang Phục"
        ],
        "Huyện Cẩm Giàng": [
          "Thị trấn Cẩm Giàng", "Xã Cao An", "Xã Cẩm Điền", "Xã Cẩm Hưng",
          "Xã Cẩm Phúc", "Xã Cẩm Vũ", "Xã Thạch Lỗi"
        ],
        "Huyện Thanh Hà": [
          "Thị trấn Thanh Hà", "Xã Cẩm Chế", "Xã Hồng Lạc", "Xã Liên Mạc",
          "Xã Thanh An", "Xã Thanh Khê", "Xã Tân Việt"
        ],
        "Huyện Thanh Miện": [
          "Thị trấn Thanh Miện", "Xã Đoàn Kết", "Xã Hồng Phong", "Xã Lê Hồng",
          "Xã Ngô Quyền", "Xã Tân Trào", "Xã Thanh Giang"
        ],
        "Huyện Ninh Giang": [
          "Thị trấn Ninh Giang", "Xã Đồng Tâm", "Xã Hồng Đức", "Xã Hồng Phong",
          "Xã Kiến Quốc", "Xã Nghĩa An", "Xã Tân Hương"
        ],
        "Huyện Bình Giang": [
          "Thị trấn Kẻ Sặt", "Xã Bình Minh", "Xã Cổ Bì", "Xã Hùng Thắng",
          "Xã Long Xuyên", "Xã Thái Học", "Xã Vĩnh Tuy"
        ]
      },
      "Hà Nam": {
        "Thành phố Phủ Lý": [
          "Phường Minh Khai", "Phường Hai Bà Trưng", "Phường Trần Hưng Đạo",
          "Phường Lê Hồng Phong", "Phường Quang Trung", "Phường Lam Hạ",
          "Xã Phù Vân", "Xã Tiên Hiệp", "Xã Tiên Tân", "Xã Kim Bình"
        ],
        "Huyện Duy Tiên": [
          "Phường Hòa Mạc", "Phường Đồng Văn", "Xã Châu Giang", "Xã Yên Bắc",
          "Xã Mộc Nam", "Xã Mộc Bắc", "Xã Tiên Ngoại"
        ],
        "Huyện Kim Bảng": [
          "Thị trấn Ba Sao", "Thị trấn Quế", "Xã Thanh Sơn", "Xã Tân Sơn",
          "Xã Thi Sơn", "Xã Liên Sơn", "Xã Ngọc Sơn"
        ],
        "Huyện Thanh Liêm": [
          "Thị trấn Tân Thanh", "Xã Thanh Thủy", "Xã Thanh Nghị", "Xã Liêm Sơn",
          "Xã Thanh Phong", "Xã Thanh Hà", "Xã Thanh Hương"
        ],
        "Huyện Bình Lục": [
          "Thị trấn Bình Mỹ", "Xã An Đổ", "Xã An Lão", "Xã Bồ Đề",
          "Xã Tiêu Động", "Xã Tràng An", "Xã Ngọc Lũ"
        ],
        "Huyện Lý Nhân": [
          "Thị trấn Vĩnh Trụ", "Xã Chân Lý", "Xã Đức Lý", "Xã Hợp Lý",
          "Xã Bắc Lý", "Xã Trung Lý", "Xã Xuân Khê"
        ]
      },
      "Hải Dương": {
        "Thành phố Hải Dương": [
          "Phường Bình Hàn", "Phường Cẩm Thượng", "Phường Hải Tân", "Phường Lê Thanh Nghị",
          "Phường Nguyễn Trãi", "Phường Phạm Ngũ Lão", "Phường Tân Bình", "Xã Gia Xuyên", "Xã Liên Hồng"
        ],
        "Thành phố Chí Linh": [
          "Phường Sao Đỏ", "Phường Cộng Hòa", "Phường Văn Đức", "Phường Thái Học",
          "Xã Bắc An", "Xã Hoàng Hoa Thám", "Xã Lê Lợi", "Xã Hưng Đạo"
        ],
        "Thị xã Kinh Môn": [
          "Phường An Lưu", "Phường Hiến Thành", "Phường Minh Tân", "Phường Phú Thứ",
          "Xã Thượng Quận", "Xã Lạc Long", "Xã Hiệp Hòa"
        ],
        "Huyện Nam Sách": [
          "Thị trấn Nam Sách", "Xã An Lâm", "Xã Cộng Hòa", "Xã Hợp Tiến",
          "Xã Nam Hưng", "Xã Quốc Tuấn", "Xã Thái Tân"
        ],
        "Huyện Gia Lộc": [
          "Thị trấn Gia Lộc", "Xã Gia Hòa", "Xã Gia Khánh", "Xã Gia Xuyên",
          "Xã Nhật Tân", "Xã Phạm Trấn", "Xã Yết Kiêu"
        ],
        "Huyện Tứ Kỳ": [
          "Thị trấn Tứ Kỳ", "Xã An Thanh", "Xã Chí Minh", "Xã Đại Sơn",
          "Xã Hà Kỳ", "Xã Hưng Đạo", "Xã Quang Phục"
        ],
        "Huyện Cẩm Giàng": [
          "Thị trấn Cẩm Giàng", "Xã Cao An", "Xã Cẩm Điền", "Xã Cẩm Hưng",
          "Xã Cẩm Phúc", "Xã Cẩm Vũ", "Xã Thạch Lỗi"
        ],
        "Huyện Thanh Hà": [
          "Thị trấn Thanh Hà", "Xã Cẩm Chế", "Xã Hồng Lạc", "Xã Liên Mạc",
          "Xã Thanh An", "Xã Thanh Khê", "Xã Tân Việt"
        ],
        "Huyện Thanh Miện": [
          "Thị trấn Thanh Miện", "Xã Đoàn Kết", "Xã Hồng Phong", "Xã Lê Hồng",
          "Xã Ngô Quyền", "Xã Tân Trào", "Xã Thanh Giang"
        ],
        "Huyện Ninh Giang": [
          "Thị trấn Ninh Giang", "Xã Đồng Tâm", "Xã Hồng Đức", "Xã Hồng Phong",
          "Xã Kiến Quốc", "Xã Nghĩa An", "Xã Tân Hương"
        ],
        "Huyện Bình Giang": [
          "Thị trấn Kẻ Sặt", "Xã Bình Minh", "Xã Cổ Bì", "Xã Hùng Thắng",
          "Xã Long Xuyên", "Xã Thái Học", "Xã Vĩnh Tuy"
        ]
      },
      "Hưng Yên": {
        "Thành phố Hưng Yên": [
          "Phường Hiến Nam", "Phường Lê Lợi", "Phường Minh Khai", "Phường Quang Trung",
          "Xã Bảo Khê", "Xã Hồng Nam", "Xã Liên Phương", "Xã Trung Nghĩa"
        ],
        "Thị xã Mỹ Hào": [
          "Phường Bần Yên Nhân", "Phường Dị Sử", "Phường Minh Đức", "Phường Nhân Hòa",
          "Xã Cẩm Xá", "Xã Hòa Phong", "Xã Phan Đình Phùng"
        ],
        "Huyện Văn Lâm": [
          "Thị trấn Như Quỳnh", "Xã Đại Đồng", "Xã Lạc Đạo", "Xã Trưng Trắc",
          "Xã Việt Hưng", "Xã Tân Quang", "Xã Phù Ủng"
        ],
        "Huyện Văn Giang": [
          "Thị trấn Văn Giang", "Xã Cửu Cao", "Xã Long Hưng", "Xã Liên Nghĩa",
          "Xã Tân Tiến", "Xã Xuân Quan", "Xã Vĩnh Khúc"
        ],
        "Huyện Yên Mỹ": [
          "Thị trấn Yên Mỹ", "Xã Đồng Than", "Xã Giai Phạm", "Xã Nghĩa Hiệp",
          "Xã Trung Hòa", "Xã Tân Lập", "Xã Thanh Long"
        ],
        "Huyện Mỹ Hào": [
          "Xã Bạch Sam", "Xã Cẩm Xá", "Xã Dương Quang", "Xã Hưng Long",
          "Xã Phùng Chí Kiên", "Xã Nhân Hòa"
        ],
        "Huyện Khoái Châu": [
          "Thị trấn Khoái Châu", "Xã An Vĩ", "Xã Bình Minh", "Xã Dân Tiến",
          "Xã Hàm Tử", "Xã Hồng Tiến", "Xã Tứ Dân"
        ],
        "Huyện Ân Thi": [
          "Thị trấn Ân Thi", "Xã Bắc Sơn", "Xã Cẩm Ninh", "Xã Đào Dương",
          "Xã Hoàng Hoa Thám", "Xã Nguyễn Trãi", "Xã Vân Du"
        ],
        "Huyện Kim Động": [
          "Thị trấn Lương Bằng", "Xã Đồng Thanh", "Xã Hùng An", "Xã Nghĩa Dân",
          "Xã Ngọc Thanh", "Xã Song Mai", "Xã Vũ Xá"
        ],
        "Huyện Phù Cừ": [
          "Thị trấn Trần Cao", "Xã Đình Cao", "Xã Nhật Quang", "Xã Quang Hưng",
          "Xã Tam Đa", "Xã Tống Trân", "Xã Tiên Tiến"
        ]
      },
      "Kiên Giang": {
        "Thành phố Rạch Giá": [
          "Phường Vĩnh Thanh Vân", "Phường Vĩnh Thanh", "Phường Vĩnh Lạc",
          "Phường Vĩnh Quang", "Phường An Hòa", "Phường Rạch Sỏi", "Xã Vĩnh Thông"
        ],
        "Thành phố Hà Tiên": [
          "Phường Đông Hồ", "Phường Tô Châu", "Phường Bình San",
          "Phường Pháo Đài", "Xã Mỹ Đức", "Xã Thuận Yên"
        ],
        "Huyện Phú Quốc": [
          "Phường Dương Đông", "Phường An Thới", "Xã Cửa Cạn", "Xã Cửa Dương",
          "Xã Gành Dầu", "Xã Hàm Ninh", "Xã Bãi Thơm"
        ],
        "Huyện An Biên": [
          "Thị trấn Thứ Ba", "Xã Đông Yên", "Xã Tây Yên", "Xã Nam Yên",
          "Xã Nam Thái", "Xã Đông Thái", "Xã Tây Yên A"
        ],
        "Huyện An Minh": [
          "Thị trấn Thứ Mười Một", "Xã Thuận Hòa", "Xã Đông Hòa", "Xã Vân Khánh",
          "Xã Vân Khánh Tây", "Xã Tân Thạnh", "Xã Đông Thạnh"
        ],
        "Huyện Châu Thành": [
          "Thị trấn Minh Lương", "Xã Bình An", "Xã Mong Thọ", "Xã Mong Thọ B",
          "Xã Vĩnh Hòa Hiệp", "Xã Vĩnh Hòa Phú", "Xã Thạnh Lộc"
        ],
        "Huyện Giang Thành": [
          "Xã Tân Khánh Hòa", "Xã Phú Lợi", "Xã Vĩnh Điều",
          "Xã Vĩnh Phú", "Xã Vĩnh Trường"
        ],
        "Huyện Gò Quao": [
          "Thị trấn Gò Quao", "Xã Vĩnh Hòa Hưng Bắc", "Xã Vĩnh Hòa Hưng Nam",
          "Xã Thủy Liểu", "Xã Vĩnh Phước A", "Xã Vĩnh Tuy", "Xã Định An"
        ],
        "Huyện Hòn Đất": [
          "Thị trấn Hòn Đất", "Thị trấn Sóc Sơn", "Xã Bình Giang", "Xã Bình Sơn",
          "Xã Lình Huỳnh", "Xã Mỹ Hiệp Sơn", "Xã Mỹ Lâm"
        ],
        "Huyện Kiên Hải": [
          "Xã Hòn Tre", "Xã Lại Sơn", "Xã An Sơn", "Xã Nam Du"
        ],
        "Huyện Kiên Lương": [
          "Thị trấn Kiên Lương", "Xã Bình An", "Xã Dương Hòa", "Xã Hòa Điền",
          "Xã Hòn Nghệ"
        ],
        "Huyện Tân Hiệp": [
          "Thị trấn Tân Hiệp", "Xã Tân Hiệp A", "Xã Tân Hiệp B", "Xã Tân Hòa",
          "Xã Thạnh Đông", "Xã Thạnh Đông A", "Xã Thạnh Trị"
        ],
        "Huyện U Minh Thượng": [
          "Xã An Minh Bắc", "Xã Hòa Chánh", "Xã Minh Thuận",
          "Xã Thạnh Yên", "Xã Thạnh Yên A"
        ],
        "Huyện Vĩnh Thuận": [
          "Thị trấn Vĩnh Thuận", "Xã Vĩnh Bình Bắc", "Xã Vĩnh Bình Nam",
          "Xã Tân Thuận", "Xã Vĩnh Thuận", "Xã Phong Đông"
        ]
      },
      "Quảng Ngãi": {
        "Thành phố Quảng Ngãi": [
          "Phường Lê Hồng Phong", "Phường Trần Hưng Đạo", "Phường Nghĩa Chánh",
          "Phường Nghĩa Lộ", "Phường Nguyễn Nghiêm", "Xã Nghĩa Dõng", "Xã Tịnh Ấn Đông"
        ],
        "Huyện Bình Sơn": [
          "Thị trấn Châu Ổ", "Xã Bình Chánh", "Xã Bình Đông", "Xã Bình Hòa",
          "Xã Bình Long", "Xã Bình Minh", "Xã Bình Nguyên"
        ],
        "Huyện Sơn Tịnh": [
          "Thị trấn Sơn Tịnh", "Xã Tịnh Hà", "Xã Tịnh Kỳ", "Xã Tịnh Sơn",
          "Xã Tịnh Trà", "Xã Tịnh Thọ", "Xã Tịnh Ấn Tây"
        ],
        "Huyện Tư Nghĩa": [
          "Thị trấn La Hà", "Xã Nghĩa Trung", "Xã Nghĩa Hiệp", "Xã Nghĩa Kỳ",
          "Xã Nghĩa Phương", "Xã Nghĩa Thương", "Xã Nghĩa Mỹ"
        ],
        "Huyện Mộ Đức": [
          "Thị trấn Mộ Đức", "Xã Đức Lân", "Xã Đức Nhuận", "Xã Đức Chánh",
          "Xã Đức Phong", "Xã Đức Phú", "Xã Đức Thạnh"
        ],
        "Huyện Đức Phổ": [
          "Phường Nguyễn Nghiêm", "Phường Phổ Hòa", "Phường Phổ Minh",
          "Xã Phổ An", "Xã Phổ Cường", "Xã Phổ Vinh", "Xã Phổ Khánh"
        ],
        "Huyện Ba Tơ": [
          "Thị trấn Ba Tơ", "Xã Ba Vì", "Xã Ba Dinh", "Xã Ba Thành",
          "Xã Ba Bích", "Xã Ba Cung", "Xã Ba Trang"
        ],
        "Huyện Trà Bồng": [
          "Thị trấn Trà Xuân", "Xã Trà Thủy", "Xã Trà Bình", "Xã Trà Phú",
          "Xã Trà Giang", "Xã Trà Sơn", "Xã Trà Hiệp"
        ],
        "Huyện Tây Trà": [
          "Xã Trà Phong", "Xã Trà Lãnh", "Xã Trà Thanh", "Xã Trà Thọ"
        ],
        "Huyện Sơn Hà": [
          "Thị trấn Di Lăng", "Xã Sơn Thành", "Xã Sơn Tân", "Xã Sơn Bao",
          "Xã Sơn Trung", "Xã Sơn Thượng", "Xã Sơn Cao"
        ],
        "Huyện Sơn Tây": [
          "Xã Sơn Dung", "Xã Sơn Mùa", "Xã Sơn Liên", "Xã Sơn Tinh", "Xã Sơn Tân"
        ],
        "Huyện Minh Long": [
          "Xã Long Hiệp", "Xã Long Mai", "Xã Thanh An", "Xã Long Sơn", "Xã Long Môn"
        ]
      },
      "Thái Bình": {
        "Thành phố Thái Bình": [
          "Phường Bồ Xuyên", "Phường Đề Thám", "Phường Kỳ Bá",
          "Phường Lê Hồng Phong", "Phường Quang Trung", "Phường Trần Hưng Đạo",
          "Phường Trần Lãm", "Xã Đông Hòa", "Xã Phú Xuân", "Xã Vũ Chính"
        ],
        "Huyện Đông Hưng": [
          "Thị trấn Đông Hưng", "Xã Đông Á", "Xã Đông Cường", "Xã Đông Hợp",
          "Xã Đông La", "Xã Đông Phương", "Xã Đông Sơn"
        ],
        "Huyện Hưng Hà": [
          "Thị trấn Hưng Hà", "Xã Canh Tân", "Xã Dân Chủ", "Xã Hồng Lĩnh",
          "Xã Minh Tân", "Xã Tân Hòa", "Xã Văn Cẩm"
        ],
        "Huyện Kiến Xương": [
          "Thị trấn Kiến Xương", "Xã Bình Định", "Xã Hòa Bình", "Xã Hồng Thái",
          "Xã Lê Lợi", "Xã Quang Lịch", "Xã Vũ An"
        ],
        "Huyện Quỳnh Phụ": [
          "Thị trấn Quỳnh Côi", "Xã An Đồng", "Xã An Hiệp", "Xã An Tràng",
          "Xã Quỳnh Hoa", "Xã Quỳnh Minh", "Xã Quỳnh Ngọc"
        ],
        "Huyện Thái Thụy": [
          "Thị trấn Diêm Điền", "Xã Thụy An", "Xã Thụy Bình", "Xã Thụy Duyên",
          "Xã Thụy Hải", "Xã Thụy Liên", "Xã Thụy Trường"
        ],
        "Huyện Tiền Hải": [
          "Thị trấn Tiền Hải", "Xã Đông Cơ", "Xã Đông Hoàng", "Xã Nam Chính",
          "Xã Nam Hưng", "Xã Nam Hải", "Xã Tây Giang"
        ],
        "Huyện Vũ Thư": [
          "Thị trấn Vũ Thư", "Xã Dũng Nghĩa", "Xã Hiệp Hòa", "Xã Hồng Lý",
          "Xã Minh Khai", "Xã Nguyên Xá", "Xã Vũ Đoài"
        ]
      }
    };

    const provinceSelect = document.getElementById("province");
    const districtSelect = document.getElementById("district");

    for (let province in data) {
      provinceSelect.innerHTML += `<option value="${province}">${province}</option>`;
    }

    provinceSelect.addEventListener("change", function() {
      const province = this.value;
      districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';

      if (province && data[province]) {
        for (let district in data[province]) {
          districtSelect.innerHTML += `<option value="${district}">${district}</option>`;
        }
        districtSelect.disabled = false;
      } else {
        districtSelect.disabled = true;
      }
    });

    document.addEventListener('DOMContentLoaded', function() {
      if (provinceSelect.options.length > 1 && "<?= htmlspecialchars($_GET['province'] ?? '') ?>") {
        provinceSelect.value = "<?= htmlspecialchars($_GET['province'] ?? '') ?>";
        provinceSelect.dispatchEvent(new Event('change'));

        if ("<?= htmlspecialchars($_GET['district'] ?? '') ?>") {
          setTimeout(() => {
            districtSelect.value = "<?= htmlspecialchars($_GET['district'] ?? '') ?>";
          }, 100);
        }
      }
    });
  </script>

</body>

</html>
<?php $conn->close(); ?>