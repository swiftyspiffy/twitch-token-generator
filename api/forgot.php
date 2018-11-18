<?
function forgotToken($token) {
	$data = getDetails($token);
	$results = interpret($data);

	$dao = new dao();
	if($results['result'] == "invalid")
		$dao->insertForgotLog("[unknown]", $_SERVER['REMOTE_ADDR']);
	else
		$dao->insertForgotLog($results['data']['username'], $_SERVER['REMOTE_ADDR']);
	
	return $results;
}
	
function interpret($data) {
	$json = json_decode($data, true);
	if(!$json['token']['valid']) {
		return array('result' => 'invalid');
	} else if($json['token']['authorization']['created_at'] == "0001-01-01T00:00:00Z") {
		return array('result' => 'expired', 'data' => array('username' => $json['token']['user_name']));
	} else {
		$scopes = array();
		foreach($json['token']['authorization']['scopes'] as $scope)
			array_push($scopes, $scope);
		return array('result' => 'success', 'data' => array('username' => $json['token']['user_name'], 'userid' => $json['token']['user_id'], 'created_at' => $json['token']['authorization']['created_at'], 'updated_at' => $json['token']['authorization']['updated_at'], 'scopes' => $scopes));
	}
}	

function getDetails($token) {
	$opts = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"Accept: application/vnd.twitchtv.v5+json\r\n" .
				  "Authorization: OAuth ".$token."\r\n" .
				  "Client-ID: ".API_CLIENT_ID."\r\n"
		)
	);

	$context = stream_context_create($opts);

	// Open the file using the HTTP headers set above
	$file = file_get_contents('https://api.twitch.tv/kraken', false, $context);
	return $file;
}

?>