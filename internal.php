<?
include("dao.php");

header('Content-Type: application/json');

if(!isset($_POST['robot_identifier']))
	exit(json_encode(array('success' => false, 'message' => "Missing robot identifier!")));
if(!isset($_POST['g-recaptcha-response']))
	exit(json_encode(array('success' => false, 'message' => "Missing g recaptcha response!")));

$id = $_POST['robot_identifier'];
$captcha = $_POST['g-recaptcha-response'];
$ip = $_SERVER['REMOTE_ADDR'];

if(!isValid($captcha, $ip))
	exit(json_encode(array('success' => false, 'message' => "reCaptcha was not valid!")));
	
$dao = new dao();
$result = $dao->getRecaptchaListing($id);
$dao->deleteRecaptchaListing($id);

if(!$result['found'])
	exit(json_encode(array('success' => false, 'message' => "Generation data not found on server!")));

exit(json_encode(array('success' => true, 'robot_identifier' => $_POST['robot_identifier'], 'result' => array('access' => $result['access'], 'refresh' => $result['refresh']))));

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