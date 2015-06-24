<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta name="viewport" content="initial-scale=1, width=device-width, user-scalable=no" />
<!--    Block for CDN copies of jquery/mobile. Consider fallback code on fail? -->
    <?php
    $cdnJqm = '1.4.5';
    $cdnJQ = '1.11.1';
    ?>
    <link rel="stylesheet" href="http://code.jquery.com/mobile/<?php echo $cdnJqm;?>/jquery.mobile-<?php echo $cdnJqm;?>.min.css" />
    <script src="http://code.jquery.com/jquery-<?php echo $cdnJQ;?>.min.js"></script>
    <script src="http://code.jquery.com/mobile/<?php echo $cdnJqm;?>/jquery.mobile-<?php echo $cdnJqm;?>.min.js"></script>
<!--==========================================-->
    <title>Paging v3</title>
</head>
<body>
<?php
$xml = simplexml_load_file("list.xml");
//if (!($xml->groups)) {
//    $xml->addChild('groups');
//}
$groups = ($xml->groups) ? $xml->groups : $xml->addChild('groups');

$edUserId = \filter_input(\INPUT_GET,'id');
if ($edUserId) {
    $user = $groups->xpath("//user[@uid='".$edUserId."']")[0];
    $nameL = $user['last'];
    $nameF = $user['first'];
    $numPager = $user->pager['num'];
    $numPagerSys = $user->pager['sys'];
    $numSms = $user->mobile['num'];
    $numSmsSys = $user->mobile['sys'];
    $numPushBul = $user->pushbul['eml'];
    $numPushOver = $user->pushover['num'];
    $numBoxcar = $user->boxcar['num'];
    $userGroup = $user->xpath('..')[0]->getName();
}
?>
<!-- Edit page -->
<div data-role="page" id="edit" data-dialog="true">
<div data-role="header">
    <h4 style="white-space: normal; text-align: center" ><?php echo ($edUserId) ? 'Edit User' : 'Add User';?></h4>
</div><!-- /header -->

<div data-role="content">
    <form method="post" action="back.php" data-ajax="false">
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
            <option value="CARDS" <?php echo ($userGroup=="CARDS") ? 'selected="selected"' : '';?>>Cardiologists</option>
            <option value="FELLOWS" <?php echo ($userGroup=="FELLOWS") ? 'selected="selected"' : '';?>>Fellows</option>
            <option value="SURG" <?php echo ($userGroup=="SURG") ? 'selected="selected"' : '';?>>CV Surgery</option>
            <option value="CICU" <?php echo ($userGroup=="CICU") ? 'selected="selected"' : '';?>>Cardiac ICU</option>
            <option value="MLP <?php echo ($userGroup=="MLP") ? 'selected="selected"' : '';?>">Mid-Level Providers</option>
            <option value="CATH" <?php echo ($userGroup=="CATH") ? 'selected="selected"' : '';?>>Cath Lab</option>
            <option value="CLINIC" <?php echo ($userGroup=="CLINIC") ? 'selected="selected"' : '';?>>Clinic, Soc Work, Nutrition</option>
            <option value="ECHO" <?php echo ($userGroup=="ECHO") ? 'selected="selected"' : '';?>>Echo Lab</option>
            <option value="ADMIN" <?php echo ($userGroup=="ADMIN") ? 'selected="selected"' : '';?>>Administration</option>
            <option value="DATA" <?php echo ($userGroup=="DATA") ? 'selected="selected"' : '';?>>Data & Research</option>
        </select>
        <input type="hidden" name="add" value="user">
        <button type="submit" class="ui-btn ui-corner-all ui-shadow ui-btn-b ui-btn-icon-left ui-icon-check" >Save</button>
    </form>
</div>

</div> <!-- end page -->

</body>

</html>