<?php 
  session_start();

  /**
  * this file helps to login to yandex account via oauth
  * we check for the availability of all the necessary data, redirect to the appropriate service of Yandex, which returns a code (? code) upon successful authentication
  * after receiving the code, we watch [resp_type] (response type) parameter and redirect to the address we need
  * !!!!IMPORTANT!!!!! all redirect urls must be with the same protocol 
  */

  //if authentication was successfully redirected to the desired service based on the parameter response type 
  if(isset($_GET['code'])){
      switch ($_SESSION['resp_type']) {
          case 'groupedit':
            $callback_url = 'https://direct.yandex.ru/registered/main.pl?cmd=showCampMultiEdit&cid=' . $_SESSION['cid'] . '&banner_status=active&adgroup_ids=' . $_SESSION['adgroup_ids'] . '&ulogin=' . $_SESSION['login'];
            break;
          case 'groupedit_rejected':
            $callback_url = 'https://direct.yandex.ru/registered/main.pl?cmd=showCampMultiEdit&cid=' . $_SESSION['cid'] . '&banner_status=rejected&adgroup_ids=' . $_SESSION['adgroup_ids'] . '&ulogin=' . $_SESSION['login'];
            break;
          case 'acc_auth':
            $callback_url = 'http://' . $_SERVER['HTTP_HOST'] . '?account=' . $_SESSION['account_id'];
            break;
          default:
            $callback_url = 'http://' . $_SERVER['HTTP_HOST'];
      }
      header('Location:'.$callback_url);
      exit();
  }

  //check whether all data for authorization (main)
  if(isset($_GET['appid'])){$_SESSION['appid'] = $_GET['appid'];}else{exit('Аутентификация невозможна: не передан id приложения [appid]');}
  if(isset($_GET['aps'])){$_SESSION['aps'] = $_GET['aps'];}else{exit('Аутентификация невозможна: не передан пароль приложения [aps]');}
  if(isset($_GET['resp_type'])){$_SESSION['resp_type'] = $_GET['resp_type'];}else{exit('Аутентификация невозможна: не передан параметр тип запроса [resp_type]');}
  if(isset($_GET['login'])){$_SESSION['login'] = $_GET['login'];}else{exit('Аутентификация невозможна: не передан логин [login]');}

  //validating data (group edit case)
  if($_GET['resp_type'] == 'groupedit'){
    if(isset($_GET['cid'])){$_SESSION['cid'] = $_GET['cid'];}else{exit('Аутентификация невозможна: не передан id кампании [cid]');}
    if(isset($_GET['adgroup_ids'])){$_SESSION['adgroup_ids'] = $_GET['adgroup_ids'];}else{exit('Аутентификация невозможна: не передан id группы [adgroup_ids]');}
  }
  // validating data (login/account chosing)
  if($_GET['resp_type'] == 'acc_auth'){
    if(isset($_GET['account_id'])){$_SESSION['account_id'] = $_GET['account_id'];}else{exit('Аутентификация невозможна: не передан id аккаунта [account_id]');}
  }

 // redirect to the appropriate service of Yandex and return here with the code (? code) (it will already work from the 11th line)
  header('Location:https://oauth.yandex.ru/authorize?response_type=code&client_id=' . $_GET['appid'] . '&login_hint=' . $_GET['login'] . '&client_secret=' . $_GET['aps']) . '&redirect_uri=' . 'http://' . $_SERVER['HTTP_HOST'] . '/yandex_auth.php';
  
 ?>