<?
include("../dao.php");

$dao = new dao();
$stats = $dao->getStats();

header('Content-Type: application/json');
echo json_encode(buildStats($stats));

function buildStats($stats) {
    $results = array();
    $rowNames = array();
    $countryResults = array();
    $countryNames = array();

	foreach($stats as $stat) {
		if(empty($stat['scopes'])) {
			if(array_key_exists("", $results)) {
				$results[""]++;
			} else {
				$results[""] = 1;
			}
		} else {
			$country = $stat['country'];
			if (!empty($country) && $country != "not_set") {
				if(in_array($country, $countryNames)) {
					$countryResults[$country]++;
				} else {
					array_push($countryNames, $country);
					$countryResults[$country] = 1;
				}
			}
			$scopes = explode(',', $stat['scopes']);
			foreach ($scopes as $scope) {
				if (array_key_exists($scope, $results)) {
					$results[$scope]++;
				} else {
					$results[$scope] = 1;
					array_push($rowNames, $scope);
				}
			}
		}	
	}

    return array('scopes' => $rowNames, 'data' => array_values($results), 'country_names' => $countryNames, 'country_results' => array_values($countryResults));
}

?>