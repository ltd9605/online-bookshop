<?php
// filepath: c:\xampp\htdocs\LTW-UD2\database\cart.php
require_once "database.php";
require_once "book.php";

class CartTable
{
    /**
     * Get cart by user ID
     * @param int $userId The user ID
     * @return array|null Cart data or null if no cart exists
     */
    public function getCartByUserId($userId)
    {
        global $pdo;
        
        try {
            $query = "SELECT * FROM cart WHERE idUser = :userId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting cart: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a new cart for a user
     * @param int $userId The user ID
     * @return int|bool The new cart ID or false on failure
     */
    public function createCart($userId)
    {
        global $pdo;
        
        try {
            $query = "INSERT INTO cart (idUser, totalPrice) VALUES (:userId, 0)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return $pdo->lastInsertId();
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error creating cart: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add or update item in cart
     * @param int $userId The user ID
     * @param int $bookId The book ID
     * @param int $quantity The quantity to add
     * @return bool Success or failure
     */
    public function addItemToCart($userId, $bookId, $quantity)
    {
        if ($quantity <= 0) return false;
        
        // Get or create user's cart
        $cart = $this->getCartByUserId($userId);
        
        if (!$cart) {
            $cartId = $this->createCart($userId);
            if (!$cartId) return false;
        } else {
            $cartId = $cart['idCart'];
        }
        
        // Check if item is already in cart
        $existingItem = $this->getCartItem($cartId, $bookId);
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['amount'] + $quantity;
            return $this->updateCartItemQuantity($cartId, $bookId, $newQuantity);
        } else {
            // Add new item
            return $this->addNewCartItem($cartId, $bookId, $quantity);
        }
    }
    
    /**
     * Get cart item by cart ID and book ID
     * @param int $cartId The cart ID
     * @param int $bookId The book ID
     * @return array|null Cart item data or null if not found
     */
    public function getCartItem($cartId, $bookId)
    {
        global $pdo;
        
        try {
            $query = "SELECT * FROM cartitems WHERE cartId = :cartId AND bookId = :bookId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':cartId', $cartId, PDO::PARAM_INT);
            $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting cart item: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Add new item to cart
     * @param int $cartId The cart ID
     * @param int $bookId The book ID
     * @param int $quantity The quantity
     * @return bool Success or failure
     */
    public function addNewCartItem($cartId, $bookId, $quantity)
    {
        global $pdo;
        
        try {
            // Check book validity and stock
            $bookTable = new BooksTable();
            $book = $bookTable->getBookById($bookId);
            
            if (!$book) {
                return false;
            }
            
            // Add item to cartitems
            $query = "INSERT INTO cartitems (cartId, bookId, amount) VALUES (:cartId, :bookId, :amount)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':cartId', $cartId, PDO::PARAM_INT);
            $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
            $stmt->bindParam(':amount', $quantity, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Update cart total
                return $this->updateCartTotal($cartId);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error adding cart item: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update quantity of cart item
     * @param int $cartId The cart ID
     * @param int $bookId The book ID
     * @param int $quantity The new quantity
     * @return bool Success or failure
     */
    public function updateCartItemQuantity($cartId, $bookId, $quantity)
    {
        global $pdo;
        
        try {
            if ($quantity <= 0) {
                return $this->removeCartItem($cartId, $bookId);
            }
            
            $query = "UPDATE cartitems SET amount = :amount WHERE cartId = :cartId AND bookId = :bookId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':amount', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':cartId', $cartId, PDO::PARAM_INT);
            $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Update cart total
                return $this->updateCartTotal($cartId);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error updating cart item: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove item from cart
     * @param int $cartId The cart ID
     * @param int $bookId The book ID
     * @return bool Success or failure
     */
    public function removeCartItem($cartId, $bookId)
    {
        global $pdo;
        
        try {
            $query = "DELETE FROM cartitems WHERE cartId = :cartId AND bookId = :bookId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':cartId', $cartId, PDO::PARAM_INT);
            $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Update cart total
                return $this->updateCartTotal($cartId);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error removing cart item: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update cart total price based on items
     * @param int $cartId The cart ID
     * @return bool Success or failure
     */
    public function updateCartTotal($cartId)
    {
        global $pdo;
        
        try {
            // Calculate new total
            $query = "SELECT SUM(b.currentPrice * ci.amount) as total 
                      FROM cartitems ci
                      JOIN books b ON ci.bookId = b.id
                      WHERE ci.cartId = :cartId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':cartId', $cartId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalPrice = $result['total'] ?? 0;
            
            // Update cart total
            $query = "UPDATE cart SET totalPrice = :totalPrice WHERE idCart = :cartId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':totalPrice', $totalPrice, PDO::PARAM_INT);
            $stmt->bindParam(':cartId', $cartId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating cart total: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cart items with book details
     * @param int $cartId The cart ID
     * @return array The cart items with book details
     */
    public function getCartItems($cartId)
    {
        global $pdo;
        
        try {
            $query = "SELECT ci.*, b.bookName, b.currentPrice, b.imageURL, b.stock
                      FROM cartitems ci
                      JOIN books b ON ci.bookId = b.id
                      WHERE ci.cartId = :cartId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':cartId', $cartId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting cart items: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get cart with all items for user
     * @param int $userId The user ID
     * @return array Cart with items or empty array if no cart
     */
    public function getUserCartWithItems($userId)
    {
        $cart = $this->getCartByUserId($userId);
        if (!$cart) return ['cart' => null, 'items' => []];
        
        $items = $this->getCartItems($cart['idCart']);
        
        return [
            'cart' => $cart,
            'items' => $items
        ];
    }
    
    /**
     * Clear all items from a cart
     * @param int $cartId The cart ID
     * @return bool Success or failure
     */
    public function clearCart($cartId)
    {
        global $pdo;
        
        try {
            $query = "DELETE FROM cartitems WHERE cartId = :cartId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':cartId', $cartId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Update cart total to zero
                $query = "UPDATE cart SET totalPrice = 0 WHERE idCart = :cartId";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':cartId', $cartId, PDO::PARAM_INT);
                
                return $stmt->execute();
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error clearing cart: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get total number of items in user's cart
     * @param int $userId The user ID
     * @return int Number of items
     */
    public function getCartItemCount($userId)
    {
        $cart = $this->getCartByUserId($userId);
        if (!$cart) return 0;
        
        global $pdo;
        
        try {
            $query = "SELECT SUM(amount) as count FROM cartitems WHERE cartId = :cartId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':cartId', $cart['idCart'], PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return intval($result['count'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error getting cart item count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update cart item quantity by item ID
     * @param int $userId User ID for security check
     * @param int $itemId The cart item ID
     * @param int $quantity The new quantity
     * @return bool Success or failure
     */
    public function updateCartItemQuantityByItemId($userId, $itemId, $quantity)
    {
        global $pdo;
        
        try {
            // Get the cart for the user
            $cart = $this->getCartByUserId($userId);
            if (!$cart) return false;
            
            // Get the cart item to verify it belongs to user's cart
            $query = "SELECT * FROM cartitems WHERE id = :itemId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
            $stmt->execute();
            
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item || $item['cartId'] != $cart['idCart']) {
                return false;
            }
            
            if ($quantity <= 0) {
                // Remove the item
                $query = "DELETE FROM cartitems WHERE id = :itemId";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
            } else {
                // Update the quantity
                $query = "UPDATE cartitems SET amount = :quantity WHERE id = :itemId";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
            }
            
            if ($stmt->execute()) {
                return $this->updateCartTotal($cart['idCart']);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error updating cart item: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove cart item by item ID
     * @param int $userId User ID for security check
     * @param int $itemId The cart item ID
     * @return bool Success or failure
     */
    public function removeCartItemById($userId, $itemId)
    {
        return $this->updateCartItemQuantityByItemId($userId, $itemId, 0);
    }
}
?>