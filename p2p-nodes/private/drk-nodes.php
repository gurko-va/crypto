<?php
try {  
	$db = new PDO("mysql:host=localhost;dbname=dash", "dash", "xxx");  
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$db->exec("set names utf8");
}  
catch(PDOException $e) {  
	echo "MySQL ERROR"; 
}

function remove_ip($val){
	return	preg_replace('/(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\:(\d+)(\s?)/', '', str_replace('"', "", $val));
}

function clean_arr($val){
	return array_unique(array_filter($val));
}

function search_node($addr, $ips = '', $port = 7903){
	global $ctx;
	foreach($addr as $key => $value){
		$ips = remove_ip(@file_get_contents("http://$value:$port/peer_addresses", 0, $ctx))." ".$ips;
	}
	return $ips;
}

$ctx = stream_context_create(array('http' => array('timeout' => 1)));
$list = 'p2pool.dashninja.pl dash.p2pools.us eu.p2pool.pl p2pool.crunchpool.com happymining.de';
$addr = explode(' ', $list);
$addr = clean_arr(explode(' ', search_node($addr))); // first
$addr = clean_arr(explode(' ', search_node($addr))); // all

$table = '';
foreach($addr as $key => $value){
	$uptime = @file_get_contents("http://$value:7903/uptime", 0, $ctx);
	if(empty($uptime)) continue;
	$json = json_decode(@file_get_contents("http://$value:7903/local_stats", 0, $ctx), true);
	if(empty($json)) continue; 
	if(!empty($json['miner_hash_rates'])){
		$sum = array_sum($json['miner_hash_rates']);
	}else{
		$sum = 0;
	}
	
	$query_select = $db->prepare("SELECT * FROM `node` WHERE `ip` = :ip");
	$query_select->bindParam(':ip', $value, PDO::PARAM_STR);
	$query_select->execute();
	if($query_select->rowCount() != 1){
		$query_insert = $db->prepare("INSERT INTO `node` (`ip`, `country`, `hash`, `uptime`) VALUES (:ip, :country, :hash, :uptime)");
		$query_insert->bindParam(':ip', $value, PDO::PARAM_STR);
		$query_insert->bindParam(':country', geoip_country_name_by_name($value), PDO::PARAM_STR);
		$query_insert->bindParam(':hash', round($sum), PDO::PARAM_STR);
		$query_insert->bindParam(':uptime', round($uptime), PDO::PARAM_STR);
		$query_insert->execute();
	}else{
		$query_update = $db->prepare("UPDATE `node` SET `country` = :country, `hash` = :hash, `uptime` = :uptime WHERE `ip` = :ip");
		$query_update->bindParam(':ip', $value, PDO::PARAM_STR);
		$query_update->bindParam(':country', geoip_country_name_by_name($value), PDO::PARAM_STR);
		$query_update->bindParam(':hash', round($sum), PDO::PARAM_STR);
		$query_update->bindParam(':uptime', round($uptime), PDO::PARAM_STR);
		$query_update->execute();
	}
}

$query = $db->prepare("SELECT * FROM `node`");
$query->execute();
while($row=$query->fetch()){
	if (!in_array($row['ip'], $addr)){
		$query_delete = $db->prepare("DELETE FROM `node` WHERE `ip` = :ip");
		$query_delete->bindParam(':ip', $row['ip'], PDO::PARAM_STR);
		$query_delete->execute();
	}
}
?>
