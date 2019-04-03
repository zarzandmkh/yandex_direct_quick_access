<?php 
	header('Content_type:text/html; charset=utf-8');
	mb_internal_encoding('utf-8');
	$model = new model;   //model object
	$model->db_connect(); // connecting database
	$view_data = array(); // data for generating html
	$errors = array();    // array with errors

	//register account
	if(isset($_GET['account_reg'])){
		$id = (int) trim(urldecode($_GET['account_reg']));
		$account = $model->get_account_data($id);
		if(!$account)exit('Неверный id аккаунта');
		$url = 'yandex_auth.php?appid=' . $dev_acc['direct_id'] . '&aps=' .  $dev_acc['direct_pwd'] . '&resp_type=acc_auth&login=' . $account['login'] . '&account_id=' . $account['id'];
		header('Location:'.$url);
		exit();
	}

	//view account data
	if(isset($_GET['account'])){
		$id = (int) trim(urldecode($_GET['account']));
		if($my_campaigns = $model->get_direct_data($model->get_account_data($id), 'https://api.direct.yandex.com/json/v5')) {
			$view_data['campaigns'] = $my_campaigns;
		}else{
			$view_data['campaigns'] = false;
		}		
	}else{
		$view_data['campaigns'] = false;
	}

	//deletiing account
	if(isset($_GET['delete_account'])){
		$id = (int) trim(urldecode($_GET['delete_account']));
		if($model->delete_account($id)){
			header("Location:http://" . $_SERVER['HTTP_HOST']);
		}else{
			$errors[] = 'Ошибка при удалении аккаунта';
		}
	}
	
	//view data
	$view_data['errors'] = $errors;
	$view_data['accounts'] = $model->get_accounts();
	$view_data['token'] = 'AQAAAAAhtqk1AAUlaTcGjwbnYkpvuucx3-dILh4';
	$view_data['account'] = isset($_GET['account'])?$model->get_account_data($_GET['account']):false;
	$view_data['account']['direct_id'] = $dev_acc['direct_id'];
	$view_data['account']['direct_pwd'] = $dev_acc['direct_pwd'];

	$html = $model->load_view('yandex_direct', $view_data);
	echo $html;
 ?>