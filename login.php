<?php
require_once __DIR__ . "/sessions.php";
require __DIR__ . "/api/login.php";
require __DIR__ . "/util.php";

// Redirect if already logged in.
if ($g_logged_in)
{
  header("Location: /");
  die();
}

$err_msg = "";
$user_email = "";

// Log user in if they sent POST
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
  $user_email = safe_input($_POST["mail"]);
  $user_pass = safe_input($_POST["password"]);
  $err_msg = login($user_email, $user_pass);

  if (empty($err_msg))
  {
    header("Location: /");
    die();
  }
}

require __DIR__ . "/_header.php";
?>

<h2>Log in</h2>

<form action="login.php" method="POST">
  <div>
    <label for="mail">E-mail:</label>
    <input type="text" id="mail" name="mail" value="<?php echo $user_email ?>" required/>
  </div>

  <div>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required/>
  </div>

  <p class="errmsg"><?php echo $err_msg ?></p>

  <div>
    <input type="submit" value="Log in"/>
  </div>
</form>

<?php require "_footer.php"; ?>
