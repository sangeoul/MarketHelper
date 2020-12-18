<?php

include $_SERVER['DOCUMENT_ROOT']."/PublicESI/phplib.php";
//esi setting


$header_type= "Content-Type:application/json";



//For server 
$client_id=$client_id["MarketHelper"];
$header_auth=$header_auth["MarketHelper"];



$ID_MARKETSTATION_JITA=60003760;
$ID_SYSTEM_JITA=30000142;
$ID_REGION_THE_FORGE=10000002;

$ID_MARKETSTATION_PERIMETER=1028858195912;
$ID_SYSTEM_PERIMETER=30000144;

$ID_MARKETSTATION_AMARR=60008494;
$ID_SYSTEM_AMARR=30002187;
$ID_REGION_DOMAIN=10000043;



$USER_ACCESS_TOKEN="";

function logincheck_markethelper($dontecho=0,$dontloging=0){
	global $dbcon;
	global $USER_ACCESS_TOKEN;

	global $client_id;
	global $header_auth;
	global $serveraddr;
	global $SSLauth;


	session_start();

	if(!isset($_SESSION['markethelper_user_id'])){
		errorlogout('로그인 되어 있지 않습니다.');
	
	}

	$dbcon->query("update marketaccounts set active=2 where expire<UTC_TIMESTAMP and active=1;");
	
	$qr="select * from marketaccounts where id=".$_SESSION['markethelper_user_id']." and active=1";

	$result=$dbcon->query($qr);
	$userdata=$result->fetch_array();

	if($result->num_rows==0){
	errorlogout('로그인 되어 있지 않습니다.');
	
	}
	else if($userdata['latest_ip']!=$_SERVER['REMOTE_ADDR']){
		echo "<script>alert('세션이 변경되어 로그아웃됩니다. 다시 로그인 해 주세요.');location.href='./logout.php'</script>";
	}
	else{

		$qr="update marketaccounts set latest_login=UTC_TIMESTAMP where active=1 and id=".$_SESSION['markethelper_user_id'].";";
		if(!($dbcon->query($qr))){
			echo ("<script>alert('문제가 발생하여 로그아웃됩니다. Login Check Error');location.href='./logout.php'</script>");

		}

	}


	$tokentime=(strtotime(gmdate("Y-m-d H:i:s"))-strtotime($userdata['latest_token']));
	
	if($tokentime>1000) { 
		
		$authcurl= curl_init();
		$header_type= "Content-Type:application/json";
		$curl_body="{'grant_type':'refresh_token','refresh_token':'".$_SESSION['markethelper_refresh_token']."'}";

		curl_setopt($authcurl,CURLOPT_URL,"https://login.eveonline.com/oauth/token");
		//curl_setopt($authcurl,CURLOPT_URL,"https://esi.evetech.net/latest/characters/92497990/?datasource=tranquility");
		curl_setopt($authcurl, CURLOPT_SSL_VERIFYPEER, $SSLauth); 
		curl_setopt($authcurl,CURLOPT_HTTPHEADER,array($header_type,$header_auth));
		curl_setopt($authcurl,CURLOPT_POSTFIELDS,$curl_body);
		curl_setopt($authcurl,CURLOPT_POST,1);
		curl_setopt($authcurl,CURLOPT_RETURNTRANSFER,true);


		$curl_response=curl_exec($authcurl);
		curl_close($authcurl);

		$obj_response=json_decode($curl_response,true);
		$USER_ACCESS_TOKEN=$obj_response["access_token"];
		$dbcon->query("update marketaccounts set access_token='".$USER_ACCESS_TOKEN."', refresh_token='".$obj_response["refresh_token"]."', latest_token=UTC_TIMESTAMP where active=1 and id=".$_SESSION['markethelper_user_id'].";");
		$_SESSION['markethelper_refresh_token']=$obj_response["refresh_token"];
	}
	else{
		$USER_ACCESS_TOKEN=$userdata['access_token'];
	}


	if(!$dontloging){
		logingip();
	}

}


function admincheck(){
	global $dbcon;
	global $ADMIN_ID;
	session_start();

	if(!isset($_SESSION['markethelper_user_id'])){
		errorhome('로그인 되어 있지 않습니다.');
		return false;
	}
	else if($_SESSION['markethelper_user_ip']!=$_SERVER['REMOTE_ADDR']){

		echo "<script>alert('IP가 일치하지 않습니다. 다시 로그인 해 주세요.');location.href='./logout.php'</script>";
		return false;
	}
	else if($_SESSION['markethelper_user_id']!=$ADMIN_ID){
		errorhome("어드민구역입니다.");
		return false;
	}
	return true;

}


