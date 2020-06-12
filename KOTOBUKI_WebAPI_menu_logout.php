<?php
//ログアウトできねえ……

  //setcookie("PHPSESSID", "", time()-60);

  session_start();
  $_SESSION = array();
  session_destroy();

echo 'ログアウトしました';
?>