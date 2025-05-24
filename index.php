
<?php 
session_start();
require_once("./database/database.php");

$servername="localhost";
$username="root";
$password="";
$dbname="ltw_ud2";
$conn=new mysqli($servername,$username,$password,$dbname,3306);
if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Store</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="stylesheet" href="./css/login.css">

    <link rel="stylesheet" href="./css/suggested_book.css">

    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>

<body>
<?php include_once "./components/header2.php";?>
<div class="min-h-screen flex items-center justify-center">
  <div class="w-[80%] flex flex-col items-center">
    <div class="flex flex-col">
      <?php include_once "./zui/suggested_book.php" ?>
    </div>
  </div>
</div>
<?php include_once "./components/footer.php";?>



</body>
<script type="module" src="index.js"></script>
</html>