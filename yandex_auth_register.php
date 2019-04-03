<?php
session_start();
//  yandex account login
// 'code' request sample
//https://oauth.yandex.ru/authorize?response_type=code&client_id=0f767a21205043cfb514736a535de31b&redirect_uri=http://demo16.unibix.ru/yandex_auth_register.php


$client_login = isset($_SESSION['direct_login'])?$_SESSION['direct_login']:$_GET['login'];
$client_id = isset($_SESSION['direct_id'])?$_SESSION['direct_id']:$_GET['id']; 

$client_secret = isset($_SESSION['direct_pwd'])?$_SESSION['direct_pwd']:$_GET['pwd']; //application password

// If the script was invoked with the parameter "code" in the URL, a request for a token is executed
if (isset($_GET['code'])){
    // Formation of parameters (body) of a POST request with confirmation code
    $query = array(
      'grant_type' => 'authorization_code',
      'code' => $_GET['code'],
      'client_id' => $client_id,
      'client_secret' => $client_secret,
      'login_hint' => $client_login
    );
    $query = http_build_query($query);

    // POST request header generation
    $header = "Content-type: application/x-www-form-urlencoded";

    // POST request execution and result
    $opts = array('http' =>
      array(
      'method'  => 'POST',
      'header'  => $header,
      'content' => $query
      ) 
    );
    $context = stream_context_create($opts);
    $result = file_get_contents('https://oauth.yandex.ru/token', false, $context);
    $result = json_decode($result);

    // The token must be saved for use in requests to the Yandex.Direct API.
    if(isset($result->access_token) && !empty($result->access_token)){
      $url = 'controller_account.php?tkn=' . $result->access_token;
      header("location:$url");
      exit();
    }else{
      // $url = 'add_account.php?error=1';
      // header("location:$url");
      // exit();
    }
}else{
  //we temporarily save the transferred data - they are needed after redirecting here with the confirmation code
  $_SESSION['direct_id'] = $_GET['id'];
  $_SESSION['direct_pwd'] = $_GET['pwd'];
  $_SESSION['direct_login'] = $_GET['login'];
  $url = 'https://oauth.yandex.ru/authorize?response_type=code&client_id=' . $client_id . '&client_secret=' . $_GET['pwd'] . '&login_hint=' . $_GET['login'] . '&redirect_uri=' . urlencode('http://' . $_SERVER['HTTP_HOST'] . '/yandex_auth_register.php');
  header("Location:$url");
  exit();
}



?>
