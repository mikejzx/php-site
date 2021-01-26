<?php

// Safens input from user to prevent
// SQL injections and XSS.
function safe_input($inp)
{
  $inp = trim($inp);
  $inp = stripslashes($inp);
  $inp = htmlspecialchars($inp);
  return $inp;
}

?>
