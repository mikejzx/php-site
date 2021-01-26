<?php
require_once "sessions.php";
require "api/register.php";
require "util.php";

$user_email = "";
$user_name = "";
$user_pgp = "";
$err_msgs = [
  "email" => "",
  "username" => "",
  "password" => "",
  "pgp" => "",
  "db" => "",
];

// Register user if they are posting
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
  $user_email = safe_input($_POST["mail"]);
  $user_pass = safe_input($_POST["password"]);
  $user_name_unsafe = $_POST["username"];
  $user_name = safe_input($user_name_unsafe);
  $user_pgp = trim($_POST["pgp"]);
  $user_pgp_fingerprint = "";

  // Restrict username to safe characters.
  if(!preg_match("/^[\p{L}\p{N}\-\_]+$/u", $user_name_unsafe))
  {
    $err_msgs["username"] = "Invalid username. Can only contain letters, digits, underscores, or hyphens.";
  }
  else if (register_validate($err_msgs, $user_email, $user_name,
    $user_pass, $user_pgp, $user_pgp_fingerprint))
  {
    if (register($err_msgs, $user_email, $user_name, $user_pass, $user_pgp_fingerprint))
    {
      // Log the user in.
      $_SESSION["email"] = $user_email;
      $_SESSION["username"] = $user_name;
      $_SESSION["pgp_fingerprint"] = $user_pgp_fingerprint;

      // Redirect to home page.
      header("Location: /api/send_confirmation.php");
      die();
    }
  }
}

require "_header.php";
?>

<h2>Registration</h2>

<form action="/register.php" method="post">
  <div>
    <label for="mail">E-mail:</label>
    <input type="text" id="mail" name="mail" value="<?php echo $user_email; ?>" required />
    <p class="errmsg"><?php echo $err_msgs["email"]; ?></p>
  </div>

  <div>
    <label for="user">Nickname:</label>
    <input type="text" id="username" name="username" value="<?php echo $user_name; ?>" required />
    <p class="errmsg"><?php echo $err_msgs["username"]; ?></p>
  </div>

  <div>
    <label for="pass">Password:</label>
    <input type="password" id="password" name="password" value="" required />
    <p class="errmsg"><?php echo $err_msgs["password"]; ?></p>
  </div>

  <div>
    <label for="pgp">Public PGP encryption key:</label><br>
    <textarea id="pgp" name="pgp" rows=10 cols=65 required placeholder="-----BEGIN PGP PUBLIC KEY BLOCK-----" ><?php echo $user_pgp ?></textarea>
    <p class="errmsg"><?php echo $err_msgs["pgp"]; ?></p>
  </div>

  <p class="errmsg"><?php echo $err_msgs["db"]; ?></p>

  <div>
    <input type="submit" value="Register"/>
  </div>
</form>

<?php require "_footer.php"; ?>
