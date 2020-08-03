<?php namespace BtcRelax\Model;

class PaymentProviderBCH extends \BtcRelax\Model\PaymentProvider
{
    public function __construct()
    {
        $this->_currencyTitle = 'Bitcoin Cash';
        $this->_currencyCode = \BtcRelax\RE::BCH;
        $this->_logoUrl = "\img\s_BCHBCH.png";
        parent::__construct();
    }

    public function initBySallerProperties($pSaller)
    {
        $result = false;
        return $result;
    }

    public function getProviderCode()
    {
        return 'BCH';
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
