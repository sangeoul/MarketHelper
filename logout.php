<?php
session_start();
unset($_SESSION['markethelper_user_name']);
unset($_SESSION['markethelper_user_id']);
unset($_SESSION['markethelper_user_ip']);
unset($_SESSION['markethelper_refresh_token']);
?>
<script>window.location.href="./loginpage.php";</script>