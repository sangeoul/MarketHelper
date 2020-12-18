<html>
<body>
<embed type="audio/mpeg" id="autoalarm" width=1 height=1></embed><br>
<div id=listarea></div>

<?php
session_start();


include "./market_phplib.php";
dbset();

logincheck_markethelper();
menutable();
?>
마켓 최저가가 내 가격보다 내려간 것이 있는지 알려주는 페이지입니다.<br><Br>

ESI 자체 딜레이가 있으므로, 실제 마켓 정보보다 최대 5분 늦게 반영될 수 있습니다.<br><br>

현재 지역 Jita Market Station <br>ESI 로딩(<span id='progress'></span> orders)

<!-- 품목 / 내 가격 / 마켓가격 / 마켓최저가와의 차이 / 알람(체크박스)-->



<?php



$REFRESH_INTERVAL = 180; // 새로고침 타이머(초) 기본값은 300초 (5분)



$authcurl= curl_init();
$header_type= "Content-Type:application/json";
$curl_body="{'grant_type':'refresh_token','refresh_token':'".$_SESSION['refresh_token']."'}";



curl_setopt($authcurl,CURLOPT_URL,"https://login.eveonline.com/oauth/token");
//curl_setopt($authcurl,CURLOPT_URL,"https://esi.evetech.net/latest/characters/92497990/?datasource=tranquility");
curl_setopt($authcurl, CURLOPT_SSL_VERIFYPEER, $SSLauth); 
curl_setopt($authcurl,CURLOPT_HTTPHEADER,array($header_type,$header_auth));
curl_setopt($authcurl,CURLOPT_POSTFIELDS,$curl_body);
curl_setopt($authcurl,CURLOPT_POST,1);
curl_setopt($authcurl,CURLOPT_RETURNTRANSFER,true);


//echo "curl -XPOST -H \"".$header_type."\" -H \"".$header_auth."\" -d \"".$curl_body."\" https://login.eveonline.com/oauth/token <br><br>";


$curl_response=curl_exec($authcurl);
curl_close($authcurl);


$obj_response=json_decode($curl_response,true);


if(isset($obj_response["access_token"])) {


	$header_type= "Content-Type:application/json";
	$authcurl= curl_init();
	curl_setopt($authcurl, CURLOPT_SSL_VERIFYPEER, $SSLauth); 
	curl_setopt($authcurl,CURLOPT_HTTPGET,true);
	curl_setopt($authcurl,CURLOPT_HTTPHEADER,array($header_type,"Authorization: Bearer ".$obj_response['access_token']));
	curl_setopt($authcurl,CURLOPT_URL,"https://esi.evetech.net/latest/characters/".$_SESSION['user_id']."/orders/?datasource=tranquility");
	curl_setopt($authcurl,CURLOPT_RETURNTRANSFER,true);

	$curl_response=curl_exec($authcurl);
	curl_close($authcurl);

	$orderdata=json_decode($curl_response,true);

	echo("<script>\n");

	echo("var GOODSLIST=JSON.parse('".$curl_response."');\n");

	echo("</script>\n");
}
else{

errorhome("ESI 에러 발생");

}

?>



<script>

document.getElementById('progress').innerHTML= "0 / "+GOODSLIST.length;


function loadESI(ESIquery)
{
	
	var ESIdata=new XMLHttpRequest();

	ESIdata.onreadystatechange=function(){

		if (this.readyState == 4 && this.status == 200){
			
			returnvalue=JSON.parse(this.responseText);
			
			
		}

			
	}

	ESIdata.open("GET",ESIquery,false);
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


var lowerorder=0;
var tablestring="나보다 낮은 오더 : <span id='lowerorder'></span><br><table border=1><tr><th>Type</th><th>My Price</th><th>Lowest Price</th><th>Difference</th><th>Alarm</th></tr>";

for(var listn=0;listn<GOODSLIST.length;listn++) {

	document.getElementById('progress').innerHTML= listn+" / "+GOODSLIST.length;
	if(GOODSLIST[listn]['region_id']==ID_REGION && GOODSLIST[listn]['is_buy_order']!=true){

		result = loadESI("https://esi.evetech.net/latest/markets/"+ID_REGION+"/orders/?datasource=tranquility&order_type=sell&page=1&type_id="+GOODSLIST[listn]['type_id']);

		// 셀오더 정리하기

		for(var i=0;i<result.length;i++) {
		var tempESI = loadESI("https://esi.evetech.net/latest/route/"+ID_SYSTEM+"/"+result[i]["system_id"]+"/?datasource=tranquility&flag=secure");
		var jumps = tempESI.length-1;
	
		if( result[i]["range"]=="station" && result[i]["location_id"]!=ID_MARKETSTATION ){
		
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

		if(result[0]["order_id"]!=GOODSLIST[listn]['order_id']){
		tablestring+="<tr><td>"+GOODSLIST[listn]['item_name']+"</td><td>"+GOODSLIST[listn]['price']+"</td><td>"+result[0]['price']+"</td><td>"+(GOODSLIST[listn]['price']-result[0]['price']).toFixed(2)+"</td><td><input type='checkbox' id='alarm"+listn+"'></td></tr>";
		lowerorder++;

		}	
	}


}

tablestring+="</table><br><br>";

document.getElementById('progress').innerHTML= GOODSLIST.length+" / "+GOODSLIST.length;
document.getElementById('lowerorder').innerHTML= lowerorder;
/*
tablestring+="나보다 낮은 오더가 없음<br><table border=1><tr><th>Type</th><th>My Price</th><th>Lowest Price</th><th>Difference</th><th>Alarm</th></tr>";

for(var listn=0;listn<GOODSLIST.length;listn++) {
	
	if(GOODSLIST[listn]['region_id']==ID_REGION && GOODSLIST[listn]['is_buy_order']!=true){
		
		result = loadESI("https://esi.evetech.net/latest/markets/"+ID_REGION+"/orders/?datasource=tranquility&order_type=sell&page=1&type_id="+GOODSLIST[listn]['type_id']);

		// 셀오더 정리하기

		for(var i=0;i<result.length;i++) {
		var tempESI = loadESI("https://esi.evetech.net/latest/route/"+ID_SYSTEM+"/"+result[i]["system_id"]+"/?datasource=tranquility&flag=secure");
		var jumps = tempESI.length-1;
	
		if( result[i]["range"]=="station" && result[i]["location_id"]!=ID_MARKETSTATION ){
		
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



</script>

<div id='alarmtable'></div>

<script>
document.getElementById('alarmtable').innerHTML=tablestring;
</script>
