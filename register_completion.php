<?php
require_once 'sessions.php';

$user_email = $_SESSION['email'];
$mail_failed = isset($_GET['failed']) && $_GET['failed'] == "1";

require '_header.php';
?>

<?php if (!$mail_failed) {?>

<h2>Almost there...</h2>

<p>
An encrypted confirmation e-mail has just been sent to <?php echo $user_email ?>. Please follow the instructions in the e-mail to complete your registration.
</p>

<p>
Didn't receive it? <a href="/api/send_confirmation.php">Click here to send again</a>.
</p>

<p>
Your account will be automatically deleted if left unconfirmed for 7 days.
</p>

<?php } else { ?>

<h2>Registration error</h2>

<p>
There was an error completing the registration. Please verify that the e-mail address, <?php echo $user_email ?> actually exists.
</p>

<p>
If the address does actually exists, please file a bug report to the support team.
</p>

<?php } ?>

<?php require "_footer.php"; ?>
