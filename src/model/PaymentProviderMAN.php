<?php namespace BtcRelax\Model;

class PaymentProviderMAN extends \BtcRelax\Model\PaymentProvider
{
    protected $_currencyTitle = 'Manual';
    protected $_currencyCode = \BtcRelax\RE::MAN;
    protected $_logoUrl = "\img\s_man.png";
    
    
    public function __construct()
    {
        parent::__construct();
    }

    public function initBySallerProperties($pSaller)
    {
        $result = false;
        
        
        return $result;
    }

    public function getProviderCode()
    {
        return 'MAN';
    }

    public function createNewWallet(array $pAdditionalParams = null)
    {
    }

    public function getBallanceByWallet()
    {
    }

    public function formatPrice(float $pPrice): float
    {
        return \round($pPrice, 8);
    }

    //put your code here
}
