<?php
// filepath: c:\xampp\htdocs\LTW-UD2\admin\gui\sidebar.php
// Session check
require_once '../database/database.php';
require_once '../database/role.php';
$roleTableSidebar = new RoleManager();

$adminID = $_SESSION['admin_id'] ?? null;
if ($adminID == null) {
    header("Location: ./login.php");
    exit;
}

// Get current page for highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Helper functions
function isActive($page)
{
    global $current_page;
    return $current_page == $page ? 'active' : '';
}

function isSectionActive($pages)
{
    global $current_page;
    return in_array($current_page, $pages) ? 'active' : '';
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* Simplified Sidebar Styles */
    :root {
        --sidebar-width: 240px;
        --sidebar-collapsed-width: 64px;
        --primary-color: #2563eb;
        --sidebar-bg: #1a2536;
        --text-color: #e2e8f0;
        --text-muted: #94a3b8;
    }

    .sidebar {
        height: 100vh;
        width: var(--sidebar-width);
        background-color: var(--sidebar-bg);
        color: var(--text-color);
        transition: width 0.2s ease;
        z-index: 50;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
    }

    /* Header */
    .sidebar-header {
        height: 60px;
        display: flex;
        align-items: center;
        padding: 0 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 12px;
        color: white;
        font-weight: 600;
    }

    .logo-icon {
        font-size: 20px;
        color: var(--primary-color);
    }

    .toggle-button {
        margin-left: auto;
        background: transparent;
        border: none;
        color: var(--text-muted);
        width: 32px;
        height: 32px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .toggle-button:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
    }

    /* Menu Container */
    .menu-container {
        flex: 1;
        overflow-y: auto;
        padding: 10px 0;
    }

    .menu-container::-webkit-scrollbar {
        width: 4px;
    }

    .menu-container::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }

    /* Menu Section */
    .menu-section {
        padding: 8px 16px;
        font-size: 12px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 8px;
    }

    /* Menu Items */
    .menu-item {
        display: flex;
        align-items: center;
        padding: 10px 16px;
        color: var(--text-color);
        text-decoration: none;
        border-left: 3px solid transparent;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .menu-item:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    .menu-item.active {
        border-left-color: var(--primary-color);
        background-color: rgba(37, 99, 235, 0.1);
    }

    .menu-item i {
        width: 20px;
        margin-right: 12px;
        text-align: center;
    }

    .menu-item-content {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Submenu */
    .submenu {
        max-height: 0;
        overflow: hidden;
        background-color: rgba(0, 0, 0, 0.15);
        transition: max-height 0.3s ease;
    }

    .submenu.open {
        max-height: 300px;
    }

    .submenu-item {
        display: block;
        padding: 8px 16px 8px 48px;
        color: var(--text-muted);
        text-decoration: none;
        position: relative;
        transition: background-color 0.2s;
    }

    .submenu-item:hover {
        background-color: rgba(255, 255, 255, 0.05);
        color: white;
    }

    .submenu-item.active {
        color: white;
        background-color: rgba(37, 99, 235, 0.1);
    }

    .submenu-item::before {
        content: "";
        position: absolute;
        left: 30px;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background-color: currentColor;
    }

    /* User Profile */
    .user-profile {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 12px;
    }

    .user-info {
        flex: 1;
        overflow: hidden;
    }

    .user-name {
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-role {
        font-size: 12px;
        color: var(--text-muted);
    }

    /* Logout Button */
    .logout-button {
        margin: 16px;
        padding: 10px;
        background-color: rgba(239, 68, 68, 0.15);
        color: rgb(239, 68, 68);
        border: none;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-weight: 500;
        transition: background-color 0.2s;
    }

    .logout-button:hover {
        background-color: rgba(239, 68, 68, 0.25);
    }

    /* Collapsed State */
    .sidebar.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    .sidebar.collapsed .logo span,
    .sidebar.collapsed .menu-section,
    .sidebar.collapsed .menu-item span,
    .sidebar.collapsed .menu-arrow,
    .sidebar.collapsed .user-info {
        display: none;
    }

    .sidebar.collapsed .menu-item {
        justify-content: center;
        padding: 14px 0;
    }

    .sidebar.collapsed .menu-item i {
        margin-right: 0;
    }

    .sidebar.collapsed .user-avatar {
        margin-right: 0;
    }

    .sidebar.collapsed .user-profile {
        justify-content: center;
    }

    .sidebar.collapsed .logout-button {
        padding: 10px;
    }

    .sidebar.collapsed .logout-button span {
        display: none;
    }
</style>

<div class="sidebar-backdrop" id="sidebar-backdrop"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-book logo-icon"></i>
            <span>Admin Dashboard</span>
        </div>

    </div>

    <div class="menu-container">
        <?php
        if ($roleTableSidebar->isAuthorized($adminID,  6, 1)) {
        ?>
            <div class="menu-section">Dashboard</div>
            <a href="./analytics.php" class="menu-item <?= isActive('analytics.php') ?>">
                <i class="fas fa-chart-line"></i>
                <span>Thống kê</span>
            </a>
        <?php } ?>
        <?php if ($roleTableSidebar->isAuthorized($adminID, 5, 1) || $roleTableSidebar->isAuthorized($adminID, 8, 1)) {
        ?>
            <div class="menu-section">Người dùng</div>
            <?php if ($roleTableSidebar->isAuthorized($adminID, 5, 1)) { ?>
                <div class="menu-item <?= isSectionActive(['thongTinKhachHang.php']) ?>" data-submenu="customers">
                    <i class="fas fa-users"></i>
                    <div class="menu-item-content">
                        <span>Khách hàng</span>
                        <i class="fas fa-chevron-right menu-arrow"></i>
                    </div>
                </div>
                <div class="submenu <?= isSectionActive(['thongTinKhachHang.php']) ? 'open' : '' ?>" id="customers">
                    <a href="./thongTinKhachHang.php" class="submenu-item <?= isActive('thongTinKhachHang.php') ?>">
                        Danh sách khách hàng
                    </a>
                </div>
            <?php } ?>
            <?php if ($roleTableSidebar->isAuthorized($adminID, 8, 1)) { ?>

                <div class="menu-item <?= isSectionActive(['nhanvien.php', 'add_nhanvien.php']) ?>" data-submenu="staff">
                    <i class="fas fa-user-tie"></i>
                    <div class="menu-item-content">
                        <span>Nhân viên</span>
                        <i class="fas fa-chevron-right menu-arrow"></i>
                    </div>
                </div>
                <div class="submenu <?= isSectionActive(['nhanvien.php', 'add_nhanvien.php']) ? 'open' : '' ?>" id="staff">
                    <a href="./nhanvien.php" class="submenu-item <?= isActive('nhanvien.php') ?>">
                        Danh sách nhân viên
                    </a>
                    <?php if ($roleTableSidebar->isAuthorized($adminID, 8, 2)) { ?>
                        <a href="./add_nhanvien.php" class="submenu-item <?= isActive('add_nhanvien.php') ?>">
                            Thêm nhân viên
                        </a>
                    <?php } ?>

                </div>
            <?php } ?>

        <?php } ?>
        <?php if (
            $roleTableSidebar->isAuthorized($adminID, 7, 1) ||
            $roleTableSidebar->isAuthorized($adminID, 9, 1) ||
            $roleTableSidebar->isAuthorized($adminID, 10, 1) ||
            $roleTableSidebar->isAuthorized($adminID, 11, 1) ||
            $roleTableSidebar->isAuthorized($adminID, 12, 1)
        ) {
        ?>
            <div class="menu-section">Hệ thống</div>
            <?php if ($roleTableSidebar->isAuthorized($adminID, 9, 1)) {            ?>
                <a href="./review.php" class="menu-item <?= isActive('review.php') ?>">
                    <i class="fas fa-star"></i>
                    <span>Đánh giá</span>
                </a>
            <?php } ?>
            <?php if (
                $roleTableSidebar->isAuthorized($adminID, 7, 1) ||
                $roleTableSidebar->isAuthorized($adminID, 13, 1) ||
                $roleTableSidebar->isAuthorized($adminID, 12, 1)
            ) {            ?>

                <div class="menu-item <?= isSectionActive(['sanphan.php', 'themSanPham.php', 'supplier.php', 'thongTinPhieuNhap.php', 'nhapSanPham.php']) ?>" data-submenu="products">
                    <i class="fas fa-box"></i>
                    <div class="menu-item-content">
                        <span>Sản phẩm</span>
                        <i class="fas fa-chevron-right menu-arrow"></i>
                    </div>
                </div>
                <div class="submenu <?= isSectionActive(['sanphan.php', 'themSanPham.php', 'supplier.php', 'thongTinPhieuNhap.php', 'nhapSanPham.php']) ? 'open' : '' ?>" id="products">
                    <?php if ($roleTableSidebar->isAuthorized($adminID, 7, 1)) {            ?>
                        <a href="./sanphan.php" class="submenu-item <?= isActive('sanphan.php') ?>">
                            Danh sách sản phẩm
                        </a>
                        <?php if ($roleTableSidebar->isAuthorized($adminID, 7, 2)) {            ?>
                            <a href="./themSanPham.php" class="submenu-item <?= isActive('themSanPham.php') ?>">
                                Thêm sản phẩm
                            </a>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($roleTableSidebar->isAuthorized($adminID, 13, 1)) {            ?>

                        <a href="./supplier.php" class="submenu-item <?= isActive('supplier.php') ?>">
                            Nhà cung cấp
                        </a>
                    <?php } ?>
                    <?php if ($roleTableSidebar->isAuthorized($adminID, 12, 1)) {            ?>

                        <a href="./thongTinPhieuNhap.php" class="submenu-item <?= isActive('thongTinPhieuNhap.php') ?>">
                            Phiếu nhập
                        </a>
                        <?php if ($roleTableSidebar->isAuthorized($adminID, 12, 2)) {            ?>

                            <a href="./nhapSanPham.php" class="submenu-item <?= isActive('nhapSanPham.php') ?>">
                                Thêm phiếu nhập
                            </a>
                        <?php } ?>
                    <?php } ?>

                </div>
            <?php } ?>
            <?php if ($roleTableSidebar->isAuthorized($adminID, 10, 1)) {            ?>

                <div class="menu-item <?= isSectionActive(['quanlidon.php']) ?>" data-submenu="orders">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="menu-item-content">
                        <span>Đơn hàng</span>
                        <i class="fas fa-chevron-right menu-arrow"></i>
                    </div>
                </div>
                <div class="submenu <?= isSectionActive(['quanlidon.php']) ? 'open' : '' ?>" id="orders">
                    <a href="./quanlidon.php" class="submenu-item <?= isActive('quanlidon.php') ?>">
                        Danh sách đơn hàng
                    </a>
                </div>
            <?php } ?>

            <?php if ($roleTableSidebar->isAuthorized($adminID, 11, 1)) {            ?>

                <div class="menu-item <?= isSectionActive(['xemquyen.php', 'themquyen.php']) ?>" data-submenu="permissions">
                    <i class="fas fa-lock"></i>
                    <div class="menu-item-content">
                        <span>Phân quyền</span>
                        <i class="fas fa-chevron-right menu-arrow"></i>
                    </div>
                </div>
                <div class="submenu <?= isSectionActive(['xemquyen.php', 'themquyen.php']) ? 'open' : '' ?>" id="permissions">
                    <a href="./xemquyen.php" class="submenu-item <?= isActive('xemquyen.php') ?>">
                        Xem quyền
                    </a>
                    <?php if ($roleTableSidebar->isAuthorized($adminID, 11, 2)) {            ?>

                        <a href="./themquyen.php" class="submenu-item <?= isActive('themquyen.php') ?>">
                            Thêm quyền
                        </a>
                </div>
            <?php } ?>
        <?php } ?>

    </div>
<?php } ?>

<div class="user-profile">
    <div class="user-avatar">
        <?php echo isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'A'; ?>
    </div>
    <div class="user-info">
        <div class="user-name">
            <?php echo $_SESSION['user_name'] ?? 'Admin User'; ?>
        </div>
        <div class="user-role">Administrator</div>
    </div>
</div>

<form action="./logout.php" method="POST">
    <button type="submit" class="logout-button">
        <i class="fas fa-sign-out-alt"></i>
        <span>Đăng xuất</span>
    </button>
</form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const menuItems = document.querySelectorAll('.menu-item[data-submenu]');

        // Toggle sidebar on desktop
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
        }


        // Close sidebar when clicking outside
        if (backdrop) {
            backdrop.addEventListener('click', function() {
                backdrop.classList.remove('show');
            });
        }


        // Toggle submenus
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                if (sidebar.classList.contains('collapsed')) return;

                const submenuId = this.dataset.submenu;
                const submenu = document.getElementById(submenuId);

                if (submenu) {
                    const isOpen = submenu.classList.toggle('open');

                    // Close other submenus
                    document.querySelectorAll('.submenu.open').forEach(menu => {
                        if (menu.id !== submenuId) {
                            menu.classList.remove('open');
                        }
                    });

                    // Rotate arrow
                    const arrow = this.querySelector('.menu-arrow');
                    if (arrow) {
                        arrow.style.transform = isOpen ? 'rotate(90deg)' : '';
                    }
                }
            });
        });

        // Open submenus containing active items on page load
        document.querySelectorAll('.submenu').forEach(submenu => {
            if (submenu.querySelector('.submenu-item.active')) {
                submenu.classList.add('open');
                const parentItem = document.querySelector(`[data-submenu="${submenu.id}"]`);
                if (parentItem) {
                    const arrow = parentItem.querySelector('.menu-arrow');
                    if (arrow) arrow.style.transform = 'rotate(90deg)';
                }
            }
        });
    });
</script>