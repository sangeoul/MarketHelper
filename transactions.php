<html>
<script type="text/javascript" src="./style/jquery-3.3.1.min.js"></script>
<style>

table{
	border-collapse:collapse;
}

td{
	border-collapse:collapse;
	border: 1px solid black;
}
</style>
<body>

<div id=listarea></div>

<?php

include "./market_phplib.php";
session_start();

dbset();



logincheck_markethelper();
menutable();
?>

<?php



if(isset($USER_ACCESS_TOKEN)) {


	$header_type= "Content-Type:application/json";
	$authcurl= curl_init();
	curl_setopt($authcurl, CURLOPT_SSL_VERIFYPEER, $SSLauth); 
	curl_setopt($authcurl,CURLOPT_HTTPGET,true);
	curl_setopt($authcurl,CURLOPT_HTTPHEADER,array($header_type,"Authorization: Bearer ".$USER_ACCESS_TOKEN));
	curl_setopt($authcurl,CURLOPT_URL,"https://esi.evetech.net/latest/characters/".$_SESSION['markethelper_user_id']."/wallet/transactions");
	curl_setopt($authcurl,CURLOPT_RETURNTRANSFER,true);

	$curl_response=curl_exec($authcurl);
	curl_close($authcurl);

	$orderdata=json_decode($curl_response,true);

	for($i=0;$i<sizeof($orderdata);$i++){
		$qr="select * from Markethelper_transactions where transaction_id=".$orderdata['transaction_id'].";";
		$result=$dbcon->query($qr);
		if($result->num_rows==0){
			
			if($orderdata[$i]['is_buy']==null){
				$orderdata[$i]['is_buy']=0;
			}
			$qr="insert into Markethelper_transactions (userid,username,client_id,date,is_buy,is_personal,journal_ref_id,location_id,quantity,transaction_id,type_id,unit_price) values (".$_SESSION['markethelper_user_id'].",\"".$_SESSION['markethelper_user_name']."\",".$orderdata[$i]['client_id'].",\"".str_replace("Z","",$orderdata[$i]['date'])."\",".$orderdata[$i]['is_buy'].",".$orderdata[$i]['is_personal'].",".$orderdata[$i]['journal_ref_id'].",".$orderdata[$i]['location_id'].",".$orderdata[$i]['quantity'].",".$orderdata[$i]['transaction_id'].",".$orderdata[$i]['type_id'].",".$orderdata[$i]['unit_price'].");";
			$dbcon->query($qr);

		}
	}
}
else{

	errorlogout("ESI 에러 발생");
}

?>

<?php

$qr="select * from Markethelper_transactions where userid=".$_SESSION['markethelper_user_id']." order by transaction_id asc;";

$result=$dbcon->query($qr);

$tax=0.072;


$buylist=Array();
$selllist=Array();

for($i=0;$i<$result->num_rows;$i++){

	$trandata=$result->fetch_array();
	
	//transaction의 리스트를 buylist 에 새로 정리
	for($j=0;$j<sizeof($buylist);$j++){

	//정리된 품목 중 같은 품목을 찾으면 같이 정리한다.		
	if($buylist[$j]['type_id']==$trandata['type_id'] && !$buylist[$j]['closed']){

		//구매기록이면 buylist에 합산
		if($trandata['is_buy']){
			$buylist[$j]['unit_price']=( $buylist[$j]['unit_price']*$buylist[$j]['quantity'] + $trandata['unit_price']*$trandata['quantity'])/($buylist[$j]['quantity']+$trandata['quantity']);	
			$buylist[$j]['quantity']+=$trandata['quantity'];
		
		}
		//셀기록이면 selllist에 합산
		else{
			
			$selllist[$j]['unit_price']=( $selllist[$j]['unit_price']*$selllist[$j]['quantity'] + $trandata['unit_price']*(1-$tax)*$trandata['quantity'])/($selllist[$j]['quantity']+$trandata['quantity']);	
			$selllist[$j]['quantity']+=$trandata['quantity'];
			$selllist[$j]['date']=$trandata['date'];
			//완판 체크
			if($selllist[$j]['quantity']==$buylist[$j]['quantity']){
				$buylist[$j]['closed']=1;
			}

			
		}
		$j=sizeof($buylist)+1;


	}

	}
	
	//못 찾으면(혹은 완판이면) 새로운 품목으로 정리한다.
	if($j==sizeof($buylist) && $trandata['is_buy']){
			
		
		
		$buylist[$j]['type_id']=$trandata['type_id'];
		$buylist[$j]['quantity']=$trandata['quantity'];
		$buylist[$j]['date']=$trandata['date'];
		$buylist[$j]['unit_price']=$trandata['unit_price'];
		$buylist[$j]['closed']=0;
		$selllist[$j]['type_id']=$trandata['type_id'];
		$selllist[$j]['quantity']=0;
		$selllist[$j]['date']=0;
		$selllist[$j]['unit_price']=0;

	}
	
}



errordebug(sizeof($buylist));

$benefit=0;
echo("<table>\n");
echo("<tr><td colspan=5><span id='benefit'></span></td></tr>\n");

for($i=sizeof($buylist)-1,$j=0;$i>=0;$i--){
	if($selllist[$i]['quantity']>0){
		echo("<tr>\n");
		echo("<td id='type".$j."'>".$buylist[$i]['type_id']."</td>\n");
		echo("<td>".number_format($selllist[$i]['quantity'])." / ".number_format($buylist[$i]['quantity'])."</td>\n");
		echo("<td><font color=green>".number_format(round($selllist[$i]['unit_price'],2),2)."</font> / <font color=red>".number_format(round($buylist[$i]['unit_price'],2),2)."</font></td>\n");
		
		if($selllist[$i]['unit_price']>$buylist[$i]['unit_price']){
			$fcolor='green';
		}
		else{
			$fcolor='red';
		}
		echo("<td><font color=".$fcolor.">".number_format(round(($selllist[$i]['unit_price']-$buylist[$i]['unit_price'])*$selllist[$i]['quantity'],2),2)."</font></td>\n");
		echo("<td>".ceil((strtotime($selllist[$i]['date'])-strtotime($buylist[$i]['date']))/86400)." days(".$buylist[$i]['date']." ~ ".$selllist[$i]['date'].")</td>\n");
		echo("</tr>\n");
		$benefit+=($selllist[$i]['unit_price']-$buylist[$i]['unit_price'])*$selllist[$i]['quantity'];
		$j++;
	}

}
echo("</table>\n");
echo("<script>\nvar benefit='".number_format(round($benefit),2)."';\n</script>\n");
	


?>



<script>

document.getElementById('benefit').innerHTML="Total : <font color=green>"+benefit+"</font>";
for(var i=0;i<1000;i++){
	if(document.getElementById('type'+i)){

		getName(i);
	}
	else
		break;

}
function getName(n){

	var namefield=document.getElementById('type'+n);

	if(namefield){
		var type_id=parseInt(namefield.innerHTML);
		var item_data=loadESI("https://esi.evetech.net/latest/universe/types/"+type_id+"/?datasource=tranquility&language=en-us");
		namefield.innerHTML=item_data['name'];
	}

}



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

</script>