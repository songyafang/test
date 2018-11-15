<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class Security extends IController{
	public function login(){
		$this->redirect('login');
	}

	public function login_ok(){
		$adminname = IReq::get('adminname');
		$password  = md5(IReq::get('password'));
		$admin1 =new IModel('admin1');
		$data = $admin1->getObj("adminname='$adminname' and password='$password'");
		 // var_dump($data);die;
		if($data){
			$session=new ISession;
			$session->set('adminname',$adminname);
			$this->redirect('/security/employee_list',true);
		}else{
			echo '登录失败';
		}
	}

	public function reg(){
		$this->redirect('reg');
	}

	public function reg_ok(){
		$username = IReq::get('username');
		$password = MD5(IReq::get('password'));
		$truename = IReq::get('truename');
		$salary = IReq::get('salary');
		$user1 =new IModel('user1');
		$data=[
			'username' =>$username,
			'password' =>$password,
			'truename' =>$truename,
			'salary' =>$salary,
		];
		$user1->setData($data);
		$user1->add();
		$this->redirect('login');
	}

	public function employee_list(){
		$redis=new Redis;
		$redis->connect('127.0.0.1',6379);
		$user1=new IModel('user1');
		if(isset($_POST['keyword']) && !empty($_POST['keyword'])){
			if($redis->exists($keyword)){
				$str = $redis->get($keyword);
				$data = json_decode($str,true);
				echo 'from redis';
			}else{
				$data = $user1->query("username like '%$keyword%'");
				$str=json_encode($data);
				$redis->set($keyword,$str);
				echo 'from db';
			}

		}else{
			$data = $user1->query();
		}
		$this->setRenderData(['data'=>$data]);
		$this->redirect('employee_list');
	}
	public function export(){
		require 'plugins/vendor/autoload.php';
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', 'Hello World !');
		$user1=new IModel('user1');
		$data=$user1->query();

		$spreadsheet->getActiveSheet()->fromArray(
				$data,
				NULL,
				'A3'
			);
		$sheet->setTitle('员工工资详情');
		$writer = new Xlsx($spreadsheet);
		$writer->save('public/员工信息1.xlsx');
		$admin_log=new IModel('admin_log');
		$data=[
			'adminname'=>ISession::get('adminname'),
			'ip'=>$_SERVER['REMOTE_ADDR'],
			'addtime'=>time(),
		];
		$admin_log->setData($data);
		$admin_log->add();
	}

	public function import(){
		require 'plugins/vendor/autoload.php';
		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
		$reader->setReadDataOnly(TRUE);
		$spreadsheet = $reader->load("public/员工信息.xlsx");

		$worksheet = $spreadsheet->getActiveSheet();
		$highestRow = $worksheet->getHighestRow();
		$highestColumn = $worksheet->getHighestColumn();
		$highestColumn++;

		for($row=3;$row<=$highestRow;++$row){
			for($col='B';$col != $highestColumn;++$col){
				$data[$row-3][]=$worksheet->getCell($col.$row)->getValue();
			}
		}
		$user1=new IMode('user1');
		foreach($data as $key => $value){
			$arr = [
				'username' =>$value[0],
				'password' =>$value[1],
				'truename' =>$value[2],
				'salary' =>$value[3],
			];
			$user1->setData($arr);
			$user1->add();
		}
	}
}
?>