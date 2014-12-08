#!/usr/bin/php


<?php

include_once('qdmail_receiver.php');
//require_once 'Mail/mimeDecode.php';

$accounts = array(
    array(  'protocol'=>'pop3',
            'host'=>'192.168.169.101',
            'user'=>'sakura.tomoyo',
            'pass'=>'1',
    ),
    array(  'protocol'=>'pop3',
            'host'=>'192.168.169.101',
            'user'=>'sakura.shaoran',
            'pass'=>'1',
    ),
    array(  'protocol'=>'pop3',
            'host'=>'192.168.169.101',
            'user'=>'sakura.toya',
            'pass'=>'1',
    ),
);

//qd_receive_mail( 'start' , $server , 'utf-8' ); 
//echo qd_receive_mail( 'count' );
//qd_receive_mail( 'next' );

if(!chroot("./mails")) {
    exit(1);
}

foreach($accounts as $account) {
    $receiver = QdmailReceiver::start('pop' , $account);

    $fcnt = 1;
    for($i = 1 ; $i <= $receiver->count() ; $i++) {

        ob_start(); 
        //echo "Mail header: ".$receiver->pointer()."\n";
                                                    
        /*echo htmlspecialchars(
            print_r(
               $receiver->header( array('subject','name') , 'none' )
            ,true)
        ,ENT_NOQUOTES);*/

        /*$this->header = array(
            'ヘッダー名（e.g Cc）'=>array(
                0 => array(
                     'mail' => 'mail_0@example.com',
                     'name' => 'お名前0',
                     'mime' => '=?iso2022-jp?B?......?=',
                ),
                1 => array(
                     'mail' => 'mail_1@example.com',
                     'name' => 'お名前1',
                     'mime' => '=?iso2022-jp?B?......?=',
                ),

            'ヘッダー名(e.g. subject)'=>array(
                  'name' => '件名',
                  'mime' => '=?iso2022-jp?B?......?=',
            ),
        );*/

        //$receiver->pointer();
        //$receiver->all();


        /*echo "subject";print_r($subject);echo "\n";
        echo "rcd";print_r($rcd);echo "\n";
        echo "matches";print_r($matches);echo "\n";
        echo "contract";print_r($contract);echo "\n";*/
        
        //echo $receiver->all();
        /*$params['include_bodies'] = true;
        $params['decode_bodies']  = true;
        $params['decode_headers'] = true;

        $decoder = new Mail_mimeDecode($receiver->all());
        $structure = $decoder->decode($params);*/

        /*$subject = $receiver->all( array('subject','name') , '' );
        echo "name :" . $subject . "\n";*/

        /*$rcd = mbereg(マッチパターン, 対象変数 [, 代入配列]);
        $rcd = mberegi(マッチパターン, 対象変数 [, 代入配列]);
        $rcd = mbereg_replace(マッチパターン, 置換文字列, 対象変数);
        $rcd = mberegi_replace(マッチパターン, 置換文字列, 対象変数);*/


        /*$subname = $receiver->header( array('subject','name') , '' );

        $internal_encoding = mb_internal_encoding();
        if($internal_encoding != 'JIS'){
            mb_internal_encoding('JIS');
        }
        $subname = mb_decode_mimeheader($subname);
        mb_internal_encoding($internal_encoding);
        //$subname = mb_convert_encoding($subname, 'UTF-8', 'ISO-2022-JP-MS');

        echo "subname :"; print_r($subname); echo "\n";*/


        $subject = $receiver->header( array('subject','value') , '' );
        $internal_encoding = mb_internal_encoding();
        if($internal_encoding != 'JIS'){
            mb_internal_encoding('JIS');
        }
        $subject = mb_decode_mimeheader($subject);
        mb_internal_encoding($internal_encoding);
        //$subject = mb_convert_encoding($subject, 'UTF-8', 'ISO-2022-JP-MS');

        echo "subject :"; print_r($subject); echo "\n";


        //$contract = "KOTEI";
        $contract = "";
        $rcd = ereg("IDENT", $subject , $matches);
        if(!$rcd) {
            $contract = "[]";
        } else {
            $var = mbereg_replace("【", "", $matches[0]);
            $var = mbereg_replace("】", "", $var);
            $contract = "[" . $var . "]";
        }

        echo "cont :"; print_r($contract); echo "\n";


        $body = $receiver->all();
        //echo "body :"; print_r($body); echo "\n";

        $rcd = ereg("Content-Transfer-Encoding.*$", $body , $matches);
        $bodyMime = "";
        if(!$rcd) {
            $bodyMime = "";
        } else {
            $bodyMime = ereg_replace("Content-Transfer-Encoding: ", "", $matches[0]);
            //echo "mimetype :["; echo $bodyMime; echo "]\n";
            if(strpos($bodyMime, "base64") === 0) {
                $bodyMime = "base64";
            } else {
                $bodyMime = "7bit";
            }
        }
        $mimeType = $bodyMime;

        echo "bodyMime :"; print_r($mimeType); echo "\n";

        $rcd = ereg("Sender:.*", $body , $matches);
        $bodyMime = "";
        if(!$rcd) {
            $bodyMime = "";
        } else {
            //$rcd = ereg("Sender:.*\r\n\r\n", $matches[0] , $matches);
            $bodyMime = ereg_replace("Sender:.*\r\n\r\n", "", $matches[0]);
            if(!$bodyMime) {
                $bodyMime = "[]";
            } else {
                if($mimeType=="base64") {
                    $bodyMime = base64_decode($bodyMime);
                } else {
                    $bodyMime = quoted_printable_decode($bodyMime);
                }
            }
        }

        echo "body :"; print_r($bodyMime); echo "\n";

        $output = ob_get_contents();
        //ob_end_clean();
        $filebase = $contract . "-" . $account['user'] . "-";
        $filename = $contract . "-" . $account['user'] . "-" . sprintf("%03d", $fcnt);

        while(file_exists( $filename )) {
            $fcnt++;
            $filename = $contract . "-" . $account['user'] . "-" . sprintf("%03d", $fcnt);
        }

        $fp = fopen($filebase . sprintf("%03d", $fcnt), 'w');
        fwrite($fp, $output);
        fclose($fp);

        $receiver->next();
    }
}
?>
