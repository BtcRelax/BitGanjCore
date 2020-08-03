<?php
namespace BtcRelax\page\main;

global $core;
// Need to check are some identity was done
//$currentSession = $core->getCurrentSession();
$current_state = \BtcRelax\SecureSession::getSessionState();

switch ($current_state) {
    case \BtcRelax\SecureSession::STATUS_NOT_INIT:
        break;
    case \BtcRelax\SecureSession::STATUS_UNAUTH:
        $vAM = \BtcRelax\Core::createAM();
        $vIdents = $vAM->getIdentifiers();
        $vInvitor = $currentSession->getValue('Invitor', $vInvitor);
        if ($vInvitor instanceof \BtcRelax\Model\User) {
            $vInviteHeaderHtml = \sprintf("<header><center><h1 class=\"w3-display-topmiddle w3-panel w3-blue w3-round-medium\">Приглашение от пользователя %s</h1></center></header>", $vInvitor->getUserNameAlias());
        }
        if ($vIdents) {
            $vFrms = \BtcRelax\page\main\renderGetAuthForms($vIdents);
        }
        $vHtml = \sprintf('<div class="panel-group" id="accordion">%s</div>', $vFrms);
        break;
    case \BtcRelax\SecureSession::STATUS_AUTH_PROCESS:
        $vReq = $core->getRequest();
        $vAM = \BtcRelax\Core::createAM();
        $vIdents = $vAM->getIdentifiers();
        if (array_key_exists('checkId', $vReq)) {
            $vIdentType = $vReq['checkId'];
            $vIdents = $vAM->doAuthentificate($vReq);
            if (\BtcRelax\SecureSession::getSessionState() === \BtcRelax\SecureSession::STATUS_GUEST) {
                \BtcRelax\Utils::Redirect('guest');
            }
            if (\BtcRelax\SecureSession::getSessionState() === \BtcRelax\SecureSession::STATUS_USER) {
                \BtcRelax\Utils::Redirect('user');
            }
            if (\BtcRelax\SecureSession::getSessionState() === \BtcRelax\SecureSession::STATUS_BANNED) {
                \BtcRelax\Utils::Redirect('banned');
            }
        }
        
        if ($vIdents) {
            $vFrms = \BtcRelax\page\main\renderGetAuthForms($vIdents);
        }
        $vHtml = \sprintf('<div class="panel-group" id="accordion">%s</div>', $vFrms);
        break;
    case \BtcRelax\SecureSession::STATUS_ROOT:
    case \BtcRelax\SecureSession::STATUS_USER:
        \BtcRelax\Utils::redirect('user');
        break;
    case \BtcRelax\SecureSession::STATUS_BANNED:
        \BtcRelax\Utils::redirect('banned');
        break;
    default:
        throw new \BtcRelax\Exception\SessionException(\sprintf("Unknown status %s on page:guest", $status));
}
function renderGetAuthForms($vIdents)
{
    $vResult = "";
    if ($vIdents instanceof \BtcRelax\Model\Identicator) {
        $vResult = $vIdents->getForm();
    } else {
        foreach ($vIdents as $vIdent) {
            $vNewForm = $vIdent->getForm();
            $vResult = $vResult . $vNewForm;
        }
    }
    return $vResult;
}
