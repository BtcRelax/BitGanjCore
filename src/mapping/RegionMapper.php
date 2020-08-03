<?php namespace BtcRelax\Mapping;

/**
 * Description of RegionMapper
 *
 * @author god
 */
final class RegionMapper
{
    //put your code here
    public static function map(\BtcRelax\Model\Region $region, array $properties)
    {
        if (array_key_exists('CreateDate', $properties)) {
            $createdOn = self::createDateTime($properties['CreateDate']);
            if ($createdOn) {
                $region->setCreateDate($createdOn);
            }
        }
        if (array_key_exists('idOrder', $properties)) {
            $region->setIdOrder($properties['idOrder']);
        }
        if (array_key_exists('OrderState', $properties)) {
            $region->setState($properties['OrderState']);
        }
        if (array_key_exists('DeliveryMethod', $properties)) {
            $region->setDeliveryMethod($properties['DeliveryMethod']);
        }
        if (array_key_exists('OrderHash', $properties)) {
            $region->setOrderHash($properties['OrderHash']);
        }
        if (array_key_exists('idCreator', $properties)) {
            $idCreator = $properties['idCreator'];
            $region->setCreator($idCreator);
        }
    }
}
