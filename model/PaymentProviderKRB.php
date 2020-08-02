<?php namespace BtcRelax\Model;

class PaymentProviderKRB extends \BtcRelax\Model\PaymentProvider
{    
    protected $_currencyTitle = 'Карбо';
    protected $_currencyCode = \BtcRelax\RE::KRB;
    protected $_logoUrl = "\img\s_KRB.gif";
    
    
    public function __construct() {
        parent::__construct();
    }

    public function initBySallerProperties($pSaller){
        $result = false;
        
        
        return $result;        
    }

    public function getProviderCode() {
        return 'KRB';
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
