<?php
header('Content-Type: application/json');

// expire api workflows after 24 hours
// TODO: Evalulate if this makes sense (maybe shorten to 12)
$expireAfterHours = 24;
if(!isset($id)) {
    exit(json_encode(array('success' => false, 'error' => 1, 'message' => 'No id provided.')));
}

$resp = $dao->getAPIStatus($id, $expireAfterHours);
if(count($resp) == 0) {
    exit(json_encode(array('success' => false, 'id' => $id, 'error' => 2, 'message' => 'Id not found.')));
} else {
    switch($resp['status']) {
        case "0":
            // user has not authorized using API workflow link
            exit(json_encode(array('success' => false, 'id' => $id, 'error' => 3, 'message' => 'Not authorized yet')));
        case "1":
            // expire API after first access to prevent accidental token leaks, then return authorization data
            $dao->expireAPI($id);
            exit(json_encode(array('success' => true, 'id' => $id, 'scopes' => $resp['scopes'], 'token' => $resp['token'], 'refresh' => $resp['refresh'], 'username' => $resp['username'], 'user_id' => $resp['user_id'], 'client_id' => API_CLIENT_ID)));
        case "2":
            // workflow has already been accessed, reject request
            exit(json_encode(array('success' => false, 'id' => $id, 'error' => 4, 'message' => 'API instance has already expired (already activated). Create a new one.')));
        case "3":
            // expire token after period of time to prevent accidental token leaks
			exit(json_encode(array('success' => false, 'id' => $id, 'error' => 6, 'message' => 'API instance has already expired (hit time limit: \''.$expireAfterHours.'\' hour(s)). Create a new one.')));
        default:
            exit(json_encode(array('success' => false, 'id' => $id, 'error' => 5, 'message' => 'Unknown status: '.$resp['status'])));
    }
}

?>