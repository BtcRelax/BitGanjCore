<?php
    namespace BtcRelax\Dao;
	
    use \PDO;
    use BtcRelax\Dao\OrderSearchCriteria;
    use BtcRelax\Exception\NotFoundException;    	
    use BtcRelax\Model\Order;

final class OrderDao extends \BtcRelax\Dao\BaseDao
	{        
            public function insert(\BtcRelax\Model\Order $newOrder)
            {
                $sql = 'INSERT INTO `Orders` (`idOrder`,`CreateDate`,`OrderState`,`idCreator`,`DeliveryMethod`, `EndDate`) 
                    VALUES (:idOrder,:CreateDate,:OrderState,:idCreator,:DeliveryMethod, :EndDate)';
                return $this->execute($sql, $newOrder);
            }
    
            public function save(\BtcRelax\Model\Order $pOrder) {
                if ($pOrder->getIdOrder() === null) {
                        return $this->insert($pOrder);
                    }
                    return $this->update($pOrder);
                }
            
                    
            public function update(\BtcRelax\Model\Order $pOrder) {
                $sql = 'UPDATE `Orders` SET
                    `EndDate` = :EndDate,
                    `OrderState` = :OrderState
                    WHERE `idOrder` = :idOrder';
                return $this->execute($sql, $pOrder);
            }

            public function execute($sql, \BtcRelax\Model\Order $vOrder) {
                $statement = $this->getDb()->prepare($sql);
                $this->executeStatement($statement, $this->getParams($vOrder));
                if (!$vOrder->getIdOrder()) {
                    $newOrderId = $this->getDb()->lastInsertId();
                    return $this->findById($newOrderId);
                }
//                if (!$statement->rowCount()) {
//                    throw new NotFoundException('Order with ID "' . $vOrder->getIdOrder() . '" does not exist.');
//                }
                return $vOrder;
            }

            public function getParams(\BtcRelax\Model\Order $vOrder) {
                $vState = $vOrder->getState();
                if ( $vState === \BtcRelax\Model\Order::STATUS_PREPARING)
                {
                    $vState = \BtcRelax\Model\Order::STATUS_CONFIRMED;
                }
                $params = [
                    ':idOrder' => $vOrder->getIdOrder(),
                    ':OrderState' => $vState,
                ];
                if (!empty($vOrder->getEndDate()))
                { $params += [':EndDate' => self::formatDateTime($vOrder->getEndDate())];}
                    else { $params += [':EndDate' => null]; }
                if (!$vOrder->getIdOrder()) {
                    $params += [':CreateDate' => self::formatDateTime($vOrder->getCreateDate())];
                    $params += [':idCreator' => $vOrder->getCreator()];
                    $params += [':DeliveryMethod' => $vOrder->getDeliveryMethod()];
                }
                return $params;
            }

            public function cancelOrder(int $pIdOrder)   {
                $result = false;
                try {
                    $db = $this->getDb(); 		 
                    // execute the stored procedure
                    $callQuery = "CALL `CancelOrder`(:pOrderId,@pResult)";
                    $call = $db->prepare($callQuery);
                    $call->bindParam(':pOrderId',$pIdOrder,PDO::PARAM_INT);
                    $call->execute();
                    // execute the second query to get values from OUT parameter
                    $select = $db->query("SELECT  @pResult");
                    $selResult = $select->fetch(PDO::FETCH_ASSOC);
                    if ($selResult)
                        {
                            $pResultId    =  intval($selResult['@pResult']);
                            $msg = sprintf('CancelOrder procedure called, with result: %s',$pResultId);
                            \BtcRelax\Log::general($msg, \BtcRelax\Log::DEBUG);   
                            if ($pResultId === 1)
                            {
                                $result = true;
                            }
                        }
                    } catch (PDOException $pe) {
                        Log::general($pe->getMessage(), Log::WARN ); 
                }
                return $result;
              }

//            public function updateHotBalance(Order $updOrder)  {
//               $result = false;
//               try {
//                    $db = $this->getDb();
//                    $orderId = $updOrder->getIdOrder();
//                    $balance = $updOrder->getInvoiceBalance();
//                    $callQuery = 'select `UpdateHotBalance`(:pOrderId, :pBalance)';
//                    $call = $db->prepare($callQuery);
//                    $call->bindParam(':pOrderId',$orderId,PDO::PARAM_INT);
//                    $call->bindParam(':pBalance',$balance,PDO::PARAM_STR);
//                    $call->execute();
//                    $selResult = $call->fetch();
//                    if ($selResult)
//                    {
//                        $pResultId    = $selResult[0];
//                        if ($pResultId == 1)
//                        {
//                            $result = $this->findById($updOrder->getIdOrder());
//                        }
//                    }
//               }
//               catch (PDOException $pe) {
//                        Log::general($pe->getMessage(), Log::WARN ); 
//                        $updOrder->setLastError($pe->getMessage());
//                        $result = $updOrder;
//                }
//                return $result;
//            }
                    
            public function find(OrderSearchCriteria $search = null) {
			$result = []; $cnt = 0;
			foreach ($this->query($this->getFindSql($search)) as $row) {
				$order = new \BtcRelax\Model\Order();
				\BtcRelax\Mapping\OrderMapper::map($order, $row);
				$cnt = $cnt + 1;  
				$result[$cnt] = $order;
			}
                        \BtcRelax\Log::general(\sprintf('Founded %s rows', count($result)), \BtcRelax\Log::DEBUG);
			return $result;
		}
		
            private function getFindSql(OrderSearchCriteria $search = null) {		
				$sql = 'SELECT idOrder, CreateDate, EndDate, OrderState, idCreator, DeliveryMethod FROM `Orders` '; 
                                $orderBy = 'CreateDate';
                                        $filter = '';
                                        if ($search !== null) {
                                                if ($search->getIsActive())
                                                {
                                                    $filter = $this->addToFilter($filter, ' NOW() BETWEEN CreateDate AND  COALESCE(EndDate, NOW())');
                                                }
                                                if ($search->getStatus() !== null) {
							//$sql .= 'AND State = ' . $this->getDb()->quote($search->getStatus());
                                                        switch ($search->getStatus()) {
								case Order::STATUS_CREATED:
									$where = 'Created';
									break;
                                                                case Order::STATUS_CONFIRMED:
									$where = 'Confirmed';
									break;
                                                                case Order::STATUS_PAID:
									$where = 'Paid';
									break;    
                                                                case Order::STATUS_WAIT_FOR_PAY:
									$where = 'WaitForPayment';
									break;
								case Order::STATUS_CANCELED:
									$where = 'Canceled';
									break;
                                                                case Order::STATUS_FINISHED:
									$where = 'Finished';
									break;
								default:
									throw new NotFoundException('No order for status: ' . $search->getStatus());
							}
                                                        $filter = $this->addToFilter($filter, sprintf('OrderState = \'%s\'', $where ));
						}
                                                if ($search->getCustomerId() !== null)
                                                {
                                                   $filter = $this->addToFilter($filter, sprintf('idCreator = \'%s\'',$search->getCustomerId() ) ); 
                                                }
                                         }
                                        $sql .= sprintf('%s ORDER BY %s',$filter , $orderBy);
                                        $msg = sprintf('Final query generated by OrderDao is: %s',$sql );
                                        \BtcRelax\Log::general($msg, \BtcRelax\Log::DEBUG);
                            return $sql;
			}
		
            public function findById($id) {
		$result=false;
                $row = $this->query(sprintf("SELECT idOrder, CreateDate, EndDate, OrderState,  idCreator, DeliveryMethod  FROM `Orders` WHERE `idOrder` = '%s' LIMIT 1 ", $id))->fetch();
                if (!$row) { $result=null;}
                else { $order = new \BtcRelax\Model\Order();
                    \BtcRelax\Mapping\OrderMapper::map($order, $row);
                    $result = $order; }
                return $result;
            }
            
            public function getHashByOrderId($orderId) {
               $result = false;
               try {
                    $db = $this->getDb();
                    $callQuery = 'select `GetHashByOrderId`(:pOrderId)';
                    $call = $db->prepare($callQuery);
                    $call->bindParam(':pOrderId',$orderId,PDO::PARAM_INT);
                    $call->execute();
                    $selResult = $call->fetch();
                    if ($selResult)
                    {
                        $result    = $selResult[0];
                    }
               }
               catch (PDOException $pe) {
                   \BtcRelax\Log::general($pe->getMessage(), Log::ERROR ); 
                }
                return $result;
            }

            
            public function fullOrders(\BtcRelax\Dao\OrderSearchCriteria $vSearch )
            {
                $result = []; $filter = '';
                $sql = "SELECT `vwOrders`.`idOrder`, `vwOrders`.`Registered`, `vwOrders`.`EndDate`," .
    			"`vwOrders`.`OrderState`, `vwOrders`.`idCreator`, `vwOrders`.`DeliveryMethod`" .
			"FROM `vwOrders` ";
                if ($vSearch !== null) {
                    if (!is_null($vSearch->getOrderId())) 
                      { $filter = $this->addToFilter($filter, sprintf('idOrder = \'%s\'',$vSearch->getOrderId() ) ); }
                    $sql .= $filter ; $cnt = 0;
                }
                foreach ($this->query($sql) as $row) {
                        $vOrder = [ $cnt => ["Registered"=> $row['Registered'], "IdOrder" =>  \intval($row['idOrder']) , 
                        "ClientId" => $row['idCreator'] , "OrderState" => $row['OrderState'] ,"OrderEndDate" => $row['EndDate'] ]] ;
                    $cnt = $cnt +1;
                    $result += $vOrder;
                }
                return $result;
            }  
            
            public function getMaxOrderId() {
               $result = false;
               try {
                    $db = $this->getDb();
                    $callQuery = 'SELECT MAX(idOrder) FROM Orders';
                    $call = $db->prepare($callQuery);
                    $call->execute();
                    $selResult = $call->fetch();
                    if ($selResult)
                    {
                        $result    = $selResult[0];
                    }
               }
               catch (PDOException $pe) {
                   \BtcRelax\Log::general($pe->getMessage(), Log::ERROR ); 
                }
                return $result;                
            }
                    
} 

