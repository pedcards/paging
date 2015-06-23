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
$edUser = \filter_input(\INPUT_GET,'id');
$xml = simplexml_load_file("list.xml");
if (!($xml->groups)) {
    $xml->addChild('groups');
}
$groups = $xml->groups;
$add = \filter_input(\INPUT_POST, 'add');
if ($add=="user") {
    $nameL = \filter_input(\INPUT_POST, 'nameL');
    $nameF = \filter_input(\INPUT_POST, 'nameF');
    $numPager = \filter_input(\INPUT_POST, 'numPager');
    $numPagerSys = \filter_input(\INPUT_POST, 'numPagerSys');
    $numSms = \filter_input(\INPUT_POST, 'numSms');
    $numSmsSys = \filter_input(\INPUT_POST, 'numSmsSys');
    $numPushBul = \filter_input(\INPUT_POST, 'numPushBul');
    $userGroup = \filter_input(\INPUT_POST, 'userGroup');
    if ($groups->xpath("//user[@name='".$nameL."']")) {
        dialog('User already exists!');
    }
    if ($nameF=="" or $nameL=="") {
        $err = "Full name required<br>";
    }
    if ($numPager=="") {
        $err .= "Pager number required<br>";
    }
    if ($numPagerSys=="") {
        $err .= "Paging system required<br>";
    }
    if ($userGroup=="Choose group") {
        $err .= "Group required<br>";
    }
    if ($err) {
        dialog($err);
    } else {                                            // No errors, write 
        if (!($groups->$userGroup)) {
            $groups->addChild('group',$userGroup);
        }
        $groupThis = $groups->$userGroup;
        if (!($groupThis->xpath("user[@name='".$nameL."']"))) {
            $user = $groupThis->addChild('user');
            $user['name'] = $nameL;
            $user['first'] = $nameF;
        }
        $user = $groupThis->xpath("user[@name='".$nameL."']");
        if ($numPager) {
            $user[0]->addChild('pager');
            $user[0]->pager->addChild('number',$numPager);
            $user[0]->pager->addChild('sys',$numPagerSys);
        }
        if ($numSms) {
            $user[0]->addChild('sms');
            $user[0]->sms->addChild('number',$numSms);
            $user[0]->sms->addChild('sys',$numSmsSys);
        }
        if ($numPushBul) {
            $user[0]->addChild('pushbul');
            $user[0]->pushbul->addChild('number',$numPushBul);
        }
    }
    $xml->asXML("list.xml");
}

?>
<!-- Edit page -->
<div data-role="page" id="edit" data-dialog="true">
<div data-role="header">
    <h4 style="white-space: normal; text-align: center" >Edit user <?php echo $edUser;?></h4>
</div><!-- /header -->

<div data-role="content">
    <?php
        $nameF=""; $nameL=""; 
    ?>
    <p style="text-align: center">ADD USER</p>
    <form method="post" action="back.php" data-ajax="false">
        <div class="ui-grid-a">
            <div class="ui-block-a" style="padding-right:10px;">
                <input name="nameF" id="addNameF" value="" placeholder="First name" type="text" >
            </div>
            <div class="ui-block-b">
                <input name="nameL" id="addNameL" value="" placeholder="Last name" type="text">
            </div>
        </div>
        <div class="ui-grid-a">
            <div class="ui-block-a" style="padding-right:10px;">
                <input name="numPager" id="addPagerNum" value="" placeholder="Pager (10-digits)" pattern="(206)[0-9]{7}" type="text">
            </div>
            <div class="ui-block-b" style="padding-top:2px;">
                <fieldset data-role="controlgroup" data-type="horizontal" class="ui-mini">
                    <input name="numPagerSys" id="addPagerSys-a" type="radio" value="COOK">
                    <label for="addPagerSys-a">Cook</label>
                    <input name="numPagerSys" id="addPagerSys-b" type="radio" value="USAM">
                    <label for="addPagerSys-b">USA-M</label>
                </fieldset>
            </div>
        </div>
        <div class="ui-grid-a">
            <div class="ui-block-a" style="padding-right:10px;">
                <input name="numSms" id="addSmsNum" value="" placeholder="SMS (10-digits)" pattern="[0-9]{10}" type="text">
            </div>
            <div class="ui-block-b" style="padding-top:2px;">
                <fieldset data-role="controlgroup" data-type="horizontal" class="ui-mini">
                    <input name="numSmsSys" id="addSmsSys-a" type="radio" value="ATT">
                    <label for="addSmsSys-a">AT&amp;T</label>
                    <input name="numSmsSys" id="addSmsSys-b" type="radio" value="Sprint">
                    <label for="addSmsSys-b">Sprint</label>
                </fieldset>
            </div>
        </div>
        <input name="numPushBul" id="addPushBul" value="" placeholder="Pushbullet email" type="text">
        <input name="numPushOver" id="addPushOver" value="" placeholder="Pushover user code" type="text">
        <input name="numBoxcar" id="addBoxcar" value="" placeholder="Boxcar user code" type="text">
        <select name="userGroup" id="addGroup" data-native-menu="false">
            <option>Choose group</option>
            <option value="CARDS">Cardiologists</option>
            <option value="FELLOWS">Fellows</option>
            <option value="SURG">CV Surgery</option>
            <option value="CICU">Cardiac ICU</option>
            <option value="MLP">Mid-Level Providers</option>
            <option value="CATH">Cath Lab</option>
            <option value="CLINIC">Clinic, Soc Work, Nutrition</option>
            <option value="ECHO">Echo Lab</option>
            <option value="ADMIN">Administration</option>
            <option value="DATA">Data & Research</option>
        </select>
        <input type="hidden" name="add" value="user">
        <button type="submit" class="ui-btn ui-corner-all ui-shadow ui-btn-b ui-btn-icon-left ui-icon-check" >Save</button>
    </form>
</div>

</div> <!-- end page -->

</body>

</html>