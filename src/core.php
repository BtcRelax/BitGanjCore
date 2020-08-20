<?php  
namespace BtcRelax;

use BtcRelax\Exception\NotFoundException;

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

    private static array $events = [];
    private static ?\BtcRelax\Core $instance = null;
    private ?\Symfony\Component\HttpFoundation\Request $request = null;
    private ?\Symfony\Component\HttpFoundation\Response $response = null;
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
    
    public static function getRequest(): ?\Symfony\Component\HttpFoundation\Request {
        return self::getInstance()->request;
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
        if ($this->request->query->has('controller')) {
            $this->response = new \Symfony\Component\HttpFoundation\Response();
        } else {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            $this->runPage($this->getPage());
        }
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
        if (\class_exists($name)) { return true; }
        if (array_key_exists($name, self::$CLASSES)) { require_once __DIR__ . self::$CLASSES[$name]; }
        return \class_exists($name);
    }

    private function getPage()
    {
        $page = $this->request->query->has('page')?$this->request->query->get('page') : $this->getDefaultPage();
        return $this->checkPage($page);
    }

    private function checkPage(string  $page)
    {
        if (!preg_match('/^[a-z0-9-]+$/i', $page)) {
            throw new \BtcRelax\Exception\NotFoundException(\sprintf("Page %s has worng symbols in name ", $page ));
        }
        if (!$this->hasScript($page) && !$this->hasTemplate($page)) {
            throw new \BtcRelax\Exception\NotFoundException(\sprintf("Page with name %s not found", $page));
        }
        return $page;
    }

    private function runPage(string $page )
    {
        if ($this->hasScript($page)) {
            $script = $this->getScript($page);
            include $script;
        }
        if ($this->hasTemplate($page)) {
            $template = $this->getTemplate($page);
            include __DIR__ . self::LAYOUT_DIR . 'index.phtml';
        }
    }

    private function getScript($page)
    {
       return __DIR__ . self::PAGE_DIR . $page . '.php';
    }

    private function getTemplate($page)
    {
        return __DIR__ . self::PAGE_DIR . $page . '.phtml';
    }
             
    private function hasScript($page)
    {
        return file_exists($this->getScript($page));
    }

    private function hasTemplate($page)
    {
        return file_exists($this->getTemplate($page));
    }

}

