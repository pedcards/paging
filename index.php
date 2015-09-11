<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="icon" type="image/png" href="favicon.png" />
    <link rel="apple-touch-icon" href="images/pager.png" />
    <link href="" rel="apple-touch-startup-image" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta name="viewport" content="initial-scale=1, width=device-width, user-scalable=no" />
    <!--==========================================-->
    <?php
    $isLoc = true;
    $cdnJqm = '1.4.5';
    $cdnJQ = '1.11.1';
    
    ?>
    <link rel="stylesheet" href="<?php echo (($isLoc) ? './jqm' : 'http://code.jquery.com/mobile/'.$cdnJqm).'/jquery.mobile-'.$cdnJqm;?>.min.css" />
    <script src="<?php echo (($isLoc) ? './jqm/' : 'http://code.jquery.com/').'jquery-'.$cdnJQ;?>.min.js"></script>
    <script src="<?php echo (($isLoc) ? './jqm' : 'http://code.jquery.com/mobile/'.$cdnJqm).'/jquery.mobile-'.$cdnJqm;?>.min.js"></script>
    <!--==========================================-->

    <script type="text/javascript" src="./jqm/jqm-windows.alertbox.min.js"></script>
    <script type="text/javascript">
        function clearMru() {
            document.cookie = "pagemru=; expires=-1; path=/";
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
if (\filter_input(INPUT_POST,'clearck')=="y"){
    setcookie('pagemru',null,-1,'/');
}
$pagealert = filter_input(INPUT_COOKIE, 'pagealert');
$alerttext =
        '<p>Paging version 3.0</p>'
        . '<p>New and improved!</p>'
        . '<p>Features:<br>'
        . '* Search bar! (upper left)<br>'
        . '* Recently used numbers!<br>'
        . '* Encryption!<br>'
        . '* Extra notification services!'
        . '</p>'
        ;
$texthash = md5($alerttext);
setcookie('pagealert', $texthash, time()+30*86400);
$call = array(
    'Ward_A', 'Ward_F',
    'ICU_A', 'ICU_F',
    'Reg_Con',
    'EP',
    'Txp',
    'ARNP_IP','ARNP_OP','ARNP_CL'
);
$chip = simplexml_load_file('../patlist/currlist.xml');
$fc_call = $chip->lists->forecast->xpath("call[@date='".date("Ymd")."']")[0];
$chip_ep = $fc_call->ARNP_IP;
$chip_el = getUid($chip_ep);

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
        "Meg" => "Margaret"
    );
    $names = explode(" ", $in);
    $el = $xml->xpath("//user[@last='".$names[1]."' and (@first='".$names[0]."' or @first='".strtr($names[0],$trans)."')]")[0];
    return $el['uid'];
}

?>

<!-- Start of first page -->
<div data-role="page" id="main">
    <script>
        $('#main').on('panelopen','#search', function(){
            $('#auto-editUser').focus();
        });
    </script>
    <div data-role="panel" id="search" data-display="overlay">
        <form class="ui-filterable">
            <input id="auto-editUser" data-type="search" placeholder="Find user...">
        </form>
        <div style="margin-bottom: 24px;">
        <ul data-role="listview" data-filter="true" data-filter-reveal="true" data-input="#auto-editUser" data-inset="true" data-theme="b">
            <?php
            // auto reveal items from search bar
            $liUsers = $xml->xpath('//user');
            $liGroupOld = "";
            foreach($liUsers as $liUser) {
                $liNameL = $liUser['last'];
                $liNameF = $liUser['first'];
                $liUserId = $liUser['uid'];
                $liGroup = $liUser->xpath('..')[0]->getName();
                echo '            <li class="ui-mini">';
                echo '<a href="proc.php?group='.$liGroup.'&id='.$liUserId.'" data-ajax="false"><i>'.$liNameL.', '.$liNameF.'</i></a>';
                echo '</li>'."\r\n";
            }
            ?>
        </ul>
        </div>
        <div data-role="collapsible" data-inset="false" data-mini="true" data-collapsed-icon="phone">
            <h4>On call list</h4>
        <ul data-role="listview">
            <?php
            foreach($call as $callU){
                $chName = $fc_call->$callU;
                if (! $chName) {
                    continue;
                }
                $liUserId = getUid($chName);
                $liUser = $xml->xpath("//user[@uid='".$liUserId."']")[0];
                $liGroup = $liUser->xpath('..')[0]->getName();
                echo '            <li class="ui-mini">';
                echo '<a href="proc.php?group='.$liGroup.'&id='.$liUserId.'" data-ajax="false"><b>'.$callU.':</b><i> '.$chName.'</i></a>';
                echo '</li>'."\r\n";
            }
            ?>
        </ul>
        </div>
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
                    setcookie('pagemru',null,-1,'/');
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

    <div data-role="header" data-theme="b" >
        <h4 style="white-space: normal; text-align: center" >Heart Center Paging</h4>
        <a href="#search" class="ui-btn ui-shadow ui-icon-search ui-btn-icon-notext ui-corner-all" >Search panel</a>
        <a href="back.php" class="ui-btn ui-shadow ui-icon-bullets ui-btn-icon-notext ui-corner-all" data-ajax="false">return to main</a>
    </div><!-- /header -->

    <div data-role="content">
    <ul data-role="listview">
        <?php
        foreach($groupfull as $grp => $grpStr) {
            echo '<li><a href="proc.php?group='.$grp.'">'.$grpStr.'</a></li>';
        }
        ?>
    </ul>
    <?php if ($pagealert!==$texthash) { ?>
        <div class="ui-content jqm-alert-box" data-alertbox-close-time="20000" data-alertbox-transition="fade" data-role="popup" data-theme="a" data-overlay-theme="b" id="popupOpts" >
            <a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
            <?php echo $alerttext;?>
        </div> 
    <?php } ?>
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