function menutable(){
	
	echo ("<meta name=viewport content=\"width=700, initial-scale=0.5\">");

	echo("<script>function moveto_userpage(num){ window.location.href='./characterpage.php?id='+num; }</script>");
	global $dbcon;
	$qr="select * from marketaccounts where id=".$_SESSION['markethelper_user_id']." and active=1;";
	$result=$dbcon->query($qr);
	$userdata=$result->fetch_array();


	//남은기간을 표시해준다.
	$omegatime=(strtotime($userdata['expire'])-strtotime(gmdate("Y-m-d H:i:s")));

	if(($omegatime/3600) >72){
		$omegastring=floor($omegatime/86400)." Day";
	}
	else if(($omegatime/3600)>24){
		$omegastring=floor($omegatime/3600)." Hours";
	}
	else if(($omegatime/3600)>1){
		$omegastring=floor($omegatime/3600).":".floor(($omegatime%3600)/60);
	}
	else if($omegatime<3600){
		$omegastring=floor($omegatime/60).":".floor($omegatime%60);
	}
	else{
		$omegastring="0";
	}

	global $ADMIN_ID;
	echo ("<table><tr>\n");
	echo ("<td class='topmenu_portrait' rowspan=2><img height=64 src='".getUserPortrait($_SESSION['markethelper_user_id'],64)."'></td>");
	echo ("<td class='topmenu_username'>".getUserName($_SESSION['markethelper_user_id'],64)."</td>");

	echo ("<td class='topmenu_portrait'><a href=./logout.php>로그아웃</a></td>");
	echo ("<td class='topmenu_omegatime'>남은 이용기간 : ".$omegastring."</td>");
	echo("</tr><tr>");
	echo ("<td class='topmenu_portrait'></td>");
	echo ("<td class='topmenu_portrait'></td>");
	echo ("<td class='topmenu_portrait'></td>");

	echo("</tr></table><hr>");
}


function getUserName($userid){
	
	global $dbcon;
	//유저ID로부터 유저명 받아오기
	$qr="select name from marketaccounts where id=".$userid." and active=1;";


	$result=$dbcon->query($qr);

	$namearray=$result->fetch_array();

	if(($result->num_rows)>0){
		return $namearray[0];
	}
	else if($userid==1){
		return "Admin";
	}
	else{
		return "Anonymous";
	}



//curl로 서버에서 직접 받아오는 방식은 너무 느려서 폐기
/*

$datacurl= curl_init();

$header_type= "Content-Type:application/json";


curl_setopt($datacurl,CURLOPT_URL,"https://esi.evetech.net/latest/characters/".$userid."/?datasource=tranquility");
curl_setopt($datacurl, CURLOPT_SSL_VERIFYPEER, $SSLauth); 
curl_setopt($datacurl,CURLOPT_HTTPHEADER,$header_type);
curl_setopt($datacurl,CURLOPT_POST,0);
curl_setopt($datacurl,CURLOPT_RETURNTRANSFER,true);

$curl_response=curl_exec($datacurl);
curl_close($datacurl);

$jsondata=json_decode($curl_response,true);

return $jsondata['name'];
*/


}




function getUserPortrait($userid,$size){
	//유저ID로부터 유저 포트레잇 받아오기
	global $dbcon;
	
	//0일 경우 X칸을 돌려준다.
	if($userid==0){
		return "./images/x64.png";
	}

	if($size<=64){
		return "https://imageserver.eveonline.com/Character/".$userid."_64.jpg";
	}
	else if($size<=128){
		return "https://imageserver.eveonline.com/Character/".$userid."_128.jpg";
	}
	else if($size<=256){
		return "https://imageserver.eveonline.com/Character/".$userid."_256.jpg";
	}
	else{
		return "https://imageserver.eveonline.com/Character/".$userid."_512.jpg";
	}

}



function readmarketdata(){

	global $dbcon;
	global $SSLauth;

	$qr="select id,datediff(UTC_TIMESTAMP,latest_loaded) from marketaccounts where name='__markettimer' and balance=1;";

	$result=$dbcon->query($qr);

	$markettimer=$result->fetch_array();


	//id가 0인데 최종갱신일과 1일 이상 차이나면 최종갱신일을 갱신하고 새로 시작한다.
	if($markettimer['id']==0 && $markettimer[1]>0){
	
		$dbcon->query("update marketaccounts set id=1, latest_loaded=UTC_TIMESTAMP where name='__markettimer' and balance=1 and active=0;");

	}
	//id가 0이 아니거나, 최종갱신일이 1일 이상 차이날 때만 작업을 한다.
	else if($markettimer['id']!=0 || $markettimer[1]>0) {

	$qr="select * from items where active=1 and loadesi=1 and id>".$markettimer[0]." order by id asc limit 3;";

	$items=$dbcon->query($qr);

	//줄이없으면 모두 갱신이 끝난것. id를 0으로 만들어서 끝을낸다.
	if($items->num_rows ==0){

		$dbcon->query("update marketaccounts set id=0 where name='__markettimer' and balance=1 and active=0;");

	}
	//줄이 있다면 하나만 갱신한다.
	else{

		$itemdata=$items->fetch_array();
		$itemcurl= curl_init();
		curl_setopt($itemcurl, CURLOPT_SSL_VERIFYPEER, $SSLauth); 
		curl_setopt($itemcurl,CURLOPT_HTTPGET,true);
		curl_setopt($itemcurl,CURLOPT_URL,"https://esi.evetech.net/latest/markets/10000002/history/?datasource=tranquility&type_id=".$itemdata['id']);
		curl_setopt($itemcurl,CURLOPT_RETURNTRANSFER,true);

		$curl_response=curl_exec($itemcurl);
		curl_close($itemcurl);

		$arr=json_decode($curl_response,true);
	
		//가격 업데이트
		$qr1="update items set price=".$arr[(sizeof($arr)-1)]["average"]." where id=".$itemdata['id'].";";
		$dbcon->query($qr1);

		//markettimer id 업데이트
		$qr2="update marketaccounts set id=".$itemdata['id']." where name='__markettimer' and balance=1 and active=0;";
		$dbcon->query($qr2);

	}	

	}
}

?>