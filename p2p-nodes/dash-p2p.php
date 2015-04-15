<?

try {  
	$db = new PDO("mysql:host=localhost;dbname=dash", "dash", "xxx");  
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$db->exec("set names utf8");
}  
catch(PDOException $e) {  
	echo "MySQL ERROR"; 
}

function secondsToTime($seconds) {
	$dtF = new DateTime("@0");
	$dtT = new DateTime("@$seconds");
	return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes');
}

function ghash($globall_hashrate){
	$hash_display = ($globall_hashrate/(1024*1024));	
	return round($hash_display, 2);
}

$table = '';
$query = $db->prepare("SELECT * FROM `node`");
$query->execute();
while($row=$query->fetch()){
	$table = $table."<tr><td><a href='http://{$row['ip']}:7903' target='_blank'>{$row['ip']}:7903</a></td><td>{$row['country']}</td><td>".ghash($row['hash'])."</td><td>".secondsToTime($row['uptime'])."</td></tr>";
}
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>DASH P2Pool nodes</title>
		<link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/plug-ins/1.10.6/integration/bootstrap/3/dataTables.bootstrap.css">
		<script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.10.2.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.datatables.net/1.10.6/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.datatables.net/plug-ins/1.10.6/integration/bootstrap/3/dataTables.bootstrap.js"></script>
		<script type="text/javascript" charset="utf-8"> $(document).ready(function() { $('#example').dataTable({ "order": [[ 2, "desc" ]] }); }); </script>
		<style type="text/css"> .table td, th { text-align: center; } </style>
	</head>
	<body>
		<div class="container">
			<center>
				<h2>DASH P2Pool nodes</h2>
				<br/>
			</center>
			<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>P2Pool</th>
						<th>Country</th>
						<th>Rate (MH/s)</th>
						<th>Uptime</th>
					</tr>
				</thead>
				<tbody>
					<? echo $table; ?>
				</tbody>
			</table>
		</div>
	</body>
</html>
