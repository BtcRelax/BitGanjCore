<?php
namespace BtcRelax\page\guest;

global $core;
$vInFormHtml = null;
$vOutFormHtml = null;

switch (\BtcRelax\SecureSession::getSessionState()) {
    case \BtcRelax\SecureSession::STATUS_GUEST:
                $vAM = \BtcRelax\Core::createAM();
                $vIdent = $vAM->getActiveIdent();
                if (false !== $vIdent) {
                    $vIdentType = $vIdent->getIdentTypeCode();
                    switch ($vIdentType) {
                            case \BtcRelax\Model\Identicator::ID_TYPE_BITID:
                                $vInFormHtml = "<p>Ваша регистрация, будет завершена после нажатия кнопки ОК.</p><p><strong>Создайте резервную копию кошелька!</strong></p>";
                                break;
                            case \BtcRelax\Model\Identicator::ID_TYPE_MAIL:
                                $vInFormHtml = "<p>Ваша регистрация, будет завершена после нажатия кнопки ОК.</p><p><strong>Но, Ваш доступ не будет полноценным, поскольку Вы используете не надёжный метод аутентификации!</strong></p>";
                                break;
                            case \BtcRelax\Model\Identicator::ID_TYPE_TELEGRAMM:
                                $vInFormHtml = "<p>Ваша регистрация, будет завершена после нажатия кнопки ОК.</p><p><strong>Но, Ваш доступ не будет полноценным, поскольку Вы используете не надёжный метод аутентификации!</strong></p>";
                                break;
                            default:
                                $errorMessage = \sprintf("Identity type:%s", $vIdentType);
                                throw new \BtcRelax\Exception\NotFoundException($errorMessage);
                    }
                }
                $vOutFormHtml = '<script>$( function() {
                                    var vWidth = $(window).innerWidth() - 100;
                                    var regdialog = $( "#dialogRegister" ).dialog({
                                      beforeClose: function(event, ui) { 
                                             JApp.activatePage(\'kill\');
                                          },
                                      show: {
                                          effect: JApp.getEffect(),
                                          duration: 5000
                                      },
                                      buttons: {
                                          Ok: function() {
                                                  JApp.registerUser();
                                              }
                                          },
                                      width: vWidth,
                                    });
                                  });
                              </script>';
        break;
    case \BtcRelax\SecureSession::STATUS_ROOT:
    case \BtcRelax\SecureSession::STATUS_USER:
        \BtcRelax\Utils::redirect('user');
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
