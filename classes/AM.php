<?php
namespace BtcRelax;

class AM implements IAM {
    private $_core;
    private $_currentSession = null;
    private $_user = null;
    private $_lastError = null;
    
    public function __construct() {
        global  $core;
        $this->_core = $core;
    }
    
    public function getLastError() {
        return $this->_lastError;
    }

    public function setLastError($_lastError = null) {
	if (!empty($_lastError)) {
            \BtcRelax\Log::general(\sprintf('AM was set last error, to:%s',$_lastError), \BtcRelax\Log::WARN );
	}
        $this->_lastError = $_lastError;
    }

    public function getCurrentSession() {
        if (is_null($this->_currentSession))
        { $this->_currentSession = $this->_core->getCurrentSession(); }
        return $this->_currentSession;
    }
    
    public function getUser(): \BtcRelax\Model\User {
        if (empty($this->_user)) { $this->_user = $this->getCurrentSession()->getValue('CurrentUser'); }
        if (empty($this->_user)) 
            { throw new \BtcRelax\Exception\AuthentificationCritical('User not identified in that session!'); }
        return $this->_user;
    }

    public function setUser(\BtcRelax\Model\User $pUser = null) {
        if ($pUser === null) { $this->_currentSession->clearValue('CurrentUser'); } 
        else { $this->_currentSession->setValue('CurrentUser',$pUser); }
        $this->_user = $pUser;
    }

    public function beginAuthenticate($pAuthType, $pParams = null) {
        $vResult= false; 
        $isActiveAuth = \BtcRelax\SecureSession::isSessionStarted() === true ? \BtcRelax\SecureSession::getValue('AuthInProcess') : false;
        if (!$isActiveAuth) { 
            $vCurrentIdent = \BtcRelax\Model\Identicator::createInstanceByType($pAuthType);
            $vCurrentIdent->init(); $vResult =  $vCurrentIdent->processAuth($pParams);
            if ($vResult) { 
                $newUser = new \BtcRelax\Model\User(); $this->setUser($newUser);
                if ($vCurrentIdent->getAuthenticationState() === \BtcRelax\Model\Identicator::STATE_INPROCESS)
                { $vSession->setValue('AuthInProcess',$vCurrentIdent->getIdentTypeCode()); } 
                else {$vSession->clearValue('AuthInProcess'); } $this->setSessionState();
          } else { $this->setLastError($vCurrentIdent->getLastError()); }
        } else { $this->setLastError("Authentification process already started!"); }
        return $vResult;        
    }

    public function checkAuth($vParams = null) {
        $result = false; $vSession = $this->getCurrentSession();
        $isActiveAuth = $vSession->getValue('AuthInProcess');
        if (!\is_null($isActiveAuth)) { $vIdent = $vSession->getValue($isActiveAuth); 
        $result= $vIdent->checkAuth($vParams); } 
        return $result;        
    }
    
    public function cancelAuthenticate() {
        $result = false; $vSession = $this->getCurrentSession();
        $isActiveAuth = $vSession->getValue('AuthInProcess');
        if (!is_null($isActiveAuth)) { $vSession->clearValue($isActiveAuth); $result = true; }
        $this->setSessionState(); 
        return $result;
    }
    
    public  function doAuthentificate($pAuthParams) {
        $vIdent = $this->getIdentifiers();
        if ($vIdent->doAuthenticate($pAuthParams))
        { if ($vIdent->getAuthenticationState()=== \BtcRelax\Model\Identicator::STATE_IDENTIFIED) { $this->SignIn($vIdent); } }
        return $vIdent;
    }

    public function isUserHasRight($pRightCode, $pUser = null) {
        if (empty($pRightCode)) { return true; } 
        else { 
            if ($pUser === null) { $pUser= $this->getUser(); }
            $vUserRights = $pUser->getRights();
            foreach ($vUserRights as $right) {
                if ($right->getRightCode() === $pRightCode) { return true; }
            }
        } return false;
    }
    
//    private function getIdentByType($pAuthType)    {   $vResult = false;
//        switch ($pAuthType) {
//            case Model\Identicator::ID_TYPE_BITID:
//                $vResult = new \BtcRelax\Model\IdentBitId();    
//                break;
//            case Model\Identicator::ID_TYPE_MAIL:
//                $vResult = new \BtcRelax\Model\IdentEMail();
//                break;
//            case Model\Identicator::ID_TYPE_TELEGRAMM:
//                $vResult = new \BtcRelax\Model\IdentTelegram();
//                break;
//            default:
//                \BtcRelax\Log::general('Unknown auth type',Log::FATAL);
//                break;
//        }
//        $vResult->init();
//        return $vResult;
//    }

