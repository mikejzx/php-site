<?php

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
  $user_email = $_POST["mail"];
  $user_pass = $_POST["password"];

  profile_delete($user_email, $user_pass);

  header("Location: /logoff.php");
  die();
}

function profile_delete($user_email, $user_pass)
{
  require_once __DIR__ . "/../sessions.php";
  require __DIR__ . "/../util.php";
  require __DIR__ . "/login.php";
  require __DIR__ . "/logoff.php";

  $user_email = safe_input($user_email);
  $user_pass = safe_input($user_pass);

  if (login($user_email, $user_pass) != "")
  {
    return false;
  }

  $config = require __DIR__ . "/../config.php";
  $conn = require __DIR__ . "/../sql.php";

  // Delete their PGP key
  $query = sprintf("SELECT pgp_key_fp FROM %s WHERE email='%s'",
    $config["sql_t_users"], $user_email);
  $result = $conn->query($query);
  $row = $result->fetch_assoc();
  putenv('GNUPGHOME=' . $config['gpg_home']);
  $gpg = gnupg_init();
  gnupg_deletekey($gpg, $row["pgp_key_fp"]);

  // Delete from the database.
  $query = sprintf("DELETE FROM %s WHERE email='%s'",
    $config["sql_t_users"], $user_email);
  $result = $conn->query($query);
  $conn->close();

  return true;
}

?>
