<?php
require_once "sessions.php";
require "_header.php";

if (!$g_logged_in)
{
  header("Location: /login.php");
  die();
}

// Get some info and change page based on it.
$config = require 'config.php';
$conn = require 'sql.php';
$query = sprintf("SELECT (status) from %s WHERE email='%s'",
  $config["sql_t_users"], $_SESSION["email"]);
$result = $conn->query($query);
$row = $result->fetch_assoc();
$profile_verified = $row["status"] != "Unverified";

?>

<h2>Profile</h2>

<?php if (!$profile_verified) { ?>
<h3>Verify profile</h3>

<p>
Your profile is still unverified. Please use the link in the confirmation e-mail to verify it.
</p>

<p>
  <a href="/api/send_confirmation.php?redir=my_profile">Re-send confirmation e-mail</a>
</p>

<?php } ?>

<h3>Manage profile</h3>

<a href="/delete.php">Delete my account</a>

<?php require "_footer.php"; ?>
