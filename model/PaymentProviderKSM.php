<?php namespace BtcRelax\Model;

class PaymentProviderKSM extends \BtcRelax\Model\PaymentProvider
{
    
    protected $_currencyTitle = 'KiyvstarMoney';
    protected $_currencyCode = \BtcRelax\RE::KSM;
    protected $_logoUrl = "\img\s_KSM.png";
    
    public function createNewWallet(array $pAdditionalParams = null) {
        
    }

    public function formatPrice(float $pPrice): float {
        
    }

    public function getBallanceByWallet() {
        
    }

    public function getProviderCode() {
        return 'KSM';
    }

    public function initBySallerProperties($pSaller) {
        
    }

}
