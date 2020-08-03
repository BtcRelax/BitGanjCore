<?php
namespace BtcRelax\Model;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class IdentEMail extends Identicator
{
    private $vMail;
    private $vIsSent = false;

    private $vVerificationCode;

    
    public function __construct()
    {
        parent::__construct();
        parent::setIdentTypeCode(Identicator::ID_TYPE_MAIL);
    }
    
    public function getId()
    {
        return parent::getId();
    }
    
    public function getIsSent()
    {
        return $this->vIsSent;
    }

    public function setIsSent($vIsSent)
    {
        if ($this->vIsSent !== $vIsSent) {
            $this->vIsSent = $vIsSent;
        }
    }

    public function getMail()
    {
        return $this->vMail;
    }

    public function setMail($vMail)
    {
        $this->vMail = $vMail;
        $this->setIsSent(!empty($vMail));
        return;
    }

    public function is_mail($mail)
    {
        if (preg_match("/^[0-9a-zA-Z\.\-\_]+\@[0-9a-zA-Z\.\-\_]+\.[0-9a-zA-Z\.\-\_]+$/is", trim($mail))) {
            return true;
        }
        return false;
    }
    
    public function sendCode($vMail)
    {
        return $this->processAuth(['vMail' => $vMail]);
    }

    public function getAuthForm()
    {
        $jFunction = 'JApp.sendMailId();';
        $templ = '<form id="frmLoginMail" class="form-horizontal" action="#" method="post">
                    <div class="form-group">
                        <div class="col-md-12">%s</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">%s</div>
                    </div>                    
                    <div class="form-group">
                        <div class="col-md-12"><center>
                            <button id="idMailSend" type="button" onclick="%s" class="btn btn-primary">Отправить код проверки</button>
                        </center></div>
                    </div>
                    <input type="hidden" name="checkId" value="%s" />
                </form>';
        $codeInput='';
        if (!empty($this->vMail) && $this->vIsSent) {
            $inputEl = sprintf('<input  id="inputEmail" type="email" name="mail" value="%s" readonly class="form-control" placeholder="Укажите e-mail!" required>', $this->vMail);
            $codeInput= '<input type="text" name="mailCode" class="form-control" id="inputVCode" placeholder="Укажите код доступа, что пришёл в письме" required>';
            $jFunction = 'JApp.checkMailCode();';
        } else {
            $inputEl = '<input type="email" name="mail"  class="form-control" id="inputEmail" placeholder="Укажите e-mail!" required>';
        }
        $result= sprintf($templ, $inputEl, $codeInput, $jFunction, $this->getIdentTypeCode());
        return $result;
    }

    public function getForm()
    {
        $vState = '';
        if (!empty($this->vMail) && $this->vIsSent) {
            $vState = 'in';
        }
        $vfrms = sprintf('<div id="id%sIdent" class="panel panel-default">
                <div class="panel-heading">
                  <center><h1>
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseMailId">E-mail</a>
                  </h1></center>
                </div>
                <div id="collapseMailId" class="panel-collapse collapse %s">
                  <div class="panel-body">%s</div>
                </div>
              </div>', $this->getIdentTypeCode(), $vState, $this->getAuthForm(), $this->getIdentTypeCode());
        return $vfrms;
    }
    

    public function checkAuth($vParams)
    {
        $checkResult = false;
        if (key_exists('mailCode', $vParams)) {
            $this->vVerificationCode = $vParams['mailCode'];
            if ($this->vIsSent) {
                $checkResult = $this->vNonce === $this->vVerificationCode;
                if (!$checkResult) {
                    \BtcRelax\Log::general(\sprintf('Incorrect mail verification code. Input:%s while wait for: %s', $this->vVerificationCode, $this->getNonce()), \BtcRelax\Log::WARN);
                    $this->vLastError = 'Некорректно узказан код подтверждения!';
                }
            }
        }
        return $checkResult;
    }

    public function doAuthenticate($vParams)
    {
        $checkResult = $this->checkAuth($vParams);
        if ($checkResult) {
            $this->setIdentityKey($this->vMail);
            $this->vLastError = '';
            $this->setMail(null);
        } else {
            \BtcRelax\Log::general(\sprintf('Incorrect mail verification code. Input:%s while wait for: %s', $this->vVerificationCode, $this->getNonce()), \BtcRelax\Log::WARN);
            $this->vLastError = 'Некорректно узказан код подтверждения!';
        }
        parent::saveSession();
        return $checkResult;
    }

    public function init()
    {
        $this->setMail(null);
        $this->vLastError = false;
    }

    public function processAuth($vParams)
    {
        $result = false;
        if (\array_key_exists('mail', $vParams)) {
            $vMail = $vParams['mail'];
            //if ($this->is_mail($vMail))
            if (\BtcRelax\Utils::is_mail($vMail)) {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->setFrom(SMTP_SEND_FROM, 'BitGanj shop');

                /* Set the SMTP port. */
                $mail->Host = SMTP_SERVER;
                $mail->Port = SMTP_PORT;
                $mail->SMTPAutoTLS = false;
                $mail->SMTPAuth = !empty(SMTP_PASS);
                if ($mail->SMTPAuth) {
                    $mail->Username = SMTP_NAME;
                    $mail->Password = SMTP_PASS;
                    if (!empty(SMTP_SECURE)) {
                        $mail->SMTPSecure = SMTP_SECURE;
                    }
                }
                $mail->addAddress($vMail);
                $mail->Subject = 'Код доступа от магазина BitGanj';
                $mail->CharSet = 'utf8';
                //$mail->SMTPDebug = 4;
                $vCode = parent::getNonce();
                $mail->msgHTML(\sprintf('Что бы войти на сайт, введите код: %s', $vCode));
                $doSend = $mail->send();
                if ($doSend) {
                    \BtcRelax\Log::general(\sprintf("Mail to:%s with code %s was sent!", $vMail, $vCode), \BtcRelax\Log::INFO);
                    $this->setMail($vMail);
                    $this->setAuthParams(['mail' => $vMail, 'isCodeSent' => true]);
                    $this->setAuthenticationState(Identicator::STATE_INPROCESS);
                    $this->vLastError = false;
                    $result = true;
                    parent::saveSession();
                } else {
                    \BtcRelax\Log::general(\sprintf("Error sending mail:%s", $mail->ErrorInfo), \BtcRelax\Log::WARN);
                    $this->setMail(null);
                    $this->setLastError($mail->ErrorInfo);
                }
            } else {
                $this->setLastError("Incorrect e-mail format!");
            }
        }
        return $result;
    }
}
