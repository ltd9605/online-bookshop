<?php
session_start();
if(!isset($_GET["user_id"])){
    header("Location: /LTW-UD2/");
}
session_unset(); 
session_destroy();
?>

<script>
alert("Tài khoản đã đăng xuất");
window.location.href = "../"; 
</script>
