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
<!-- Block for local copies of jquery/mobile.
    <link rel="stylesheet" href="./jqm/jquery.mobile-1.3.2.min.css" />
    <script src="./jqm/jquery-1.9.1.min.js"></script>
    <script src="./jqm/jquery.mobile-1.3.2.min.js"></script>
<!--==========================================-->
    <!--<script type="text/javascript" src="./jqm/jqm-alertbox.min.js"></script>-->


    <title>Paging v3</title>
</head>
<body>
<?php
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

$group = filter_input(INPUT_GET,'group');
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
$modDate = date ("m/d/Y", filemtime("list.xml"));
$xml = simplexml_load_file("list.xml");
$groups = $xml->groups;
?>

<!-- Start of first page -->
<div data-role="page" id="main">
    <script>
        $('#main').on('panelopen','#search', function(){
            $('#auto-editUser').focus();
        });
    </script>
    <div data-role="panel" id="search" data-display="">
        <form class="ui-filterable">
            <input id="auto-editUser" data-type="search" placeholder="Find user...">
        </form>
        <div style="margin-bottom: 24px;">
        <ul data-role="listview" data-filter="true" data-filter-reveal="true" data-input="#auto-editUser" data-inset="true" data-theme="b">
            <?php
            $liUsers = $xml->xpath('//user');
            $liGroupOld = "";
            foreach($liUsers as $liUser) {
                $liNameL = $liUser['last'];
                $liNameF = $liUser['first'];
                $liUserId = $liUser['uid'];
                $liGroup = $liUser->xpath('..')[0]->getName();
                if (!($liGroup==$liGroupOld)) {
                    //echo "\r\n".'        <li data-role="list-divider">'.$groupfull[$liGroup].'</li>'."\r\n";
                    $liGroupOld = $liGroup;
                }
                echo '            <li class="ui-mini">';
                echo '<a href="proc.php?group='.$liGroup.'&id='.$liUserId.'" data-ajax="false"><i>'.$liNameL.', '.$liNameF.'</i></a>';
                echo '</li>'."\r\n";
            }
            ?>
        </ul>
        </div>
        <ul data-role="listview">
            <?php
            $cookie = explode(",", filter_input(INPUT_COOKIE,'pagemru'));
            foreach($cookie as $cvals){
                if ($cvals==''){
                    continue;
                }
                $liUser = $xml->xpath("//user[@uid='".$cvals."']")[0];
                $liUserId = $liUser['uid'];
                $liGroup = $liUser->xpath("..")[0]->getName();
                echo '<li><a href="proc.php?group='.$liGroup.'&id='.$cvals.'" data-ajax="false">'.$liUser['first'].' '.$liUser['last'].'</a></li>'."\r\n";
            }
            if ($cookie){
//                echo '<li>clear</li>'."\r\n";
            }
            ?>
        </ul>
    </div>

    <div data-role="header">
        <h4 style="white-space: normal; text-align: center" >Heart Center Paging</h4>
        <a href="#search" class="ui-btn ui-shadow ui-icon-search ui-btn-icon-notext ui-corner-all" >Search panel</a>
        <a href="back.php" class="ui-btn ui-shadow ui-icon-user ui-btn-icon-notext ui-corner-all" data-ajax="false">return to main</a>
    </div><!-- /header -->

    <div data-role="content">
    <ul data-role="listview">
        <?php
        foreach($groupfull as $grp => $grpStr) {
            echo '<li><a href="proc.php?group='.$grp.'">'.$grpStr.'</a></li>';
        }?>
    </ul>

    <div data-alertbox-close-time="5000" data-alertbox-transition="fade" data-role="popup" data-theme="a" data-overlay-theme="b" id="popupOpts" class="ui-content jqm-alert-box" style="max-width:280px">
        <a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
        <p>REMINDER!</p>
        <p>This paging site is for internal Heart Center use. Please do not share this link with others outside of the organization. Thanks for your understanding!</p>
    </div>
    </div>

    <div data-role="footer" >
        <h5><small>
&COPY;(2007-2015) Terrence Chun, MD<br>
        </small></h5>
    </div><!-- /footer -->
</div><!-- /page -->


<!-- Last modified 7/9/15 -->

</body>
</html>
