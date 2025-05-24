<?php
require_once("../database/database.php");
require_once("../database/book.php");


$product = new BooksTable($pdo);

$id = $img = $name = $sId = $subject = $class = $price = $description = '';
$subjects = $product->getAllSubject();




// if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit-form'])) {
// echo "<script>console.log('ok nè');</script>";

//     try {
//         if (empty($_POST['id']) || empty($_POST['name']) || empty($_POST['subject']) || empty($_POST['class']) || empty($_POST['description'])) {
//             throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
//         }

//         $dbImagePath = $_POST['image']; 
        
//         if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
//             $imageTmpName = $_FILES['imageFile']['tmp_name'];
//             $imageName = uniqid() . '_' . basename($_FILES['imageFile']['name']);
//             $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/LTW-UD2/images/Products/';
//             $imagePath = $uploadDir . $imageName;

//             if (!move_uploaded_file($imageTmpName, $imagePath)) {
//                 throw new Exception('Lỗi khi tải ảnh lên');
//             }

//             $dbImagePath = '/LTW-UD2/images/Products/' . $imageName;
            
//             if (!empty($_POST['image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $_POST['image'])) {
//                 unlink($_SERVER['DOCUMENT_ROOT'] . $_POST['image']);
//             }
//         }
// echo "<script>console.log('Bắt đầu updateBook')</script>";
//         $result = $product->updateBook(
//             $_POST['id'],
//             $_POST['name'],
//             $_POST['subject'],
//             $_POST['class'],
//             $dbImagePath,
//             $_POST['description']
//         );
// echo "<script>console.log('Kết quả trả về từ updateBook: " . json_encode($result) . "');</script>";

//         if ($result) {
//             echo '<script>alert("Cập nhật thông tin sách thành công!");</script>';
//         } else {
//             throw new Exception('Có lỗi khi cập nhật thông tin sách');
//         }
//         exit;

//     } catch (Exception $e) {
//         echo '<script>alert("'.$e->getMessage().'"); window.history.back();</script>';
//         exit;
//     }
// }


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update-product'])) {
    if (!isset($_POST['id'])) {
        die('<script>alert("Thiếu ID sản phẩm"); window.history.back();</script>');
    }
    $id = $_POST['id'];
    $products = $product->getBookById($id);
    $img = $products['imageURL'];
    $name = $products['bookName'];
    $sId = $products['subjectId'];
    $subject = $product->getSubjectNameById($sId);
    $class = $products['classNumber'];
    $price = $products['currentPrice'];
    $description = $products['description'];
    $subjects = $product->getAllSubject();
}

?>

<h3>Cập nhật thông tin sách</h3>
<form id="editProductForm" method="POST" enctype="multipart/form-data" name="submit-form">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="submit-form" value="20">

    <div class="leftForm">
        <img id="BookImg" src="<?php echo $img ?>" alt="" width="95%" height="auto">
        <label for="imageFile" class="custom-file-upload">📁 Duyệt ảnh</label>
        <input type="file" id="imageFile" name="imageFile" accept="image/*" style="display: none;">
        <input type="hidden" name="image" id="imagePath" value="<?php echo $img ?>">
    </div>

    <div class="rightForm">
        <div class="rightForm-container">
            <div class="rightForm-left">
                <label for="book-name">Tên sách:</label>
                <input type="text" id="book-name" name="name" value="<?php echo $name ?>" required>
                <label for="price">Giá:</label>
                <input type="text" id="price" name="price" value="<?php echo number_format($price, 0, ',', '.') . 'đ' ?>" readonly>
            </div>

            <div class="rightForm-right">
                <label for="subject">Môn học:</label>
                <select id="subject" name="subject" required>
                    <option value="">-- Chọn môn học --</option>
                    <?php foreach ($subjects as $sub): ?>
                        <option value="<?php echo $sub['id']; ?>" <?php if ($sub['id'] == $sId) echo 'selected'; ?>>
                            <?php echo $sub['subjectName']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="class">Lớp:</label>
                <select id="class" name="class" required>
                    <option value="">-- Chọn lớp --</option>
                    <?php 
                    for ($i = 1; $i <= 12; $i++) {
                        $selected = ($i == $class) ? 'selected' : '';
                    ?>
                        <option value="<?php echo $i; ?>" <?php echo $selected; ?>>Lớp <?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <br>
        <label for="description">Mô tả:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($description); ?></textarea>
        <br>
        <br>
    </div>

    <button type="submit">Lưu thay đổi</button>
</form>

<script>
$(document).ready(function() {
    $('#imageFile').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#BookImg').attr('src', e.target.result).show();
                // location.reload();
            };
            reader.readAsDataURL(file);
        }
    });

    $('#editProductForm').submit(function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Xác nhận cập nhật sách?',
            text: "Bạn có chắc muốn cập nhật thông tin sách?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Xác nhận',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(this);
                // formData.append('submit-form', '20');
                
                let dataPreview = '';
                for (let [key, value] of formData.entries()) {
                    dataPreview += `${key}: ${value}\n`;
                }
                console.log(dataPreview);



                $.ajax({
                    url: './handleEditProduct.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            title: 'Thành công!',
                            text: 'Đã cập nhật thông tin sách thành công',
                            icon: 'success'
                        }).then(() => {
                            $("#myModal").css("display", "none");
                            location.reload();
                            // $('.sp-concon').html(response);
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Lỗi!',
                            text: xhr.responseText || 'Có lỗi xảy ra khi cập nhật',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });
});
</script>




<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
.modal {
    font-family: 'Poppins', sans-serif;
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    width: 60%;
    min-width: 650px;
    padding: 25px 45px;
}

.modal-content input,
.modal-content select,
.modal-content textarea {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.modal-content button {
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-left: calc(50% - 68px);
}

.modal-content button:hover {
    background-color: #0056b3;
}

.swal2-container {
    z-index: 10000 !important;
}

.modal-content h3 {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 20px;
}

#editProductForm {
    display: float;
}

.leftForm {
    float: left;
    width: 29%;
    height: auto;
    margin-top: 20px;
    display: flex;
    align-items: center;
    /* justify-content: center; */
    flex-direction: column;
}

.rightForm {
    float: right;
    width: 69%;
    height: 90%;
}

.rightForm-container {
    display: flex;
}

.rightForm-left {
    width: 53%;
    height: auto;
    padding: 1%;
}

.rightForm-right {
    width: 45%;
    padding: 1%;
    height: 90%;
}
.custom-file-upload {
    width: 150px;
    display: inline-block;
    padding: 8px 16px;
    background-color: #007bff;
    color: white;
    font-weight: 500;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 10px;
}

.custom-file-upload:hover {
    background-color: #0056b3;
}

</style>
