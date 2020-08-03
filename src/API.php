<?php
namespace BtcRelax;

use BtcRelax\Model\Identicator;

abstract class API
{
    public static function response($data, int $status = 200)
    {
        \BtcRelax\API::setHeaders($status);
        $answer = \json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE);
        \BtcRelax\Log::general($answer, \BtcRelax\Log::INFO);
        echo $answer;
    }

    public static function getStatusMessage(int $code = 200)
    {
        $status = array(
            100 => 'Continue',101 => 'Switching Protocols',
            200 => 'OK',201 => 'Created',202 => 'Accepted',203 => 'Non-Authoritative Information', 204 => 'No Content',205 => 'Reset Content',206 => 'Partial Content',
            300 => 'Multiple Choices',301 => 'Moved Permanently',302 => 'Found',303 => 'See Other',304 => 'Not Modified',
            305 => 'Use Proxy',306 => 'Session have not nonce',307 => 'Temporary Redirect',
            400 => 'Bad Request',401 => 'Unauthorized',402 => 'Payment Required',403 => 'Forbidden',404 => 'Not Found',
            405 => 'Method Not Allowed',406 => 'Not Acceptable',407 => 'Proxy Authentication Required',408 => 'Request Timeout',409 => 'Conflict',
            410 => 'Gone',411 => 'Length Required',412 => 'Precondition Failed',413 => 'Request Entity Too Large',414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',416 => 'Requested Range Not Satisfiable',417 => 'Expectation Failed',
            500 => 'Internal Server Error',501 => 'Not Implemented',502 => 'Bad Gateway',503 => 'Service Unavailable',504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported');
        return ($status[$code]) ? $status[$code] : $status[500];
    }

    public static function setHeaders(int $code = 200)
    {
        header("HTTP/1.1 " . $code . " " . \BtcRelax\API::getStatusMessage($code));
        header("Content-Type:application/json");
    }

    abstract public function processApi();

    protected function process()
    {
        global $core;
        $vRequest = $core->getRequest();
        $reqFunc = $vRequest->getParamByKey('action');
        if ((int) \method_exists($this, $reqFunc) > 0) {
            try {
                \BtcRelax\Log::general(\sprintf('API was called method: %s', $reqFunc), \BtcRelax\Log::INFO);
                $this->$reqFunc();
            } catch (\Error $exc) {
                \BtcRelax\Log::general($exc, \BtcRelax\Log::FATAL);
            }
        } else {
            \BtcRelax\Log::general(\sprintf('Incorrect call to API. Unknown method:%s', $reqFunc), \BtcRelax\Log::WARN);
            \BtcRelax\API::response('Method not found', 404);
        }
    }

//        if ($vSessionState !== \BtcRelax\SecureSession::STATUS_NOT_INIT) {
//           $vCore = \BtcRelax\Core::getIstance();
//           $param["SessionLifeTime"] = $vSession->getSessionLifetime();
//           if ($vSessionState === \BtcRelax\SecureSession::STATUS_AUTH_PROCESS)
//           {
//               $vAM = \BtcRelax\Core::createAM();
//               $vAuthenticator = $vAM->getActiveIdent();
//               $vResult = $vAuthenticator->getAuthParams();
//               $vResult += ["authType"=>$vAuthenticator->getIdentTypeCode() ];
//               $param["StartAuthResponce"] = $vResult;
//           }
//           if (($vSessionState === \BtcRelax\SecureSession::STATUS_USER) || ($vSessionState === \BtcRelax\SecureSession::STATUS_ROOT)
//                || ($vSessionState === \BtcRelax\SecureSession::STATUS_BANNED) ) {
//                $vAM = \BtcRelax\Core::createAM();
//                $vUser = $vAM->getUser();
//                $vOM = \BtcRelax\Core::createOM();
//                $vCurrentOrder = $vOM->getActualOrder();
//                //$vLHC = new \BtcRelax\LHCApi(LHC_URL,LHC_USER,LHC_API_KEY);
//                $param["UserInfo"] = ["userId" => $vUser->getIdCustomer() ,
//                    "info" => $vAM->getUserInfoById() ,
//                    "lhcParams" => \BtcRelax\LHCApi::prepareUserInfo($vUser),
//                    "userRights" => $vAM->renderGetUserRights(),
//                    "userProperties" => $vAM->renderGetUserProperties(),
//                    "userIdentifiers" => $vAM->renderGetIdentifiers() ,
//                    "isBaned" => $vUser->getIsBaned() ,
//                    "hasActiveOrder" => \FALSE !== $vCurrentOrder ];
//            }
//        }
//    }
//
    /*
     *  Get info about current user
     */
//    public function getUserInfo() {
//        $param = [];
//        $this->_core->initSession();
//        $vSessionState = $this->_core->getSessionState();
//        if (($vSessionState === \BtcRelax\SecureSession::STATUS_USER) || ($vSessionState === \BtcRelax\SecureSession::STATUS_ROOT)  ) {
//            $vAM = \BtcRelax\Core::createAM();
//            $vOM = \BtcRelax\Core::createOM();
//            //$vLHC = new \BtcRelax\LHCApi(LHC_URL,LHC_USER,LHC_API_KEY);
//            $vCurrentOrder = $vOM->getActualOrder();
//            $vUser = $vAM->getUser();
//            $param["UserInfo"] = ["userId" => $vUser->getIdCustomer(),
//                "info" => $vAM->getUserInfoById() ,
//                "lhcParams" => \BtcRelax\LHCApi::prepareUserInfo($vUser),
//                "userRights" => $vAM->renderGetUserRights(),
//                "userProperties" => $vAM->renderGetUserProperties(),
//                "userIdentifiers" => $vAM->renderGetIdentifiers(),
//                "isBaned" => $vUser->getIsBaned() ,
//                "hasActiveOrder" => \FALSE !== $vCurrentOrder ];
//        } else {$param["UserInfo"] = ["error" => "Session state is not a logged in user!"]; }
//        $this->response($param, 200);
//    }
    
//    public function LHCChat() {
//        $param = [];
//        $this->_core->initSession();
//        $vSessionState = $this->_core->getSessionState();
//        if (($vSessionState === \BtcRelax\SecureSession::STATUS_USER) ||
//                ($vSessionState === \BtcRelax\SecureSession::STATUS_ROOT) ||
//                ($vSessionState === \BtcRelax\SecureSession::STATUS_BANNED) ) {
//            $vAM = \BtcRelax\Core::createAM();
//            $vCurrentUser = $vAM->getUser();
//            $vLHC = new \BtcRelax\LHCApi(LHC_URL,LHC_USER,LHC_API_KEY);
//            $param += $vLHC->prepareUserInfo($vCurrentUser) ;
//            $this->response($param, 200);
//        }
//        else { $this->response("You not logged in", 401  ); }
//    }
    
    /*
     * Get sessions if user has rights
     */
    public function getSessions()
    {
        $param = [];
        $this->_core->initSession();
        $vSessionState = $this->_core->getSessionState();
        if ($vSessionState === \BtcRelax\SecureSession::STATUS_USER || $vSessionState === \BtcRelax\SecureSession::STATUS_ROOT) {
            $vAM = \BtcRelax\Core::createAM();
            if ($vAM->isUserHasRight("SESSIONS")) {
                $vSession = $this->_core->getCurrentSession();
                $param = ["rows" => $vSession->getSessionsInfo()];
                $this->response($param, 200);
            } else {
                $this->response("You not have rights", 401);
            }
        } else {
            $this->response("You not logged in", 401);
        }
    }

    /*
     *  Get invoices for user *
    */
    public function getInvoices()
    {
        $param = [];
        $this->_core->initSession();
        $vSessionState = $this->_core->getSessionState();
        if ($vSessionState === \BtcRelax\SecureSession::STATUS_USER || $vSessionState === \BtcRelax\SecureSession::STATUS_ROOT) {
            $vAM = \BtcRelax\Core::createAM();
            $vInvoicesList = $vAM->getUserInvoices();
            $param = ["rows" => $vInvoicesList ];
            $this->response($param, 200);
        } else {
            $this->response("You not logged in", 401);
        }
    }
    
    public function Session()
    {
        $vParams = $this->_core->getRequest();
        if (array_key_exists('action', $vParams)) {
            $vAction = \strtolower($vParams['action']);
            switch ($vAction) {
                // Require 1 parameters
                // id - CustomerId;
                case 'startauth':
                    $sess = $this->_core->getCurrentSession();
                    $param["isSessionStarted"] = $sess->startSession();
                    $this->response($param, 200);
                    break;
                default:
                    $this->response("Not Acceptable call!", 406);
                    break;
                }
        } else {
            \BtcRelax\Log::general("Cannot determine action!", \BtcRelax\Log::ERROR);
            $this->response("Not Acceptable call!", 406);
        }
    }
    
    public function User()
    {
        $vParams = $this->_core->getRequest();
        if (array_key_exists('action', $vParams)) {
            $vAction = \strtolower($vParams['action']);
            switch ($vAction) {
                // Require 1 parameters
                // id - CustomerId;
                case 'getinfo':
                    if (\array_key_exists("id", $vParams)) {
                        $vAM = \BtcRelax\Core::createAM();
                        $result = $vAM->getUserInfoById($vParams);
                        if (\FALSE !== $result) {
                            $vUser = \BtcRelax\AM::userById($vParams['id']);
                            $vIdentifiers = $vAM->renderGetIdentifiers($vUser);
                            $result +=  ["userIdentifiers" => $vIdentifiers] ;
                            $this->response($result, 200);
                        } else {
                            $this->response("User not found", 404);
                        }
                    } else {
                        $this->response("Argument id not found", 405);
                    }
                    break;
                case 'setbanned':
                    if (\array_key_exists("id", $vParams)) {
                        try {
                            $vUser = \BtcRelax\AM::userById($vParams['id']);
                            if (\array_key_exists("banned", $vParams)) {
                                $newState = $vParams['banned'];
                            } else {
                                $newState = !$vUser->getIsBaned();
                            }
                            $vUser->setIsBaned($newState);
                            $result = \BtcRelax\AM::saveUser($vUser);
                            $this->response($result->getArray(), 200);
                        } catch (\LogicException $exc) {
                            $this->response($exc->getMessage(), 404);
                        }
                    } else {
                        $this->response("Argument id not found", 405);
                    }
                    break;
                default:
                    $this->response("Not Acceptable call!", 406);
                    break;
            }
        } else {
            \BtcRelax\Log::general("Cannot determine action!", \BtcRelax\Log::ERROR);
            $this->response("Not Acceptable call!", 406);
        }
    }
    
    
    
    
    public function Statistics()
    {
        $vParams = $this->_core->getRequest();
        if (array_key_exists('action', $vParams)) {
            $vAction = \strtolower($vParams['action']);
            switch ($vAction) {
                // Require 1 parameters
                // id - CustomerId;
                // includeIdentifiers - add identifiers array to output
                case 'getuser':
                    if (\array_key_exists("id", $vParams)) {
                        $vAM = \BtcRelax\Core::createAM();
                        $result = $vAM->getUserInfoById($vParams);
                        if (\FALSE !== $result) {
                            if ((\array_key_exists("includeIdentifiers", $vParams)) && ($vParams['includeIdentifiers'] == true)) {
                                $vUser = \BtcRelax\AM::userById($vParams['id']);
                                $vIdentifiers = $vAM->renderGetIdentifiers($vUser);
                                $result +=  ["userIdentifiers" => $vIdentifiers] ;
                            }
                        } else {
                            $this->response("User not found", 404);
                        }
                    } else {
                        $this->response("Argument id not found", 405);
                    }
                    break;
                // Require 1 parameter
                // id - OrderId
                case 'getorder':
                    $vOM = \BtcRelax\Core::createOM();
                    $result = $vOM->getOrderInfoById($vParams);
                    break;
                case 'getmaxorderid':
                    $vOM = \BtcRelax\Core::createOM();
                    $result = $vOM->getMaxOrderId();
                    break;
                // Require 1 parameter
                // id - InvoiceId
                // isNeedCheckBalance - are system will check balance for current invoice
                case 'getinvoice':
                    $vRE = \BtcRelax\Core::createRE();
                    $result = $vRE->getInvoiceInfoById($vParams);
                    break;
                default:
                    $this->response("Not Acceptable call!", 406);
                    break;
            }
            $this->response($result, 200);
        } else {
            \BtcRelax\Log::general("Cannot determine action!", \BtcRelax\Log::ERROR);
            $this->response("Not Acceptable call!", 406);
        }
    }
    
    /*
     * External methods:
     * Register user if any identificator already authenticated.
     * Loop all idenficetors throught object user.
     */
    public function registerUser()
    {
        $this->_core->initSession();
        $vAM = \BtcRelax\Core::createAM();
        $result = $vAM->createNewUser();
        $param["registerUserResult"] = $result;
        if (!$result) {
            $param["Error"] = $vAM->getLastError();
        }
        $this->response($param, 200);
    }

    /*
     * External methods:
     * checkNonce from arguments
     */
    public function checkNonce()
    {
        $this->_core->initSession();
        $vAM = \BtcRelax\Core::createAM();
        $result["checkNonceResult"] = $vAM->checkAuth();
        $this->response($result, 200);
    }

    /* Wait for input argument from Post/Get
    * mailCode - where code that was sent to mail
    */
    public function checkMailCode()
    {
        $this->_core->initSession();
        $vParams = $this->_core->getRequest();
        if (array_key_exists('mailCode', $vParams)) {
            $vAM = \BtcRelax\Core::createAM();
            $vResult = $vAM->checkAuth($vParams);
            if (!$vResult) {
                $vIdent = $vAM->getIdentifiers();
                $result["Error"] = $vIdent->getLastError();
            }
            $result["checkMailCodeResponce"] = $vResult;
            $this->response($result, 200);
        } else {
            Log::general('Not Acceptable call! CheckMailCode method need parameter: mailCode', Log::ERROR);
            $this->response('Not Acceptable call!', 406);
        }
    }

    /* Wait for input argument from Post/Get
    * mail - where mail for send code
    */
    public function sendMailId()
    {
        $this->_core->initSession();
        $vParams = $this->_core->getRequest();
        if (array_key_exists('mail', $vParams)) {
            $vAM = \BtcRelax\Core::createAM();
            $vResult = $vAM->beginAuthenticate(Identicator::ID_TYPE_MAIL, $vParams);
            $result["sendMailIdResponce"] = $vResult;
            if (false === $vResult) {
                $result["Error"] = $vAM->getLastError();
            }
            $this->response($result, 200);
        } else {
            $this->response('Parameter needed: mail', 406);
        }
    }

    public function startAuth()
    {
        $this->_core->initSession();
        $vParams = $this->_core->getRequest();
        if (array_key_exists('authType', $vParams)) {
            $result = array();
            $vAuthType = $vParams['authType'];
            $vAM = Core::createAM();
            $vResult = $vAM->beginAuthenticate($vAuthType, $vParams);
            if (false !== $vResult) {
                $vResult += ["authType"=>$vAuthType];
                $vSessionState = \BtcRelax\SecureSession::getSessionState();
                $result["SessionState"] = $vSessionState;
            }
            $result["StartAuthResponce"] = $vResult;
            $this->response($result, 200);
        } else {
            Log::general('Not Acceptable call! startAuth method need parameter: authType', Log::ERROR);
            $this->response('Not Acceptable call!', 406);
        }
    }

    public function stopAuth()
    {
        //Todo
        $this->response('Session killed', 200);
    }

    public function getActiveOrder()
    {
        $result["OrderResult"] = false;
        $this->_core->initSession();
        $vSessionState = $this->_core->getSessionState();
        if (($vSessionState === \BtcRelax\SecureSession::STATUS_USER) || ($vSessionState === \BtcRelax\SecureSession::STATUS_ROOT)) {
            $vOM = Core::createOM();
            $vOrder = $vOM->getActualOrder();
            if ($vOrder instanceof \BtcRelax\Model\Order) {
                $result["OrderResult"] = true;
                $result["OrderInfo"] = $vOM->renderActiveOrder();
            }
        }
        $this->response($result, 200);
    }

    public function Order()
    {
        try {
            $this->_core->initSession();
            $result["OrderResult"] = false;
            $vOM = Core::createOM();
            $vParams = $this->_core->getRequest();
            $vOrder = $vOM->getActualOrder();
            if (\FALSE === $vOrder) {
                $vOrder = $vOM->createNewOrder();
            }
            $result["OrderResult"] = $vOM->setOrder($vParams);
            if (false !== $result["OrderResult"]) {
                $result["OrderInfo"] = $vOM->renderActiveOrder();
            } else {
                $result["Error"] = $vOM->getLastError();
            }
            $this->response($result, 200);
        } catch (\BtcRelax\Exception\AuthentificationCritical $vError) {
            $this->response($vError->getMessage(), 401);
        }
    }

    public function Product()
    {
        $vPM = Core::createPM();
        $vCurrentProduct = null;
        $vParams = $this->_core->getRequest();
        if (array_key_exists('action', $vParams)) {
            $vAction = \strtolower($vParams['action']);
            switch ($vAction) {
                case'create':
                    $vCreateParams = \json_decode($vParams['params'], true);
                    $vNewProduct = \BtcRelax\Model\Product::WithParams($vCreateParams);
                    $vCurrentProduct = $vPM->setProduct($vNewProduct);
                    break;
                case'get':
                    $vGetParams = \json_decode($vParams['params'], true);
                    $vParamsArray = \array_pop($vGetParams);
                    $vProductId = (int)$vParamsArray['ProductId'];
                    $vCurrentProduct = $vPM->getProduct($vProductId);
                    break;
                case'update':
                    $vCurrentProduct = $vPM->setProduct($vParams);
                    break;
                default:
                    break;
            }
            $result["ProductResult"] = !is_null($vCurrentProduct);
            if (false !==$result["ProductResult"]) {
                $result["ProductState"] = $vPM->renderProduct($vCurrentProduct);
            } else {
                $result["Error"] = $vPM->getLastError();
            }
            $this->response($result, 200);
        } else {
            \BtcRelax\Log::general("Cannot determine action!", \BtcRelax\Log::ERROR);
            $this->response('Not Acceptable call!', 406);
        }
    }
    
    public function Region()
    {
        $vPM = Core::createPM();
    }
    
    public function Bookmark()
    {
        $vPM = Core::createPM();
        $vParams = $this->_core->getRequest();
        if (array_key_exists('action', $vParams)) /* @var $vAction type */
        {   $vAction = $vParams['action'];
            $result["BookmarkResult"] = false;
            switch ($vAction) {
            case'CancelFromOrder':
                if (\array_key_exists('bookmarkId', $vParams) && \array_key_exists('author', $vParams)) {
                    $vAM = \BtcRelax\Core::createAM();
                    $vUser = $vAM->getUserById($vParams['author']);
                    $vPointId = (int) $vParams['bookmarkId'];
                    $vBookmark = \BtcRelax\PM::bookmarkById($vPointId);
                    if ($vBookmark instanceof \BtcRelax\Model\Bookmark) {
                        $result["BookmarkResult"] = true;
                        $result["BookmarkState"] = $vBookmark->getStateInfo();
                    } else {
                        $result["BookmarkError"] = $vPM->getLastError();
                    }
                } else {
                    $this->response("Method SetNewState precondition failed", 412);
                }
                break;
            case'SetNewState':
                if (\array_key_exists('bookmarkId', $vParams) && \array_key_exists('author', $vParams) && \array_key_exists('state', $vParams)) {
                    $vAM = \BtcRelax\Core::createAM();
                    $vUser = $vAM->getUserById($vParams['author']);
                    $vPointId = (int) $vParams['bookmarkId'];
                    $vNewState = \BtcRelax\Validation\BookmarkValidator::validateStatus($vParams['state']);
                    $vBookmark = $vPM->setNewState($vUser, $vPointId, $vNewState);
                    if ($vBookmark instanceof \BtcRelax\Model\Bookmark) {
                        $result["BookmarkResult"] = true;
                        $result["BookmarkState"] = $vBookmark->getStateInfo();
                    } else {
                        $result["BookmarkError"] = $vPM->getLastError();
                    }
                } else {
                    $this->response("Method SetNewState precondition failed", 412);
                }
                break;
            case'UpdatePoint':
                if (\array_key_exists('bookmarkId', $vParams) && \array_key_exists('author', $vParams) && \array_key_exists('params', $vParams)) {
                    $vAM = \BtcRelax\Core::createAM();
                    $vUser = $vAM->getUserById($vParams['author']);
                    $vPointUpdateParams = \json_decode($vParams['params'], true);
                    $vPointId = (int) $vParams['bookmarkId'];
                    $vBookmark = $vPM->updatePointById($vUser, $vPointId, \array_pop($vPointUpdateParams));
                    if ($vBookmark instanceof \BtcRelax\Model\Bookmark) {
                        $result["BookmarkResult"] = true;
                        $result["BookmarkState"] = ["bookmarkId" => $vBookmark->getIdBookmark(),
                            "bookmarkState" => $vBookmark->getState() , "bookmarkPhotoLink" => $vBookmark->getLink(),
                            "bookmarkLatitude" => $vBookmark->getLatitude(), "bookmarkLongitude" => $vBookmark->getLongitude(),
                            "bookmarkDescription" => $vBookmark->getDescription() ];
                    } else {
                        $result["BookmarkError"] = $vPM->getLastError();
                    }
                } else {
                    $this->response("Method UpdatePoint precondition failed", 412);
                }
                break;
            case'GetPointState':
                if (array_key_exists('bookmarkId', $vParams) && array_key_exists('author', $vParams)) {
                    $vBookmark = $vPM->getBookmarkById($vParams['bookmarkId']);
                    if ($vBookmark instanceof \BtcRelax\Model\Bookmark) {
                        if ($vBookmark->getIdDroper() === $vParams['author']) {
                            $result["BookmarkResult"] = true;
                            $result["BookmarkState"] = $vBookmark->getStateInfo();
                        } else {
                            $this->response("You are not owner", 401);
                        }
                    } else {
                        $result["BookmarkError"] = $vPM->getLastError();
                    }
                } else {
                    $this->response("Method GetPointState precondition failed", 412);
                }
                break;
            case'CreateNewPoint':
                if (\array_key_exists('author', $vParams) && array_key_exists('params', $vParams)) {
                    $vAM = \BtcRelax\Core::createAM();
                    $vUser = $vAM->getUserById($vParams['author']);
                    if (false != $vUser) {
                        $vPointParams = \json_decode($vParams['params'], true);
                        $vBookmark = $vPM->createNewPoint($vUser, \array_pop($vPointParams));
                        if ($vBookmark instanceof \BtcRelax\Model\Bookmark) {
                            $result["BookmarkResult"] = true;
                            $result["BookmarkState"] = $vBookmark->getStateInfo();
                        } else {
                            $result["BookmarkResult"] = false;
                            $result["BookmarkError"] = $vPM->getLastError();
                        }
                    } else {
                        $result["BookmarkResult"] = false;
                        $result["BookmarkError"] = $vAM->getLastError();
                    }
                } else {
                    $this->response("Method CreateNewPoint precondition failed", 412);
                }
                break;
            default:
                \BtcRelax\Log::general(\sprintf("Unknown action:%s.", $vAction), \BtcRelax\Log::ERROR);
                break;
            }
            $this->response($result, 200);
        } else {
            \BtcRelax\Log::general("Cannot determine action!", \BtcRelax\Log::ERROR);
            $this->response('Not Acceptable call!', 406);
        }
    }
}
