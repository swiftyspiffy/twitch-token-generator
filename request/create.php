<?
include("..config.php");

if(!isset($_POST['scopes']))
	exit(json_encode(array("success" => false, "message" => "scopes missing")));
$scopes = strtolower($_POST['scopes']);
if(!validateScopes($scopes))
	exit(json_encode(array("success" => false, "message" => "invalid scopes")));
if(!isset($_POST['my_name']))
	exit(json_encode(array("success" => false, "message" => "invalid name")));
if(!isset($_POST['my_email']) || !filter_var($_POST['my_email'], FILTER_VALIDATE_EMAIL))
	exit(json_encode(array("success" => false, "message" => "invalid email")));

$unique = randStrGen(7);
insertRequest($unique, $scopes, $_POST['my_name'], $_POST['my_email']);

exit(json_encode(array("success" => true, "message" => "https://twitchtokengenerator.com/request/".$unique)));

function insertRequest($unique, $scopes, $name, $email) {
	mysql_connect(DB_HOST, DB_USER, DB_PASS) or
		die("Could not connect: " . mysql_error());
	mysql_select_db(DB_ANONDATA);

	$ret = array();
	
	$ab = mysql_query("INSERT INTO `DB_ANONDATA`.`TABLE_REQUESTS` (`id`, `unique_string`, `scopes`, `requester_name`, `requester_email`, `enabled`) VALUES (NULL, '".$unique."', '".mysql_real_escape_string($scopes)."', '".mysql_real_escape_string($name)."', '".mysql_real_escape_string($email)."', '1');") or trigger_error(mysql_error());
}

function validateScopes($scopes) {
	$valid_scopes = array("user_read", "user_blocks_edit", "user_blocks_read", "user_follows_edit", "channel_read", "channel_editor", "channel_commercial", "channel_stream", "channel_subscriptions", "user_subscriptions", "channel_check_subscription", "chat_login", "channel_feed_read", "channel_feed_edit", "collections_edit", "communities_edit", "communities_moderate", "viewing_activity_read");
	
	$checkScopes = array();
	if (strpos($scopes, '+') !== false)
		$checkScopes = explode("+", $scopes);
	else
		array_push($checkScopes, $scopes);
	
	foreach($checkScopes as $scope) {
		if(!in_array($scope, $valid_scopes))
			return false;
	}
	
	return true;
}

function randStrGen($len){
    $result = "";
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $charArray = str_split($chars);
    for($i = 0; $i < $len; $i++){
	    $randItem = array_rand($charArray);
	    $result .= "".$charArray[$randItem];
    }
    return $result;
}
?>