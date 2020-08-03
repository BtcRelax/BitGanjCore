<?php
namespace BtcRelax\Mapping;

use \DateTime;
use \BtcRelax\Log;

class IdentifierMapper
{
    private function __construct()
    {
    }

    public static function map(\BtcRelax\Model\Identicator $ident, array $properties)
    {
        if (array_key_exists('CreateDate', $properties)) {
            $createdOn = self::createDateTime($properties['CreateDate']);
            if ($createdOn) {
                $ident->setCreateDate($createdOn);
            }
        }
        if (array_key_exists('EndDate', $properties)) {
            $EndDate = self::createDateTime($properties['EndDate']);
            if ($EndDate) {
                $ident->setEndDate($EndDate);
            }
        }
        if (array_key_exists('idIdentity', $properties)) {
            $ident->setIdIdentity($properties['idIdentity']);
        }
        if (array_key_exists('IdentityKey', $properties)) {
            $ident->setIdentityKey($properties['IdentityKey']);
        }
        if (array_key_exists('idCustomer', $properties)) {
            $ident->setIdCustomer($properties['idCustomer']);
        }
        if (array_key_exists('Description', $properties)) {
            $ident->setDescription($properties['Description']);
        }
    }

    private static function createDateTime($input)
    {
        return \DateTime::createFromFormat('Y-n-j H:i:s', $input);
    }
}
