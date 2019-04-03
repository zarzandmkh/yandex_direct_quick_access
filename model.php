<?php 
class model{
	private $dbh;				//dtabase object
	private $app_id = ""; 		// yandex application ID
	private $app_pass = ""; 	// application password

	function __construct(){
		if(empty($this->app_id) || empty($this->app_pass))exit('ERROR:You need to add your app id and password, see readme');
	}


	/**
	* debugging
	*/
	public function out($var){
		echo '<pre>';
		print_r($var);
		echo '</pre>';
	}

	/**
	* sending request to yandex direct api via $url and returns result
	* yandex direct api documentation https://tech.yandex.ru/direct/doc/dg/concepts/about-docpage/
	* @param string $url request url
	* @param string $login yandex account login
	* @param string $token oauth token
	* @param array $params request parameters 
	* @return array arrat vith data or terminates script with error message if there is an error from yandex direct api
	*/
	public function get_response($url, $login, $token, $params){

		$headers = array(
		   "Authorization: Bearer $token",                   	// OAuth-token. Using word Bearer is neccesary
		   "Client-Login: $login",                      		// Advertising agency client login
		   "Accept-Language: ru",                             	// Response language
		   "Content-Type: application/json; charset=utf-8"    	// Data type and request encoding
		);
		// Convert request input parameters to JSON format
		$body = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		// Creating stream context: setting HTTP headers and request bodyа
		$streamOptions = stream_context_create(array(
		   	  'http' => array(
		      'method' => 'POST',
		      'header' => $headers,
		      'content' => $body
		   ),
		));

		$result = json_decode(file_get_contents($url, 0, $streamOptions));

        //if there is an error in the request, display the error and terminate the script
		if(isset($result->error)){
			echo __METHOD__ . ': ошибка';
			$this->out($result->error->error_detail);
			exit();
		}

		return $result;


	}

	/**
	* Returns account information by id
	* @param integer id
	* @return array with data or (bool) false if there is an error
	*/
	public function get_account_data($id){
		$sql = "SELECT * FROM `accounts` WHERE `id`=$id LIMIT 1";
		return $this->dbh->row($sql);
	}

	//returns array with list of accounts
	public function get_accounts(){
		$sql = "SELECT * FROM `accounts` LIMIT 1000";
		return $this->dbh->query($sql);
	}

