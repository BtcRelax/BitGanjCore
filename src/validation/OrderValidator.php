<?php
namespace BtcRelax\Validation;

use \BtcRelax\Exception\NotFoundException;
use \BtcRelax\Model\Order;

final class OrderValidator
{
    private function __construct()
    {
    }

    public static function validate(Order $order)
    {
        $errors = [];
        if (!empty($order->getLastError())) {
            $errors[] = new \BtcRelax\Validation\ValidationError('Ошибка регистрации заказа!', $order->getLastError());
        }
        return $errors;
    }



    public static function validateStatus($status)
    {
        if (!self::isValidStatus($status)) {
            throw new NotFoundException('Unknown status: ' . $status);
        }
    }
    private static function isValidStatus($status)
    {
        return in_array($status, Order::allStatuses());
    }
}
