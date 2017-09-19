<?
if(!isset($_GET['request']))
	exit(header("https://twitchtokengenerator.com/"));

$request = $_GET['request'];
$id;
if (strpos($request, '/') !== false) {
    $parts = explode("/", $request);
	$id = $parts[0];
	$token = $parts[1];
	if(strlen($parts[1]) < 2) {
		include("request.php");
	} else {
		include("success.php");
	}
} else {
	$id = $request;
	include("request.php");
}

?>