<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="icon" type="image/png" href="favicon.png" />
    <link rel="apple-touch-icon" href="favicon.png" />
    <link href="" rel="apple-touch-startup-image" />
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
    <!--<script type="text/javascript" src="./jqm/jqm-alertbox.min.js"></script>-->

    <title>Paging v3</title>
<?php
?>
</head>
<body>
<?php
$xml = (simplexml_load_file("list.xml")) ?: new SimpleXMLElement("<root />");
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

$add = \filter_input(\INPUT_POST, 'add');
$save = \filter_input(\INPUT_POST, 'save');
$import = \filter_input(INPUT_POST, 'import');

if (($save!=='y') and ($uid = \filter_input(\INPUT_POST, 'uid'))) {
    // $save exists but not 'y', must have clicked Delete.
    $user = $groups->xpath("//user[@uid='".$uid."']")[0];
    unset($user[0]);
    $xml->asXML("list.xml");
    $add = '';
}
if ($add) {
    $nameL = \filter_input(\INPUT_POST, 'nameL');
    $nameF = \filter_input(\INPUT_POST, 'nameF');
    $numPager = \filter_input(\INPUT_POST, 'numPager');
    $numPagerSys = \filter_input(\INPUT_POST, 'numPagerSys');
    $numSms = \filter_input(\INPUT_POST, 'numSms');
    $numSmsSys = \filter_input(\INPUT_POST, 'numSmsSys');
    $numPushBul = \filter_input(\INPUT_POST, 'numPushBul');
    $numBoxcar = \filter_input(\INPUT_POST, 'numBoxcar');
    $numPushOver = \filter_input(\INPUT_POST, 'numPushOver');
    $userGroup = \filter_input(\INPUT_POST, 'userGroup');
    if ($add=="user") {
        !($groups->xpath("//user[@last='".$nameL."' and @first='".$nameF."']")) ?: errmsg('User already exists!');
    }
        $err = ($nameF=="" or $nameL=="") ? "Full name required<br>" : '';
        $err .= ($numPager=="") ? "Pager number required<br>" : '';
        $err .= ($numPagerSys=="") ? "Paging system required<br>" : '';
        $err .= ($userGroup=="Choose group") ? "Group required<br>" : '';
    if ($err) {
        errmsg($err);
    } else {                                                // No errors, write
        $groupThis = ($groups->$userGroup) ?: $groups->addChild($userGroup);
        $user = ($groupThis->xpath("user[@last='".$nameL."' and @first='".$nameF."']")[0]) ?: $groupThis->addChild('user');
            $user['last'] = $nameL;
            $user['first'] = $nameF;
            $user['uid'] = uniqid();
        ($user->pager) ?: $user->addChild('pager');
            $user->pager['num'] = $numPager;
            $user->pager['sys'] = $numPagerSys;
        ($user->sms) ?: $user->addChild('sms');
            $user->sms['num'] = $numSms;
            $user->sms['sys'] = $numSmsSys;
        ($user->pushbul) ?: $user->addChild('pushbul');
            $user->pushbul['eml'] = $numPushBul;
        ($user->pushover) ?: $user->addChild('pushover');
            $user->pushover['num'] = $numPushOver;
        ($user->boxcar) ?: $user->addChild('boxcar');
            $user->boxcar['num'] = $numBoxcar;
        foreach ($groupThis->user as $userSort) {
            if (strcasecmp($userSort['last'].', '.$userSort['first'], $nameL.', '.$nameF) > 0) {
                swapUser($userSort['uid'], $user['uid']);
                break;
            }
        }
        foreach($groupfull as $grp => $grpStr) {
            ($groups->$grp) ?: $groups->addChild($grp);
            $domgrp = $groups->$grp;
            $dom_All = dom_import_simplexml($groups[0]);
            $dom_grp = dom_import_simplexml($domgrp[0]);
            $dom_new = $dom_All->appendChild($dom_grp);
            simplexml_import_dom($dom_new);
        }
    $xml->asXML("list.xml");
    }
}
if ($import) {
    // Read "list.csv" into array
    //
    // TODO: Special element for section headers
    $imXml = new SimpleXMLElement("<root />");
    $arrLine = array();
    $pagerblock = "";
    $row = 0;
    $imXml->addChild('groups');
    if (($handle = fopen("list.csv", "r")) !== FALSE) {
        while (($arrLine[] = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $tmpGroup = $arrLine[$row][0];
            if ($tmpGroup == 'Group') {
                $tmpGroup = '';
            }
            if ($tmpGroup == '') {
                $row++;
                continue;
            }
            usleep(1);
            $tmpLastName = $arrLine[$row][1];
            $tmpFirstName = $arrLine[$row][2];
            $tmpPageSys = $arrLine[$row][3];
            $tmpPageNum = $arrLine[$row][4];
            $tmpCellSys = $arrLine[$row][5];
            $tmpCellNum = $arrLine[$row][6];
            $tmpCellOpt = $arrLine[$row][7];
            $tmpKey = $arrLine[$row][8];
            if (substr($tmpLastName, 0, 3)==":::") {
                $tmpSection = substr($tmpLastName, 4);
                $tmpLastName = ":::";
                $tmpFirstName = ":::";
            } else { 
                $tmpSection = "";
            }

            $tmpUserGrp = ($imXml->groups->$tmpGroup) ?: $imXml->groups->addChild($tmpGroup);
            $tmpUser = $tmpUserGrp->addChild('user');
                $tmpUser['last'] = $tmpLastName;
                $tmpUser['first'] = $tmpFirstName;
                if ($tmpSection) {
                    $tmpUser['sec'] = $tmpSection;
                }
                $tmpUser['uid'] = uniqid();
            $tmpUser->addChild('pager');
                $tmpUser->pager['num'] = $tmpPageNum;
                $tmpUser->pager['sys'] = $tmpPageSys;
            if ($tmpCellNum) {
                $tmpUser->addChild('sms');
                $tmpUser->sms['num'] = $tmpCellNum;
                $tmpUser->sms['sys'] = $tmpCellSys;
            }
            if ($tmpCellOpt) {
                $tmpUser->addChild('option');
                $tmpUser->option['mode'] = $tmpCellOpt;
            }
            $row++;
        } // Finish loop to get lines
    } 
    fclose($handle);
    $tmpListName = date('YmdHis').'.xml';
    $imXml->asXML($tmpListName);
    ?>
    <div data-role="page" id="importConf" data-overlay-theme="b">
        <div data-role="header">
            <h4 style="white-space: normal; text-align: center" >Replace list.csv</h4>
        </div>
        <div data-role="content">
        <?php
            $msg = (copy('list.xml','list.'.date('YmdHis').'.bak')) ? 'List.xml backed up</br>' : 'Failed to backup list.xml</br>';
            $msg .= (copy($tmpListName,'list.xml')) ? 'List.xml successfully replaced</br>' : 'Failed to replace list.xml</br>';
            echo '<a href="back.php" class="ui-btn ui-btn-b" data-ajax="false">'.$msg.'</a>';
        ?>
        </div>
    </div>
<?php
}
function errmsg($msg) {
?>
    <div data-role="page" id="dialogWin">
        <div data-role="header">
            <h2>ERROR!</h2>
        </div>
        <div data-role="content">
            <a href="#" data-rel="back" class="ui-btn ui-btn-b ui-corner-all">
                <?php echo "<br>".$msg."<br><br>[click to go back]<br>";?>
            </a>
        </div>
    </div>
<?php
}
function swapUser($user1, $user2)
{
    global $groupThis;
    $dom = dom_import_simplexml($groupThis);
    
    $new = $dom->insertBefore(
        dom_import_simplexml($groupThis->xpath("//user[@uid='".$user2."']")[0]),
        dom_import_simplexml($groupThis->xpath("//user[@uid='".$user1."']")[0])
    );
    return simplexml_import_dom($new);
}
?>

<!-- Start of first page -->
<div data-role="page" id="main" >

<div data-role="header">
        <h4 style="white-space: normal; text-align: center" >User Manager</h4>
        <a href="index.php" class="ui-btn ui-shadow ui-btn-icon-left ui-icon-back ui-btn-icon-notext ui-corner-all" data-ajax="false">return to main</a>
</div><!-- /header -->

<div data-role="content">
    <a href="edit.php" class="ui-btn ui-icon-plus ui-btn-icon-left">Add a user</a>
    <form class="ui-filterable">
        <input id="auto-editUser" data-type="search" placeholder="Search...">
    </form>
    <ul data-role="listview" data-filter="true" data-filter-reveal="" data-input="#auto-editUser" data-inset="true">
        <?php
        $edUsers = $xml->xpath('//user');
        $edGroupOld = "";
        foreach($edUsers as $edUser) {
            $edNameL = $edUser['last'];
            $edNameF = $edUser['first'];
            $edUserId = $edUser['uid'];
            $edSection = $edUser['sec'];
            $edGroup = $edUser->xpath('..')[0]->getName();
            if (!($edGroup==$edGroupOld)) {
                echo "\r\n".'        <li data-role="list-divider">'.$edGroup.'</li>'."\r\n";
                $edGroupOld = $edGroup;
            }
            echo '            <li class="ui-mini">';
            echo '<a href="edit.php?id='.$edUserId.'" ><i>'.(($edSection) ? ('::: '.$edSection.' :::') : ($edNameL.', '.$edNameF)).'</i></a>';
            echo '<a href="edit.php?id='.$edUserId.'&move=Y" class="ui-btn ui-icon-recycle">Reorder user</a>';
            echo '</li>'."\r\n";
            // perhaps use Session variable?
        }
        ?>
    </ul>
    <a href="#import" class="ui-btn">Import CSV</a>
</div>

<div data-role="footer" >
        <h5><small>
&COPY;(2007-2015) Terrence Chun, MD<br>
        </small></h5>
    </div><!-- /footer -->

</div><!-- /page -->

<div data-role="page" id="import">
    <div data-role="header">
        <h4 style="white-space: normal; text-align: center" >Import CSV</h4>
    </div>
    <div data-role="content">
        <form method="post" id="importform" action="#">
            <button type="submit" class="ui-btn ui-btn-a" name="import" value="y">Yes, import list.csv file.</button>
        </form>
        <a href="back.php" class="ui-btn ui-btn-b " data-ajax="false">NO, GET ME OUT OF HERE!</a>
    </div>
</div>

</body>
</html>
