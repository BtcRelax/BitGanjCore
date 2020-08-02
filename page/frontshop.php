<?php

namespace BtcRelax\page\frontshop;

switch (\BtcRelax\SecureSession::getSessionState()) {
    case \BtcRelax\SecureSession::STATUS_ROOT:
        break;
    case \BtcRelax\SecureSession::STATUS_USER:
        break;
    case \BtcRelax\SecureSession::STATUS_GUEST:
        \BtcRelax\Utils::redirect('guest');
        break;
    default:
        \BtcRelax\Utils::redirect('main');
        break;
}

$vAM = \BtcRelax\Core::createAM();
$cUser = $vAM->getUser();
$om = \BtcRelax\Core::createOM();
$activeOrder = $om->getActualOrder();



function renderGetActiveBookmarks()
{
    $result = "";
    $vPM = \BtcRelax\Core::createPM();
    $bList = $vPM->actionGetFrontshopBookmarks();
    foreach ($bList as $curPoint) {
        $result .= $curPoint->GetPublicForm();
    }
    return empty($result) ? "<div id=\"dialogEmptyFrontshop\" title=\"Закладки отсутствуют\"><div id=\"lhc_form_embed_container\" ></div></div>": $result ;
}

if (!function_exists('sys_get_temp_dir')) {
    function sys_get_temp_dir()
    {
        if (!empty($_ENV['TMP'])) {
            return realpath($_ENV['TMP']);
        }
        if (!empty($_ENV['TMPDIR'])) {
            return realpath($_ENV['TMPDIR']);
        }
        if (!empty($_ENV['TEMP'])) {
            return realpath($_ENV['TEMP']);
        }
        $tempfile = tempnam(__FILE__, '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
            return \realpath(dirname($tempfile));
        }
        return null;
    }
} 


