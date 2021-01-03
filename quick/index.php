<?
if(!isset($_GET['id'])) {
    header('Content-Type: application/json');
    exit(json_encode(array('success' => false, 'error' => 5, 'message' => 'No action provided.')));
}

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
	case "create":
		$scopes = $args[0];
		$auth = "auth_stay";
		if($args[1] != null && $args[1] != "")
			$auth = $args[1];
		include("create.php");
		break;
	default:
		include("../dao.php");
		$dao = new dao();
		$parts = $dao->getQuickLink($action);
		if($parts == null) {
			header('Content-Type: application/json');
			exit(json_encode(array('success' => false, 'error' => 39, 'message' => 'Invalid quick link id.')));
		}
		$stored_scopes = $parts['scopes'];
		$auth = $parts['auth'];
		$str = "";
		foreach($stored_scopes as $scope) {
			if($str == "")
				$str = $scope;
			else
				$str .= "+".$scope;
		}
		$dao->updateClickCount($action);
		exit(header("Location: https://twitchtokengenerator.com/?scope=".$str."&auth=".$auth));
		break;
}

?>