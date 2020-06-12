<?php
  session_start();
  if(!$_SESSION['userid']){
    echo '<h1>ログインしてください</h1>';
    echo' <p><button type="button" id="close">閉じる</button></p>';
  }else{
    //ini_set('display_errors',1);
    //タイムゾーンを日本に設定
date_default_timezone_set('Asia/Tokyo');
//FileMakerのクラスを使えるようにする
require_once('FileMaker.php');


$userid = $_SESSION['userid'];
$password = $_SESSION['password'];

//データベース名・ホスト・アカウントを定義
$fm = new FileMaker();
$fm->setProperty('database', '倉庫管理テスト_0330');
$fm->setProperty('hostspec', 'http://192.168.0.73');
$fm->setProperty('username', $userid);
$fm->setProperty('password', $password);



$recordid = htmlspecialchars($_POST['recordid'], ENT_QUOTES, 'UTF-8');

$deleteCommand = $fm->newEditCommand('棚卸し編集' ,$recordid);
$deleteCommand->setField('削除フラグ','1');
  $result = $deleteCommand->execute();
if(FileMaker::isError($result)){
  $fail = array('fail'=>$result->getMessage);
  $jsonfail = json_encode($fail, JSON_UNESCAPED_UNICODE);
  echo $jsonfail;
}else{
  $success = array('success'=>'削除しました');
  $jsonsuccess = json_encode($success, JSON_UNESCAPED_UNICODE);
  echo $jsonsuccess;

}
  }
  ?>