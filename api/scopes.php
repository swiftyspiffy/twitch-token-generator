<?
function getScopes($dao, $type = "") {
	$scope_data = array();
	if($type != "") {
		switch($type) {
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
	
	return $scope_data;
}

?>