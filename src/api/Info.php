<?php
namespace BtcRelax\api;

final class Info extends \BtcRelax\API
{
    public function processApi()
    {
        parent::process();
    }

    /// Functions of that controller
    public function getver()
    {
        $param = \BtcRelax\Core::getVersion();
        \BtcRelax\API::response($param, 200);
    }
    
    public function getsessionstate()
    {
        $param["SessionState"] = \BtcRelax\SecureSession::getSessionState();
        $param["CallerIp"] = \BtcRelax\Utils::getIpAddress();
        $param["CallerFromProxy"] = \BtcRelax\Utils::getIpAddressFromProxy();
        switch ($param["SessionState"]) {
            case \BtcRelax\SecureSession::STATUS_NOT_INIT:
                break;
            case \BtcRelax\SecureSession::STATUS_AUTH_PROCESS:
                $vAM = \BtcRelax\Core::createAM();
                $vAuthenticator = $vAM->getActiveIdent();
                $param["StartAuthResponce"] =  $vAuthenticator->getAuthParams();
                // no break
            default:
                $param["SessionLifeTime"] = \BtcRelax\SecureSession::getSessionLifetime();
                break;
            }
        \BtcRelax\API::response($param);
    }
    
    public function getconfig()
    {
        $param["InstanceId"] = \filter_input(\INPUT_SERVER, "SERVER_NAME");
        $param["LHC_url"] = LHC_URL;
        \BtcRelax\API::response($param);
    }
}
