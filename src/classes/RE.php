<?php
namespace BtcRelax;

use BtcRelax\Utils;

class RE implements IRE
{
    public static $CURRENCIES = [
        IRE::BTC => 'BTC',
        IRE::BCH => 'BCH',
        IRE::KRB => 'KRB',
        IRE::XMR => 'XMR',
        IRE::UAH => 'UAH',
        IRE::USD => 'USD',
        IRE::EPAY => 'EPAY',
        IRE::KSM => 'KSM',
        IRE::MAN => 'MAN',
    ];

    public $_paymentProviders = array();


    public function __construct()
    {
        if (IS_EASYPAY) {
            $vNewPP = new \BtcRelax\Model\PaymentProviderEPU();
            $this->_paymentProviders += [$vNewPP->getProviderCode() => $vNewPP];
        }
        if (IS_BCH) {
            $vNewPP = new \BtcRelax\Model\PaymentProviderBCH();
            $this->_paymentProviders += [$vNewPP->getProviderCode() => $vNewPP];
        }
        if (IS_KRB) {
            $vNewPP = new \BtcRelax\Model\PaymentProviderKRB();
            $this->_paymentProviders += [$vNewPP->getProviderCode() => $vNewPP];
        }
        if (IS_XMR) {
            $vNewPP = new \BtcRelax\Model\PaymentProviderXMR();
            $this->_paymentProviders += [$vNewPP->getProviderCode() => $vNewPP];
        }
        if (IS_BTC) {
            $vNewPP = new \BtcRelax\Model\PaymentProviderBTC();
            $this->_paymentProviders += [$vNewPP->getProviderCode() => $vNewPP];
        }
        if (IS_KSM) {
            $vNewPP = new \BtcRelax\Model\PaymentProviderKSM();
            $this->_paymentProviders += [$vNewPP->getProviderCode() => $vNewPP];
        }
        if (IS_MAN) {
            $vNewPP = new \BtcRelax\Model\PaymentProviderMAN();
            $this->_paymentProviders += [$vNewPP->getProviderCode() => $vNewPP];
        }
    }
    
    public function actionGetInvoicesByOrder(\BtcRelax\Model\Order $vOrder)
    {
        $vInvoices = $vOrder->getInvoices();
        if (empty($vInvoices)) {
            $vSearch = new \BtcRelax\Dao\InvoiceSearchCriteria(["idOrder" => $vOrder->getIdOrder(),"isActive"=> false]);
            $vInvoiceDao = new \BtcRelax\Dao\InvoiceDao();
            $vInvoices = $vInvoiceDao->find($vSearch);
            $vOrder->setInvoices($vInvoices);
        }
        return $vInvoices;
    }

    public function renderGetInvoicesByOrder(\BtcRelax\Model\Order $vOrder)
    {
        $vInvoices = $this->actionGetInvoicesByOrder($pOrder);
        $result = [];
        foreach ($vInvoices as $cInvoice) {
            array_push($result, ['id' => $cInvoice->getIdInvoices(),
               'Title' => $cInvoice->getAdvertiseTitle(), 'RegionTitle' => $cBookmark->getRegionTitle()]);
        }
        return $result;
    }
    
    public function getBTCPrice($price, $currency = 'UAH')
    {
        $result = null;
        if (!is_numeric($price)) {
            throw new \LogicException('Price has incorrect value!');
        }
        switch ($currency) {
            case IRE::UAH:
                $vExchangeResult = $this->getExchangeRate();
                $result = 1/$vExchangeResult*$price;
                break;
            default:
                throw new \LogicException('Price set with incompatible currency!');
        }
        if (!is_numeric($result)) {
            throw new \LogicException('Price undefined!');
        }
        return $result;
    }
    
