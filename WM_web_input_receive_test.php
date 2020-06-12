<?php
//エラー内容を表示
//ini_set('display_errors',1);

session_start();

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

//空欄等処理
if(empty($_POST['qr01']) or empty($_POST['case01']) or empty($_POST['tana'])){
  $not_isset= array('fail' => '必須項目を記入してください');
  $fail = json_encode($not_isset);
  echo $fail;
  
  exit;
}


//フォームからもらってきた値を変数に入れる
$qrcode = array(
  'qr01'=>htmlspecialchars($_POST['qr01']), 
  'qr02'=>htmlspecialchars($_POST['qr02']),
  'qr03'=>htmlspecialchars($_POST['qr03']),
  'qr04'=>htmlspecialchars($_POST['qr04']),
  'qr05'=>htmlspecialchars($_POST['qr05']),
  ENT_QUOTES, 'UTF-8');

  $case_amount = array(
    'case01'=>htmlspecialchars($_POST['case01']),
    'case02'=>htmlspecialchars($_POST['case02']),
    'case03'=>htmlspecialchars($_POST['case03']),
    'case04'=>htmlspecialchars($_POST['case04']),
    'case05'=>htmlspecialchars($_POST['case05']),
    ENT_QUOTES, 'UTF-8' );




 

//$qrcode = htmlspecialchars($_POST['qr01'], ENT_QUOTES, 'UTF-8');
//$case = htmlspecialchars($_POST['case01'], ENT_QUOTES, 'UTF-8');
    //$tant = htmlspecialchars($_POST['tant'], ENT_QUOTES,'UTF-8');
    $tana = htmlspecialchars($_POST['tana'], ENT_QUOTES, 'UTF-8');
    $move = htmlspecialchars($_POST['move'], ENT_QUOTES, 'UTF-8');
    //$radio = htmlspecialchars($_POST['inout'], ENT_QUOTES, 'UTF-8')
    
  //switch($_POST['inout']){

    
    
    $info01 = explode(" ", $qrcode['qr01']);

      
      //入庫処理
    //case '入庫':
    if($_POST['inout'] === '入庫'){

    
      if(!empty($qrcode['qr01'])){
        //$info01 = explode(" ", $qrcode['qr01']);
          $newCommand = $fm->newAddCommand('入庫インポート_HT情報入庫');
          $newCommand->setField('整理番号', $info01[0]);
          $newCommand->setField('整理枝番', $info01[1]);
          $newCommand->setField('受注番号', $info01[2].'-'.$info01[3]);
          //$newCommand->setField('数量_入庫', $info01[4] * $case_amount['case01']);
          $newCommand->setField('入数_入庫', $info01[4]);
          $newCommand->setField('ケース数_入庫', $case_amount['case01']);
          $newCommand->setField('棚番_入庫', $tana);
          //$newCommand->setField('手入力_担当者_入庫', $tant);
          $newCommand->setField('移動フラグ', $move);
          $newCommand->setScript('入庫情報入力');
          if($move){
            $find = array('seiri'=>$info01[0], 'order'=>$info01[2].'-'.$info01[3], 'amount'=>$info01[4], 'tana'=>$tana, 'case_amount'=>$case_amount['case01']);
            $jsonfind = json_encode($find);
            $newCommand->setPreSortScript('PHP_movecheck_in', $jsonfind);
          }
          $result01_in = $newCommand->execute();
      }
        
        

      if(FileMaker::isError($result01_in)){
        //エラー処理（入庫）
        $result01_in->getCode();
        $jsonfail_in = array('fail'=> $result01_in->getMessage());
        $fail = json_encode($jsonfail_in, JSON_UNESCAPED_UNICODE);
        echo $fail;
      //break;
      }else{
        //正常処理（入庫）
        $success_in = array('success' => $move.'入力完了');
        $jsonsuccess = json_encode($success_in, JSON_UNESCAPED_UNICODE);
        echo $jsonsuccess;
        
      }
         
      //break;


    }else if($_POST['inout'] === '出庫'){
      //出庫待ち状態のレコードがあるかどうかを調べる
      $FindRequest = array();

      $FindRequest[0] = $fm->newFindRequest('出庫インポート_HT情報出庫');
      
      $FindRequest[0]->addFindCriterion('受注番号', $info01[2].'-'.$info01[3]);
      $FindRequest[0]->addFindCriterion('入数_出庫', $info01[4]);
      $FindRequest[0]->addFindCriterion('棚番_出庫', $tana);

      $FindRequest[1] = $fm->newFindRequest('出庫インポート_HT情報出庫');
      $FindRequest[1]->addFindCriterion('出庫フラグ', '1');
      $FindRequest[1]->setOmit(true);

      $compoundFind = $fm->newCompoundFindCommand('出庫インポート_HT情報出庫');
      $compoundFind->add(1, $FindRequest[0]);
      $compoundFind->add(2, $FindRequest[1]);
      
      
      $result_out_find = $compoundFind->execute();


      if(FileMaker::isError($result_out_find)){
        
          $newCommand = $fm->newAddCommand('出庫インポート_HT情報出庫');
          $newCommand->setField('整理番号', $info01[0]);
          $newCommand->setField('整理枝番', $info01[1]);
          $newCommand->setField('受注番号', $info01[2].'-'.$info01[3]);
          $newCommand->setField('入数_出庫', $info01[4]);
          $newCommand->setField('ケース数_出庫', $case_amount['case01']);
          $newCommand->setField('棚番_出庫', $tana);
          //$newCommand->setField('担当者_出庫', $tant);
          $newCommand->setFIeld('移動フラグ', $move);
         
          $newCommand->setField('出庫フラグ', '1');
          if($move == "移動"){
            $find = array('seiri'=>$info01[0], 'order'=>$info01[2].'-'.$info01[3], 'amount'=>$info01[4], 'tana'=>$tana, 'case_amount'=>$case_amount['case01']);
            $jsonfind = json_encode($find);
            $newCommand->setScript('PHP_movecheck_out', $jsonfind);
          }
          
          $result01_out = $newCommand->execute();
        }else{
          //レコードがあったら編集する
          $record = $result_out_find->getFirstRecord();
          $recordID = $record->getField('c_レコードID');
          $modificationID = $record->getField('c_レコード修正ID');

  
          $newEditCommand = $fm->newEditCommand('出庫インポート_HT情報出庫', $recordID);
          $newEditCommand->setField('出庫フラグ', '1');
          //$newEditCommand->setRecordId($recordID);
          //$newEditCommand->setModificationId($modificationID + 0);
          $result01_out = $newEditCommand->execute();
        }//if
  
        if(FileMaker::isError($result01_out)){
          //エラー処理
          $result01_out->getCode();
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
         
      //break;
  
    //}//switch
    }else if(empty($_POST['inout'])){
      $not_inout = array('fail' => '入庫か出庫をチェックしてください');
      $fail = json_encode($not_inout, JSON_UNESCAPED_UNICODE);
      echo $fail;
    }
      
    

   
  ?>