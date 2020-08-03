<?php
  namespace BtcRelax\Mapping;

  use \DateTime;

final class InvoiceMapper
{
    private function __construct()
    {
    }

    public static function map(\BtcRelax\Model\Invoice $invoice, array $properties)
    {
        if (array_key_exists('CreateDate', $properties)) {
            $createdOn = self::createDateTime($properties['CreateDate']);
            if ($createdOn) {
                $invoice->setCreateDate($createdOn);
            }
        }
                
        if (array_key_exists('EndDate', $properties)) {
            $finishDate = self::createDateTime($properties['EndDate']);
            if ($finishDate) {
                $invoice->setEndDate($finishDate);
            }
        }

        if (array_key_exists('PricingDate', $properties)) {
            $pricingDate = self::createDateTime($properties['PricingDate']);
            if ($pricingDate) {
                $invoice->setPricingDate($pricingDate);
            }
        }
                
        if (array_key_exists('BalanceDate', $properties)) {
            $balanceDate = self::createDateTime($properties['BalanceDate']);
            if ($balanceDate) {
                $invoice->setBalanceDate($balanceDate);
            }
        }
                                
        if (array_key_exists('idInvoices', $properties)) {
            $invoice->setIdInvoices($properties['idInvoices']);
        }
                
        if (array_key_exists('Orders_idOrder', $properties)) {
            $invoice->setIdOrder($properties['Orders_idOrder']);
        }
                
        if (array_key_exists('Currency', $properties)) {
            $invoice->setCurrency($properties['Currency']);
        }
                
        if (array_key_exists('Price', $properties)) {
            $invoice->setPrice($properties['Price']);
        }
                
        if (array_key_exists('InitialBallance', $properties)) {
            $invoice->setInitialBallance($properties['InitialBallance']);
        }
    
        if (array_key_exists('InvoiceAddress', $properties)) {
            $invoice->setInvoiceAddress($properties['InvoiceAddress']);
        }
    
        if (array_key_exists('InvoiceBalance', $properties)) {
            $invoice->setInvoiceBalance($properties['InvoiceBalance']);
        }
    
        if (array_key_exists('InvoiceState', $properties)) {
            $invoice->setInvoiceState($properties['InvoiceState']);
        }
    }
        
    private static function createDateTime($input)
    {
        return DateTime::createFromFormat('Y-n-j H:i:s', $input);
    }
}