    /// Can return false in case of no auth process now
    public function getActiveIdent()    {
        $result = false;
        $vSession = $this->getCurrentSession();
        $isActiveAuth = $vSession->getValue('AuthInProcess');
        if (!is_null($isActiveAuth)) { 
           $activeIdent = $vSession->getValue($isActiveAuth);
           if ($activeIdent instanceof \BtcRelax\Model\Identicator)
           { \BtcRelax\Log::general(\sprintf("Active ident type:%s with context: %s", $isActiveAuth, $activeIdent), \BtcRelax\Log::DEBUG);
             $result =  $activeIdent; }
        }
        return $result;
    }

    public function getIdentifiers() {
        $result = $this->getActiveIdent();
        if (!$result)
            {
                $vSession = $this->getCurrentSession();
                $vBitIdIdent = $vSession->getValue(\BtcRelax\Model\Identicator::ID_TYPE_BITID);
                if (\is_null($vBitIdIdent)) 
                    { $vBitIdIdent = \BtcRelax\Model\Identicator::createInstanceByType(\BtcRelax\Model\Identicator::ID_TYPE_BITID); }
                    $identCollection = [ \BtcRelax\Model\Identicator::ID_TYPE_BITID => $vBitIdIdent ];
                if (IS_ALLOW_EMAIL) 
                { 
                    $vNewIdent = $vSession->getValue(\BtcRelax\Model\Identicator::ID_TYPE_MAIL);
                    $vNewIdent = \is_null($vNewIdent)? \BtcRelax\Model\Identicator::createInstanceByType(\BtcRelax\Model\Identicator::ID_TYPE_MAIL): $vNewIdent;
                    $identCollection += [\BtcRelax\Model\Identicator::ID_TYPE_MAIL =>  $vNewIdent ];           
                }
                if (IS_ALLOW_TELEGRAM)
                {
                    $vNewIdent = $vSession->getValue(Model\Identicator::ID_TYPE_TELEGRAMM);
                    $vNewIdent = \is_null($vNewIdent)? \BtcRelax\Model\Identicator::createInstanceByType(\BtcRelax\Model\Identicator::ID_TYPE_TELEGRAMM): $vNewIdent; 
                    $identCollection += [Model\Identicator::ID_TYPE_TELEGRAMM =>  $vNewIdent ];
                }         
            $result = $identCollection;
            }
        return $result;
    }

    public function NotifyRoot(string $vMessage, string $vEvent) 
    {   
        /* @var $vIsEnabled type */
        $vNotifyOptions = \explode(",", NOTIFY_EVENTS);
        $vIsEnabled = \in_array($vEvent, $vNotifyOptions);
        \BtcRelax\Log::general(\sprintf("Notifing event \"%s\" with message:%s set to:%s \n Notify settings:%s",$vEvent, $vMessage , $vIsEnabled, \BtcRelax\Utils::toJson($vNotifyOptions)), \BtcRelax\Log::DEBUG);
        if ($vIsEnabled === true) 
        {
            $vNotificator =  $this->getUserNotificatorByUserId(HUB_ROOT);
            if (FALSE !== $vNotificator) {
                $vNotificator->pushMessage($vMessage);
                \BtcRelax\Log::general(\sprintf("Pushed message:%s", $vMsg), \BtcRelax\Log::INFO );
                $this->setLastError();
            } else { $this->setLastError(\sprintf("Error getting notificator for user id: %s", $pUserId));  }
        }
    }
    
    public function createNewUser() {
       $result = false;
        $ident = $this->getActiveIdent();
        if ($ident != false)
        {
            if (IS_FREE_REGISTER) {
                $vUser = $this->getUser();
                $msg= \sprintf('Try to create user for ident: %s', $ident->__toString() );
                $result = $vUser->RegisterNewUserId($ident);
                $msg .= \sprintf(' Result is:%s', $result);
                \BtcRelax\Log::general($msg, \BtcRelax\Log::INFO);
                if ($result) {
                    $this->NotifyRoot($msg, 'register');
                    $result = $this->SignIn($ident);
                }
            } else { $this->setLastError("Не, розслабься. Джа не велел тебя пускать, и я тебя не пропущу. Но, если ты так считаешь что я ошибся, то обратись к Джа, возможно он тебя пропустит."); }
        } else  { $this->setLastError("Hasnt any identification for register"); }
        return $result;
    }
    
