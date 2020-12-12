<?php session_start();?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="icon" type="image/png" href="favicon.png" />
    <link rel="apple-touch-icon" href="images/pager.png" />
    <link href="" rel="apple-touch-startup-image" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-capable" content="no" />
    <meta name="viewport" content="initial-scale=1, width=device-width, user-scalable=no" />
    <!--==========================================-->
    <?php
    $ini = parse_ini_file("paging.ini");
    $isLoc = true;
    $cdnJqm = $ini['jqm'];
    $cdnJQ = $ini['jquery'];
    $instr = $ini['copyright'];
    
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
        $names = explode(" ", $in, 2);
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
    function eventlog($text) {
        global $logfile;
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
                $text
            )
        ); 
        fclose($out);
    }
    
    ?>
    <link rel="stylesheet" href="<?php echo (($isLoc) ? './jqm' : 'http://code.jquery.com/mobile/'.$cdnJqm).'/jquery.mobile-'.$cdnJqm;?>.min.css" />
    <script src="<?php echo (($isLoc) ? './jqm/' : 'http://code.jquery.com/').'jquery-'.$cdnJQ;?>.min.js"></script>
    <script src="<?php echo (($isLoc) ? './jqm' : 'http://code.jquery.com/mobile/'.$cdnJqm).'/jquery.mobile-'.$cdnJqm;?>.min.js"></script>
    <!--==========================================-->

    <script type="text/javascript" src="./jqm/jqm-windows.alertbox.min.js"></script>
    <script type="text/javascript">
        function clearMru() {
            document.cookie = "pagemru=; expires=-1";
            location.reload();
        }
    </script>

    <title>Paging v3</title>
</head>
<body>
<?php
$group = filter_input(INPUT_GET,'group');
$modDate = \date("m/d/Y", filemtime("list.xml"));
$xml = simplexml_load_file("list.xml");
$groups = $xml->groups;
$groupfull = array();
foreach ($groups->children() as $grp0) {
    $groupfull[$grp0->getName()] = $grp0->attributes()->full;
}
$logfile = 'logs/'.date('Ym').'.csv';
$_SESSION['valid'] = filter_input(INPUT_COOKIE, 'authcookie');

// comment this out to run without check
// remove comments to reset to fresh state
//    $_SESSION['valid']=''; setcookie('authcookie','');

$authName = strtolower(\filter_input(INPUT_POST, 'user'));
if ($authName) {
    $users = $groups->xpath("//user/auth");
    foreach ($users as $user0) {
        $userauth[simple_decrypt($user0->attributes()->cis)] = simple_decrypt($user0->attributes()->eml);
    }
    $authChk = ($userauth[$authName]=='') ? '' : $authName;
    
    $_SESSION['valid']= $authChk;
    setcookie('authcookie',$authChk,time()+3*86400);
    
    eventlog(
        'Login '.$authName.(($authChk=='') 
            ? ' failed.' 
            : ' success.'
            )
        );
    
}
if (\filter_input(INPUT_POST,'clearck')=="y"){
    setcookie('pagemru',null,-1);
}
$pagealert = filter_input(INPUT_COOKIE, 'pagealert');
$alerttext = $ini['announce'];
$texthash = md5($alerttext);
setcookie('pagealert', $texthash, time()+30*86400);
$call = array(
    'Ward_A', 'Ward_F',
    'ICU_A', 'ICU_F',
    'CICU_Red', 'CICU_Blue',
    'Reg_Con',
    'EP',
    'Cath_res',
    'Txp_ICU',
    'Txp_Ward',
    'Txp_res',
    'Fetal',
    'ARNP_IP',
    'ARNP_OP',
    'ARNP_CL',
    'South_Sound_Cardiology'
);
$chip = simplexml_load_file('../patlist/currlist.xml');
$call_dt = date("Ymd");
$call_d = date("l");
$call_t = date("H");
if ((preg_match('/(Saturday|Sunday)/',$call_d)) or ($call_t >= 17 || $call_t < 8)) {
    $call = array(
        'PM_We_A', 'PM_We_F',
        ($call_t >= 17 || $call_t < 8) ? 'CICU_PM' : 'CICU_Red',
        'EP',
        'Txp_ICU',
        'ARNP_IP',
        'Echo_Tech',
        'Fetal',
        'South_Sound_Cardiology'
    );
}
if ($call_t < 8) {
    $call_dt = date("Ymd", time()-60*60*24);
}

