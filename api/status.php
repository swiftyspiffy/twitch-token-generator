<?php
header('Content-Type: application/json');

if(!isset($id)) {
    exit(json_encode(array('success' => false, 'error' => 1, 'message' => 'No id provided.')));
}

$resp = $dao->getAPIStatus($id);
if(count($resp) == 0) {
    exit(json_encode(array('success' => false, 'id' => $id, 'error' => 2, 'message' => 'Id not found.')));
} else {
    switch($resp['status']) {
        case "0":
            exit(json_encode(array('success' => false, 'id' => $id, 'error' => 3, 'message' => 'Not authorized yet')));
        case "1":
            $dao->expireAPI($id);
            exit(json_encode(array('success' => true, 'id' => $id, 'scopes' => $resp['scopes'], 'token' => $resp['token'], 'refresh' => $resp['refresh'], 'username' => $resp['username'], 'user_id' => $resp['user_id'])));
        case "2":
            exit(json_encode(array('success' => false, 'id' => $id, 'error' => 4, 'message' => 'API instance has already expired. Create a new one.')));
        default:
            exit(json_encode(array('success' => false, 'id' => $id, 'error' => 5, 'message' => 'Unknown status: '.$resp['status'])));
    }
}
?>