<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../");
    exit();
}

$servername="localhost";
$username="root";
$password="";
$dbname="ltw_ud2";
$conn=new mysqli($servername,$username,$password,$dbname);
if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<?php include_once "../components/header2.php";?>

<div class="bg-white mt-4  flex flex-col items-center justify-center font-sans min-h-screen">

  <div class=" bg-gradient-to-br from-white to-white">
    <div class="w-full h-full bg-[url('https://www.toptal.com/designers/subtlepatterns/patterns/dot-grid.png')] opacity-10"></div>
  </div>

  <div class="relative z-10 text-center max-w-lg px-6">
    <div class="text-4xl mb-4">🛍</div>
    <h1 class="text-4xl font-bold text-gray-900 mb-4">Thank you!</h1>
    <p class="text-lg text-gray-600 mb-6">
      A confirmation for buying books from our store.<br>
    </p>

    <!-- Email signup form -->
    <form class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-10">
      <button type="submit"
        class="px-5 py-2 bg-gradient-to-r from-orange-300 to-teal-300 text-white font-semibold rounded-lg hover:opacity-90 transition">
        BOOKSTORE
      </button>
    </form>

    <!-- Social -->
    <div class="text-sm text-gray-700 mb-2">Let’s Be Friends!</div>
    <div class="flex justify-center space-x-4 text-gray-700 text-xl">
      <a href="#" class="hover:text-blue-500"><i class="fab fa-facebook"></i>🔵</a>
      <a href="#" class="hover:text-blue-400"><i class="fab fa-twitter"></i>🐦</a>
      <a href="#" class="hover:text-pink-500"><i class="fab fa-instagram"></i>📸</a>
      <a href="#" class="hover:text-black"><i class="fab fa-github"></i>💻</a>
    </div>
  </div>
</div>

<?php include_once "../components/footer.php";?>
</body>
</html>