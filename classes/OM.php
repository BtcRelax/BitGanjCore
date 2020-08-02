<?php
namespace BtcRelax;

class OM implements IOM {

    private $pCore;
    private $pCurrentSession = null;
    private $pLastError = null;
    private $pCurrentOrder = null;

    private function getCurrentOrder() {
        return $this->pCurrentOrder === null ? false : $this->pCurrentOrder;
    }

    private function setCurrentOrder(\BtcRelax\Model\Order $pCurrentOrder = null) {
        if (!empty($pCurrentOrder))
        { $this->getCurrentSession()->setValue('CurrentOrder', $pCurrentOrder);
        } else { $this->getCurrentSession()->clearValue('CurrentOrder'); }
        $this->pCurrentOrder = $pCurrentOrder;
    }

    public function __construct() {
        global $core;
        $this->pCore = $core;
    }

    private function getCurrentSession(): \BtcRelax\SecureSession {
        if (is_null($this->pCurrentSession)) {
            $this->pCurrentSession = $this->pCore->getCurrentSession();
        }
        return $this->pCurrentSession;
    }

    public function getLastError() {
        return $this->pLastError;
    }

    public function setLastError($_lastError = null) {
        if ($_lastError !== null && !empty($_lastError)) {
            \BtcRelax\Log::general(\sprintf('OM was set last error, to:%s', $_lastError), \BtcRelax\Log::WARN);
        }
        $this->_lastError = $_lastError;
    }

 

    // Get opened order if his exists.
    // Check in DB for active order, if not exists 
    // check session, and fill.
    public function getActualOrder() {
        $result = $this->getCurrentOrder();
        if (\FALSE === $result) {
            $vOrder = $this->getOrdersByUser();
            if (\FALSE !== $vOrder) {                
                $this->setCurrentOrder($vOrder);
            }
        }
        $vSession = $this->getCurrentSession();
        return $vSession->getValue('CurrentOrder') ?? $result;
    }


    /// Create new order  for current user, and return it
    public function createNewOrder(): \BtcRelax\Model\Order {
        $vAM = \BtcRelax\Core::createAM();
        $pUser = $vAM->getUser();
        $newOrder = new \BtcRelax\Model\Order();
        $newOrder->createNew($pUser);
        $this->setCurrentOrder($newOrder);
        return $newOrder;
    }

    /// Get payment providers from session
    public function actionGetCurrentPaymentProviders() {
        $vSession = $this->getCurrentSession();
        $pRes = $vSession->getValue('CurrentPaymentProviders');
        return $pRes;
    }

    /// 
    public function checkPaymentByOrderId(int $pOrderId) {
        $result = false;
        $pOrder = $this->getOrderById($pOrderId);
        $vOrder = \BtcRelax\OM::initOrder($pOrder);
        $vInvoices = $vOrder->getInvoices();
        if ($vInvoices > 0) {
            foreach ($vInvoices as $vInvoice) {
                \BtcRelax\Log::general(\sprintf("Invoice id:%s will check ballance.", $vInvoice->getIdInvoices() ), \BtcRelax\Log::INFO);
                $checkResult = $vInvoice->actionCheckPayment();
                if (FALSE !== $checkResult) {
                    $vInvoiceDao = new \BtcRelax\Dao\InvoiceDao(null, false);
                    $vDB = $vInvoiceDao->getDb();
                    $vDB->beginTransaction();
                    $vInvoiceDao->save($checkResult);
                    $vOrderStateBefore = $vOrder->getState();
                    if (($checkResult->getInvoiceState() === \BtcRelax\Model\Invoice::STATE_PAYED) ||
                            ($checkResult->getInvoiceState() === \BtcRelax\Model\Invoice::STATE_OVER)) {
                        $vOrder->setState(\BtcRelax\Model\Order::STATUS_PAID);
                    } else {
                        $vOrder->setState(\BtcRelax\Model\Order::STATUS_WAIT_FOR_PAY);
                    }
                    if ($vOrderStateBefore !== $vOrder->getState()) {
                        $orderDao = new \BtcRelax\Dao\OrderDao($vDB, false);
                        $orderDao->save($vOrder);
                    }
                    $result = $vDB->commit();
                    $this->setLastError();
                } else {
                    $this->setLastError("Cannot update ballance");
                }
            }
        } else { 
            $this->setLastError(\sprintf("Invoices not found in order id:%s",$pOrderId ));
        }
        return $result;
    }

