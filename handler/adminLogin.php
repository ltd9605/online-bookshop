<?php
include_once("../database/database.php");
include_once("../database/user.php");
include_once("../database/book.php");

$userTable = new UsersTable();
$password = htmlspecialchars($_GET["password"]);
$username = htmlspecialchars($_GET["username"]);

$user = $userTable->getUserByUsername($username);


if ($user == null) {
    header("Location: http://localhost/LTW-UD2/admin/");
} else {
    if ($user["password"] == $password && $user["roleId"] != 1) {
        session_start();
        $_SESSION["admin"] = $user["id"];
        header("Location: http://localhost/LTW-UD2/admin/");
    } else {
        header("Location: http://localhost/LTW-UD2/admin/");
    }
}
