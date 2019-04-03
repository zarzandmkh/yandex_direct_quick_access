<?php include('header.tpl');?>
<div class="container-fluid">
	<div class="row justify-content-center">
 	<?php if(!$campaigns):?>
 	<div class="col-12">
 		<h3 class="centered">Выберите аккаунт</h3>
 	</div>
 	<?php if (isset($errors) && count($errors) > 0): ?>
		<div class="alert alert-danger" role="alert">
		  <?php foreach ($errors as $key => $error): ?>
		  	<?=$error . ($key == count($errors)-1?'':'<br>');?>
		  <?php endforeach ?>
		</div>
	<?php endif ?>
 	<div class="col-12 flex-centered">
 		<ul>
			<?php foreach ($accounts as $key => $account): ?>
				<li>
					<a href="?account_reg=<?=$account['id'];?>"><?=(!empty($account['name'])?$account['name']:$account['login']);?></a> 
					<span class="settings-gear" data-container="body" data-toggle="popover" data-html="true" data-placement="right" data-content="
						<a onclick='return confirm('Вы уверены?')?true:false;' href='?delete_account=<?=$account['id'];?>'>удалить</a> | <a href='controller_account.php?edit_account=<?=$account['id'];?>'>редактировать</a>
						">
					</span>
				</li>
			<?php endforeach ?>
	 	</ul>
	 	<a href="controller_account.php">Добавить аккаунт</a>	
 	</div>
 	<?php endif ?>


 	<?php if ($campaigns): ?>
 		<?php if ($account['login']): ?>
 			<div class="col-12"><h1><?=$account['login'];?></h1><a href="/">К списку аккаунтов</a></div>

 		<?php endif ?>
 		<div class="col-12 flex-centered">
	 		<table border="1" cellpadding="5" style="border-collapse: collapse;">
	 			<tr>
	 				<th>РК</th>
	 				<th>Количество групп</th>
	 				<th>Количество фраз</th>
	 				<th>Статус</th>
	 				<th>Группы, где мало показов</th>
	 				<th>Группы, в которых менее 3 объявлений</th>
	 				<th>Группы, со статусом "Отклонено"</th>
	 			</tr>
	 			<?php foreach ($campaigns as $key => $campaign): ?>
	 				<?php if ($campaign->StatusClarification != 'Кампания перенесена в архив'): ?>
	 					<input type="hidden" name="campaign_id" value="<?=$campaign->Id;?>">
	 					<input type="hidden" name="campaign<?=$campaign->Id;?>" value="<?=htmlspecialchars(json_encode($campaign));?>">
		 				<tr>
		 					<td valign="top"><?=$campaign->Name;?></td>
		 					<td valign="top" style="text-align: center;"><?=$campaign->NumOfGroups;?></td>
		 					<td valign="top" style="text-align: center;"><?=$campaign->NumOfKeywords;?></td>	
		 					<td valign="top" style="text-align: center;"><?=$campaign->StatusClarification;?></td>	
		 					<td valign="top">
		 						<?php if (count($campaign->RarelyServedGroups) > 0 ): ?>
									<?php foreach ($campaign->RarelyServedGroups as $key => $group): ?>
			 							<?=$group->Name;?> <a href="yandex_auth.php?cid=<?=$campaign->Id;?>&adgroup_ids=<?=$group->Id;?>&appid=<?=$account['direct_id'];?>&aps=<?=$account['direct_pwd'];?>&resp_type=groupedit&login=<?=$account['login'];?>" target="_blank">перейти</a><br>	
			 						<?php endforeach ?>
		 						<?php else: ?>
		 							групп со статусом мало показов не обнаружено
		 						<?php endif ?>
		 					</td>
		 					<td valign="top" id="less3<?=$campaign->Id;?>"  style="text-align: center;">				 						
		 						<span id="wait-alert<?=$campaign->Id;?>">
									<img src="../images/loading.gif" alt="Загрузка"><br>
		 							подождите, список групп загружается.......
		 						</span>
		 					</td>
		 					<td valign="top" id="rejected<?=$campaign->Id;?>"  style="text-align: center;">				 						
		 						<span id="wait-alert-rejected<?=$campaign->Id;?>">
									<img src="../images/loading.gif" alt="Загрузка"><br>
		 							подождите, список групп загружается.......
		 						</span>
		 					</td>	
		 				</tr>
		 			<?php endif ?> 				
	 			<?php endforeach ?>
	 		</table>
 		</div>
 		<div class="col-12">
 			<button onclick="$('html, body').animate({scrollTop: $('#archive-hash').offset().top}, 1000);" class="btn btn-primary" type="button" data-toggle="collapse" data-target="#archive" aria-expanded="false" aria-controls="archive">
 				Показать архивные кампании
 			</button>
 		</div>
 		<div id="archive-hash"></div>
		<div id="archive" class="collapse col-12 flex-centered">
			<table border="1" cellpadding="5" style="border-collapse: collapse;">
	 			<tr>
	 				<th>РК</th>
	 				<th>Количество групп</th>
	 				<th>Количество фраз</th>
	 				<th>Статус</th>
	 				<th>Группы, где мало показов</th>
	 				<!-- <th>Группы, в которых менее 3 объявлений</th> -->
	 			</tr>
	 			<?php foreach ($campaigns as $key => $campaign): ?>
	 				<?php if ($campaign->StatusClarification == 'Кампания перенесена в архив'): ?>
		 				<tr>
		 					<td valign="top"><?=$campaign->Name;?></td>
		 					<td valign="top" style="text-align: center;"><?=$campaign->NumOfGroups;?></td>
		 					<td valign="top" style="text-align: center;"><?=$campaign->NumOfKeywords;?></td>	
		 					<td valign="top" style="text-align: center;"><?=$campaign->StatusClarification;?></td>	
		 					<td>
		 						<?php foreach ($campaign->RarelyServedGroups as $key => $group): ?>
		 							<?=$group->Name;?> <a href="yandex_auth.php?cid=<?=$campaign->Id;?>&adgroup_ids=<?=$group->Id;?>&appid=<?=$account['direct_id'];?>&aps=<?=$account['direct_pwd'];?>&resp_type=groupedit&login=<?=$account['login'];?>" target="_blank">перейти</a><br>
		 							
		 						<?php endforeach ?>
		 					</td>	
		 				</tr>
		 			<?php endif ?> 				
	 			<?php endforeach ?>
	 		</table>
		</div>
	 	<a id="back-to-top" href="#" class="btn btn-primary" role="button" title="Вверх" >
			вверх ^
		</a>		
 	<?php endif ?>