    /// Check order for payment 
    public function checkOrderPayment() {
        $vOrder = $this->getActualOrder();
        $result = is_numeric($vOrder->getIdOrder());
        if ($result) {
				$result = $this->checkPaymentByOrderId($vOrder->getIdOrder());
        };
        return $result;
    }

    /// Cancel order if possible, in another case return false
    public function changeOrderState(\BtcRelax\Model\Order $pOrder = null, \BtcRelax\Model\User $pUser = null, $vNewState ) {
        $vAM = \BtcRelax\Core::createAM();
        if (empty($pOrder)) {$pOrder = $this->getActualOrder();}
        if (empty($pUser)) { $pUser = $vAM->getUser(); }
        if ($pOrder instanceof \BtcRelax\Model\Order) {   
            $vState = $pOrder->getState();
            \BtcRelax\Log::general(\sprintf("Trying to cancel order from state:%s",$vState), \BtcRelax\Log::DEBUG);           
            switch ($vState) {
                case \BtcRelax\Model\Order::STATUS_CREATED:
                    $this->setCurrentOrder();
                case \BtcRelax\Model\Order::STATUS_CANCELED:
                    $result = true;
                    $this->setLastError();
                    break;
                case \BtcRelax\Model\Order::STATUS_FINISHED:
                case \BtcRelax\Model\Order::STATUS_PAID:                    
                    $this->setLastError("Order cannot be canceled from that state");
                    break;
                default:
                    if ($vAM->isUserHasRight('CANCEL_ORDER',$pUser)) {
                        $vOrderId = $pOrder->getIdOrder();
                        $vOrderDao = new \BtcRelax\Dao\OrderDao();
                        $result = $vOrderDao->cancelOrder($vOrderId);
                        if ($result === true) { $this->setCurrentOrder(); $this->setLastError(); }
                        else { $this->setLastError("Error when cancel procedure called"); }
                    } else { $this->setLastError("You have not rights to cancel order!"); }
                    break;
                }
        }  else { $this->setLastError("Order not found");}
        return $result;
    }

    
    /// Cancel order if possible, in another case return false
    public function cancelOrder() {
        $vOrder = $this->getActualOrder();
        $result = !is_numeric($vOrder->getIdOrder());
        if (!$result) {
            if ($vOrder->getState() === \BtcRelax\Model\Order::STATUS_FINISHED)
            { $result = true; } 
            else {
                $vAM = \BtcRelax\Core::createAM();
                if ($vAM->isUserHasRight('CANCEL_ORDER')) {
                  $vCurrentId = $vOrder->getIdOrder();
                  $vOrderDao = new \BtcRelax\Dao\OrderDao();
                  $result   = $vOrderDao->cancelOrder($vCurrentId);
                  if ($result) {                  
                    $vOrderUpdated = $this->getOrderById($vCurrentId);
                    $this->setCurrentOrder($vOrderUpdated); }
                } else { $this->setLastError("Does not have access rights!"); } }
        }
        if  ($result) {
            $this->getCurrentSession()->clearValue('CurrentOrder');
            $this->setLastError("");
        }
        return $result;
    }
    
    
    
    public function renderGetCurrentPaymentProviders() {
        $pPaymentProviders = [];
        $vCollection = $this->actionGetCurrentPaymentProviders();
        foreach ($vCollection as $provider) {
            $vIsInited = $provider->isInited();
            $providerInfo = [];
            $providerInfo += ['inited' => $vIsInited];
            $providerInfo += ['logo' => $provider->getLogoUrl()];
            $providerInfo += ['title' => $provider->getCurrencyTitle()];
            if (!$vIsInited) {
                $providerInfo += ['error_message' => $provider->getLastError()];
            }
            $pPaymentProviders += [$provider->getProviderCode() => $providerInfo];
        }
        return $pPaymentProviders;
    }