$fc_call = $chip->lists->forecast->xpath("call[@date='".$call_dt."']")[0];

if ($_SESSION['valid']=='') {
    ?>
    <div data-role="page" id="auth1" data-dialog="true" data-close-btn="none">
        <div data-role="header">
            <h4 style="white-space: normal; text-align: center" >Seattle Children's<br>Heart Center<br>Paging</h4>
        </div>
        <div data-role="content">
            <form method="post" action="#">
                <input name="user" id="authName" placeholder="Enter your CIS login name" type="text" >
                <button type="submit" class="ui-btn ui-corner-all ui-shadow ui-btn-b" >OK</button>
            </form>
        </div>
    </div> 
    <?php
} else { 
    setcookie('authcookie',$_SESSION['valid'],time()+3*86400);
    ?>

<!-- Start of first page -->
<div data-role="page" id="main">
    <div data-role="panel" id="search" data-display="overlay">
        <form class="ui-filterable">
            <input id="auto-editUser" data-type="search" placeholder="Find user...">
        </form>
        <div style="margin-bottom: 24px;">
        <ul data-role="listview" data-filter="true" data-filter-reveal="true" data-input="#auto-editUser" data-inset="false" data-theme="b">
            <?php
            // auto reveal items from search bar
            $liUsers = $xml->xpath('//user');
            $liGroupOld = "";
            foreach($liUsers as $liUser) {
                $liNameL = $liUser['last'];
                $liNameF = $liUser['first'];
                $liUserId = $liUser['uid'];
                $liPgr = $liUser->pager['num'];
                $liGroup = $liUser->xpath('..')[0]->getName();
                if ($liPgr) {
                    echo '            <li class="ui-mini">';
                    echo '<a href="proc.php?group='.$liGroup.'&id='.$liUserId.'" data-ajax="false"><i>'.$liNameL.', '.$liNameF.'</i><span style="font-size:x-small" class="ui-li-count">'.$liGroup.'</span></a>';
                    echo '</li>'."\r\n";
                }
            }
            ?>
        </ul>
        </div>
        <div data-role="collapsibleset" data-inset="false">
        <div data-role="collapsible" data-inset="false" data-mini="true" data-collapsed="true" data-collapsed-icon="phone">
            <h4>On call: <?php echo date("D m/d/Y");?></h4>
        <ul data-role="listview">
            <?php
            foreach($call as $callU){
                $chName = $fc_call->$callU;
                if ($callU=='Ward_A' && $chName=='') {
                    $callU = 'PM_We_A';
                    $chName = $fc_call->$callU;
                }
                if ($callU=='Ward_F' && ($fc_call->Ward_A == '')) {
                    $callU = 'PM_We_F';
                    $chName = $fc_call->$callU;
                }
                if ($callU=='EP') {
                    if ($call_d=='Friday' && $call_t>=17) {
                        $chName = $chip->lists->forecast->xpath("call[@date='".date("Ymd",time()+60*60*24)."']/EP")[0];
                    }
                    if ($call_d=='Saturday') {
                        $chName = $chip->lists->forecast->xpath("call[@date='".date("Ymd",time())."']/EP")[0];
                    }
                }
                if ($callU=='South_Sound_Cardiology') {
                    // $chName = 'South Sound On-Call: '.$chName;
                    $callU = 'South Sound';
                }
                if ($chName=='') {
                    continue;
                }
                
                $liUserId = getUid($chName);
                if (! $liUserId) {
                    $liUserId = fuzzyname($chName)['uid'];
                    $chName = "'".$chName."'";
                }
                $liUser = $xml->xpath("//user[@uid='".$liUserId."']")[0];
                $liGroup = $liUser->xpath('..')[0]->getName();
                echo '            <li class="ui-mini">';
                echo '<a href="proc.php?group='.$liGroup.'&id='.$liUserId.'" data-ajax="false"><b>'.$callU.':</b><i> '.$chName.'</i></a>';
                echo '</li>'."\r\n";
            }
            ?>
        </ul>
        </div>
        <div data-role="collapsible" data-inset="false" data-mini="true" data-collapsed="true" data-collapsed-icon="clock">
            <?php
            if (filter_input(INPUT_COOKIE,'pagemru')){
                echo '            <h4>Recently used numbers</h4>';
            }
            ?>
        <form method="post" id="clearcookie" action="index.php" data-ajax="false">
            <input type="hidden" name="clearck" value="y">
        </form>
        <ul data-role="listview">
            <?php
            // show cookies
            $cookie = explode(",", filter_input(INPUT_COOKIE,'pagemru'));
            foreach($cookie as $cvals){
                if ($cvals==''){
                    continue;
                }
                $ckUser = $xml->xpath("//user[@uid='".$cvals."']")[0];
                if (!$ckUser) {
                    setcookie('pagemru',null,-1);
                    break;
                }
                $ckCt ++;
                $ckUserId = $ckUser['uid'];
                $ckGroup = $ckUser->xpath("..")[0]->getName();
                echo '<li><a href="proc.php?group='.$ckGroup.'&id='.$cvals.'" data-ajax="false">'.$ckUser['first'].' '.$ckUser['last'].'</a></li>'."\r\n";
            }
            if ($ckCt) {
                echo '<li data-icon="delete"><a href="" onclick="clearMru();">Clear list</a></li>'."\r\n";
            }
            ?>
        </ul>
        </div>
        </div>
    </div>
    <div data-role="panel" id="info" data-display="overlay" data-position="right">
        <ul data-role="listview" data-inset="false">
            <li data-icon="info"><a href="#messPopup" data-rel="popup" data-position-to="window" data-transition="pop">Last system message</a></li>
            <li data-icon="cloud"><a href="notifs.php" data-transition="slide">Notification services</a></li>
            <li data-icon="gear"><a href="back.php">User administration</a></li>
            <li data-icon="location"><a>IP: <?php echo $_SERVER['REMOTE_ADDR'];?></a></li>
        </ul>
        <div data-role="popup" id="messPopup" >
            <div data-role="header" >
                <h4>System message</h4>
            </div>
            <div data-role="main" class="ui-content">
                <?php echo $alerttext;?>
            </div>
        </div>
    </div>

    <div data-role="header" data-theme="b" >
        <h4 style="white-space: normal; text-align: center" >Heart Center Paging</h4>
        <a href="#search" class="ui-btn ui-shadow ui-icon-search ui-btn-icon-notext ui-corner-all" >Search panel</a>
        <a href="#info" class="ui-btn ui-shadow ui-icon-bullets ui-btn-icon-notext ui-corner-all" data-ajax="false">return to main</a>
    </div><!-- /header -->

    <div data-role="content">
    <ul data-role="listview">
        <?php
        foreach($groupfull as $grp => $grpStr) {
            echo '<li><a href="proc.php?group='.$grp.'">'.$grpStr.'</a></li>';
        }
        ?>
    </ul>
    <?php if (($pagealert) && ($pagealert!==$texthash)) { ?>
        <div class="ui-content jqm-alert-box" data-alertbox-close-time="20000" data-alertbox-transition="fade" data-role="popup" data-theme="a" data-overlay-theme="b" id="popupOpts" >
            <a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
            <?php echo $alerttext;?>
        </div> 
    <?php } ?>
    </div>

    <div data-role="footer" >
        <h5><small>&COPY;(2007-<?php echo date('Y');?>) Terrence Chun, MD<br></small></h5>
    </div><!-- /footer -->
</div><!-- /page -->

<?php } ?>

</body>
</html>
