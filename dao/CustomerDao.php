<?php
namespace BtcRelax\Dao;

use PDO;
        
final class CustomerDao extends BaseDao
{
    public function getParams(\BtcRelax\Model\Customer $vCustomer) {
        $params = [':idCustomer' => $vCustomer->getIdCustomer()];
        if (empty($vCustomer->getCreateDate())) { $params += [':CreateDate' => self::formatDateTime(new \DateTime())];}
        //else { $params += [':CreateDate' => self::formatDateTime($vCustomer->getCreateDate())]; }
        $params += [':isBaned' => $vCustomer->getIsBaned() === true? 1 : 0 ];
        $params += [':Balance' => $vCustomer->getBalance()];
        $params += [':Preferences' => $vCustomer->getPreferences()];
        $params += [':ChangeDate' => self::formatDateTime(new \DateTime()) ];
        return $params;
    }
    
    public function execute($sql, \BtcRelax\Model\Customer $vCustomer) {
        $statement = $this->getDb()->prepare($sql);
        $vParams = $this->getParams($vCustomer);
        $this->executeStatement($statement, $vParams);
        if ($vCustomer->getCreateDate() === null) {
            return $this->findById($vCustomer->getIdCustomer());
        }
        if (!$statement->rowCount()) {
            throw new NotFoundException('Customer with ID "' . $vCustomer->getIdCustomer() . '" does not exist.');
        }
        return $vCustomer;
    }    
    
    public function insert(\BtcRelax\Model\Customer $newCustomer) {
        $sql = "INSERT INTO `Customers` (`idCustomer`,`CreateDate`,`isBaned`,`ChangeDate`, `Preferences`, `Balance` )" .
            "VALUES (:idCustomer, :CreateDate, :isBaned, :ChangeDate, :Preferences, :Balance)";
        return $this->execute($sql, $newCustomer);
    }

    public function update(\BtcRelax\Model\Customer $pCutomer) {
        $sql = "UPDATE `Customers` SET `isBaned` = :isBaned,
                `ChangeDate` = :ChangeDate,
                `Preferences` = :Preferences,
                `Balance` = :Balance
                WHERE `idCustomer` = :idCustomer";
        return $this->execute($sql, $pCutomer);
    }


    public function save(\BtcRelax\Model\Customer $pCustomer) {
        if ($pCustomer->getCreateDate() === null) {
            return $this->insert($pCustomer);
        }
        return $this->update($pCustomer);
    }
    
    public function addCustomersHierarhy($vParentId,$vChildId) {
        $sql = "INSERT INTO `CustomersHierarhy` (`CustomersParent`, `CustomersChild`)"
            . " VALUES (:CustomersParent, :CustomersChild)";
        $statement = $this->getDb()->prepare($sql);
        $vParams = [":CustomersParent" => $vParentId ];
        $vParams += [":CustomersChild" => $vChildId ];
        $this->executeStatement($statement, $vParams);
    }

    public function registerUserId($vIdType, $vIdNewKey, $vUserId) {
        $result = false;  
        try {
            $db = $this->getDb(); 		 
            $callQuery = "CALL CreateCustomerById(:pIdType, :pIdNewKey , :pUserId, @out_id);";
            $call = $db->prepare($callQuery);
            $call->bindParam(':pIdType', $vIdType, PDO::PARAM_STR);
            $call->bindParam(':pIdNewKey', $vIdNewKey, PDO::PARAM_STR);
            $call->bindParam(':pUserId',$vUserId, PDO::PARAM_STR);
            $call->execute();
            $select = $db->query("SELECT  @out_id");
            $result = $select->fetch(PDO::FETCH_ASSOC);
            if ($result) { $pResultId = $result['@out_id']; 
                if ($pResultId) { $result = true; } }					
        } catch (Exception $pe) {
                    \BtcRelax\Log::general($pe, LOG::WARN );
                    $result = false;	
        }
        return $result;
    }
            
    public function getUserByToken(\BtcRelax\Model\token $vToken) {
        $result = false;  
        try {
            $vCustId = $vToken->getIdCustomer();
            $resultObject = $this->findById($vCustId);
            if (FALSE !== $resultObject) {
                        $result = $resultObject;
            }} 
        catch ( Exception $pe) {
            \BtcRelax\Log::general($pe->getMessage(), LOG::WARN );
            $result = false;	
        }
        return $result;
    }
        
