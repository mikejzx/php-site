<?php
session_start();
$g_logged_in = session_id() !== '' && isset($_SESSION["username"]);
?>

