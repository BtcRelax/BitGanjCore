<?php
namespace BtcRelax;

use BtcRelax\Dao\SessionsDao;

final class Session extends DbSession
{
    const STATUS_NOT_INIT = "NOT_INITIALIZED";
    const STATUS_UNAUTH = "UNAUTHENTICATED";
    const STATUS_AUTH_PROCESS = "AUTH_PROCESS";
    const STATUS_GUEST = "GUEST";
    const STATUS_USER = "USER";
    const STATUS_ROOT = "ROOT";
    const STATUS_BANNED = "BANNED";
    
    
    private static $instance;
    
    private function __construct()
    {
    }

    private function __clone()
    {
        trigger_error("Clonig not allowed");
    }
    
    
    public static function getIstance(): \BtcRelax\Session
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
            self::$instance->init();
        }
        return self::$instance;
    }
    
    public function init()
    {
        //print_r($config);
        //$this->_MYSESSION_CONF=$config;

        $this->db_type = 'mysql';
        $this->db_name = DB_NAME;
        $this->db_pass = DB_PASS;
        $this->db_server = DB_HOST;
        $this->db_username = DB_USER;

        
        $this->table_name_session = 'Sessions';
        $this->table_name_variable = 'SessionVars';
        $this->table_column_sid = 'sid';
        $this->table_column_name = 'name';
        $this->table_column_value = 'value';
        $this->table_column_fexp = 'forced_expires';
        $this->table_column_ua = 'ua';
        $this->table_column_exp = 'expires';

        $this->sid_len = 10;
        $this->session_duration = intval(SESS_DURATION);
        $this->session_max_duration = intval(SESS_MAX_DURATION);
        $this->encrypt_data = false;
        $this->encrypt_key = "godjah";
        $this->dbConnection();
            
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

    
   /**
     * Read actual sessionId or create a new one
     *
     * @access private
     */
    protected function readSessionId()
    {
        if (isset($_COOKIE[SIDNAME])) { //there some jam in the cookie
            $this->sessionId=$_COOKIE[SIDNAME];
            //check if the jam can be eated
            if ($this->checkSessionId()) {
                $num = $this->getSidCount($this->sessionId);
                if ($num === 1) {
                    $this->loadSesionVars();
                } else {
                    trigger_error(\sprintf("Session id:%s not fount in db.", $this->sessionId), E_USER_ERROR);
                }
                //setcookie (SIDNAME, $this->sessionId,time()+$this->session_duration,"/",'',false,true);
            } else {
                $this->destroySession(false);
                trigger_error("Coockie has session id but UA changed!", E_USER_ERROR);
            }
        } else {
            trigger_error("Coockie has not session id.", E_USER_ERROR);
        }
    }    
    
    public static function getSessionsCount()
    {
        $result = 0;
        if (!empty($this->dbsession)) {
            $vSessions = $this->dbsession->getGlobalSessionsInfo();
            $result = count($vSessions);
        }
        return $result;
    }

    public static function getSessionsInfo()
    {
        $vSessDao = new SessionsDao();
        $result = $vSessDao->getSessions(session_id());
        return $result;
    }
        
    public function startSession()
    {
        if ($this->newSid()) {
            $vSessionId = $this->getSessionId();
            session_id($vSessionId);
            session_start();
            $_SESSION['start_time'] = time();
            $_SESSION['last_active'] = time();
            $_SESSION['SessionState'] = SecureSession::STATUS_UNAUTH;
            \setcookie($this->sid_name, $this->sessionId, \time()+$this->session_duration, "/", '', true, false);
            \session_set_cookie_params(SESS_MAX_DURATION);
            Log::general(\sprintf('New session with Id:%s was started!', session_id()), Log::DEBUG);
            return true;
        }
        return false;
    }

    public static function getSessionState()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return ($_SESSION['SessionState']);
        } else {
            return SecureSession::STATUS_NOT_INIT;
        }
    }
   
    public static function getSessionLifetime():int
    {
        return self::SessionCheck()? $_SESSION['last_active'] - $_SESSION['start_time'] : 0 ;
    }
    
    private static function SessionCheck():bool
    {
        $result = self::isSessionStarted();
        if (($result) && isset($_SESSION['last_active'])) {
            if (time() < ($_SESSION['last_active'] + intval(SESS_DURATION))) {
                $result = true;
            } else {
                $result = false;
                //throw new SessionException(\sprintf("Session id:%s was expired!",\session_id()));
            }
        }
        return $result;
    }

    public static function isSessionStarted():bool
    {
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                return session_status() === PHP_SESSION_ACTIVE ? true : false;
            } else {
                return session_id() === '' ? false : true;
            }
        }
        return false;
    }

    public static function setValue($session, $value)
    {
        if (is_string($value)) {
            Log::general(\sprintf("Session id:%s saved value:%s for key:%s", session_id(), $value, $session), Log::DEBUG);
        } else {
            Log::general(\sprintf("Session id:%s saved value of type:%s for key:%s", \session_id(), \gettype($value), $session), Log::DEBUG);
        }
        if (SecureSession::isSessionStarted()) {
            $_SESSION[$session] = $value;
            $_SESSION['last_active'] = time();
        }
    }


    /// Getting value by name
    /// If not found, result false
    /// new SessionNotInitializedException
    public static function getValue($session)
    {
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
        } else {
            throw  new \BtcRelax\SessionNotInitializedException();
        }
    }

    public static function clearValue($session)
    {
        parent::delete($session);
        unset($_SESSION[$session]);
        Log::general(\sprintf("Session id:%s cleared key:%s", session_id(), $session), Log::DEBUG);
        return true;
    }

    public static function killSession()
    {
        $_SESSION = array();
        if (isset($_COOKIE)):
                \setcookie(session_name(), '', time() - 7000000, '/');
        endif;
        \session_destroy();
        return;
    }

    public static function allStatuses()
    {
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
