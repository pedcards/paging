    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="icon" type="image/png" href="favicon.png" />
    <link rel="apple-touch-icon" href="images/pager.png" />
    <link href="" rel="apple-touch-startup-image" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-capable" content="no" />
    <meta name="viewport" content="initial-scale=1, width=device-width, user-scalable=no" />
    <!--==========================================-->
    <?php
    $ini = parse_ini_file("paging.ini");
    $isLoc = true;
    $cdnJqm = $ini['jqm'];
    $cdnJQ = $ini['jquery'];
    ?>
    <link rel="stylesheet" href="<?php echo (($isLoc) ? './jqm' : 'http://code.jquery.com/mobile/'.$cdnJqm).'/jquery.mobile-'.$cdnJqm;?>.min.css" />
    <script src="<?php echo (($isLoc) ? './jqm/' : 'http://code.jquery.com/').'jquery-'.$cdnJQ;?>.min.js"></script>
    <script src="<?php echo (($isLoc) ? './jqm' : 'http://code.jquery.com/mobile/'.$cdnJqm).'/jquery.mobile-'.$cdnJqm;?>.min.js"></script>
    <!--==========================================-->
    <title>Paging v3</title>
</head>
<body>
<div data-role="page" id="main">
    <div data-role="header" data-theme="b" >
        <h4 style="white-space: normal; text-align: center" >Optional notification services</h4>
        <a href="#" data-rel="back" class="ui-btn ui-shadow ui-icon-back ui-btn-icon-notext ui-corner-all" >go back</a>
    </div><!-- /header -->

    <div data-role="content" id="notifSMS">
        <div data-role="collapsibleset">
            <div data-role="collapsible" data-collapsed="true">
                <h4>SMS text message</h4>
                <div data-role="main" class="ui-content">
                    <p>Cost: Carrier charges may apply</p>
                    <p>Utilizes email-to-SMS gateway</p>
                    <p>Networks: AT&amp;T, Verizon, T-Mobile</p>
                    <p>Messages likely not delivered within hospital or clinic</p>
                    <p>Delivery time variable</p>
                </div>
            </div>
            <div data-role="collapsible" data-collapsed="true">
                <h4>Prowl</h4>
                <div class="content">
                    <a href="http://prowlapp.com" data-role="button" target="_blank">ProwlApp</a>
                    <p>iOS app $2.99</p>
                    <p>Received instantaneously</p>
                    <p>Supports Smart Numbers</p>
                    <p>Works anywhere you have a signal, either cellular or wi-fi </p>
                    <p>Can also receive any other Growl notifications</p>
                </div>
            </div>
            <div data-role="collapsible" data-collapsed="true">
                <h4>Boxcar</h4>
                <div class="content">
                    <a href="http://boxcar.io/client" data-role="button" target="_blank">Boxcar</a>
                    <p>Free app for iOS</p>
                    <p>Received instantaneously</p>
                    <p>Works anywhere you have a signal, either cellular or wi-fi </p>
                </div>
            </div>
            <div data-role="collapsible" data-collapsed="true">
                <h4>Pushbullet</h4>
                <div class="content">
                    <a href="http://pushbullet.com" data-role="button" target="_blank">Pushbullet</a>
                    <p>Free app for iOS, Android (although changing to "freemium" model)</p>
                    <p>Requires installation of browser extension in Firefox, Chrome, or Safari</p>
                    <p>Messages can also be received in browser</p>
                    <p>Received instantaneously</p>
                    <p>Works anywhere you have a signal, either cellular or wi-fi </p>
                </div>
            </div>
            <div data-role="collapsible" data-collapsed="true">
                <h4>Pushover</h4>
                <div class="content">
                    <a href="http://pushover.net" data-role="button" target="_blank">Pushover</a>
                    <p>iOS and Android app $4.99</p>
                    <p>Received instantaneously</p>
                    <p>Supports Smart Numbers</p>
                    <p>Works anywhere you have a signal, either cellular or wi-fi </p>
                    <p>Also useful for other notifications</p>
                </div>
            </div>
            <div data-role="collapsible" data-collapsed="true">
                <h4>TigerText</h4>
                <div class="content">
                    <a href="http://tigertext.com" data-role="button" target="_blank">TigerText</a>
                    <p>Free app for iOS and Android</p>
                    <p>Received instantaneously</p>
                    <p>Supports Smart Numbers</p>
                    <p>Works anywhere you have a signal, either cellular or wi-fi</p>
                    <p>Peer-to-peer secure messaging</p>
                    <p>Can send audio, images, video</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
