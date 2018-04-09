<?
include("../dao.php");
if(!isset($_GET['request']))
	exit(header("https://twitchtokengenerator.com/"));

$dao = new dao();

$request = $_GET['request'];
$id;
if (strpos($request, '/') !== false) {
    $parts = explode("/", $request);
	$id = $parts[0];
	$token = $parts[1];
	if(strlen($parts[1]) < 2) {
		include("request.php");
	} else {
		$refresh = $parts[2];
		include("success.php");
	}
} else {
	$id = $request;
	include("request.php");
}

?>