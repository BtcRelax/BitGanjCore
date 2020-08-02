<?php
namespace BtcRelax\api;

final class Session extends \BtcRelax\API {
    public function processApi() {
        parent::process();
    }

    /// Functions of that controller

    public function startauth() {
        global $core;
        $param["isSessionStarted"] = $core->startSession();
        $this->response($param,200);
    }
    
}
