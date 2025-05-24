<?php
session_start();
require_once("../database/database.php");
require_once("../database/user.php");
require_once("../database/book.php");
require_once("../database/hoadon.php");
require_once("../database/chitiethoadon.php");
$userTable = new UsersTable();
$bookTable = new BooksTable();
$hoadonTable = new HoadonTable();
$chiTietHoadonTable = new ChiTietHoadonTable();
$user = null;
if (isset($_SESSION["admin_id"]) && $_SESSION["admin_id"] != null) {
  $user = $userTable->getUserDetailsById($_SESSION["admin_id"]);
  if ($user == null) {
    unset($_SESSION["admin_id"]);
  }
}

// Get date range parameters with better defaults
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-6 months'));
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$view = isset($_GET['view']) ? $_GET['view'] : 'dashboard';

// Get essential data based on date range
$allHoaDon = $hoadonTable->getAllHoaDon();
$allUsers = $userTable->getAllUser();
$completedOrders = $hoadonTable->getCompletedOrdersCount();

// Get product sales data with enhanced sorting
$productSales = $chiTietHoadonTable->getProductSalesByDateRange($dateFrom, $dateTo);
$totalRevenue = 0;
$totalItemsSold = 0;
foreach ($productSales as $product) {
  $totalRevenue += $product['total_revenue'];
  $totalItemsSold += $product['quantity_sold'];
}

// Sort products by revenue (best to worst)
$bestSellingProducts = $productSales;
usort($bestSellingProducts, function ($a, $b) {
  return $b['total_revenue'] - $a['total_revenue'];
});

// Get worst selling products
$worstSellingProducts = $bestSellingProducts;
$bestSellingProducts = array_slice($bestSellingProducts, 0, 5); // Top 5
$worstSellingProducts = array_slice(array_reverse($worstSellingProducts), 0, 5); // Bottom 5

// Get customer data with enhanced details
$customerSales = $userTable->getCustomerSalesByDateRange($dateFrom, $dateTo);
usort($customerSales, function ($a, $b) {
  return $b['total_spent'] - $a['total_spent'];
});
$topCustomers = array_slice($customerSales, 0, 10); // Top 10 customers

// Calculate average order value
$avgOrderValue = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;

// Monthly revenue data
$monthlyRevenue = $hoadonTable->getlast6Monthstotal();

