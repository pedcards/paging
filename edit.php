<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta name="viewport" content="initial-scale=1, width=device-width, user-scalable=no" />
<!--    Block for CDN copies of jquery/mobile. Consider fallback code on fail? -->
    <?php
    $isLoc = true;
    $cdnJqm = '1.4.5';
    $cdnJQ = '1.11.1';
    
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
    ?>
</head>
<body>
<?php
$xml = simplexml_load_file("list.xml");
$groups = ($xml->groups) ?: $xml->addChild('groups');
$groupfull = array(
    'CARDS' => 'Cardiologists',
    'FELLOWS' => 'Fellows',
    'SURG' => 'CV Surgery',
    'CICU' => 'Cardiac ICU',
    'MLP' => 'Mid Level Providers',
    'CATH' => 'Cath Lab',
    'CLINIC' => 'Clinic RN, Soc Work, Nutrition',
    'ECHO' => 'Echo Lab',
    'ADMIN' => 'Admin Office',
    'DATA' => 'Research, Data'
    );

$edUserId = \filter_input(\INPUT_GET,'id');
if ($edUserId) {
    $user = $groups->xpath("//user[@uid='".$edUserId."']")[0];
    $nameL = $user['last'];
    $nameF = $user['first'];
    $sec = $user['sec'];
    $numPager = $user->pager['num'];
    $numPagerSys = $user->pager['sys'];
    $numSms = $user->sms['num'];
    $numSmsSys = $user->sms['sys'];
    $numPushBul = $user->pushbul['eml'];
    $numPushOver = $user->pushover['num'];
    $numBoxcar = $user->boxcar['num'];
    $userGroup = $user->xpath('..')[0];
    $userGroupName = $userGroup->getName();
}
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
    <?php if ($edUserId) { echo '    <a href="#delConf" data-rel="popup" data-position-to="window" class="ui-btn ui-shadow ui-btn-icon-left ui-icon-forbidden ui-corner-all" >DELETE</a>';}?>

</div><!-- /header -->

<div data-role="content">
    <form method="post" id="edForm" action="back.php" data-ajax="false">
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
                    <input name="numPagerSys" id="addPagerSys-a" type="radio" value="COOK" <?php echo ($numPagerSys=="COOK") ? 'checked="checked"' : '';?>>
                    <label for="addPagerSys-a">Cook</label>
                    <input name="numPagerSys" id="addPagerSys-b" type="radio" value="USAM" <?php echo ($numPagerSys=="USAM") ? 'checked="checked"' : '';?>>
                    <label for="addPagerSys-b">USA-M</label>
                </fieldset>
            </div>
        </div>
        <div class="ui-grid-a">
            <div class="ui-block-a" style="padding-right:10px;">
                <input name="numSms" id="addSmsNum" value="<?php echo $numSms;?>" placeholder="SMS (10-digits)" pattern="[0-9]{10}" type="text">
            </div>
            <div class="ui-block-b" style="padding-top:2px;">
                <fieldset data-role="controlgroup" data-type="horizontal" class="ui-mini">
                    <input name="numSmsSys" id="addSmsSys-a" type="radio" value="ATT" <?php echo ($numSmsSys=="ATT") ? 'checked="checked"' : '';?>>
                    <label for="addSmsSys-a">AT&amp;T</label>
                    <input name="numSmsSys" id="addSmsSys-b" type="radio" value="Sprint" <?php echo ($numSmsSys=="Sprint") ? 'checked="checked"' : '';?>>
                    <label for="addSmsSys-b">Sprint</label>
                </fieldset>
            </div>
        </div>
        <input name="numPushBul" id="addPushBul" value="<?php echo $numPushBul;?>" placeholder="Pushbullet email" type="text">
        <input name="numPushOver" id="addPushOver" value="<?php echo $numPushOver;?>" placeholder="Pushover user code" type="text">
        <input name="numBoxcar" id="addBoxcar" value="<?php echo $numBoxcar;?>" placeholder="Boxcar user code" type="text">
        <select name="userGroup" id="addGroup" data-native-menu="false">
            <option>Choose group</option>
            <?php
            foreach($groupfull as $grp => $grpStr) {
                echo '<option value="'.$grp.'" '.(($userGroupName==$grp) ? 'selected="selected"' : '').'">'.$grpStr.'</option>';
            }?>
        </select>
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
