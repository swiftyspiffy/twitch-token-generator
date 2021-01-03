<?
include("dao.php");
include("encrypt_decrypt.php");

header('Content-Type: application/json');

if(!isset($_GET['security_code'])) {
    exit(json_encode(array('successful' => false, 'message' => "no security code")));
}
if(!validSecurityCode($_GET['security_code'], $gracePeriodSeconds)) {
    exit(json_encode(array('successful' => false, 'message' => "invalid security code")));
}

if(!isset($_GET['action'])) {
    exit(json_encode(array('successful' => false, 'message' => "no action provided")));
}

$dao = new dao();

switch($_GET['action']) {
    case "button":
        if(!isset($_GET['id'])) {
            exit(json_encode(array('successful' => false, 'message' => "no id provided")));
        }
        $dao->insertButtonMetrics($_GET['id'], $_SERVER['REMOTE_ADDR']);
        exit(json_encode(array('successful' => true, 'message' => "")));
    break;
    default:
        exit(json_encode(array('successful' => false, 'message' => "unknown action: ".$_GET['action'])));
}

?>