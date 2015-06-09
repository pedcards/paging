<!DOCTYPE html>
<html>
<head>
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="icon" type="image/png" href="favicon.png" />
    <link rel="apple-touch-icon" href="favicon.png" />
    <link href="" rel="apple-touch-startup-image" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="viewport" content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" />
<!--    Block for CDN copies of jquery/mobile. Consider fallback code on fail? -->
    <link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
    <script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
<!--==========================================-->
<!-- Block for local copies of jquery/mobile. 
    <link rel="stylesheet" href="./jqm/jquery.mobile-1.4.5.min.css" />
    <script src="./jqm/jquery-1.11.1.min.js"></script>
    <script src="./jqm/jquery.mobile-1.4.5.min.js"></script>
<!--==========================================-->
    <!--<script type="text/javascript" src="./jqm/jqm-alertbox.min.js"></script>-->
    <script>
            $(document).bind("mobileinit", function(){
                    $.mobile.defaultPageTransition = 'none';
            });
    </script>
    
    <script type="text/javascript">
    // from http://web.enavu.com/daily-tip/maxlength-for-textarea-with-jquery/
        $(document).ready(function() {  
            $('textarea[maxlength]').keyup(function(){  //get the limit from maxlength attribute  
                var limit = parseInt($(this).attr('maxlength'));  //get the current text inside the textarea  
                var text = $(this).val();  //count the number of characters in the text  
                var chars = text.length;  //check if there are more characters then allowed  
                if(chars > limit){  //and if there are use substr to get the text before the limit  
                    var new_text = text.substr(0, limit);  //and change the current text with the new text  
                    $(this).val(new_text);  
                }  
            });  
        });  
    </script>
    
    <title>Paging v3</title>
</head>
<body>
<?php
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

$groupfull = array(
    'CARDS' => 'Cardiologists',
    'FELLOWS' => 'Fellows',
    'SURG' => 'Surgery',
    'CICU' => 'CICU',
    'ARNP' => 'ARNP',
    'CATH' => 'Cath Lab',
    'CLINIC' => 'Clinic RN, Soc Work',
    'ECHO' => 'Echo Lab',
    'ADMIN' => 'Admin Office',
    'DATA' => 'Research, Data'
    );

function str_rot($s, $n = -1) {
    //Rotate a string by a number.
    static $letters = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789.,!$*+-?@#'; //To be able to de-obfuscate your string the length of this needs to be a multiple of 4 AND no duplicate characters
    $letterLen=round(strlen($letters)/2);
    if($n==-1) {
        $n=(int)($letterLen/2); 
    }//Find the "halfway rotate point"
    $n = (int)$n % ($letterLen);
    if (!$n) {
        return $s;
    }
    if ($n < 0) {
        $n += ($letterLen);
    }
    //if ($n == 13) return str_rot13($s);
    $rep = substr($letters, $n * 2) . substr($letters, 0, $n * 2);
    return strtr($s, $letters, $rep);
}

