#!/usr/bin/php


<?php

include_once('qdmail_receiver.php');
//require_once 'Mail/mimeDecode.php';

$accounts = array(
    array(  'protocol'=>'pop3',
            'host'=>'192.168.169.100',
            'user'=>'sakura.tomoyo',
            'pass'=>'1',
    ),
    array(  'protocol'=>'pop3',
            'host'=>'192.168.169.100',
            'user'=>'sakura.shaoran',
            'pass'=>'1',
    ),
    array(  'protocol'=>'pop3',
            'host'=>'192.168.169.100',
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



        $subject = $receiver->header( array('subject','value') , '' );
        //echo "header :"; print_r($receiver->header()); echo "\n";

        $subject = mb_decode_mimeheader($subject);

        //echo "mime :"; print_r($subject); echo "\n";

        /*$rcd = mbereg(マッチパターン, 対象変数 [, 代入配列]);
        $rcd = mberegi(マッチパターン, 対象変数 [, 代入配列]);
        $rcd = mbereg_replace(マッチパターン, 置換文字列, 対象変数);
        $rcd = mberegi_replace(マッチパターン, 置換文字列, 対象変数);*/

        $rcd = ereg("IDF", $subject , $matches);
        $contract = "";
        if(!$rcd) {
            $contract = "[]";
        } else {
            $contract = "[" . $matches[0] . "]";
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
        ob_end_clean();
        $filebase = $contract . "-" . $account['user'] . "-";
        $filename = $contract . "-" . $account['user'] . "-" . sprintf("%03d", $fcnt);

        while(file_exists( $filename )) {
            $fcnt++;
            $filename = $contract . "-" . $account['user'] . "-" . sprintf("%03d", $fcnt);
        }

        $fp = fopen($filebase . sprintf("%03d", $fcnt), 'w');
        fwrite($fp, $output);
        fclose($fp);

        //print_r($account['user']);
        $receiver->next();
    }
}
?>
