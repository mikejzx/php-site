<?php
require_once "sessions.php";

$config = require 'config.php';
if ($config['debug'])
{
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Forum</title>

    <style>
      html {
        background: #202020;
        padding: 0;
        background: linear-gradient(#161c11, #1e4c1a) no-repeat fixed;
      }
      body {
        color: #fff;
        padding: 24px;
        margin: 0;
      }
      h1, h2, h3, h4, h5, h6 {
        color: #fc0;
      }
      a {
        color: #48f;
      }
      table, tr, td {
        border: 1px solid #fff;
      }
      .errmsg {
        color: #f44;
      }
    </style>
  </head>
  <body>
    <h1>Site</h1>

    <!-- Main navbar -->
    <header>
      <a href="/">Home</a>

      <?php if (!$g_logged_in) { ?>
        <a href="/login.php">Login</a>
        <a href="/register.php">Register</a>
      <?php } else { ?>
        Logged in as <a href="/profile.php"><?php echo $_SESSION["username"]; ?></a>
        <a href="/logoff.php">Log off</a>
        <a href="/my_profile.php">Profile Options</a>
      <?php } ?>

      <a href="/users.php">Users</a>

    </header>
    <hr>
