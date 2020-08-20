<?php  
namespace BtcRelax;

use BtcRelax\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;

final class Core
{
    const PAGE_DIR = '/page/';
    const LAYOUT_DIR = '/layout/';
    const VER = '1.5.18.2';
        
    private static $CLASSES = [
        'BtcRelax\Config' => '/config.php',
        'BtcRelax\Session' => '/session.php',
        'BtcRelax\Logger' => '/logger.php',
    ];

    private static $events = array();
    private static ?Core $instance = null;
    private $request = null;
    private ?\BtcRelax\Config $config = null;
    private ?\BtcRelax\Session $session = null;
    
    /// Static methods
        
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
    
    public static function getInstance(): \BtcRelax\Core
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
            self::$instance->init();
        }
        return self::$instance;
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
      
    // Public
    public function getConfig(string $ConfigName):array
    {
        return  $this->config->getConfig($ConfigName);
    }

    public function getCurrentSession(): \BtcRelax\Session
    {
        return $this->session;
    }

    // Private methods
    private function init()
    {
        \spl_autoload_register([$this, 'loadClass']);
        \set_exception_handler([$this, 'handleException']);
        $this->config = new \BtcRelax\Config();
        $this->session = new \BtcRelax\Session();
    }
    
    private function __construct()
    {
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
        trigger_error("Clonig not allowed");
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    private function __wakeup()
    {
    }
                
    private function getDefaultPage()
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
     
    public function run( \Symfony\Component\HttpFoundation\Request $request = null  )
    {
        $this->request = $request ?? \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        if (\property_exists($this->request->query->parameters , "controller")) {
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
        \BtcRelax\Logger::general($ex, \BtcRelax\Logger::ERROR);
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
        \BtcRelax\Logger::general(\sprintf("Loading class: %s", $name), \BtcRelax\Logger::DEBUG);
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
            \BtcRelax\Logger::general(\sprintf("Try to request unsafe page:", $page), \BtcRelax\Logger::WARN);
            throw new \BtcRelax\Exception\NotFoundException('Unsafe page "' . $page . '" requested');
        }
        if (!$this->hasScript($page) && !$this->hasTemplate($page)) {
            \BtcRelax\Logger::general(\sprintf("Try to request not existent page:", $page), \BtcRelax\Logger::WARN);
            throw new \BtcRelax\Exception\NotFoundException('Page "' . $page . '" not found');
        }
        return $page;
    }

    private function runPage(string $page = null )
    {
        if (empty($page)) { $page = $this->GetDefaultPage();  };
        \BtcRelax\Logger::general(\sprintf('Try to load page:%s called with method:%s', $page, \BtcRelax\Utils::getRequestMethod()), \BtcRelax\Logger::INFO);
        $run = false;
        if ($this->hasScript($page)) {
            $run = true;
            $script = $this->getScript($page);
            \BtcRelax\Logger::general(\sprintf('Loading script:%s', $script), \BtcRelax\Logger::INFO);
            require $script;
        }
        if ($this->hasTemplate($page)) {
            $run = true;
            //header(\sprintf('page:%s',$page));
            // data for main template
            \BtcRelax\Logger::general(\sprintf('Loading template for page:%s', $page), \BtcRelax\Logger::INFO);
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
             
    private function hasScript($page)
    {
        return file_exists($this->getScript($page));
    }

    private function hasTemplate($page)
    {
        return file_exists($this->getTemplate($page));
    }

    private function tryToAutoload($f)
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

