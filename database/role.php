<?php
require_once "database.php";

class RoleManager
{
    /**
     * Get all roles in the system
     * @return array Array of role records
     */
    public function getAllRoles()
    {
        global $pdo;
        $query = "SELECT * FROM roles ORDER BY role_id";  // Changed from "roles" to "role"
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a specific role by ID
     * @param int $roleId The ID of the role
     * @return array|false Role record or false if not found
     */
    public function getRoleById($roleId)
    {
        global $pdo;
        $query = "SELECT * FROM roles WHERE role_id = :roleId";  // Changed from "roles" to "role"
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Other methods...

    /**
     * Delete a role
     * @param int $roleId The ID of the role to delete
     * @return bool True if successful, false otherwise
     */
    public function deleteRole($roleId)
    {
        global $pdo;

        // Start transaction to ensure data integrity
        $pdo->beginTransaction();

        try {
            // First remove all permissions associated with this role
            $query1 = "DELETE FROM rolepermissions WHERE roleid = :roleId";  // Make sure this table name is correct
            $stmt1 = $pdo->prepare($query1);
            $stmt1->bindParam(':roleId', $roleId, PDO::PARAM_INT);
            $stmt1->execute();

            // Then delete the role
            $query2 = "DELETE FROM roles WHERE role_id = :roleId";  // Changed from "roles" to "role"
            $stmt2 = $pdo->prepare($query2);
            $stmt2->bindParam(':roleId', $roleId, PDO::PARAM_INT);
            $stmt2->execute();

            // Commit transaction
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            // Roll back transaction on error
            $pdo->rollBack();
            return false;
        }
    }


    public function createRole($roleName)
    {
        global $pdo;

        try {
            $query = "INSERT INTO roles (role_name) VALUES (:roleName)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':roleName', $roleName, PDO::PARAM_STR);

            if ($stmt->execute()) {
                // Return the last inserted ID
                return $pdo->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            // Log error if needed
            // error_log("Error creating role: " . $e->getMessage());
            return false;
        }
    }

    public function setRolePermissions($roleId, $permissions)
    {
        global $pdo;

        // Start transaction
        $pdo->beginTransaction();


        try {
            // First, remove all existing permissions for this role
            $query1 = "DELETE FROM rolepermissions WHERE roleid = :roleId";
            $stmt1 = $pdo->prepare($query1);
            $stmt1->bindParam(':roleId', $roleId, PDO::PARAM_INT);
            $stmt1->execute();

            // Prepare the insert statement once outside the loop
            $query2 = "INSERT INTO rolepermissions (roleid, chucnang_id, manage_id) VALUES (?, ?, ?)";
            $stmt2 = $pdo->prepare($query2);

            // Add the new permissions
            foreach ($permissions as $permission) {
                // Execute with values directly rather than binding parameters each time
                $stmt2->execute([$roleId, $permission[0], $permission[1]]);
            }

            // Commit transaction
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            // Roll back transaction on error
            $pdo->rollBack();
            // Add error logging to help debug
            error_log("Error in setRolePermissions: " . $e->getMessage());
            return false;
        }
    }

    public function getUserCountByRole()
    {
        global $pdo;
        $query = "  SELECT r.role_id, r.role_name, COUNT(u.id) as user_count
            FROM roles r 
            LEFT JOIN users u ON r.role_id = u.role_id
            GROUP BY r.role_id
            ORDER BY r.role_id
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Get all functions (chucnang)
     * @return array Array of function records
     */
    public function getAllFunctions()
    {
        global $pdo;
        $query = "SELECT * FROM chucnang";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all management operations
     * @return array Array of management operation records
     */
    public function getAllManageOperations()
    {
        global $pdo;
        $query = "SELECT * FROM manage ORDER BY id";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if a role has a specific permission
     * @param int $roleId The ID of the role
     * @param int $functionId The ID of the function
     * @param int $manageId The ID of the management operation
     * @return bool True if the role has the permission, false otherwise
     */
    public function hasPermission($roleId, $functionId, $manageId)
    {
        global $pdo;
        $query = "SELECT COUNT(*) as count FROM rolepermissions WHERE roleid = :roleId AND chucnang_id = :functionId AND manage_id = :manageId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
        $stmt->bindParam(':functionId', $functionId, PDO::PARAM_INT);
        $stmt->bindParam(':manageId', $manageId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Get users with a specific role
     * @param int $roleId The ID of the role
     * @param int $limit Maximum number of users to return
     * @param int $offset Offset for pagination
     * @return array Array of user records
     */
    public function getUsersByRole($roleId, $limit = 10, $offset = 0)
    {
        global $pdo;
        $query = "SELECT id, username, email, avatar, status_user FROM users WHERE role_id = :roleId ORDER BY id LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of users with a specific role
     * @param int $roleId The ID of the role
     * @return int Number of users with the role
     */
    public function getUserCountForRole($roleId)
    {
        global $pdo;
        $query = "SELECT COUNT(*) as count FROM users WHERE role_id = :roleId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }


    public function getRolePermissions($roleId)
    {
        global $pdo;

        $query = "SELECT * FROM rolepermissions WHERE roleid = :roleId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateRoleName($roleId, $roleName)
    {
        global $pdo;

        try {
            $query = "UPDATE roles SET role_name = :roleName WHERE role_id = :roleId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
            $stmt->bindParam(':roleName', $roleName, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            // Log error if needed
            error_log("Error updating role name: " . $e->getMessage());
            return false;
        }
    }
    public function isAuthorized($userId, $functionId, $manageId)
    {
        global $pdo;
        try {

            $query = "SELECT COUNT(*) as count FROM rolepermissions rp
              JOIN users u ON rp.roleId = u.role_id
              WHERE u.id = :userId AND rp.chucnang_id = :functionId AND rp.manage_id = :manageId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':functionId', $functionId, PDO::PARAM_INT);
            $stmt->bindParam(':manageId', $manageId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
