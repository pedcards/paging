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
        <title>Heart Center Contacts</title>
    </head>
<body>
    <?php
    $browser = $_SERVER['HTTP_USER_AGENT'];
    $phone = preg_match('/(iPhone|Android|Windows Phone)/i',$browser);
    $chip = simplexml_load_file('../patlist/currlist.xml');
    $fc_call = $chip->lists->forecast->xpath("call[@date='".$call_dt."']")[0];
    $call = array(
        'Ward_A',
        'ICU_A',
        'CICU',
        'EP'
    );
    $call_dt = date("Ymd");
    $call_d = date("l");
    $call_t = date("H");
    if ((preg_match('/(Saturday|Sunday)/i',$call_d)) or ($call_t >= 17 || $call_t < 8)) {
        $call = array(
            'PM_We_A',
            ($call_t >= 17 || $call_t < 8) ? 'CICU_PM' : 'CICU',
            'EP'
        );
    }
    if ($call_t < 8) {
        $call_dt = date("Ymd", time()-60*60*24);
    }
    
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
    function getUid($in) {
        global $xml;
        $trans = array(
            "Terry" => "Terrence",
            "Steve" => "Stephen",
            "Tom" => "Thomas",
            "Jenny" => "Jennifer",
            "Matt" => "Matthew",
            "John" => "Jonathon",
            "Mike" => "Michael",
            "Katherine" => "Katie"
        );
        $names = explode(" ", $in);
        $el = $xml->xpath("//user[@last='".$names[1]."' and (@first='".$names[0]."' or @first='".strtr($names[0],$trans)."')]")[0];
        return $el['uid'];
    }
    function fuzzyname($str) {
        global $xml;
        $users = $xml->xpath('//user');
        $shortest = -1;
        foreach ($users as $user) {
            $name = $user['first']." ".$user['last'];
            $lev = levenshtein($str, $name);
            if ($lev == 0) {
                $closest = $name;
                $shortest = 0;
                $uid = $user['uid'];
                break;
            }
            if ($lev <= $shortest || $shortest < 0) {
                $closest = $name;
                $shortest = $lev;
                $uid = $user['uid'];
            }
        }
        $user = $xml->xpath("//user[@uid='".$uid."']")[0];
        return array('first'=>$user['first'], 'last'=>$user['last'], 'uid'=>$user['uid']);
    }
    ?>
    
    <div data-role="panel" id="info" data-display="overlay" data-position="right">
        <ul data-role="listview" data-inset="false">
            <li data-icon="location"><a>IP: <?php echo $_SERVER['REMOTE_ADDR'];?></a></li>
            <li data-icon="eye"><a href="#ua_Popup" data-rel="popup" data-position-to="window" data-transition="pop">Browser</a></li>
            <li data-icon="info"><a href="#info_Popup" data-rel="popup" data-position-to="window" data-transition="pop">About this thing...</a></li>
        </ul>
        <div data-role="popup" id="ua_Popup" >
            <div data-role="header" >
                <h4>User Agent string</h4>
            </div>
            <div data-role="main" class="ui-content">
                <?php echo $browser.'<br>'.!$phone;?>
            </div>
        </div>
        <div data-role="popup" id="info_Popup" >
            <div data-role="header" >
                <h4>About this thing...</h4>
            </div>
            <div data-role="main" class="ui-content">
                Yeah, about this thing.<br>
                I mean, really.
            </div>
        </div>
    </div>

    <div data-role="header" data-theme="b" >
        <h4 style="white-space: normal; text-align: center" >Heart Center Contacts</h4>
        <a href="#info" class="ui-btn ui-shadow ui-icon-bullets ui-btn-icon-notext ui-corner-all ui-btn-right" data-ajax="false">return to main</a>
    </div><!-- /header -->
    
    <div data-role="content">
        <a href="contactproc.php?group=SURG&id=55b948fa1c644" class="ui-btn ui-mini">Page Jonathon</a>
        <?php if ($phone) { echo '<a href="#" class="ui-btn ui-mini">Text Jonathon</a>'; }?>
        <br>
        <a href="contactproc.php?group=CARDS&id=55b948fa18a52" class="ui-btn ui-mini">Page Mark</a>
        <br>
        <a href="#" class="ui-btn ui-mini">Page CICU Attending<?php echo !$phone ? ' ' : '<br>';?>On-Call: Harris</a>
        <a href="#" class="ui-btn ui-mini">Page ICU Consult Cardiologist<?php echo (!$phone) ? ' ' : '<br>';?>On-Call: Tim</a>
        <a href="#" class="ui-btn ui-mini">Page Ward Consult Cardiologist<?php echo (!$phone) ? ' ' : '<br>';?>On-Call: Tim, too</a>
        <a href="#" class="ui-btn ui-mini">Page EP Attending<?php echo (!$phone) ? ' ' : '<br>';?>On-Call: Terry, again</a>
        <br>
        <a href="#" class="ui-btn ui-mini">MEDCON/Transport<br>206-987-8899</a>
        <a href="#" class="ui-btn ui-mini">Physician Consult Line<br>206-987-7777</a>
        <br>
        <a href="#" class="ui-btn ui-mini">Surgical/Procedure Coordinators<br>206-987-2198</a>
        <a href="#" class="ui-btn ui-mini">Prenatal Center<br>206-987-xxxx</a>
        <a href="#" class="ui-btn ui-mini">Regional Liaison: Emily<br>206-987-xxxx</a>
        <a href="#" class="ui-btn ui-mini">Regional Liaison: Anya<br>206-987-xxxx</a>
    </div>

</body>
</html>
