<?php
// Get the confirmation code from GET parameters.
if (!isset($_GET['c']) || empty($_GET['c']))
{
  header('Location: /');
  die();
}
$confirm_code = $_GET['c'];

require_once __DIR__ . '/sessions.php';
$config = require __DIR__ . '/config.php';
$conn = require __DIR__ . '/sql.php';

// Get the e-mail associated with the confirmation code.
$query = sprintf("SELECT email FROM %s WHERE confirm_code='%s'",
  $config['sql_t_confirms'], $confirm_code);
$result = $conn->query($query);
if ($result->num_rows == 0)
{
  header('Location: /');
  die();
}

// Confirm the user.
// * Remove them from the Pending Confirmations table
// * Update row in Users table status to "User"
$user_email = $result->fetch_assoc()['email'];
if ($conn->query(sprintf("DELETE FROM %s WHERE email='%s'",
  $config['sql_t_confirms'], $user_email)) !== TRUE)
{
  die("Failed to remove user from Pending Confirmations table: " . $conn->error);
}

if ($conn->query(sprintf("UPDATE %s SET status='User' WHERE email='%s'",
  $config['sql_t_users'], $user_email)) !== TRUE)
{
  die("Failed to update user status: " . $conn->error);
}

require "_header.php";
?>

<h1>Confirmation</h1>

<p>Your profile (<?php echo $user_email ?>) has successfully been confirmed.</p>

<?php require "_footer.php"; ?>
