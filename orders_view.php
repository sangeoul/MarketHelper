<html>
	<head>
<script type="text/javascript" src="./style/jquery-3.3.1.min.js"></script>
<!-- 구글애드센스-->

<script data-ad-client="ca-pub-7625490600882004" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
</head>
<body>
<embed type="audio/mpeg" id="autoalarm" width=1 height=1 ></embed><br>
<div id=listarea></div>

<?php
session_start();

include "./market_phplib.php";


dbset();
echo $addsense;

logincheck_markethelper();
menutable();

?>





<?php

//GET 정보를 세팅한다(지역설정)

echo("<script>\n");

if(!isset($_GET['region'])) {	
		echo("var id_region=".$ID_REGION_THE_FORGE.";\n");
}
else{
		echo("var id_region=".$_GET['region'].";\n");
}

if(!isset($_GET['system'])) {	
		echo("var id_system=".$ID_SYSTEM_JITA.";\n");
}
else{
		echo("var id_system=".$_GET['system'].";\n");
}

if(!isset($_GET['station'])) {	
		echo("var id_marketstation=".$ID_MARKETSTATION_JITA.";\n");
}
else{
		echo("var id_marketstation=".$_GET['station'].";\n");
}

if(!isset($_GET['type'])) {	
		echo("var ordertype='all';\n");
}
else{
		echo("var ordertype='".$_GET['type']."';\n");
}

echo("</script>");


echo("ESI 자체 딜레이가 있으므로, 실제 마켓 정보보다 최대 5분 늦게 반영될 수 있습니다.<br>\n새로고침 마구 누른다고 더 빨리 갱신되지 않습니다. 트래픽을 아껴주세요 ㅠㅠ<br>가끔 ESI서버가 불안정할 경우 페이지가 멈추는 경우가 있습니다. ESI가 정상화 된 뒤에 새로고침 해 주세요.<br><br>\n\n");

echo("현재 지역 : <span id='location'></span><br>");

echo("<a href='./orders_view.php?region=10000002&system=30000142&station=60003760&type=".$_GET['type']."'>Jita</a>\n");
echo("<a href='./orders_view.php?region=10000043&system=30002187&station=60008494&type=".$_GET['type']."'>Amarr</a>\n");
echo("<a href='./orders_view.php?region=10000002&system=30000144&station=1028858195912&type=".$_GET['type']."'>Perimeter</a>\n");

echo("<br><br>\n");


?>


ESI 로딩(<span id='progress'></span> orders) 자동 새로고침(3분)<input type='checkbox' name='autorefresh' id='autorefresh' checked> <span id=refreshtimer></span><br>
<a href='javascript:playalarm()'>Test Alarm</a><br>

<?php

$REFRESH_INTERVAL = 180; // 새로고침 타이머(초) 기본값은 300초 (5분)


if(isset($USER_ACCESS_TOKEN)) {


	$header_type= "Content-Type:application/json";
	$authcurl= curl_init();
	curl_setopt($authcurl, CURLOPT_SSL_VERIFYPEER, $SSLauth); 
	curl_setopt($authcurl,CURLOPT_HTTPGET,true);
	curl_setopt($authcurl,CURLOPT_HTTPHEADER,array($header_type,"Authorization: Bearer ".$USER_ACCESS_TOKEN));
	curl_setopt($authcurl,CURLOPT_URL,"https://esi.evetech.net/latest/characters/".$_SESSION['markethelper_user_id']."/orders/?datasource=tranquility");
	curl_setopt($authcurl,CURLOPT_RETURNTRANSFER,true);

	$curl_response=curl_exec($authcurl);
	curl_close($authcurl);

	$orderdata=json_decode($curl_response,true);
	$dbcon->query("update marketaccounts set orders=".sizeof($orderdata)." where id=".$_SESSION['markethelper_user_id']." and active=1;");
	echo("<script>\n");

	echo("var GOODSLIST=JSON.parse('".$curl_response."');\n");

	echo("</script>\n");
}
else{

errorlogout("ESI 에러 발생");

}

?>
<script>



//알람기능에 대한 스크립트.........=================================================================
var ALARM_DEFAULT='0';

function setAlarmCheckbox(order_id){

	if(document.getElementById('alarm'+order_id).checked==true){
		localStorage.setItem(String(order_id),'1');

	}
	else{
		localStorage.setItem(String(order_id),'0');

	}

}



var alarm_number=0;

