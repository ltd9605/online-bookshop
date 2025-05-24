<?php
require_once "database.php";

class BooksTable
{

    public function getBookById($id)
    {
        global $pdo;
        $query = "SELECT * FROM books WHERE id = $id";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    
public function changeActive($id, $isActive)
{
    global $pdo;
    try {
        // Ensure isActive is either 0 or 1
        $isActive = ($isActive == 1) ? 1 : 0;
        
        $query = "UPDATE books SET isActive = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $success = $stmt->execute([$isActive, $id]);
        
        return $success;
    } catch (PDOException $e) {
        error_log("Database error in changeActive: " . $e->getMessage());
        return false;
    }
}
    public function searchBook($search)
    {
        global $pdo;
        $query = "SELECT * FROM books WHERE MATCH(bookName) AGAINST ($search IN BOOLEAN MODE)";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getRandomBookByAmount($amount)
    {
        global $pdo;
        $query = "SELECT * FROM books ORDER BY RAND() LIMIT $amount";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getAllBook()
    {
        global $pdo;
        $query = "SELECT * FROM books";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getSubjectNameById($sId)
    {
        global $pdo;
        $query = "SELECT subjectName FROM subjects WHERE id = $sId";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['subjectName'] ?? '';
    }

    public function getSubjectIdByName($name)
    {
        global $pdo;
        $query = "SELECT id FROM subjects WHERE subjectName = $name";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'] ?? '';
    }

    public function getBooksByCondition($cond)
    {
        global $pdo;
        $query = $cond;
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function deleteById($id)
{
    global $pdo;
    try {
        // Kiểm tra xem sách có liên quan đến bất kỳ bản ghi bán hàng nào không
        $query = "SELECT COUNT(*) as count FROM chitiethoadon WHERE idBook = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            // Nếu sách đã được bán, chỉ cập nhật status thành 0 (xóa mềm)
            $query = "UPDATE books SET status = 0 WHERE id = ?";
            $stmt = $pdo->prepare($query);
            return $stmt->execute([$id]);
        } else {
            // Nếu sách chưa được bán, xóa vĩnh viễn
            $query = "DELETE FROM books WHERE id = ?";
            $stmt = $pdo->prepare($query);
            return $stmt->execute([$id]);
        }
    } catch (PDOException $e) {
        error_log("Lỗi cơ sở dữ liệu trong deleteById: " . $e->getMessage());
        return false;
    }
}

    public function getAllSubject()
    {
        global $pdo;
        $query = "SELECT * FROM subjects";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function updateBook($id, $name, $subjectId, $class, $image, $description)
    {
        global $pdo;
        $query = "UPDATE books SET 
                bookName = ?, 
                subjectId = ?, 
                classNumber = ?, 
                imageURL = ?, 
                description = ?
                WHERE id = ?";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([$name, $subjectId, $class, $image, $description, $id]);
    }


    public function addBook($name, $subjectId, $class, $image, $desc, $oldPrice, $currentPrice, $bookType, $publish)
    {
        global $pdo;
        try {
            // Fixed the duplicate isActive column and changed bookType to type to match DB structure
            $stmt = $pdo->prepare("INSERT INTO books 
                      (bookName, subjectId, classNumber, imageURL, description, status, isActive, oldPrice, currentPrice, type) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Added status value as 1 and used $publish for isActive
            $success = $stmt->execute([
                $name,
                $subjectId,
                $class,
                $image,
                $desc,
                1,          // status always 1 for active records
                $publish,   // isActive from parameter
                $oldPrice,
                $currentPrice,
                $bookType   // goes into the 'type' column
            ]);

            // Return actual success status
            return $success && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database error in addBook: " . $e->getMessage());
            return false;
        }
    }
}