    public function findById($id) {
        $result = false;
        $row = parent::query(sprintf("SELECT idCustomer, CreateDate, isBaned, ChangeDate, Preferences, TotalOrdersCount, LostsCount, Balance, LastOrderDate  "
                    . "FROM vwCustomers WHERE idCustomer = '%s' LIMIT 1 ", $id))->fetch();
        if ($row) { $result = $row; }
        return $result;
    }

    public function GetPubKeyByCustomer($customerId) {
        $result = false;
        try {
            $db = $this->getDb();
            $callQuery = 'select `GetPubKeyByCustomer`(:pIdCustomer )';
            $call = $db->prepare($callQuery);
            $call->bindParam(':pIdCustomer',$customerId ,PDO::PARAM_STR);
            $call->execute();
            $selResult = $call->fetch(PDO::FETCH_NUM);
            if ($selResult) {
                $result = $selResult[0];
                \BtcRelax\Log::general(sprintf('PubKey:%s received for CustomerId:%s',$result,$customerId ), Log::INFO);
            }
        } catch (PDOException $pe) {
            \BtcRelax\Log::general($pe->getMessage(), \BtcRelax\Log::ERROR ); 
        }
        return $result;
    }

    public function GetInvoiceAddressCountByXPub($xPubKey) {
        $result = false;
        try {
            $db = $this->getDb();
            $callQuery = 'select `GetInvoiceAddressCountByXPub`(:pXPubKey )';
            $call = $db->prepare($callQuery);
            $call->bindParam(':pXPubKey',$xPubKey ,PDO::PARAM_STR);
            $call->execute();
            $selResult = $call->fetch(PDO::FETCH_NUM);
            if ($selResult) {
                        $invoicesCount = $selResult[0];
                        $result = $this->get_numeric($invoicesCount);
                        
            }
        } catch (PDOException $pe) {
            \BtcRelax\Log::general($pe->getMessage(), \BtcRelax\Log::ERROR ); 
        }
        return $result;           
    }
        
    public function AddInvoiceAddressToXPub($xPubKey,$invoiceAddres, $inBalance) {
        $result = false;
        try {
            $db = $this->getDb();
            $callQuery = 'select `AddInvoiceAddressToXPub`(:pXPubKey , :pInvoiceAddres, :pBalance)';
            $call = $db->prepare($callQuery);
            $call->bindParam(':pXPubKey',$xPubKey ,PDO::PARAM_STR);
            $call->bindParam(':pInvoiceAddres',$invoiceAddres ,PDO::PARAM_STR);
            $call->bindParam(':pBalance', $inBalance ,PDO::PARAM_STR);
            $call->execute();
            $selResult = $call->fetch(PDO::FETCH_NUM);
            if ($selResult) {
                $result    = $selResult[0];
            }
        } catch (PDOException $pe) {
            \BtcRelax\Log::general($pe->getMessage(), \BtcRelax\Log::ERROR ); 
        }
        return $result;           
    }
        
    public function fullCustomers (\BtcRelax\Dao\CustomerSearchCriteria $vSearch ) {
        $result = []; $filter = '';  $cnt = 0;
        $sql = "SELECT `idCustomer`,`Registered`,`Nick`,`isBaned`,`TotalOrdersCount`, `LostsCount`, `Balance`, `LastOrderDate` FROM `vwUserInfo` ";
        if ($vSearch !== null) {
            if (!is_null($vSearch->getCustomerId())) { $filter = $this->addToFilter($filter, sprintf('idCustomer = \'%s\'',$vSearch->getCustomerId() ) ); }
            $sql .= $filter ; }
        \BtcRelax\Log::general(\sprintf("Final query:%s",$sql), \BtcRelax\Log::INFO);
        foreach ($this->query($sql) as $row) {
            $vUser = [ $cnt => ["Registered"=> $row['Registered'], "idCustomer" => $row['idCustomer'] , "Nick" => $row['Nick'] , 
                "isBaned" => boolval($row['isBaned']) , "TotalOrdersCount" => $row['TotalOrdersCount'],  "LostsCount" => $row['LostsCount'],
                "Balance" => $row['Balance'] , "LastOrderDate" => $row['LastOrderDate'] ]] ;
            $cnt = $cnt +1;
            $result += $vUser;
        }
        return $result;
    }
        
}
        
        
        
        