function fillCheckbox(){

	alarm_number=0;
	for(var i=0;i<GOODSLIST.length;i++){
		
		if(document.getElementById('alarm'+GOODSLIST[i]['order_id']) && GOODSLIST[i]['alarm']=='1'){
			
			document.getElementById('alarm'+GOODSLIST[i]['order_id']).checked=true;
			alarm_number++;
		}

	}
	if(alarm_number>0){
		playalarm();
	}

}

function playalarm(){

document.getElementById('autoalarm').src='./ding.mp3';

document.getElementById('autoalarm').play();
}


</script>


<script>





document.getElementById('progress').innerHTML= "0 / "+GOODSLIST.length;


function loadESI(ESIquery)
{
	
	var ESIdata=new XMLHttpRequest();

	ESIdata.onreadystatechange=function(){

		if (this.readyState == XMLHttpRequest.DONE){
			
			returnvalue=JSON.parse(this.responseText);
			
			
		}

			
	}

	ESIdata.open("GET",ESIquery,false);
	ESIdata.setRequestHeader("Content-Type", "application/json");
	ESIdata.send();
	
	return returnvalue;
}

/*
function sortGOODS(colum,asc){
	
	if(colum=='item_name'){
	}
	

	else if(asc){
		for(var i=0;i<GOODSLIST.length;i++){
			for(var j=i+1;j<GOODSLIST.length;j++){
				if(!GOODSLIST[i][colum] || (GOODSLIST[i][colum]>GOODSLIST[j][colum] && GOODSLIST[j][colum])){
					var tempr=GOODSLIST[i];
					GOODSLIST[i]=GOODSLIST[j];
					GOODSLIST[j]=tempr;
				}
			}
		}
	}
	else{
		for(var i=0;i<GOODSLIST.length;i++){
			for(var j=i+1;j<GOODSLIST.length;j++){	
				if(!GOODSLIST[i][colum] || (GOODSLIST[i][colum]<GOODSLIST[j][colum] && GOODSLIST[j][colum] )){
					var tempr=GOODSLIST[i];
					GOODSLIST[i]=GOODSLIST[j];
					GOODSLIST[j]=tempr;
				}
			}
		}
	}


}
*/

function sortJSONresult(colum,asc){

	if(asc){
		for(var i=0;i<result.length;i++){
			for(var j=i+1;j<result.length;j++){
				if( result[i][colum]>result[j][colum] || (result[j]['checkthis'] && !result[i]['checkthis']) ) {
					var tempr=result[i];
					result[i]=result[j];
					result[j]=tempr;
				}
			}
		}
	}
	else{
		for(var i=0;i<result.length;i++){
			for(var j=i+1;j<result.length;j++){	
				if( result[i][colum]<result[j][colum] || (result[j]['checkthis'] && !result[i]['checkthis']) ){
					var tempr=result[i];
					result[i]=result[j];
					result[j]=tempr;
				}
			}
		}
	}

}


var ID_SYSTEM=30000142;
var ID_MARKETSTATION=60003760;
var ID_REGION=10000002;



for (var listn=0;listn<GOODSLIST.length ;listn++ ){
		
	var typeURL= "https://esi.evetech.net/latest/universe/types/"+GOODSLIST[listn]["type_id"]+"/?datasource=tranquility&language=en-us";

	result=loadESI(typeURL);
	
	GOODSLIST[listn]["item_name"]=result["name"];
}

for (var listn=0;listn<GOODSLIST.length ;listn++ ){
	for(var i=listn+1;i<GOODSLIST.length;i++){

		if(GOODSLIST[listn]["item_name"]>GOODSLIST[i]["item_name"]){

			var tmpGOODS=GOODSLIST[listn];
			GOODSLIST[listn]=GOODSLIST[i];
			GOODSLIST[i]=tmpGOODS;

		}
	}
}



//알람 기능을 구현하려면 오더 내역으로부터 알람을 배정할 배열을 생성해야 한다.
//그 뒤 로컬스토리지에서는 혹시 이전에 알람설정을 해놓은게 있는지 그 정보만 받아온다.

for(var i=0;i<GOODSLIST.length;i++){

	if(localStorage.getItem(String(GOODSLIST[i]['order_id']))) {

		GOODSLIST[i]['alarm']=localStorage.getItem(String(GOODSLIST[i]['order_id']) );
	}
	else{
		GOODSLIST[i]['alarm']=ALARM_DEFAULT;
	}

	
}

//정보를 다 읽어왔다면 정보를 완전히 제거하고 새로 생성해야 한다.
localStorage.clear();


for(var i=0;i<GOODSLIST.length;i++){
	localStorage.setItem(String(GOODSLIST[i]['order_id']),GOODSLIST[i]['alarm']);

}

