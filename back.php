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
    $cdnJqm = '1.4.5';
    $cdnJQ = '1.11.1';
    ?>
    <link rel="stylesheet" href="http://code.jquery.com/mobile/<?php echo $cdnJqm;?>/jquery.mobile-<?php echo $cdnJqm;?>.min.css" />
    <script src="http://code.jquery.com/jquery-<?php echo $cdnJQ;?>.min.js"></script>
    <script src="http://code.jquery.com/mobile/<?php echo $cdnJqm;?>/jquery.mobile-<?php echo $cdnJqm;?>.min.js"></script>
<!--==========================================-->
<!-- Block for local copies of jquery/mobile. 
    <link rel="stylesheet" href="./jqm/jquery.mobile-1.4.5.min.css" />
    <script src="./jqm/jquery-1.11.1.min.js"></script>
    <script src="./jqm/jquery.mobile-1.4.5.min.js"></script>
    ========================================== -->
    <!--<script type="text/javascript" src="./jqm/jqm-alertbox.min.js"></script>-->
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
    <a href="edit.php" class="ui-btn ui-icon-plus ui-btn-icon-left">Add a user</a>
    <form class="ui-filterable">
        <input id="auto-editUser" data-type="search" placeholder="Enter user name">
    </form>
    <ul data-role="listview" data-filter="true" data-filter-reveal="true" data-input="#auto-editUser" data-inset="true">
        <?php
        $edUsers = $xml->xpath('//user');
        $edGroupOld = "";
        foreach($edUsers as $edUser) {
            $edNameL = $edUser['last'];
            $edNameF = $edUser['first'];
            $edUserId = $edUser['uid'];
            $edGroup = $edUser->xpath('..')[0]->getName();
            if (!($edGroup==$edGroupOld)) {
                echo "\r\n".'        <li data-role="list-divider">'.$edGroup.'</li>'."\r\n";
                $edGroupOld = $edGroup;
            }
            echo '            <li class="ui-mini">';
            echo '<a href="edit.php?id='.$edUserId.'" data-ajax="false"><i>'.$edNameL.', '.$edNameF.'</i></a>';
            echo '</li>'."\r\n";
            // perhaps use Session variable?
        }
        ?>
    </ul>
</div>

<div data-role="footer" >
        <h5><small>
&COPY;(2007-2015) Terrence Chun, MD<br>
        </small></h5>
    </div><!-- /footer -->

</div><!-- /page -->

</body>
</html>
