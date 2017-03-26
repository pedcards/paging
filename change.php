<!DOCTYPE html>
<?php session_start();?>
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
    function dialog($title,$tcolor,$msg1,$msg2,$img,$alt,$bar,$fg,$bg) {
        ?>
        <div data-role="page" data-dialog="true" id="dialog-fn" data-overlay-theme="<?php echo $bg;?>">
            <div data-role="header" data-theme="<?php echo $bar;?>">
                <h1 style="color:<?php echo $tcolor;?>"><?php echo $title;?></h1>
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

/*  If directed from edit.php, read the form input
 *  create the "cookie" (crypted values, key, and expiration time)
 *  Send mail to affected user.
 */
if (stripos($_SERVER['HTTP_REFERER'],'edit.php')) {
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
    $userID = \filter_input(\INPUT_POST, 'uid');
    $matchUser = \filter_input(\INPUT_POST, 'match');
    $cookieTime = time()+20*60;
    
    $pagerblock = array(
        $userID,
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
    $key = substr(str_shuffle('ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwyxz'),0,12); // no upper "I" or lower "l" to avoid confusion.
    $keytxt = simple_encrypt(implode(",", $pagerblock));
    $_SESSION[$key] = $keytxt;
    
    require './lib/PHPMailerAutoload.php';
    $mail = new PHPMailer;
    $mail->isSendmail();
    $mail->setFrom('pedcards@uw.edu', 'Heart Center Paging');
    $mail->addAddress($userEml);
    $mail->Subject = 'Heart Center Paging';
    $mail->isHTML(true);
    $mail->Body    = 'On '.date(DATE_RFC2822).'<br>'
            .'someone (hopefully you) made some edits to your user information.<br><br>'
            .'<a href="http://depts.washington.edu/pedcards/paging3/change.php?do=1&id='.$key.'">AUTHORIZE</a> this change.<br><br>'
            .'If you do not approve, '
            .'<a href="http://depts.washington.edu/pedcards/paging3/change.php?do=0&id='.$key.'">DENY</a> it.<br><br>'
            .'<i>- The Management</i>';
    if (!$mail->send()) {
        dialog('ERROR', 'Red', 'Email error', '', 'dead_ipod.jpg', 'bummer', 'b', 'a', 'b');
    } else {
        dialog('NOTIFICATION', '', 'Confirmation email sent to', $userEml, '', '', 'b', 'a', 'b');
    }
    exit;
}
/*  This section at end for committing to list.xml
 * 
 */
$xml = (simplexml_load_file("list.xml")) ?: new SimpleXMLElement("<root />");
$groups = ($xml->groups) ?: $xml->addChild('groups');
?>
</BODY>
</HTML>