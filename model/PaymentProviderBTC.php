<?php namespace BtcRelax\Model;

use BtcRelax\Log;
use BtcRelax\RE;
use function GuzzleHttp\json_decode;

class PaymentProviderBTC extends PaymentProvider
{    
    public $_xpub;
    public $_xpub_step;
    public $_hd;
    
    public function __construct() {
        $this->_hd = new \BtcRelax\HD();
        parent::setCurrencyTitle('Bitcoin');
        parent::setCurrencyCode(RE::BTC);
        parent::setLogoUrl("\img\s_BTCBTC.png");
        parent::__construct();
    }
    
    public function doInvoiceSavedEvent($pDB = null)  {
        $vSallerId = (string) $this->_sallerId;
        $vNewProperty = \BtcRelax\Model\CustomerProperty::WithParams($vSallerId, 'xpub_step', $this->_xpub_step + 1 );
        $vCustomerPropDao = new \BtcRelax\Dao\CustomerPropertyDao($pDB, false);
        $vCustomerPropDao->insert($vNewProperty);                
        return parent::doInvoiceSavedEvent($pDB);
    }

    public function initBySallerProperties($pSaller)
    {
        $result = false;
        $this->_xpub = $pSaller->getPropertyValue('xpub');
        $this->_xpub_step = $pSaller->getPropertyValue('xpub_step');
        if (($this->_xpub !== FALSE) && ($this->_xpub_step !== FALSE))
        {
                $result  = $this->createNewWallet();
                $this->setIsInited( $pSaller , $result);
        }
        return $this->isInited();
    }
    
   
    function getXpub() {
        return $this->_xpub;
    }

    function getXpubStep() {
        return $this->_xpub_step;
    }
    
    
    public function getProviderCode() {
        return 'BTC';
    }

    public function createNewWallet(array $pAdditionalParams = null) {
        $result=false;
        $xpub = $this->getXpub();
            $path = \sprintf('0/%d', $this->getXpubStep());
            $this->_hd->set_xpub($xpub);
            $newAddress = $this->_hd->address_from_xpub($path);
            $getBallanceResult = $this->_hd->get_address_balance($newAddress);
            if (is_numeric($getBallanceResult))
            {
                parent::setInitialBallance($getBallanceResult);
                parent::setInvoiceAddress($newAddress);                
                $result =  true;
            }
            else {
                parent::setLastError($getBallanceResult['error_message']);
            }
            return $result;
    }

    public function getBallanceByWallet() {
        $result = false;
        $balance = $this->_hd->get_address_balance(parent::getInvoiceAddress());
        if (is_numeric($balance))
            {
                //parent::setBalanceDate(\getdate());
                //parent::setInvoiceBalance($balance);
                $result = $balance;
            }
        return $result;
    }

    public function formatPrice(float $pPrice): float {
        return \round($pPrice, 8);
    }

}
