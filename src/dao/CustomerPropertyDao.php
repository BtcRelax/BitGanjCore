<?php
    namespace BtcRelax\Dao;

        final class CustomerPropertyDao extends BaseDao
        {
            public function findById($pIdCustomer)
            {
                $result = [];
                $cnt = 0;
                $vQuery = sprintf("SELECT idCustomer, PropertyValue, "
                    . "PropertyTypeCode, PropertyTypeTitle FROM vwCustomerProperties WHERE idCustomer = '%s'", $pIdCustomer);
                foreach ($this->query($vQuery) as $row) {
                    $customerProperty = new \BtcRelax\Model\CustomerProperty();
                    \BtcRelax\Mapping\CustomerPropertyMapper::map($customerProperty, $row);
                    $cnt = $cnt + 1;
                    $result[$cnt] = $customerProperty;
                }
                return $result;
            }
        
            public function insert(\BtcRelax\Model\CustomerProperty $customerProperty)
            {
                $vCloseQuery = \sprintf(
                    "UPDATE CustomerProperty SET `EndDate` = NOW() WHERE `idCustomer` = '%s' AND `PropertyTypeCode` = '%s'AND `EndDate` = 0;",
                    $customerProperty->getIdCustomer(),
                    $customerProperty->getPropertyTypeCode()
                );
                $vQuery = \sprintf(
                    "%s INSERT INTO CustomerProperty ( `idCustomer`, `PropertyTypeCode`, `PropertyValue`) VALUES ('%s','%s','%s');",
                    $vCloseQuery,
                    $customerProperty->getIdCustomer(),
                    $customerProperty->getPropertyTypeCode(),
                    $customerProperty->getPropertyValue()
                );
                $this->query($vQuery);
            }
        }
