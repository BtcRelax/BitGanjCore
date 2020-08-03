<?php

namespace BtcRelax\Dao;

use BtcRelax\Exception\NotFoundException;

final class InvoiceDao extends \BtcRelax\Dao\BaseDao
{

//            public function __construct($isAutocommit) {
//                parent::__construct(null, $isAutocommit);
//            }


    public function insert(\BtcRelax\Model\Invoice $newInvoice)
    {
        $sql = 'INSERT INTO `Invoices`
                (`idInvoices`,`Orders_idOrder`, `Currency`, `Price`, `PricingDate`, `InitialBallance`, `InvoiceAddress`,`InvoiceBalance`,`BalanceDate`,`CreateDate`,`InvoiceState`)
                VALUES (:idInvoices, :idOrder, :Currency, :Price, :PricingDate , :InitialBallance, :InvoiceAddress , :InvoiceBalance , :BalanceDate , :CreateDate, :InvoiceState )';
        return $this->execute($sql, $newInvoice);
    }

    public function save(\BtcRelax\Model\Invoice $pInvoice)
    {
        if ($pInvoice->getIdInvoices() === null) {
            return $this->insert($pInvoice);
        }
        return $this->update($pInvoice);
    }

    public function update(\BtcRelax\Model\Invoice $pInvoice)
    {
        $sql = 'UPDATE `Invoices` SET
                    `InvoiceBalance` = :InvoiceBalance ,
                    `BalanceDate` = :BalanceDate,
                    `EndDate` = :EndDate,
                    `InvoiceState` = :InvoiceState
                     WHERE `idInvoices` = :idInvoices';
        return $this->execute($sql, $pInvoice);
    }

    /**
     * @return Todo
     * @throws Exception
     */
    public function execute($sql, \BtcRelax\Model\Invoice $vInvoice)
    {
        $statement = $this->getDb()->prepare($sql);
        $this->executeStatement($statement, $this->getParams($vInvoice));
        if (!$vInvoice->getIdInvoices()) {
            $vNewInvoiceId = $this->getDb()->lastInsertId();
            return $this->findById($vNewInvoiceId);
        }
        if (!$statement->rowCount()) {
            throw new NotFoundException('Invoice with ID "' . $vInvoice->getIdInvoices() . '" does not exist.');
        }
        return $vInvoice;
    }

    public function getParams(\BtcRelax\Model\Invoice $vInvoice)
    {
        $vEndDate = $vInvoice->getEndDate();
        $params = [
            ':idInvoices' => $vInvoice->getIdInvoices(),
            ':InvoiceBalance' => $vInvoice->getInvoiceBalance(),
            ':BalanceDate' => self::formatDateTime($vInvoice->getBalanceDate()),
            ':InvoiceState' => $vInvoice->getInvoiceState(),
        ];
        if (!empty($vEndDate)) {
            $params += [':EndDate' => self::formatDateTime($vEndDate)];
        } else {
            $params += [':EndDate' => null];
        }
        if (!$vInvoice->getIdInvoices()) {
            $params += [':idOrder' => $vInvoice->getIdOrder()];
            $params += [':Currency' => $vInvoice->getCurrency()];
            $params += [':Price' => $vInvoice->getPrice()];
            $params += [':PricingDate' => self::formatDateTime($vInvoice->getPricingDate())];
            $params += [':InitialBallance' => $vInvoice->getInitialBallance()];
            $params += [':InvoiceAddress' => $vInvoice->getInvoiceAddress()];
            $params += [':CreateDate' => self::formatDateTime($vInvoice->getCreateDate())];
            unset($params[':EndDate']);
        }
        return $params;
    }

