<?php

//タイムゾーンを日本に設定
date_default_timezone_set('Asia/Tokyo');
//FileMakerのクラスを使えるようにする
require_once('FileMaker.php');

session_start();
$userid = $_SESSION['userid'];
$password = $_SESSION['password'];

//データベース名・ホスト・アカウントを定義
$fm = new FileMaker();
$fm->setProperty('database', '倉庫管理テスト_0330');
$fm->setProperty('hostspec', 'http://192.168.0.73');
$fm->setProperty('username', $userid);
$fm->setProperty('password', $password);





$seiri = htmlspecialchars($_POST['seiri'], ENT_QUOTES, 'UTF-8');
$case_amount = htmlspecialchars($_POST['case_amount'], ENT_QUOTES, 'UTF-8');
$order = htmlspecialchars($_POST['order'], ENT_QUOTES, 'UTF-8');
$amount = htmlspecialchars($_POST['amount'], ENT_QUOTES, 'UTF-8');
$tana = htmlspecialchars($_POST['tana'], ENT_QUOTES, 'UTF-8');
$recordid = htmlspecialchars($_POST['recordid'], ENT_QUOTES, 'UTF-8');

if($seiri && $order){


/*
$findCommand = $fm->newFindCommand('出庫インポート_HT情報出庫');
$findCommand->addFindCriterion('整理番号', $seiri);
$findCommand->addFindCriterion('入数_出庫', $amount);
$findCommand->addFindCriterion('受注番号', $order);
$findCommand->addFindCriterion('棚番_出庫', $tana);
$findCommand->addFindCriterion('ケース数_出庫', $case_amount);
$result = $findCommand->execute();

if(FileMaker::isError($result)){
  
  $notfound = array('fail' => $result->getmessage());
      $fail = json_encode($notfound, JSON_UNESCAPED_UNICODE);
     echo $fail;
}else{
  */
  //正常処理
  $EditCommand = $fm->newEditCommand('出庫インポート_HT情報出庫', $recordid);
  $EditCommand->setRecordId($recordid);
  $EditCommand->setField('出庫フラグ', '1');

  $result01_out = $EditCommand->execute();

  if(FileMaker::isError($result01_out)){
    //エラー処理
    
    $jsonfail_out = array('fail'=> $result01_out->getMessage());
    $fail = json_encode($jsonfail_out, JSON_UNESCAPED_UNICODE);
    echo $fail;
  //break;
  }else{
    //正常処理
    $success_out = array('success' => '入力完了');
    $jsonsuccess = json_encode($success_out, JSON_UNESCAPED_UNICODE);
    echo $jsonsuccess;
  }
  
//}
}else if($recordid){
  $deleteCommand = $fm->newEditCommand('出庫手入力' ,$recordid);
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
}else{
  $notperfect = array('fail' => 'QRコードを正しく入力してください');
      $fail = json_encode($notperfect, JSON_UNESCAPED_UNICODE);
      echo $fail;
}
?>