	/**
	* Displaying of the main info - Campaigns, groups with small display, status of campaigns, number of groups and phrases
	* @param array $account_data account data
	* @param string $url request address without request object
	* @return array with account campaign 
	*/ 
	public function get_direct_data($account_data = array(), $url = 'https://api.direct.yandex.com/json/v5'){
		//verifying data
		if(!$account_data || count($account_data) == 0)return false;
		if(!isset($account_data['login']))exit(__METHOD__ . ': Не передан логин аккаунта (account_data[login])');
		if(!isset($account_data['auth_token']))exit(__METHOD__ . ': Не передан token аккаунта (account_data[auth_token])');
		$clientLogin = $account_data['login'];//лyandex account login
		$token = $account_data['auth_token'];//oauth token

		//custom function for sorting an array by the properties of objects inside an array (an array of campaigns / campaigns - objects)
		function mySort($f1,$f2){
	      if($f1->Name < $f2->Name) return -1;
	      elseif($f1->Name > $f2->Name) return 1;
	      else return 0;
	   	}
		// campaign
		// Parameters of request to the Yandex direct API server
		$params = array(
		   'method' => 'get',                                 // Campaigns service method used
		   'params' => array(
		      'SelectionCriteria' => (object) array(),        // Campaign selection criteria. Must be empty for all campaigns
		      'FieldNames' => array('Id', 'Name', 'Status', 'StatusClarification')             // Names of parameters to get
		   )
		);
		$campaigns = $this->get_response($url . '/campaigns', $clientLogin, $token, $params);
		if($campaigns){
			$my_campaigns = $campaigns->result->Campaigns;
		}else{
			return false;
		}

		//groups and phrases
		foreach ($my_campaigns as $key => &$cmp) {
			$archived_campaigns = array();
			$num_of_groups = 0;//counting groups
			$num_of_keywords = 0; // counting phrases

			//groups
			$params = array(
			   'method' => 'get',                                 
			   'params' => array(
			      'SelectionCriteria' => (object) array(
			      			"CampaignIds" => array($cmp->Id)
			      		),  
			      'FieldNames' => array('Id', 'Name', 'ServingStatus', 'Status') 
			   )
			);
			$groups = $this->get_response($url . '/adgroups', $clientLogin, $token, $params);
			$cmp->AdGroups = $groups->result->AdGroups;
			usort($cmp->AdGroups, 'mySort');
			$cmp->RarelyServedGroups = array(); //groups with few hits

			//phrases 
			$params['params']['SelectionCriteria'] = (object) array("CampaignIds" => array($cmp->Id));
			$params['params']['FieldNames'] = array('Id', 'Keyword');
			$keywords = $this->get_response($url . '/keywords', $clientLogin, $token, $params);
			if(count($keywords->result) == 0 || !isset($keywords->result->Keywords)){
				$cmp->NumOfKeywords = 0;
			}else{
				$cmp->NumOfKeywords = count($keywords->result->Keywords);
			}

			//few hits
			foreach ($cmp->AdGroups as $key => &$group) {
				if($group->ServingStatus == 'RARELY_SERVED')$cmp->RarelyServedGroups[] = $group; 
				$num_of_groups++;

			}
			$cmp->NumOfGroups =  $num_of_groups;

		}

	   	if($my_campaigns){
	   		usort($my_campaigns, 'mySort');
	   		return $my_campaigns;
	   	}else{
	   		return false;
	   	}


	}


	/**
	* Returns lists of groups with less than 3 ads and where the group status is rejected or there is an ad with rejected status in the group
	* @param object $campaign - campaign data
	* @param integer $account_id - Yandex account id
	* @param string $url string with the address of the request to api yandex direct 
	* @return string json string of array with two lists - [0] - groups with less than 3 ads, [1] - groups with rejected status  
	*/
	public function ajax_get_groups_less_3_ads($campaign, $account_id = false, $url='https://api.direct.yandex.com/json/v5'){
		if(!$campaign)exit(__METHOD__ . ": Ошибка: не переданы данные кампании <br>\r\n");
		if(!$account_id)exit(__METHOD__ . ": Ошибка: не передан id аккаунта <br>\r\n");
		$account_data = $this->get_account_data($account_id);
		$clientLogin = $account_data['login'];
		$token = $account_data['auth_token'];
		$groups_with_less_than_3_ads = array();//groups with less than 3 ads
		$rejected_groups = array(); //groups with ads with rejected status
		function mySort($f1,$f2){
	      if($f1->Name < $f2->Name) return -1;
	      elseif($f1->Name > $f2->Name) return 1;
	      else return 0;
	   	}
		$AdGroups = $campaign->AdGroups;

		usort($AdGroups, 'mySort');
		foreach ($AdGroups as $key => &$group) {
			//ads
			$params = array(
			   'method' => 'get',                                 
			   'params' => array(
			      'SelectionCriteria' => (object) array(
			      			"CampaignIds" => array($campaign->Id),
      						"AdGroupIds"  => array($group->Id)
			      		),  
			      'FieldNames' => array('Id', 'Status') 
			   )
			);
			$ads = $this->get_response($url . '/ads', $clientLogin, $token, $params);	
			if(isset($ads)){
				$group->Ads = $ads->result->Ads;
				if(count($group->Ads) < 3)$groups_with_less_than_3_ads[] = $group;

				foreach ($group->Ads as $key => $ad) {
					if($ad->Status == "REJECTED" || $group->Status == "REJECTED"){
						$rejected_groups[] = $group;
						break;
					}
				}
			}
			
		}
		return json_encode(array($groups_with_less_than_3_ads, $rejected_groups));
	}

