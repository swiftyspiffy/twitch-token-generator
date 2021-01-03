<?
include("../dao.php");
header('Content-Type: application/json');

if($scopes == '')
	exit(json_encode(array('success' => false, 'error' => 30, 'message' => 'No scopes provided.')));

$dao = new dao();
$raw = $dao->getRawScopes();
if(!validateScopes($scopes, $raw))
	exit(json_encode(array('success' => false, 'error' => 31, 'message' => 'Provided scopes are invalid.')));

$ran = generateRandomString();

$dao->insertQuickLink($ran, implode(' ', getScopesFromIds(explode(' ', $scopes), $raw)), $auth);

exit(json_encode(array('success' => true, 'message' => 'https://twitchtokengenerator.com/quick/'.$ran)));

function validateScopes($scopes, $validScopes) {
	$scopes = explode(' ', $scopes);
	$validScopeNames = getValidScopes($validScopes);
	$scopes = getScopesFromIds($scopes, $validScopes);
    $checkScopes = array();
    if (strpos($scopes, ' ') !== false)
        $checkScopes = explode(" ", $scopes);
    else
        array_push($checkScopes, $scopes);

    foreach($checkScopes as $scope) {
        if(!in_array($scope, $validScopeNames))
            return false;
    }

    return true;
}

function getScopesFromIds($scopes, $rawScopes) {
	$scopeNames = array();
	foreach($rawScopes as $rawScope) {
		if(in_array($rawScope['id'], $scopes)) {
			array_push($scopeNames, $rawScope['scope']);
		}
	}
	return $scopeNames;
}

function getValidScopes($rawScopes) {
	$validScopes = array();
	foreach($rawScopes as $rawScope) {
		array_push($validScopes, $rawScope['scope']);
	}
	return $validScopes;
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