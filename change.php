<!DOCTYPE html>
<HTML>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="apple-mobile-web-app-capable" content="no" />
        <meta name="viewport" content="initial-scale=1, width=device-width, user-scalable=no" />
        <?php
        $isLoc = true;
        $ini = parse_ini_file("paging.ini");
        $cdnJqm = $ini['jqm'];
        $cdnJQ = $ini['jquery'];
        $instr = $ini['copyright'];
        ?>
        <link rel="stylesheet" href="<?php echo (($isLoc) ? './jqm' : 'http://code.jquery.com/mobile/'.$cdnJqm).'/jquery.mobile-'.$cdnJqm;?>.min.css" />
        <script src="<?php echo (($isLoc) ? './jqm/' : 'http://code.jquery.com/').'jquery-'.$cdnJQ;?>.min.js"></script>
        <script src="<?php echo (($isLoc) ? './jqm' : 'http://code.jquery.com/mobile/'.$cdnJqm).'/jquery.mobile-'.$cdnJqm;?>.min.js"></script>
        <script src="./lib/cookies.js"></script>
<!--==========================================-->
        <title>Heart Center Paging</title>
    </head>
<body>

<?php


/*  This section at end for committing to list.xml
 * 
 */
$xml = (simplexml_load_file("list.xml")) ?: new SimpleXMLElement("<root />");
$groups = ($xml->groups) ?: $xml->addChild('groups');
