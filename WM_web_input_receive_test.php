<?php
//エラー内容を表示
//ini_set('display_errors',1);
$seiri;
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
if(empty($_POST['qr01']) or empty($_POST['case01'])){
  $not_isset= array('fail' => '必須項目を記入してください');
  $fail = json_encode($not_isset);
  echo $fail;
  
  exit;
}else if(empty($_POST['tana'])){
  $not_isset= array('fail' => '棚番を選択してください');
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
    $seiri = intval($info01[0]);
    

      
    switch($_POST['inout']){
      //入庫処理
    case '入庫':
    //if($_POST['inout'] === '入庫'){

    
      
        //$info01 = explode(" ", $qrcode['qr01']);
          $newCommand = $fm->newAddCommand('入庫インポート_HT情報入庫');
          $newCommand->setField('整理番号', $seiri);
          $newCommand->setField('整理枝番', $info01[1]);
          $newCommand->setField('受注番号', $info01[2].'-'.$info01[3]);
          $newCommand->setField('入数_入庫', $info01[4]);
          $newCommand->setField('ケース数_入庫', $case_amount['case01']);
          $newCommand->setField('棚番_入庫', $tana);
          


          if($move){
            $find = array('seiri'=>$seiri, 'order'=>$info01[2].'-'.$info01[3], 'amount'=>$info01[4], 'tana'=>$tana, 'case_amount'=>$case_amount['case01']);
            $jsonfind = json_encode($find);
            $newCommand->setPreSortScript('PHP_movecheck_in', $jsonfind);
          }
          $find = array('amount'=>intval($info01[4]), 'case_amount'=>intval($case_amount ['case01']), 'eda'=>intval($info01[1]),  'order'=>$info01[2].'-'.$info01[3], 'seiri'=>$seiri,  'tana'=>$tana);
          //JSON化してJSONGetElementで受けられるように
          $jsonfind = json_encode($find);

          
          $newCommand->setPreCommandScript('PHP_double_defense_in', $jsonfind);

          $newCommand->setScript('入庫情報入力', $jsonfind);
          $result01_in = $newCommand->execute();
      
        
        

      if(FileMaker::isError($result01_in)){
        //エラー処理（入庫）
        $result01_in->getCode();
        $jsonfail_in = array('fail'=> $result01_in->getCode().$result01_in->getMessage());
        $fail = json_encode($jsonfail_in, JSON_UNESCAPED_UNICODE);
        echo $fail;
      //break;
      }else{
        //正常処理（入庫）
        $success_in = array('success' => $move.'入力完了');
        $jsonsuccess = json_encode($success_in, JSON_UNESCAPED_UNICODE);
        echo $jsonsuccess;
        
      }
         
      break;


    //}else if($_POST['inout'] === '出庫'){
      case '出庫';
      //出庫待ち状態のレコードがあるかどうかを調べる
      $FindRequest = array();

      $FindRequest[0] = $fm->newFindRequest('出庫インポート_HT情報出庫');
      
      $FindRequest[0]->addFindCriterion('整理番号', $seiri);
      $FindRequest[0]->addFindCriterion('整理枝番', $info01[1]);
      $FindRequest[0]->addFindCriterion('受注番号', $info01[2].'-'.$info01[3]);
      $FindRequest[0]->addFindCriterion('入数_出庫', $info01[4]);
      $FindRequest[0]->addFindCriterion('棚番_出庫', $tana);

      $FindRequest[1] = $fm->newFindRequest('出庫インポート_HT情報出庫');
      $FindRequest[1]->addFindCriterion('出庫フラグ', '1');
      $FindRequest[1]->setOmit(true);

      $FindRequest[2] = $fm->newFindRequest('出庫インポート_HT情報出庫');
      $FindRequest[2]->addFindCriterion('削除フラグ', '1');
      $FindRequest[2]->setOmit(true);

      $FindRequest[3] = $fm->newFindRequest('出庫インポート_HT情報出庫');
      $FindRequest[3]->addFindCriterion('移動フラグ', '移動');
      $FindRequest[3]->setOmit(true);


      $compoundFind = $fm->newCompoundFindCommand('出庫インポート_HT情報出庫');
      $compoundFind->add(1, $FindRequest[0]);
      $compoundFind->add(2, $FindRequest[1]);
      $compoundFind->add(3, $FindRequest[2]);
      $compoundFind->add(4, $FindRequest[3]);
      
      
      $result_out_find = $compoundFind->execute();


      if(FileMaker::isError($result_out_find)){
        //ない場合は新しく作成
          $newCommand = $fm->newAddCommand('出庫インポート_HT情報出庫');
          $newCommand->setField('整理番号', $seiri);
          $newCommand->setField('整理枝番', $info01[1]);
          $newCommand->setField('受注番号', $info01[2].'-'.$info01[3]);
          if(!$info01[4]){
            $newCommand->setField('入数_出庫', 1000);
          }else{
            $newCommand->setField('入数_出庫', $info01[4]);
          }
          
          $newCommand->setField('ケース数_出庫', $case_amount['case01']);
          $newCommand->setField('棚番_出庫', $tana);
          $newCommand->setField('実務担当者_出庫', $userid);
          
          $newCommand->setField('出庫フラグ', '1');
          
          $find = array('amount'=>intval($info01[4]), 'case_amount'=>intval($case_amount ['case01']), 'eda'=>intval($info01[1]),  'order'=>$info01[2].'-'.$info01[3], 'seiri'=>$seiri,  'tana'=>$tana);
            //JSON化してJSONGetElementで受けられるように
            $jsonfind = json_encode($find);
            $newCommand->setPreCommandScript('PHP_double_defense_out', $jsonfind);
          
          $result01_out = $newCommand->execute();
        }else{
          //レコードがあったら編集する
          $record = $result_out_find->getFirstRecord();
          $recordID = $record->getField('c_レコードID');
          $modificationID = $record->getField('c_レコード修正ID');

          $newEditCommand = $fm->newEditCommand('出庫インポート_HT情報出庫', $recordID);
          
            $newEditCommand->setField('出庫フラグ', '1');
            $newEditCommand->setField('実務担当者_出庫', $userid);
                    
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
         
      break;
  
    //}//switch

    case '移動';

    $FindRequest_move = array();

    $FindRequest_move[0] = $fm->newFindRequest('出庫インポート_HT情報出庫');
    
    $FindRequest_move[0]->addFindCriterion('整理番号', $seiri);
    $FindRequest_move[0]->addFindCriterion('受注番号', $info01[2].'-'.$info01[3]);
    $FindRequest_move[0]->addFindCriterion('入数_出庫', $info01[4]);
    $FindRequest_move[0]->addFindCriterion('棚番_出庫', $tana);
    $FindRequest_move[0]->addFindCriterion('移動フラグ', '移動');

    $FindRequest_move[1] = $fm->newFindRequest('出庫インポート_HT情報出庫');
    $FindRequest_move[1]->addFindCriterion('出庫フラグ', '1');
    $FindRequest_move[1]->setOmit(true);

    $FindRequest_move[2] = $fm->newFindRequest('出庫インポート_HT情報出庫');
    $FindRequest_move[2]->addFindCriterion('削除フラグ', '1');
    $FindRequest_move[2]->setOmit(true);

    $FindRequest_move[3] = $fm->newFindRequest('出庫インポート_HT情報出庫');
    $FindRequest_move[3]->addFindCriterion('移動完了フラグ', '1');
    $FindRequest_move[3]->setOmit(true);


    $compoundFind_move = $fm->newCompoundFindCommand('出庫インポート_HT情報出庫');
    $compoundFind_move->add(1, $FindRequest_move[0]);
    $compoundFind_move->add(2, $FindRequest_move[1]);
    $compoundFind_move->add(3, $FindRequest_move[2]);
    $compoundFind_move->add(4, $FindRequest_move[3]);
    
    
    $result_move = $compoundFind_move->execute();

    if(FileMaker::isError($result_move)){
      //なかったら創る
      $newCommand_move = $fm->newAddCommand('出庫インポート_HT情報出庫');
      $newCommand_move->setField('整理番号', $seiri);
      $newCommand_move->setField('整理枝番', $info01[1]);
      $newCommand_move->setField('受注番号', $info01[2].'-'.$info01[3]);
      $newCommand_move->setField('入数_出庫', $info01[4]);
      $newCommand_move->setField('ケース数_出庫', $case_amount['case01']);
      $newCommand_move->setField('棚番_出庫', $tana);
      $newCommand_move->setField('実務担当者_出庫', $userid);
      $newCommand_move->setField('移動フラグ', '移動');

        //FMスクリプトで使うスクリプト引数を指定
        $find = array('seiri'=>$seiri, 'order'=>$info01[2].'-'.$info01[3], 'amount'=>$info01[4], 'tana'=>$tana, 'case_amount'=>$case_amount ['case01']);
            //JSON化してJSONGetElementで受けられるように
            $jsonfind = json_encode($find);
            $newCommand_move->setScript('PHP_movecheck_out', $jsonfind);
        $newCommand_move->setScript('PHP_movecheck_out', $jsonfind);

      $result01_move = $newCommand_move->execute();
    }else{
      //あったら編集する
      $record_move = $result_move->getFirstRecord();
      $recordID_move = $record_move->getField('c_レコードID');
      $modificationID_move = $record_move->getField('c_レコード修正ID');

      $newEditCommand_move = $fm->newEditCommand('出庫インポート_HT情報出庫', $recordID_move);

        $record_case_amount = $record_move->getField('ケース数_出庫');
        $newEditCommand_move->setField('ケース数_出庫', $record_case_amount + $case_amount['case01']);
        $newEditCommand_move->setField('実務担当者_出庫', $userid);

        //FMスクリプトで使うスクリプト引数を指定
        $find = array('seiri'=>$seiri, 'order'=>$info01[2].'-'.$info01[3], 'amount'=>$info01[4], 'tana'=>$tana, 'case_amount'=>$record_case_amount + $case_amount ['case01']);
            //JSON化してJSONGetElementで受けられるように
            $jsonfind = json_encode($find);
            $newEditCommand_move->setScript('PHP_movecheck_out', $jsonfind);
        
                
      $result01_move = $newEditCommand_move->execute();
    }

    if(FileMaker::isError($result01_move)){
      //エラー処理
      $result01_move->getCode();
      $jsonfail_move = array('fail'=> $result01_move->getMessage());
      $fail = json_encode($jsonfail_move, JSON_UNESCAPED_UNICODE);
      echo $fail;
    //break;
    }else{
      //正常処理
      $success_move = array('success' => '移動の入力完了');
      $jsonsuccess = json_encode($success_move, JSON_UNESCAPED_UNICODE);
      echo $jsonsuccess;
    }
     
  break;


 


  default:
    //}else if(empty($_POST['inout'])){
      $not_inout = array('fail' => '入庫か出庫か移動出庫をチェックしてください');
      $fail = json_encode($not_inout, JSON_UNESCAPED_UNICODE);
      echo $fail;
    }
      
    /*

if($move == "移動"){
            $find = array('seiri'=>$seiri, 'order'=>$info01[2].'-'.$info01[3], 'amount'=>$info01[4], 'tana'=>$tana, 'case_amount'=>$case_amount['case01']);
            $jsonfind = json_encode($find);
            $newCommand->setScript('PHP_movecheck_out', $jsonfind);
          }else{
            $newCommand->setField('出庫フラグ', '1');
          }


          if($move == "移動"){
            $record_case_amount = $record->getField('ケース数_出庫');
            $newEditCommand->setField('ケース数_出庫', $record_case_amount + $case_amount);
          }else{
            $newEditCommand->setField('出庫フラグ', '1');
            $newEditCommand->setField('実務担当者_出庫', $userid);
          }
      */

   
  ?>
