<?php
require_once "database.php";

class PhieuNhap 
{
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM hoadonnhap WHERE id = :id AND status = 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserNameById($id)
    {
        $stmt = $this->pdo->prepare("SELECT userName FROM users WHERE id = :id AND status_user = 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['userName'] ?? '';
    }

    public function getFullNameById($id)
    {
        $stmt = $this->pdo->prepare("SELECT fullName FROM users WHERE id = :id AND status_user = 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['fullName'] ?? '';
    }

public function deleteById($id)
{
    try {
        $this->pdo->beginTransaction();

        // First delete child records from the details table to maintain referential integrity
        $stmt = $this->pdo->prepare("DELETE FROM chitietphieunhap WHERE idPhieuNhap = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Then delete the main receipt record
        $stmt = $this->pdo->prepare("DELETE FROM hoadonnhap WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $result = $stmt->execute();

        $this->pdo->commit();
        return $result;
    } catch (PDOException $e) {
        $this->pdo->rollBack();
        error_log("Error deleting receipt ID $id: " . $e->getMessage());
        return false;
    }
}

    public function getByCondition($sql)
    {
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPhieuNhap($search = '', $startDate = '', $endDate = '', $sortBy = 'date-desc')
    {
        $query = "SELECT hdn.*, u.username as ten_nguoi_nhap, u.fullName 
                 FROM hoadonnhap hdn 
                 LEFT JOIN users u ON hdn.idNguoiNhap = u.id 
                 WHERE hdn.status = 1";

        $params = [];
        
        // Apply search filter
        if (!empty($search)) {
            $query .= " AND (
                hdn.idNguoiNhap IN (SELECT id FROM users WHERE userName LIKE :search OR fullName LIKE :search) 
                OR hdn.id LIKE :search_id
            )";
            $params[':search'] = "%$search%";
            $params[':search_id'] = "%$search%";
        }
        
        // Apply date range filter
        if (!empty($startDate)) {
            $query .= " AND DATE(hdn.date) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        
        if (!empty($endDate)) {
            $query .= " AND DATE(hdn.date) <= :end_date";
            $params[':end_date'] = $endDate;
        }
        
        // Apply sorting
        switch ($sortBy) {
            case 'date-asc':
                $query .= " ORDER BY hdn.date ASC";
                break;
            case 'tongtien-desc':
                $query .= " ORDER BY hdn.tongtien DESC";
                break;
            case 'tongtien-asc':
                $query .= " ORDER BY hdn.tongtien ASC";
                break;
            case 'date-desc':
            default:
                $query .= " ORDER BY hdn.date DESC";
        }
        
        $stmt = $this->pdo->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
   
    public function getStatistics()
    {
        $stats = [
            'total_receipts' => 0,
            'total_value' => 0,
            'total_items' => 0,
            'receipts_this_month' => 0,
            'monthly_data' => []
        ];
        
        try {
            // Total number of receipts
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM hoadonnhap WHERE status = 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_receipts'] = (int)$result['total'];
            
            // Total value of all imports
            $stmt = $this->pdo->prepare("SELECT SUM(tongtien) as total_value FROM hoadonnhap WHERE status = 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_value'] = (float)($result['total_value'] ?? 0);
            
            // Total number of items imported
            $stmt = $this->pdo->prepare("
                SELECT SUM(cp.soluong) as total_items 
                FROM chitietphieunhap cp
                JOIN hoadonnhap h ON cp.idPhieuNhap = h.id
                WHERE h.status = 1 AND cp.status = 1
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_items'] = (int)($result['total_items'] ?? 0);
            
            // Number of receipts this month
            $currentMonth = date('Y-m');
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total 
                FROM hoadonnhap 
                WHERE status = 1 AND DATE_FORMAT(date, '%Y-%m') = :current_month
            ");
            $stmt->bindValue(':current_month', $currentMonth);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['receipts_this_month'] = (int)$result['total'];
            
            // Get monthly import data for the chart (last 6 months)
            $stmt = $this->pdo->prepare("
                SELECT 
                    DATE_FORMAT(date, '%Y-%m') as month,
                    SUM(tongtien) as monthly_value,
                    COUNT(*) as receipt_count
                FROM hoadonnhap
                WHERE 
                    status = 1 AND
                    date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                GROUP BY month
                ORDER BY month ASC
            ");
            $stmt->execute();
            $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the data for the chart
            foreach ($monthlyData as $data) {
                $monthName = date('M Y', strtotime($data['month'] . '-01'));
                $stats['monthly_data'][] = [
                    'month' => $monthName,
                    'value' => (float)$data['monthly_value'],
                    'count' => (int)$data['receipt_count']
                ];
            }
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting statistics: " . $e->getMessage());
            return $stats; // Return default stats on error
        }
    }
    
    /**
     * Get import receipts with pagination and enhanced filtering
     * 
     * @param int $offset Pagination offset
     * @param int $itemPerPage Items per page
     * @param string $search Search term
     * @param string $startDate Filter by start date
     * @param string $endDate Filter by end date
     * @param string $sortBy Sorting option
     * @return array Filtered and paginated import receipts
     */
    public function getPhieuNhapPagination($offset, $itemPerPage, $search = '', $startDate = '', $endDate = '', $sortBy = 'date-desc')
    {
        $query = "SELECT hdn.*, u.username as ten_nguoi_nhap, u.fullName 
                 FROM hoadonnhap hdn 
                 LEFT JOIN users u ON hdn.idNguoiNhap = u.id 
                 WHERE hdn.status = 1";
        
        $params = [];
        
        // Apply search filter
        if (!empty($search)) {
            $query .= " AND (
                hdn.idNguoiNhap IN (SELECT id FROM users WHERE userName LIKE :search OR fullName LIKE :search) 
                OR hdn.id LIKE :search_id
            )";
            $params[':search'] = "%$search%";
            $params[':search_id'] = "%$search%";
        }
        
        // Apply date range filter
        if (!empty($startDate)) {
            $query .= " AND DATE(hdn.date) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        
        if (!empty($endDate)) {
            $query .= " AND DATE(hdn.date) <= :end_date";
            $params[':end_date'] = $endDate;
        }
        
        // Apply sorting
        switch ($sortBy) {
            case 'date-asc':
                $query .= " ORDER BY hdn.date ASC";
                break;
            case 'tongtien-desc':
                $query .= " ORDER BY hdn.tongtien DESC";
                break;
            case 'tongtien-asc':
                $query .= " ORDER BY hdn.tongtien ASC";
                break;
            case 'date-desc':
            default:
                $query .= " ORDER BY hdn.date DESC";
        }
        
        $query .= " LIMIT :offset, :limit";
        
        $stmt = $this->pdo->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$itemPerPage, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Count all import receipts with enhanced filtering
     * 
     * @param string $search Search term
     * @param string $startDate Filter by start date
     * @param string $endDate Filter by end date
     * @return int Total count of matching records
     */
    public function countAllPhieuNhap($search = '', $startDate = '', $endDate = '')
    {
        $query = "SELECT COUNT(*) as total 
                 FROM hoadonnhap hdn 
                 LEFT JOIN users u ON hdn.idNguoiNhap = u.id
                 WHERE hdn.status = 1";
        
        $params = [];
        
        // Apply search filter
        if (!empty($search)) {
            $query .= " AND (
                hdn.idNguoiNhap IN (SELECT id FROM users WHERE userName LIKE :search OR fullName LIKE :search)
                OR hdn.id LIKE :search_id
            )";
            $params[':search'] = "%$search%";
            $params[':search_id'] = "%$search%";
        }
        
        // Apply date range filter
        if (!empty($startDate)) {
            $query .= " AND DATE(hdn.date) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        
        if (!empty($endDate)) {
            $query .= " AND DATE(hdn.date) <= :end_date";
            $params[':end_date'] = $endDate;
        }
        
        $stmt = $this->pdo->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }
}