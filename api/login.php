<?php
function login($user_email, $user_pass)
{
  require_once __DIR__ . "/../sessions.php";
  $config = require_once __DIR__ . "/../config.php";
  $conn = require_once __DIR__ . "/../sql.php";

  if (strlen($user_email) == 0 || strlen($user_pass) == 0)
  {
    return "Please enter valid credentials.";
  }

  $query = sprintf("SELECT email, username, pw_hash from %s WHERE email='%s'",
    $config["sql_t_users"], $user_email);

  $result = $conn->query($query);
  if ($result->num_rows === 0)
  {
    $conn->close();
    return "Incorrect username or password.";
  }

  // Verify the password
  $pw_peppered = hash_hmac("sha256", $user_pass, $config["pepper"]);
  $row = $result->fetch_assoc();
  if (!password_verify($pw_peppered, $row["pw_hash"]))
  {
    $conn->close();
    return "Incorrect username or password.";
  }

  // Password was correct. Redirect to home and log user in.
  $_SESSION["email"] = $row["email"];
  $_SESSION["username"] = $row["username"];

  $result->free_result();
  $conn->close();

  return "";
}
?>
