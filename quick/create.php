<?
include("../dao.php");
header('Content-Type: application/json');

if($scopes == '')
	exit(json_encode(array('success' => false, 'error' => 30, 'message' => 'No scopes provided.')));

$dao = new dao();
if(!validateScopes($scopes, $dao->getRawScopes()))
	exit(json_encode(array('success' => false, 'error' => 31, 'message' => 'Provided scopes are invalid.')));

$ran = generateRandomString();

$dao->insertQuickLink($ran, $scopes, $auth);

exit(json_encode(array('success' => true, 'message' => 'https://twitchtokengenerator.com/quick/'.$ran)));

function validateScopes($scopes, $validScopes) {
    $checkScopes = array();
    if (strpos($scopes, ' ') !== false)
        $checkScopes = explode(" ", $scopes);
    else
        array_push($checkScopes, $scopes);

    foreach($checkScopes as $scope) {
        if(!in_array($scope, $validScopes))
            return false;
    }

    return true;
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

?>