    ///
    ///
    ///
    public function registerNewUser(\BtcRelax\Model\User $pInvitor, \BtcRelax\Model\Identicator $pNewIdentificator = null) {
        try {
            $vNewUser = \BtcRelax\Model\User::createNew();
            $vNewIdent = $pNewIdentificator === null ? $this->getActiveIdent(): $pNewIdentificator;
            $customerDao = new \BtcRelax\Dao\CustomerDao(null,false);
            $vDB = $customerDao->getDb();
            $vDB->beginTransaction();           
            $vNewUserResult = $customerDao->save($vNewUser);
            $identifierDao = new \BtcRelax\Dao\IdentifierDao($vDB, false);
            $vNewIdent->setIdCustomer($vNewUser->getIdCustomer());
            $vNewIdentResult = $identifierDao->save($vNewIdent);
            $customerDao->addCustomersHierarhy($pInvitor->getIdCustomer(), $vNewUserResult->getIdCustomer());
            $result = $vDB->commit();
        } catch (\Exception $exc) {
            \BtcRelax\Log::general($exc, \BtcRelax\Log::ERROR);
            $this->setLastError($exc->getMessage());
        }
        return isset($result) ? $result : false;        
    }
            
    protected function setSessionState()    {
        $vResult = \BtcRelax\SecureSession::STATUS_UNAUTH;
        $vSession = $this->getCurrentSession();
        $beginingState = \BtcRelax\SecureSession::getSessionState();
        $isActiveAuth = $vSession->getValue('AuthInProcess');
        if ($isActiveAuth)
        {
          $vIdent = $this->getActiveIdent();
          if ($vIdent->getAuthenticationState()!== \BtcRelax\Model\Identicator::STATE_IDENTIFIED)
            {
               $vResult = \BtcRelax\SecureSession::STATUS_AUTH_PROCESS; 
            }
        }
        if ($vResult !== \BtcRelax\SecureSession::STATUS_AUTH_PROCESS)
        {
            /* @var $vUser \BtcRelax\Model\User */
            $vUser = $this->getUser();
            if (FALSE === $vUser->getIsSignedIn())
            {
                $vResult = \BtcRelax\SecureSession::STATUS_GUEST;
            } else {
                $vCustomerId = $vUser->getIdCustomer();
                if ($vCustomerId === HUB_ROOT) {
                        $vResult = \BtcRelax\SecureSession::STATUS_ROOT;
                    } else {
                        $vResult = $vUser->getIsBaned() ? \BtcRelax\SecureSession::STATUS_BANNED: \BtcRelax\SecureSession::STATUS_USER;
                    }
                }            
        }
        if ( $beginingState !== $vResult)
        {
            \BtcRelax\Log::general(\sprintf("Session Id:%s changed state to:%s", session_id(),$vResult  ), \BtcRelax\Log::INFO);
            if (($vResult === \BtcRelax\SecureSession::STATUS_ROOT) || ($vResult === \BtcRelax\SecureSession::STATUS_BANNED) || ($vResult === \BtcRelax\SecureSession::STATUS_USER))
            { $vSession->setValue("Signed",true); } else {  $vSession->setValue("Signed", false); }
            $vSession->setValue("SessionState",$vResult);
        }
    }              
    
//    public function getRootNotificator()  {
//        $result = false;
//        $vRootUser = $this->getHubRootUser();
//        if (!empty($vRootUser)) 
//        {
//            $result = $this->getUserNotificatorByUserId($vRootUser);
//        }
//        $vPushbullet = new \BtcRelax\PushbulletApi();
//        if (FALSE !== $vRootUser) {
//            $vPushToken = $vRootUser->getPropertyValue('pushbull_token');
//            if ($vPushbullet->init($vPushToken)) {
//                return $vPushbullet;
//            } else {
//                \BtcRelax\Log::general(\sprintf("Error while init Pushbullet provider:%s", $vPushbullet->getLastError()), \BtcRelax\Log::WARN);
//            }
//        }
//        return $result;
//    }
    
