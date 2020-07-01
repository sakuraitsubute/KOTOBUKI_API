<?php
header("Content-Type: application/json; charset=UTF-8");

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

$case = htmlspecialchars($_POST['case_amount']);
$qr = htmlspecialchars($_POST['qr01']);
//$tant = htmlspecialchars($_POST['tant']);
$amount = htmlspecialchars($_POST['amount']);
$extra = htmlspecialchars($_POST['extra']);
$tana = htmlspecialchars($_POST['tana']);
$order = htmlspecialchars($_POST['order']);

$qr_explode = explode(' ', $qr);
$seiri = $qr_explode[0];
$eda = $qr_explode[1];


if(!is_numeric($seiri)){
  $check = array('fail' => '値が不適切です');
  $fail = json_encode($check);
  echo $fail;
  exit;
  
}else if(!isset($case) or $case === 0){
  $empty = array('fail' => 'ケース数を入力してください');
  $fail = json_encode($empty);
  echo $fail;
  exit;
}


if(!empty($_POST['qr01'])){
  $findrequest = array();

  $findrequest[0] = $fm->newFindRequest('棚卸し手入力');
 $findrequest[0]->addFindCriterion('整理番号', $seiri);
 $findrequest[0]->addFindCriterion('受注番号', $order);
 $findrequest[0]->addFindCriterion('入数', $amount);
 $findrequest[0]->addFindCriterion('棚番', $tana);
 $findrequest[0]->addFindCriterion('整理枝番', $eda);

 $findrequest[1] = $fm->newFindRequest('棚卸し手入力');
 $findrequest[1]->addFindCriterion('削除フラグ', 1);
 $findrequest[1]->setOmit(true);

 $findrequest[2] = $fm->newFindRequest('棚卸し手入力');
 $findrequest[2]->addFindCriterion('在庫入力フラグ', 1);
 $findrequest[2]->setOmit(true);

 $compoundfind = $fm->newCompoundFindCommand('棚卸し手入力');
 $compoundfind->add(1, $findrequest[0]);
 $compoundfind->add(2, $findrequest[1]);
 $compoundfind->add(3, $findrequest[2]);

 $resultfind = $compoundfind->execute();
if(FileMaker::isError($resultfind) && $resultfind->getCode() <> "401"){
  $FMerror = array('fail' => 'findエラーコード:'.$resultfind->getCode().$resultfind->getMessage());
  $fail = json_encode($FMerror);
  echo $fail;
  die;
}else if(FileMaker::isError($resultfind) && $resultfind->getCode() == "401"){
  
  $newCommand = $fm->newAddCommand('棚卸し手入力');
  $newCommand->setField('整理番号', $seiri);
  $newCommand->setField('整理枝番', $eda);
  $newCommand->setField('受注番号', $order);
  $newCommand->setField('入数', $amount);
  $newCommand->setField('ケース数', $case);
  $newCommand->setField('棚番', $tana);
  $newCommand->setField('入力担当者', $userid);
  $newCommand->setField('棚卸し_備考', $extra);
  //$newCommand->setScript('入庫情報入力');
  $result = $newCommand->execute();

  if(FileMaker::isError($result)){
  $FMerror = array('fail' => 'newエラーコード:'.$result->getCode().$result->getMessage());
  $fail = json_encode($FMerror);
  echo $fail;
  }else{
  $goodjob = array('success' => '入力完了しました');
  $success = json_encode($goodjob);
  echo $success;
  }
}else{
  $record = $resultfind->getFirstRecord();
  $recordid = $record->getField('c_レコードID');
  $record_case_amount = $record->getField('ケース数');
  $editcommand = $fm->newEditCommand('棚卸し手入力', $recordid);
  $editcommand->setField('ケース数', $record_case_amount + $case);
  $result = $editcommand->execute();
    if(FileMaker::isError($result)){
      $FMerror = array('fail' => 'editエラーコード:'.$result->getCode().$result->getMessage());
      $fail = json_encode($FMerror);
    }else{
      $goodjob = array('success' => '入力完了しました');
      $success = json_encode($goodjob);
      echo $success;
    }
 }



  

}else{
  $error = array('fail'=>'QRコードを読み込んでから送信ボタンを押してください');
  $fail = json_encode($error);
  echo $fail;
};
