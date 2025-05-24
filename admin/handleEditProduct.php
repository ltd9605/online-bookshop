<?php
require_once("../database/database.php");
require_once("../database/book.php");


$product = new BooksTable($pdo);

$id = $img = $name = $sId = $subject = $class = $price = $description = '';
$subjects = $product->getAllSubject();




// if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
echo "<script>console.log('ok nè');</script>";

    try {
        if (empty($_POST['id']) || empty($_POST['name']) || empty($_POST['subject']) || empty($_POST['class']) || empty($_POST['description'])) {
            throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
        }

        $dbImagePath = $_POST['image']; 
        
        if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
            $imageTmpName = $_FILES['imageFile']['tmp_name'];
            $imageName = uniqid() . '_' . basename($_FILES['imageFile']['name']);
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/LTW-UD2/images/Products/';
            $imagePath = $uploadDir . $imageName;

            if (!move_uploaded_file($imageTmpName, $imagePath)) {
                throw new Exception('Lỗi khi tải ảnh lên');
            }

            $dbImagePath = '/LTW-UD2/images/Products/' . $imageName;
            
            if (!empty($_POST['image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $_POST['image'])) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $_POST['image']);
            }
        }
echo "<script>console.log('Bắt đầu updateBook')</script>";
        $result = $product->updateBook(
            $_POST['id'],
            $_POST['name'],
            $_POST['subject'],
            $_POST['class'],
            $dbImagePath,
            $_POST['description']
        );
echo "<script>console.log('Kết quả trả về từ updateBook: " . json_encode($result) . "');</script>";

        if ($result) {
            echo '<script>alert("Cập nhật thông tin sách thành công!");</script>';
        } else {
            throw new Exception('Có lỗi khi cập nhật thông tin sách');
        }
        exit;

    } catch (Exception $e) {
        echo '<script>alert("'.$e->getMessage().'"); window.history.back();</script>';
        exit;
    }
// }
?>