function dialog($msg) {
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
?>

<!-- Start of first page -->
<div data-role="page" id="main" >

<div data-role="header">
        <h4 style="white-space: normal; text-align: center" >User Manager</h4>
</div><!-- /header -->

<div data-role="content">
    <a href="#addUser" data-rel="popup" data-position-to="window" data-transition="pop" class="ui-btn ui-icon-plus ui-btn-icon-left">Add a user</a>
    <form class="ui-filterable">
        <input id="auto-editUser" data-type="search" placeholder="Enter user name">
    </form>
    <ul data-role="listview" data-filter="true" data-filter-reveal="true" data-input="#auto-editUser" data-inset="true">
        <?php
        $edUsers = $xml->xpath('//user');
        $edGroupOld = "";
        foreach($edUsers as $edUser) {
            $edNameL = $edUser['name'];
            $edNameF = $edUser['first'];
            $edGroup = $edUser->xpath('..')[0]->getName();
            if (!($edGroup==$edGroupOld)) {
                echo "\r\n".'        <li data-role="list-divider">'.$edGroup.'</li>'."\r\n";
                $edGroupOld = $edGroup;
            }
            echo '            <li class="ui-mini">';
            echo '<a href="#edit?nm='.$edNameL.'-'.$edNameF.'"><i>'.$edNameL.', '.$edNameF.'</i></a>';
            echo '</li>'."\r\n";
        }
        ?>
    </ul>
</div>

<div data-role="footer" >
        <h5><small>
&COPY;(2007-2015) Terrence Chun, MD<br>
        </small></h5>
    </div><!-- /footer -->

<!--Add a new user-->
<div data-role="popup" id="addUser" style="padding:10px 20px;">
    <p style="text-align: center">ADD USER</p>
    <form method="post" action="#" data-ajax="false">
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
                    <input name="numPagerSys" id="addPagerSys-a" type="radio" value="COOK">
                    <label for="addPagerSys-a">Cook</label>
                    <input name="numPagerSys" id="addPagerSys-b" type="radio" value="USAM">
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
                    <input name="numSmsSys" id="addSmsSys-a" type="radio" value="ATT">
                    <label for="addSmsSys-a">AT&amp;T</label>
                    <input name="numSmsSys" id="addSmsSys-b" type="radio" value="Sprint">
                    <label for="addSmsSys-b">Sprint</label>
                </fieldset>
            </div>
        </div>
        <input name="numPushBul" id="addPushBul" value="<?php echo $numPushBul;?>" placeholder="Pushbullet email" type="text">
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

</div><!-- /page -->

<!-- Edit page -->
<div data-role="page" id="edit">
<?php
$edUser = filter_input(INPUT_GET,'nm');
?>
<div data-role="header">
    <h4 style="white-space: normal; text-align: center" >Edit user <?php echo $edUser;?></h4>
</div><!-- /header -->

<div data-role="content">
    <form method="post" action="#" data-ajax="false">
        <div class="ui-grid-a">
            <div class="ui-block-a" style="padding-right:10px;">
                <input name="nameF" id="editNameF" value="<?php echo $nameF;?>" placeholder="First name" type="text" >
            </div>
            <div class="ui-block-b">
                <input name="nameL" id="editNameL" value="<?php echo $nameL;?>" placeholder="Last name" type="text">
            </div>
        </div>
        <div class="ui-grid-a">
            <div class="ui-block-a" style="padding-right:10px;">
                <input name="numPager" id="editPagerNum" value="<?php echo $numPager;?>" placeholder="Pager (10-digits)" pattern="(206)[0-9]{7}" type="text">
            </div>
            <div class="ui-block-b" style="padding-top:2px;">
                <fieldset data-role="controlgroup" data-type="horizontal" class="ui-mini">
                    <input name="numPagerSys" id="editPagerSys-a" type="radio" value="COOK">
                    <label for="addPagerSys-a">Cook</label>
                    <input name="numPagerSys" id="editPagerSys-b" type="radio" value="USAM">
                    <label for="addPagerSys-b">USA-M</label>
                </fieldset>
            </div>
        </div>
        <div class="ui-grid-a">
            <div class="ui-block-a" style="padding-right:10px;">
                <input name="numSms" id="editSmsNum" value="<?php echo $numSms;?>" placeholder="SMS (10-digits)" pattern="[0-9]{10}" type="text">
            </div>
            <div class="ui-block-b" style="padding-top:2px;">
                <fieldset data-role="controlgroup" data-type="horizontal" class="ui-mini">
                    <input name="numSmsSys" id="editSmsSys-a" type="radio" value="ATT">
                    <label for="addSmsSys-a">AT&amp;T</label>
                    <input name="numSmsSys" id="editSmsSys-b" type="radio" value="Sprint">
                    <label for="addSmsSys-b">Sprint</label>
                </fieldset>
            </div>
        </div>
        <input name="numPushBul" id="editPushBul" value="<?php echo $numPushBul;?>" placeholder="Pushbullet email" type="text">
        <select name="userGroup" id="editGroup" data-native-menu="false">
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
        <input type="hidden" name="add" value="edit">
        <button type="submit" class="ui-btn ui-corner-all ui-shadow ui-btn-b ui-btn-icon-left ui-icon-check" >Save</button>
    </form>
</div>

</div> <!-- end page -->

</body>
</html>
