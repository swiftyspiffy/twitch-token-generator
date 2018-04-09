<?
include("config.php");

class dao {
	private $conn;
	
	/*
	
	Public DB calls
	
	*/
	
	function __construct() {
		$this->conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_ANONDATA, DB_USER, DB_PASS);
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	
	function updateAPIListing($unique, $token, $refresh, $username, $userid) {
		$statement = $this->conn->prepare("UPDATE `".TABLE_API."` SET  `status` =  ?, `token` = ?, `refresh` = ?, `username` = ?, `user_id` = ? WHERE `unique_string` = ?;");
		$statement->execute(array("1", $token, $refresh, $username, $userid, $unique));
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
                return array('available' => true, 'title' => $row->title, 'scopes' => $row->scopes);
            }
		}
		return array('available' => false);
	}
	
	function getAPIStatus($id) {
		$statement = $this->conn->prepare("SELECT status, scopes, token, refresh, username, user_id FROM `".TABLE_API."` WHERE `unique_string` = ?");
		$statement->execute(array($id));
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			if($row->status != "1") {
                return array('status' => $row->status);
            } else {
                $scopes = $row->scopes;
                $scopesArr = explode(' ', $scopes);
                return array('status' => "1", 'scopes' => $scopesArr, 'token' => $row->token, 'refresh' => $row->refresh, 'username' => $row->username, 'user_id' => $row->user_id);
            }
		}
		return array();
	}
	
	function insertAPI($unique, $title, $scopes) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_API."` (`unique_string`, `title`, `scopes`, `status`, `token`, `username`, `timestamp`) VALUES (?, ?, ?, ?, ?, ?, ?);");
		$statement->execute(array($unique, $title, $scopes, "0", "", "", time()));
	}
	
	function insertQuickLink($key, $scopes, $auth) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_QUICK_LINKS."` (`key`, `scopes`, `auth`, `timestamp`, `clicks`) VALUES (?, ?, ?, ?, ?);");
		$statement->execute(array($key, $scopes, $auth, time(), "0"));
	}
	
	function getAPIData($id) {
		$statement = $this->conn->prepare("SELECT unique_string, scopes, status FROM `".TABLE_API."` WHERE `unique_string` = ? ;");
		$statement->execute(array($id));
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			if($row->status != "0") {
				return array('error' => 1);
			} else {
				return array('error' => 0, 'unique' => $row->unique_string, 'scopes' => $row->scopes);
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
		$usernameResult = file_get_contents("https://api.twitch.tv/kraken?oauth_token=" . $token);
		$json_decoded_usernameResult = json_decode($usernameResult, true);
		return $json_decoded_usernameResult['token']['user_name'];
	}
	
	function getUserId($name, $token) {
		$useridResult = file_get_contents("https://api.twitch.tv/kraken/users/".$name."?oauth_token=".$token);
		$json_decoded_useridResult = json_decode($useridResult, true);
		return $json_decoded_useridResult['_id'];
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
		
		$statement = $this->conn->prepare("SELECT api_set, scope, description FROM `".TABLE_SCOPES."`;");
		$statement->execute();
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			if(!array_key_exists($row->api_set, $res))
				$res[$row->api_set] = array();
			array_push($res[$row->api_set], array('scope' => $row->scope, 'desc' => $row->description));
		}
		
		return $res;
	}
	
	function getAllScopes() {
		$res = array();
		
		$statement = $this->conn->prepare("SELECT scope, description FROM `".TABLE_SCOPES."`;");
		$statement->execute();
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			$res[$row->scope] = array('scope' => $row->scope, 'desc' => $row->description);
		}
		
		return $res;
	}
	
	function getRawScopes() {
		$res = array();
		
		$statement = $this->conn->prepare("SELECT scope, description FROM `".TABLE_SCOPES."`;");
		$statement->execute();
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			array_push($res, $row->scope);
		}
		
		return $res;
	}
	
	function getRecentAuth() {
		$statement = $this->conn->prepare("SELECT utc, scopes, country FROM `".TABLE_DATA."` ORDER BY `id` DESC LIMIT 1");
		$statement->execute();
		while($row=$statement->fetch(PDO::FETCH_OBJ)) {
			return array('utc' => $row->utc, 'scopes' => $row->scopes, 'country' => $row->country);
		}
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
	
	/*
	
	Stats DB calls
	
	*/
	
	function logUsage($ip, $scopes, $country) {
		$statement = $this->conn->prepare("INSERT INTO `".TABLE_DATA."` (`ip`, `utc`, `scopes`, `country`) VALUES (?,?,?,?);");
		$statement->execute(array($ip, time(), str_replace(" ", ",", $scopes), $country));
	}
	
	function getCountry($ip) {
		try {
			$cnts = file_get_contents("http://freegeoip.net/json/".$ip);
			$json = json_decode($cnts);
			$country = $json->{'country_name'};
			return $country;
		} catch(Exception $e) {
			return "Unknown";
		}
	}
}


?>