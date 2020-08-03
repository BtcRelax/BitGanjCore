<?php
  namespace BtcRelax\Mapping;

    final class CustomerRightMapper
    {
        private function __construct()
        {
        }


        public static function map(\BtcRelax\Model\CustomerRight $custRight, array $properties)
        {
            if (array_key_exists('idCustomer', $properties)) {
                $custRight->setIdCustomer($properties['idCustomer']);
            }
                
            if (array_key_exists('RightCode', $properties)) {
                $custRight->setRightCode($properties['RightCode']);
            }
                
            if (array_key_exists('RightDescription', $properties)) {
                $custRight->setRightDescription($properties['RightDescription']);
            }
        }
    }
