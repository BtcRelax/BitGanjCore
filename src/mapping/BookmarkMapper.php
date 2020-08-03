<?php
namespace BtcRelax\Mapping;

use \DateTime;
use BtcRelax\Log;

final class BookmarkMapper
{
    private function __construct()
    {
    }
    public static function map(\BtcRelax\Model\Bookmark $bookmark, array $properties)
    {
        if (array_key_exists('idBookmark', $properties)) {
            $bookmark->setIdBookmark($properties['idBookmark']);
        }
        if (array_key_exists('CreateDate', $properties)) {
            $createdOn = self::createDateTime($properties['CreateDate']);
            if ($createdOn) {
                $bookmark->setCreateDate($createdOn);
            }
        }
        if (array_key_exists('idOrder', $properties)) {
            $bookmark->setIdOrder($properties['idOrder']);
        }
        if (array_key_exists('Quantity', $properties)) {
            $bookmark->setQuantity($properties['Quantity']);
        }
        if (array_key_exists('EndDate', $properties)) {
            $Ended = self::createDateTime($properties['EndDate']);
            if ($Ended) {
                $bookmark->setEndDate($Ended);
            }
        }
        if (array_key_exists('Latitude', $properties)) {
            $bookmark->setLatitude($properties['Latitude']);
        }
        if (array_key_exists('Longitude', $properties)) {
            $bookmark->setLongitude($properties['Longitude']);
        }
        if (array_key_exists('Link', $properties)) {
            $bookmark->setLink($properties['Link']);
        }
        if (array_key_exists('Description', $properties)) {
            $bookmark->setDescription($properties['Description']);
        }
        if (array_key_exists('RegionTitle', $properties)) {
            $bookmark->setRegionTitle($properties['RegionTitle']);
        }
        if (array_key_exists('CustomPrice', $properties)) {
            $bookmark->setCustomPrice($properties['CustomPrice']);
        }
        if (array_key_exists('PriceCurrency', $properties)) {
            $bookmark->setPriceCurrency($properties['PriceCurrency']);
        }
        if (array_key_exists('AdvertiseTitle', $properties)) {
            $bookmark->setAdvertiseTitle($properties['AdvertiseTitle']);
        }
        if (array_key_exists('UnlockDate', $properties)) {
            $Unlocked = self::createDateTime($properties['UnlockDate']);
            if ($Unlocked) {
                $bookmark->setUnlockDate($Unlocked);
            }
        }
        if (array_key_exists('State', $properties)) {
            $bookmark->setState($properties['State']);
        }
        if (\array_key_exists('IdDroper', $properties)) {
            $bookmark->setIdDroper($properties['IdDroper']);
        }
        if (array_key_exists('TargetAddress', $properties)) {
            $bookmark->setTargetAddress($properties['TargetAddress']);
        }
        if (array_key_exists('BookmarkHash', $properties)) {
            $bookmark->setBookmarkHash($properties['BookmarkHash']);
        }
    }

    private static function createDateTime($input)
    {
        return DateTime::createFromFormat('Y-n-j H:i:s', $input);
    }
}
