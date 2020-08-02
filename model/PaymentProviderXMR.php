<?php namespace BtcRelax\Model;

class PaymentProviderXMR extends \BtcRelax\Model\PaymentProvider
{    
    protected $_currencyTitle = 'Monero';
    protected $_currencyCode = \BtcRelax\RE::XMR;
    protected $_logoUrl = "\img\s_XMRXMR.png";    
    
    
    public function __construct() {
        parent::__construct();
    }

    public function initBySallerProperties($pSaller){
        $result = false;
        
        
        return $result;        
    }

    public function getProviderCode() {
        return 'XMR';
    }

    public function createNewWallet(array $pAdditionalParams = null) {
        
    }

    public function getBallanceByWallet() {
        
    }

    public function formatPrice(float $pPrice): float {
        return \round($pPrice, 8);
    }

    //put your code here
}
