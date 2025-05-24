<?php

$dsn = "mysql:host=localhost;port=3306;dbname=ltw_ud2";
$dbUsername = "root";
$dbPassword = "";
try {
    $pdo = new PDO($dsn, $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
