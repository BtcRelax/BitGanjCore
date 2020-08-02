<?php
namespace BtcRelax;

use BtcRelax\Dao\SessionsDao;

final class SecureSession  extends DbSession {

    const STATUS_NOT_INIT = "NOT_INITIALIZED";
    const STATUS_UNAUTH = "UNAUTHENTICATED";
    const STATUS_AUTH_PROCESS = "AUTH_PROCESS";
    const STATUS_GUEST = "GUEST";
    const STATUS_USER = "USER";
    const STATUS_ROOT = "ROOT";
    const STATUS_BANNED = "BANNED";
    
    
    private static $instance;
    
    private function __construct() {

    }

    private function  __clone() {
        trigger_error("Clonig not allowed");
    }
    
    
    public static function getIstance(): \BtcRelax\SecureSession
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
            self::$instance->setUp();
        }
        return self::$instance;
    }
    
    public function  init() {
        parent::readSessionId();
        //check if i have to overwrite php
        //yes.. i'm the best so i overwrite php function
            //Make sure session cookies expire when we want it to expires
        ini_set('session.cookie_lifetime', $this->session_duration);            
            //set the value of the garbage collector
        ini_set('session.gc_maxlifetime', $this->session_max_duration);
            // set the session name to our fantastic name
        ini_set('session.name', SIDNAME);                        

            // register the new handler
        session_set_save_handler(
                array(&$this, 'open'),
                array(&$this, 'close'),
                array(&$this, 'read'),
                array(&$this, 'write'),
                array(&$this, 'destroy'),
                array(&$this, 'gc')
            );
        // start the session and cross finger
        $vSessionId = $this->getSessionId();
        session_id($vSessionId);
        session_start();
    }

    
    public static function getSessionsCount() {
        $result = 0;
        if (!empty($this->dbsession))
        {
            $vSessions = $this->dbsession->getGlobalSessionsInfo();
            $result = count($vSessions);
        }
        return $result;
    }

    public static function getSessionsInfo() {
        $vSessDao = new SessionsDao();
        $result = $vSessDao->getSessions(session_id());
        return $result;
    }
        
    public function startSession(){
        if ($this->newSid()) {
                $vSessionId = $this->getSessionId();
                session_id($vSessionId);
                session_start();
                $_SESSION['start_time'] = time();
                $_SESSION['last_active'] = time();
                $_SESSION['SessionState'] = SecureSession::STATUS_UNAUTH;
                \setcookie ($this->sid_name, $this->sessionId, \time()+$this->session_duration,"/",'',true,false);
                \session_set_cookie_params(SESS_MAX_DURATION);
                Log::general(\sprintf('New session with Id:%s was started!', session_id()), Log::DEBUG);
                return true;
            }
        return false;
    }

    public static function getSessionState() { 
       if (session_status() === PHP_SESSION_ACTIVE) {
        return ($_SESSION['SessionState']);} 
       else { return SecureSession::STATUS_NOT_INIT;}
    }
   
    public static function getSessionLifetime():int {
            return self::SessionCheck()? $_SESSION['last_active'] - $_SESSION['start_time'] : 0 ;
    }
    
    private static function SessionCheck():bool {
        $result = self::isSessionStarted();
        if (($result) && isset($_SESSION['last_active'])) 
        {   if (time() < ($_SESSION['last_active'] + intval(SESS_DURATION) )) {
                $result = true;
            } else {
                $result = false;
                //throw new SessionException(\sprintf("Session id:%s was expired!",\session_id()));
            }
        }
        return $result;
    }

    public static function isSessionStarted():bool {
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            } else {
                return session_id() === '' ? FALSE : TRUE;
            }
        }
        return FALSE;
    }

    public static function setValue($session, $value) {
        if (is_string($value))
        {
            Log::general(\sprintf("Session id:%s saved value:%s for key:%s",session_id(),$value,$session ),Log::DEBUG);
        }
        else {
            Log::general(\sprintf("Session id:%s saved value of type:%s for key:%s", \session_id(), \gettype($value),$session ),Log::DEBUG);
        }
        if (SecureSession::isSessionStarted()) {
            $_SESSION[$session] = $value;
            $_SESSION['last_active'] = time();
        }
    }


    /// Getting value by name
    /// If not found, result false
    /// new SessionNotInitializedException
    public static function getValue($session) {
        if (self::isSessionStarted()) {
            if (self::SessionCheck()) {
                    $_SESSION['last_active'] = time();
                    return $_SESSION[$session];
                } else {
                    //error_log();
                    //$vIdSess = session_id();
                    Log::general(\sprintf("Session id:%s expire, and will be killed", \session_id()), Log::INFO);
                   self::killSession();
                }
        } else { throw  new \BtcRelax\SessionNotInitializedException(); }
    }

    public static function clearValue($session) {
        parent::delete($session);
        unset($_SESSION[$session]);
        Log::general(\sprintf("Session id:%s cleared key:%s",session_id(),$session ),Log::DEBUG);
        return true;        
    }

    public static function killSession() {
        $_SESSION = array();
            if (isset($_COOKIE)):
                \setcookie(session_name(), '', time() - 7000000, '/');
            endif;
            \session_destroy();
        return;
    }

    public static function allStatuses() {
        return [
            self::STATUS_NOT_INIT,
            self::STATUS_AUTH_PROCESS,
            self::STATUS_UNAUTH,
            self::STATUS_GUEST,
            self::STATUS_USER,
            self::STATUS_ROOT,
            self::STATUS_BANNED
        ];
    }

}
