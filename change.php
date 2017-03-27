<!DOCTYPE html>
<HTML>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="apple-mobile-web-app-capable" content="no" />
        <meta name="viewport" content="initial-scale=1, width=device-width, user-scalable=no" />
        <?php
        $isLoc = true;
        $ini = parse_ini_file("paging.ini");
        $cdnJqm = $ini['jqm'];
        $cdnJQ = $ini['jquery'];
        $instr = $ini['copyright'];
        ?>
        <link rel="stylesheet" href="<?php echo (($isLoc) ? './jqm' : 'http://code.jquery.com/mobile/'.$cdnJqm).'/jquery.mobile-'.$cdnJqm;?>.min.css" />
        <script src="<?php echo (($isLoc) ? './jqm/' : 'http://code.jquery.com/').'jquery-'.$cdnJQ;?>.min.js"></script>
        <script src="<?php echo (($isLoc) ? './jqm' : 'http://code.jquery.com/mobile/'.$cdnJqm).'/jquery.mobile-'.$cdnJqm;?>.min.js"></script>
        <script src="./lib/cookies.js"></script>
<!--==========================================-->
        <title>Heart Center Paging</title>
    </head>
<body>
<?php
    function simple_encrypt($text, $salt = "") {
        if (!$salt) {
            global $instr; $salt = $instr;
        }
        if (!$text) {
            return $text;
        }
        return trim(base64_encode(mcrypt_encrypt(MCRYPT_BLOWFISH, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB), MCRYPT_RAND))));
    }
    function simple_decrypt($text, $salt = "") {
        if (!$salt) {
            global $instr; $salt = $instr;
        }
        if (!$text) {
            return $text;
        }
        return trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB), MCRYPT_RAND)));
    }
    function compare($field,$old,$new) {
        if ($old==$new) {
            return '';
        } else {
            return $field.": '".$old."' -> '".$new."'\r\n";
        }
    }
    function dialog($title,$tcolor,$msg1,$msg2,$img,$alt,$bar,$fg,$bg) {
        ?>
        <div data-role="page" data-dialog="true" id="dialog-fn" data-overlay-theme="<?php echo $bg;?>">
            <div data-role="header" data-theme="<?php echo $bar;?>">
                <h1 style="color:<?php echo $tcolor;?>"><?php echo $title;?></h1>
                <a href="index.php" class="ui-btn ui-shadow ui-btn-icon-left ui-icon-delete ui-btn-icon-notext ui-corner-all">go back</a>
            </div>
            <div data-role="content" data-theme="<?php echo $fg;?>">
                <p style="text-align:center">
                    <?php echo $msg1;?><br>
                    <img src="images/<?php echo $img;?>" alt="<?php echo $alt;?>"><br>
                    <?php echo $msg2;?><br>
                </p>
            </div>
        </div>
        <?php
    }
    function logger($msg) {
        global $user;
        $logfile = './logs/'.date('Ym').'.csv';
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else if(getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } else if(getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } else if(getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } else if(getenv('HTTP_FORWARDED')) {
           $ipaddress = getenv('HTTP_FORWARDED');
        } else if(getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
            $ipaddress = 'UNKNOWN';
        }
        $out = fopen($logfile,'a');
        fputcsv(
            $out, 
            array(
                date('c'),
                $ipaddress,
                $user,
                $msg
            )
        ); 
        fclose($out);
    }
require_once './lib/PHPMailerAutoload.php';

/*  Clean out any leftover blob files
 */
foreach (glob('./logs/*.blob') as $fname) {
    $fmdate = filemtime($fname);
    if ((time()-$fmdate) > (20*60)) {
        unlink($fname);
    }
}
/*  If directed from edit.php, read the form input
 *  create the "cookie" (crypted values, key, and expiration time)
 *  Send mail to affected user.
 */
