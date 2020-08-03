<?php
namespace BtcRelax\Model;

abstract class Identicator
{
    const ID_TYPE_BITID     = "BitId";
    const ID_TYPE_MAIL      = "EmailId";
    const ID_TYPE_TELEGRAMM = "TelegramId";
    const ID_TYPE_ANDROID   = "AndroidId";
    
    const STATE_UNIDENTIFIED = -1;
    const STATE_INPROCESS = 0;
    const STATE_IDENTIFIED = 1;

    
    protected $vIdentityKey;
    protected $vAuthenticationState = Identicator::STATE_UNIDENTIFIED;
    protected $vNonce;
    protected $vSid = null;
    //protected $vBitId;
    protected $vCreateDate;
    protected $vIdIdentity;
    protected $vIdentTypeCode;
    protected $vEndDate;
    protected $vDescription;
    protected $vIdCustomer = null;
    protected $vLastError = null;
    protected $vAuthParams;


    public function __construct()
    {
        $vBitId = new \BtcRelax\BitID();
        $this->vNonce = $vBitId->generateNonce(8);
        if (\BtcRelax\SecureSession::isSessionStarted()) {
            $this->vSid = \session_id();
        }
    }
    
    public function getDescription()
    {
        return $this->vDescription;
    }

    public function setDescription($vDescription)
    {
        $this->vDescription = $vDescription;
    }

        
    
    public function getAuthParams():array
    {
        $result = ["authType"=> $this->getIdentTypeCode()];
        return $result += $this->getAuthParams();
    }

    public function setAuthParams(array $vAuthParams)
    {
        $this->vAuthParams = $vAuthParams;
    }

        
    public function getLastError()
    {
        return $this->vLastError;
    }

    public function setLastError($vLastError = null)
    {
        $this->vLastError = $vLastError;
    }

    public function setIdIdentity($vIdIdentity)
    {
        $this->vIdIdentity = $vIdIdentity;
    }

    public function getIdCustomer()
    {
        return $this->vIdCustomer;
    }

    public function setIdCustomer($vIdCustomer)
    {
        $this->vIdCustomer = $vIdCustomer;
    }

        
    public function getAuthenticationState()
    {
        return $this->vAuthenticationState;
    }

    protected function setAuthenticationState($vNewState)
    {
        if ($this->getSid()) {
            if ($this->vAuthenticationState != $vNewState) {
                $msg = \sprintf('SessionId:%s has changed state from %s to %s', $this->vSid, $this->vAuthenticationState, $vNewState);
                \BtcRelax\Log::general($msg, \BtcRelax\Log::INFO);
                $this->vAuthenticationState = $vNewState;
            }
        }
    }


    public function getCreateDate()
    {
        return $this->vCreateDate;
    }

    public function getIdentTypeCode()
    {
        return $this->vIdentTypeCode;
    }

    public function setCreateDate($vCreateDate)
    {
        $this->vCreateDate = $vCreateDate;
        return $this;
    }

    public function setIdentTypeCode($vIdentTypeCode)
    {
        $this->vIdentTypeCode = $vIdentTypeCode;
        return $this;
    }

    public function setEndDate($vEndDate)
    {
        $this->vEndDate = $vEndDate;
        return $this;
    }

    public function getIdIdentity()
    {
        return $this->vIdIdentity;
    }

    public function getEndDate()
    {
        return $this->vEndDate;
    }

    public function getNonce()
    {
        return $this->vNonce;
    }
    
    public function getSid()
    {
        $result = $this->vSid === null? false: $this->vSid;
        return $result;
    }

        
    public function getIdentityKey()
    {
        return $this->vIdentityKey;
    }

    final public function qrCode($uri)
    {
        $vBitId = new \BtcRelax\BitID();
        return $vBitId->qrCode($uri);
    }
    
    final public function setIdentityKey($vIdentityKey)
    {
        $this->vIdentityKey = $vIdentityKey;
        $this->setAuthenticationState(Identicator::STATE_IDENTIFIED);
        return $this;
    }
   
    protected function saveSession()
    {
        \BtcRelax\SecureSession::setValue($this->vIdentTypeCode, $this);
    }
    
    protected function loadSession()
    {
        return \BtcRelax\SecureSession::getValue($this->vIdentTypeCode);
    }

    public function __toString()
    {
        return sprintf('Type:%s ;State:%s ;Key: %s', $this->getIdentTypeCode(), $this->getAuthenticationState(), $this->getIdentityKey());
    }

    public static function createInstanceByType($pIdentType)
    {
        $result = false;
        switch ($pIdentType) {
            case \BtcRelax\Model\Identicator::ID_TYPE_BITID:
                $result = new \BtcRelax\Model\IdentBitId();
                break;
            case \BtcRelax\Model\Identicator::ID_TYPE_MAIL:
                $result = new \BtcRelax\Model\IdentEMail();
                break;
            case \BtcRelax\Model\Identicator::ID_TYPE_TELEGRAMM:
                $result = new \BtcRelax\Model\IdentTelegram();
                break;
            default:
                throw  new \BtcRelax\Exception\NotFoundException(sprintf("Itentifier type:%s unknown", $vIdentType));
                break;
        }
        return $result;
    }
    
    abstract public function init();
    abstract public function processAuth($vParams);
    abstract public function checkAuth($vParams);
    abstract public function doAuthenticate($vParams);
    abstract public function getAuthForm();
    abstract public function getForm();
        
    public static function allStatuses()
    {
        return [
            self::STATE_IDENTIFIED,
            self::STATE_INPROCESS,
            self::STATE_UNIDENTIFIED,
        ];
    }
    
    public static function allTypes()
    {
        return [
            self::ID_TYPE_BITID,
            self::ID_TYPE_MAIL,
            self::ID_TYPE_TELEGRAMM,
            self::ID_TYPE_ANDROID,
        ];
    }
}
