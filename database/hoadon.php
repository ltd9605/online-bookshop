<?php
require_once "database.php";

class HoadonTable
{

    public function getHoaDonById($id)
    {
        global $pdo;
        $query = "SELECT * FROM hoadon WHERE id = $id";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getAllHoaDon()
    {
        global $pdo;
        $query = "SELECT * FROM hoadon";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

public function getlast6Monthstotal()
{
    global $pdo;
    $query = "SELECT 
        DATE_FORMAT(`create_at`, '%Y-%m') AS month,
        SUM(`totalBill`) AS total_bill
    FROM `hoadon`
    WHERE `create_at` >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}
public function getCompletedOrdersCount()
{
    global $pdo;
    $query = "SELECT COUNT(*) as completed_count 
              FROM hoadon 
              WHERE statusBill IN (3, 4)";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['completed_count'];
}

}
