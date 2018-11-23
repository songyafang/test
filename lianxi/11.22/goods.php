<?php
	header('content-type:text/html;charset=utf-8');
	$redis = new Redis();
	$redis->connect("127.0.0.1","6379");
	$pdo = new PDO("mysql:host=127.0.0.1;dbname=exam","root","root");
	$page = isset($_GET['page'])?$_GET['page']:1;
	$sql1 = "select count(*) from goods";
	$data = $pdo->query($sql1)->fetch();
	$count = $data['count(*)'];
	$pagesize = 2;
	$limit = ($page-1)*$pagesize;
	$countsize = ceil($count/$pagesize);
	if($redis->get("data".$page)){
		echo "只是redis中的数据";
		$redisdata = $redis->get("data".$page);
		$res = json_decode($redisdata,true);
	}else{
		$sql = "select * from goods limit $limit,$pagesize";
		$res = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$redisdata = json_encode($res);
		$redis->set("data".$page,$redisdata);
	}
	$pre = $page-1<1?1:$page-1;
	$next = $page+1>$countsize?$countsize:$page+1;
	$page = "<a href='goods.php?page=1'>首页</a>||<a href='goods.php?page=$pre'>上一页</a>||<a href='goods.php?page=$next'>下一页</a>||<a href='goods.php?page=$countsize'>尾页</a>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	<table border="1">
		<tr>
			<td>商品名称</td>
			<td>商品数量</td>
			<td>商品价格</td>
			<td>操作</td>
		</tr>
		<?php foreach($res as $v){ ?>
			<tr>
				<td><?php echo $v['goods_name']?></td>
				<td><?php echo $v['number']?></td>
				<td><?php echo $v['score']?></td>
				<td><a href="detail.php?id=<?php echo $v['id']?>"><input type="button" value="商品详情"></a></td>
			</tr>
		<?php }?>
	</table>
	<?php echo $page?>
</body>
</html>
