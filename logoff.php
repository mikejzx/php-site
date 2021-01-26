<?php
require "api/logoff.php";

logoff();

$g_logged_in = false;

// Redirect to home page
header("Location: /");
die();
?>
