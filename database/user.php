<?php
require_once "database.php";

class UsersTable
{
    public function getUserByUsername($username)
    {
        global $pdo;
        $query = "SELECT * FROM users WHERE userName = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    
    
public function searchNonEmployeeUsers($searchTerm = '', $searchType = 'all')
{
    global $pdo;
    
    $params = [];
    
    if ($searchType === 'id' && is_numeric($searchTerm)) {
        $query = "SELECT * FROM users WHERE role_id IS NULL AND id = ?";
        $params[] = intval($searchTerm);
    } else {
        $query = "SELECT * FROM users WHERE role_id IS NULL";
        
        if (!empty($searchTerm)) {
            switch ($searchType) {
                case 'username':
                    $query .= " AND userName LIKE ?";
                    $params[] = "%$searchTerm%";
                    break;
                case 'email':
                    $query .= " AND email LIKE ?";
                    $params[] = "%$searchTerm%";
                    break;
                default: // 'all' or any other value
                    $query .= " AND (userName LIKE ? OR email LIKE ?)";
                    $params[] = "%$searchTerm%";
                    $params[] = "%$searchTerm%";
            }
        }
    }
    
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Count total non-employee users matching search criteria
 * @param string $searchTerm Search term for filtering
 * @param string $searchType Type of search: 'all', 'id', 'username', or 'email'
 * @return int Number of matching users
 */
public function countNonEmployeeUsers($searchTerm = '', $searchType = 'all')
{
    global $pdo;
    
    $params = [];
    
    if ($searchType === 'id' && is_numeric($searchTerm)) {
        $query = "SELECT COUNT(*) as total FROM users WHERE role_id IS NULL AND id = ?";
        $params[] = intval($searchTerm);
    } else {
        $query = "SELECT COUNT(*) as total FROM users WHERE role_id IS NULL";
        
        if (!empty($searchTerm)) {
            switch ($searchType) {
                case 'username':
                    $query .= " AND userName LIKE ?";
                    $params[] = "%$searchTerm%";
                    break;
                case 'email':
                    $query .= " AND email LIKE ?";
                    $params[] = "%$searchTerm%";
                    break;
                default: // 'all' or any other value
                    $query .= " AND (userName LIKE ? OR email LIKE ?)";
                    $params[] = "%$searchTerm%";
                    $params[] = "%$searchTerm%";
            }
        }
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return (int)$result['total'];
}
    
public function getEmployees($search = '', $roleId = 0, $limit = 10, $offset = 0)
{
    global $pdo;
    
    $query = "SELECT * FROM users WHERE role_id IS NOT NULL";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function countEmployees($search = '', $roleId = 0)
{
    global $pdo;
    
    $query = "SELECT COUNT(*) as total FROM users WHERE role_id IS NOT NULL";
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND (userName LIKE ? OR email LIKE ? OR fullName LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam; 
        $params[] = $searchParam;
    }
    
    if ($roleId > 0) {
        $query .= " AND role_id = ?";
        $params[] = $roleId;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return (int)$result['total'];
}

public function updateUserRole($userId, $roleId)
{
    global $pdo;
    
    try {
        $query = "UPDATE users SET role_id = :roleId WHERE id = :userId";
        $stmt = $pdo->prepare($query);
        
        // Allow null values to remove employee status (convert to customer)
        if ($roleId === null) {
            $stmt->bindValue(':roleId', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
        }
        
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        // Log error if needed
        error_log("Error updating user role: " . $e->getMessage());
        return false;
    }
}
    
    public function getUserDetailsById($userId)
    {
        global $pdo;
        $query = "SELECT * FROM users WHERE id = :userId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function updateUserDisplayNameById($userId, $displayName)
    {
        global $pdo;
        // Fixed SQL injection vulnerability - using prepared statements properly
        $query = "UPDATE users SET fullName = :displayName WHERE id = :userId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':displayName', $displayName, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function updateUserStatusById($userId, $status)
    {
        global $pdo;
        // Fixed SQL injection vulnerability - using prepared statements properly
        $query = "UPDATE users SET status_user = :status WHERE id = :userId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function getAllUser()
    {
        global $pdo;
        $query = "SELECT * FROM users";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function getTop5UsersByBooksOrdered()
    {
        global $pdo;
        $query = "
      SELECT 
        u.id AS userId,
        u.userName,
        u.fullName,
        u.email,
        COUNT(DISTINCT h.idBill) AS order_count,
        SUM(h.totalBill) AS totalSpent
      FROM 
        users u
      JOIN 
        hoadon h ON u.id = h.idUser
      WHERE 
        h.statusBill IN (3, 4) -- Only include completed orders
      GROUP BY 
        u.id, u.userName, u.fullName, u.email
      ORDER BY 
        totalSpent DESC
      LIMIT 5";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function adminLogin($username, $password)
    {
        global $pdo;
        $query = "SELECT * FROM users WHERE role_id IS NOT NULL AND userName = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && password_verify($password, $result['password'])) {
            $_SESSION["admin_id"] = $result["id"];
            return $result;
        } else {
            return false; // Return false if login fails
        }
    }
    
    public function getCustomerSalesByDateRange($fromDate, $toDate)
    {
        global $pdo;
        $fromDate = date('Y-m-d 00:00:00', strtotime($fromDate));
        $toDate = date('Y-m-d 23:59:59', strtotime($toDate));
  
        $query = "SELECT
                u.id,
                u.fullName,
                u.email,
                COUNT(DISTINCT h.idBill) as order_count,
                SUM(h.totalBill) as total_spent
            FROM 
                users u
            JOIN 
                hoadon h ON u.id = h.idUser
            WHERE 
                h.create_at BETWEEN :fromDate AND :toDate
                AND h.statusBill IN (3, 4) -- only completed orders
            GROUP BY 
                u.id, u.fullName, u.email
            ORDER BY 
                total_spent DESC";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':fromDate', $fromDate);
        $stmt->bindParam(':toDate', $toDate);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // New function to update user profile
    public function updateUserProfile($userId, $userData)
    {
        global $pdo;
        
        // Build the SQL query dynamically based on provided fields
        $updateFields = [];
        $params = [];
        
        // Process each field if it exists
        if (isset($userData['userName'])) {
            $updateFields[] = "userName = :userName";
            $params[':userName'] = $userData['userName'];
        }
        
        if (isset($userData['fullName'])) {
            $updateFields[] = "fullName = :fullName";
            $params[':fullName'] = $userData['fullName'];
        }
        
        if (isset($userData['email'])) {
            $updateFields[] = "email = :email";
            $params[':email'] = $userData['email'];
        }
        
        if (isset($userData['phoneNumber'])) {
            $updateFields[] = "phoneNumber = :phoneNumber";
            $params[':phoneNumber'] = $userData['phoneNumber'];
        }
        
        if (isset($userData['dateOfBirth'])) {
            $updateFields[] = "dateOfBirth = :dateOfBirth";
            $params[':dateOfBirth'] = $userData['dateOfBirth'];
        }
        
        if (isset($userData['avatar'])) {
            $updateFields[] = "avatar = :avatar";
            $params[':avatar'] = $userData['avatar'];
        }
        
        // If no fields to update, return false
        if (empty($updateFields)) {
            return false;
        }
        
        // Build the complete query
        $query = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = :userId";
        $params[':userId'] = $userId;
        
        // Execute the query
        $stmt = $pdo->prepare($query);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        return $stmt->execute();
    }
    
    // New function to update user password
    public function updateUserPassword($userId, $newPassword)
    {
        global $pdo;
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $query = "UPDATE users SET password = :password WHERE id = :userId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    // New function to verify current password
    public function verifyUserPassword($userId, $password)
    {
        global $pdo;
        
        $query = "SELECT password FROM users WHERE id = :userId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['password'])) {
            return password_verify($password, $result['password']);
        }
        
        return false;
    }
    
    // New function to get user orders
    public function getUserOrders($userId, $limit = 10)
    {
        global $pdo;
        
        $query = "SELECT 
                    h.idBill,
                    h.totalBill,
                    h.create_at,
                    h.statusBill,
                    COUNT(c.id) as item_count
                  FROM 
                    hoadon h
                  LEFT JOIN 
                    chitiethoadon c ON h.idBill = c.idHoadon
                  WHERE 
                    h.idUser = :userId
                  GROUP BY 
                    h.idBill
                  ORDER BY 
                    h.create_at DESC
                  LIMIT :limit";
                  
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}