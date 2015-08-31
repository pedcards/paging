<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta name="viewport" content="initial-scale=1, width=device-width, user-scalable=no" />
<!--==========================================-->
    <?php
    $isLoc = true;
    $cdnJqm = '1.4.5';
    $cdnJQ = '1.11.1';
    $instr = "(c)2007-2015 by Terrence Chun, MD.";
    
    ?>
    <link rel="stylesheet" href="<?php echo (($isLoc) ? './jqm' : 'http://code.jquery.com/mobile/'.$cdnJqm).'/jquery.mobile-'.$cdnJqm;?>.min.css" />
    <script src="<?php echo (($isLoc) ? './jqm/' : 'http://code.jquery.com/').'jquery-'.$cdnJQ;?>.min.js"></script>
    <script src="<?php echo (($isLoc) ? './jqm' : 'http://code.jquery.com/mobile/'.$cdnJqm).'/jquery.mobile-'.$cdnJqm;?>.min.js"></script>
<!--==========================================-->
    <title>Paging v3</title>
    
    <?php
function swapUser($user1, $user2)
{
    global $userGroup;
    $dom = dom_import_simplexml($userGroup);
    
    $new = $dom->insertBefore(
        dom_import_simplexml($userGroup->xpath("//user[@uid='".$user2."']")[0]),
        dom_import_simplexml($userGroup->xpath("//user[@uid='".$user1."']")[0])
    );
    return simplexml_import_dom($new);
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

    ?>
</head>
<body>
<?php
$isAdmin = false;
$xml = simplexml_load_file("list.xml");
$groups = ($xml->groups) ?: $xml->addChild('groups');
foreach ($groups->children() as $grp0) {
    $groupfull[$grp0->getName()] = $grp0->attributes()->full;
}
if (\filter_input(\INPUT_GET, 'auth') == '1') {
    ?>
    <div data-role="page" id="auth1" data-dialog="true">
        <div data-role="header">
            <h4 style="white-space: normal; text-align: center" >Edit authorization</h4>
            <a href="#" data-rel="back" class="ui-btn ui-shadow ui-btn-icon-left ui-icon-delete ui-btn-icon-notext ui-corner-all">go back</a>
        </div>
        <div data-role="content">
            <form method="post" action="?auth=2">
                <input name="auth" id="authName" placeholder="CIS login name" type="text" >
                <button type="submit" class="ui-btn ui-corner-all ui-shadow ui-btn-b">Submit</button>
            </form>
        </div>
    </div>
    
    <?php
}
if (\filter_input(\INPUT_GET, 'auth') == '2') {
    $authName = \filter_input(\INPUT_POST, 'auth');
    $users = $groups->xpath("//user/auth");
    foreach ($users as $user0) {
        $userauth[simple_decrypt($user0->attributes()->cis)] = simple_decrypt($user0->attributes()->eml);
    }
    $eml = $userauth[$authName];
    if (!$eml) {
        ?>
        <div data-role="page" id="noAuth" data-dialog="true">
            <div data-role="header">
                <h4 style="white-space: normal; text-align: center" >ERROR</h4>
                <a href="#" data-rel="back" class="ui-btn ui-shadow ui-btn-icon-left ui-icon-delete ui-btn-icon-notext ui-corner-all">go back</a>
            </div>
            <div data-role="content">
                <a href="#" data-rel="back" class="ui-btn ui-shadow ui-corner-all">Go back</a>
            </div>
        </div>
        <?php
    }
    $key = 'BRQA';
//    mail($eml, 
//            "Heart Center Paging", 
//            "Someone (hopefully you) has requested access to edit user information.\r\n\r\n"
//            .'The access token is "'.$key.'"'."\r\n\r\n"
//            ."The code will self-destruct in 20 minutes.\r\n"
//            ."Please act responsibly."
//            );
    $cookieTime = time()+5*60;
    setcookie("pageedit", simple_encrypt($authName,$key), $cookieTime);
    setcookie("pageeditT",$cookieTime);
    ?>
    <div data-role="page" id="auth2" data-dialog="true">
        <div data-role="header">
            <h4 style="white-space: normal; text-align: center" >Enter auth code <?php echo $ref;?></h4>
            <a href="#" data-rel="back" class="ui-btn ui-shadow ui-btn-icon-left ui-icon-delete ui-btn-icon-notext ui-corner-all">go back</a>
        </div>
        <div data-role="content">
            <form method="post" action="back.php">
                <input name="auth" id="authCode" placeholder="" type="text" >
                <input name="authname" type="hidden" id="authUser" value="<?php echo $authName;?>">
                <button type="submit" class="ui-btn ui-corner-all ui-shadow ui-btn-b">Submit</button>
            </form>
        </div>
    </div>
    
    <?php
}

$edUserId = \filter_input(\INPUT_GET,'id');
    $user = ($edUserId) ? $groups->xpath("//user[@uid='".$edUserId."']")[0] : '';
    $nameL = ($edUserId) ? $user['last'] : '';
    $nameF = ($edUserId) ? $user['first'] : '';
    $sec = ($edUserId) ? $user['sec'] : '';
    $numPager = ($edUserId) ? simple_decrypt($user->pager['num']) : '';
    $numPagerSys = ($edUserId) ? $user->pager['sys'] : '';
    $numSms = ($edUserId) ? simple_decrypt($user->sms['num']) : '';
    $numSmsSys = ($edUserId) ? $user->sms['sys'] : '';
    $numPushBul = ($edUserId) ? simple_decrypt($user->pushbul['eml']) : '';
    $numPushOver = ($edUserId) ? simple_decrypt($user->pushover['num']) : '';
    $numBoxcar = ($edUserId) ? simple_decrypt($user->boxcar['num']) : '';
    $numSysOpt = ($edUserId) ? $user->option['mode'] : 'A';
    $numNotifSys = ($edUserId) ? $user->option['sys'] : '';
    $userGroup = ($edUserId) ? $user->xpath('..')[0] : '';
    $userGroupName = ($edUserId) ? $userGroup->getName() : '';

if (\filter_input(\INPUT_GET, 'move') == 'Y') {
    $moveway = \filter_input(\INPUT_POST,'action');
    if ($moveway=='up') {
        swapUser(
            \filter_input(\INPUT_POST,'userPre'),
            $edUserId
        );
        $xml->asXML("list.xml");
    }
    if ($moveway=='down') {
        swapUser(
            $edUserId,
            \filter_input(\INPUT_POST,'userFol')
        );
        $xml->asXML("list.xml");
    }
    $userP1 = $user->xpath("preceding-sibling::user[1]")[0];
    $userP1name = ($userP1['sec']) ?: $userP1['last'].", ".$userP1['first'];
    $userF1 = $user->xpath('following-sibling::user[1]')[0];
    $userF1name = ($userF1['sec']) ?: $userF1['last'].", ".$userF1['first'];

?>
<div data-role="page" id="move" data-dialog="true">
    <div data-role="header">
        <h4 style="text-align: center">Reorder users</h4>
        <a href="back.php" class="ui-btn ui-shadow ui-btn-icon-left ui-icon-delete ui-btn-icon-notext ui-corner-all" data-ajax="false">go back</a>
    </div>

    <div data-role="content">
        <ul data-role="listview">
            <?php if ($userP1) { echo '<li>'.$userP1name.'</li>';}?>
            <li><b>---&nbsp;<?php echo $nameL.', '.$nameF;?></b></li>
            <?php if ($userF1) { echo '<li>'.$userF1name.'</li>';}?>

        </ul>

    </div>

    <div data-role="footer">
        <form method="post" action="#">
            <input type="hidden" name="userPre" value="<?php echo $userP1['uid'];?>">
            <input type="hidden" name="userFol" value="<?php echo $userF1['uid'];?>">
            <button type="submit" style="width: 100%" <?php echo ($userP1) ?: 'disabled=""';?> class="ui-btn ui-corner-all ui-shadow ui-btn-b ui-btn-icon-left ui-icon-carat-u" name="action" value="up">UP</button>
            <button type="submit" style="width: 100%" <?php echo ($userF1) ?: 'disabled=""';?> class="ui-btn ui-corner-all ui-shadow ui-btn-b ui-btn-icon-left ui-icon-carat-d" name="action" value="down">DOWN</button>

        </form>
    </div>
</div>
<?php
}
?>
<!-- Edit page -->
<div data-role="page" id="edit" data-dialog="true">
<div data-role="header">
    <h4 style="white-space: normal; text-align: center" ><?php echo ($edUserId) ? 'Edit User' : 'Add User';?></h4>
    <a href="back.php" class="ui-btn ui-shadow ui-btn-icon-left ui-icon-delete ui-btn-icon-notext ui-corner-all">go back</a>
    <?php if ($edUserId && $isAdmin) { echo '    <a href="#delConf" data-rel="popup" data-position-to="window" class="ui-btn ui-shadow ui-btn-icon-left ui-icon-forbidden ui-corner-all" >DELETE</a>';}?>

</div><!-- /header -->

<div data-role="content">
    <form method="post" id="edForm" action="back.php" data-ajax="false">
        <div class="ui-corner-all custom-corners">
            <div class="ui-bar ui-bar-a">
                <h3>Paging info</h3>
            </div>
            <div class="ui-bar ui-bar-a">
                <div class="ui-grid-a">
                    <div class="ui-block-a" style="padding-right:10px;">
                        <input name="nameF" id="addNameF" value="<?php echo $nameF;?>" placeholder="First name" type="text" >
                    </div>
                    <div class="ui-block-b">
                        <input name="nameL" id="addNameL" value="<?php echo $nameL;?>" placeholder="Last name" type="text">
                    </div>
                </div>
                <div class="ui-grid-a">
                    <div class="ui-block-a" style="padding-right:10px;">
                        <input name="numPager" id="addPagerNum" value="<?php echo $numPager;?>" placeholder="Pager (10-digits)" pattern="(206)[0-9]{7}" type="text">
                    </div>
                    <div class="ui-block-b" style="padding-top:2px;">
                        <fieldset data-role="controlgroup" data-type="horizontal" class="ui-mini">
                            <input name="numPagerSys" id="addPagerSys-a" type="radio" value="C" <?php echo ($numPagerSys=="C") ? 'checked="checked"' : '';?>>
                            <label for="addPagerSys-a">Cook</label>
                            <input name="numPagerSys" id="addPagerSys-b" type="radio" value="U" <?php echo ($numPagerSys=="U") ? 'checked="checked"' : '';?>>
                            <label for="addPagerSys-b">USA-M</label>
                        </fieldset>
                    </div>
                </div>
                <select name="userGroup" id="addGroup" data-native-menu="false">
                    <option>Choose group</option>
                    <?php
                    foreach($groupfull as $grp => $grpStr) {
                        echo '<option value="'.$grp.'" '.(($userGroupName==$grp) ? 'selected="selected"' : '').'">'.$grpStr.'</option>';
                    }?>
                </select>
            </div>
        </div>
        <fieldset data-role="controlgroup" data-type="horizontal">
            <input name="numSysOpt" id="addSysOpt-a" data-mini="true" type="radio" value="A" <?php echo (($numSysOpt=="A")||($numSysOpt=="")) ? 'checked="checked"' : '';?>>
            <label for="addSysOpt-a">&nbsp;&nbsp;Pager&nbsp;&nbsp;</label>
            <input name="numSysOpt" id="addSysOpt-b" data-mini="true" type="radio" value="B" <?php echo ($numSysOpt=="B") ? 'checked="checked"' : '';?>>
            <label for="addSysOpt-b">Pager+Opt</label>
            <input name="numSysOpt" id="addSysOpt-c" data-mini="true" type="radio" value="C" <?php echo ($numSysOpt=="C") ? 'checked="checked"' : '';?>>
            <label for="addSysOpt-c">&nbsp;Opt only&nbsp;</label>
        </fieldset>
        <div class="ui-corner-all custom-corners">
            <div class="ui-bar ui-bar-a">
                <h3>Optional alert systems</h3>
            </div>
            <div class="ui-bar ui-bar-a" >
                <select name="numNotifSys" id="addOptSys" data-mini="true" data-native-menu="false">
                    <option >Choose notification...</option>
                    <option value="nul">None</option>
                    <option value="sms" <?php echo ($numNotifSys=="sms") ? 'selected="selected"':'';?>>Text message</option>
                    <option value="pbl" <?php echo ($numNotifSys=="pbl") ? 'selected="selected"':'';?>>Pushbullet</option>
                    <option value="pov" <?php echo ($numNotifSys=="pov") ? 'selected="selected"':'';?>>Pushover</option>
                    <option value="bxc" <?php echo ($numNotifSys=="bxc") ? 'selected="selected"':'';?>>Boxcar</option>
                </select>
                <div class="ui-grid-a">
                    <div class="ui-block-a" style="padding-right:10px;">
                        <input name="numSms" id="addSmsNum" value="<?php echo $numSms;?>" placeholder="SMS (10-digits)" pattern="[0-9]{10}" type="text">
                    </div>
                    <div class="ui-block-b" style="padding-top:2px;">
                        <select name="numSmsSys" id="addSmsSys" data-mini="true" data-native-menu="false">
                            <option>Choose carrier</option>
                            <option value="A" <?php echo ($numSmsSys=="A") ? 'selected="selected"':'';?>>AT&amp;T</option>
                            <option value="V" <?php echo ($numSmsSys=="V") ? 'selected="selected"':'';?>>Verizon</option>
                            <option value="T" <?php echo ($numSmsSys=="T") ? 'selected="selected"':'';?>>T-Mobile</option>
                        </select>
                    </div>
                </div>
                <div class="ui-field-contain">
                    <label for="addPushBul">Pushbullet</label>
                    <input name="numPushBul" id="addPushBul" value="<?php echo $numPushBul;?>" placeholder="Pushbullet email" type="text">
                </div>
                <div class="ui-field-contain">
                    <label for="addPushOver">Pushover</label>
                    <input name="numPushOver" id="addPushOver" value="<?php echo $numPushOver;?>" placeholder="Pushover user code" type="text">
                </div>
                <div class="ui-field-contain">
                    <label for="addBoxcar">Boxcar</label>
                    <input name="numBoxcar" id="addBoxcar" value="<?php echo $numBoxcar;?>" placeholder="Boxcar user code" type="text">
                </div>
            </div>
        </div>
        <input type="hidden" name="add" value="<?php echo ($edUserId) ? 'edit' : 'user';?>">
        <input type="hidden" name="uid" value="<?php echo $edUserId;?>">
        <button type="submit" class="ui-btn ui-corner-all ui-shadow ui-btn-b ui-btn-icon-left ui-icon-check" name="save" value="y">Save</button>
    </form>
</div>

<div data-role="popup" id="delConf">
    <div data-role="header">
        <h2>REALLY DELETE?</h2>
    </div>
    <div data-role="content">
        <a href="#" class="ui-btn ui-btn-b ui-corner-all" onclick="$('form#edForm').trigger('submit');" >YES</a>
        <br/>
        <a href="#" data-rel="back" class="ui-btn ui-btn-b ui-corner-all" >NO!</a>
    </div>
</div>

</div> <!-- end page -->

</body>

</html>
