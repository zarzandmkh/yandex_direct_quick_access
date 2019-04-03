<?php 
	session_start();
	error_reporting(E_ALL);
	header('Content_type:text/html; charset=utf-8');
	mb_internal_encoding('utf-8');

	include('model.php');
	$model = new model;

	$view_data = array();
	$errors = array();

	$dbh = $model->db_connect();

    //validating input filelds
	function check_fields($data){
		$errors = array();
		if(isset($data['name']) && strlen($data['name']) < 2)$errors[] = 'Имя должно содержать не менее 2 символа';
		if(!isset($data['login']) || empty($data['login']))$errors[] = 'Введите логин яндекс аккаунта';
		return $errors; 
	}

	//page view data
	$view_data['page_name'] = 'Добавить аккакунт';
	$view_data['submit_name'] = 'add';
	$view_data['submit_title'] = 'Добавить';

	if(isset($_POST['add'])){ // adding new account
		$data = $_POST['account'];
		foreach ($data as $key => $dt) {
			if(empty($dt))unset($data[$key]);
		}

		$errors = check_fields($data);

		if(count($errors) == 0){
			if($id = $model->add_data($data, 'accounts')){
				$_SESSION['add_success'] = $id;
				header("location:controller_account.php");
			}else{
				$errors[] = 'Ошибка при добавлении аккаунта';
			}
		}
	}else if(isset($_GET['edit_account'])){
		//esit account
		$account_id = (int)  trim(urldecode($_GET['edit_account']));
		$view_data['page_name'] = 'Редактировать аккакунт';
		$view_data['submit_name'] = 'save_changes';
		$view_data['submit_title'] = 'Сохранить';
		if(isset($_SESSION['edit_success'])){
			$view_data['edit_success'] = $_SESSION['edit_success'];
			unset($_SESSION['edit_success']);
		}
		if(!$view_data['account'] = $model->get_account_data($account_id))$errors[] = 'Такого аккаунта не существует';
		if(isset($_POST['save_changes'])){
			$errors = check_fields($_POST['account']);
			if(count($errors) == 0){
				if($id = $model->edit_account($account_id, $_POST['account'])){
					$_SESSION['edit_success'] = $id;
					header("location:controller_account.php" . (!empty($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:'') );
				}else{
					$errors[] = 'Ошибка при сохранении данных';
				}
			}
		}

	}else{
		if(isset($_SESSION['add_success'])){
			$view_data['success'] = $_SESSION['add_success'];
			if(isset($_GET['tkn'])){
				if(!$model->add_token($_SESSION['add_success'], $_GET['tkn'])){
					exit('Ошибка:Не удалось добавить токен после получения. Попробуйте добавить вручную. Токен:' . $_GET['tkn']);	
				}else{
					header("location:index.php?account_reg=" . $_SESSION['add_success']);
				}
			}else{
				if(!$model->add_token($_SESSION['add_success']) ){
					$view_data['errors'][] = 'Не удалось добавить токен. попробуйте добавить вручную';
					unset($view_data['success']);
					unset($_SESSION['add_success']);
				}
			}
			
			unset($_SESSION['add_success']);
		}
	}
	$view_data['errors'] = $errors;
	$html = $model->load_view('add_account', $view_data);
	echo $html;	
	$model->db_disconnect();
	exit();
 ?>