    public function fullInvoices(\BtcRelax\Dao\InvoiceSearchCriteria $vSearch)
    {
        $result = [];
        $filter = '';
        $sql = "SELECT `Registered`, `idInvoices`, `IdOrder`, `ClientId`, `IdPoint`, `SallerId`, `InvoiceState`, "
                    . "`InvoiceEndDate`, `PointState`, `PointEndDate`, `OrderState`, `OrderEndDate`, `Currency`, `Price`,"
                    . "`PricingDate`,  `InitialBallance`, `InvoiceAddress`, `InvoiceBalance`, `BalanceDate` FROM `vwInvoices` ";
        if ($vSearch !== null) {
            if (!is_null($vSearch->getIdInvoice())) {
                $filter = $this->addToFilter($filter, sprintf('idInvoices = \'%s\'', $vSearch->getIdInvoice()));
            }
            if (!is_null($vSearch->getOrderId())) {
                $filter = $this->addToFilter($filter, sprintf('IdOrder = \'%s\'', $vSearch->getOrderId()));
            }
            if (!is_null($vSearch->getClientId())) {
                $filter = $this->addToFilter($filter, sprintf('ClientId = \'%s\'', $vSearch->getClientId()));
            }
        }
        $sql .= $filter ;
        $cnt = 0;
        foreach ($this->query($sql) as $row) {
            $vInvoice = [ $cnt => ["Registered"=> $row['Registered'], "idInvoices" => (int)$row['idInvoices'] , "IdOrder" => (int)$row['IdOrder'] ,
                      "ClientId" => $row['ClientId'] , "IdPoint" => (int)$row['IdPoint'] , "SallerId" => $row['SallerId'] , "InvoiceState" => $row['InvoiceState'] ,
                        "InvoiceEndDate" => $row['InvoiceEndDate'] ,"PointState" => $row['PointState'] ,"PointEndDate" => $row['PointEndDate'] ,
                        "OrderState" => $row['OrderState'] ,"OrderEndDate" => $row['OrderEndDate'] , "Currency" => $row['Currency'],
                        "Price" => $row['Price'], "PricingDate" => $row['PricingDate'],  "InitialBallance" => $row['InitialBallance'] ,
                        "InvoiceAddress" => $row['InvoiceAddress'], "InvoiceBalance" => $row['InvoiceBalance'] ,
                        "BalanceDate" => $row['BalanceDate'], ] ] ;
            $cnt = $cnt +1;
            $result += $vInvoice;
        }
        return $result;
    }
                
    public function getInvoices(\BtcRelax\Dao\InvoiceSearchCriteria $vSearch)
    {
        $result = [];
        $filter = '';
        $sql = "SELECT `vwInvoices`.`Registered`, `vwInvoices`.`idInvoices`, `vwInvoices`.`InvoiceState`,
    `vwInvoices`.`IdOrder`, `vwInvoices`.`OrderState`, `vwInvoices`.`ClientId`, `vwInvoices`.`IdPoint`, `vwInvoices`.`PointState`,
    `vwInvoices`.`SallerId`,  `vwInvoices`.`InvoiceEndDate`, `vwInvoices`.`Currency`, `vwInvoices`.`Price`,
    `vwInvoices`.`PricingDate`,  `vwInvoices`.`InitialBallance`, `vwInvoices`.`InvoiceAddress`, `vwInvoices`.`InvoiceBalance`,
    `vwInvoices`.`BalanceDate` FROM `vwInvoices` ";
        if ($vSearch !== null) {
            if (!is_null($vSearch->getIdInvoice())) {
                $filter = $this->addToFilter($filter, sprintf('`idInvoices` = \'%s\'', $vSearch->getIdInvoice()));
            }
            if (!is_null($vSearch->getOrderId())) {
                $filter = $this->addToFilter($filter, sprintf('`IdOrder` = \'%s\'', $vSearch->getOrderId()));
            }
            if (!is_null($vSearch->getClientId())) {
                $filter = $this->addToFilter($filter, sprintf('`ClientId` = \'%s\'', $vSearch->getClientId()));
            }
            if (!is_null($vSearch->getSallerId())) {
                $filter = $this->addToFilter($filter, sprintf('`SallerId` = \'%s\'', $vSearch->getSallerId()));
            }
        }
        $sql .= $filter;
        \BtcRelax\Log::general(\sprintf('Executing query : %s', $sql), \BtcRelax\Log::INFO);
                
        foreach ($this->query($sql) as $row) {
            array_push($result, ["id" => (int) $row['idInvoices'], "data" => [$row['Registered'], (int) $row['idInvoices'], $row['InvoiceState'], (int) $row['IdOrder'], $row['OrderState'] ,
                (int) $row['IdPoint'], $row['PointState'], $row['ClientId'], $row['SallerId'],
                    $row['Price'], $row['Currency'], $row['InvoiceAddress'], $row['InvoiceBalance'] ]]);
        }
        return $result;
    }