var lowerorder=0,higherorder=0;
var loadedlistn=0;


if(ordertype!='buy'){

document.writeln("나보다 낮은 오더 : <span id='lowerorder'></span><br><div id='selltable'></div>");

var selltablestring="<table border=1><tr><th rowspan=2>Type</th><th>My Price</th><th>Lowest Price</th><th rowspan=2>Difference</th><th rowspan=2>Alarm</th></tr><tr><th colspan=2>Amount</th></tr>";

for(var listn=0;listn<GOODSLIST.length;listn++) {

	document.getElementById('progress').innerHTML= loadedlistn+" / "+GOODSLIST.length;

	//바이오더는 리전 단위로 보지만 셀오더는 스테이션 단위로만 보도록 한다.
	if(GOODSLIST[listn]['location_id']==id_marketstation && GOODSLIST[listn]['is_buy_order']!=true){
		loadedlistn++;
		result = loadESI("https://esi.evetech.net/latest/markets/"+id_region+"/orders/?datasource=tranquility&order_type=sell&page=1&type_id="+GOODSLIST[listn]['type_id']);

		// 셀오더 정리하기

		for(var i=0;i<result.length;i++) {
		var tempESI = loadESI("https://esi.evetech.net/latest/route/"+id_system+"/"+result[i]["system_id"]+"/?datasource=tranquility&flag=secure");
		var jumps = tempESI.length-1;
	
		if( result[i]["range"]=="station" && result[i]["location_id"]!=id_marketstation ){
		
			result[i]["checkthis"]=false;
		}

		else if( result[i]["range"]=="solarsystem" && jumps!=0 ) {
		
			result[i]["checkthis"]=false;
		}
		else if( result[i]["range"]!="region" && jumps>parseInt(result[i]["range"]) ) {
		
			result[i]["checkthis"]=false;
		}
		//셀오더는 실질적으로 마켓 스테이션에서 직접 파는 것이 아니면 계산하지 않는것으로 한다.
		else if( result[i]["location_id"]!=id_marketstation) {
			result[i]["checkthis"]=false;
		}
		else{
			result[i]["checkthis"]=true;
		}

		}
		sortJSONresult("price",true);

		if(result[0]["order_id"]!=GOODSLIST[listn]['order_id']){
		selltablestring+="<tr><td rowspan=2>"+GOODSLIST[listn]['item_name']+"</td><td>"+GOODSLIST[listn]['price']+"</td><td>"+result[0]['price']+"</td><td rowspan=2>"+(GOODSLIST[listn]['price']-result[0]['price']).toFixed(2)+"</td><td rowspan=2><input type='checkbox' id='alarm"+GOODSLIST[listn]['order_id']+"' onchange='javascript:setAlarmCheckbox("+GOODSLIST[listn]['order_id']+")'></td></tr>";

		selltablestring+="<tr><td>"+GOODSLIST[listn]['volume_remain']+"</td><td>"+result[0]['volume_remain']+"</td></tr>";
		lowerorder++;

		}
		document.getElementById('selltable').innerHTML=selltablestring;
	}


}
selltablestring+="</table><br><hr><br>";
document.getElementById('selltable').innerHTML=selltablestring;

document.getElementById('lowerorder').innerHTML= lowerorder;

}


