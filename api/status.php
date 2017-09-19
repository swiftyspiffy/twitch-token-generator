<?php
header('Content-Type: application/json');

if(!isset($id)) {
    exit(json_encode(array('success' => false, 'error' => 1, 'message' => 'No id provided.')));
}

$resp = getStatus($id);
if(count($resp) == 0) {
    exit(json_encode(array('success' => false, 'id' => $id, 'error' => 2, 'message' => 'Id not found.')));
} else {
    switch($resp['status']) {
        case "0":
            exit(json_encode(array('success' => false, 'id' => $id, 'error' => 3, 'message' => 'Not authorized yet')));
        case "1":
            expireAPI($id);
            exit(json_encode(array('success' => true, 'id' => $id, 'scopes' => $resp['scopes'], 'token' => $resp['token'], 'username' => $resp['username'])));
        case "2":
            exit(json_encode(array('success' => false, 'id' => $id, 'error' => 4, 'message' => 'API instance has already expired. Created a new one.')));
        default:
            exit(json_encode(array('success' => false, 'id' => $id, 'error' => 5, 'message' => 'Unknown status.')));
    }
}

function getStatus($id) {
    mysql_connect(DB_HOST, DB_USER, DB_PASS) or
    die("Could not connect: " . mysql_error());
    mysql_select_db("twitgsvi_logs");

    $ab = mysql_query("SELECT * FROM `API`") or trigger_error(mysql_error());
    while ($row = mysql_fetch_array($ab)) {
        if($row['unique_string'] == $id) {
            if($row['status'] != "1") {
                return array('status' => $row['status']);
            } else {
                $scopes = $row['scopes'];
                $scopesArr = explode(' ', $scopes);
                return array('status' => "1", 'scopes' => $scopesArr, 'token' => $row['token'], 'username' => $row['username']);
            }
        }
    }
    return array();
}

function expireAPI($id) {
    mysql_connect(DB_HOST, DB_USER, DB_PASS) or
    die("Could not connect: " . mysql_error());
    mysql_select_db(DB_ANONDATA);
	// id does not need to be validated since getStatus validates it
    $ab = mysql_query("UPDATE  `DB_ANONDATA`.`API` SET  `status` =  '2' WHERE  `api`.`unique_string` ='".$id."';") or trigger_error(mysql_error());
}

?>