    public function find(\BtcRelax\Dao\InvoiceSearchCriteria $search = null)
    {
        $result = [];
        $cnt = 0;
        foreach ($this->query($this->getFindSql($search)) as $row) {
            $invoice = new \BtcRelax\Model\Invoice();
            \BtcRelax\Mapping\InvoiceMapper::map($invoice, $row);
            $cnt = $cnt + 1;
            $result[$cnt] = $invoice;
        }
        return $result;
    }

    private function getFindSql(\BtcRelax\Dao\InvoiceSearchCriteria $search = null)
    {
        $sql = "SELECT `idInvoices`,`Orders_idOrder`, `Currency`, `Price`, `PricingDate`, `InitialBallance`, `InvoiceAddress`, `InvoiceBalance`,"
                . "`BalanceDate`, `CreateDate`, `EndDate`, `InvoiceState` FROM `Invoices` ";
        $orderBy = 'CreateDate';
        $filter = '';
        if ($search !== null) {
            if ($search->getIsActive()) {
                $filter = $this->addToFilter($filter, ' NOW() BETWEEN CreateDate AND  COALESCE(EndDate, NOW())');
            }
            if ($search->getStatus() !== null) {
                //$sql .= 'AND State = ' . $this->getDb()->quote($search->getStatus());
                switch ($search->getStatus()) {
                    case \BtcRelax\Model\Invoice::STATE_WAIT:
                        $where = 'WaitForPay';
                        break;
                    case \BtcRelax\Model\Invoice::STATE_CANCELED:
                        $where = 'Canceled';
                        break;
                    case \BtcRelax\Model\Invoice::STATE_PAYED:
                        $where = 'Payed';
                        break;
                    case \BtcRelax\Model\Invoice::STATE_OVER:
                        $where = 'OverPay';
                        break;
                    default:
                        throw new NotFoundException('No order for status: ' . $search->getStatus());
                }
                $filter = $this->addToFilter($filter, sprintf('InvoiceState = \'%s\'', $where));
            }
            if ($search->getOrderId() !== null) {
                $filter = $this->addToFilter($filter, sprintf('Orders_idOrder = \'%s\'', $search->getOrderId()));
            }
            if ($search->getIdInvoice() !== null) {
                $filter = $this->addToFilter($filter, sprintf('idInvoices = \'%s\'', $search->getIdInvoice()));
            }
        }
        $sql .= sprintf('%s ORDER BY %s', $filter, $orderBy);
        $msg = sprintf('Final query generated by InvoiceDao is: %s', $sql);
        \BtcRelax\Log::general($msg, \BtcRelax\Log::INFO);
        return $sql;
    }

    public function findById($id)
    {
        $result = false;
        $row = $this::query(\sprintf("SELECT `idInvoices`,`Orders_idOrder`, `Currency`, `Price`, `PricingDate`, `InitialBallance`, `InvoiceAddress`, `InvoiceBalance`,"
                                . "`BalanceDate`, `CreateDate`, `EndDate`, `InvoiceState` FROM `Invoices`  WHERE idInvoices = '%s' LIMIT 1 ", $id))->fetch();
        if ($row) {
            $invoice = new \BtcRelax\Model\Invoice();
            \BtcRelax\Mapping\InvoiceMapper::map($invoice, $row);
            $result = $invoice;
        }
        return $result;
    }
}
