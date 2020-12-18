<html>
	<head>
	<script data-ad-client="ca-pub-7625490600882004" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<meta name=viewport content="width=700, initial-scale=0.5">
		<title>SSO Login</title>
		<link rel="stylesheet" type="text/css" href="./style/mainstyle.css">
	</head>
	<body>

<?php
include 'market_phplib.php';
dbset();

if($dbcon->connect_error){
	die("Connection Failed<br>".$dbcon->connect_error);
}

//else echo "Connected MariaDB Successfully.<br><br>";

echo ("<div class=login>");

$esiurl="https://login.eveonline.com/oauth/authorize?response_type=code&redirect_uri=https://".$serveraddr."/MarketHelper/getesi.php&client_id=".$client_id."&scope=esi-wallet.read_character_wallet.v1 esi-wallet.read_corporation_wallet.v1 esi-markets.structure_markets.v1 esi-markets.read_character_orders.v1 esi-wallet.read_corporation_wallets.v1 esi-markets.read_corporation_orders.v1";

echo "<a href='".$esiurl."'><img src=./images/loginbutton.jpg></a><br>\n";

//echo ("현재는 가입을 받고 있지 않습니다.");

echo ("</div>");

?>

<br><br> Market Helper : 지타와 아마르 마켓에서 내가 올린 오더보다 더 싼 오더/ 더 비싼 오더를 자동으로 표시해 주는 웹페이지입니다.
</body>
</html>

