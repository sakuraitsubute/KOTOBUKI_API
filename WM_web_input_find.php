<?php

//タイムゾーンを日本に設定
date_default_timezone_set('Asia/Tokyo');
//FileMakerのクラスを使えるようにする
require_once('FileMaker.php');

//データベース名・ホスト・アカウントを定義
$fm = new FileMaker();
$fm->setProperty('database', '倉庫管理テスト_0330');
$fm->setProperty('hostspec', 'http://192.168.0.73');
$fm->setProperty('username', 'Web');
$fm->setProperty('password', 'tEL6728061');

if(isset($_POST['seiri']) && isset($_POST['eda']) && isset($_POST['order'])){



$seiri = htmlspecialchars($_POST['seiri'], ENT_QUOTES, 'UTF-8');
$eda = htmlspecialchars($_POST['eda'], ENT_QUOTES, 'UTF-8');
$order = htmlspecialchars($_POST['order'], ENT_QUOTES, 'UTF-8');

$findCommand = $fm->newFindCommand('dbo.findview');
$findCommand->addFindCriterion('整理番号_UNSIGN_NUMERIC', $seiri);
$findCommand->addFindCriterion('整理枝番_UNSIGN_NUMERIC', $eda);
$findCommand->addFindCriterion('受注番号_CHAR', $order);
$findCommand->addSortRule('納期', 1, FILEMAKER_SORT_DESCEND);
$result = $findCommand->execute();

if(FileMaker::isError($result)){
  echo 'FileMaker Error Code:'. $result->getCode();
  echo '<p>'. $result->getMessage(). '</p>';
}else{
  //正常処理
  $record = $result->getFirstRecord();
  $tokui = $record->getField('得意先名');
  $user = $record->getField('ユーザー名');
  $title = $record->getField('タイトル名');
  
  $str = "得意先名:".$tokui."\n ユーザー名:".$user."\n タイトル:".$title."\n";
  $find = nl2br($str);
  echo $find;
}
}else{
  echo 'QRコードを読み込んでください';
}
?>
