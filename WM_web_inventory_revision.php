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
$seiri = htmlspecialchars($_POST['seiri'], ENT_QUOTES, 'UTF-8');
$order = htmlspecialchars($_POST['order'], ENT_QUOTES, 'UTF-8');
$amount = htmlspecialchars($_POST['amount'], ENT_QUOTES, 'UTF-8');
$case_amount = htmlspecialchars($_POST['case_amount'], ENT_QUOTES, 'UTF-8');
$tana = htmlspecialchars($_POST['tana'], ENT_QUOTES, 'UTF-8');
$extra = htmlspecialchars($_POST['extra'], ENT_QUOTES, 'UTF-8');

$findCommand = $fm->newFindCommand('棚卸し編集');
$findCommand->addFindCriterion('c_レコードID', $recordid);
$result = $findCommand->execute();

if(FileMaker::isError($result)){
  $error = array('fail'=>$result->getmessage().$recordid);
  $jsonerror = json_encode($error);
  echo $jsonerror;

}else{
  $record = $result->getFirstRecord();
  $modificate = $record->getField('c_修正ID');

$editCommand = $fm->newEditCommand('棚卸し編集', $recordid);
$editCommand->setField('整理番号', $seiri);
$editCommand->setField('受注番号', $order);
$editCommand->setField('入数', $amount);
$editCommand->setField('ケース数', $case_amount);
$editCommand->setField('棚番', $tana);
$editCommand->setField('棚卸し_備考', $extra);
$editCommand->setModificationId($modificate);

$editresult = $editCommand->execute();
if(FileMaker::isError($editresult)){
  $editerror = array('fail'=>$editresult->getmessage());
  $jsonediterror = json_encode($editerror);
  echo $jsonediterror;
}else{
  $success = array('success'=>'入力完了');
  $jsonsuccess = json_encode($success);
  echo $jsonsuccess;
}
}







  }