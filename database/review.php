<?php
require_once "database.php";

class ReviewTable
{
    public function getAllreview()
    {
        global $pdo;
        $query = "SELECT * FROM review ORDER BY create_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReviewById($id)
    {
        global $pdo;
        $query = "SELECT * FROM review WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addReview($bookId, $userId, $rating, $comment)
    {
        global $pdo;
        // Fix: Changed user_id to userId to match database column name
        $query = "INSERT INTO review (bookId, userId, rating, review, create_at) 
                  VALUES (:bookId, :userId, :rating, :review, NOW())";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT); // Fixed parameter name
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':review', $comment, PDO::PARAM_STR); // Fixed column name to match DB
        return $stmt->execute();
    }

    public function getreviewByBookId($bookId)
    {
        global $pdo;
        $query = "SELECT r.*, u.fullName as userName, u.email
                  FROM review r
                  JOIN users u ON r.userId = u.id
                  WHERE r.bookId = :bookId
                  ORDER BY r.create_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateReview($reviewId, $rating, $comment)
    {
        global $pdo;
        $query = "UPDATE review 
                  SET rating = :rating, review = :review 
                  WHERE id = :reviewId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':review', $comment, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function deleteReview($reviewId)
    {
        global $pdo;
        $query = "DELETE FROM review WHERE id = :reviewId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getReviewsByUserId($userId)
    {
        global $pdo;
        $query = "SELECT r.*, b.bookName, b.imageURL
                  FROM review r
                  JOIN books b ON r.bookId = b.id
                  WHERE r.userId = :userId
                  ORDER BY r.create_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countreviewByBookId($bookId)
    {
        global $pdo;
        $query = "SELECT COUNT(*) as count FROM review WHERE bookId = :bookId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function getAverageRatingByBookId($bookId)
    {
        global $pdo;
        $query = "SELECT AVG(rating) as avg_rating FROM review WHERE bookId = :bookId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['avg_rating'] ? round($result['avg_rating'], 1) : 0;
    }

    public function getRatingDistributionByBookId($bookId)
    {
        global $pdo;
        $query = "SELECT rating, COUNT(*) as count 
                  FROM review 
                  WHERE bookId = :bookId 
                  GROUP BY rating 
                  ORDER BY rating DESC";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkUserReview($userId, $bookId)
    {
        global $pdo;
        $query = "SELECT * FROM review WHERE userId = :userId AND bookId = :bookId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFilteredReviews($search = '', $ratingFilter = 0, $page = 1, $perPage = 10)
    {
        global $pdo;

        try {
            $query = "SELECT r.* 
            FROM review r
            JOIN books b ON r.bookId = b.id
            JOIN users u ON r.userId = u.id 
            WHERE 1=1";
            $params = [];

            if ($ratingFilter > 0) {
                $query .= " AND r.rating = $ratingFilter";
            }


            if (!empty($search)) {
                $query .= " AND (b.id = $search OR u.id =  $search OR r.id LIKE $search)";
            }

            $query .= " ORDER BY r.create_at DESC";

            $offset = ($page - 1) * $perPage;
            $query .= " LIMIT $perPage OFFSET $offset";

            $stmt = $pdo->prepare($query);

     


            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting filtered reviews: " . $e->getMessage());
            return [];
        }
    }

    public function countFilteredReviews($search = '', $ratingFilter = 0)
    {
        global $pdo;

        try {
            $query = "SELECT COUNT(*) as total 
        FROM review r
        JOIN books b ON r.bookId = b.id
        JOIN users u ON r.userId = u.id  /* FIXED: changed u.user_id to u.id */
        WHERE 1=1";
            $params = [];

            // Add rating filter
            if ($ratingFilter > 0) {
                $query .= " AND r.rating = :rating";
                $params[':rating'] = $ratingFilter;
            }

            // Add search filter
            if (!empty($search)) {
                $search = '%' . $search . '%';
                $query .= " AND (b.bookName LIKE :search OR u.username LIKE :search OR r.review LIKE :search)";
                $params[':search'] = $search;
            }

            $stmt = $pdo->prepare($query);

            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return intval($result['total']);
        } catch (PDOException $e) {
            error_log("Error counting filtered reviews: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get review statistics for admin dashboard
     */
    public function getReviewStatistics()
    {
        global $pdo;

        try {
            $stats = [
                'total' => 0,
                'avgRating' => 0,
                'highRatings' => 0,
                'lowRatings' => 0
            ];

            // Get total count
            $query = "SELECT COUNT(*) as count FROM review";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total'] = intval($result['count']);

            // Get average rating
            $query = "SELECT AVG(rating) as avg FROM review";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['avgRating'] = round(floatval($result['avg'] ?? 0), 1);

            // Get high ratings (4-5 stars)
            $query = "SELECT COUNT(*) as count FROM review WHERE rating >= 4";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['highRatings'] = intval($result['count']);

            // Get low ratings (1-2 stars)
            $query = "SELECT COUNT(*) as count FROM review WHERE rating <= 2";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['lowRatings'] = intval($result['count']);

            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting review statistics: " . $e->getMessage());
            return [
                'total' => 0,
                'avgRating' => 0,
                'highRatings' => 0,
                'lowRatings' => 0
            ];
        }
    }
    public function getLatestReviews($limit = 5)
    {
        global $pdo;
        $query = "SELECT r.*, u.fullName as userName, b.bookName, b.imageURL
                  FROM review r
                  JOIN users u ON r.userId = u.id
                  JOIN books b ON r.bookId = b.id
                  ORDER BY r.create_at DESC
                  LIMIT :limit";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
