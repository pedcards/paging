<!DOCTYPE html>
<HTML>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="apple-mobile-web-app-capable" content="no" />
        <meta name="viewport" content="initial-scale=1, width=device-width, user-scalable=no" />
        <?php
        $isLoc = true;
        $cdnJqm = '1.4.5';
        $cdnJQ = '1.11.1';
        $instr = "(c)2007-2015 by Terrence Chun, MD.";
        ?>
        <title>Heart Center Paging</title>
    </head>
<body>

<?php
function dialog($title,$tcolor,$msg1,$msg2,$img,$alt,$bar,$fg,$bg) {
    ?>
    <div data-role="page" data-dialog="true" id="dialog-fn" data-overlay-theme="<?php echo $bg;?>">
        <div data-role="header" data-theme="<?php echo $bar;?>">
            <h1 style="color:<?php echo $tcolor;?>"><?php echo $title;?></h1>
        </div>
        <div data-role="content" data-theme="<?php echo $fg;?>">
            <p style="text-align:center">
                <?php echo $msg1;?><br>
                <img src="images/<?php echo $img;?>" alt="<?php echo $alt;?>"><br>
                <?php echo $msg2;?><br>
            </p>
        </div>
    </div>
    <?php
}
function simple_decrypt($text, $salt = "") {
    if (!$salt) {
        global $instr; $salt = $instr;
    }
    if (!$text) {
        return $text;
    }
    return trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB), MCRYPT_RAND)));
}
function smartnum($text) {
    return preg_replace('/(?<!98)7(\-)?(\d{4})/', '987$2', $text);
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
$pinarray = explode(",", trim(simple_decrypt(filter_input(INPUT_POST,'NUMBER'))));  // Pin Number from form page
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
    setcookie('pagemru',$cstr,time()+(86400*30));
}
// Error handling if no FROM specified

// TODO: create function call for dialog boxes.

if ($fromName == "") {
    dialog('FAIL','red','', (($pin)?'The <b>FROM</b> field is <b>REQUIRED!</b>':'Must select a user!'), 'dead_ipod.jpg', 'bummer', 'b', 'a', 'b');
    exit;
}

// Send other services
$smsMsg = 'Page';
if (($sendto == "B") || ($sendto == "C")) {
    if ($sendSvc == 'sms'){
        $headers = "From: ".$fromName."@heart.center\r\n".
            "X-Mailer: php";
        mail(smartnum($sendStr), "", smartnum($messagePost)."\n==========\n<<<Do not reply to this message!>>>", $headers);
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
            "title" => "FROM: ".smartnum($fromName),
            "body" => smartnum($messagePost)
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
        $frmRepl = smartnum($fromName);
        $msgRepl = smartnum($messagePost);
        curl_setopt_array($ch = curl_init(), array(
            CURLOPT_URL => "https://api.pushover.net/1/messages.json",
            CURLOPT_POSTFIELDS => array(
                "token" => "asfJPnVyAUvsTkoGT8cAEvtE8pndHY",
                "user" => $sendStr,
                "title" => "[FROM: ".$frmRepl.']',
                "message" => $msgRepl
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
                "notification[title]" => 'FROM: '.smartnum($fromName),
                "notification[long_message]" => smartnum($messagePost),
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
if ($pagesys === "USAM") {
    dialog('USA Mobility','green', $smsMsg.' sent!*', '<small>*hopefully you won\'t regret what you just sent!</small>', 'pager.jpg', 'pager', '', '', 'b');
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
