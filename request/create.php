<?
include("../dao.php");
error_reporting(E_ERROR | E_PARSE);

if(!isset($_POST['scopes']))
	exit(json_encode(array("success" => false, "message" => "scopes missing")));
$dao = new dao();
$scopes = strtolower($_POST['scopes']);
if(!validateScopes($scopes, $dao->getAllScopes()))
	exit(json_encode(array("success" => false, "message" => "invalid scopes")));
if(!isset($_POST['my_name']))
	exit(json_encode(array("success" => false, "message" => "invalid name")));
if(!isset($_POST['my_email']) || !filter_var($_POST['my_email'], FILTER_VALIDATE_EMAIL))
	exit(json_encode(array("success" => false, "message" => "invalid email")));

$unique = randStrGen(7);
$dao->insertRequest($unique, $scopes, $_POST['my_name'], $_POST['my_email']);

exit(json_encode(array("success" => true, "message" => "https://twitchtokengenerator.com/request/".$unique)));

function validateScopes($scopes, $allValidScopes) {
    $checkScopes = array();
    if (strpos($scopes, '+') !== false)
        $checkScopes = explode("+", $scopes);
    else
        array_push($checkScopes, $scopes);

    foreach($checkScopes as $scope) {
		if(!array_key_exists($scope, $allValidScopes))
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