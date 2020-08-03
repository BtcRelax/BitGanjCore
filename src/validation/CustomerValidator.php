<?php

namespace BtcRelax\Validation;

use BtcRelax\Exception\NotFoundException;

use \BtcRelax\Model\Customer;

final class CustomerValidator
{
    private function __construct()
    {
    }

    public static function validate(Customer $customer)
    {
        $errors = [];
        if (!$todo->getCreatedOn()) {
            $errors[] = new \Validation\ValidationError('createdOn', 'Empty or invalid Created On.');
        }
        if (!$todo->getLastModifiedOn()) {
            $errors[] = new \ValidationError('lastModifiedOn', 'Empty or invalid Last Modified On.');
        }
        if (!trim($todo->getTitle())) {
            $errors[] = new \TodoList\Validation\ValidationError('title', 'Title cannot be empty.');
        }
        if (!trim($todo->getStatus())) {
            $errors[] = new \TodoList\Validation\ValidationError('status', 'Status cannot be empty.');
        } elseif (!self::isValidStatus($todo->getStatus())) {
            $errors[] = new \TodoList\Validation\ValidationError('status', 'Invalid Status set.');
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
        return true;
    }
}
