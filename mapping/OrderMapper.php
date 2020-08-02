<?php
  namespace BtcRelax\Mapping;

  use \DateTime;
  use \BtcRelax\Model\Order;

  final class OrderMapper {
	private function __construct() {
	}

        public static function map(\BtcRelax\Model\Order $order, array $properties) {
		if (array_key_exists('CreateDate', $properties)) {
			$createdOn = self::createDateTime($properties['CreateDate']);
			if ($createdOn) { $order->setCreateDate($createdOn);  }    
		}
                if (array_key_exists('EndDate', $properties)) {
                    $finishDate = self::createDateTime($properties['EndDate']);    
                    if ($finishDate) { $order->setEndDate($finishDate); }
                }
                if (array_key_exists('idOrder', $properties)) {
                    $order->setIdOrder($properties['idOrder']);                 
                }
                if (array_key_exists('OrderState', $properties)) {
                    $order->setState($properties['OrderState']);
                }
                if (array_key_exists('DeliveryMethod', $properties)) {
                    $order->setDeliveryMethod($properties['DeliveryMethod']);
                }
                if (array_key_exists('OrderHash', $properties)) {
                    $order->setOrderHash($properties['OrderHash']);
                }
                if (array_key_exists('idCreator', $properties)) {
                    $idCreator = $properties['idCreator'];
                    $order->setCreator($idCreator);
                }
    }
        
        private static function createDateTime($input) {
		return DateTime::createFromFormat('Y-n-j H:i:s', $input);
        }
  }

