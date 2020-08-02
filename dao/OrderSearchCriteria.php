<?php

namespace BtcRelax\Dao;

final class OrderSearchCriteria {

    private $status = null;
    private $customerId = null;
    private $isActive = true;
    private $orderId = null;

    public function __construct($customerId = null, $isActive = true) {
        $this->customerId = $customerId;
        $this->isActive = $isActive;
    }

    public function getOrderId() {
        return $this->orderId;
    }

    public function setOrderId($OrderId) {
        $this->orderId = $OrderId;
        return $this;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getIsActive() {
        return $this->isActive;
    }

    public function setIsActive($isActive) {
        $this->isActive = $isActive;
    }

    public function getCustomerId() {
        return $this->customerId;
    }

    public function setCustomerId($customerId) {
        $this->customerId = $customerId;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

} ?>