    public function tryConfirmOrder() {
        try {
            $vOrder = $this->getActualOrder();
            $orderDao = new \BtcRelax\Dao\OrderDao(null, false);
            $vDB = $orderDao->getDb();
            $vDB->beginTransaction();
            $vNewOrder = $orderDao->save($vOrder);
            $vBookmarks = $vOrder->getBookmarks();
            $vSallers = [];
            foreach ($vBookmarks as $cBookmark) {
                $bookmarkDao = new \BtcRelax\Dao\BookmarkDao($vDB, false);
                $vUpdatedBookmark = $bookmarkDao->assignBookmarkToOrder($cBookmark, $vNewOrder->getIdOrder());
                $vNewOrder->addBookmark($vUpdatedBookmark);
                \array_push($vSallers, ["CustomerId" =>  $vUpdatedBookmark->getIdDroper(), "BookmarkId" => $vUpdatedBookmark->getIdBookmark() ]);
            }
            $vInvoices = $vOrder->getInvoices();
            foreach ($vInvoices as $cInvoice) {
                $invoiceDao = new \BtcRelax\Dao\InvoiceDao($vDB, false);
                $cInvoice->setIdOrder($vNewOrder->getIdOrder());
                $vNewInvoice = $invoiceDao->save($cInvoice);
                $vPaymentProvider = $cInvoice->getPaymentProvider();
                $vPaymentProvider->doInvoiceSavedEvent($vDB);
                $vNewInvoice->setPaymentProvider($vPaymentProvider);
                $vNewOrder->addInvoice($vNewInvoice);
            }
            $result = $vDB->commit();
            $this->setCurrentOrder($vNewOrder);
            $this->notifyDropers($vSallers);
        } catch (\BtcRelax\Exception\AssignBookmarkException $exc) {
            $this->setCurrentOrder();
            \BtcRelax\Log::general($exc->getMessage(), \BtcRelax\Log::WARN);
            $this->setLastError("That hidden point already catched by other user!");
        } catch (PDOException $e) {
            $this->setCurrentOrder();
            $this->setLastError($e->getMessage());
        }
        return isset($result) ? $result : false;
    }

    public function notifyDropers(array $pUserPointsList) {
        foreach ($pUserPointsList as $vUserPoint ) {
            $pUserId = $vUserPoint['CustomerId'];
            $vAM = \BtcRelax\Core::createAM();
            $vNotificator = $vAM->getUserNotificatorByUserId($pUserId);
            if (FALSE !== $vNotificator) {
                $vMsg = \sprintf("Ваша закладка №:%s была только что заказанна. Проверьте её состояние.",$vUserPoint['BookmarkId'] );
                $vNotificator->pushMessage($vMsg);
                \BtcRelax\Log::general( \sprintf("Pushed message:%s", $vMsg), \BtcRelax\Log::INFO  );
                $this->setLastError();
            } else { $this->setLastError(\sprintf("Error getting notificator for saller id: %s", $pUserId));  }
        }
    }
    
    public function setOrder(array $pParams) {
        // Need to check are that owner try to change order
        $result = false;
        if (!count($pParams) > 0) {
            $this->setLastError("Error while setting order. 0 arguments passed in, when call setOrder method");} 
            else { 
                $vOrder = $this->getActualOrder();
                $vSession = $this->getCurrentSession();
                foreach ($pParams as $key => $value) {
                 switch ($key) {
                    case 'pProvider':
                        $vProviders = $this->actionGetCurrentPaymentProviders();
                        foreach ($vProviders as $cProvider) {
                            if ($cProvider->getProviderCode() === $value) {
                                $vRE = \BtcRelax\Core::createRE();
                                $vInvoice = $vRE->createInvoice($vOrder, $cProvider);
                                if ($vInvoice instanceof \BtcRelax\Model\Invoice ) {
                                    $vInvoice->setPaymentProvider($cProvider);
                                    $vOrder->addInvoice($vInvoice);
                                    $vSession->clearValue('CurrentPaymentProviders');
                                    $vOrder->setState(\BtcRelax\Model\Order::STATUS_PREPARING);
                                    $this->setCurrentOrder($vOrder);
                                    $result = true;                                
                                } else { 
                                    $this->setLastError(\sprintf("Error while creating invoice for payment provider: %s",$value ));
                                }
                                break;} } break;
                    case 'idBookmark':
                        if (!$vOrder->isHasBookmarkId($value)) {
                            $vAM = \BtcRelax\Core::createAM();
                            $vRE = \BtcRelax\Core::createRE();
                            $vPM = \BtcRelax\Core::createPM();
                            $vBookmark = $vPM->getBookmarkById($value);
                            if ($vBookmark->getState() == Model\Bookmark::STATUS_PUBLISHED) {
                                $vBookmarkOwnerId = $vBookmark->getIdDroper();
                                $vSaller = $vAM->getUserById($vBookmarkOwnerId);
                                $vPaymentProviders = $vRE->initPaymentProviders($vSaller);
                                $vSession->setValue('CurrentPaymentProviders', $vPaymentProviders);
                                $vOrder->addBookmark($vBookmark);
                                $vOrder->setState(\BtcRelax\Model\Order::STATUS_CREATED);
                                $this->setCurrentOrder($vOrder);
                                $result = true;} 
                            else { 
                                $this->setLastError("Bookmark already changed state!");
                                $result = false;}
                            } else { $result = true; } break;
                    case 'confirmOrder': $result = $this->tryConfirmOrder();
                        break;
                    case 'cancelOrder': $result = $this->cancelOrder();
                        break;
                    case 'finishOrder': $result = $this->finishOrder();
                        break;
                    case 'checkOrderPayment': $result = $this->checkOrderPayment();
                        break;
                    case 'unlockBookmark': $result = $this->unlockBookmark($value);
                        break;
                    case 'bookmarkCatched': $result = $this->bookmarkCatched($value);
                        break;
                    default: break;
                }
            }
        }
        if ($result) {
            $this->setLastError();
        }
        return $result;
    }

