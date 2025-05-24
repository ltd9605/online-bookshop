<?php
require_once "database.php";

class SupplierTable
{
    public function getSuppliers($search = '', $limit = 0, $offset = 0)
    {
        global $pdo;

        try {
            $query = "SELECT * FROM nhacungcap";
            $params = [];

            if (!empty($search)) {
                $query .= " WHERE id = $search";
            }

            $query .= " ORDER BY id DESC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getSuppliers: " . $e->getMessage());
            return [];
        }
    }

    public function getSupplierById($id)
    {
        global $pdo;

        try {
            $query = "SELECT * FROM nhacungcap WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getSupplierById: " . $e->getMessage());
            return null;
        }
    }

    public function countSuppliers($search = '')
    {
        global $pdo;

        try {
            $query = "SELECT COUNT(*) as total FROM nhacungcap WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $query .= " AND name LIKE ?";
                $params[] = "%$search%";
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Database error in countSuppliers: " . $e->getMessage());
            return 0;
        }
    }

    public function addSupplier($name)
    {
        global $pdo;

        try {
            $query = "INSERT INTO nhacungcap (name) VALUES (?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$name]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database error in addSupplier: " . $e->getMessage());
            return false;
        }
    }

    public function updateSupplier($id, $name)
    {
        global $pdo;

        try {
            $query = "UPDATE nhacungcap SET name = ? WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$name, $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database error in updateSupplier: " . $e->getMessage());
            return false;
        }
    }

    public function deleteSupplier($id)
    {
        global $pdo;

        try {
            $query = "DELETE FROM nhacungcap WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database error in deleteSupplier: " . $e->getMessage());
            return false;
        }
    }

    public function countBooksWithSupplier($supplierId)
    {
        global $pdo;

        try {
            $query = "SELECT COUNT(*) as count FROM books WHERE supplier_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$supplierId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Database error in countBooksWithSupplier: " . $e->getMessage());
            return 0;
        }
    }

    public function getAllSupplierOptions()
    {
        global $pdo;

        try {
            $query = "SELECT id, name FROM nhacungcap ORDER BY name ASC";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getAllSupplierOptions: " . $e->getMessage());
            return [];
        }
    }
}