$uid = \filter_input(\INPUT_POST, 'uid');
if ($uid) {
    $nameL = \filter_input(\INPUT_POST, 'nameL');
    $nameF = \filter_input(\INPUT_POST, 'nameF');
    $numPager = \filter_input(\INPUT_POST, 'numPager');
    $numPagerSys = \filter_input(\INPUT_POST, 'numPagerSys');
    $numSms = \filter_input(\INPUT_POST, 'numSms');
    $numSmsSys = \filter_input(\INPUT_POST, 'numSmsSys');
    $numPushBul = \filter_input(\INPUT_POST, 'numPushBul');
    $numBoxcar = \filter_input(\INPUT_POST, 'numBoxcar');
    $numProwl = \filter_input(\INPUT_POST, 'numProwl');
    $numPushOver = \filter_input(\INPUT_POST, 'numPushOver');
    $numTigerText = \filter_input(\INPUT_POST, 'numTigerText');
    $userGroup = \filter_input(\INPUT_POST, 'userGroup');
    $numSysOpt = \filter_input(\INPUT_POST, 'numSysOpt');
    $numNotifSys = \filter_input(\INPUT_POST, 'numNotifSys');
    $userCis = \filter_input(\INPUT_POST, 'userCis');
    $userEml = \filter_input(\INPUT_POST, 'userEml');
    $cookieTime = time()+20*60;
    
    $pagerblock = array(
        $uid,
        $numPager, $numPagerSys,
        $numSms, $numSmsSys,
        $numTigerText,
        $numPushOver,
        $numPushBul,
        $numBoxcar,
        $numProwl,
        $numSysOpt,
        $numNotifSys,
        $cookieTime
    );
    $key = substr(str_shuffle('ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwyxz'),0,8); // no upper "I" or lower "l" to avoid confusion.
    $keytxt = simple_encrypt(implode(",", $pagerblock));
    file_put_contents('./logs/'.$key.'.blob', $keytxt);
    
    $mail = new PHPMailer;
    $mail->isSendmail();
    $mail->setFrom('pedcards@uw.edu', 'Heart Center Paging');
    $mail->addAddress($userEml);
    $mail->Subject = 'Heart Center Paging';
    $mail->isHTML(true);
    $mail->Body    = 'On '.date(DATE_RFC2822).'<br>'
            .'someone (hopefully you) made some proposed edits to your user information.<br><br>'
            .'<a href="http://depts.washington.edu/pedcards/paging3/change.php?do=1&id='.$key.'">AUTHORIZE</a> this change. '
            .'This link will expire in 20 minutes.<br><br>'
            .'If you do not approve, '
            .'<a href="http://depts.washington.edu/pedcards/paging3/change.php?do=0&id='.$key.'">DENY</a> it.<br><br>'
            .'<i>- The Management</i>';
    if (!$mail->send()) {
        dialog('ERROR', 'Red', 'Email error', '', 'dead_ipod.jpg', 'bummer', 'b', 'a', 'b');
    } else {
        dialog('NOTIFICATION', '', 'Confirmation email sent to', $userEml, 'sms-128.png', 'w00t', 'b', 'a', 'b');
    }
}

