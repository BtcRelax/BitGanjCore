<?php

namespace BtcRelax\Model;

use BtcRelax\Log;
use Exception;

/**
 * Description of paymentProvider
 *
 * @author Chronos
 */
abstract class PaymentProvider
{
    protected $_currencyCode; //Currency
    protected $_currencyTitle;
    protected $_logoUrl;
    public $_isInited = false;
    // Price, PricingDate - filled be RE
    //After finifsh: idInvoice,OrderCreatorId,idOrder
    protected $_sallerId;
    // After create new wallet will be filled
    protected $_initialBallance;
    protected $_invoiceAddress;
    // After get ballance will be  filled
    protected $_invoiceBalance;
    protected $_balanceDate;
    // After creating and saving invoice to DB
    protected $_createDate;
    private $Last_error = '';

    public function __construct()
    {
        $this->Last_error = "Продавец, не поддерживает прём оплаты в данной валюте!";
    }

    public function getInvoiceAddress()
    {
        return $this->_invoiceAddress;
    }

    public function setInvoiceAddress($invoiceAddress)
    {
        $this->_invoiceAddress = $invoiceAddress;
    }

    public function getInitialBallance()
    {
        return $this->_initialBallance;
    }

    public function setInitialBallance($initialBallance)
    {
        $this->_initialBallance = $initialBallance;
    }

    public function getInvoiceBalance()
    {
        return $this->_invoiceBalance;
    }

    public function getBalanceDate()
    {
        return $this->_balanceDate;
    }

    public function setInvoiceBalance($invoiceBalance)
    {
        $this->_invoiceBalance = $invoiceBalance;
    }

    public function setBalanceDate($balanceDate)
    {
        $this->_balanceDate = $balanceDate;
    }

    public function getLogoUrl()
    {
        return $this->_logoUrl;
    }

    public function getLastError()
    {
        return $this->Last_error;
    }

    protected function setLastError($Last_error = null)
    {
        $this->Last_error = $Last_error;
    }

    public function isInited()
    {
        return $this->_isInited;
    }

    public function getCurrencyCode()
    {
        return $this->_currencyCode;
    }

    public function setIsInited($pSaller, $isInited)
    {
        try {
            $this->_sallerId = $isInited === true ? $pSaller->getIdCustomer() : null;
            $this->_isInited = $isInited;
        } catch (Exception $exc) {
            Log::general($exc, Log::FATAL);
        }
    }

    public function getCurrencyTitle()
    {
        return $this->_currencyTitle;
    }

    public function setCurrencyCode($_currencyCode)
    {
        $this->_currencyCode = $_currencyCode;
    }

    public function setCurrencyTitle($_currencyTitle)
    {
        $this->_currencyTitle = $_currencyTitle;
    }

    public function setLogoUrl($_logoUrl)
    {
        $this->_logoUrl = $_logoUrl;
    }

    public function doInvoiceSavedEvent($pDB = null)
    {
        return true;
    }
      
    //
    abstract public function initBySallerProperties($pSaller);

    abstract public function getProviderCode();

    abstract public function formatPrice(float $pPrice): float;

    // Createing exclusive payment target
    // Result: false, true.
    // If true - than new wallet set in vars.
    // Else, last error will filled
    abstract public function createNewWallet(array $pAdditionalParams = null);

    // Checking payment target.
    // Result: false, true.
    // If true - than old balance and new ballance set to vars.
    // Else, fill last error
    abstract public function getBallanceByWallet();
}
