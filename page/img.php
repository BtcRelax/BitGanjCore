<?php 
    require('BtcRelax/core.inc');
    $core = \BtcRelax\Core::getIstance(false);
    $bitid = new \BtcRelax\BitID();
    try {
        $vRequest = $core->getRequest();
        if (array_key_exists('uri', $vRequest))
        {
                $uri = $vRequest['uri'];
                $nonceFile = \sprintf('%s.png', $bitid->extractNonce($uri));
                $tempFile = \sprintf('%s/%s', sys_get_temp_dir(), $nonceFile);
                if (array_key_exists('u', $vRequest)) { $uri  .= "&u=1"; }
                if (!file_exists($tempFile)) {
                    \QRcode::png($uri, $tempFile, QR_ECLEVEL_L, 6);
            }
            header('content-type: image/png');
            header('content-disposition: inline; filename="' . $nonceFile . '";');
            readfile($tempFile);        
        } else {
            \BtcRelax\Log::general('Incorrect call to img.php. Without URI parameter!', \BtcRelax\Log::FATAL);
            }
        } catch (\Exception $exc) { \BtcRelax\Log::general($exc, \BtcRelax\Log::ERROR); }
