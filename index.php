<head>
<script data-ad-client="ca-pub-7625490600882004" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
</head>
<?php

	session_start();

	if(!isset($_SESSION['markethelper_user_id'])){
		echo "<script language=javascript>window.location.href='./loginpage.php'</script>";
	}

	else{
		echo "<script language=javascript>window.location.href='./orders_view.php'</script>";
	}

?>