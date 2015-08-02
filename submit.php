<!DOCTYPE html>
<HTML>
    <head>
        <meta content="yes" name="apple-mobile-web-app-capable" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="" rel="apple-touch-icon" />
        <link href="" rel="apple-touch-startup-image" />
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="apple-mobile-web-app-capable" content="YES" />
        <meta name="viewport" content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" />
        <title>Heart Center Paging</title>
    </head>
<body>

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
function dialog($title,$tcolor,$msg1,$msg2,$img,$alt,$bar,$fg,$bg) {
    if ($msg1=='sent') {
        $msg1 = 'Page sent!*<br>';
        $msg2 = '<small>*hopefully you won\'t regret what you just sent!</small><br>';
    }
    echo '<div data-role="page" data-dialog="true" id="dialog-fn" data-overlay-theme="'.$bg.'">'."\r\n";
    echo '    <div data-role="header" data-theme="'.$bar.'">'."\r\n";
    echo '        <h1 style="color:'.$tcolor.'">'.$title.'</h1>'."\r\n";
    echo '    <div>';
    echo '    <div data-role="content" data-theme="'.$fg.'">'."\r\n";
    echo '        <p style="text-align:center">'."\r\n";
    echo '            '.$msg1."<br>\r\n";
    echo '            <img src="images/'.$img.'" alt="'.$alt.'"><br>'."\r\n";
    echo '            '.$msg2."<br>\r\n";
    echo '        </p>'."\r\n";
    echo '    </div>'."\r\n";
    echo '</div>'."\r\n";
}
// **** Referrer info for logfile ****
    $logfile = 'logs/'.date('Ym').'.csv';
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP')) {
        $ipaddress = getenv('HTTP_CLIENT_IP');
    } else if(getenv('HTTP_X_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    } else if(getenv('HTTP_X_FORWARDED')) {
        $ipaddress = getenv('HTTP_X_FORWARDED');
    } else if(getenv('HTTP_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    } else if(getenv('HTTP_FORWARDED')) {
       $ipaddress = getenv('HTTP_FORWARDED');
    } else if(getenv('REMOTE_ADDR')) {
        $ipaddress = getenv('REMOTE_ADDR');
    } else {
        $ipaddress = 'UNKNOWN';
    }

// **** SNPP server Info ****
//$ip = '63.172.11.60';
$ip = 'snpp.amsmsg.net';
$port = '444';

$success = 0;  // preset success to boolean false

// Get values from form page
$fromName = ucwords(trim(filter_input(INPUT_POST,'MYNAME')));
$pinarray = explode(",", trim(str_rot(filter_input(INPUT_POST,'NUMBER'))));  // Pin Number from form page
    $uid = $pinarray[0]; // recipient UID
    $pagesys = $pinarray[1]; // Cook vs USA Mobility
    $pin = $pinarray[2]; // pager number
    $sendto = $pinarray[3]; // A:$pin, B:both, C:service
    $sendSvc = $pinarray[4]; // extra service: sms,pbl,pov,bxc
    $sendStr = $pinarray[5]; // user string
$messagePost = trim(filter_input(INPUT_POST,'MESSAGE'));  // Get message from form page
$messageMerged = "[From: ".$fromName."] ".$messagePost; // Construct Message, add MYNAME in front of MESSAGE.
$message = str_replace("\r\n" , "\n" , $messageMerged);  // Filter LF,CR and replace with newline.

// Log the access
$out = fopen($logfile,'a');
fputcsv(
    $out, 
    array(
        date('c'),
        $ipaddress,
        $pin,
        $uid,
        $fromName,
        $messagePost        // TODO: could use str_rot13($messagePost) for some privacy
    )
); 
fclose($out);

// Update the MRU cookie
if ($pin) {
    $cstr = $uid;
    $cookie = explode(",", filter_input(INPUT_COOKIE,'pagemru'));
    $i = 0;
    foreach($cookie as $cvals){
        if ($cvals==$uid){
            continue;
        }
        $i++;
        if ($i==5){
            break;
        }
        $cstr .= ','.$cvals;
    }
    setcookie("pagemru",$cstr,time()+(86400*30),"/");
}
// Error handling if no FROM specified

// TODO: create function call for dialog boxes.

if ($fromName == "") {
    dialog('FAIL','red','', (($pin)?'The <b>FROM</b> field is <b>REQUIRED!</b>':'Must select a user!'), 'dead_ipod.jpg', 'bummer', 'b', 'a', 'b');
    exit;
}

// Send SMS email
$smsMsg = 'Page';
if (($sendto == "B") || ($sendto == "C")) {
    if ($sendSvc == 'sms'){
        $headers = "From: ".$fromName."@heart.center\r\n".
            "X-Mailer: php";
        mail($sendStr, "", $messagePost."\n==========\n<<<Do not reply to this message!>>>", $headers);
        $diag = array(
            'SMS','green',
            'Text message sent to cell phone!',
            '<small>*May take a while to be received.</small>',
            'sms-128.png', 'sms',
            '', '', 'b');
        $smsMsg = "SMS and Page";
    }
    if ($sendSvc == 'pbl'){
        $data = array(
            "email" => $sendStr,
            "type" => "note",
            "title" => "[FROM: ".$fromName."] ".$messagePost,
            "body" => $messagePost
            );
        $data_string = json_encode($data);
        $ch = curl_init('https://api.pushbullet.com/v2/pushes');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ouJTM6bJedxmqB4iY1pLNsIFp4b843qB',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        $result = curl_exec($ch);
        $diag = array(
            'Pushbullet','green',
            'Pushbullet message sent!','',
            'sms-128.png','pushbullet',
            '','','b');
        $smsMsg = "Pushbullet and Page";
    }
    if ($sendSvc=='pov'){
        curl_setopt_array($ch = curl_init(), array(
            CURLOPT_URL => "https://api.pushover.net/1/messages.json",
            CURLOPT_POSTFIELDS => array(
                "token" => "asfJPnVyAUvsTkoGT8cAEvtE8pndHY",
                "user" => $sendStr,
                "title" => "FROM: ".$fromName,
                "message" => $messagePost
            ),
            CURLOPT_SAFE_UPLOAD => true,
        ));
        curl_exec($ch);
        curl_close($ch);
        $diag = array(
            'Pushover','green', 
            'Pushover message sent!', '', 
            'sms-128.png', 'pushover', 
            '', '', 'b');
        $smsMsg = "Pushover and Page";
    }
    if ($sendSvc=='bxc'){
        curl_setopt_array($chpush = curl_init(), array(
            CURLOPT_URL => "https://new.boxcar.io/api/notifications",
            CURLOPT_POSTFIELDS => array(
                "user_credentials" => $sendStr,
                "notification[title]" => 'FROM: '.$fromName,
                "notification[long_message]" => $messagePost,
                "notification[sound]" => "bird-1",
            ),
            CURLOPT_SAFE_UPLOAD => true,
        ));
        $ret = curl_exec($chpush);
        curl_close($chpush);
        $diag = array(
            'Boxcar','green', 
            'Boxcar message sent!', '', 
            'sms-128.png', 'boxcar', 
            '', '', 'b');
        $smsMsg = "Boxcar and Page";
    }
}

// Option C, stop after sending SMS.
if ($sendto === "C") {
    dialog($diag[0],$diag[1],$diag[2],$diag[3],$diag[4],$diag[5],$diag[6],$diag[7],$diag[8]);
    exit;
}

// Block for submitting to USA Mobility number
// $valid = ereg('^[0-9]{10}$' , $pin);  // Test pin for 10 numeric digits. Anything else is considered invalid.
// $usamobility = (strpos($pin, '469') !== 3);  // If prefix is not 469, this is USA Mobility number.
if ($pagesys === "USAM") {
    dialog('USA Mobility','green', 'sent', 'sent', 'pager.jpg', 'pager', '', '', 'b');
    ?>
    <form name="Terminal" action="http://www.usamobility.net/cgi-bin/wwwpage.exe" method="POST" >
        <input type="hidden" name="PIN" value="<?php echo $pin; ?>">
        <input type="hidden" name="MSSG" value="<?php echo $message; ?>">
        <input type="hidden" name="Q1" value="0">
        <script type="text/javascript">document.Terminal.submit();</script>
    </form>
    <?php
    exit;
}

// Block for submitting to CookMail
// This is the socket connection to the snpp server
$snppPointer = fsockopen($ip , $port);  // Open connection
$snppConnect = fgets($snppPointer);

fwrite($snppPointer, "PAGE ".($pin)."\r\n");  // Send the pin to the SNPP server
$snppPage = fgets($snppPointer);

fwrite($snppPointer, "MESS ".($message)."\r\n");  // Send the message to the SNPP Server
$snppMsg = fgets($snppPointer);

fwrite($snppPointer, "SEND\r\n");  // Send
$snppSend = fgets($snppPointer);

fwrite($snppPointer, "QUIT\r\n");  // Send QUIT to the SNPP Server
$snppQuit = fgets($snppPointer);

fclose($snppPointer);  // Close the connection

// Testing for a server response code 250 (good).
// If doesn't meet this, then error back.

//echo $snppSend;

if (substr($snppSend,0,3) === "250") { 
    $success = 1;
    dialog('SUCCESS!', 'green', $smsMsg.' sent!*', '<small>*hopefully you won\'t regret what you just sent!</small>', 'pager.jpg', 'pager', '', '', 'b');
    exit;
    }

  // Testing for a server response code 550 (bad). Pager number was not valid. Error back.
else if (($snppPage[0] === "4") || ($snppPage[0] === "5")) {
    $success = false;
    dialog('SERVER ERROR', 'red', 'ERROR!!!', 'Message failed to send!<br>'.$snppSend, 'dead_ipod.jpg', 'bummer', 'b', 'a', 'b');
}
?>

</body>
</HTML>
