<?php
require_once __DIR__ . '/../sessions.php';
$config = require __DIR__ . '/../config.php';
$conn = require __DIR__ . '/../sql.php';
require __DIR__  . '/mailer.php';

if (!$g_logged_in)
{
  header('Location: /register.php');
  die();
}

$user_email = $_SESSION['email'];
if (!isset($user_email) || empty($user_email))
{
  die();
}

$mail_failed = "0";

// Get PGP key from database
$query = sprintf("SELECT (pgp_key_fp) FROM %s WHERE email='%s'",
  $config["sql_t_users"], $user_email);
$result = $conn->query($query);
$user_pgp_fp = $result->fetch_assoc()['pgp_key_fp'];
if (empty($user_pgp_fp))
{
  die("No encryption key fingerprint given.");
}

// Get confirmation code from database.
$query = sprintf("SELECT confirm_code FROM %s WHERE email='%s'",
  $config['sql_t_confirms'], $user_email);
$result = $conn->query($query);
$confirm_code = $result->fetch_assoc()['confirm_code'];

try
{
  send_mail($user_email,
    "Registration Confirmation",
    "\r\nPlease follow the link below to confirm your registration.\r\n\r\n" .
    $config['base_url'] . '/confirm.php?c=' . $confirm_code,
    $user_pgp_fp);
}
catch (Exception $e)
{
  $mail_failed = "1";
  die($e);
}

$manual_redirect = isset($_GET['redir']) && !empty($_GET['redir']);
if ($manual_redirect == "my_profile")
{
  header('Location: /my_profile.php');
  die();
}

header('Location: /register_completion.php?failed=' . $mail_failed);
die();
?>
