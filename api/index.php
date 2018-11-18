<?php
include("../dao.php");

header("Access-Control-Allow-Origin: *");
if(!isset($_GET['id'])) {
    header('Content-Type: application/json');
    exit(json_encode(array('success' => false, 'error' => 5, 'message' => 'No action provided.')));
}

$dao = new dao();

$action;
$args = array();
if (strpos($_GET['id'], '/') !== false) {
    $parts = explode('/', $_GET['id']);
    $action = $parts[0];
    $args = array_slice($parts, 1);
} else {
    $action = $_GET['id'];
}

switch($action) {
	case "waiting_texts":
		$results = $dao->getWaitingTexts();
		header('Content-Type: application/json');
        exit(json_encode(array('success' => true, 'texts' => $results)));
	case "scopes":
		include("scopes.php");
		$type = "";
		if(count($args) == 1) {
			$type = $args[0];
		} else if(count($args) > 1) {
			header('Content-Type: application/json');
            exit(json_encode(array('success' => false, 'error' => 54, 'message' => 'One optional argument possible: scopes type')));
		}
	
		$results = getScopes($dao, $type);
		header('Content-Type: application/json');
		exit(json_encode($results));
	case "forgot":
		include("forgot.php");
		if(count($args) != 1) {
			header('Content-Type: application/json');
            exit(json_encode(array('success' => false, 'error' => 44, 'message' => 'Only one argument is to be provided: oauth access token')));
		}
		$results = forgotToken($args[0]);
		
		header('Content-Type: application/json');
		exit(json_encode($results));
	case "revoke":
		if(count($args) != 1) {
			header('Content-Type: application/json');
            exit(json_encode(array('success' => false, 'error' => 44, 'message' => 'Only one argument is to be provided: oauth access token')));
		}
		$result = $dao->revokeToken($args[0]);
		if($result) {
			header('Content-Type: application/json');
            exit(json_encode(array('success' => true)));
		} else {
			header('Content-Type: application/json');
            exit(json_encode(array('success' => false, 'error' => 45, 'message' => 'Invalid oauth access token provided.')));
		}
    case "create":
        if(count($args) != 2) {
            header('Content-Type: application/json');
            exit(json_encode(array('success' => false, 'error' => 14, 'message' => 'Only two arguments are to be provided: title (base64encoded), scopes (with + as delimeter)')));
        }
        $title = base64_decode($args[0]);
        $scopes = $args[1];
        include("create.php");
        break;
    case "status":
        $id = $args[0];
        include("status.php");
        break;
    case "success":
        include("success.php");
        break;
    case "refresh":
	include("refresh.php");
	break;
    default:
        $resp = $dao->getAPIData($action);
        switch($resp['error']) {
            case 0:
                $scopes = str_replace(" ", "+", $resp['scopes']);
                $unique = $action;
                $state = base64_encode(json_encode(array('action' => 'api', 'id' => $unique)));
                $url = "https://api.twitch.tv/kraken/oauth2/authorize?response_type=code&client_id=zkxgn9qm9y3kzrb1p0px68qa69t3ae&redirect_uri=https://twitchtokengenerator.com/api/success&scope=".$scopes."&state=".$state."&force_verify=true";
                exit(header("Location: ".$url));
                break;
            case 1:
                include("used.php");
                break;
            case 2:
                include("notfound.php");
                break;
        }
        break;
}

?>