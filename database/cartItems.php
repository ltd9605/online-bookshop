<?php
require_once "database.php";
class CartItemsTable
{
    function getAllItemsFromUserId($userId)
    {
        global $pdo;
        $query = "SELECT * FROM cartitems WHERE userId = $userId";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    function updateCartItemAmount($itemId, $amount)
    {

        global $pdo;
        $query = "UPDATE cartitems SET amount = $amount WHERE id = $itemId";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
    }
    function addItemToCart($bookId, $amount)
    {
        global $pdo;
        $userId = $_SESSION["userId"];
        $query = "SELECT * WHERE bookId = $bookId AND userId = $userId";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result == null) {
            $insertQuery = "INSERT INTO cartitems (bookId,userId,amount) VALUES ($bookId,$userId,$amount)";
            $stmt = $pdo->prepare($insertQuery);
            $stmt->execute();
        } else {

            $updateQuery = "UPDATE cartitems SET amount = " . ($result["amount"] + $amount) . " WHERE bookId = $bookId AND userId = $userId";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->execute();
        }
    }
    function removeItemFromCart($itemId)
    {
        global $pdo;
        $query = "DELETE * FROM cartitems WHERE id = $itemId";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
    }
}
