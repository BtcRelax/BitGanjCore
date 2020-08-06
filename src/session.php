<?php
namespace BtcRelax;

final class Session 
{
    const STATUS_NOT_INIT = "NOT_INITIALIZED";
    const STATUS_UNAUTH = "UNAUTHENTICATED";
    const STATUS_AUTH_PROCESS = "AUTH_PROCESS";
    const STATUS_GUEST = "GUEST";
    const STATUS_USER = "USER";
    const STATUS_ROOT = "ROOT";
    const STATUS_BANNED = "BANNED";
    

    public $VARS = [];
    private $sessionId;
    private $dao;
    private $config = [];
    private $sid_name = '';
    private $sessionIp = '';
    

    function __construct() {
        $this->config = \BtcRelax\Core::getIstance()->getConfig("session");
        $pdo = \BtcRelax\dao\BaseDao::prepareConnection($this->config['DB_HOST'],$this->config['DB_NAME'],$this->config['DB_USER'],$this->config['DB_PASS']); 
        $this->dao = new  \BtcRelax\dao\SessionsDao($pdo);
        // register the new handler
        session_set_save_handler(
            array(&$this, 'open'),
            array(&$this, 'close'),
            array(&$this, 'read'),
            array(&$this, 'write'),
            array(&$this, 'destroy'),
            array(&$this, 'gc')
        );        
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

    /**
     * Get a stored var
     *
     * @param string $name Variable name
     * @return object Store variable
     */
    public function getVar($varName)
    {
        return $this->VARS[$varName];
    }

    /**
     * Get all stored vars
     *
     * @return object All stored vars
     */
    public function getVars()
    {
        return $this->VARS;
    }
 
    /**
     * Save a variable into the session
     *
     * @access public
     *
     * @param string $nome The name of the session variable
     * @param string $valore The value of the session variable.
     */
    public function save($nome, $valore)
    {
        $this->finalizeSaving($nome, $valore);
    }

    /**
     * Register a variable into the session
     *
     * @access public
     *
     * @param object $nome The variable to save. This variable is saved into the
     *                     session array with the name of the saved variable
     *
     * @example $myVars = "fooo";<br>
     *          $this->register($myVars);<br>
     *          <br>
     *          The vars array will be: $this->VARS["myVars"] = "fooo";
     */
    public function register(&$nome)
    {
        $this->finalizeSaving($this->varName($nome), $nome);
    }

    /**
     * Execute the real saving procedure, insert or update the session value
     *
     * @access private
     * @param string $finalName The name of the variable
     * @param string $finalValue The value of the saved variable
     */
    private function finalizeSaving($finalName, $finalValue)
    {
        $finalValue = serialize($finalValue);

        //$this->del($finalName);
        //$this->insert($finalName, $finalValue);

        $this->loadSesionVars();
    }

    /**
     * Delete a variable from the session
     *
     * @access public
     *
     * @param string $nome Variable name
     */
    public function delete($nome)
    {
        //$this->del($nome);
        $this->loadSesionVars();
    }


    /* PRIVATE METHOD */


    /**
     * Check if session must expire due to "MAX_DURATA"
     *
     * @access private

     * @return boolean: true if session must expire | false if session can continue
     */
    private function expiredSession()
    {
        if (time()>$this->forcedExpire) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Load session vars to the VARS array
     *
     * @access private
     */
    private function loadSesionVars()
    {
        $this->VARS = array();

        $this->updateSessionExpireTime();

        $dati = $this->selectSessionVars();

        foreach ($dati as $infos) {
            $this->VARS[$infos["nome"]]=unserialize($infos["valore"]);
        }
    }

    /**
     * Check if the session id found is able ti be used
     *
     * @return boolean True if the session Id is ok, False if not
     */


    /**
     * Generate a new unique session id
     *
     * @access protected
     * @return bool True if session insert, false elsewhere
     */
    protected function newSid()
    {
        $this->sessionId= \BtcRelax\Utils::generateNonce($this->sid_len);
        $this->forcedExpire = time()+ $this->session_max_duration;
        $expireTime = time() + $this->session_duration;
        $vUaLen = 40;
        $vUA = $this->getUa();
        $this->setSessionIp();
                    
        $this->SQLStatement_InsertSession->bindParam(':expires', $expireTime, \PDO::PARAM_INT);
        $this->SQLStatement_InsertSession->bindParam(':forcedExpires', $this->forcedExpire, \PDO::PARAM_INT);
        $this->SQLStatement_InsertSession->bindParam(':sid', $this->sessionId, \PDO::PARAM_STR);
        $this->SQLStatement_InsertSession->bindParam(':ua', $vUA, \PDO::PARAM_STR, $vUaLen);
        $this->SQLStatement_InsertSession->bindParam(':netinfo', $this->getSessionIp(), \PDO::PARAM_STR, 45);
        return $this->SQLStatement_InsertSession->execute();
    }


    
    /**
     * Setting up random seed random generator
     *
     * @access private
     * @return float
     */
    private function makeSeed()
    {
        list($usec, $sec) = explode(' ', microtime());
        return (float) $sec + ((float) $usec * 100000);
    }


    /**
     * Retrive the name of a variable, giving the varaible as argument
     *
     * @access private
     * @param mixed type $var The variable
     * @param boolean $scope The Scope
     * @return string The variable name
     *
     * @example $this->varName($mySuperVariable);<br>
     *          return: "mySuperVariable"
     */
    private function varName(&$var, $scope=0)
    {
        $old = $var;
        if (($key = array_search($var = 'unique'.rand().'value', !$scope ? $GLOBALS : $scope)) && $var = $old) {
            return $key;
        }
    }


    /**
     * Destroy session
     * Delete variable from database and free resources
     *
     * @access private
     * @param boolean $sql True if delete both resource and database rows. False to keep database rows
     * @return boolen true if all is ok, false elsewhere
     */
    private function destroySession($sql)
    {
        $check = true;

        if ($sql) {
            $this->SQLStatement_DeleteSession->bindParam('sid', $this->sessionId, \PDO::PARAM_STR, $this->sid_len);
            if ($this->SQLStatement_DeleteSession->execute() === false) {
                $check = false;
            }
        }

        if (setcookie('SIDNAME', $this->sessionId, time() - 3600, "/", '', false, true) === false) {
            $check = false;
        }
            
        unset($_REQUEST['SIDNAME']);
        unset($_POST['SIDNAME']);
        unset($_GET['SIDNAME']);

        return $check;
    }

    /**
     * Setting up the class, reading
     * an existing session, check if a session
     * is expired.

    
    

    //--------------------OVERWRITED FUNCTION

    /**
     *  Our open() function
     *
     *  @access private
     */
    public function open($save_path, $session_name)
    {
        return true;
    }

    /**
     *  Our close() function
     *
     *  @access private
     */
    public function close()
    {
        return true;
    }

    /**
     *  Our read() function
     *
     *  @access private
     */
    public function read($session_id)
    {
        return (string) $this->serializePhpSession($this->getVars());
    }

    /**
     *  Our write() function
     *
     *  @access private
     */
    public function write($session_id, $session_data)
    {
        //$myData = $this->unserializePhpSession(base64_decode($session_data));
        $session_data_prepared = \preg_replace_callback('!s:(\d+):"(.*?)";!', function ($m) {
            return 's:'. \strlen($m[2]).':"'.$m[2].'";';
        }, $session_data);
        $myData = $this->unserializePhpSession($session_data_prepared);
        foreach ($myData as $name => $value) {
            $this->save($name, $value);
        }
        return true;
    }

    /**
     * Helper function that serialize an object to a string
     * in the Php session format
     *
     * @param mixed object $data Session data (or any object)
     * @return string serialied object
     * @access private
     */
    private function serializePhpSession($data)
    {
        $serialized = '';

        foreach ($this->getVars() as $key => $value) {
            $serialized .= $key . "|" . serialize($value);
        }

        return (string) $serialized;
    }

    /**
     * Helper function that unserialize PHP Session Data string
     *
     * @param string $data Sessione serialized data
     * @return object
     * @access private
     */
    private function unserializePhpSession($data)
    {
        if (strlen($data) == 0) {
            return array();
        }

        // match all the session keys and offsets
        \preg_match_all('/(^|;|\})([a-zA-Z0-9_]+)\|/i', $data, $matchesarray, PREG_OFFSET_CAPTURE);

        $returnArray = array();

        $lastOffset = null;
        $currentKey = '';
        foreach ($matchesarray[2] as $value) {
            $offset = $value[1];
            if (!is_null($lastOffset)) {
                $valueText = substr($data, $lastOffset, $offset - $lastOffset);
                $returnArray[$currentKey] = unserialize($valueText);
            }
            $currentKey = $value[0];

            $lastOffset = $offset + strlen($currentKey)+1;
        }

        $valueText = substr($data, $lastOffset);
        $returnArray[$currentKey] = unserialize($valueText);

        return $returnArray;
    }

    /**
     *  Our destroy() function
     *
     *  @access private
     */
    public function destroy($session_id)
    {
        return $this->destroySession(true);
    }

    /**
     *  Our gc() function (garbage collector)
     *
     *  @access private
     */
    public function gc()
    {
        \BtcRelax\Logger::general("Garbage collector working", \BtcRelax\Logger::INFO);
        $time = time() - $this->session_max_duration;
        $this->SQLStatement_DeleteExpiredSession->bindParam('time', $time, \PDO::PARAM_INT);
        if ($this->SQLStatement_DeleteExpiredSession->execute()===false) {
            trigger_error("Somenthing goes wrong with the garbace collector", E_USER_ERROR);
        } else {
            return true;
        }
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
               trigger_error( "Coockie has session id but UA changed!", E_USER_ERROR);
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
            Logger::general(\sprintf('New session with Id:%s was started!', session_id()), Logger::DEBUG);
            return true;
        }
        return false;
    }

    public static function getSessionState()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return ($_SESSION['SessionState']);
        } else {
            return \BtcRelax\Session::STATUS_NOT_INIT;
        }
    }
   
    public static function getSessionLifetime():int
    {
        return self::SessionCheck()? $_SESSION['last_active'] - $_SESSION['start_time'] : 0 ;
    }
    
    private static function SessionCheck()
    {
        $result = \BtcRelax\Session::isSessionStarted();
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



    public static function clearValue($session)
    {
        self::delete($session);
        unset($_SESSION[$session]);
        \BtcRelax\Logger::general(\sprintf("Session id:%s cleared key:%s", session_id(), $session), \BtcRelax\Logger::DEBUG);
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
