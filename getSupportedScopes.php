<?
include("dao.php");

$dao = new dao();
$scope_data = array();

$scope_data = array();
if(isset($_GET['type'])) {
	switch($_GET['type']) {
		case "just_scopes":
			$data = $dao->getScopes();
			foreach($data as $api_set)
				foreach($api_set as $scope)
					array_push($scope_data, $scope['scope']);
			break;
		default:
			$scope_data = $dao->getScopes();
			break;
	}
} else {
	$scope_data = $dao->getScopes();
}

header('Content-Type: application/json');
exit(json_encode($scope_data));

?>