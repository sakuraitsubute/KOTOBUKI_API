<?php

date_default_timezone_set('Asia/Tokyo');
//FileMakerのクラスを使えるようにする
require_once('FileMaker.php');


$userid = htmlspecialchars($_POST['userid']);
$password = htmlspecialchars($_POST['password']);

//$password_hash = password_hash($password, PASSWORD_DEFAULT);
//echo $password_hash;

$hash = '$2y$10$5HWSSzo8q2lKgTbPWIxUWe8ZieI5BAgghVVLQ8Edsr/aIAUPQW3r.';

if(!$userid or !$password){
  session_unset();
  $empty = array('fail'=>'ユーザーIDとパスワードを入力してください');
  $jsonfail = json_encode($empty, JSON_UNESCAPED_UNICODE);
  echo $jsonfail;
}else if(!password_verify($password, $hash)){
  session_unset();
  $error = array('fail'=>'パスワードが違います');
  $jsonerror = json_encode($error, JSON_UNESCAPED_UNICODE);
  echo $jsonerror;
}else{

  $fm = new FileMaker();
$fm->setProperty('database', '倉庫管理テスト_0330');
$fm->setProperty('hostspec', 'http://192.168.0.73');
$fm->setProperty('username', $userid);
$fm->setProperty('password', $password);

$layoutOBJ = $fm->getLayout('入庫手入力');

if(FileMaker::isError($layoutOBJ)){
  $fmerror = array('fail'=>$layoutOBJ->getmessage());
  $jsonfmerror = json_encode($fmerror);
  echo $jsonfmerror;
}else{
  
  session_start();
session_regenerate_id(true);
$_SESSION['userid'] = $userid;
$_SESSION['password'] = $password;
  $success = array('success'=>'ログイン成功');
  $jsonsuccess = json_encode($success, JSON_UNESCAPED_UNICODE);
  echo $jsonsuccess;
}


//echo $_SESSION['userid'];
//echo $_SESSION['password'];


}



?>