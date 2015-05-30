<!DOCTYPE html>
<html>
<head>
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="icon" type="image/png" href="favicon.png" />
    <link rel="apple-touch-icon" href="favicon.png" />
    <link href="" rel="apple-touch-startup-image" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-touch-fullscreen" content="YES" />
    <meta name="viewport" content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" />
<!--    Block for CDN copies of jquery/mobile. Consider fallback code on fail? -->
    <link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.2/jquery.mobile-1.4.2.min.css" />
    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script src="http://code.jquery.com/mobile/1.4.2/jquery.mobile-1.4.2.min.js"></script>
<!--==========================================-->
<!-- Block for local copies of jquery/mobile. 
    <link rel="stylesheet" href="./jqm/jquery.mobile-1.3.2.min.css" />
    <script src="./jqm/jquery-1.9.1.min.js"></script>
    <script src="./jqm/jquery.mobile-1.3.2.min.js"></script>
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
    
    <title>Paging v2</title>
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
    'SURG' => 'Surgery',
    'CICU' => 'CICU',
    'ARNP' => 'ARNP',
    'CATH' => 'Cath Lab',
    'CLINIC' => 'Clinic RN, Soc Work',
    'ECHO' => 'Echo Lab',
    'ADMIN' => 'Admin Office',
    'DATA' => 'Research, Data'
    );
// Read "list.csv" into array
$arrLine = array();
$pagerblock = "";
$row = 0;
if (($handle = fopen("list.csv", "r")) !== FALSE) {
    while (($arrLine[] = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($arrLine[$row][0] === $group) {
            $tmpLastName = $arrLine[$row][1];
            $tmpFirstName = $arrLine[$row][2];
            $tmpPageSys = $arrLine[$row][3];
            $tmpPageNum = $arrLine[$row][4];
            $tmpCellSys = $arrLine[$row][5];
            $tmpCellNum = $arrLine[$row][6];
            $tmpCellOpt = $arrLine[$row][7];
            $tmpKey = $arrLine[$row][8];
            $pagerline = 
                    $tmpPageSys.",".$tmpPageNum.",".
                    $tmpCellSys.",".$tmpCellNum.",".$tmpCellOpt.",".$tmpLastName ;
            $pagerblock .= "<option value=\"".str_rot($pagerline)."\">".$tmpFirstName." ".$tmpLastName."</option>\r\n";
            }
            $row++;
        } // Finish loop to get lines
    } 
    fclose($handle);
    $modDate = date ("m/d/Y", filemtime("list.csv"));

?>

<!-- Start of first page -->
<div data-role="page" id="main">

    <div data-role="header">
        <h4 style="white-space: normal; text-align: center" ><?php echo $group; ?></h4>
    </div><!-- /header -->

    <ul data-role="listview">
        <li><a href="?group=CARDS&#proc" >Cardiologists</a></li>
        <li><a href="?group=FELLOWS" >Fellows</a></li>
        <li><a href="?group=SURG" >CV Surgery/Anesthesia/Perfusion</a></li>
        <li><a href="?group=CICU" >CICU</a></li>
        <li><a href="?group=ARNP" >ARNP's</a></li>
        <li><a href="?group=CATH" >Cath Lab</a></li>
        <li><a href="?group=CLINIC" >Clinic RN, Social Work, Nutrition</a></li>
        <li><a href="?group=ECHO" >Echo Lab</a></li>
        <li><a href="?group=ADMIN" >Admin Office</a></li>
        <li><a href="?group=DATA" >Research, Data, Computers</a></li>
    </ul>
    
    <form name="mainlist" action="#proc" method="get">
        <input type="submit" class="ui-btn ui-shadow ui-btn-icon-left ui-corner-all ui-icon-carat-l ui-btn-icon-notext" value="Cardiology">
    </form>

    <div data-alertbox-close-time="5000" data-alertbox-transition="fade" data-role="popup" data-theme="a" data-overlay-theme="b" id="popupOpts" class="ui-content jqm-alert-box" style="max-width:280px">
        <a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
        <p>REMINDER!</p>
        <p>This paging site is for internal Heart Center use. Please do not share this link with others outside of the organization. Thanks for your understanding!</p>
    </div>

    <div data-role="footer" >
        <h5><small>
&COPY;(2007-2014) Terrence Chun, MD<br>
        </small></h5>
    </div><!-- /footer -->
</div><!-- /page -->

<!-- Start of process page -->
<div data-role="page" id="proc" data-dom-cache="true"> <!-- page -->
<?php
?>
    <div data-role="header" data-add-back-btn="true" >
        <a href="javascript:history.go(-1);" data-icon="arrow-l"><small>Back</small></a>
        <h3><?php echo $group; ?></h3>
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
        <select name="NUMBER" id="NUMBER">
            <?php echo $pagerblock; ?>
        </select>
        <label for="MYNAME">From:</label>
        <input type="text" name="MYNAME" id="MYNAME" value="" placeholder="REQUIRED" maxlength="20"/>
    </div>

    <div data-role="fieldcontain" style="text-align: right">
        <textarea name="MESSAGE" id="MESSAGE" maxlength="220"></textarea>
    </div>
    <input type="hidden" name="GROUP" value="<?php echo $group; ?>">
    <div style="text-align: center">
        <input type="submit" value="SUBMIT!" data-inline="true" data-theme="b" />
    </div>
</div>
</form>

    <div data-role="footer" data-position="fixed">
        <h5><small>
&COPY;(2007-2014) Terrence Chun, MD<br>
Data revised: <?php echo $modDate; ?><br>
        </small></h5>
    </div><!-- /footer -->
</div><!-- /page -->

<!-- Last modified 2/19/13 -->

</body>
</html>
