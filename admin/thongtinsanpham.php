<?php

require_once("../database/database.php");
require_once("../database/book.php");


    $product = new BooksTable($pdo);
    $products = $product->getAllBook();

    
    if (isset($_POST['update-status']) && isset($_POST['id']) && isset($_POST['isActive'])) {
        $id = $_POST['id'];
        $isActive = $_POST['isActive'];

        $product = new BooksTable($pdo);
        $result = $product->changeActive($id, $isActive);

        if ($result) {
            $message = $isActive == 1 ? "Sản phẩm đã được cập nhật để bán!" : "Sản phẩm đã được ngừng bán!";
            echo json_encode([
                'success' => true,
                'message' => $message,
                'newStatus' => $isActive 
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Đã xảy ra lỗi khi cập nhật trạng thái sản phẩm!"
            ]);
        }
        exit();
    }


    if (isset($_POST['delete-product'])) {
        $id = $_POST['id'];
        $product->deleteById($id);
        exit();
    }

    $itemPerPage = 4;
    $currentPage = isset($_POST['currentPage']) ? (int)$_POST['currentPage'] : 1;
    $search = isset($_POST['valueSearch']) ? $_POST['valueSearch'] : "";

    $offset = ($currentPage - 1) * $itemPerPage;

    $sqlCount = "SELECT COUNT(*) as total FROM books WHERE status = 1";
    if ($search !== "") {
        $sqlCount .= " AND bookName LIKE '%$search%'";
    }
    $totalResult = $pdo->query($sqlCount)->fetch();
    $totalItems = $totalResult['total'];
    $totalPages = ceil($totalItems / $itemPerPage);

    $sql = "SELECT * FROM books
        WHERE books.status = 1";

    if ($search != "") {
        $sql .= " AND books.bookName like '%$search%'";
    }
    $sql .= " LIMIT $itemPerPage OFFSET $offset";
    $products = $product->getBooksByCondition($sql);
    // foreach ($products as $product) {
    // echo $product['id']; 
// }
?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><div>Môn học</div></th>
                <th><div>Tên sách</div></th>
                <th><div>Lớp</div></th>
                <th><div>Giá bán</div></th>
                <th><div>Hình ảnh</div></th>
                <th><div>Số lượng</div></th>
                <th><div>Mô tả</div></th>

                <?php
                // if ($checkDeleteAndUpdate) {
                    ?>
                    <th>Chức năng</th>
                    <?php
                // }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!$products) {
                ?>

                <tr>
                    <td style="padding: 15px;" colspan="8">Không tìm thấy sản phẩm</td>
                </tr>
                <?php
            }
            $book = new BooksTable($pdo);
            $vnd ='đ';
            foreach ($products as $product) {
                ?>
                <tr>
                    <td>
                        <div>
                            <?php echo $book->getSubjectNameById($product['subjectId']) ?>
                        </div>
                        
                    </td>
                    <td>
                        <?php echo $product['bookName'] ?>
                    </td>
                    <td>
                        <div>
                            <?php echo $product['classNumber'] ?>
                        </div>
                    </td>
                    <td>
                        <div>
                        <?php echo number_format($product['currentPrice'], 0, ',', '.') . 'đ';?>
                        </div>
                    </td>
                    <td>
                        <div>
                        <img src="<?php echo $product['imageURL'] ?>" alt="" width="70" height="70">
                        </div>
                    </td>
                    <td>
                        <div>
                            <?php echo $product['quantitySold'] ?>
                        </div>
                        
                    </td>
                    <td>
                        <?php echo $product['description'] ?>
                    </td>

                    <td>
                        <div class="icon">
                            <i class="fa-solid fa-trash delete-icon" data-id="<?php echo $product['id'] ?>"></i>
                            <i class="fa-regular fa-pen-to-square update-icon"  data-id="<?php echo $product['id'] ?>" id="openModalBtn" ></i>
                            <?php if ($product['isActive'] == 1): ?>
                                <i class="fa-solid fa-toggle-on check-icon" data-id="<?php echo $product['id'] ?>" style="color:green;" title="Đang bán"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-toggle-off check-icon" data-id="<?php echo $product['id'] ?>" style="color:red;" title="Ngừng bán"></i>
                            <?php endif; ?>



                        </div>

                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>
<?php if ($totalPages > 1): ?>
    <div class="sp-pagination">
        <ul class="sp-pagination-list">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="sp-page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                    <a class="sp-page-link page-number" href="#" data-page="<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </div>
<?php endif; ?>
<div id="myModal" class="modal" style="display: none;">
        <div class="modal-content">
        </div>
    </div>



<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.table-responsive{
    width: 100% !important;
    overflow-x: auto;
}
.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(33, 37, 41, 0.05);
}
.table-hover tbody tr:hover {
    background-color: rgba(33, 37, 41, 0.075);
}
.table > tbody > tr.active,
.table > tbody > tr.active > td {
    background-color: rgba(33, 37, 41, 0.1);
}
.table td:nth-child(3) div,
.table td:nth-child(6) div,
.table td:nth-child(4) div,
.table td:nth-child(5) div{
    text-align: center;
    padding: 20px 20px;
}
.table th:nth-child(3) div,
.table th:nth-child(6) div,
.table th:nth-child(4) div,
.table th:nth-child(5) div{
    text-align: center;
    padding: 20px 20px;
}
.table th:nth-child(1),
.table td:nth-child(1) div{
    padding-left: 30px;
    padding-right: 15px;
}
.table th:nth-child(8){
    padding: 10px 15px;
    text-align: center;
}
.table td:nth-child(8) {
    padding: 10px 10px 10px 20px;
}
.icon {
    display: flex;
    gap: 12px; 
    align-items: center;
}
.icon i {
    font-size: 20px;
    cursor: pointer;
    transition: color 0.2s ease;
}
.delete-icon {
    color: #C7422F;
}
.delete-icon:hover {
    color: #a00000;
    font-size: 23px;
}
.update-icon {
    color: #0079BC;
}
.update-icon:hover {
    color: #005f94;
    font-size: 23px;
}
.check-icon{
    font-size: 20px;
}
.check-icon:hover{
    font-size: 25px;
}
.sp-pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    padding-left: 0;
}

.sp-pagination-list {
    display: flex;
    list-style: none;
    gap: 5px;
    padding: 0;
    margin: 0;
}

.sp-page-item {
    display: inline-block;
}

.sp-page-link {
    display: inline-block;
    padding: 5px 11px;
    color: #007bff;
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
    font-family: 'Poppins', sans-serif;
}

.sp-page-link:hover {
    background-color: #007bff;
    color: #fff;
}

.sp-page-item.active .sp-page-link {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
    cursor: default;
}


</style>