    public static function orderById(int $orderId):\BtcRelax\Model\Order {
        $dao = new \BtcRelax\Dao\OrderDao();
        $vOrder = $dao->findById($orderId);
        return \BtcRelax\OM::initOrder($vOrder);
    }
            
            
    public function getOrderById($orderId) {
        $dao = new \BtcRelax\Dao\OrderDao();
        $result = $dao->findById($orderId);
        return $result;
    }

    public function renderActiveOrder() {
        $result = []; $vOrder = $this->getActualOrder();
        if (\FALSE !== $vOrder) {
            $vOrderState = $vOrder->getState(); $result += ["OrderState" => $vOrderState];
            if (!empty($vOrder->getIdOrder())) { $result += ["OrderId" => $vOrder->getIdOrder()];}
            $vPM = \BtcRelax\Core::createPM();
            $vBookmarksList = $vPM->renderGetOrderBookmarks($vOrder);
            $result += ["CurrentBookmarks" => $vBookmarksList];
            switch ($vOrderState) {
                case \BtcRelax\Model\Order::STATUS_CREATED:
                    $result += ["CurrentPaymentProviders" => $this->renderGetCurrentPaymentProviders()];
                    break;
                case \BtcRelax\Model\Order::STATUS_PREPARING:
                    $result += ["CurrentInvoices" => $this->renderOrderInvoice()];
                    break;
                case \BtcRelax\Model\Order::STATUS_CONFIRMED: case \BtcRelax\Model\Order::STATUS_WAIT_FOR_PAY:
                    $result += ["CurrentInvoices" => $this->renderOrderInvoice()];
                    break;
                case \BtcRelax\Model\Order::STATUS_PAID:
                    $result += ["LockedPoints" => $this->renderBookmarksForUnlock($vBookmarksList)];
                    $result += ["UnLockedPoints" => $this->renderBookmarksForConfirm($vBookmarksList)];
                    break;
                case \BtcRelax\Model\Order::STATUS_CANCELED:
                case \BtcRelax\Model\Order::STATUS_FINISHED:
                    $result += ["Finished" => $vOrder->getEndDate()];
                    break;
                default: break;
            }
        }
        return $result;
    }

    public function renderOrderInvoice() {
        $invoiceInfo = [];
        $vOrder = $this->getActualOrder();
        foreach ($vOrder->getInvoices() as $vInvoice) {
            array_push($invoiceInfo, $vInvoice->renderInvoice());
        }
        return $invoiceInfo;
    }

    public function getOrdersByUser( \BtcRelax\Model\User $pUser = null,$onlyActive = true) {
        $result = false;
        /* @var $onlyActive boolean */
        $vAM = \BtcRelax\Core::createAM();
        if (empty($pUser)) {  $pUser = $vAM->getUser(); }
        $vCustomerId = $pUser->getIdCustomer();
        $orderSearchCriteria = new \BtcRelax\Dao\OrderSearchCriteria($vCustomerId, $onlyActive);
        $dao = new \BtcRelax\Dao\OrderDao();
        $founded = $dao->find($orderSearchCriteria);
        if (count($founded) > 0) {
            $activeOrder = reset($founded);            
            $vPM = \BtcRelax\Core::createPM();
            $vRE = \BtcRelax\Core::createRE();
            $vBookmarks = $vPM->actionGetOrderBookmarks($activeOrder);
            $vInvoices = $vRE->actionGetInvoicesByOrder($activeOrder);
            $vPaymentProviders = [];
            foreach ($vBookmarks as $vBookmark) {
                $vPaymentProviders = $vRE->initPaymentProviders($vAM->getUserById($vBookmark->getIdDroper()));
            }
            $vRE->initInvoicesProviders($vPaymentProviders, $vInvoices);
            $result = $activeOrder;
        }
        return $result;
    }

