<?php
require_once "sessions.php";
require "_header.php";

if (!$g_logged_in)
{
  header("Location: /login.php");
  die();
}

$user_email = $_SESSION["email"];
$user_name = $_SESSION["username"];
?>

<h2>Delete Account</h2>

<p>Fill out the information below to delete your account. Once deleted, your account cannot be recovered.</p>

<form action="api/profile_delete.php" method="POST">
  <div>
    <label for="mail">E-mail:</label>
    <input type="text" id="mail" name="mail" required/>
  </div>

  <div>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required/>
  </div>

  <input type="submit" value="Delete my account"/>
</form>

<?php require "_footer.php"; ?>
