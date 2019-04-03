<?php include('header.tpl');?>
<div class="container">
	<div class="row justify-content-center flex-column">
	<h1 class="centered"><?=$page_name;?></h1>
	<?php if (isset($errors) && count($errors) > 0): ?>
		<div class="alert alert-danger" role="alert">
		  <?php foreach ($errors as $key => $error): ?>
		  	<?=$error . ($key == count($errors)-1?'':'<br>');?>
		  <?php endforeach ?>
		</div>
	<?php endif ?>
	<?php if (isset($success)): ?>
		<div class="alert alert-success" role="alert">
		  Аккаунт успешно добавлен
		</div>
	<?php endif ?>
	<?php if (isset($edit_success)): ?>
		<div class="alert alert-success" role="alert">
		  Изменения успешно сохранены
		</div>
	<?php endif ?>
	<form method="post" action="../controller_account.php<?=!empty($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:'';?>">
	  <div class="form-group">
	    <label>Название аккаунта</label>
	    <input class="form-control" type="text" name="account[name]" value="<?=isset($_POST['account']['name'])?$_POST['account']['name']:(isset($account['name'])?$account['name']:'');?>">
	  </div>
	  <div class="form-group">
	    <label>Логин Яндекс аккаунта</label>
	    <input class="form-control" type="text" name="account[login]" value="<?=isset($_POST['account']['login'])?$_POST['account']['login']:(isset($account['login'])?$account['login']:'');?>">
	  </div>
	  <button type="submit" name="<?=$submit_name;?>" class="btn btn-primary"><?=$submit_title;?></button>
	</div>
	<a href="/">на главную</a>
</div>
<?php include('footer.tpl');?>
