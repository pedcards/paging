<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
<body>

<?php
$ini = parse_ini_file("paging.ini");
$instr = $ini['copyright'];
$xml = simplexml_load_file("list0.xml");
$groups = $xml->groups;

function old_encrypt($text, $salt = "") {
    if (!$salt) {
        global $instr; $salt = $instr;
    }
    if (!$text) {
        return $text;
    }
    return trim(base64_encode(mcrypt_encrypt(MCRYPT_BLOWFISH, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB), MCRYPT_RAND))));
}
function old_decrypt($text, $salt = "") {
    if (!$salt) {
        global $instr; $salt = $instr;
    }
    if (!$text) {
        return $text;
    }
    return trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB), MCRYPT_RAND)));
}
function change($in) {
    global $instr;
    $out = old_decrypt($in);
    return openssl_encrypt(
            $out,
            'AES-128-CBC',
            $instr
            );
}
foreach ($xml->xpath("//user") as $user)
{
    $liUid = $user['uid'];
    $liSec = $user['sec'];
    if ($liSec) {
        continue;
    }
    $user->pager['num'] = change($user->pager['num']);
    $user->auth['cis'] = change($user->auth['cis']);
    $user->auth['eml'] = change($user->auth['eml']);
    if ($user->option->sms) {
        $user->option->sms['num'] = change($user->option->sms['num']);
    }
    if ($user->option->pushbul) {
        $user->option->pushbul['eml'] = change($user->option->pushbul['eml']);
    }
    if ($user->option->pushover) {
        $user->option->pushover['num'] = change($user->option->pushover['num']);
    }
    if ($user->option->boxcar) {
        $user->option->boxcar['num'] = change($user->option->boxcar['num']);
    }
    if ($user->option->prowl) {
        $user->option->prowl['num'] = change($user->option->prowl['num']);
    }
    if ($user->option->tigertext) {
        $user->option->tigertext['num'] = change($user->option->tigertext['num']);
    }
}
$xml->asXML("list.xml");
echo 'list0.xml -> list.xml';
?>

</body>
</html>
