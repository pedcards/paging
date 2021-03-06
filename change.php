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
    require './lib/PHPMailerAutoload.php';
    
    function simple_encrypt($text, $salt = "") {
        if (!$salt) {
            global $instr; $salt = $instr;
        }
        if (!$text) {
            return $text;
        }
        return openssl_encrypt(
                $text, 
                'AES-128-CBC',
                $salt);
    }
    function simple_decrypt($text, $salt = "") {
        if (!$salt) {
            global $instr; $salt = $instr;
        }
        if (!$text) {
            return $text;
        }
        return openssl_decrypt(
                $text, 
                'AES-128-CBC',
                $salt);
    }
    function compare($field,$old,$new) {
        global $changes;
        if ($old==$new) {
            return '';
        } else {
            $changes[] = $field.':'.$new;
            return $field.": '".$old."' => '".$new."'\r\n";
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
        exit;
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
    function changed($uid) {
        global $val, $xml, $show;
        
        if (is_null($uid)) {
            return;
        }
        $xml = simplexml_load_file("list.xml");
            $user = $xml->xpath("//user[@uid='".$uid."']")[0];
            $origDom = dom_import_simplexml($user)->cloneNode(true);
            $origXml = simplexml_import_dom($origDom);
            
        $show .= printQ(compare('numPager', simple_decrypt($origXml->pager['num']), $val['numPager']),'<li>###</li>');
        $show .= printQ(preg_replace('/U/','USAM/Spok',preg_replace('/C/','Cook/AMS',compare('numPagersys', $origXml->pager['sys'], $val['numPagerSys']))),'<li>###</li>');
        $show .= printQ(compare('numSms',simple_decrypt($origXml->option->sms['num']),$val['numSms']),'<li>###</li>');
        $show .= printQ(preg_replace('/A/','AT&T',preg_replace('/V/','Verizon',preg_replace('/T/','T-Mobile',compare('numSmsSys',$origXml->option->sms['sys'],$val['numSmsSys'])))),'<li>###</li>');
        $show .= printQ(compare('numPushBul',  simple_decrypt($origXml->option->pushbul['eml']),$val['numPushBul']),'<li>###</li>');
        $show .= printQ(compare('numPushOver',  simple_decrypt($origXml->option->pushover['num']),$val['numPushOver']),'<li>###</li>');
        $show .= printQ(compare('numTigerText',  simple_decrypt($origXml->option->tigertext['num']),$val['numTigerText']),'<li>###</li>');
        $show .= printQ(compare('numBoxcar',  simple_decrypt($origXml->option->boxcar['num']),$val['numBoxcar']),'<li>###</li>');
        $show .= printQ(compare('numProwl',  simple_decrypt($origXml->option->prowl['num']),$val['numProwl']),'<li>###</li>');
        $show .= printQ(preg_replace('/C/','Opt only',preg_replace('/B/','Pager+Opt',preg_replace('/A/','Pager only',compare('numSysOpt',$origXml->option['mode'],$val['numSysOpt'])))),'<li>###</li>');
        $show .= printQ(compare('numNotifSys',$origXml->option['sys'],$val['numNotifSys']),'<li>###</li>');
        
        return $show;
    }
    function printQ($txt,$str='###') {
        if ($txt=='') {
            return '';
        } else {
            return preg_replace('/###/',$txt,$str);
        }
    }
    function makeblob($uid,$obj) {
        $cookieTime = time()+20*60;
        $key = substr(str_shuffle('ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwyxz'),0,8); // no upper "I" or lower "l" to avoid confusion.
        $keytxt = simple_encrypt(
            implode(',',
                array(
                    $uid,
                    simple_encrypt(implode(",", $obj)),
                    $cookieTime
                )
            )
        );
        $ret = (object) [
            'key'   => $key,
            'blob'  => $keytxt
        ];
        return $ret;
    }

/*  Begin the script
 * 
 */
$uid = \filter_input(\INPUT_POST, 'uid');
$key = \filter_input(\INPUT_GET,'id');
$do = \filter_input(\INPUT_GET,'do');
$group = \filter_input(\INPUT_POST, 'GROUP');
$oncall = \filter_input(\INPUT_GET, 'call');

/*  Clean out any leftover blob files
 */
foreach (glob('./logs/*.blob') as $fname) {
    $fmdate = filemtime($fname);
    if ((time()-$fmdate) > (20*60)) {
        unlink($fname);
        logger('Removed '.$fname);
    }
}

if ($uid) {
/*  If directed from edit.php, read the form input
 *  create the "cookie" (crypted values, key, and expiration time)
 *  Send mail to affected user.
 */
    $val['nameL'] = \filter_input(\INPUT_POST, 'nameL');
    $val['nameF'] = \filter_input(\INPUT_POST, 'nameF');
    $val['numPager'] = \filter_input(\INPUT_POST, 'numPager');
    $val['numPagerSys'] = \filter_input(\INPUT_POST, 'numPagerSys');
    $val['numSms'] = \filter_input(\INPUT_POST, 'numSms');
    $val['numSmsSys'] = \filter_input(\INPUT_POST, 'numSmsSys');
    $val['numPushBul'] = \filter_input(\INPUT_POST, 'numPushBul');
    $val['numBoxcar'] = \filter_input(\INPUT_POST, 'numBoxcar');
    $val['numProwl'] = \filter_input(\INPUT_POST, 'numProwl');
    $val['numPushOver'] = \filter_input(\INPUT_POST, 'numPushOver');
    $val['numTigerText'] = \filter_input(\INPUT_POST, 'numTigerText');
    $val['userGroup'] = \filter_input(\INPUT_POST, 'userGroup');
    $val['numSysOpt'] = \filter_input(\INPUT_POST, 'numSysOpt');
    $val['numNotifSys'] = \filter_input(\INPUT_POST, 'numNotifSys');
    $val['userCis'] = \filter_input(\INPUT_POST, 'userCis');
    $val['userEml'] = \filter_input(\INPUT_POST, 'userEml');
    $val['cookieTime'] = time()+20*60;
    
    $show = changed($uid);
    if ($show) {
        $blob = makeblob($uid,$changes);
        $key = $blob->key;
        $keytxt = $blob->blob;
        file_put_contents('./logs/'.$key.'.blob', $keytxt);
        
        $mail = new PHPMailer;
        $mail->isSendmail();
        $mail->setFrom('pedcards@uw.edu', 'Heart Center Paging');
        $mail->addAddress($val['userEml']);
        $mail->Subject = 'Heart Center Paging';
        $mail->isHTML(true);
        $mail->Body    = 'On '.date(DATE_RFC2822).', '
                .'someone (hopefully you) made some proposed edits to your user information.<br><br>'
                .'<blockquote><ul>'.$show.'</ul></blockquote><br>'
                .'<a href="http://depts.washington.edu/pedcards/'.basename(getcwd()).'/change.php?do=1&id='.$key.'">AUTHORIZE</a> this change. '
                .'This link will expire in 20 minutes.<br><br>'
                .'If you do not approve, '
                .'<a href="http://depts.washington.edu/pedcards/'.basename(getcwd()).'/change.php?do=0&id='.$key.'">DENY</a> it.<br><br>'
                .'<i>- The Management</i>';
        if (!$mail->send()) {
            logger('Email error sending to '.$val['userEml']);
            dialog('ERROR', 'Red', 'Email error', '', 'dead_ipod.jpg', 'bummer', 'b', 'a', 'a');
        } else {
            logger('Change notification sent to '.$val['userEml']);
            dialog('NOTIFICATION', '', 'Confirmation email sent to', $val['userEml'], 'sms-128.png', 'w00t', 'b', 'a', 'a');
        }
    }
} else if ($key) {
/*  Accept or reject the blob change 
 */
    if ($do == '1') {
        list(
            $uid,
            $keytxt,
            $val['cookieTime']
        ) = explode(",", simple_decrypt(file_get_contents('./logs/'.$key.'.blob')));
        
        if (time()>$val['cookieTime']) {
            unlink('./logs/'.$key.'.blob');
            logger('Blob '.$key.' expired.');
            dialog('ERROR', 'Red', 'Link expired', 'Try again', 'dead_ipod.jpg', 'bummer', 'b', 'a', 'a');
        }
        /*  This is where we will write to list.xml
         *  and email user with confirmation
         */
        $xml = simplexml_load_file("list.xml");
        $user = $xml->xpath("//user[@uid='".$uid."']")[0];
        
        $changes = explode(',', simple_decrypt($keytxt));
        foreach ($changes as $el) {
            list($label,$value) = explode(':',$el);
            $val[$label] = $value;
        }
        if ($val['numPager']) {
            $user->pager['num'] = simple_encrypt($val['numPager']);
        }
        if ($val['numPagerSys']) {
            $user->pager['sys'] = $val['numPagerSys'];
        }
        if ($val['numSms']) {
            $user->option->sms['num'] = simple_encrypt($val['numSms']);
        }
        if ($val['numSmsSys']) {
            $user->option->sms['sys'] = $val['numSmsSys'];
        }
        if ($val['numPushBul']) {
            $user->option->pushbul['eml'] = simple_encrypt($val['numPushBul']);
        }
        if ($val['numPushOver']) {
            $user->option->pushover['num'] = simple_encrypt($val['numPushOver']);
        }
        if ($val['numTigerText']) {
            $user->option->tigertext['num'] = simple_encrypt($val['numTigerText']);
        }
        if ($val['numBoxcar']) {
            $user->option->boxcar['num'] = simple_encrypt($val['numBoxcar']);
        }
        if ($val['numProwl']) {
            $user->option->prowl['num'] = simple_encrypt($val['numProwl']);
        }
        if ($val['numSysOpt']) {
            $user->option['mode'] = $val['numSysOpt'];
        }
        if (($val['numNotifSys'])&&($val['numNotifSys']!=='Choose notification...')) {
            $user->option['sys'] = $val['numNotifSys'];
        }
        logger(simple_decrypt($user->auth['cis']).' changed: '.$show);
        
        copy('list.xml','lists/'.date('YmdHis').'.xml');
        $xml->asXML("list.xml");
        
        unlink('./logs/'.$key.'.blob');
        logger('Changes saved, '.$key.' blob file unlinked.');
        dialog('NOTIFICATION', '', 'Changes accepted!', 'Thank you!', 'sms-128.png', 'w00t', 'b', 'a', 'a');
    } else {
        unlink('./logs/'.$key.'.blob');
        logger('Blob file '.$key.' unlinked.');
        dialog('DECLINED', '', 'Change denied', 'Try again', 'pager.jpg', '', 'b', 'a', 'a');
    }
} else if ($oncall) {
    $serv = [
        'CARDS'     => 'PM_We_A',
        'FELLOWS'   => 'PM_We_F',
        'ECHO'      => 'Echo_Tech'
    ];
    $chipdir = (basename(getcwd())=='paging') ? '../patlist/' : '../testlist/';
    $xml = simplexml_load_file("list.xml");
    $user = $xml->xpath("//user[@uid='".$oncall."']")[0];
    $userEml = simple_decrypt($user->auth['eml']);
    
    if ($do=='1') {                         // commit the change blob
        // Decrypt blob
        list(
            $uid,
            $keytxt,
            $cookieTime
        ) = explode(",", simple_decrypt(file_get_contents('./logs/'.$oncall.'.blob')));
        
        // Decrypt keytxt
        list(
            $call_dt,
            $name,
            $svc
        ) = explode(",", simple_decrypt($keytxt));
        
        // Check for expired cookie
        if (time()>$cookieTime) {
            unlink('./logs/'.$oncall.'.blob');
            logger('Blob '.$oncall.' expired.');
            dialog('ERROR', 'Red', 'Link expired', 'Try again', 'dead_ipod.jpg', 'bummer', 'b', 'a', 'a');
        }
        
        // Make change in currlist
        $chip = simplexml_load_file($chipdir."currlist.xml");
        $chip->lists->forecast->xpath("call[@date='".$call_dt."']")[0]->$svc = $name;
        $chip->asXML($chipdir."currlist.xml");
        
        // Create/append change file
        $chg = (simplexml_load_file($chipdir."change.xml")) ?: new SimpleXMLElement('<root />');     // load change.xml if exists or start <root> in local memory
        $node = $chg[0]->addChild('node');
        $node['type'] = 'call';
        $node['MRN'] = $name;
        $node['change'] = $svc;
        $node['date'] = $call_dt;
        $chg->asXML($chipdir."change.xml");
        
        unlink('./logs/'.$oncall.'.blob');
        logger('Call changes saved. '.$oncall.' unlinked.');
        dialog('NOTIFICATION', '', 'Changes accepted!', 'Thank you!', 'sms-128.png', 'w00t', 'b', 'a', 'a');
    } else if ($do=='0') {
        unlink('./logs/'.$oncall.'.blob');
        logger('Blob file '.$oncall.' unlinked.');
        dialog('DECLINED', '', 'Change denied', 'Try again', 'pager.jpg', '', 'b', 'a', 'a');
    } else {                                // set the change blob
        $changes = [
            'date'  => date('Ymd'),
            'name'  => $user['first'].' '.$user['last'],
            'svc'   => $serv[$group]
        ];
        $blob = makeblob($oncall,$changes);
        $key = $blob->key;
        $keytxt = $blob->blob;
        file_put_contents('./logs/'.$key.'.blob', $keytxt);
        
        $mail = new PHPMailer;
        $mail->isSendmail();
        $mail->setFrom('pedcards@uw.edu', 'Heart Center Paging');
        $mail->addAddress($userEml);
        $mail->Subject = 'Heart Center Paging';
        $mail->isHTML(true);
        $mail->Body    = 'On '.date(DATE_RFC2822).', '
                .'someone (hopefully you) proposed that you are on-call for '
                .'<blockquote><ul>'.$serv[$group].'</ul></blockquote><br>'
                .'<a href="http://depts.washington.edu/pedcards/'.basename(getcwd()).'/change.php?do=1&call='.$key.'">AUTHORIZE</a> this change. '
                .'This link will expire in 20 minutes.<br><br>'
                .'If you do not approve, '
                .'<a href="http://depts.washington.edu/pedcards/'.basename(getcwd()).'/change.php?do=0&call='.$key.'">DENY</a> it.<br><br>'
                .'<i>- The Management</i>';
        if (!$mail->send()) {
            logger('Email error sending to '.$userEml);
            dialog('ERROR', 'Red', 'Email error', '', 'dead_ipod.jpg', 'bummer', 'b', 'a', 'a');
        } else {
            logger('Change notification sent to '.$userEml);
            dialog('NOTIFICATION', '', 'Confirmation email sent to', $userEml, 'sms-128.png', 'w00t', 'b', 'a', 'a');
        }
    }
} else {
    logger('Guru Meditation');
    dialog('GURU MEDITATION','red','','','dead_ipod.jpg','','b','a','a');
}
?>
</BODY>
</HTML>
