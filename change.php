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
    ?>
        <title>Heart Center Paging</title>
    </head>
<body>

<?php
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
        $numNotifSys
    );
    $key = substr(str_shuffle('ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwyxz'),0,12); // no upper "I" or lower "l" to avoid confusion.
    $keytxt = simple_encrypt(implode(",", $pagerblock));
}
/*  This section at end for committing to list.xml
 * 
 */
$xml = (simplexml_load_file("list.xml")) ?: new SimpleXMLElement("<root />");
$groups = ($xml->groups) ?: $xml->addChild('groups');
?>
</BODY>
</HTML>