$key = \filter_input(\INPUT_GET,'id');
$do = \filter_input(\INPUT_GET,'do');
if ($key) {
    if ($do == '1') {
        $keytxt = file_get_contents('./logs/'.$key.'.blob');
        list(
            $uid,
            $numPager, $numPagerSys,
            $numSms, $numSmsSys,
            $numTigerText,
            $numPushOver,
            $numPushBul,
            $numBoxcar,
            $numProwl,
            $numSysOpt,
            $numNotifSys,
            $cookieTime
        ) = explode(",", simple_decrypt($keytxt));
        
        if (time()>$cookieTime) {
            unlink('./logs/'.$key.'.blob');
            dialog('ERROR', 'Red', 'Link expired', 'Try again', 'dead_ipod.jpg', 'bummer', 'b', 'a', 'a');
        }
        /*  This is where we will write to list.xml
         *  and email user with confirmation
         */
        $xml = simplexml_load_file("list.xml");
        $user = $xml->xpath("//user[@uid='".$uid."']")[0];
        $origDom = dom_import_simplexml($xml->xpath("//user[@uid='".$uid."']")[0])->cloneNode(true);
        $origXml = simplexml_import_dom($origDom);
        
        if ($numPager) {
            $user->pager['num'] = simple_encrypt($numPager);
            $user->pager['sys'] = $numPagerSys;
            $show .= compare('Pager', simple_decrypt($origXml->pager['num']), $numPager);
            $show .= preg_replace('/U/','USAM/Spok',preg_replace('/C/','Cook/AMS',compare('Pager sys', $origXml->pager['sys'], $numPagerSys)));
        } else {
            unset($user->pager);
        }
        if ($numSms) {
            $user->option->sms['num'] = simple_encrypt($numSms);
            $user->option->sms['sys'] = $numSmsSys;
            $show .= compare('SMS',simple_decrypt($origXml->option->sms['num']),$numSms);
            $show .= preg_replace('/A/','AT&T',preg_replace('/V/','Verizon',preg_replace('/T/','T-Mobile',compare('SMS sys',$origXml->option->sms['sys'],$numSmsSys))));
        } else {
            unset($user->option->sms);
        }
        if ($numPushBul) {
            $user->option->pushbul['eml'] = simple_encrypt($numPushBul);
            $show .= compare('Pushbullet',  simple_decrypt($origXml->option->pushbul['eml']),$numPushBul);
        } else {
            unset($user->option->pushbul);
        }
        if ($numPushOver) {
            $user->option->pushover['num'] = simple_encrypt($numPushOver);
            $show .= compare('Pushover',  simple_decrypt($origXml->option->pushover['num']),$numPushOver);
        } else {
            unset($user->option->pushover);
        }
        if ($numTigerText) {
            $user->option->tigertext['num'] = simple_encrypt($numTigerText);
            $show .= compare('TigerText',  simple_decrypt($origXml->option->tigertext['num']),$numTigerText);
        } else {
            unset($user->option->tigertext);
        }
        if ($numBoxcar) {
            $user->option->boxcar['num'] = simple_encrypt($numBoxcar);
            $show .= compare('Boxcar',  simple_decrypt($origXml->option->boxcar['num']),$numBoxcar);
        } else {
            unset($user->option->boxcar);
        }
        if ($numProwl) {
            $user->option->prowl['num'] = simple_encrypt($numProwl);
            $show .= compare('Prowl',  simple_decrypt($origXml->option->prowl['num']),$numProwl);
        } else {
            unset($user->option->prowl);
        }
        if ($numSysOpt) {
            $user->option['mode'] = $numSysOpt;
            $show .= preg_replace('/C/','Opt only',preg_replace('/B/','Pager+Opt',preg_replace('/A/','Pager only',compare('Option',$origXml->option['mode'],$numSysOpt))));
        } else {
            $user->option['mode'] = 'A';
        }
        if (($numNotifSys)&&($numNotifSys!=='Choose notification...')) {
            $user->option['sys'] = $numNotifSys;
            $show .= compare('System',$origXml->option['sys'],$numNotifSys);
        } else {
            unset($user->option['sys']);
        }
        if (strlen($show)) {
            mail(simple_decrypt($user->auth['eml']),
                "Heart Center Paging info confirmation",
                "The following changes were saved to your user account:\r\n".$show,
                "Bcc: pedcards@uw.edu"
            );
            logger(simple_decrypt($user->auth['cis']).' changed: '.$show);
        }
        $xml->asXML("list.xml");
        
        unlink('./logs/'.$key.'.blob');
        dialog('NOTIFICATION', '', 'Changes accepted!', 'Thank you!', 'sms-128.png', 'w00t', 'b', 'a', 'a');
    } else {
        unlink('./logs/'.$key.'.blob');
        dialog('DECLINED', '', 'Change denied', 'Try again', 'pager.jpg', '', 'b', 'a', 'a');
    }
}
dialog('GURU MEDITATION','red','','','dead_ipod.jpg','','b','a','b');
?>
</BODY>
</HTML>
