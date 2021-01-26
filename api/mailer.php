<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\PHPMailerPGP;
use PHPMailer\PHPMailer\SMTP;

function send_mail($to, $subject, $msg, $keyfp="", $encrypt=true)
{
  require __DIR__ . '/../vendor/PHPMailer/Exception.php';
  require __DIR__ . '/../vendor/PHPMailer/PHPMailer.php';
  require __DIR__ . '/../vendor/PHPMailer/PHPMailerPGP.php';
  require __DIR__ . '/../vendor/PHPMailer/SMTP.php';
  $config = require __DIR__ . '/../config.php';

  try
  {
    // Set up mailer
    $mail = new PHPMailerPGP();
    $mail->SMTPDebug  = SMTP::DEBUG_OFF; //SMTP::DEBUG_SERVER;
    $mail->isSmtp();
    $mail->Host       = $config['mail_host'];
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = PHPMailerPGP::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->Username   = $config['mail_from'];
    $mail->Password   = $config['mail_pass'];

    // Set headers
    $mail->setFrom($config['mail_from'], $config['mail_from_name']);
    $mail->addAddress($to);
    $mail->isHtml(false);
    $mail->Subject = $subject;

    // Create and encrypt message.
    $msg_send = wordwrap($msg, 72);
    if ($encrypt && !empty($keyfp))
    {
      $mail->setGPGHome($config['gpg_home']);
      $mail->autoAddRecipients(false);
      $mail->addRecipient($to, $keyfp);
      $mail->encrypt();
    }
    $mail->Body = $msg_send;

    // Send
    $mail->send();
  }
  catch (Exception $e)
  {
    throw $e;
  }

  return true;
}

?>
