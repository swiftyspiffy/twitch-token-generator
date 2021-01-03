<?

if(isset($_POST['robot_identifier'])) {
	$id = $_POST['robot_identifier'];
	$data = $dao->getApiRecaptchaStatus($id);
	if($data['error'] != "0") {
		header('Content-Type: application/json');
        exit(json_encode(array('success' => false, 'message' => 'Unknown API workflow identifier')));
	}
	
	$captcha = $_POST['g-recaptcha-response'];
	$ip = $_SERVER['REMOTE_ADDR'];

	if(!isValid($captcha, $ip))
		exit(json_encode(array('success' => false, 'message' => "reCaptcha was not valid!")));

	switch($data['recaptcha_status']) {
		case "0":
			if(strlen($data['token']) == 0) {
				// send to twitch to auth
				$url = getTwitchAuthUrl($data['scopes'], $id);
				exit(header("Location: ".$url));
			} else {
				// send to success
				$dao->updateApiRecaptchaStatus($id, "2");
				$title = $data['title'];
				include("success_after_verify.php");
				exit();
			}
		case "1":
			// authing now
			$dao->updateApiRecaptchaStatus($id, "2");
			$url = getTwitchAuthUrl($data['scopes'], $id);
			exit(header("Location: ".$url));
		default:
			header('Content-Type: application/json');
			exit(json_encode(array('success' => false, 'message' => 'Unknown API workflow identifier')));
			break;
	}
} else {
	$data = $dao->getApiRecaptchaStatus($identifier);
	if(data['error'] != "0") {
		header('Content-Type: application/json');
        exit(json_encode(array('success' => false, 'message' => 'Unknown API workflow identifier')));
	}
	if($data['recaptcha_status'] == "0" && strlen($data['token']) == 0) {
		// go to twitch
		$url = getTwitchAuthUrl($data['scopes'], $identifier);
		exit(header("Location: ".$url));
	}
	exit("status: ".$data['recaptcha_status'].", token: '".$data['token']."'");
	if($data['recaptcha_status'] == "2") {
		// go to success page
		$title = $data['title'];
		include("success_after_verify.php");
		exit();
	}
	if($data['recaptcha_status'] == "1" && strlen($data['token']) > 0) {
		header('Content-Type: application/json');
		exit(json_encode(array('success' => false, 'message' => 'Invalid API workflow state')));
	}
}

?>

<style>
.g-recaptcha{
   margin: 15px auto !important;
   width: auto !important;
   height: auto !important;
   text-align: -webkit-center;
   text-align: -moz-center;
   text-align: -o-center;
   text-align: -ms-center;
}
</style>

<script>
function recaptchaSuccess() {
	$("#robot_form").submit();
}
</script>

<title>Twitch Token Generator by swiftyspiffy - Recaptcha</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>

<br>
<div class="container">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title text-center">Twitch Token Generator - Recaptcha</h3>
		</div>
		<div class="panel-body">
			<center>
				<span>Thank you for using TwitchTokenGenerator! Please complete the reCaptcha below!</span>
			</center>
			<form id="robot_form" action="verify" method="post">
				<input type="hidden" id="robot_identifier" name="robot_identifier" value="<? echo $identifier; ?>"></input>
				<div class="g-recaptcha" data-callback="recaptchaSuccess" style="padding-left: 23%" data-sitekey="6LeaCF0UAAAAAMG7-HRJ1Oq_aneLPdQQNN0r9_no"></div>
			</form>
		</div>
	</div>
</div>

<?
function getTwitchAuthUrl($scopes, $id) {
	$url = "https://api.twitch.tv/kraken/oauth2/authorize?response_type=code&client_id=".API_CLIENT_ID."&redirect_uri=https://twitchtokengenerator.com/api/success&scope=".str_replace(" ", "+", $scopes);
	$state = base64_encode(json_encode(array('action' => 'api', 'id' => $id)));;
	
	return $url."&state=".$state."&force_verify=true";
}

function isValid($captcha, $ip) {
	try {

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = ['secret'   => RECAPTCHA_SECRET,
                 'response' => $captcha,
                 'remoteip' => $ip];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data) 
            ]
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result)->success;
    }
    catch (Exception $e) {
		$dao->insertError("internal.php", "isValid", "failed to verify captcha with google");
        return null;
    }
}

?>