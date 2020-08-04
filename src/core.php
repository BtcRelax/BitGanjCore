<?php
namespace BtcRelax;

use BtcRelax\Exception\NotFoundException;

final class Core
{
    const DEFAULT_PAGE = 'main';
    const PAGE_DIR = '/page/';
    const LAYOUT_DIR = '/layout/';
    const VER = '1.5.18.2';
        
    private static $CLASSES = [
        'BtcRelax\Config' => '/config.php',
        'BtcRelax\Session' => '/session.php',
        'BtcRelax\Flash' => '/flash/flash.php',
        'BtcRelax\Exception\NotFoundException' => '/exception/NotFoundException.php',
        'BtcRelax\Exception\SessionException' => '/exception/SessionException.php',
        'BtcRelax\Exception\AuthentificationCritical' => '/exception/AuthExceptions.php',
        'BtcRelax\Exception\AccessDeniedException' => '/exception/AccessException.php',
        'BtcRelax\Exception\AssignBookmarkException' => '/exception/AssignBookmarkException.php',
        'BtcRelax\Dao\BaseDao' => '/dao/BaseDao.php',
        'BtcRelax\DAO' => '/dao/DAO.php',
        'BtcRelax\Dao\BookmarkDao' => '/dao/BookmarkDao.php',
        'BtcRelax\Dao\BookmarkSearchCriteria' => '/dao/BookmarkSearchCriteria.php',
        'BtcRelax\Dao\OrderDao' => '/dao/OrderDao.php',
        'BtcRelax\Dao\OrderSearchCriteria' => '/dao/OrderSearchCriteria.php',
        'BtcRelax\Dao\CustomerDao' => '/dao/CustomerDao.php',
        'BtcRelax\Dao\CustomerRightsDao' => '/dao/CustomerRightsDao.php',
        'BtcRelax\Dao\CustomerPropertyDao' => '/dao/CustomerPropertyDao.php',
        'BtcRelax\Dao\CustomerSearchCriteria' => '/dao/CustomerSearchCriteria.php',
        'BtcRelax\Dao\IdentifierDao' => '/dao/IdentifierDao.php',
        'BtcRelax\Dao\IdentifierSearchCriteria' => '/dao/IdentifierSearchCriteria.php',
        'BtcRelax\Dao\InvoiceSearchCriteria' => '/dao/InvoiceSearchCriteria.php',
        'BtcRelax\Dao\InvoiceDao' => '/dao/InvoiceDao.php',
        'BtcRelax\Dao\SessionsDao' => '/dao/SessionsDao.php',
        'BtcRelax\Dao\RegionDao' => '/dao/RegionDao.php',
        'BtcRelax\Dao\ProductDao' => '/dao/ProductDao.php',
        'BtcRelax\Mapping\BookmarkMapper' => '/mapping/BookmarkMapper.php',
        'BtcRelax\Mapping\CustomerMapper' => '/mapping/CustomerMapper.php',
        'BtcRelax\Mapping\CustomerRightMapper' => '/mapping/CustomerRightMapper.php',
        'BtcRelax\Mapping\CustomerPropertyMapper' => '/mapping/CustomerPropertyMapper.php',
        'BtcRelax\Mapping\OrderMapper' => '/mapping/OrderMapper.php',
        'BtcRelax\Mapping\InvoiceMapper' => '/mapping/InvoiceMapper.php',
        'BtcRelax\Mapping\IdentifierMapper' => '/mapping/IdentifierMapper.php',
        'BtcRelax\Mapping\ProductMapper' => '/mapping/ProductMapper.php',
        'BtcRelax\Model\Bookmark' => '/model/bookmark.php',
        'BtcRelax\Model\Customer' => '/model/customer.php',
        'BtcRelax\Model\CustomerRight' => '/model/customerRight.php',
        'BtcRelax\Model\CustomerProperty' => '/model/customerProperty.php',
        'BtcRelax\Model\Order' => '/model/order.php',
        'BtcRelax\Model\Invoice' => '/model/invoice.php',
        'BtcRelax\Model\Identicator' => '/model/identicator.php',
        'BtcRelax\Model\PaymentProvider' => '/model/paymentProvider.php',
        'BtcRelax\Model\PaymentProviderEPU' => '/model/PaymentProviderEPU.php',
        'BtcRelax\Model\PaymentProviderBTC' => '/model/PaymentProviderBTC.php',
        'BtcRelax\Model\PaymentProviderBCH' => '/model/PaymentProviderBCH.php',
        'BtcRelax\Model\PaymentProviderKRB' => '/model/PaymentProviderKRB.php',
        'BtcRelax\Model\PaymentProviderXMR' => '/model/PaymentProviderXMR.php',
        'BtcRelax\Model\PaymentProviderKSM' => '/model/PaymentProviderKSM.php',
        'BtcRelax\Model\PaymentProviderMAN' => '/model/PaymentProviderMAN.php',
        'BtcRelax\Model\IdentTelegram' => '/model/identTelegram.php',
        'BtcRelax\Model\IdentEMail' => '/model/identEMail.php',
        'BtcRelax\Model\IdentBitId' => '/model/identBitId.php',
        'BtcRelax\Model\User' => '/model/user.php',
        'BtcRelax\Model\Region' => '/model/region.php',
        'BtcRelax\Model\Product' => '/model/product.php',
        'BtcRelax\Validation\BookmarkValidator' => '/validation/BookmarkValidator.php',
        'BtcRelax\Validation\CustomerValidator' => '/validation/CustomerValidator.php',
        'BtcRelax\Validation\OrderValidator' => '/validation/OrderValidator.php',
        'BtcRelax\Validation\ValidationError' => '/validation/ValidationError.php',
        'BtcRelax\Utils' => '/utils/utils.php',
        'BtcRelax\BitID' => '/BitID.php',
        'BtcRelax\Layout\header' => '/layout/header.php',
        'Geary' => '/external/Geary.php',
        'BtcRelax\Log' => '/logger.php',
        'QRcode' => '/classes/QRcode.php',
        'BtcRelax\HD' => '/HD.php',
        'BtcRelax\APIClient' => '/APIClient.php',
        'BtcRelax\DbSession' => '/classes/DbSession.php'
    ];
    