    public function getUserNotificatorByUserId($pUserId) {
        $result = false;
        //$vUser = $this->getUserById($pUserId);
        $vUser = \BtcRelax\AM::userById($pUserId);
        if ($vUser instanceof \BtcRelax\Model\User) {
            $vPushToken = $vUser->getPropertyValue('pushbull_token');
            if ($vPushToken) {
                $vPushbullet = new \BtcRelax\PushbulletApi();
                if ($vPushbullet->init($vPushToken)) {
                    $result = $vPushbullet;
                    \BtcRelax\Log::general(\sprintf("Pushbullet provider inited for user:%s", $pUserId), \BtcRelax\Log::DEBUG);
		    $this->setLastError();
                } else {
                    \BtcRelax\Log::general(\sprintf("Error while init Pushbullet provider:%s", $vPushbullet->getLastError()), \BtcRelax\Log::WARN);
                }
            } else { 
                \BtcRelax\Log::general(\sprintf("User id:%s has no push token property!", $pUserId), \BtcRelax\Log::WARN);
            }
        } else { $this->setLastError(\sprintf("User id:%s not found, when try to get notificator", $pUserId)); }
        return $result;
    }

    public static function userById(string $pId): \BtcRelax\Model\User {
        $vUser = new \BtcRelax\Model\User();
        $inited = $vUser->init($pId);        
        return $inited ;
    }
    
    public static function saveUser(\BtcRelax\Model\User $vUser) {
        $dbo = new \BtcRelax\Dao\CustomerDao();
        return $dbo->save($vUser);
    }




    public function getUserById($pId) {
        $vUser = new \BtcRelax\Model\User();
        try {
            $vUser->init($pId);
            $this->actionGetUserRights($vUser);
            $this->actionGetProperties($vUser);
            $this->actionGetIdentifiers($vUser);
            $this->setLastError();
        } catch (\LogicException $exc) {
            $this->setLastError($exc->getMessage());
            return false;
        }
        return $vUser;
    }

    public function loginUserByToken($token) {
        $result = false;
        $dao = new CustomerDao();
        $customerId = $dao->getUserByToken($token);
        if (FALSE !== $customerId)
        {
            global $core;
            $user = $this->getUserById($customerId);
            $result = $core->setAuthenticate($user);
        }
        return $result;
    }
    
    private function fillUserInfo(\BtcRelax\Model\User $pUser)    {
        $dao = new CustomerDao();
        $vCustId = $pUser->getCustomerId();
        $vXPub = $pUser->getXPub();
        $pUser->setXPub($dao->GetPubKeyByCustomer($vCustId));
        $invoicesCount = $dao->GetInvoiceAddressCountByXPub($vXPub);
        $pUser->setInvoicesCount($invoicesCount);     
        return $pUser;
    }

    public function SignIn(\BtcRelax\Model\Identicator $pIdent) {
        $result = false;
        \BtcRelax\Log::general(\sprintf('Begin sign in:%s',$pIdent ), \BtcRelax\Log::DEBUG);
        $vIdentDao = new \BtcRelax\Dao\IdentifierDao();
        $vIdentSearchCriteria = new \BtcRelax\Dao\IdentifierSearchCriteria(["IdentTypeCode" => $pIdent->getIdentTypeCode(), "IdentityKey" => $pIdent->getIdentityKey() ]);
        $vQuery = $vIdentDao->getFindSql($vIdentSearchCriteria);
        $vIdentRow  = $vIdentDao->query($vQuery)->fetch();
        if (\FALSE !== $vIdentRow)
        {
            \BtcRelax\Mapping\IdentifierMapper::map($pIdent, $vIdentRow);
            $vFoundedCustomerId = $pIdent->getIdCustomer();
            if (!empty($vFoundedCustomerId))
            {  $vUser = $this->getUserById($vFoundedCustomerId); 
                $this->setUser($vUser);
                $result = true;  
                $vMsg = \sprintf('Session id:%s <br> Signed in user id:%s <br> Identifier:%s', session_id() , $vFoundedCustomerId, $pIdent);
                $this->NotifyRoot($vMsg, 'login'); 
                \BtcRelax\Log::general( $vMsg, \BtcRelax\Log::INFO ); 
            }
        } else { \BtcRelax\Log::general(\sprintf('User not found by identifier:%s',$pIdent), \BtcRelax\Log::INFO ); }
        $this->setSessionState(); return $result;
    }
    
