<?php
error_reporting(E_ERROR | E_PARSE);
header('Content-Type: application/json');
$scopes = strtolower($scopes);
if(!validateScopes($scopes))
    exit(json_encode(array("success" => false, 'error' => 13, "message" => "invalid scopes")));
if(strlen($title) > 500)
    exit(json_encode(array("success" => false, "error" => 14, "message" => "invalid title")));

$unique = randStrGen(20);
insertAPI($unique, $title, $scopes);

exit(json_encode(array("success" => true, 'id' => $unique, "message" => "https://twitchtokengenerator.com/api/".$unique)));

function insertAPI($unique, $title, $scopes) {
    mysql_connect(DB_HOST, DB_USER, DB_PASS) or
    die("Could not connect: " . mysql_error());
    mysql_select_db(DB_ANONDATA);

    $ret = array();

    $ab = mysql_query("INSERT INTO `DB_ANONDATA`.`API` (`id`, `unique_string`, `title`, `scopes`, `status`, `token`, `username`) VALUES (NULL, '".$unique."', '".mysql_real_escape_string($title)."', '".mysql_real_escape_string($scopes)."', '0', '', '');") or trigger_error(mysql_error());
}

function validateScopes($scopes) {
    $valid_scopes = array("user_read", "user_blocks_edit", "user_blocks_read", "user_follows_edit", "channel_read", "channel_editor", "channel_commercial", "channel_stream", "channel_subscriptions", "user_subscriptions", "channel_check_subscription", "chat_login", "channel_feed_read", "channel_feed_edit", "collections_edit", "communities_edit", "communities_moderate", "viewing_activity_read");

    $checkScopes = array();
    if (strpos($scopes, ' ') !== false)
        $checkScopes = explode(" ", $scopes);
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