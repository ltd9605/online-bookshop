<?php

function uploadsAvatar($avatar, $id)
{
    $target_dir = __DIR__ . "\\avatar\\";

    // get extension of file
    $new_name = "avatar-" . $id . "." . pathinfo($avatar["name"], PATHINFO_EXTENSION);

    $target_file = $target_dir . $new_name;
    move_uploaded_file($avatar["tmp_name"], $target_file);

    return "./avatar/" . $new_name;
}

function uploadsImageProduct($image, $idProduct, $db)
{
    $target_dir = __DIR__ . "/image/products/";
    $sql = "SELECT * FROM imageproducts WHERE idProduct = $idProduct";
    $count = $db->selectAll($sql)->num_rows + 1;
    $imageLink = $idProduct . "-" . $count . "." . pathinfo($image["name"], PATHINFO_EXTENSION);
    $target_file = $target_dir . $imageLink;
    move_uploaded_file($image["tmp_name"], $target_file);

    $link = "./image/products/" . $imageLink;

    return $link;
}


//update logo collection
function uploadsLogoCollection($image, $idBrand)
{
    $target_dir = __DIR__ . "/admin/logoCollection/";


    $date = date_create();

    $imageLink = "image" . strval(date_timestamp_get($date)) . "." . pathinfo($image["name"], PATHINFO_EXTENSION);
    $target_file = $target_dir . $imageLink;
    echo $target_file . "<br>";
    move_uploaded_file($image["tmp_name"], $target_file);

    $link = "./admin/logoCollection/" . $imageLink;
    // echo ($image["tmp_name"] . "--");

    return $link;
}

function changeImageProduct($image, $idImage, $db)
{
    $target_dir = __DIR__ . "/image/products/";
    $sql = "SELECT * FROM imageproducts WHERE idImage = $idImage";
    $imageName = $db->selectAll($sql)->fetch_assoc()['image'];
    $imageName = substr($imageName, 17, strlen($imageName));
    $imageName = explode(".", $imageName)[0] . "." . pathinfo($image["name"], PATHINFO_EXTENSION);
    $link = $target_dir . $imageName;
    move_uploaded_file($image["tmp_name"], $link);

    $link = "./image/products/" . $imageName;
    return $link;
}

function convertPrice($price)
{
    // 0: là số chữ số thập phân
    // ,: kí tự ngăn cách phần thập phân
    // .: kí tự ngăn cách phần nghìn
    return number_format($price, 0, ',', '.') . "đ";
}

function convertDate($date)
{
    return date("d/m/Y", strtotime($date));
}

function convertTime($time)
{
    return date("H:i:s", strtotime($time));
}