    public function SignOut() {
        $vSession = $this->getCurrentSession();
        $vSession->clearValue('AuthInProcess');
        $vSession->clearValue('CurrentUser');
        $vSession->clearValue('CurrentOrder');
        $isActiveAuth = $vSession->getValue('AuthInProcess');
        if (!empty($isActiveAuth))
        {
            $vSession->clearValue($isActiveAuth);
        }    
        $vSession->setValue("Signed",false);
        $vSession->setValue("SessionState", \BtcRelax\SecureSession::STATUS_UNAUTH);
    }
    
    private function actionGetUserRights(\BtcRelax\Model\User $vUser = null) {
        if (empty($vUser)) { $vUser = $this->getUser(); }
        $vId = $vUser->getIdCustomer();
        $dao = new \BtcRelax\Dao\CustomerRightsDao();
        $vRights = $dao->findById($vId);
        $vUser->setRights($vRights);
        return $vRights;
    }
    
    private function actionGetProperties(\BtcRelax\Model\User $vUser = null)    {
        if (empty($vUser)) { $vUser = $this->getUser(); }
        $vId = $vUser->getIdCustomer();
        $dao = new \BtcRelax\Dao\CustomerPropertyDao();
        $vProperties = $dao->findById($vId);
        $vUser->setProperties($vProperties);
        return $vProperties;        
    }

    private function actionGetIdentifiers(\BtcRelax\Model\User $vUser = null) {
        if (empty($vUser)) { $vUser = $this->getUser(); }
        $vId = $vUser->getIdCustomer();
        $vSearch = new \BtcRelax\Dao\IdentifierSearchCriteria(["IdCustomer" => $vId]);
        $dao = new \BtcRelax\Dao\IdentifierDao();
        $vIdentifiers = $dao->find($vSearch);
        $vUser->setIdentifiers($vIdentifiers);
        return $vIdentifiers;  
    } 
    
    public function renderGetIdentifiers(\BtcRelax\Model\User $vUser = null) {
        $vResult = [];
        $vIdents = $this->actionGetIdentifiers($vUser);
        foreach ($vIdents as $ident) {
            $msg = \sprintf("Founded identifier :%s", $ident);
            \BtcRelax\Log::general($msg, \BtcRelax\Log::DEBUG);    
            array_push($vResult, ['IdentType' => $ident->getIdentTypeCode(), 'IdentKey' => $ident->getIdentityKey(), 'Description' => $ident->getDescription() ] ) ;
        }
        return $vResult;
    }
 
    public function renderGetUserRights(\BtcRelax\Model\User $vUser = null) {
        $vResult = [];
        $vRights = $this->actionGetUserRights($vUser);
        foreach ($vRights as $right) {
            $vResult += [$right->getRightCode() => $right->getRightDescription()];
        }
        return $vResult;
    }
    
    public function renderGetUserProperties(\BtcRelax\Model\User $vUser = null) {
        $vResult = [];
        $vProps = $this->actionGetProperties($vUser);
        foreach ($vProps as $prop) {
            $vResult += [$prop->getPropertyTypeCode() => [$prop->getPropertyValue(), $prop->getPropertyTypeTitle()]];
        }
        return $vResult;
    }

    public function getUserInfoById($vParams = null) {
        if (empty($vParams)) { $vCurrentUser = $this->getUser(); $vParams = ["id" => $vCurrentUser->getIdCustomer()];  }
        $vSearchCriteria = new \BtcRelax\Dao\CustomerSearchCriteria($vParams);
        $vCustomerDao = new \BtcRelax\Dao\CustomerDao();
        $resultArray = $vCustomerDao->fullCustomers($vSearchCriteria);
        if (\count($resultArray) === 1) { $result = $resultArray[0]; } else { $result = false; }
        return $result;
    }
        
        
    public function getUserInvoices(\BtcRelax\Model\User $vUser = null) {
        if (empty($vUser)) { $vUser = $this->getUser(); }
        $vSearchCriteria = new \BtcRelax\Dao\InvoiceSearchCriteria(["SallerId" => $vUser->getIdCustomer()]);
        $vInvoiceDao = new \BtcRelax\Dao\InvoiceDao();
        return $vInvoiceDao->getInvoices($vSearchCriteria);
    }
}
