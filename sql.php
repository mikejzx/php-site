<?php
$config = require "config.php";
$conn = mysqli_connect(
  $config["sql_server"],
  $config["sql_user"],
  $config["sql_pass"],
  $config["sql_db"]
);

// Check connection
if (!$conn)
{
  die ("Database connection failed: " . $conn->connect_error());
}
return $conn;

?>
