<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ltw_ud2";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();



$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 8;
$offset = ($currentPage - 1) * $itemsPerPage;


$class = $_GET["class"] ?? null;
$subject = $_GET["subject"] ?? null;
$type = $_GET["type"] ?? null;
$search = $_GET["search"] ?? null;
$option = $_GET["Optioncost"] ?? null;
$min_class = $_GET["min_class"] ?? null;
$max_class = $_GET["max_class"] ?? null;
$min_cost = $_GET["min_cost"] ?? null;
$max_cost = $_GET["max_cost"] ?? null;

if ($option == 1) {
  $min_cost = 0;
  $max_cost = 50000;
} elseif ($option == 2) {
  $min_cost = 50000;
  $max_cost = 100000;
} elseif ($option == 3) {
  $min_cost = 100000;
  $max_cost = 200000;
}

$countQuery = "SELECT COUNT(*) AS total FROM books where 1=1 ";
$query_result_books = "SELECT books.* FROM books where 1=1  ";

if (!empty($subject)) {
    $query_result_books .= " and books.subjectId = " . (int)$subject;
    $countQuery .= " and books.subjectId = " . (int)$subject;
} else {
    $query_result_books .= " and books.isActive=1 and books.status = 1 and books.quantitySold > 0  ";  
    $countQuery .= "and  books.isActive=1 and books.status = 1 and books.quantitySold > 0 ";
}


if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query_result_books .= " AND bookName LIKE '%$search%' ";
    $countQuery .= " AND bookName LIKE '%$search%' ";
}

if (!empty($type)) {
    $query_result_books .= " AND books.type = '" . $type."' ";
    $countQuery .= " AND books.type = '" . $type. "' ";
}
if (!empty($min_cost) && !empty($max_cost)) {
    $query_result_books .= " AND books.currentPrice BETWEEN $min_cost AND $max_cost ";
    $countQuery .= " AND books.currentPrice BETWEEN $min_cost AND $max_cost ";
}
if (!empty($class)) {
    $query_result_books .= " AND books.classNumber = " . (int)$class;
    $countQuery .= " AND books.classNumber = " . (int)$class;
}


$sort = $_GET["sort"] ?? null;
if (!empty($sort)) {
    if ($sort === "asc") {
        $query_result_books .= " ORDER BY books.currentPrice ASC";
        $countQuery .= " ORDER BY books.currentPrice ASC";
    } elseif ($sort === "desc") {
        $query_result_books .= " ORDER BY books.currentPrice DESC";
        $countQuery .= " ORDER BY books.currentPrice DESC";
    }
}

$countResult = $conn->query($countQuery);
$totalRows = $countResult->fetch_assoc()['total'];

$totalPages = ceil($totalRows / $itemsPerPage);
$query_result_books .= " LIMIT $offset, $itemsPerPage";

$result = $conn->query($query_result_books);
$num_rows = $result->num_rows;
?>

<p class="hidden search-summary">
  <?php
    $tukhoa = htmlspecialchars($_GET['search'] ?? '');
    echo $tukhoa ? "$tukhoa ($totalRows kết quả)" : "$totalRows kết quả";
  ?>
</p>

<?php echo '<div class="grid grid-cols-4 gap-4" >'; ?>

    <?php
    $result_books = $conn->query($query_result_books);
    ?>

    <?php
    if($result_books->num_rows>0){
    
        while($row=$result_books->fetch_assoc()){
    ?>

    <!-- <div class="bg-white rounded-2xl shadow p-2"> -->
    <div class=" bg-gray-50 rounded-xl shadow-md overflow-hidden hover:shadow-xl transition duration-300 relative group">
        <img src="<?php echo $row['imageURL'];?>" alt="Book" class="w-full h-80 object-cover transition duration-300 group-hover:brightness-75">

        <div class="absolute inset-0 flex items-center justify-center gap-4 opacity-0 group-hover:opacity-100 transition duration-300">
        
        <a href="book?bookId=<?php echo $row["id"]?>" class="bg-white p-2 rounded-full shadow hover:bg-gray-100">
            <span class="icon text-xl">🔍</span>
        </a>

        <button onclick="themVaoGio(<?= $row['id'] ?>)" 
        type="" 
        class="bg-white p-2 rounded-full shadow hover:bg-gray-100">
            <span class="icon text-xl">🛒</span>
        </button>
        </div>

        <!-- <h3 class="text-sm font-semibold m-4"><?php //echo $row['bookName'];?></h3>
        <div class="text-red-600 font-semibold m-4"><?php //echo number_format($row['currentPrice'], 0, ',', '.'); ?> đ <span class="text-xs text-gray-500 line-through"><?php //echo number_format($row['oldPrice'], 0, ',', '.'); ?> đ</span></div>
        -->
        <div class="p-4">
        <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($row["bookName"]); ?></h3>
        <div class="flex items-center space-x-2 mt-2">
            <span class="text-lg font-bold text-red-500"><?php echo number_format($row["currentPrice"], 0, ',', '.'); ?> đ</span>
            <span class="text-sm text-gray-400 line-through"><?php echo number_format($row["oldPrice"], 0, ',', '.'); ?> đ</span>
        </div>
        <span class="text-sm text-white bg-red-400 px-2 py-0.5 rounded">
            <?php
            $old = (float)$row["oldPrice"];
            $current = (float)$row["currentPrice"];

            $discount = ($old > 0) ? number_format((1 - $current / $old) * 100, 3) : 0;

            ?>

            <span class="text-sm text-white bg-red-400 px-2 py-0.5 rounded">
                -<?= $discount ?>%
            </span>
        </span>
        <span class="ml-2 inline-block bg-pink-100 text-pink-700 text-xs font-semibold px-2 py-0.5 rounded-full shadow-sm">
            Còn lại: <?= $row["quantitySold"] ?> cuốn
        </span>
        </div>
    </div>

    <?php
        }
    }else {
        echo "<p class='text-center col-span-4'>Không tìm thấy sách nào.</p>";
    }
    ?>
<?php echo "</div>" ?>
<?php
if ($totalPages > 1) {
    echo '<div class="flex justify-center mt-8" id="pagination"><nav class="inline-flex items-center space-x-1 rounded-xl bg-white px-4 py-2 shadow-md border border-gray-200">';
    
    if ($currentPage > 1) {
        echo '<a href="#" onclick="changePage(' . ($currentPage - 1) . ')" class="px-3 py-1 rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200 transition">«</a>';
    }

    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $currentPage ? 'bg-blue-500 text-white font-semibold shadow' : 'text-gray-600 hover:bg-gray-100';
        echo '<a href="#" onclick="changePage(' . $i . ')" class="px-3 py-1 rounded-lg ' . $active . ' transition">' . $i . '</a>';
    }

    if ($currentPage < $totalPages) {
        echo '<a href="#" onclick="changePage(' . ($currentPage + 1) . ')" class="px-3 py-1 rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200 transition">»</a>';
    }

    echo '</nav></div>';
}

?>