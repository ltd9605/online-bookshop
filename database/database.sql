-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 24, 2025 lúc 10:35 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `ltw_ud2`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `bookName` varchar(255) NOT NULL,
  `subjectId` int(11) DEFAULT NULL,
  `classNumber` varchar(50) DEFAULT NULL,
  `oldPrice` decimal(10,2) DEFAULT NULL,
  `currentPrice` decimal(10,2) DEFAULT NULL,
  `quantitySold` int(11) DEFAULT 0,
  `imageURL` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `isActive` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `books`
--

INSERT INTO `books` (`id`, `bookName`, `subjectId`, `classNumber`, `oldPrice`, `currentPrice`, `quantitySold`, `imageURL`, `status`, `isActive`, `description`, `type`) VALUES
(1, 'Toán 6 - Giáo Khoa Cơ Bản', 2, '6', 18000.00, 16000.00, 166, 'https://sachhoc.com/image/cache/catalog/LuyenThi/Lop6-9/Sach-giao-khoa-toan-lop-6-tap-1-ket-noi-tri-thuc-voi-cuoc-song-500x554.jpg', 0, 1, 'Toán lớp 6 bản cơ bản.', 'Giáo Khoa Cơ Bản'),
(3, 'Toán 7 - Giáo Khoa Cơ Bản', 1, '7', 19000.00, 17000.00, 133, 'https://classbook.vn/static/covers/STK07TCBNC02/cover.clsbi', 0, 1, 'Toán lớp 7 bản cơ bản.', 'Giáo Khoa Cơ Bản'),
(4, 'Ngữ Văn 7 - Bài Tập', 2, '7', 21000.00, 19000.00, 85, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRqvFq42_zLyEGSnqgSIvQHnedai8cmUFR9DQ&s', 0, 1, 'Bài tập Ngữ văn lớp 7.', 'Bài Tập'),
(5, 'Toán 8 - Giáo Khoa Nâng Cao', 1, '8', 20000.00, 18000.00, 106, 'https://down-vn.img.susercontent.com/file/vn-11134208-7qukw-lk1ug2tsdm9u08', 0, 1, 'Toán lớp 8 nâng cao.', 'Giáo Khoa Nâng Cao'),
(6, 'Hóa Học 8 - Bài Tập', 7, '8', 22000.00, 20000.00, 71, '/LTW-UD2/images/Products/681d4a736e040_hoahoc-8-baitap.jpg', 0, 1, 'Bài tập Hóa học lớp 8.', 'Bài Tập'),
(7, 'Toán 9 - Ôn Thi vào 10', 1, '10', 25000.00, 23000.00, 150, 'https://sobee.vn/wp-content/uploads/2025/02/Bia-On-thi-vao-10-mon-Toan-1-600x853.jpg', 0, 1, 'Ôn thi Toán lớp 9 vào 10.', 'Ôn Thi'),
(8, 'Ngữ Văn 9 - Ôn Thi vào 10', 2, '9', 24000.00, 22000.00, 144, 'https://ebdbook.vn/upload/stk/lop9/ngu-van/lam-chu-kien-thuc-ngu-van-9-luyen-thi-vao-lop-10-phan-1-doc-hieu-van-ban/11-compressed.jpg?v=1.0.1', 1, 1, 'Ôn thi Ngữ văn lớp 9.', 'Ôn Thi'),
(9, 'Tiếng Anh 9 - Ôn Thi', 8, '9', 23000.00, 21000.00, 100, 'https://cdn1.fahasa.com/media/flashmagazine/images/page_images/tong_on_tieng_anh_9___tap_1_chuong_trinh_sgk_moi/2024_11_14_16_58_00_1-390x510.jpg', 1, 1, 'Ôn thi tiếng Anh lớp 9.', 'Ôn Thi'),
(10, 'Toán 9 - Giáo Khoa Cơ Bản', 1, '9', 24000.00, 22000.00, 90, 'https://img.websosanh.vn/v10/users/review/images/a9cwtpmu6641q/sgk-toan-lop-9-tap-2.jpg?compress=85', 1, 1, 'Toán lớp 9 cơ bản.', 'Giáo Khoa Cơ Bản'),
(11, 'Vật Lý 9 - Giáo Trình', 5, '9', 22500.00, 20500.00, 81, '/LTW-UD2/images/Products/681d6ab95cc65_681d4ce5e4212_vatlii9-giaokhoa.jpg', 1, 1, 'Vật lý lớp 9.', 'Giáo Trình'),
(12, 'Ngữ Văn 9 - Bài Tập', 2, '9', 22000.00, 20000.00, 95, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQoFovmyLGrJTbyg_rv2UsCqcgTPbb4onaOHw&s', 1, 1, 'Bài tập văn học lớp 9.', 'Bài Tập'),
(13, 'Sinh Học 9 - Ôn Thi', 6, '9', 23000.00, 21500.00, 65, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTE9q_gBT0Pvuds7_z_s_go_18krS2qah-_4w&s', 1, 1, 'Ôn thi Sinh học lớp 9.', 'Ôn Thi'),
(14, 'Hóa Học 9 - Ôn Thi', 7, '9', 23500.00, 21500.00, 60, 'https://down-vn.img.susercontent.com/file/db208c68264f1bd4d60237a97607a091', 1, 1, 'Ôn thi Hóa lớp 9.', 'Ôn Thi'),
(15, 'Toán 10 - Giáo Khoa Cơ Bản', 1, '10', 26000.00, 24000.00, 131, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRCVeWm0ZmbHqcJ-1E3OCKGYOr5RZapAF_xqA&s', 1, 1, 'Toán lớp 10 bản cơ bản.', 'Giáo Khoa Cơ Bản'),
(16, 'Ngữ Văn 10 - Bài Giảng', 2, '10', 27000.00, 25000.00, 95, 'https://hieusach24h.com/wp-content/uploads/2021/09/Ngu-Van-10-Tap-1-1.jpg', 1, 1, 'Bài giảng Văn học lớp 10.', 'Bài Giảng'),
(17, 'Toán 11 - Giáo Trình', 1, '11', 28000.00, 26000.00, 85, 'https://toanmath.com/wp-content/uploads/2022/12/sach-giao-khoa-toan-11-tap-1-canh-dieu.png', 1, 1, 'Giáo trình Toán lớp 11.', 'Giáo Trình'),
(18, 'Ngữ Văn 11 - Ôn Thi', 2, '11', 28500.00, 26500.00, 81, 'https://video.vietjack.com/upload/images/documents/banner/gk1-ctst-1687763095.png', 1, 1, 'Ôn thi Ngữ văn lớp 11.', 'Ôn Thi'),
(19, 'Toán 12 - Ôn Thi THPT', 1, '12', 32000.00, 30000.00, 180, 'https://toanmath.com/wp-content/uploads/2025/03/chuyen-de-on-thi-tot-nghiep-thpt-2025-mon-toan-nguyen-tien-ha.png', 1, 1, 'Ôn thi tốt nghiệp môn Toán.', 'Ôn Thi'),
(20, 'Ngữ Văn 12 - Ôn Thi THPT', 2, '12', 31000.00, 29000.00, 170, 'https://hieusach24h.com/wp-content/uploads/2021/09/Toan-5-1.jpg', 1, 1, 'Ôn thi tốt nghiệp môn Ngữ văn.', 'Ôn Thi'),
(21, 'Tiếng Anh 12 - Ôn Thi THPT', 8, '12', 30000.00, 28000.00, 150, 'https://sachhoc.com/image/cache/catalog/Sachtienganh/Luyen-thi/Lop10-12/12-chuyen-de-on-thi-thpt-quoc-gia-mon-tieng-anh-co-mai-phuong-500x554.jpg', 1, 1, 'Ôn thi tiếng Anh THPT.', 'Ôn Thi'),
(22, 'Toán 12 - Giáo Khoa Cơ Bản', 1, '12', 31000.00, 29000.00, 140, 'https://toanmath.com/wp-content/uploads/2016/12/sach-giao-khoa-giai-tich-12-co-ban.png', 1, 1, 'Toán lớp 12 cơ bản.', 'Giáo Khoa Cơ Bản'),
(23, 'Vật Lý 12 - Giáo Trình', 5, '12', 30000.00, 28000.00, 130, 'https://thuvienvatly.com/home/images/download_thumb/1dQt09bdxnqCEMpjHYZMfEaghJl8pJOe2.jpg', 1, 1, 'Giáo trình Vật lý lớp 12.', 'Giáo Trình'),
(24, 'Sinh Học 12 - Ôn Thi THPT', 6, '12', 30500.00, 28500.00, 115, 'https://sachhoc.com/image/cache/catalog/LuyenThi/Lop10-12/On-tap-mon-sinh-hoc-chuan-bi-cho-ki-thi-thpt-quoc-gia-500x554.jpg', 1, 1, 'Ôn thi Sinh học THPT.', 'Ôn Thi'),
(25, 'Hóa Học 12 - Ôn Thi THPT', 7, '12', 31000.00, 29000.00, 120, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQcE4fBjAP-DYqMifDj65Bt8SeBvGSRy8RdIA&s', 1, 1, 'Ôn thi Hóa học THPT.', 'Ôn Thi'),
(26, 'Ngữ Văn 12 - Bài Tập', 2, '12', 30000.00, 28000.00, 90, 'https://sachcanhdieu.vn/wp-content/uploads/2024/07/Bia-STKTY-Bai-tap-doc-hieu-Ngu-van-12-tap-1.png', 1, 1, 'Bài tập Văn học lớp 12.', 'Bài Tập'),
(35, 'testing', 2, '4', 234000.00, 2000.00, 0, '/LTW-UD2/images/Products/681f76c2839fd_Acer_Wallpaper_03_3840x2400.jpg', 0, 0, 'fiwwfwief', 'Giáo Khoa Nâng Cao');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

CREATE TABLE `cart` (
  `idCart` int(11) NOT NULL,
  `idUser` int(11) DEFAULT NULL,
  `totalPrice` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cart`
--

INSERT INTO `cart` (`idCart`, `idUser`, `totalPrice`) VALUES
(1, 1, 0.00),
(2, 2, 36000.00),
(3, 3, 957000.00),
(4, 4, 45000.00),
(5, 5, 43000.00),
(6, 6, 40500.00),
(7, 7, 43000.00),
(8, 8, 49000.00),
(9, 9, 52500.00),
(10, 10, 59000.00),
(11, 11, 57000.00),
(12, 12, 56500.00),
(13, 13, 29000.00),
(14, 14, 230000.00),
(15, 15, 240000.00),
(16, 16, 0.00),
(17, 17, 0.00),
(18, 20, 0.00),
(19, 21, 0.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cartitems`
--

CREATE TABLE `cartitems` (
  `id` int(11) NOT NULL,
  `bookId` int(11) DEFAULT NULL,
  `cartId` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cartitems`
--

INSERT INTO `cartitems` (`id`, `bookId`, `cartId`, `amount`) VALUES
(4, 3, 2, 1),
(7, 4, 4, 1),
(8, 5, 4, 1),
(9, 5, 5, 1),
(10, 6, 5, 1),
(11, 6, 6, 1),
(12, 7, 6, 1),
(13, 7, 7, 1),
(14, 8, 7, 1),
(15, 8, 8, 1),
(16, 9, 8, 1),
(17, 9, 9, 1),
(18, 10, 9, 1),
(19, 10, 10, 1),
(20, 11, 10, 1),
(21, 11, 11, 1),
(22, 12, 11, 1),
(23, 12, 12, 1),
(24, 13, 12, 1),
(25, 13, 13, 1),
(26, 14, 13, 1),
(27, 14, 14, 1),
(28, 15, 14, 1),
(29, 15, 15, 1),
(30, 16, 15, 1),
(58, 5, 3, 34),
(59, 3, 3, 14),
(60, 1, 3, 2),
(61, 19, 3, 1),
(62, 17, 3, 1),
(63, 4, 3, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitiethoadon`
--

CREATE TABLE `chitiethoadon` (
  `id` int(11) NOT NULL,
  `idBook` int(11) DEFAULT NULL,
  `idHoadon` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `pricePerItem` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitiethoadon`
--

INSERT INTO `chitiethoadon` (`id`, `idBook`, `idHoadon`, `amount`, `pricePerItem`) VALUES
(33, 17, 57, 5, 26000),
(34, 24, 57, 5, 28500),
(35, 19, 58, 5, 30000),
(36, 10, 59, 5, 22000),
(37, 15, 60, 1, 24000),
(38, 17, 61, 1, 26000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietphieunhap`
--

CREATE TABLE `chitietphieunhap` (
  `id` int(11) NOT NULL,
  `idPhieuNhap` int(11) NOT NULL,
  `idBook` int(11) NOT NULL,
  `idCungCap` int(11) NOT NULL,
  `soluong` int(11) NOT NULL,
  `gianhap` decimal(10,2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `loinhuan` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietphieunhap`
--

INSERT INTO `chitietphieunhap` (`id`, `idPhieuNhap`, `idBook`, `idCungCap`, `soluong`, `gianhap`, `status`, `loinhuan`) VALUES
(8, 10, 6, 1, 1, 16000.00, 1, 10),
(9, 10, 5, 1, 1, 14000.00, 1, 10),
(10, 11, 8, 2, 4, 18000.00, 1, 10),
(11, 12, 5, 1, 1, 14000.00, 1, 10),
(12, 12, 5, 1, 9, 14000.00, 1, 10),
(13, 12, 11, 1, 1, 16000.00, 1, 10),
(14, 13, 18, 1, 1, 21000.00, 1, 10),
(15, 13, 15, 1, 1, 19000.00, 1, 10);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chucnang`
--

CREATE TABLE `chucnang` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chucnang`
--

INSERT INTO `chucnang` (`id`, `name`) VALUES
(5, 'Quản lí khách hàng'),
(6, 'Thống kê'),
(7, 'Quản lý sản phẩm'),
(8, 'Quản lý nhân viên'),
(9, 'Đánh giá'),
(10, 'Quản lý đơn hàng');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoadon`
--

CREATE TABLE `hoadon` (
  `idBill` int(11) NOT NULL,
  `idUser` int(11) DEFAULT NULL,
  `nhanvien_id` int(11) DEFAULT NULL,
  `totalBill` decimal(10,2) DEFAULT NULL,
  `paymentMethod` varchar(50) DEFAULT NULL,
  `statusBill` int(11) DEFAULT 1,
  `ly_do_huy` varchar(50) DEFAULT NULL,
  `ly_do_tra_hang` varchar(50) DEFAULT NULL,
  `create_at` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp(),
  `id_diachi` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hoadon`
--

INSERT INTO `hoadon` (`idBill`, `idUser`, `nhanvien_id`, `totalBill`, `paymentMethod`, `statusBill`, `ly_do_huy`, `ly_do_tra_hang`, `create_at`, `ngay_cap_nhat`, `id_diachi`) VALUES
(57, 16, NULL, 272500.00, 'Tien mat', 3, NULL, NULL, '2025-05-10 21:07:02', '2025-05-10 21:07:02', 16),
(58, 16, NULL, 150000.00, 'Tien mat', 3, NULL, NULL, '2025-03-12 21:09:41', '2025-05-10 21:09:41', 17),
(59, 17, NULL, 110000.00, 'Tien mat', 3, NULL, NULL, '2025-04-24 21:15:08', '2025-05-10 21:15:08', 18),
(60, 20, NULL, 24000.00, 'Tien mat', 1, NULL, NULL, '2025-05-17 08:40:35', '2025-05-17 08:40:35', 19),
(61, 21, NULL, 26000.00, 'Chuyen khoan', 1, NULL, NULL, '2025-05-20 22:15:16', '2025-05-20 22:15:16', 20);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoadonnhap`
--

CREATE TABLE `hoadonnhap` (
  `id` int(11) NOT NULL,
  `tongtien` decimal(15,2) DEFAULT NULL,
  `idNguoiNhap` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hoadonnhap`
--

INSERT INTO `hoadonnhap` (`id`, `tongtien`, `idNguoiNhap`, `date`, `status`) VALUES
(10, 30000.00, 1, '2025-05-11 00:00:00', 1),
(11, 72000.00, 1, '2025-05-11 00:00:00', 1),
(12, 156000.00, 1, '2025-04-15 00:00:00', 1),
(13, 40000.00, 1, '2025-03-20 00:00:00', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoadon_trangthai`
--

CREATE TABLE `hoadon_trangthai` (
  `id` int(11) NOT NULL,
  `idBill` int(11) NOT NULL,
  `id_nhanvien` int(11) DEFAULT NULL,
  `trangthai` varchar(50) NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `create_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hoadon_trangthai`
--

INSERT INTO `hoadon_trangthai` (`id`, `idBill`, `id_nhanvien`, `trangthai`, `ghi_chu`, `create_at`) VALUES
(1, 57, NULL, '3', NULL, '2025-05-10 21:09:03'),
(2, 58, NULL, '3', NULL, '2025-05-10 21:09:57'),
(3, 58, NULL, '2', NULL, '2025-05-10 21:10:48'),
(4, 58, NULL, '3', NULL, '2025-05-10 21:12:24'),
(5, 59, NULL, '3', NULL, '2025-05-10 21:15:38');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `manage`
--

CREATE TABLE `manage` (
  `id` int(11) NOT NULL,
  `manage_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `manage`
--

INSERT INTO `manage` (`id`, `manage_name`) VALUES
(1, 'Xem'),
(2, 'Thêm'),
(3, 'Sửa'),
(4, 'Xóa');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhacungcap`
--

CREATE TABLE `nhacungcap` (
  `id` int(11) NOT NULL,
  `name` varchar(156) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nhacungcap`
--

INSERT INTO `nhacungcap` (`id`, `name`) VALUES
(1, 'Công ty Cổ Phần Sách Mcbooks'),
(2, 'Công ty Phát Hành Sách Tp.HCM');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `review`
--

CREATE TABLE `review` (
  `id` int(11) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `review` text DEFAULT NULL,
  `bookId` int(11) DEFAULT NULL,
  `create_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `review`
--

INSERT INTO `review` (`id`, `userId`, `rating`, `review`, `bookId`, `create_at`) VALUES
(1, 1, 5, 'Sách rất hữu ích và dễ hiểu.', 1, '2025-04-29 17:56:11'),
(3, 2, 4, 'Trình bày rõ ràng, dễ tiếp thu.', 3, '2025-04-29 17:56:11'),
(4, 2, 5, 'Giá hợp lý, chất lượng tốt.', 4, '2025-04-29 17:56:11'),
(5, 3, 3, 'Còn một số lỗi nhỏ, nhưng tạm ổn.', 5, '2025-04-29 17:56:11'),
(6, 3, 4, 'Rất đáng để mua.', 6, '2025-04-29 17:56:11'),
(7, 4, 5, 'Giúp mình ôn thi hiệu quả.', 7, '2025-04-29 17:56:11'),
(8, 4, 4, 'Minh họa sinh động, học dễ vào.', 8, '2025-04-29 17:56:11'),
(9, 5, 5, 'Giáo viên cũng khuyên dùng sách này.', 9, '2025-04-29 17:56:11'),
(10, 5, 4, 'Giao hàng nhanh, sách mới.', 10, '2025-04-29 17:56:11'),
(11, 6, 4, 'Giấy in đẹp, dễ đọc.', 11, '2025-04-29 17:56:11'),
(12, 6, 3, 'Sách hơi mỏng nhưng nội dung tốt.', 12, '2025-04-29 17:56:11'),
(13, 7, 5, 'Học xong tiến bộ rõ rệt.', 13, '2025-04-29 17:56:11'),
(14, 7, 4, 'Nội dung bám sát đề cương.', 14, '2025-04-29 17:56:11'),
(15, 8, 4, 'Thích hợp để tự học ở nhà.', 15, '2025-04-29 17:56:11'),
(16, 8, 5, 'Mỗi chương đều được trình bày chi tiết.', 16, '2025-04-29 17:56:11'),
(17, 9, 3, 'Tạm được, có thể cải thiện hơn.', 17, '2025-04-29 17:56:11'),
(18, 9, 5, 'Rất đáng giá tiền.', 18, '2025-04-29 17:56:11'),
(19, 10, 4, 'Có cả bài tập và lý thuyết.', 19, '2025-04-29 17:56:11'),
(20, 10, 5, 'Sách hay, trình bày dễ hiểu.', 20, '2025-04-29 17:56:11'),
(21, 11, 5, 'Rất phù hợp với học sinh cấp 2.', 21, '2025-04-29 17:56:11'),
(22, 11, 4, 'Bố cục khoa học, dễ học.', 22, '2025-04-29 17:56:11'),
(23, 12, 4, 'Bài tập phong phú.', 23, '2025-04-29 17:56:11'),
(24, 12, 5, 'Nội dung đầy đủ, chi tiết.', 24, '2025-04-29 17:56:11'),
(25, 13, 5, 'Giúp mình ôn tập hiệu quả.', 25, '2025-04-29 17:56:11'),
(26, 13, 4, 'Sách tốt hơn mong đợi.', 26, '2025-04-29 17:56:11'),
(27, 14, 4, 'Nội dung rõ ràng và dễ hiểu.', 1, '2025-04-29 17:56:11'),
(29, 15, 5, 'Cực kỳ hài lòng với cuốn sách.', 3, '2025-04-29 17:56:11'),
(30, 15, 4, 'Sẽ giới thiệu cho bạn bè.', 4, '2025-04-29 17:56:11'),
(31, 21, 5, 'abcd', 15, '2025-05-20 22:17:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `rolepermissions`
--

CREATE TABLE `rolepermissions` (
  `id` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  `chucnang_id` int(11) NOT NULL,
  `manage_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `rolepermissions`
--

INSERT INTO `rolepermissions` (`id`, `roleId`, `chucnang_id`, `manage_id`) VALUES
(51, 1, 5, 1),
(52, 1, 5, 2),
(53, 1, 5, 3),
(54, 1, 5, 4),
(55, 1, 6, 1),
(56, 1, 6, 2),
(57, 1, 6, 3),
(58, 1, 6, 4),
(59, 1, 7, 1),
(60, 1, 7, 2),
(61, 1, 7, 3),
(62, 1, 7, 4),
(63, 1, 8, 1),
(64, 1, 8, 2),
(65, 1, 8, 3),
(66, 1, 8, 4),
(67, 1, 9, 1),
(68, 1, 9, 2),
(69, 1, 9, 3),
(70, 1, 9, 4),
(71, 1, 10, 1),
(72, 1, 10, 2),
(73, 1, 10, 3),
(74, 1, 10, 4),
(75, 2, 9, 1),
(76, 2, 9, 2),
(77, 2, 9, 3),
(78, 2, 9, 4),
(79, 2, 10, 1),
(80, 2, 10, 2),
(81, 2, 10, 3),
(82, 2, 10, 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Nhân viên bán hàng'),
(3, 'Quản lý kho');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subjectName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `subjects`
--

INSERT INTO `subjects` (`id`, `subjectName`) VALUES
(1, 'Toán'),
(2, 'Ngữ Văn'),
(3, 'Lịch Sử'),
(4, 'Địa Lý'),
(5, 'Vật Lý'),
(6, 'Sinh Học'),
(7, 'Hóa Học'),
(8, 'Tiếng Anh');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thongtingiaohang`
--

CREATE TABLE `thongtingiaohang` (
  `id` int(11) NOT NULL,
  `tennguoinhan` varchar(100) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `diachi` varchar(255) DEFAULT NULL,
  `thanhpho` varchar(25) DEFAULT NULL,
  `quan` varchar(25) DEFAULT NULL,
  `huyen` varchar(25) DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thongtingiaohang`
--

INSERT INTO `thongtingiaohang` (`id`, `tennguoinhan`, `id_user`, `diachi`, `thanhpho`, `quan`, `huyen`, `sdt`, `status`) VALUES
(1, 'Hồ Minh Hùng', 1, '123 Đường Trần Hưng Đạo', 'Hồ Chí Minh', 'Quận 1', 'Phường Bến Thành', '0901000001', 1),
(2, 'Lê Văn Luyện', 2, '45 Đường Nguyễn Huệ', 'Hồ Chí Minh', 'Quận 1', 'Phường Nguyễn Thái Bình', '0902000002', 1),
(3, 'Trần Thị Mai', 3, '78 Đường Lý Tự Trọng', 'Hồ Chí Minh', 'Quận 3', 'Phường Võ Thị Sáu', '0903000003', 1),
(4, 'Nguyễn Hữu Tài', 4, '89 Đường Cách Mạng Tháng 8', 'Hồ Chí Minh', 'Quận 10', 'Phường 13', '0904000004', 1),
(5, 'Đặng Thanh Tâm', 5, '32 Đường Tô Hiến Thành', 'Hồ Chí Minh', 'Quận 10', 'Phường 15', '0905000005', 1),
(6, 'Phan Ngọc Trân', 6, '14 Đường 3 Tháng 2', 'Hồ Chí Minh', 'Quận 11', 'Phường 5', '0906000006', 1),
(7, 'Võ Minh Tùng', 7, '23 Đường Lạc Long Quân', 'Hồ Chí Minh', 'Quận 11', 'Phường 1', '0907000007', 1),
(8, 'Bùi Thị Hương', 8, '56 Đường Âu Cơ', 'Hồ Chí Minh', 'Tân Phú', 'Phường Phú Thọ Hòa', '0908000008', 1),
(9, 'Lâm Văn Toàn', 9, '67 Đường Trường Chinh', 'Hồ Chí Minh', 'Tân Bình', 'Phường 14', '0909000009', 1),
(10, 'Trịnh Thanh Long', 10, '89 Đường Nguyễn Văn Cừ', 'Hồ Chí Minh', 'Quận 5', 'Phường 2', '0910000010', 1),
(11, 'Ngô Minh Hiếu', 11, '102 Đường Phan Văn Trị', 'Hồ Chí Minh', 'Gò Vấp', 'Phường 7', '0911000011', 1),
(12, 'Đỗ Thị Lan', 12, '12 Đường Dương Quảng Hàm', 'Hồ Chí Minh', 'Gò Vấp', 'Phường 5', '0912000012', 1),
(13, 'Hoàng Trung Nghĩa', 13, '21 Đường Nguyễn Kiệm', 'Hồ Chí Minh', 'Phú Nhuận', 'Phường 3', '0913000013', 1),
(14, 'Tô Hoàng Nam', 14, '34 Đường Huỳnh Văn Bánh', 'Hồ Chí Minh', 'Phú Nhuận', 'Phường 17', '0914000014', 1),
(15, 'Vũ Thị Ngọc Hà', 15, '90 Đường Trần Quang Diệu', 'Hồ Chí Minh', 'Quận 3', 'Phường 14', '0915000015', 1),
(16, 'Bao Huynh', 16, 'jfiewf23', 'Ninh Bình', 'Thành phố Ninh Bình', 'Phường Đông Thành', '0776929128', 0),
(17, 'Bao Huynh', 16, 'jfiewf23', 'Ninh Bình', 'Thành phố Ninh Bình', 'Phường Đông Thành', '0776929128', 0),
(18, 'Dong', 17, '8 lạc', 'Khánh Hòa', 'Thành phố Nha Trang', 'Phường Vĩnh Trường', '0914356422', 0),
(19, 'abc', 20, 'adadada', 'Hồ Chí Minh', 'Quận 3', 'Phường 12', '0364002647', 0),
(20, 'Lê Công', 21, '772 Trần Hưng Đạo', 'Hồ Chí Minh', 'Quận 5', 'Phường 7', '0123456789', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `userName` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status_user` tinyint(1) DEFAULT NULL,
  `fullName` varchar(100) DEFAULT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `role_id`, `userName`, `password`, `email`, `avatar`, `status_user`, `fullName`, `phoneNumber`, `created_at`) VALUES
(1, 1, 'Admin', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'admin@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Admin', '0911111234', '2025-05-10 00:00:00'),
(2, 2, 'minhtran', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'minhtran@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Minh Trần', '0911222333', '2025-05-10 00:00:00'),
(3, 1, 'thaole', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'thaole@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Thảo Lê', '0911333444', '2025-05-10 00:00:00'),
(4, 2, 'huypham', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'huypham@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Huy Phạm', '0911444555', '2025-05-10 00:00:00'),
(5, 3, 'lanho', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'lanho@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Lan Hồ', '0911555666', '2025-05-10 00:00:00'),
(6, 3, 'namdang', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'namdang@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Nam Đặng', '0911666777', '2025-05-10 00:00:00'),
(7, 2, 'tuvu', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'tuvu@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Tú Vũ', '0911777888', '2025-05-10 00:00:00'),
(8, NULL, 'quynhanh', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'quynhanh@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Quỳnh Anh', '0911888999', '2025-05-10 00:00:00'),
(9, NULL, 'michaelng', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'michael@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Michael Nguyen', '0911999000', '2025-05-10 00:00:00'),
(10, NULL, 'jessicatrinh', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'jessica@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Jessica Trinh', '0912000111', '2025-05-10 00:00:00'),
(11, NULL, 'tommyle', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'tommy@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Tommy Le', '0912111222', '2025-05-10 00:00:00'),
(12, NULL, 'davidhoang', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'david@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'David Hoang', '0912222333', '2025-05-10 00:00:00'),
(13, NULL, 'emilydang', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'emily@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Emily Dang', '0912333444', '2025-05-10 00:00:00'),
(14, NULL, 'chloephan', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'chloe@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Chloe Phan', '0912444555', '2025-05-10 00:00:00'),
(15, NULL, 'anthonytran', '$2y$10$IMFONH6sSOMQbjUElSu/Qe2NNqUfoH6eaPgwQsuLmgWQdNN8kl72e', 'anthony@gmail.com', 'https://icons.iconarchive.com/icons/papirus-team/papirus-status/512/avatar-default-icon.png', 1, 'Anthony Tran', '0912555666', '2025-05-10 00:00:00'),
(16, NULL, NULL, '$2y$10$dq0kE3fbl6ydrczj8jB.5.EKbKjtM/2R8UhdzNYdGpbjgri/xyq/2', NULL, NULL, NULL, 'New user', '0776929128', '2025-05-10 00:00:00'),
(17, NULL, NULL, '$2y$10$yIUCcJ.Kq7ZYlxBgILzbMO.Q3vnLqPjOOVzNA9TnKORw/i2ZGTY6K', NULL, NULL, NULL, 'New user', '0914356422', '2025-05-10 00:00:00'),
(18, NULL, 'user_6987', '$2y$10$C1pj1HI2.V4trMtd5jnzB.cNIRhuqWIoaoE4kgGRpi7FfGH7REl3C', NULL, 'default.png', 1, 'Người dùng mới', '123', '2025-05-17 03:02:26'),
(19, NULL, 'user_3286', '$2y$10$TI5VPVPVoNKXbkFo4daZ2O8N/f6dp4yeA6EsBdkqxRLI6PY7sKBfS', NULL, 'default.png', 1, 'Người dùng mới', 'abc', '2025-05-17 03:14:56'),
(20, NULL, 'user_1253', '$2y$10$hTWLkdTFdxfyEmALBcpc6uGNnIbBftyUMWDbrx.H/vPvEblqmq4sm', NULL, 'default.png', 1, 'Người dùng mới', '0364002647', '2025-05-17 03:34:25'),
(21, NULL, 'lecong123', '$2y$10$Gm3W2ZStEVljgqFm3riePOQFbxBWvUuUfls4xaH7m1w85Iwmm6ctK', 'lecong@gmail.com', 'default.png', 1, 'Lê Công', '0123456789', '2025-05-20 17:11:36');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subjectId` (`subjectId`);

--
-- Chỉ mục cho bảng `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`idCart`),
  ADD KEY `idUser` (`idUser`);

--
-- Chỉ mục cho bảng `cartitems`
--
ALTER TABLE `cartitems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookId` (`bookId`),
  ADD KEY `cartId` (`cartId`);

--
-- Chỉ mục cho bảng `chitiethoadon`
--
ALTER TABLE `chitiethoadon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idBook` (`idBook`),
  ADD KEY `idHoadon` (`idHoadon`);

--
-- Chỉ mục cho bảng `chitietphieunhap`
--
ALTER TABLE `chitietphieunhap`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idPhieuNhap` (`idPhieuNhap`),
  ADD KEY `idBook` (`idBook`),
  ADD KEY `idCungCap` (`idCungCap`);

--
-- Chỉ mục cho bảng `chucnang`
--
ALTER TABLE `chucnang`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  ADD PRIMARY KEY (`idBill`),
  ADD KEY `idUser` (`idUser`),
  ADD KEY `nhanvien_id` (`nhanvien_id`),
  ADD KEY `fk_hoadon_diachi` (`id_diachi`);

--
-- Chỉ mục cho bảng `hoadonnhap`
--
ALTER TABLE `hoadonnhap`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idNguoiNhap` (`idNguoiNhap`);

--
-- Chỉ mục cho bảng `hoadon_trangthai`
--
ALTER TABLE `hoadon_trangthai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idBill` (`idBill`),
  ADD KEY `id_nhanvien` (`id_nhanvien`);

--
-- Chỉ mục cho bảng `manage`
--
ALTER TABLE `manage`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `nhacungcap`
--
ALTER TABLE `nhacungcap`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `bookId` (`bookId`);

--
-- Chỉ mục cho bảng `rolepermissions`
--
ALTER TABLE `rolepermissions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Chỉ mục cho bảng `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `thongtingiaohang`
--
ALTER TABLE `thongtingiaohang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`userName`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `userName_2` (`userName`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT cho bảng `cart`
--
ALTER TABLE `cart`
  MODIFY `idCart` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `cartitems`
--
ALTER TABLE `cartitems`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT cho bảng `chitiethoadon`
--
ALTER TABLE `chitiethoadon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT cho bảng `chitietphieunhap`
--
ALTER TABLE `chitietphieunhap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `chucnang`
--
ALTER TABLE `chucnang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  MODIFY `idBill` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT cho bảng `hoadonnhap`
--
ALTER TABLE `hoadonnhap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `hoadon_trangthai`
--
ALTER TABLE `hoadon_trangthai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `manage`
--
ALTER TABLE `manage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `nhacungcap`
--
ALTER TABLE `nhacungcap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `review`
--
ALTER TABLE `review`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT cho bảng `rolepermissions`
--
ALTER TABLE `rolepermissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT cho bảng `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `thongtingiaohang`
--
ALTER TABLE `thongtingiaohang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`subjectId`) REFERENCES `subjects` (`id`);

--
-- Các ràng buộc cho bảng `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `cartitems`
--
ALTER TABLE `cartitems`
  ADD CONSTRAINT `cartitems_ibfk_1` FOREIGN KEY (`bookId`) REFERENCES `books` (`id`),
  ADD CONSTRAINT `cartitems_ibfk_2` FOREIGN KEY (`cartId`) REFERENCES `cart` (`idCart`);

--
-- Các ràng buộc cho bảng `chitiethoadon`
--
ALTER TABLE `chitiethoadon`
  ADD CONSTRAINT `chitiethoadon_ibfk_1` FOREIGN KEY (`idBook`) REFERENCES `books` (`id`),
  ADD CONSTRAINT `chitiethoadon_ibfk_2` FOREIGN KEY (`idHoadon`) REFERENCES `hoadon` (`idBill`);

--
-- Các ràng buộc cho bảng `chitietphieunhap`
--
ALTER TABLE `chitietphieunhap`
  ADD CONSTRAINT `fkBook` FOREIGN KEY (`idBook`) REFERENCES `books` (`id`),
  ADD CONSTRAINT `fkPhieuNhap` FOREIGN KEY (`idPhieuNhap`) REFERENCES `hoadonnhap` (`id`),
  ADD CONSTRAINT `fkncc` FOREIGN KEY (`idCungCap`) REFERENCES `nhacungcap` (`id`);

--
-- Các ràng buộc cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  ADD CONSTRAINT `fk_hoadon_diachi` FOREIGN KEY (`id_diachi`) REFERENCES `thongtingiaohang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hoadon_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `hoadon_ibfk_2` FOREIGN KEY (`nhanvien_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `hoadonnhap`
--
ALTER TABLE `hoadonnhap`
  ADD CONSTRAINT `fkNguoiNhap` FOREIGN KEY (`idNguoiNhap`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `hoadon_trangthai`
--
ALTER TABLE `hoadon_trangthai`
  ADD CONSTRAINT `hoadon_trangthai_ibfk_1` FOREIGN KEY (`idBill`) REFERENCES `hoadon` (`idBill`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hoadon_trangthai_ibfk_2` FOREIGN KEY (`id_nhanvien`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`bookId`) REFERENCES `books` (`id`);

--
-- Các ràng buộc cho bảng `thongtingiaohang`
--
ALTER TABLE `thongtingiaohang`
  ADD CONSTRAINT `thongtingiaohang_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
