<?php
error_reporting(E_ERROR | E_PARSE);
header('Content-Type: application/json');
$scopes = strtolower($scopes);
if(!validateScopes($scopes, $dao->getRawScopes()))
    exit(json_encode(array("success" => false, 'error' => 13, "message" => "invalid scopes")));
if(strlen($title) > 500)
    exit(json_encode(array("success" => false, "error" => 14, "message" => "invalid title")));

$unique = randStrGen(20);
$dao->insertAPI($unique, $title, $scopes, $_SERVER['REMOTE_ADDR']);

exit(json_encode(array("success" => true, 'id' => $unique, "message" => "https://twitchtokengenerator.com/api/".$unique)));

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