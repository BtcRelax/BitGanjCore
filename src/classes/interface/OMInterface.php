<?php
namespace BtcRelax;

interface IOM
{
    public function createNewOrder():\BtcRelax\Model\Order ;

    public function getOrderById($orderId);
    
    public function getOrdersByUser(\BtcRelax\Model\User $pUser, $onlyActive = true);

    public function tryConfirmOrder();
    
    //public function setPointCatched(\BtcRelax\Model\Order $order, $bookmarkId );
}