    public static function initOrder(\BtcRelax\Model\Order $pOrder): \BtcRelax\Model\Order {
        $vAM = \BtcRelax\Core::createAM();
        $vPM = \BtcRelax\Core::createPM();
        $vRE = \BtcRelax\Core::createRE();
        $vBookmarks = $vPM->actionGetOrderBookmarks($pOrder);
        $vInvoices = $vRE->actionGetInvoicesByOrder($pOrder);
        $vPaymentProviders = [];
        foreach ($vBookmarks as $vBookmark) {
            $vPaymentProviders = $vRE->initPaymentProviders($vAM->getUserById($vBookmark->getIdDroper()));
        }
        $vRE->initInvoicesProviders($vPaymentProviders, $vInvoices);
        return ($pOrder);
    }

    public function unlockBookmark($value) {
        $vOrder = $this->getActualOrder();
        $vBookmarkId = (int) $value;
        $vPM = \BtcRelax\Core::createPM();
        $result = $vPM->unlockBookmarkByOrder($vOrder, $vBookmarkId);
        return $result;
    }

    public function renderBookmarksForConfirm($vBookmarksList) {
        $result = [];
        foreach ($vBookmarksList as $cBookmark) {
            if ($cBookmark["Unlocked"]) {
                $result += ["id" => $cBookmark["id"]];
            }
        }
        return $result;
    }

    public function renderBookmarksForUnlock($vBookmarksList) {
        $result = [];
        foreach ($vBookmarksList as $cBookmark) {
            if (!$cBookmark["Unlocked"]) {
                $result += ["id" => $cBookmark["id"]];
            }
        }
        return $result;
    }

    public function bookmarkCatched($value) {
        $vOrder = $this->getActualOrder();
        $vBookmarkId = (int) $value;
        $vPM = \BtcRelax\Core::createPM();
        $result = $vPM->setBookmarkCatchedByOrder($vOrder, $vBookmarkId);
        if ($result) {
            $vIsAllCatched = true;
            foreach ($vOrder->getBookmarks() as $cBookmark) {
                if (($cBookmark->getState() !== \BtcRelax\Model\Bookmark::STATUS_LOST) && ($cBookmark->getState() !== \BtcRelax\Model\Bookmark::STATUS_CATCHED)) {
                    $vIsAllCatched = false;
                    break;
                }
            }
            if ($vIsAllCatched) {
                $vOrder->setEndDate(new \DateTime());
                $vOrder->setState(\BtcRelax\Model\Order::STATUS_FINISHED);
                $vOrderDao = new \BtcRelax\Dao\OrderDao();
                $vOrderDao->save($vOrder);
            }
        }
        return $result;
    }

    public function finishOrder() {
        $result = false;
        $vOrder = $this->getActualOrder();
        if ($vOrder instanceof \BtcRelax\Model\Order) {
            if ($vOrder->getState() === \BtcRelax\Model\Order::STATUS_FINISHED) {
                $this->setCurrentOrder();
                $this->setLastError();
                $result = true;
            }
        } else {
            $this->setLastError("There is now active order!");
        }
        return $result;
    }

    public function getOrderInfoById($vParams) {
        if (\is_int($vParams)) { $pId = $vParams; } else { $pId = $vParams['id']; }
        $pOrder = $this->getOrderById($pId);
        if (\FALSE !== $pOrder ) {
            \BtcRelax\Log::general(\sprintf('OM getting info for order id:%s',$pId), \BtcRelax\Log::INFO );
			$vOrderDao = new \BtcRelax\Dao\OrderDao(); 
                        $vSearchCriteria = new \BtcRelax\Dao\OrderSearchCriteria();
                        $vSearchCriteria->setOrderId($pId);
			$vSearchCriteriaI = new \BtcRelax\Dao\InvoiceSearchCriteria(["idOrder" => $pId, "isActive" => false]);
                        $vInvoiceDao = new \BtcRelax\Dao\InvoiceDao();
                        $vOrder = $vOrderDao->fullOrders($vSearchCriteria);
			$vInvoice = $vInvoiceDao->fullInvoices($vSearchCriteriaI);
			$result  = [ "order" => $vOrder[0], "invoice" => $vInvoice[0] ];
			\BtcRelax\Log::general(\sprintf('OM order info:%s',\json_encode($result)), \BtcRelax\Log::INFO ); 
        } else { $this->setLastError(\sprintf("Order with id:%s not found",$pId)) ; $result = ["error"=> $this->getLastError()]; }
        return $result;
        
    }
    
    public function getMaxOrderId() {
        $vOrderDao = new \BtcRelax\Dao\OrderDao();
        $result = $vOrderDao->getMaxOrderId();
        return $result;       
    }



}
