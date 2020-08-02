<?php
namespace BtcRelax;

switch (\BtcRelax\SecureSession::getSessionState()) {
        case \BtcRelax\SecureSession::STATUS_ROOT:
        case \BtcRelax\SecureSession::STATUS_USER:           
            $vAM = \BtcRelax\Core::createAM();
            $cUser = $vAM->getUser();
            $vAlias = empty($cUser->getPropertyValue("alias_nick"))?"Аноним": $cUser->getPropertyValue("alias_nick");    
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

