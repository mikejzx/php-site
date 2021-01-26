<?php

namespace PHPMailer\PHPMailer;

class PHPMailerPGP extends PHPMailer
{
  protected $gpg = null;

  protected $gpgHome = null;

  protected $encrypted = false;

  protected $autoRecipients = true;

  protected $recipientKeys = array();

  protected function gpg_init()
  {
    if (!class_exists('gnupg')) {
      throw new PHPMailerPGPException('PHPMailerPGP requires GnuPG');
    }

    if (!$this->gpgHome && isset($_SERVER['HOME'])) {
      $this->gpgHome = $_SERVER['HOME'] . '/.gnupg';
    }

    if (!$this->gpgHome && getenv('HOME')) {
      $this->gpgHome = getenv('HOME') . '/.gnupg';
    }

    if (!$this->gpgHome) {
      throw new PHPMailerPGPException('GnuPG home path does not exist');
    }

    putenv("GNUPGHOME=" . escapeshellcmd($this->gpgHome));

    if (!$this->gpg) {
      $this->gpg = new \gnupg();
    }
    $this->gpg->seterrormode(\gnupg::ERROR_EXCEPTION);
  }

  public function setGPGHome($home)
  {
    if (!file_exists($home)) {
      throw new PHPMailerPGPException('Cannot set GPG home: path does not exist');
    }
    $this->gpgHome = $home;
  }

  public function encrypt($encrypt=true)
  {
    $this->gpg_init();
    $this->encrypted = (bool)$encrypt;
  }

  public function autoAddRecipients($autoAdd=true)
  {
    $this->gpg_init();
    $this->autoRecipients = (bool)$autoAdd;
  }

  public function addRecipient($id, $keyFingerprint=null)
  {
    $this->gpg_init();

    if (!$keyFingerprint) {
      $keyFingerprint = $this->get_key($id, 'encrypt');
    }

    $this->recipientKeys[$id] = $keyFingerprint;
  }

  public function importKey($data)
  {
    $this->gpg_init();

    if (!file_exists($this->gpgHome) || !is_writable($this->gpgHome)) {
      throw new PHPMailerPGPException('Cannot import key: GPG home dir is not writable.');
    }

    $results = $this->gpg->import($data);
    $this->edebug($results['imported'].' keys imported');
    $this->edebug($results['unchanged'].' keys unchanged');
    $this->edebug($results['newuserids'].' new user ids imported');
    $this->edebug($results['newsubkeys'].' new subkeys imported');
    $this->edebug($results['secretimported'].' secret keys imported');
    $this->edebug($results['secretunchanged'].' secret keys unchanged');
    $this->edebug($results['newsignatures'].' new signatures imported');
    $this->edebug($results['skippedkeys'].' skipped keys');
  }

  public function getMailMIME()
  {
    $result = '';
    switch ($this->message_type)
    {
    case 'encrypted':
    {
      $result .= $this->headerLine('Content-Type', 'multipart/encrypted;');
      $result .= $this->textLine(' protocol="application/pgp-encrypted";');
      $result .= $this->textLine(' boundary="' . $this->boundary[1] . '"');

      if ($this->Mailer != 'mail')
      {
        $result .= static::$LE;
      }

      return $result;
    }

    default:
    {
      return parent::getMailMIME();
    }
    }
  }

  public function createBody()
  {
    $body = parent::createBody();

    if (!$body || !$this->encrypted)
    {
      return $body;
    }

    $unencryptedBody = $body;

    if ($this->encrypted) {
      $encryptedBody = $body;

      if ($this->autoRecipients) {
        $recipients = $this->getAllRecipientAddresses();
        foreach ($recipients as $recipient => $temp) {
          if (!isset($this->recipientKeys[$recipient])) {
            $this->addRecipient($recipient);
          }
        }
      }

      if (!$this->recipientKeys) {
        throw new PHPMailerPGPException('No recipients. Cannot encrypt');
      }

      $encryptedBody = $this->pgp_encrypt_string($encryptedBody,
        array_values($this->recipientKeys));

      $this->message_type = 'encrypted';
      $this->Encoding = '7bit';

      $boundary = md5('pgpencrypt'.uniqid(time()));
      $this->boundary[1] = 'b1_' . $boundary;
      $this->boundary[2] = 'b2_' . $boundary;
      $this->boundary[3] = 'b3_' . $boundary;

      $body = '';
      //$body .= $this->textLine('This is an OpenPGP/MIME encrypted message (RFC 4880 and 3156)');
      //$body .= static::$LE;
      $body .= $this->textLine('--b1_' . $boundary);
      $body .= $this->textLine('Content-Type: application/pgp-encrypted');
      $body .= $this->textLine('Content-Description: PGP/MIME version identification');
      $body .= static::$LE;
      $body .= $this->textLine('Version: 1');
      $body .= static::$LE;
      $body .= $this->textLine('--b1_' . $boundary);
      $body .= $this->textLine('Content-Type: application/octet-stream; name="msg.asc"');
      $body .= $this->textLine('Content-Description: OpenPGP encrypted message');
      $body .= $this->textLine('Content-Disposition: inline; filename="msg.asc"');
      $body .= static::$LE;
      $body .= $encryptedBody;
      $body .= static::$LE;
      $body .= static::$LE;
      $body .= $this->textLine('--b1_' . $boundary . '--');
    }

    return $body;
  }

  protected function pgp_encrypt_string($plaintext, $keyFingerprints)
  {
    $this->gpg->clearencryptkeys();

    foreach ($keyFingerprints as $fp) {
      $this->gpg->addencryptkey($fp);
    }

    $this->gpg->setarmor(1);

    $encrypted = $this->gpg->encrypt($plaintext);
    if ($encrypted) {
      return $encrypted;
    }

    throw new PHPMailerPGPException('Unable to encrypt message.');
  }

  protected function get_key($id, $purpose)
  {
    $keys = $this->gpg->keyinfo($id);
    $fingerprints = array();

    foreach ($keys as $key) {
      if ($key['disabled']) continue;
      if ($key['expired']) continue;
      if ($key['revoked']) continue;
      if ($purpose === 'sign' && !$key['can_sign']) continue;
      if ($purpose === 'encrypt' && !$key['can_encrypt']) continue;

      foreach ($key['subkeys'] as $subkey) {
        if ($subkey['disabled']) continue;
        if ($subkey['expired']) continue;
        if ($subkey['revoked']) continue;
        if ($subkey['invalid']) continue;
        if ($purpose === 'sign' && !$subkey['can_sign']) continue;
        if ($purpose === 'encrypt' && !$subkey['can_encrypt']) continue;
        $fingerprints[] = $subkey['fingerprint'];
      }
    }

    if (count($fingerprints) === 1) {
      return $fingerprints[0];
    }

    if (count($fingerprints) > 1) {
      throw new PHPMailerPGPException('Found more than one active key for '
        . $id . ', use addRecipient() or addSignature()');
    }

    throw new PHPMailerPGPException('Unable to find an active key to '
      . $purpose . ' for ' . $id . ', try importing keys first');
  }
}

class PHPMailerPGPException extends Exception {};

?>
