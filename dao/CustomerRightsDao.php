<?php
	namespace BtcRelax\Dao;

        final class CustomerRightsDao extends BaseDao
	{
	
        
        
	public function findById($pIdCustomer)
	{
            $result = [];
            $cnt = 0;
            $vQuery = sprintf("SELECT idCustomer, RightCode, RightDescription  FROM vwCustomerRights WHERE idCustomer = '%s' ", $pIdCustomer);
            foreach ($this->query($vQuery) as $row) {
				$customerRight = new \BtcRelax\Model\CustomerRight();
				\BtcRelax\Mapping\CustomerRightMapper::map($customerRight, $row);
				$cnt = $cnt + 1;  
				$result[$cnt] = $customerRight;
            }
            return $result;           
	}
	                
}
        
        
        
        

