<?php

function register_validate(&$err_msgs, $user_email, $user_name,
  $user_pass, $user_pgp, &$o_pgp_fingerprint)
{
  $config = require "config.php";
  $did_error = false;

  // Check if e-mail is valid
  if (empty($user_email) || !filter_var($user_email, FILTER_VALIDATE_EMAIL))
  {
    $err_msgs["email"] = "Please enter a valid e-mail address.";
    $did_error = true;
  }
  else if (strlen($user_email) >= 32)
  {
    // Make sure e-mail isn't too long.
    $err_msgs["email"] = "E-mail must be less than 32 characters long.";
    $did_error = true;
  }

  // Check if username is valid
  $ulen = strlen($user_name);
  if ($ulen >= 32 || $ulen == 0)
  {
    $err_msgs["user_name"] = "Username must less than 32 characters in length.";
    $did_error = true;
  }

  // Ensure they gave us a password
  $plen = strlen($user_pass);
  if ($plen < 5)
  {
    $err_msgs["password"] = "Password must be at least 5 characters long.";
    $did_error = true;
  } else if ($plen >= 192)
  {
    $err_msgs["password"] = "Password must be less than 192 characters long.";
    $did_error = true;
  }

  // Ensure PGP key is valid.
  // (This env variable must point to a directory
  // which 'http' user can read/write from.)
  putenv('GNUPGHOME=' . $config['gpg_home']);
  $gpg = gnupg_init();
  $key = gnupg_import($gpg, $user_pgp);
  if ($key !== false)
  {
    $o_pgp_fingerprint = $key["fingerprint"];
    $keyinfo = gnupg_keyinfo($gpg, $o_pgp_fingerprint);

    if (count($keyinfo) <= 0)
    {
      $err_msgs["pgp"] = "Unable to read PGP key.";
      $did_error = true;
    }
    else if (!$keyinfo[0]["can_encrypt"])
    {
      $err_msgs["pgp"] = "Cannot encrypt with this key. Please use another one.";
      $did_error = true;
    }
  }
  else
  {
    $err_msgs["pgp"] = "Invalid PGP key.";
    $did_error = true;
  }

  if ($did_error)
  {
    return false;
  }
  return true;
}

function register(&$err_msgs, $user_email, $user_name, $user_pass, $user_pgp_fp)
{
  require_once "sessions.php";
  $conn = require "sql.php";
  $config = require "config.php";

  // Check if email exists in database already.
  $query = sprintf(
    "SELECT (email) FROM %s WHERE email='%s'",
    $config["sql_t_users"], $user_email);
  if ($conn->query($query)->num_rows > 0)
  {
    $err_msgs["email"] = "This e-mail has already been registered. Try another one.";
    return false;
  }

  // Create the salted/peppered password
  $pw_peppered = hash_hmac("sha256", $user_pass, $config["pepper"]);
  $user_hash = password_hash($pw_peppered, PASSWORD_ARGON2I);

  // Insert into Confirmations table
  $user_confirmcode = hash("sha256", $user_email . rand() . time());
  $query = sprintf(
    "INSERT INTO %s (email, confirm_code) VALUES ('%s', '%s')",
    $config["sql_t_confirms"], $user_email, $user_confirmcode
  );
  if ($conn->query($query) !== TRUE)
  {
    $err_msgs["db"] = "An error occurred: " . $conn->error;
    $conn->close();
    return false;
  }

  // Insert into Users table
  $query = sprintf(
    "INSERT INTO %s (email, username, pw_hash, pgp_key_fp, status) VALUES ('%s', '%s', '%s', '%s', '%s')",
    $config["sql_t_users"], $user_email, $user_name, $user_hash, $user_pgp_fp, "Unverified");
  if ($conn->query($query) !== TRUE)
  {
    $err_msgs["db"] = "An error occurred: " . $conn->error;
    $conn->close();
    return false;
  }

  $conn->close();
  return true;
}
?>
