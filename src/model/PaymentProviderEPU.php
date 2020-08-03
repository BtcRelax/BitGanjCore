<?php namespace BtcRelax\Model;

use BtcRelax\RE;

class PaymentProviderEPU extends PaymentProvider
{
    public $_epayApi;
    public $_User;
    public $_Password;


    /// EasyPayUa
    public function __construct()
    {
        parent::__construct();
        parent::setCurrencyCode(RE::EPAY);
        parent::setLogoUrl("\img\s_EPU.gif");
        parent::setCurrencyTitle("EasyPay");
    }

    public function initBySallerProperties($pSaller)
    {
        $this->_User = $pSaller->getPropertyValue('epay_login');
        $this->_Password = $pSaller->getPropertyValue('epay_pass');
        if ($this->_User !== false && $this->_Password !== false) {
            $this->_epayApi = new \BtcRelax\EasyPayApi($this->_User, $this->_Password);
            $this->_epayApi->setProxyUrl(EPAY_PROXY);
            if (!$this->_epayApi->init()) {
                \BtcRelax\Log::general(\sprintf("Error while init payment provider easy pay. Message:%s", $this->_epayApi->getLastError()), \BtcRelax\Log::WARN);
                $this->setLastError($this->_epayApi->getLastError());
            }
            $this->setIsInited($pSaller, $this->_epayApi->isInited());
        }
        return $this->isInited();
    }
    
    public function createNewWallet(array $pAdditionalParams = null)
    {
        $result=false;
        $vNewName = \sprintf('%s_%s', date("hi"), substr($this->_User, 4));
        if (!empty($pAdditionalParams)) {
            if (array_key_exists('BookmarkId', $pAdditionalParams)) {
                $vNewName = \sprintf("BookmarkId:%s", $pAdditionalParams['BookmarkId']);
            }
        }
        $addWalletResult = $this->_epayApi->actionNewWallet($vNewName);
        if ($addWalletResult!==false) {
            parent::setInitialBallance($addWalletResult['balance']);
            parent::setInvoiceAddress($addWalletResult['number']);
            $result =  true;
        } else {
            parent::setLastError($this->_epayApi->getLastError());
        }
        return $result;
    }

    public function getBallanceByWallet()
    {
        $vInvoiceAddress = $this->getInvoiceAddress();
        $getBalanceResult = $this->_epayApi->actionGetWalletBalanceByAddress($vInvoiceAddress);
        if (is_numeric($getBalanceResult)) {
            $this->setLastError();
            return $getBalanceResult;
        } else {
            $this->setLastError($this->_epayApi->getLastError());
            return false;
        }
    }

    
    public function getProviderCode()
    {
        return 'EPU';
    }

    public function formatPrice(float $pPrice): float
    {
        return \round($pPrice, 2);
    }
}
