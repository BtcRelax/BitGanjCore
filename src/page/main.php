<?php
namespace BtcRelax\page\main;

// Need to check are some identity was done
//$currentSession = $core->getCurrentSession();
$current_state = \BtcRelax\Session::getSessionState();

switch ($current_state) {
    case \BtcRelax\Session::STATUS_NOT_INIT:
        break;
    case \BtcRelax\Session::STATUS_UNAUTH:
        break;
    case \BtcRelax\Session::STATUS_AUTH_PROCESS:
        break;
    case \BtcRelax\Session::STATUS_ROOT:
    case \BtcRelax\Session::STATUS_USER:
        \BtcRelax\Utils::redirect('user');
        break;
    case \BtcRelax\SecureSession::STATUS_BANNED:
        \BtcRelax\Utils::redirect('banned');
        break;
    default:
        throw new \BtcRelax\Exception\SessionException(\sprintf("Unknown status %s on page:guest", $status));
}