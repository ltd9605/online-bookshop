<?php
require_once("../database/database.php");
require_once("../database/user.php");
$userTable = new UsersTable();
$user = null;
if (isset($_SESSION["user"]) && $_SESSION["user"] != null) {
    $user = $userTable->getUserDetailsById($_SESSION["user"]);
    if ($user == null) {
        unset($_SESSION["user"]);
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/LTW-UD2/admin/assets/css/layout.css">

</head>

<body>
    <div id="admin-container">
        <div id="admin-header">
            <div id="header-left">
                <h3 id="Fahahahasa">Fahahahasa</h3>
                <div id="closeSidebar" onclick="closeSidebar()">
                    <img id="bars" src="./assets/icon/bars.svg" alt="" class="dark-img">
                </div>
            </div>
            <div id="header-right">
                <div id="avt"> <img src="./assets/icon/face.svg" alt=""></div>
                <div id="user-who">Xin chào, <?php if ($user != null) {
                                                    echo htmlspecialchars($user['fullName']);  # code...
                                                }  ?></div>

            </div>
        </div>

        <div class="flex w-full min-h-screen bg-[#ecedf3]">

            <div class="w-1/5 min-w-[250px] bg-[#1a2536] text-[rgb(204,200,200)] flex flex-col">
                <div class="menu-item admin-nav-btn" page="analytics">
                    <img src="./assets/icon/chart-line.svg" class="dark-img" alt="">
                    Thống kê
                </div>
                <h5>QUẢN LÝ THÔNG TIN </h5>
                <div class="menu-item admin-nav-btn" page="customer"> <img src="./assets/icon/users.svg" class="dark-img" alt="">
                    Quản lý khách hàng &#9662</div>
                <div class="submenu">
                    <div class="submenu-item">Thông tin khách hàng</div>
                </div>
                <div class="menu-item admin-nav-btn" page="employee"> <img src="./assets/icon/user-injured.svg" class="dark-img" alt="">
                    Quản lý nhân viên &#9662</div>
                <div class="submenu">
                    <div class="submenu-item">Thông tin nhân viên</div>
                    <div class="submenu-item">Thêm nhân viên</div>
                </div>
                <div class="menu-item"> <img src="./assets/icon/book.svg" class="dark-img" alt="">
                    Quản lí sản phẩm &#9662</div>
                <div class="submenu">
                    <div class="submenu-item admin-nav-btn" page="thongtinsanpham">Thông tin sản phẩm</div>
                    <div class="submenu-item admin-nav-btn" page="themsac">Thêm sản phẩm</div>
                    <div class="submenu-item admin-nav-btn" page="nhaphang">Nhập hàng</div>
                    <div class="submenu-item admin-nav-btn" page="thongtinnhaphang">Thông tin nhập hàng</div>
                </div>
                <div class="menu-item admin-nav-btn" page="quanlidon">
                    <img src="./assets/icon/cart-shopping.svg" class="dark-img" alt="">
                    Quản lý đơn hàng &#9662
                </div>
                <div class="submenu">
                    <div page="quanlidon" class="submenu-item admin-nav-btn">Duyệt đơn hàng</div>

                </div>
                <div class="menu-item admin-nav-btn" page="permission"> <img src="./assets/icon/credit-card.svg" class="dark-img" alt="">
                    Quản lý phân quyền &#9662</div>
                <div class="submenu">
                    <div class="submenu-item">Xem các quyền</div>
                    <div class="submenu-item">Thêm quyền</div>
                </div>
                <div class="menu-item admin-nav-btn"> <img src="./assets/icon/layer-group.svg" class="dark-img" alt="">
                    Quản lí danh mục &#9662</div>
                <div class="submenu">
                    <div class="submenu-item">Danh mục sản phẩm</div>
                    <div class="submenu-item">Thêm danh mục</div>
                    <div class="submenu-item">Thêm thiết kế</div>
                </div>
                <div class="menu-item admin-nav-btn"> <img src="./assets/icon/address-card.svg" class="dark-img" alt="">
                    Thông tin nhà cung cấp &#9662</div>
                <div class="submenu">
                    <div class="submenu-item">Thông tin nhà cung cấp</div>
                    <div class="submenu-item">Thông tin chi tiết nhà cung cấp</div>
                </div>
            </div>

            <div page="analytics" >
                <div id="main-content">
                    <?php include_once './gui/analytics.php' ?>
                </div>
            </div>
            <div page="quanlidon" >
                <div id="main-content">
                    <?php include_once './gui/quanlidon.php' ?>
                </div>
            </div>
            <div page="customer"    >
                <div id="main-content">
                    <?php include_once './gui/thongTinKhachHang.php' ?>
                </div>
            </div>
            <div page="employee" >
                <div id="main-content">
                    nhan vien
                </div>
            </div>
            <div page="thongtinsanpham" >
                <div id="main-content">
                    <?php require_once "sanphan.php" ?>
                </div>
            </div>
            <div page="themsac" >
                <div id="main-content">
                    <?php require_once "themsanpham.php" ?>
                </div>
            </div>
            <div page="nhaphang" >
                <div id="main-content">
                    nhập hàng
                </div>
            </div>
            <div page="thongtinnhaphang" >
                <div id="main-content">
                    thông tin phiếu nhập
                </div>
            </div>
            <div page="permission" >
                <div id="main-content">
                    <?php include './components/role_permissions.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="myModal" class="modal" style="display: none;">
        <div class="modal-content">
        </div>
    </div>


    <script>
        function closeSidebar() {
            let element = document.getElementById("sidebar");
            if (element.style.display === "none") {
                element.style.display = "flex";
                document.getElementById("header-left").style.backgroundColor = "#1a2536";
                document.getElementById("Fahahahasa").style.float = "left";
                document.getElementById("Fahahahasa").style.color = "rgb(254, 225, 225)";
                document.getElementById("closeSidebar").style.float = "right";
                document.getElementById("bars").classList.add("dark-img");
                document.querySelectorAll(".content").forEach((content) => {
                    content.style.width = "80%";
                });
                document.getElementById("content-container").style.justifyContent = "left";
            } else {
                element.style.display = "none";
                document.getElementById("header-left").style.backgroundColor = "white";
                document.getElementById("Fahahahasa").style.float = "right";
                document.getElementById("closeSidebar").style.float = "left";
                document.getElementById("bars").classList.remove("dark-img");
                document.querySelectorAll(".content").forEach((content) => {
                    content.style.width = "100%";
                });
                document.getElementById("Fahahahasa").style.color = "#080e18";
                document.getElementById("content-container").style.justifyContent = "center";
                openContent();
            }
        }

        function openContent() {
            document.querySelectorAll(".content").forEach((content) => {
                content.style.width = "100%";
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            const menuItems = document.querySelectorAll(".menu-item");

            menuItems.forEach(menuItem => {
                menuItem.addEventListener("click", function() {
                    let nextElement = menuItem.nextElementSibling;

                    if (nextElement && nextElement.classList.contains("submenu")) {
                        nextElement.classList.toggle("active");

                        document.querySelectorAll(".submenu").forEach(submenu => {
                            if (submenu !== nextElement) {
                                submenu.classList.remove("active");
                            }
                        });
                    }
                });
            });
        });
    </script>


</body>

</html>