<?php
include("../dao.php");

// increment stats
$dao = new dao();
$amount = $dao->getFrontendUsage();
$dao->insertStat("primary_logs", "total", $amount);

// clear oauth/refresh tokens from table after a certain period of time (because of reCaptcha protection)
$dao->clearRecaptchaTempTable();
?>