</div>
</div>
<?php include('footer.tpl');?>
<?php if ($campaigns && isset($_GET['account'])): ?>
<script>
	//groups with less than 3 ads or with ads with rejected status 
	$(document).ready(function(){
		$('input[name=campaign_id]').each(function(){
			var campaignId = $(this).val();
			var campaign = $('input[name=campaign' + campaignId + ']').val();
	 		$.ajax({
			  method: "POST",
			  url: "controller_ajax.php",
			  dataType:'json',
			  data: {ajax_campaign:campaign, ajax_campaign_id:campaignId, account:<?=$_GET['account'];?>}
			})
			  .done(function( msg ) {
			  	//groups with less than 3 ads
			  	var groups = msg[0];
			  	if(groups.length > 0){
			  		for(var i = 0; i < groups.length; i++){
			  			$('#wait-alert'+campaignId).hide();
			  			$('#wait-alert'+campaignId).parent('td').css({'text-align':'left'});
			  			$('#less3'+campaignId).append(groups[i]['Name']+' <a href="yandex_auth.php?cid=' + campaignId + '&adgroup_ids=' + groups[i]['Id'] + '&appid=<?=$account['direct_id'];?>&aps=<?=$account['direct_pwd'];?>&resp_type=groupedit&login=<?=$account['login'];?>" target="_blank"> Перейти </a><br>' + "\r\n");
			  		}	
			  	}else{
			  		$('#wait-alert'+campaignId).hide();
			  		$('#less3'+campaignId).append('во всех группах минимум по 3 объявления');
			  	}

			  	//groups with ads with rejected status
			  	var rejectedGroups = msg[1];
			  	if(rejectedGroups.length > 0){
			  		for(var i = 0; i < rejectedGroups.length; i++){
			  			$('#wait-alert-rejected'+campaignId).hide();
			  			$('#wait-alert-rejected'+campaignId).parent('td').css({'text-align':'left'});
			  			$('#rejected'+campaignId).append(rejectedGroups[i]['Name']+' <a href="yandex_auth.php?cid=' + campaignId + '&adgroup_ids=' + rejectedGroups[i]['Id'] + '&appid=<?=$account['direct_id'];?>&aps=<?=$account['direct_pwd'];?>&resp_type=groupedit_rejected&login=<?=$account['login'];?>" target="_blank"> Перейти </a><br>' + "\r\n");
			  		}	
			  	}else{
			  		$('#wait-alert-rejected'+campaignId).hide();
			  		$('#rejected'+campaignId).append('нет групп, со статусом "отклонено"');
			  	}
						  				    
			})
			.fail(function(msg){
				console.log(msg);
				$('#wait-alert, #wait-alert-rejected'+campaignId).hide();
		  		$('#less3, #rejected'+campaignId).append('ошибка при получении данных');
			})
		});

		// back to top button
		$(window).scroll(function () {
	        if ($(this).scrollTop() > 10) { 
	            $('#back-to-top').fadeIn();
	        } else {
	            $('#back-to-top').fadeOut();
	        }
	    });
	    // scroll body to 0px on click
	    $('#back-to-top').click(function () {
	        $('body,html').animate({
	            scrollTop: 0
	        }, 800);
	        return false;
	    });
	});
	</script>
<?php endif ?>
<script>
	$(function () {
	  $('[data-toggle="popover"]').popover()
	})
</script>