	/**
	* accepts template and data, returns ready html fragment
	* @param string $name string with tpl template name without file extension
	* @param array $data - view data
	*/
	public function load_view($name = 'index', $data=array()) {
		$full_name = 'views/' . $name . '.tpl';
		 	
		if (file_exists($full_name)) {
	        $view = $full_name;
	    } else {
	    	exit('Error: template file not found: ' . $full_name);
	    }
		
		extract($data);
		
		ob_start();
		include $view;
		return ob_get_clean();
	}

	/**
	* connecting database
	*/
	public function db_connect(){
		require_once('helpers/sqlite_pdo.php');
		$this->dbh = new DB("sqlite:db.sqlite");
		$this->dbh->exec("CREATE TABLE IF NOT EXISTS `accounts` (
			`id` INTEGER PRIMARY KEY AUTOINCREMENT,
		 	`name` VARCHAR, 
			`login` VARCHAR,
			`direct_id` VARCHAR,
			`direct_pwd` VARCHAR,
			`auth_token` VARCHAR,
			`date_add` INTEGER,
			`date_edit` INTEGER
		)");
		return $this->dbh;
	}

	/**
	* closing database connection
	*/
	public function db_disconnect(){
		return $this->dbh = null;
	}


	/**
	* adding data to database
	* @param array $data array with data - [key] is the name of column [value] is the value to be written
	* @param string $table - table name
	* @return last insert row id or (bool) false if there is an error 
	*/
	public function add_data($data=array(), $table){
		if(!is_array($data)){return false;}

		$data['date_add'] = time();
		$data['date_edit'] = 0;

		$into = '';
		$values = '';

		$i = 0;//counter
		foreach($data as $key => $value){
			if(count($data) == 1 || $i == (count($data) - 1)){
				$into .= '`' . $key . '` ';
				$values .= '\'' . $value . '\' ';
			}else{
				$into .= '`' . $key . '`, ';
				$values .= '\'' . $value . '\', ';
			}
			$i++;
		}

		$sql = "INSERT INTO `" . $table . "`(" . $into . ") VALUES (" . $values . ")";
		$d = $this->dbh->exec($sql);
		if($d){
			$last_id = $this->dbh->row("SELECT last_insert_rowid() as 'last_id'");
			return $last_id['last_id'];
		}else{
			return false;
		}
	}

	/**
	* editing account
	* @param integer $id account id
	* @param array $data data
	* @return (bool) true if success and false if there is an error
	*/
	public function edit_account($id, $data){
		$set = '';
		$i = 0;
		foreach($data as $key => $value){
			if(count($data) == 1 || $i == (count($data) - 1)){
				$set .= '`' . $key . '` = \'' . $value . '\' ';
			}else{
				$set .= '`' . $key . '` = \'' . $value . '\', ';
			}
			$i++;
		}
		return $this->dbh->exec("UPDATE `accounts` SET $set WHERE `id` = $id");
		
	}

	/**
	* deleting account
	* @param integer $id account id
	* @return integer true if success and false if there is an error
	*/
	public function delete_account($id = 0){
		if(!$id)return false;
		$sql = "DELETE FROM `accounts` WHERE `id` = $id";
		return $this->dbh->exec($sql);
	}

	/**
	* adds an account token. If the token is transferred manually, it simply writes to the database, otherwise it redirects to receive from the request to api yandex direct
	*/
	public function add_token($id, $token=false){
		if($token != false){
			$sql = "UPDATE `accounts` SET `auth_token` = '$token' WHERE `id` = $id";
			return $this->dbh->exec($sql);
		}else{
			$sql = "SELECT * FROM `accounts` WHERE id = $id LIMIT 1";
			$account = $this->dbh->row($sql);
			if(!$account)return false;
			$url = 'yandex_auth_register.php?id=' . $this->app_id . '&pwd=' . $this->app_pass . '&login=' . $account['login'];
			header("location:$url");
			exit();
		}
		
	}

}

?>
