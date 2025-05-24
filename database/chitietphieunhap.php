<?php
require_once "database.php";


class ChiTietPhieuNhap {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getByPhieuNhapId($idPhieuNhap) {
        $sql = "
            SELECT 
                ct.*, 
                b.bookName as ten_sach,
                b.imageURL as hinh_anh,
                ncc.name as ten_ncc,
                (ct.soluong * ct.gianhap) as thanh_tien
            FROM chitietphieunhap ct
            LEFT JOIN books b ON ct.idBook = b.id
            LEFT JOIN nhacungcap ncc ON ct.idCungCap = ncc.id
            WHERE ct.idPhieuNhap = :id AND ct.status = 1
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $idPhieuNhap, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPhieuNhapSummary($idPhieuNhap) {
        $sql = "
            SELECT 
                hdn.*,
                u.userName,
                u.fullName,
                u.avatar
            FROM hoadonnhap hdn
            LEFT JOIN users u ON hdn.idNguoiNhap = u.id
            WHERE hdn.id = :id AND hdn.status = 1
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $idPhieuNhap, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllDetailById($idpn){
        global $pdo;
        $query = "SELECT * FROM chitietphieunhap WHERE idPhieuNhap = $idpn  AND status = 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getBookNameById($pId) {
        global $pdo;
        $query = "SELECT bookName FROM books WHERE id = $pId";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['bookName'] ?? '';
    }

    public function getNCCNameById($nccId) {
        global $pdo;
        $query = "SELECT name FROM nhacungcap WHERE id = $nccId";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['name'] ?? '';
    }
}
