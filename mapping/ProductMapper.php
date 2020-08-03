<?php namespace BtcRelax\Mapping;

use BtcRelax\Model\Product;

/**
 * Description of ProductMapper
 *
 * @author god
 */
final class ProductMapper
{
    //put your code here
    
    public static function map(Product $product, array $properties)
    {
        if (array_key_exists('CreateDate', $properties)) {
            $createdOn = self::createDateTime($properties['CreateDate']);
            if ($createdOn) {
                $product->setCreateDate($createdOn);
            }
        }
                
                
        if (array_key_exists('idProduct', $properties)) {
            $product->setProductId($properties['idProduct']);
        }
        if (array_key_exists('ProductTitle', $properties)) {
            $product->setProductName($properties['ProductTitle']);
        }
        if (array_key_exists('InfoURL', $properties)) {
            $product->setDescriptionUrl($properties['InfoURL']);
        }
    }
            
    private static function createDateTime($input)
    {
        return \DateTime::createFromFormat('Y-n-j H:i:s', $input);
    }
}
