<!DOCTYPE html>
<HTML>
<HEAD>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="icon" type="image/png" href="favicon.png" />
    <link rel="apple-touch-icon" href="favicon.png" />
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
    <title>Heart Center Paging</title>
</HEAD>
<BODY>

<?php
function str_rot($s, $n = -1) {
    //Rotate a string by a number.
    static $letters = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789.,!$*+-?@#'; 
    //To be able to de-obfuscate your string the length of this needs to be a multiple of 4 AND no duplicate characters
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

$grp = filter_input(INPUT_GET,'group');
$uid = filter_input(INPUT_GET,'id');
$modDate = date ("m/d/Y", filemtime("list.xml"));
$xml = simplexml_load_file("list.xml");
$groups = $xml->groups;
$groupfull = array();
foreach ($groups->children() as $grp0) {
    $groupfull[$grp0->getName()] = $grp0->attributes()->full;
}
$group = $xml->groups->$grp;
?>

<!-- Start of first page -->
<div data-role="page" id="procmain" data-dom-cache="true"> <!-- page -->
    <div data-role="header" data-add-back-btn="true" >
        <a href="index.php" data-ajax="false" class="ui-btn ui-shadow ui-icon-arrow-l ui-btn-icon-notext ui-corner-all" ><small>Back</small></a>
        <h3><?php echo $groupfull[$grp]; ?></h3>
    </div><!-- /header -->

<form action="submit.php" method="POST" name="HTMLForm1" data-prefetch>
    <input type="hidden" name="SERVER_IP" value="63.172.11.60">
    <input type="hidden" name="SERVER_PORT" value="444">
    <input type="hidden" name="WEBPAGE" value="yes">
    <input type="hidden" name="ALPHA" value="a">
    <input type="hidden" name="ACCEPT_PAGE" value="/paging/page_accepted.htm">
    <input type="hidden" name="NUMBER" value="">
    <input type="hidden" name="NUMBER2" value="">
    <input type="hidden" name="NUMBER3" value="">
    <input type="hidden" name="NUMBER4" value="">
    <input type="hidden" name="NUMBER5" value="">
    <input type="hidden" name="MYNAME" value="">
    <input type="hidden" name="SUBJECT" value=  "">
    <input type="hidden" name="MESSAGE" value="">

<div data-role="content">
    <div data-role="fieldcontain" >
        <label for="NUMBER" >To:</label>
        <select name="NUMBER" id="NUMBER" data-native-menu="true">
            <?php 
            foreach($group->user as $liUser) {
                $liUid = $liUser['uid'];
                $liNameL = $liUser['last'];
                $liNameF = $liUser['first'];
                if ($liUser['sec']){
                    $liSec = $liUser['sec'];
                    $pagerline = '';
                    $liName = '::: '.$liSec.' :::';
                } else {
                    $liSec = '';
                    $pagerline = array(
                        $liUser->pager['sys'],
                        $liUser->pager['num'],
                        $liUser->sms['sys'],
                        $liUser->sms['num'],
                        $liUser->pushbul['eml'],
                        $liUser->pushover['num'],
                        $liUser->boxcar['num'],
                        $liUser->option['mode'],
                        $liUser->option['svc'],
                        $liUid
                    );
                    $liName = $liNameF.' '.$liNameL;
                }
                echo '<option value="'.str_rot(implode(",",$pagerline)).'" '.(($liUid==$uid)?'selected="selected"':'').'>'.$liName.'</option>'."\r\n";
            }
            // TODO: only send opt nums if selected. no need to send for everyone.
            ?>
        </select>
        <label for="MYNAME">From:</label>
        <input type="text" name="MYNAME" id="MYNAME" value="" placeholder="REQUIRED" maxlength="20"/>
    </div>

    <div data-role="fieldcontain" style="text-align: right">
        <textarea name="MESSAGE" id="MESSAGE" maxlength="200"></textarea>
    </div>
    <input type="hidden" name="GROUP" value="<?php echo $group; ?>">
    <div style="text-align: center">
        <input type="submit" value="SUBMIT!" data-inline="true" data-theme="b" />
    </div>
</div>
</form>

    <div data-role="footer" data-position="fixed">
        <?php
        ?>
        <h5><small>
&COPY;(2007-2015) Terrence Chun, MD<br>
Data revised: <?php echo $modDate; ?><br>
        </small></h5>
    </div><!-- /footer -->
</div><!-- /page -->

<!-- Last revised 01/15/15 -->


</BODY>
</HTML>
