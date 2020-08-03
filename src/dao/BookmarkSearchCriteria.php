<?php namespace BtcRelax\Dao;

final class BookmarkSearchCriteria
{
    private $status = null;
    private $orderId= null;
    private $isActive = true;
    private $isFrontshop = false;
         
    public function __construct($orderId = null, $isActive = true)
    {
        $this->orderId = $orderId;
        $this->isActive = $isActive;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive($isActive):bool
    {
        $this->isActive = $isActive;
    }

    public function getIsFrontshop()
    {
        return $this->isFrontshop;
    }

    public function setIsFrontshop($isFrontshop)
    {
        $this->isFrontshop = $isFrontshop;
    }

    public function getStatus()
    {
        return $this->status;
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
        return $this;
    }
}
