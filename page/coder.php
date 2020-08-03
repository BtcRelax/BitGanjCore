<?php
   
        require('BtcRelax/core.inc');
        //$core = new \BtcRelax\Core();
    //$core->init();
    $core = \BtcRelax\Core::getIstance();
        $status = \BtcRelax\SecureSession::getSessionState();
        if ($status == BtcRelax\SecureSession::STATUS_ROOT) {
            require_once('src/common.php');

            $_SESSION['user'] = 'chronos';
            $_SESSION['lang'] = 'en';
            $_SESSION['theme'] = 'default';
            $_SESSION['project'] = 'BtcRelax';
        }
