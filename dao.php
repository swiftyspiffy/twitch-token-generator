<?
include("config.php");

class dao {
	private $conn;
	
	function __construct() {
		$this->conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	function insertButtonMetrics($btnId, $ip) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_BUTTON_METRICS."` (`btn_id`, `ip`, `timestamp`) VALUES (?, ?, ?);");
		$statement->execute(array($btnId, $ip, time()));
	}
	
	function insertMissingSecurityCode($ip, $useragent) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_MISSING_SECURITY_CODE."` (`ip`, `user_agent`, `utc`) VALUES (?, ?, ?);");
		$statement->execute(array($ip, $useragent, time()));
	}
	
	function insertRefreshRequest($success, $username, $ip) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_REFRESH_REQUESTS."` (`success`, `username`, `ip`) VALUES (?, ?, ?);");
		$statement->execute(array($success, $username, $ip));
	}
	
	function insertRevokeRequest($success, $ip) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_REVOKE_REQUESTS."` (`success`, `ip`, `utc`) VALUES (?, ?, ?);");
		$statement->execute(array($success, $ip, time()));
	}
	
	function getSpamRules() {
	    $rules = array();
	    
	    $statement = $this->conn->prepare("SELECT * FROM `".TABLE_BANNED."`");
		$statement->execute();
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			array_push($rules, array('id' => $row->id, 'ip' => $row->ip, 'scopes' => $row->scope, 'country' => $row->country, 'username' => $row->username, 'useragent' => $row->useragent));
		}
		
		return $rules;
	}
	
	function insertForgotLog($username, $ip) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_FORGOT."` (`username`, `ip`, `timestamp`) VALUES (?, ?, ?);");
		$statement->execute(array($username, $ip, time()));
	}
	
	function deleteRecaptchaListing($id) {
		$statement = $this->conn->prepare("DELETE FROM `".TABLE_RECAPTCHA."` WHERE `identifier` = ?");
		$statement->execute(array($id));
	}
	
	function getRecaptchaListing($id) {
		$statement = $this->conn->prepare("SELECT * FROM `".TABLE_RECAPTCHA."` WHERE `identifier` LIKE ?");
		$statement->execute(array($id));
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			return array('found' => true, 'access' => $row->access, 'refresh' => $row->refresh);
		}
		return array('found' => false);
	}
	
	function insertRecaptchaListing($id, $access, $refresh, $username) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_RECAPTCHA."` (`identifier`, `access`, `refresh`, `timestamp`, `username`) VALUES (?, ?, ?, ?, ?);");
		$statement->execute(array($id, $access, $refresh, time(), $username));
	}
	
	function updateAPIListing($unique, $token, $refresh, $username, $userid) {
		$statement = $this->conn->prepare("UPDATE `".TABLE_API."` SET  `status` =  ?, `token` = ?, `refresh` = ?, `username` = ?, `user_id` = ?, `updated_at` = ? WHERE `unique_string` = ?;");
		$statement->execute(array("1", $token, $refresh, $username, $userid, time(), $unique));
	}
	
	function expireAPI($id) {
		$statement = $this->conn->prepare("UPDATE `".TABLE_API."` SET `status` =  '2' WHERE `".TABLE_API."`.`unique_string` = ?;");
		$statement->execute(array($id));
	}
	
	function getAPISuccessStatus($id) {
		$statement = $this->conn->prepare("SELECT status, title, scopes FROM `".TABLE_API."` WHERE `unique_string` = ?");
		$statement->execute(array($id));
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			if($row->status != "0") {
                return array('available' => false);
            } else {
                return array('available' => true, 'title' => $row->title, 'scopes' => $row->scopes, 'redirect_url' => $row->redirect_url);
            }
		}
		return array('available' => false);
	}

	function getAPIStatus($id, $expireAfterHours = 24) {
		$statement = $this->conn->prepare("SELECT status, scopes, token, refresh, username, user_id, updated_at FROM `".TABLE_API."` WHERE `unique_string` = ?");
		$statement->execute(array($id));
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			if($row->status != "1") {
                return array('status' => $row->status);
            } else {
				$updated_at = $row->updated_at;
				if(($expireAfterHours * 3600) < (time() - $updated_at)) {
					return array('status' => "3");
				} else {
					$scopes = $row->scopes;
					$scopesArr = explode(' ', $scopes);
					return array('status' => "1", 'scopes' => $scopesArr, 'token' => $row->token, 'refresh' => $row->refresh, 'username' => $row->username, 'user_id' => $row->user_id);
				}
            }
		}
		return array();
	}
	
	function insertAPI($unique, $title, $scopes, $ip = "not_set", $redirect = "", $recaptcha = "0") {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_API."` (`unique_string`, `title`, `scopes`, `status`, `token`, `username`, `created_at`, `updated_at`, `creation_ip`, `redirect_url`, `recaptcha_status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
		$statement->execute(array($unique, $title, $scopes, "0", "", "", time(), "0", $ip, $redirect, $recaptcha));
	}
	
	function insertQuickLink($key, $scopes, $auth) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_QUICK_LINKS."` (`key`, `scopes`, `auth`, `timestamp`, `clicks`) VALUES (?, ?, ?, ?, ?);");
		$statement->execute(array($key, $scopes, $auth, time(), "0"));
	}
	
	function insertReferrer($referrer) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_REFERRER."` (`ip`, `referrer`, `utc`) VALUES (?, ?, ?);");
		$statement->execute(array($_SERVER['REMOTE_ADDR'], $referrer, time()));
	}
	
	function getAPIData($id) {
		$statement = $this->conn->prepare("SELECT unique_string, scopes, status, title, recaptcha_status FROM `".TABLE_API."` WHERE `unique_string` = ? ;");
		$statement->execute(array($id));
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			if($row->status != "0") {
				return array('error' => 1);
			} else {
				return array('error' => 0, 'unique' => $row->unique_string, 'scopes' => $row->scopes, 'title' => $row->title);
			}
		}
		return array('error' => 2);
	}
	
	function getQuickLink($key) {
		$statement = $this->conn->prepare("SELECT * FROM  `".TABLE_QUICK_LINKS."` WHERE `key` = ?");
		$statement->execute(array($key));
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			return array('scopes' => explode(' ', $row->scopes), 'auth' => $row->auth);
		}
		return null;
	}
	
	function updateClickCount($key) {
		$statement = $this->conn->prepare("UPDATE  `".TABLE_QUICK_LINKS."` SET  `clicks` =  `clicks` + 1 WHERE  `key` = ?;");
		$statement->execute(array($key));
	}
	
	function insertRequest($unique, $scopes, $name, $email) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_REQUESTS."` (`unique_string`, `scopes`, `requester_name`, `requester_email`, `enabled`, `timestamp`) VALUES (?, ?, ?, ?, ?, ?);");
		$statement->execute(array($unique, $scopes, $name, $email, '1', time()));
	}
	
	function getUsername($token) {
		if($token == null || strlen($token) < 3)
			return null;
		$usernameResult = file_get_contents("https://api.twitch.tv/kraken?oauth_token=" . $token."&api_version=5");
		$json_decoded_usernameResult = json_decode($usernameResult, true);
		return $json_decoded_usernameResult['token']['user_name'];
	}
	
	function getUserId($name, $token) {
		$useridResult = file_get_contents("https://api.twitch.tv/kraken/users/".$name."?oauth_token=".$token);
		$json_decoded_useridResult = json_decode($useridResult, true);
		return $json_decoded_useridResult['_id'];
	}
	
	function getUserData($name, $token) {
		if($name == "[Not set]")
			return array('userid' => "-1", 'followers' => "-1", 'views' => "-1", 'partner' => false);
		
		$opts = array(
			'http'=>array(
				'method'=>"GET",
				'header'=>"Client-ID: ".FRONTEND_CLIENT_ID."\r\n" .
					"Authorization: OAuth ".$token."\r\n" .
					"Accept: application/vnd.twitchtv.v5+json\r\n"
			)
		);
		$context = stream_context_create($opts);

		$data = file_get_contents("https://api.twitch.tv/kraken", false, $context);
		$res = json_decode($data, true);
		$userid = $res['token']['user_id'];
		
		$data = file_get_contents("https://api.twitch.tv/kraken/channels/".$userid, false, $context);
		$this->dumpRequest($name."(".$userid.")", $data);
		$res = json_decode($data, true); 
		return array('userid' => $userid, 'followers' => $res['followers'], 'views' => $res['views'], 'partner' => $res['partner'], 'logo' => $res['logo']);
	}
	
	function getUserViewCount($name) {
		if($name == "[Not set]")
			return array('userid' => "-1", 'followers' => "-1", 'views' => "-1", 'partner' => false);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.twitch.tv/helix/users?login=".$name);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Accept: application/vnd.twitchtv.v5+json',
			'Client-ID: '.FRONTEND_CLIENT_ID
		));
		
		$result = curl_exec($ch);
		curl_close();
		
		$json = json_decode($result, true);
		return $json['data'][0]['view_count'];
	}
	
	function disableRequest($id) {
		$statement = $this->conn->prepare("UPDATE `".TABLE_REQUESTS."` SET  `enabled` =  '0' WHERE `unique_string` = ?");
		$statement->execute(array($id));
	}
	
	function getRequestDetails($id) {
		$statement = $this->conn->prepare("SELECT enabled, scopes, requester_name, requester_email FROM `".TABLE_REQUESTS."` WHERE `unique_string` LIKE ?");
		$statement->execute(array($id));
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			$scopes = array();
			if (strpos($row->scopes, '+') !== false)
				$scopes = explode("+", $row->scopes);
			else
				array_push($scopes, $row->scopes);
			return array("enabled" => $row->enabled, "scopes_str" => $row->scopes, "scopes" => $scopes, "requester_name" => $row->requester_name, "requester_email" => $row->requester_email);
			
		}
		return array();
	}
	
	function getScopes() {
		$res = array();
		
		$statement = $this->conn->prepare("SELECT id, api_set, scope, description FROM `".TABLE_SCOPES."`;");
		$statement->execute();
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			if(!array_key_exists($row->api_set, $res))
				$res[$row->api_set] = array();
			array_push($res[$row->api_set], array('id' => $row->id, 'scope' => $row->scope, 'desc' => $row->description));
		}
		
		return $res;
	}
	
	function getAllScopes() {
		$res = array();
		
		$statement = $this->conn->prepare("SELECT id, scope, description FROM `".TABLE_SCOPES."`;");
		$statement->execute();
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			$res[$row->scope] = array('id' => $row->id, 'scope' => $row->scope, 'desc' => $row->description);
		}
		
		return $res;
	}
	
	function getRawScopes() {
		$res = array();
		
		$statement = $this->conn->prepare("SELECT id, scope, description FROM `".TABLE_SCOPES."`;");
		$statement->execute();
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			array_push($res, array('id' => $row->id, 'scope' => $row->scope));
		}
		
		return $res;
	}
	
	function getStats() {
		$res = array();
		
		$statement = $this->conn->prepare("SELECT scopes, country FROM ".TABLE_DATA);
		$statement->execute();
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			array_push($res, array('scopes' => $row->scopes, 'country' => $row->country));
		}
		return $res;
	}
	
	function logMetadata($username = "", $userid = -1, $followers = -1, $views = -1, $partner = -1) {
		if($userid == null)
			$userid = -1;
		if($followers == null)
			$followers = -1;
		if($views == null)
			$views = -1;
		if($partner == null)
			$partner = -1;
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_META."` (`username`, `userid`, `followers`, `views`, `partner`) VALUES (?,?,?,?,?);");
		$statement->execute(array($username, $userid, $followers, $views, $partner));
	}
	
	function getCountry($ip) {
		try {
			$ctx = stream_context_create(array('http'=>
				array(
					'timeout' => 2,
				)
			));
			$cnts = file_get_contents("https://api.ipgeolocation.io/ipgeo?apiKey=".GEOIP_API_KEY."&ip=".$ip, false, $ctx);
			$json = json_decode($cnts, true);
			$country = $json['country_name'];
			if(strlen($country) == 0)
				return "not_set";
			return $country;
		} catch(Exception $e) {
			$this->insertError("dao.php", "getCountry", "failed to ip details on: ".$ip);
			return "not_set";
		}
	}
	
	// this is run on an hourly cronjob
	function clearRecaptchaTempTable() {
		$cur = time();
		$hourAgo = $cur - 3600;
		$statement = $this->conn->prepare("DELETE FROM `".TABLE_RECAPTCHA."` WHERE `timestamp` <= ?");
		$statement->execute(array($hourAgo));
	}
	
	function revokeToken($accessToken) {
		$url = 'https://id.twitch.tv/oauth2/revoke?client_id='.FRONTEND_CLIENT_ID.'&token='.$accessToken;
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		$data = curl_exec($curl);
		$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		return $statusCode == 200;
	}
	
	function insertError($page, $name, $description) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_ERRORS."` (`page`, `name`, `description`) VALUES (?, ?, ?);");
		$statement->execute(array($page, $name, $description));
	}
	
	function insertStat($set, $name, $value) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_STATS."` (`set_name`, `name`, `value`) VALUES (?, ?, ?);");
		$statement->execute(array($set, $name, $value));
	}
	
	function getFrontendUsage() {
	    $results = array();
	    
	    $statement = $this->conn->prepare("SELECT COUNT(1) AS amount FROM ".TABLE_DATA);
		$statement->execute();
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			return $row->amount;
		}
		
		return 0;
	}
	
	function getWaitingTexts() {
		$results = array();
	    
	    $statement = $this->conn->prepare("SELECT text FROM ".TABLE_WAITING_TEXTS);
		$statement->execute();
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			array_push($results, $row->text);
		}
		
		return $results;
	}
	
	function insertRecaptchaCompletionListing($unique_string = "", $userid = "", $username = "") {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_RECAPTCHA_COMPLETION."` (`unique_string`, `userid`, `username`, `created_at`, `completed`) VALUES (?, ?, ?, ?, ?);");
		$statement->execute(array($unique_string, $userid, $username, date(DATE_ATOM), '0'));
	}
	
	function finishRecaptchaCompletionListing($unique_string) {
		$statement = $this->conn->prepare("UPDATE `".TABLE_RECAPTCHA_COMPLETION."` SET `completed` = ? WHERE `".TABLE_RECAPTCHA_COMPLETION."`.`unique_string` = ?");
		$statement->execute(array("1", $unique_string));
	}
	
	function getApiRecaptchaStatus($id) {
		$statement = $this->conn->prepare("SELECT title, token, scopes, recaptcha_status FROM `".TABLE_API."` WHERE `unique_string` = ? ;");
		$statement->execute(array($id));
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			return array('error' => 0, 'title' => $row->title, 'token' => $row->token, 'scopes' => $row->scopes, 'recaptcha_status' => $row->recaptcha_status);
		}
		return array('error' => 1);
	}
	
	function updateApiRecaptchaStatus($identifier, $status) {
		$statement = $this->conn->prepare("UPDATE `".TABLE_API."` SET `recaptcha_status` = ? WHERE `".TABLE_API."`.`unique_string` = ?");
		$statement->execute(array($status, $identifier));
	}
	
	function dumpRequest($username = "", $data = "") {
		if($username == null || $username == "")
			$username = "not_set";
		if($data == null || $data == "")
			$data = "not_set";
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_REQUEST_DEBUG."` (`username`, `data`, `utc`) VALUES (?, ?, ?);");
		$statement->execute(array($username, $data, time()));
	}
}


?>