if(ordertype!='sell'){

document.writeln("나보다 높은 오더 : <span id='higherorder'></span><br><div id='buytable'></div>");

var buytablestring="<table border=1><tr><th rowspan=2>Type</th><th>My Price</th><th>Highest Price</th><th rowspan=2>Difference</th><th rowspan=2>Alarm</th></tr><tr><th colspan=2>Amount</th></tr>";

for(var listn=0;listn<GOODSLIST.length;listn++) {

	document.getElementById('progress').innerHTML= loadedlistn+" / "+GOODSLIST.length;
	if(GOODSLIST[listn]['region_id']==id_region && GOODSLIST[listn]['is_buy_order']==true){
		loadedlistn++;
		result = loadESI("https://esi.evetech.net/latest/markets/"+id_region+"/orders/?datasource=tranquility&order_type=buy&page=1&type_id="+GOODSLIST[listn]['type_id']);

		// 셀오더 정리하기

		for(var i=0;i<result.length;i++) {
		var tempESI = loadESI("https://esi.evetech.net/latest/route/"+id_system+"/"+result[i]["system_id"]+"/?datasource=tranquility&flag=secure");
		var jumps = tempESI.length-1;
	
		if( result[i]["range"]=="station" && result[i]["location_id"]!=id_marketstation ){
		
			result[i]["checkthis"]=false;
		}

		else if( result[i]["range"]=="solarsystem" && jumps!=0 ) {
		
			result[i]["checkthis"]=false;
		}
		else if( result[i]["range"]!="region" && jumps>parseInt(result[i]["range"]) ) {
		
			result[i]["checkthis"]=false;
		}
		else{
			result[i]["checkthis"]=true;
		}
		}
		sortJSONresult("price",false);

		if(result[0]["order_id"]!=GOODSLIST[listn]['order_id']){
		buytablestring+="<tr><td rowspan=2>"+GOODSLIST[listn]['item_name']+"</td><td>"+GOODSLIST[listn]['price']+"</td><td>"+result[0]['price']+"</td><td rowspan=2>"+(GOODSLIST[listn]['price']-result[0]['price']).toFixed(2)+"</td><td rowspan=2><input type='checkbox' id='alarm"+GOODSLIST[listn]['order_id']+"' onchange='javascript:setAlarmCheckbox("+GOODSLIST[listn]['order_id']+")'></td></tr>";

		buytablestring+="<tr><td>"+GOODSLIST[listn]['volume_remain']+"</td><td>"+result[0]['volume_remain']+"</td></tr>";

		higherorder++;

		}
		
		document.getElementById('buytable').innerHTML=buytablestring;
	}


}

buytablestring+="</table><br><br>";

document.getElementById('buytable').innerHTML=buytablestring;

document.getElementById('higherorder').innerHTML= higherorder;

document.getElementById('progress').innerHTML= GOODSLIST.length+" / "+GOODSLIST.length;

}


fillCheckbox();
/*
tablestring+="나보다 낮은 오더가 없음<br><table border=1><tr><th>Type</th><th>My Price</th><th>Lowest Price</th><th>Difference</th><th>Alarm</th></tr>";

for(var listn=0;listn<GOODSLIST.length;listn++) {
	
	if(GOODSLIST[listn]['region_id']==id_region && GOODSLIST[listn]['is_buy_order']!=true){
		
		result = loadESI("https://esi.evetech.net/latest/markets/"+id_region+"/orders/?datasource=tranquility&order_type=sell&page=1&type_id="+GOODSLIST[listn]['type_id']);

		// 셀오더 정리하기

		for(var i=0;i<result.length;i++) {
		var tempESI = loadESI("https://esi.evetech.net/latest/route/"+id_system+"/"+result[i]["system_id"]+"/?datasource=tranquility&flag=secure");
		var jumps = tempESI.length-1;
	
		if( result[i]["range"]=="station" && result[i]["location_id"]!=id_marketstation ){
		
			result[i]["checkthis"]=false;
		}

		else if( result[i]["range"]=="solarsystem" && jumps!=0 ) {
		
			result[i]["checkthis"]=false;
		}
		else if( result[i]["range"]!="region" && jumps>parseInt(result[i]["range"]) ) {
		
			result[i]["checkthis"]=false;
		}
		else{
			result[i]["checkthis"]=true;
		}
		}
		sortJSONresult("price",true);

		if(result[0]["order_id"]==GOODSLIST[listn]['order_id']){
		tablestring+="<tr><td>"+GOODSLIST[listn]['item_name']+"</td><td>"+GOODSLIST[listn]['price']+"</td><td>"+result[1]['price']+"</td><td>"+(GOODSLIST[listn]['price']-result[0]['price']).toFixed(2)+"</td><td><input type='checkbox' id='alarm"+listn+"'></td></tr>";

		}	
	}
}

tablestring+="</table><br><br>";
*/

//document.writeln(tablestring);




if(id_marketstation==60003760){
	locstring="Jita Market Station - The Forge";
}
else if(id_marketstation==60008494){
	locstring="Amarr Market Station - Domain";
}

else if(id_marketstation==1028858195912){
	locstring="Perimeter Market Keepstar - The Forge";
}


document.getElementById('location').innerHTML=locstring;



</script>

<script>

var refresh_interval=180;
var lt=new Date();
var loaded_time=lt.getTime();
var Timer=setInterval(autorefresh,1000);

function autorefresh(){

	if(document.getElementById('autorefresh').checked){
		var nt=new Date();
		var now_time=nt.getTime();

		var last_time=loaded_time+(refresh_interval*1000)-now_time;

		if(last_time<=0){
			clearInterval(Timer);
			window.location.reload();
		}
		else{
			document.getElementById('refreshtimer').innerHTML=Math.round(last_time/1000)+"s";
		}
	}
	else{
		document.getElementById('refreshtimer').innerHTML="";
		lt=new Date();
		loaded_time=lt.getTime();
	}
}


</script>