    private static $instance;
    private static $events = array();

    private $config;
    private $request;
    private $session;
    
 
    
    public static function bindEvent($event, $callback, $obj = null)
    {
        if (!self::$events[$event]) {
            self::$events[$event] = array();
        }
   
        self::$events[$event][] = ($obj === null)  ? $callback : array($obj, $callback);
    }
 
    public static function runEvent($event)
    {
        if (!self::$events[$event]) {
            return;
        }

        foreach (self::$events[$event] as $callback) {
            if (call_user_func($callback) === false) {
                break;
            }
        }
    }
        
    public static function getIstance(): \BtcRelax\Core
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
            self::$instance->init();
        }
        return self::$instance;
    }
        
    public static function getRequest():\BtcRelax\Request
    {
        return self::$request;
    }
    
    public static function getVersion():array
    {
        $vCurrentDateTime = new \DateTime();
        $vDAO = new \BtcRelax\DAO();
        $param = $vDAO->now();
        $param["Core"] = self::VER;
        $param["DBSession"] = \BtcRelax\DbSession::getVersion();
        $param["ServerTime"] =  date_format($vCurrentDateTime, "Y-m-d H:i:s");
        $param["PHP"] = phpversion();
        $param["InstanceId"] = \filter_input(\INPUT_SERVER, "SERVER_NAME");
        return $param;
    }

    private function init()
    {
        \spl_autoload_register([$this, 'loadClass']);
        \set_exception_handler([$this, 'handleException']);
        $this->config = \BtcRelax\Config::getIstance();     
    }

    public function startSession()
    {
        $this->current_session = \BtcRelax\Session::getIstance();
        return $this->current_session->startSession();
    }
     
    
    public static function createApiClient(): \BtcRelax\APIClient
    {
        return new APIClient();
    }

    public static function createAM(): \BtcRelax\AM
    {
        return new AM();
    }
        
    public static function createOM(): \BtcRelax\OM
    {
        return new OM();
    }

    public static function createRE(): \BtcRelax\RE
    {
        return new RE();
    }
        
    public static function createPM(): \BtcRelax\PM
    {
        return new PM();
    }
       
    public static function getCurrentUser(): \BtcRelax\Model\User
    {
        $vAM = self::createAM();
        return $vAM->getUser();
    }
        
    public function getCurrentSession(): \BtcRelax\Session
    {
        if ($this->current_session instanceof \BtcRelax\Session) {
            return $this->current_session;
        } else {
            \BtcRelax\Log::general("Incorrect session instance inside core!", \BtcRelax\Log::FATAL);
        }
    }
            
    public function getDefaultPage()
    {
        switch (\BtcRelax\Session::getSessionState()) {
            case Session::STATUS_GUEST:
                $result_page = 'guest';
                break;
            case Session::STATUS_USER:
            case Session::STATUS_ROOT:
                $result_page = 'user';
                break;
            case Session::STATUS_BANNED:
                $result_page = 'banned';
                break;
            default:
                $result_page = 'main';
               break;
            }
        return $result_page;
    }
     
    public function run()
    {
        if (\property_exists($this->request, "controller")) {
        } else {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            $this->runPage($this->getPage());
        }
        //if ($this->getRequest()->isCanAcceptHtml()) {
            
        //else {  $cClassName = $this->getRequest()->getApiClassName();
        //        if (!empty($cClassName) && $this->loadClass($cClassName)) {
        //            $vController = new $cClassName ;  $vController->processApi(); }
        //        else { \BtcRelax\API::response('Controller not found',503); }
        //    }
    }
    
    public function handleException($ex)
    {
        \BtcRelax\Log::general($ex, \BtcRelax\Log::ERROR);
        switch ($ex) {
                    case $ex instanceof NotFoundException:
            \header('HTTP/1.0 404 Not Found');
            $this->runPage('404', $ex->getMessage());
                        break;
                    case $ex instanceof \BtcRelax\Exception\SessionException:
                        \header('HTTP/1.0 440 Session expired');
                        $this->runPage('main', $ex->getMessage());
                        break;
                    default:
                        \header('HTTP/1.1 500 Internal Server Error');
            $this->runPage('500', $ex->getMessage());
                        break;
                }
    }

    public function loadClass($name):bool
    {
        if (\class_exists($name)) {
            return true;
        }
        \BtcRelax\Log::general(\sprintf("Loading class: %s", $name), \BtcRelax\Log::DEBUG);
        if (array_key_exists($name, self::$CLASSES)) {
            require_once __DIR__ . self::$CLASSES[$name];
        } else {
            if (!$this->tryToAutoload($name)) {
                require_once __DIR__ . '/vendor/autoload.php';
            } else {
                return true;
            }
        }
        return \class_exists($name);
    }

    private function getPage($extraParams = null)
    {
        if (\property_exists($extraParams, 'page')) {
            $page = $extraParams['page'];
        } else {
            $page = $this->GetDefaultPage();
        }
        return $this->checkPage($page);
    }

    private function checkPage(string  $page)
    {
        if (!preg_match('/^[a-z0-9-]+$/i', $page)) {
            Log::general(\sprintf("Try to request unsafe page:", $page), Log::WARN);
            throw new \BtcRelax\Exception\NotFoundException('Unsafe page "' . $page . '" requested');
        }
        if (!$this->hasScript($page) && !$this->hasTemplate($page)) {
            Log::general(\sprintf("Try to request not existent page:", $page), Log::WARN);
            throw new \BtcRelax\Exception\NotFoundException('Page "' . $page . '" not found');
        }
        return $page;
    }

    private function runPage(string $page = null )
    {
        if (empty($page)) { $page = $this->GetDefaultPage();  };
        \BtcRelax\Log::general(\sprintf('Try to load page:%s called with method:%s', $page, \BtcRelax\Utils::getRequestMethod()), \BtcRelax\Log::INFO);
        $run = false;
        if ($this->hasScript($page)) {
            $run = true;
            $script = $this->getScript($page);
            \BtcRelax\Log::general(\sprintf('Loading script:%s', $script), \BtcRelax\Log::INFO);
            require $script;
        }
        if ($this->hasTemplate($page)) {
            $run = true;
            //header(\sprintf('page:%s',$page));
            // data for main template
            \BtcRelax\Log::general(\sprintf('Loading template for page:%s', $page), \BtcRelax\Log::INFO);
            $template = $this->getTemplate($page);
            //			$flashes = null;
            //			if (Flash::hasFlashes()) {
            //				$flashes = Flash::getFlashes();
            //			}
            // main template (layout)
            $header = $this->getHeader();
            require \filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT') . self::LAYOUT_DIR . 'index.phtml';
        }
        if (!$run) {
            die('Page "' . $page . '" has neither script nor template!');
        }
    }

    private function getScript($page)
    {
        //return filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT') . self::PAGE_DIR . $page . '.php';
        return self::PAGE_DIR . $page . '.php';
    }

    private function getTemplate($page)
    {
        return  self::PAGE_DIR . $page . '.phtml';
    }
    
    private function getHeader()
    {
        $vState = \BtcRelax\Session::getSessionState();
        if (($vState === Session::STATUS_USER) || ($vState === Session::STATUS_ROOT)
                    || ($vState === Session::STATUS_BANNED)) {
            return new \BtcRelax\Layout\header();
        }
    }
                
    private function hasScript($page)
    {
        return file_exists($this->getScript($page));
    }

    private function hasTemplate($page)
    {
        return file_exists($this->getTemplate($page));
    }

    public function tryToAutoload($f)
    {
        $result = false;
        $base = dirname(__FILE__) . "/";
        $interfaceFile = $base . "classes/interface/" . $f . "Interface.php";
        if (file_exists($interfaceFile)) {
            require_once $interfaceFile;
        }
        $classFile = $base . "classes/" . $f . ".php";
        if (file_exists($classFile)) {
            require_once $classFile;
            $result = true;
        }
        $fpath = explode("\\", $f);
        if ((!$result) && (count($fpath)>1)) {
            $lf = $fpath[count($fpath)-1];
            $result = $this->tryToAutoload($lf);
        }
        return $result;
    }
}
