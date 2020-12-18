<html>
	<head>
		<title>SSO Authorization</title>
		<link rel="stylesheet" type="text/css" href="./style/mainstyle.css">
	</head>
	<body>
<?php


include 'market_phplib.php';
dbset();

$authcurl= curl_init();

$header_type= "Content-Type:application/json";
$curl_body="{'grant_type':'authorization_code','code':'".$_GET['code']."'}";


curl_setopt($authcurl,CURLOPT_URL,"https://login.eveonline.com/oauth/token");
curl_setopt($authcurl,CURLOPT_SSL_VERIFYPEER, $SSLauth);
curl_setopt($authcurl,CURLOPT_HTTPHEADER,array($header_type,$header_auth));
curl_setopt($authcurl,CURLOPT_POSTFIELDS,$curl_body);
curl_setopt($authcurl,CURLOPT_POST,1);
curl_setopt($authcurl,CURLOPT_RETURNTRANSFER,true);


$redirect_uri = "https://".$serveraddr."/MarketHelper/getesi.php"; 

$curl_response=curl_exec($authcurl);
 

curl_close($authcurl);

$token_data=json_decode($curl_response,true);


/* DEBUG
echo "access_token : ".$token_data["access_token"]."<br>";
echo "token_type : ".$token_data['token_type']."<br>";
echo "expires_in : ".$token_data['expires_in']."<br>";
echo "refresh_token : ".$token_data['refresh_token']."<br>";
*/
session_start();

if(isset($token_data["access_token"])){

	echo "<br><br> =============Character Data============ <br><br>";
	$authcurl= curl_init();
	curl_setopt($authcurl, CURLOPT_SSL_VERIFYPEER, $SSLauth); 
	curl_setopt($authcurl,CURLOPT_HTTPGET,true);
	curl_setopt($authcurl,CURLOPT_HTTPHEADER,array($header_type,"Authorization: Bearer ".$token_data['access_token']));
	curl_setopt($authcurl,CURLOPT_URL,"https://login.eveonline.com/oauth/verify");
	curl_setopt($authcurl,CURLOPT_RETURNTRANSFER,true);

	$curl_response=curl_exec($authcurl);
	curl_close($authcurl);

	$character_data=json_decode($curl_response,true);

	echo "<br>character name : ".$character_data['CharacterName'];


	//이미 등록되어있는지 검사
	$qr="select active from marketaccounts where id=".$character_data['CharacterID']." and active>0;";
	$result=$dbcon->query($qr);
	//echo($qr);
	$activen=$result->fetch_array();

	//등록이 되어 있으면 바로 로그인.
	if($activen[0] == 1){

		$qr= "update marketaccounts set latest_ip='".$_SERVER['REMOTE_ADDR']."', refresh_token='".$token_data['refresh_token']."',access_token='".$token_data['access_token']."', latest_token=UTC_TIMESTAMP where id=".$character_data['CharacterID']." and active=1;";
		
		if($dbcon->query($qr)){
		
		$_SESSION['markethelper_user_name']=$character_data['CharacterName'];
		$_SESSION['markethelper_user_id']=$character_data['CharacterID'];
		$_SESSION['markethelper_refresh_token']=$token_data['refresh_token'];
		echo "<script language=javascript>location.replace('./index.php');</script>";
		}

		else{
			errorhome('로그인에 실패하였습니다. DB Error');
		}

	}
	//가입은 했으나 미수락 상태일 경우
	else if($activen[0] == 2){

		errorhome("현재 권한이 없습니다. 관리자에게 연락해주세요.");
	}

	//등록이 안되어있으면 등록
	else if($result->num_rows==0 || $activen[0] == 0 ){

		//포트레잇 파일을 받아오는 식을 작성할 것: 매번 서버에 요청했더니 오래걸리는 것 같음;;

		//DB에 등록한다.
		$qr="insert into marketaccounts (id,registered_date,name,latest_ip,access_token,latest_token,refresh_token,active,expire) value (".$character_data['CharacterID'].",UTC_TIMESTAMP,'".$character_data['CharacterName']."','".$_SERVER['REMOTE_ADDR']."','".$token_data['access_token']."',UTC_TIMESTAMP,'".$token_data['refresh_token']."',1,date_add(UTC_TIMESTAMP, interval +7 day));";

	if($dbcon->query($qr)){
		echo "<script>alert('최초 로그인.캐릭터가 등록되었습니다. ".$character_data['CharacterName']."');\n";
		$_SESSION['markethelper_user_name']=$character_data['CharacterName'];
		$_SESSION['markethelper_user_id']=$character_data['CharacterID'];
		$_SESSION['markethelper_user_ip']=$_SERVER['REMOTE_ADDR'];
		$_SESSION['markethelper_refresh_token']=$token_data['refresh_token'];

		echo "location.replace('./index.php');</script>";
	}
	else{
		echo "<script>alert('ESI 등록에 실패했습니다.');\n";
		
		session_destroy();
		echo "location.replace('./index.php');</script>\n";
		
	}

	}

	
	



}
else{

		echo "<script>alert('다시 로그인 해 주세요.');\n";
		session_destroy();
		echo "location.replace('./index.php');</script>\n";
		
}

?>
</body>
</html>