    // Calculate price by order and use specific provider to create invoice
    // Result, object invoice
    public function createInvoice(\BtcRelax\Model\Order $vOrder, \BtcRelax\Model\PaymentProvider $vProvider)
    {
        $result = false;
        $vBookmark = $vOrder->getBookmark(); //$vPM->getBookmarkById($vIdBookmark);
        $vInvoiceCurrency= $vProvider->getCurrencyCode();
        $vInvoicePrice = $this->getPriceByExchangeRate($vBookmark->getPriceCurrency(), $vInvoiceCurrency, $vBookmark->getCustomPrice());
        $vAdditionalParams = ["BookmarkId" => $vBookmark->getIdBookmark() ];
        if ($vProvider->createNewWallet($vAdditionalParams)) {
            $result = new \BtcRelax\Model\Invoice($vInvoiceCurrency, $vInvoicePrice, $vProvider->getInvoiceAddress(), $vProvider->getInitialBallance());
        }
        return $result;
    }
    
    
    /// Convert price from source to target currency
    private function getPriceByExchangeRate($pSourceCurrency, $pTargetCurrency, $pPrice)
    {
        $result = null;
        if (!is_numeric($pPrice)) {
            throw new \LogicException('Price has incorrect value!');
        }
        if ($pSourceCurrency===$pTargetCurrency) {
            return $pPrice;
        }
        
        switch ($pSourceCurrency) {
            case IRE::UAH:
            {
                switch ($pTargetCurrency) {
                    case IRE::EPAY:
                    {
                        $result = $pPrice;
                        break;
                    }
                    case IRE::BTC:
                    {
                        $vExchangeResult = $this->getExchangeRate();
                        $result = 1/$vExchangeResult*$pPrice;
                        break;
                    }
                    default:
                        throw new \LogicException('Incompatible target currency!');
                }
                break;
            }
            default:
                throw new \LogicException('Incompatible source currency!');
        }
        if (!is_numeric($result)) {
            throw new \LogicException('Price undefined!');
        }
        return $result;
    }
        
    private function getExchangeRate()
    {
        $req = Utils::httpGet("https://kuna.io/api/v2/tickers/btcuah");
        $json = json_decode($req);
        if (json_last_error() === JSON_ERROR_NONE) {
            $last = $json->ticker->last;
            if (is_numeric($last)) {
                return $json->ticker->last;
            }
        }
        throw  new \BtcRelax\Exception\NotFoundException("Cannot get exchange rate!");
    }

    
    public function initPaymentProviders(\BtcRelax\Model\User $vSaller)
    {
        $result = [];
        foreach ($this->_paymentProviders as $paymentProvider) {
            $paymentProvider->initBySallerProperties($vSaller);
            $result += [$paymentProvider->getProviderCode() => $paymentProvider];
        }
        return $result;
    }
    
    public function initInvoicesProviders($pPaymentProviders, $pInvoices)
    {
        $result = [];
        $this->_paymentProviders = $pPaymentProviders;
        foreach ($pInvoices as $pInvoice) {
            if (empty($pInvoice->getPaymentProvider())) {
                foreach ($pPaymentProviders as $paymentProvider) {
                    if ($paymentProvider->getCurrencyCode() === $pInvoice->getCurrency()) {
                        $pInvoice->setPaymentProvider($paymentProvider);
                    }
                }
            }
        }
        return $result;
    }

    public function getInvoiceInfoById($vParams)
    {
        $pId = $vParams['id'];
        $pIsCheckBalance = $vParams['isNeedCheckBalance'];
        $vInvoiceDao = new \BtcRelax\Dao\InvoiceDao();
        $vInvoice = $vInvoiceDao->findById($pId);
        if (\FALSE !== $vInvoice) {
            if ($pIsCheckBalance) {
                $vOrderId = $vInvoice->getIdOrder();
                $vOM = \BtcRelax\Core::createOM();
                $checkResult = $vOM->checkPaymentByOrderId($vOrderId);
                \BtcRelax\Log::general(\sprintf("Cheking ballance for invoice by order id:%s was %s ", $vOrderId, $checkResult), \BtcRelax\Log::INFO);
            }
            $vSearchCriteriaI = new \BtcRelax\Dao\InvoiceSearchCriteria(["idInvoice" => $pId, "isActive" => false]);
            $vInvoice = $vInvoiceDao->fullInvoices($vSearchCriteriaI);
            $result = [ "invoice" => $vInvoice[0] ];
        } else {
            $result = ["error"=> "Invoice not found"];
        }
        return $result;
    }
}
