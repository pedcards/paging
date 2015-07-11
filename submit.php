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
$ip = '63.172.11.60';
$port = '444';

$success = 0;  // preset success to boolean false

// Get values from form page
$fromName = ucwords(trim(filter_input(INPUT_POST,'MYNAME')));
$pinarray = explode(",", trim(str_rot(filter_input(INPUT_POST,'NUMBER'))));  // Pin Number from form page
    $pagesys = $pinarray[0]; // Cook vs USA Mobility
    $pin = $pinarray[1]; // pager number
    $cellsys = $pinarray[2]; // cell system
    $cellnum = $pinarray[3]; // cell number
    $pushbul = $pinarray[4]; // pushbullet eml
    $pushover = $pinarray[5]; // pushover ID
    $boxcar = $pinarray[6]; // boxcar ID
    $sendto = $pinarray[7]; // A:$pin, B:both, C:cell
    $sendname = $pinarray[8]; // recipient name
    if ($cellsys === "ATT") {
        $cellnum .= "@txt.att.net";
        }
    elseif ($cellsys === "VZN") {
        }
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
        $sendname,
        $fromName,
        $messagePost        // could use str_rot13($messagePost) for some privacy
    )
); 
fclose($out);

// Error handling if no FROM specified
if ($fromName == "") {
?>
    <div data-role="dialog" id="dialog-fail" data-overlay-theme="b">
        <div data-role="header" data-theme="b" >
            <h1 style="color:red">FAIL</h1>
        </div>
        <DIV data-role="content" data-theme="a" >
            <p style="text-align:center">
                <img src="images/dead_ipod.jpg" alt="bummer"><br>
                The <b>FROM</b> field is <b>REQUIRED!</b>
            </p>
        </DIV>
    </div>
<?php
exit;
}

// Block for submitting to USA Mobility number
// $valid = ereg('^[0-9]{10}$' , $pin);  // Test pin for 10 numeric digits. Anything else is considered invalid.
// $usamobility = (strpos($pin, '469') !== 3);  // If prefix is not 469, this is USA Mobility number.
if ($pagesys === "USAM") {
?>
    <div data-role="dialog" id="dialog-usamobility" data-overlay-theme="b">
        <div data-role="header" >
            <h1>USA Mobility</h1>
        </div>
        <DIV data-role="content" >
            <p style="text-align:center">
                Page sent!*<br>
                <img src="images/pager.jpg" alt="pager"><br>
                <small>*hopefully you won't regret what you just sent!</small>
            </p>
        </DIV>
        <form name="Terminal" action="http://www.usamobility.net/cgi-bin/wwwpage.exe" method="POST" >
            <input type="hidden" name="PIN" value="<?php echo $pin; ?>">
            <input type="hidden" name="MSSG" value="<?php echo $message; ?>">
            <input type="hidden" name="Q1" value="0">
            <script type="text/javascript">document.Terminal.submit();</script>
        </form>
    </div>
<?php exit;
}

// Send SMS email
if (($sendto === "B") || ($sendto === "C")) {
    $headers = "From: ".$fromName."@heart.center\r\n".
        "X-Mailer: php";
    mail($cellnum, "", $messagePost."\n==========\n<<<Do not reply to this message!>>>", $headers);
}
// Option C, stop after sending SMS.
if ($sendto === "C") {
?>
    <div data-role="dialog" id="dialog-sms" data-overlay-theme="b">
        <div data-role="header" >
            <h1>SMS</h1>
        </div>
        <DIV data-role="content"  >
            <p style="text-align:center">
                Text message sent to cell phone!<br>
                <img src="images/sms-128.png" alt="sms"><br>
                <small>*May take a while to be received.</small>
            </p>
        </DIV>
    </div>
<?php exit;
}

    $smsMsg = "Page";
    if ($sendto === "B") {
        $smsMsg = "SMS and Page";
        }

// Send Pushbullet
if ($sendto === "D") {
    $data = array(
        "email" => "tchun47@gmail.com",
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

?>
    <div data-role="dialog" id="dialog-pbul" data-overlay-theme="b">
        <div data-role="header" >
            <h1>SMS</h1>
        </div>
        <DIV data-role="content"  >
            <p style="text-align:center">
                Text message sent to cell phone!<br>
                <img src="images/sms-128.png" alt="sms"><br>
                <small>*May take a while to be received.<br>
                <? echo $result;?>
                </small>
                
            </p>
        </DIV>
    </div>
<?php 
}

// Send PushOver
if ($sendto === "E") {
    curl_setopt_array($ch = curl_init(), array(
        CURLOPT_URL => "https://api.pushover.net/1/messages.json",
        CURLOPT_POSTFIELDS => array(
            "token" => "asfJPnVyAUvsTkoGT8cAEvtE8pndHY",
            "user" => "ussgHSjm3HpytUncU6Sg1jimxj7TiJ",
            "title" => "FROM: ".$fromName,
            "message" => $messagePost
        ),
        CURLOPT_SAFE_UPLOAD => true,
    ));
    curl_exec($ch);
    curl_close($ch);
?>
    <div data-role="dialog" id="dialog-pushover" data-overlay-theme="b">
        <div data-role="header" >
            <h1>SMS</h1>
        </div>
        <DIV data-role="content"  >
            <p style="text-align:center">
                PushOver message sent!<br>
                <img src="images/sms-128.png" alt="sms"><br>
                <small>*May take a while to be received.<br>
                <? echo $result;?>
                </small>
                
            </p>
        </DIV>
    </div>
<?php 
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

echo $snppSend;

if (substr($snppSend,0,3) === "250") { 
    $success = 1;
?>
    <div data-role="dialog" id="page-success" data-overlay-theme="b">
        <div data-role="header" >
            <h1 style="color:green">SUCCESS!</h1>
        </div>
        <DIV data-role="content" >
            <p style="text-align:center">
                <?php echo $smsMsg; ?> sent!*<br>
                <img src="images/pager.jpg" alt="pager"><br>
                <small>*hopefully you won't regret what you just sent!</small>
            </p>
        </DIV>
    </div>

<?PHP
    exit;
    }

  // Testing for a server response code 550 (bad). Pager number was not valid. Error back.
else if (($snppPage[0] === "4") || ($snppPage[0] === "5")) {
    $success = false;
?>
    <div data-role="dialog" id="server-fail" data-overlay-theme="b">
        <div data-role="header" data-theme="d" >
            <h1 style="color:red">SERVER ERROR</h1>
        </div>
        <DIV data-role="content" >
            <p style="text-align:center">
                ERROR!!!
                <img src="images/dead_ipod.jpg" alt="bummer"><br>
                Message failed to send!<br>
            </p>
        </DIV>
    </div>
<?PHP
    }
?>

</body>
</HTML>
