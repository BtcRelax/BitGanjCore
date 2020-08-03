<?php
namespace BtcRelax\page\invite;

switch (\BtcRelax\SecureSession::getSessionState()) {
        case \BtcRelax\SecureSession::STATUS_ROOT:
        case \BtcRelax\SecureSession::STATUS_USER:
            $vReq = $core->getRequest();
            if (array_key_exists('action', $vReq)) {
                if ($vReq['action'] === 'doInvite') {
                    \BtcRelax\page\invite\doInvite();
                }
            }
            break;
        case \BtcRelax\SecureSession::STATUS_GUEST:
            \BtcRelax\Utils::redirect('guest');
            break;
        case \BtcRelax\SecureSession::STATUS_BANNED:
            \BtcRelax\Utils::redirect('banned');
            break;
        case \BtcRelax\SecureSession::STATUS_UNAUTH:
            \BtcRelax\Utils::redirect('main');
            break;
        default:
            throw new \BtcRelax\Exception\SessionException(\sprintf("Unknown status %s on page:guest", $status));
}

function doInvite()
{
    global $core;
    $vAM = \BtcRelax\Core::createAM();
    $vSession = $core->getCurrentSession();
    $vInvitor = $vAM->getUser();
    $vSession->setValue('Invitor', $vInvitor);
    $vAM->SignOut();
    \BtcRelax\Utils::redirect('main');
}
