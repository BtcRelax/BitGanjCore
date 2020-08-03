<?php
namespace BtcRelax\Dao;

final class InvoiceSearchCriteria
{
    /**
     * @var array
     */
    private $IdInvoice = null;
    private $status = null;
    private $orderId = null;
    private $isActive = true;
    private $ClientId = null;
    private $SallerId = null;
        
    public function __construct(array $params = null)
    {
        if ($params !== null) {
            $this->parseParams($params);
        }
    }

    public function getClientId()
    {
        return $this->ClientId;
    }

    public function setClientId($ClientId)
    {
        $this->ClientId = $ClientId;
        return $this;
    }

    public function getSallerId()
    {
        return $this->SallerId;
    }

    public function setSallerId($SallerId)
    {
        $this->SallerId = $SallerId;
    }

                
    public function getIdInvoice()
    {
        return $this->IdInvoice;
    }

    public function setIdInvoice($IdInvoice)
    {
        $this->IdInvoice = $IdInvoice;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    private function parseParams(array $params)
    {
        foreach ($params as $key => $value) {
            if ($key == 'idInvoice') {
                $this->setIdInvoice($value);
            }
            if ($key == 'idOrder') {
                $this->setOrderId($value);
            }
            if ($key == 'isActive') {
                $this->setIsActive($value);
            }
            if ($key == 'ClientId') {
                $this->setClientId($value);
            }
            if ($key == 'SallerId') {
                $this->setSallerId($value);
            }
        }
        return $this;
    }
}
