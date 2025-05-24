<?php
require_once "database.php";

class ChiTietHoadonTable
{

    public function getById($id)
    {
        global $pdo;
        $query = "SELECT * FROM chitiethoadon WHERE id = $id";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getAll()
    {
        global $pdo;
        $query = "SELECT * FROM chitiethoadon";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }



    public function getProductSalesByDateRange($fromDate, $toDate)
    {
        global $pdo;

        // Format dates to ensure consistency
        $fromDate = date('Y-m-d 00:00:00', strtotime($fromDate));
        $toDate = date('Y-m-d 23:59:59', strtotime($toDate));

        $query = "SELECT 
            b.id,
            b.bookName,
            b.currentPrice,
            b.imageURL,
            SUM(ct.amount) as quantity_sold,
            SUM(ct.amount * ct.pricePerItem) as total_revenue
        FROM 
            chitiethoadon ct
        JOIN 
            books b ON ct.idBook = b.id
        JOIN 
            hoadon h ON ct.idHoadon = h.idBill
        WHERE 
            h.create_at BETWEEN :fromDate AND :toDate
            AND h.statusBill IN (3, 4) -- Updated to likely completed order status codes
        GROUP BY 
            b.id
        ORDER BY 
            total_revenue DESC";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':fromDate', $fromDate);
        $stmt->bindParam(':toDate', $toDate);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        return $results;
    }
    public function getAllProductSales()
    {
        global $pdo;


        $query = "SELECT 
            b.id,
            b.bookName,
            b.currentPrice,
            b.imageURL,
            SUM(ct.amount) as quantity_sold,
            SUM(ct.amount * ct.pricePerItem) as total_revenue
        FROM 
            chitiethoadon ct
        JOIN 
            books b ON ct.idBook = b.id
        JOIN 
            hoadon h ON ct.idHoadon = h.idBill
        WHERE 
 h.statusBill = 3 
         GROUP BY 
            b.id
        ORDER BY 
            total_revenue DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        return $results;
    }
}
