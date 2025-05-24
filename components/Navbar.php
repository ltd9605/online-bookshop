
<div class="navbar">
    <div class="logo">
        <h1>Book</h1>
    </div>
    <div class="search-bar">
        <input type="text" placeholder="Tìm kiếm sản phẩm...">
        <button><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <div class="nav-icons">
        <a href="#" class="notifications"><i class="fa-solid fa-bell"></i></a>

        <div class="dropdown">
            <button class="cart"><i class="fa-solid fa-shopping-cart"></i></button>
            <div class="submenu">
                <button>Xem giỏ hàng</button>
                <button>Thanh toán</button>
            </div>
        </div>
        <div class="dropdown">
        <button class="account"><i class="fa-solid fa-user"></i></button>
        <div class="submenu">
        <button id="login-btn">Đăng nhập</button>
        <button id="signin-btn">Đăng ký</button>
        </div>
    </div>
</div>

    </div>
</div>
<nav class="menu">
    <ul>
        <li><a href="#">Trang chủ</a></li>
        <li class="dropdown">
            <a href="#">Danh mục sản phẩm</a>
            <ul class="submenu">
                <li><a href="#">Sách</a></li>
                <li><a href="#">Văn phòng phẩm</a></li>
                <li><a href="#">Quà lưu niệm</a></li>
            </ul>
        </li>
        <li><a href="#">Khuyến mãi</a></li>
        <li><a href="#">Hỗ trợ</a></li>
    </ul>
</nav>
<style>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 10px 20px;
    border-bottom: 1px solid #ddd;
}

.logo h1 {
    color: red;
    font-size: 24px;
    font-weight: bold;
}

.search-bar {
    position: relative;
    width: 70%;
}

.search-bar input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.search-bar button {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    background: red;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
}

.nav-icons {
    display: flex;
    align-items: center;
    gap: 15px;
}

.nav-icons a, .dropdown button {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
}

.dropdown {
    position: relative;
}
.dropdown:hover .submenu {
    display: block;
}
.submenu {
    display: none;
    position: absolute;
    right: 0;
    background: white;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    padding: 10px;
    border-radius: 5px;
    width: 200px;
    z-index: 10;
}
#login-btn {
    background: red;
    color: white;
    padding: 5px;
    border-radius: 5px;
    width: 100%;
    text-align: center;
    margin-top: 10px;
}
#signin-btn{
    background: white;
    color: red;
    border: solid 1px red;
    padding: 5px;
    border-radius: 5px;
    width: 100%;
    text-align: center;
    margin-top: 10px;
}
.submenu button {
    display: block;
    width: 100%;
    background: none;
    border: none;
    padding: 10px;
    text-align: left;
    cursor: pointer;
}

.menu {
    background:rgb(211, 42, 42);
    padding: 10px 0;
    text-align: center;
    margin: 10px;

}

.menu ul {
    list-style: none;
    display: flex;
    justify-content: center;
    gap: 20px;
}

.menu ul li {
    position: relative;
}

.menu ul li a {
    text-decoration: none;
    color: black;
    font-weight: bold;
}

.menu .dropdown .submenu {
    position: absolute;
    left: 0;
    background: white;
    display: none;
    flex-direction: column;
    padding: 10px;
}

.menu .dropdown:hover .submenu {
    display: block;
}

</style>