// Calculate growth rate
$currentMonth = $monthlyRevenue[count($monthlyRevenue) - 1]['total_bill'] ?? 0;
$previousMonth = $monthlyRevenue[count($monthlyRevenue) - 2]['total_bill'] ?? 0; // avoid division by zero
$growthRate = $previousMonth > 0 ? (($currentMonth - $previousMonth) / $previousMonth) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thống kê kinh doanh</title>
  <?php
  // echo json_encode($customerSales);
  ?>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .stats-card {
      transition: all 0.3s ease;
    }

    .stats-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .badge {
      display: inline-flex;
      align-items: center;
      padding: 0.25rem 0.5rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;
      line-height: 1;
    }

    .badge-success {
      background-color: #d1fae5;
      color: #065f46;
    }

    .badge-danger {
      background-color: #fee2e2;
      color: #991b1b;
    }

    .badge-info {
      background-color: #e0f2fe;
      color: #0369a1;
    }

    .highlight-row {
      position: relative;
    }

    .highlight-row::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      height: 100%;
      width: 4px;
    }

    .highlight-success::before {
      background-color: #10b981;
    }

    .highlight-danger::before {
      background-color: #ef4444;
    }

    .table-hover tr:hover {
      background-color: #f9fafb;
    }

    @media (max-width: 640px) {
      .date-range-form {
        flex-direction: column;
        align-items: stretch;
      }

      .date-range-form>div {
        margin-bottom: 10px;
        margin-right: 0;
      }
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
      <div class="bg-white shadow-lg border border-gray-300 rounded-lg p-3 sm:p-4 md:p-6 w-full max-w-full">
        <!-- Date Range Selector -->

        <div class="mb-4 md:mb-6">
          <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2 sm:mb-4 flex items-center">
            <i class="fas fa-chart-line mr-2 text-blue-600"></i>
            Thống kê kinh doanh
          </h1>
          <form method="GET" action="" class="flex flex-wrap gap-3 md:flex-nowrap date-range-form bg-gray-50 p-3 rounded-lg">
            <div class="w-full sm:w-auto">
              <label class="block text-sm font-medium text-gray-700">Từ ngày</label>
              <input type="date" name="date_from" value="<?php echo $dateFrom; ?>"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            <div class="w-full sm:w-auto">
              <label class="block text-sm font-medium text-gray-700">Đến ngày</label>
              <input type="date" name="date_to" value="<?php echo $dateTo; ?>"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            <input type="hidden" name="view" value="<?php echo $view; ?>">
            <div class="w-full sm:w-auto sm:self-end">
              <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-transparent 
                                    rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition duration-150 ease-in-out">
                <i class="fas fa-filter mr-2"></i> Áp dụng
              </button>
            </div>
          </form>
        </div>

        <!-- Navigation Tabs - Scrollable on mobile -->
        <div class="border-b border-gray-200 mb-4 md:mb-6 overflow-x-auto pb-2">
          <nav class="flex space-x-4 md:space-x-6 min-w-max">
            <a href="?view=dashboard&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>"
              class="<?php echo $view == 'dashboard' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> 
                           whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm flex items-center">
              <i class="fas fa-tachometer-alt mr-2"></i> Tổng quan
            </a>
            <a href="?view=products&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>"
              class="<?php echo $view == 'products' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> 
                           whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm flex items-center">
              <i class="fas fa-book mr-2"></i> Thống kê sản phẩm
            </a>
            <a href="?view=customers&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>"
              class="<?php echo $view == 'customers' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> 
                           whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm flex items-center">
              <i class="fas fa-users mr-2"></i> Thống kê khách hàng
            </a>
          </nav>
        </div>

        <?php if ($view == 'dashboard'): ?>
          <!-- Dashboard View -->
          <div>
            <!-- Summary Cards - Responsive grid with hover effects -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6">
              <div class="stats-card bg-white shadow-md rounded-lg p-4 md:p-6 flex flex-col items-start justify-center border-l-4 border-blue-500">
                <div class="flex items-center justify-between w-full">
                  <h3 class="text-base md:text-lg font-semibold text-gray-700">Tổng doanh thu</h3>
                  <span class="text-blue-500 bg-blue-100 p-2 rounded-full">
                    <i class="fas fa-dollar-sign"></i>
                  </span>
                </div>
                <div class="text-2xl md:text-3xl font-bold text-blue-600 mt-2">
                  <?php echo number_format($totalRevenue, 0, ',', '.'); ?>đ
                </div>
                <div class="text-sm text-gray-500 mt-1">
                  <span class="<?php echo $growthRate >= 0 ? 'text-green-500' : 'text-red-500'; ?>">
                    <i class="fas <?php echo $growthRate >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                    <?php echo abs(round($growthRate, 1)); ?>%
                  </span>
                  so với tháng trước
                </div>
              </div>

              <div class="stats-card bg-white shadow-md rounded-lg p-4 md:p-6 flex flex-col items-start justify-center border-l-4 border-green-500">
                <div class="flex items-center justify-between w-full">
                  <h3 class="text-base md:text-lg font-semibold text-gray-700">Sản phẩm đã bán</h3>
                  <span class="text-green-500 bg-green-100 p-2 rounded-full">
                    <i class="fas fa-shopping-bag"></i>
                  </span>
                </div>
                <div class="text-2xl md:text-3xl font-bold text-green-600 mt-2">
                  <?php echo number_format($totalItemsSold); ?>
                </div>
                <div class="text-sm text-gray-500 mt-1">
                  <?php echo count($productSales); ?> loại sản phẩm
                </div>
              </div>

              <div class="stats-card bg-white shadow-md rounded-lg p-4 md:p-6 flex flex-col items-start justify-center border-l-4 border-yellow-500">
                <div class="flex items-center justify-between w-full">
                  <h3 class="text-base md:text-lg font-semibold text-gray-700">Đơn hàng</h3>
                  <span class="text-yellow-500 bg-yellow-100 p-2 rounded-full">
                    <i class="fas fa-file-invoice"></i>
                  </span>
                </div>
                <div class="text-2xl md:text-3xl font-bold text-yellow-600 mt-2">
                  <?php echo $completedOrders; ?>
                </div>
                <div class="text-sm text-gray-500 mt-1">
                  Hoàn thành từ <?php echo count($allHoaDon); ?> đơn
                </div>
              </div>

              <div class="stats-card bg-white shadow-md rounded-lg p-4 md:p-6 flex flex-col items-start justify-center border-l-4 border-purple-500">
                <div class="flex items-center justify-between w-full">
                  <h3 class="text-base md:text-lg font-semibold text-gray-700">Giá trị đơn TB</h3>
                  <span class="text-purple-500 bg-purple-100 p-2 rounded-full">
                    <i class="fas fa-chart-line"></i>
                  </span>
                </div>
                <div class="text-2xl md:text-3xl font-bold text-purple-600 mt-2">
                  <?php echo number_format($avgOrderValue, 0, ',', '.'); ?>đ
                </div>
                <div class="text-sm text-gray-500 mt-1">
                  <?php echo count($customerSales); ?> khách hàng
                </div>
              </div>
            </div>

            <!-- Revenue chart and product comparison -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6">
              <!-- Monthly Revenue Chart -->
              <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-base md:text-lg font-medium text-gray-800 mb-4">Doanh thu 6 tháng qua</h3>
                <div style="height: 250px;">
                  <canvas id="revenueChart"></canvas>
                </div>
              </div>

              <!-- Product Comparison -->
              <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-base md:text-lg font-medium text-gray-800 mb-4">So sánh sản phẩm</h3>
                <div class="flex flex-col">
                  <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-600 mb-2">Sản phẩm bán chạy nhất</h4>
                    <?php if (!empty($bestSellingProducts)): ?>
                      <div class="flex items-center p-3 bg-green-50 rounded-lg">
                        <div class="flex-shrink-0 h-12 w-12 rounded overflow-hidden">
                          <img class="h-12 w-12 object-cover" src="<?php echo $bestSellingProducts[0]['imageURL']; ?>" alt="Top product">
                        </div>
                        <div class="ml-3">
                          <p class="text-sm font-medium text-gray-900"><?php echo $bestSellingProducts[0]['bookName']; ?></p>
                          <p class="text-sm text-gray-500">
                            <span class="font-semibold text-green-600"><?php echo number_format($bestSellingProducts[0]['total_revenue'], 0, ',', '.'); ?>đ</span>
                            (<?php echo $bestSellingProducts[0]['quantity_sold']; ?> sản phẩm)
                          </p>
                        </div>
                        <div class="ml-auto">
                          <a href="./quanlidon.php?product_id=<?php echo $bestSellingProducts[0]['id']; ?>" class="text-blue-600 hover:underline">
                            <i class="fas fa-external-link-alt"></i>
                          </a>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>

                  <div>
                    <h4 class="text-sm font-medium text-gray-600 mb-2">Sản phẩm bán chậm nhất</h4>
                    <?php if (!empty($worstSellingProducts)): ?>
                      <div class="flex items-center p-3 bg-red-50 rounded-lg">
                        <div class="flex-shrink-0 h-12 w-12 rounded overflow-hidden">
                          <img class="h-12 w-12 object-cover" src="<?php echo $worstSellingProducts[0]['imageURL']; ?>" alt="Worst product">
                        </div>
                        <div class="ml-3">
                          <p class="text-sm font-medium text-gray-900"><?php echo $worstSellingProducts[0]['bookName']; ?></p>
                          <p class="text-sm text-gray-500">
                            <span class="font-semibold text-red-600"><?php echo number_format($worstSellingProducts[0]['total_revenue'], 0, ',', '.'); ?>đ</span>
                            (<?php echo $worstSellingProducts[0]['quantity_sold']; ?> sản phẩm)
                          </p>
                        </div>
                        <div class="ml-auto">
                          <a href="./quanlidon.php?product_id=<?php echo $worstSellingProducts[0]['id']; ?>" class="text-blue-600 hover:underline">
                            <i class="fas fa-external-link-alt"></i>
                          </a>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tables for Top Customers and Products -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
              <!-- Top 5 Customers Table -->
              <div class="bg-white p-3 sm:p-4 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                  <h3 class="text-base md:text-lg font-medium text-gray-800">Top 5 khách hàng</h3>
                  <a href="?view=customers&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" class="text-sm text-blue-600 hover:underline">
                    Xem tất cả
                  </a>
                </div>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 table-hover">
                    <thead class="bg-gray-50">
                      <tr>
                        <th scope="col" class="px-3 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                        <th scope="col" class="px-3 py-2 md:px-4 md:py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn hàng</th>
                        <th scope="col" class="px-3 py-2 md:px-4 md:py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Doanh thu</th>
                        <th scope="col" class="px-3 py-2 md:px-4 md:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Hoá đơn</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <?php foreach (array_slice($customerSales, 0, 5) as $index => $customer): ?>
                        <tr class="hover:bg-gray-50 highlight-row <?php echo $index === 0 ? 'highlight-success' : ''; ?>">
                          <td class="px-3 py-2 md:px-4 md:py-3 whitespace-nowrap">
                            <div class="flex items-center">
                              <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-500">
                                <span class="font-medium"><?php echo $index + 1; ?></span>
                              </div>
                              <div class="ml-2 md:ml-3">
                                <div class="text-xs md:text-sm font-medium text-gray-900"><?php echo $customer['fullName']; ?></div>
                                <div class="text-xs text-gray-500 hidden sm:block"><?php echo $customer['email']; ?></div>
                              </div>
                            </div>
                          </td>
                          <td class="px-3 py-2 md:px-4 md:py-3 whitespace-nowrap text-xs md:text-sm text-right"><?php echo $customer['order_count']; ?></td>
                          <td class="px-3 py-2 md:px-4 md:py-3 whitespace-nowrap text-xs md:text-sm text-right font-medium text-blue-600"><?php echo number_format($customer['total_spent'], 0, ',', '.'); ?>đ</td>
                          <td class="px-3 py-2 md:px-4 md:py-3 whitespace-nowrap text-xs md:text-sm text-center">
                            <a href="./quanlidon.php?user_id=<?php echo $customer['id']; ?>" class="text-blue-600 hover:text-blue-900 hover:underline">
                              <i class="fas fa-eye"></i>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Top 5 Products Table -->
              <div class="bg-white p-3 sm:p-4 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                  <h3 class="text-base md:text-lg font-medium text-gray-800">Top 5 sản phẩm bán chạy</h3>
                  <a href="?view=products&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" class="text-sm text-blue-600 hover:underline">
                    Xem tất cả
                  </a>
                </div>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 table-hover">
                    <thead class="bg-gray-50">
                      <tr>
                        <th scope="col" class="px-3 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                        <th scope="col" class="px-3 py-2 md:px-4 md:py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                        <th scope="col" class="px-3 py-2 md:px-4 md:py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Doanh thu</th>
                        <th scope="col" class="px-3 py-2 md:px-4 md:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Hoá đơn</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <?php foreach ($bestSellingProducts as $index => $product): ?>
                        <tr class="hover:bg-gray-50 highlight-row <?php echo $index === 0 ? 'highlight-success' : ''; ?>">
                          <td class="px-3 py-2 md:px-4 md:py-3 whitespace-nowrap">
                            <div class="flex items-center">
                              <div class="flex-shrink-0 h-8 w-8 rounded overflow-hidden">
                                <img class="h-8 w-8 object-cover" src="<?php echo $product['imageURL']; ?>" alt="Product">
                              </div>
                              <div class="ml-2 md:ml-3">
                                <div class="text-xs md:text-sm font-medium text-gray-900 line-clamp-1"><?php echo $product['bookName']; ?></div>
                                <div class="text-xs text-gray-500"><?php echo number_format($product['currentPrice'], 0, ',', '.'); ?>đ</div>
                              </div>
                            </div>
                          </td>
                          <td class="px-3 py-2 md:px-4 md:py-3 whitespace-nowrap text-xs md:text-sm text-right"><?php echo $product['quantity_sold']; ?></td>
                          <td class="px-3 py-2 md:px-4 md:py-3 whitespace-nowrap text-xs md:text-sm text-right font-medium text-green-600"><?php echo number_format($product['total_revenue'], 0, ',', '.'); ?>đ</td>
                          <td class="px-3 py-2 md:px-4 md:py-3 whitespace-nowrap text-xs md:text-sm text-center">
                            <a href="./quanlidon.php?product_id=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-900 hover:underline">
                              <i class="fas fa-eye"></i>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($view == 'products'): ?>
          <!-- Products Statistics View -->
          <div>
            <div class="mb-6">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
                <h2 class="text-lg md:text-xl font-semibold text-gray-800 flex items-center">
                  <i class="fas fa-book mr-2 text-blue-600"></i>
                  Thống kê sản phẩm
                </h2>
                <div>
                  <label class="mr-2">Sắp xếp:</label>
                  <select id="productSort" class="border rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="revenue-desc">Doanh thu cao nhất</option>
                    <option value="revenue-asc">Doanh thu thấp nhất</option>
                    <option value="quantity-desc">Số lượng bán nhiều nhất</option>
                    <option value="quantity-asc">Số lượng bán ít nhất</option>
                  </select>
                </div>
              </div>

              <!-- Summary stats -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                  <div class="text-sm font-medium text-gray-500">Tổng doanh thu</div>
                  <div class="mt-1 text-xl font-semibold text-blue-600">
                    <?php echo number_format($totalRevenue, 0, ',', '.'); ?>đ
                  </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                  <div class="text-sm font-medium text-gray-500">Tổng số lượng bán</div>
                  <div class="mt-1 text-xl font-semibold text-green-600">
                    <?php echo number_format($totalItemsSold); ?> sản phẩm
                  </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
                  <div class="text-sm font-medium text-gray-500">Số lượng mặt hàng</div>
                  <div class="mt-1 text-xl font-semibold text-purple-600">
                    <?php echo count($productSales); ?> sản phẩm
                  </div>
                </div>
              </div>
            </div>

            <!-- Responsive table wrapper with search filter -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="p-4 border-b">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                  <h3 class="text-base font-medium text-gray-700">Chi tiết sản phẩm</h3>
                  <div class="relative">
                    <input type="text" id="productSearch" placeholder="Tìm kiếm sản phẩm..."
                      class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute left-3 top-2.5 text-gray-400">
                      <i class="fas fa-search"></i>
                    </div>
                  </div>
                </div>
              </div>

              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 table-hover">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                      <th scope="col" class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Giá bán</th>
                      <th scope="col" class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                      <th scope="col" class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Doanh thu</th>
                      <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tỷ lệ</th>
                      <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Xem hoá đơn</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200" id="productTableBody">
                    <?php foreach ($productSales as $index => $product):
                      $isBestSeller = $index < 3; // Top 3 best sellers
                      $isWorstSeller = $index >= count($productSales) - 3 && count($productSales) > 5; // Bottom 3 worst sellers
                      $percentOfTotal = $totalRevenue > 0 ? ($product['total_revenue'] / $totalRevenue) * 100 : 0;
                    ?>
                      <tr class="<?php echo $isBestSeller ? 'bg-green-50' : ($isWorstSeller ? 'bg-red-50' : ''); ?> hover:bg-opacity-80">
                        <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 sm:h-12 sm:w-12">
                              <img class="h-10 w-10 sm:h-12 sm:w-12 rounded-md object-cover" src="<?php echo $product['imageURL']; ?>" alt="">
                            </div>
                            <div class="ml-2 sm:ml-4">
                              <div class="text-xs sm:text-sm font-medium text-gray-900"><?php echo $product['bookName']; ?></div>
                              <?php if ($isBestSeller): ?>
                                <span class="badge badge-success">
                                  <i class="fas fa-crown mr-1"></i> Top <?php echo $index + 1; ?>
                                </span>
                              <?php endif; ?>
                              <?php if ($isWorstSeller): ?>
                                <span class="badge badge-danger">
                                  <i class="fas fa-exclamation-circle mr-1"></i> Bán chậm
                                </span>
                              <?php endif; ?>
                            </div>
                          </div>
                        </td>
                        <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-right text-gray-500">
                          <?php echo number_format($product['currentPrice'], 0, ',', '.'); ?>đ
                        </td>
                        <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-right text-gray-500">
                          <?php echo $product['quantity_sold']; ?>
                        </td>
                        <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-right">
                          <span class="font-semibold <?php echo $isBestSeller ? 'text-green-600' : ($isWorstSeller ? 'text-red-600' : 'text-gray-900'); ?>">
                            <?php echo number_format($product['total_revenue'], 0, ',', '.'); ?>đ
                          </span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-center">
                          <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 my-1">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo round($percentOfTotal, 1); ?>%"></div>
                          </div>
                          <span class="text-xs text-gray-500"><?php echo round($percentOfTotal, 1); ?>%</span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-center">
                          <a href="./quanlidon.php?product_id=<?php echo $product['id']; ?>"
                            class="text-white bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded inline-flex items-center gap-1 transition-colors">
                            <i class="fas fa-receipt"></i>
                            <span class="hidden sm:inline">Xem</span>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>

                <div id="noProductsFound" class="hidden py-8 text-center text-gray-500">
                  <i class="fas fa-search mb-2 text-2xl"></i>
                  <p>Không tìm thấy sản phẩm phù hợp</p>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($view == 'customers'): ?>
          <!-- Customers Statistics View -->
          <div>
            <div class="mb-6">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
                <h2 class="text-lg md:text-xl font-semibold text-gray-800 flex items-center">
                  <i class="fas fa-users mr-2 text-blue-600"></i>
                  Thống kê khách hàng
                </h2>
                <div>
                  <label class="mr-2">Sắp xếp:</label>
                  <select id="customerSort" class="border rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="revenue-desc">Doanh thu cao nhất</option>
                    <option value="revenue-asc">Doanh thu thấp nhất</option>
                    <option value="orders-desc">Nhiều đơn hàng nhất</option>
                    <option value="orders-asc">Ít đơn hàng nhất</option>
                  </select>
                </div>
              </div>

              <!-- Summary stats -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                  <div class="text-sm font-medium text-gray-500">Tổng khách hàng</div>
                  <div class="mt-1 text-xl font-semibold text-blue-600">
                    <?php echo count($customerSales); ?> khách hàng
                  </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                  <div class="text-sm font-medium text-gray-500">Giá trị khách hàng TB</div>
                  <div class="mt-1 text-xl font-semibold text-green-600">
                    <?php
                    $avgCustomerValue = count($customerSales) > 0 ? $totalRevenue / count($customerSales) : 0;
                    echo number_format($avgCustomerValue, 0, ',', '.');
                    ?>đ
                  </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
                  <div class="text-sm font-medium text-gray-500">Đơn hàng TB/khách</div>
                  <div class="mt-1 text-xl font-semibold text-purple-600">
                    <?php
                    $totalOrders = 0;
                    foreach ($customerSales as $customer) {
                      $totalOrders += $customer['order_count'];
                    }
                    $avgOrders = count($customerSales) > 0 ? round($totalOrders / count($customerSales), 1) : 0;
                    echo $avgOrders;
                    ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- Responsive table wrapper with search filter -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="p-4 border-b">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                  <h3 class="text-base font-medium text-gray-700">Chi tiết khách hàng</h3>
                  <div class="relative">
                    <input type="text" id="customerSearch" placeholder="Tìm kiếm khách hàng..."
                      class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute left-3 top-2.5 text-gray-400">
                      <i class="fas fa-search"></i>
                    </div>
                  </div>
                </div>
              </div>

              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 table-hover">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                      <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                      <th scope="col" class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn hàng</th>
                      <th scope="col" class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Doanh thu</th>
                      <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tỷ lệ</th>
                      <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Xem hoá đơn</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200" id="customerTableBody">
                    <?php foreach ($topCustomers as $index => $customer):
                      $isTopCustomer = $index < 3; // Top 3 customers
                      $percentOfTotal = $totalRevenue > 0 ? ($customer['total_spent'] / $totalRevenue) * 100 : 0;
                    ?>
                      <tr class="<?php echo $isTopCustomer ? 'bg-blue-50' : ''; ?> hover:bg-opacity-80">
                        <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-blue-200 flex items-center justify-center text-blue-600">
                              <span class="text-lg font-medium"><?php echo substr($customer['fullName'], 0, 1); ?></span>
                            </div>
                            <div class="ml-3 sm:ml-4">
                              <div class="text-xs sm:text-sm font-medium text-gray-900"><?php echo $customer['fullName']; ?></div>
                              <?php if ($isTopCustomer): ?>
                                <span class="badge badge-info">
                                  <i class="fas fa-star mr-1"></i> Top <?php echo $index + 1; ?>
                                </span>
                              <?php endif; ?>
                            </div>
                          </div>
                        </td>
                        <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                          <span class="hidden sm:inline"><?php echo $customer['email']; ?></span>
                          <span class="sm:hidden"><?php echo substr($customer['email'], 0, 10); ?>...</span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-right">
                          <?php echo $customer['order_count']; ?>
                        </td>
                        <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-right">
                          <span class="font-semibold <?php echo $isTopCustomer ? 'text-blue-600' : 'text-gray-900'; ?>">
                            <?php echo number_format($customer['total_spent'], 0, ',', '.'); ?>đ
                          </span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-center">
                          <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 my-1">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo round($percentOfTotal, 1); ?>%"></div>
                          </div>
                          <span class="text-xs text-gray-500"><?php echo round($percentOfTotal, 1); ?>%</span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-center">
                          <a href="./quanlidon.php?user_id=<?php echo $customer['id']; ?>"
                            class="text-white bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded inline-flex items-center gap-1 transition-colors">
                            <i class="fas fa-receipt"></i>
                            <span class="hidden sm:inline">Xem</span>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>

                <div id="noCustomersFound" class="hidden py-8 text-center text-gray-500">
                  <i class="fas fa-search mb-2 text-2xl"></i>
                  <p>Không tìm thấy khách hàng phù hợp</p>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
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

    // Product sorting functionality
    document.getElementById('productSort')?.addEventListener('change', function() {
      const sortValue = this.value;
      const tableBody = document.getElementById('productTableBody');
      const rows = Array.from(tableBody.querySelectorAll('tr'));

      rows.sort((a, b) => {
        let aValue, bValue;

        if (sortValue.startsWith('revenue')) {
          aValue = parseInt(a.querySelector('td:nth-child(4)').textContent.replace(/\D/g, ''));
          bValue = parseInt(b.querySelector('td:nth-child(4)').textContent.replace(/\D/g, ''));
        } else {
          aValue = parseInt(a.querySelector('td:nth-child(3)').textContent);
          bValue = parseInt(b.querySelector('td:nth-child(3)').textContent);
        }

        return sortValue.endsWith('asc') ? aValue - bValue : bValue - aValue;
      });

      // Clear the table and append sorted rows
      tableBody.innerHTML = '';
      rows.forEach(row => tableBody.appendChild(row));

      // Update highlight classes based on new order
      updateHighlightRows(rows, tableBody);
    });

    // Customer sorting functionality
    document.getElementById('customerSort')?.addEventListener('change', function() {
      const sortValue = this.value;
      const tableBody = document.getElementById('customerTableBody');
      const rows = Array.from(tableBody.querySelectorAll('tr'));

      rows.sort((a, b) => {
        let aValue, bValue;

        if (sortValue.startsWith('revenue')) {
          aValue = parseInt(a.querySelector('td:nth-child(4)').textContent.replace(/\D/g, ''));
          bValue = parseInt(b.querySelector('td:nth-child(4)').textContent.replace(/\D/g, ''));
        } else {
          aValue = parseInt(a.querySelector('td:nth-child(3)').textContent);
          bValue = parseInt(b.querySelector('td:nth-child(3)').textContent);
        }

        return sortValue.endsWith('asc') ? aValue - bValue : bValue - aValue;
      });

      // Clear the table and append sorted rows
      tableBody.innerHTML = '';
      rows.forEach(row => tableBody.appendChild(row));

      // Update highlight classes based on new order
      updateHighlightRows(rows, tableBody);
    });

    // Search functionality for products
    document.getElementById('productSearch')?.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const tableBody = document.getElementById('productTableBody');
      const rows = tableBody.querySelectorAll('tr');
      const noProductsFound = document.getElementById('noProductsFound');

      let found = false;

      rows.forEach(row => {
        const productName = row.querySelector('td:first-child').textContent.toLowerCase();
        if (productName.includes(searchTerm)) {
          row.style.display = '';
          found = true;
        } else {
          row.style.display = 'none';
        }
      });

      if (found) {
        noProductsFound.classList.add('hidden');
      } else {
        noProductsFound.classList.remove('hidden');
      }
    });

    // Search functionality for customers
    document.getElementById('customerSearch')?.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const tableBody = document.getElementById('customerTableBody');
      const rows = tableBody.querySelectorAll('tr');
      const noCustomersFound = document.getElementById('noCustomersFound');

      let found = false;

      rows.forEach(row => {
        const customerName = row.querySelector('td:first-child').textContent.toLowerCase();
        const customerEmail = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        if (customerName.includes(searchTerm) || customerEmail.includes(searchTerm)) {
          row.style.display = '';
          found = true;
        } else {
          row.style.display = 'none';
        }
      });

      if (found) {
        noCustomersFound.classList.add('hidden');
      } else {
        noCustomersFound.classList.remove('hidden');
      }
    });

    function updateHighlightRows(rows, tableBody) {
      // Reset all highlight classes
      rows.forEach(row => {
        row.classList.remove('bg-green-50', 'bg-blue-50', 'bg-red-50', 'highlight-success', 'highlight-danger');
      });

      // Add highlight classes to top 3 and bottom 3
      if (rows.length > 5) {
        for (let i = 0; i < 3 && i < rows.length; i++) {
          if (tableBody.id === 'productTableBody') {
            rows[i].classList.add('bg-green-50', 'highlight-row', 'highlight-success');
            rows[rows.length - 1 - i].classList.add('bg-red-50', 'highlight-row', 'highlight-danger');
          } else {
            rows[i].classList.add('bg-blue-50', 'highlight-row', 'highlight-success');
          }
        }
      }
    }

    // Initialize revenue chart
    document.addEventListener('DOMContentLoaded', function() {
      const revenueChart = document.getElementById('revenueChart');

      if (revenueChart) {
        const ctx = revenueChart.getContext('2d');

        // Get monthly revenue data from PHP
        const monthlyData = <?php echo json_encode(array_reverse($monthlyRevenue)); ?>;
        const months = monthlyData.map(item => item.month);
        const revenues = monthlyData.map(item => item.total_bill);

        new Chart(ctx, {
          type: 'line',
          data: {
            labels: months,
            datasets: [{
              label: 'Doanh thu',
              data: revenues,
              backgroundColor: 'rgba(59, 130, 246, 0.2)',
              borderColor: 'rgba(59, 130, 246, 1)',
              borderWidth: 2,
              tension: 0.3,
              fill: true,
              pointBackgroundColor: 'rgba(59, 130, 246, 1)',
              pointBorderColor: '#fff',
              pointBorderWidth: 2,
              pointRadius: 4,
              pointHoverRadius: 6
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              },
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
                        currency: 'VND'
                      }).format(context.parsed.y);
                    }
                    return label;
                  }
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: function(value) {
                    if (value >= 1000000) {
                      return (value / 1000000).toLocaleString() + 'M';
                    } else if (value >= 1000) {
                      return (value / 1000).toLocaleString() + 'K';
                    }
                    return value.toLocaleString();
                  }
                }
              }
            }
          }
        });
      }
    });
  </script>
</body>

</html>