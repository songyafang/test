<?php
	header('content-type:text/html;charset=utf-8');
	$redis = new Redis();
	$redis->connect("127.0.0.1","6379");
	$pdo = new PDO("mysql:host=127.0.0.1;dbname=exam","root","root");
	$id = $_GET['id'];
	$sql = "select * from goods where id=$id";
	$res = $pdo->query($sql)->fetch();
	if($redis->get("num".$id)){
		$redis->incr("num".$id);
		$num = $redis->get("num".$id);
	}else{
		$redis->set("num".$id,1);
		$num = $redis->get("num".$id);
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	<h1>您已经查看此商品：<?php echo $num?>次了</h1>
	<table border="1">
		<tr>
			<td>商品名称</td>
			<td>数量</td>
			<td>价钱</td>
		</tr>
		<tr>
			<td><?php echo $res['goods_name']?></td>
			<td><?php echo $res['number']?></td>
			<td><?php echo $res['score']?></td>
		</tr>
	</table>
</body>
</html>