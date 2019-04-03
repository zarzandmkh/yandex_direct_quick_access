<?php
//controller for ajax requests
	include('model.php');
	$model = new model;
	$model->db_connect();
	$accounts = $model->get_accounts();
	$output = '';
	if(!isset($_POST['account']) || empty($_POST['account'])){
		$output .='Ошибка: не передана id аккаунта <br>' . "\r\n";
		exit();
	}
	if(isset($_POST['ajax_campaign'])){
		$campaign = json_decode($_POST['ajax_campaign']);
		$output .= $model->ajax_get_groups_less_3_ads($campaign, strip_tags($_POST['account']));
	}else{
		$output .= 'Ошибка: не переданы данные кампании <br>' . "\r\n" ;
	}
	echo $output;
	exit();

?>