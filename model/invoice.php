<?php
namespace BtcRelax\Model;

class Invoice
{
    const STATE_OVER = "OverPay";
    const STATE_WAIT = "WaitForPay";
    const STATE_CANCELED = "Canceled";
    const STATE_PAYED = "Payed";

    private $pIdInvoices;
    private $pIdOrder;
    private $pCurrency; //` enum('UAH','BTC','BCH','XMR','KRB','EPAY','EUR','USD') DEFAULT NULL,
    private $pPrice;
    private $pPricingDate;
    private $pInitialBallance;
    private $pInvoiceAddress;
    private $pInvoiceBalance;
    private $pBalanceDate;
    private $pCreateDate;
    private $pEndDate;
    private $pInvoiceState; //` enum('WaitForPay','Payed','Canceled','OverPay') DEFAULT 'WaitForPay',
    private $pPaymentProvider;


    public function __construct($pCurrency = null, $pPrice = null, $pInvoiceAddress = null, $pInitialBallance = null)
    {
        $this->pCurrency = $pCurrency;
        $this->pPrice = $pPrice;
        $this->pPricingDate = new \DateTime();
        $this->pCreateDate = new \DateTime();
        $this->pInitialBallance = $pInitialBallance;
        $this->pBalanceDate = new \DateTime();
        $this->pInvoiceAddress = $pInvoiceAddress;
        $this->pInvoiceBalance = $pInitialBallance;
        $this->pInvoiceState = self::STATE_WAIT;
    }

    public function getPaymentProvider()
    {
        return $this->pPaymentProvider;
    }

    public function setPaymentProvider($pPaymentProvider)
    {
        $this->pPaymentProvider = $pPaymentProvider;
    }
  
    public function getIdOrder()
    {
        return $this->pIdOrder;
    }

    public function setIdOrder($pIdOrder)
    {
        $this->pIdOrder = $pIdOrder;
    }

  
    
    public function getIdInvoices()
    {
        return $this->pIdInvoices;
    }

    public function getCurrency()
    {
        return $this->pCurrency;
    }

    public function getPrice()
    {
        return $this->pPrice;
    }

    public function getPricingDate()
    {
        return $this->pPricingDate;
    }

    public function getInitialBallance()
    {
        return $this->pInitialBallance;
    }

    public function getInvoiceAddress()
    {
        return $this->pInvoiceAddress;
    }

    public function getInvoiceBalance()
    {
        return $this->pInvoiceBalance;
    }
  
    public function getInvoiceBalanceDelta()
    {
        return $this->pInvoiceBalance - $this->pInitialBallance;
    }
  

    public function getBalanceDate()
    {
        return $this->pBalanceDate;
    }

    public function getCreateDate()
    {
        return $this->pCreateDate;
    }

    public function getEndDate()
    {
        return $this->pEndDate;
    }

    public function getInvoiceState()
    {
        return $this->pInvoiceState;
    }

    public function setIdInvoices($pIdInvoices)
    {
        $this->pIdInvoices = $pIdInvoices;
    }

    public function setCurrency($pCurrency)
    {
        $this->pCurrency = $pCurrency;
    }

    public function setPrice($pPrice)
    {
        $this->pPrice = $pPrice;
    }

    public function setPricingDate($pPricingDate)
    {
        $this->pPricingDate = $pPricingDate;
    }

    public function setInitialBallance($pInitialBallance)
    {
        $this->pInitialBallance = $pInitialBallance;
    }

    public function setInvoiceAddress($pInvoiceAddress)
    {
        $this->pInvoiceAddress = $pInvoiceAddress;
    }

    public function setInvoiceBalance($pInvoiceBalance)
    {
        $this->pInvoiceBalance = $pInvoiceBalance;
        $this->setBalanceDate(new \DateTime());
        $vPrice = $this->getPrice();
        if ($this->getInvoiceBalanceDelta() >= $vPrice) {
            if ($this->getInvoiceBalanceDelta() > $vPrice) {
                $this->setInvoiceState(\BtcRelax\Model\Invoice::STATE_OVER);
            } else {
                $this->setInvoiceState(\BtcRelax\Model\Invoice::STATE_PAYED);
            }
        }
    }

    public function setBalanceDate($pBalanceDate)
    {
        $this->pBalanceDate = $pBalanceDate;
    }

    public function setCreateDate($pCreateDate)
    {
        $this->pCreateDate = $pCreateDate;
    }

    public function setEndDate($pEndDate)
    {
        $this->pEndDate = $pEndDate;
    }

    public function setInvoiceState($pInvoiceState)
    {
        $this->pInvoiceState = $pInvoiceState;
        if (($pInvoiceState === \BtcRelax\Model\Invoice::STATE_CANCELED) ||
           ($pInvoiceState === \BtcRelax\Model\Invoice::STATE_OVER) ||
              ($pInvoiceState === \BtcRelax\Model\Invoice::STATE_PAYED)) {
            $this->setEndDate(new \DateTime());
        }
    }
  
    public function renderInvoice()
    {
        $result = false;
        $provider = $this->getPaymentProvider();
        if (!empty($provider)) {
            $result = ['created' => $this->getCreateDate()];
            if (!\is_null($this->getIdInvoices())) {
                $result += ['idInvoice' => $this->getIdInvoices()];
            }
            $result += ['logo' => $provider->getLogoUrl()];
            $result += ['currencyTitle' => $provider->getCurrencyTitle()];
            $result += ['price' => $provider->formatPrice($this->getPrice())];
            $result += ['priceDate' => $this->getPricingDate()];
            $result += ['address' => $this->getInvoiceAddress()];
            $result += ['balance' => $this->getInvoiceBalanceDelta()];
            $result += ['balanceDate' => $this->getBalanceDate()];
            $result += ['state' => $this->getInvoiceState()];
        } else {
            $result = ['created' => $this->getCreateDate()];
            if (!\is_null($this->getIdInvoices())) {
                $result += ['idInvoice' => $this->getIdInvoices()];
            }
            $result += ['priceDate' => $this->getPricingDate()];
            $result += ['address' => $this->getInvoiceAddress()];
            $result += ['balance' => $this->getInvoiceBalanceDelta()];
            $result += ['balanceDate' => $this->getBalanceDate()];
            $result += ['state' => $this->getInvoiceState()];
        }
        return  $result;
    }
  
    /// Check for balance changes and if all ok, return true
    // If some error was happend, return false
    public function actionCheckPayment()
    {
        $result = false;
        $vCurresntState = $this->getInvoiceState();
        if ($vCurresntState !== \BtcRelax\Model\Invoice::STATE_CANCELED) {
            $vOldBallance = $this->getInvoiceBalance();
            $vInvoiceAddress = $this->getInvoiceAddress();
            $vPaymentProvider = $this->getPaymentProvider();
            $vPaymentProvider->setInvoiceAddress($vInvoiceAddress);
            $vNewBalance = $vPaymentProvider->getBallanceByWallet();
            if (\is_numeric($vNewBalance)) {
                $this->setInvoiceBalance($vNewBalance);
                $result = $this;
            }
        }
        return $result;
    }
}
