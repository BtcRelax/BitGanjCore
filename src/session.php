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
    

    private $sessionId;
    private $dao;
    private $config = [];
    private $sid_name = '';
    private $sessionIp = '';
    private $vars = [];

    function __construct() {
        $this->config = \BtcRelax\Core::getInstance()->getConfig("session");
        $pdo = \BtcRelax\dao\BaseDao::prepareConnection($this->config['DB_HOST'],$this->config['DB_NAME'],$this->config['DB_USER'],$this->config['DB_PASS']); 
        $this->dao = new  \BtcRelax\dao\SessionsDao($pdo);
        $this->sid_name = $this->config['SIDNAME']??"JAHSID";
        // register the new handler
        session_set_save_handler(  
        array($this, "_open"),  
        array($this, "_close"),  
        array($this, "_read"),  
        array($this, "_write"),  
        array($this, "_destroy"),  
        array($this, "_gc")  
        );        
    }
     
    public function _open() {
        if ($this->dao) {
            return true;
        }
        return false;
    }

    public function _close() {
        return $this->dao->getDb()->close();
    }

    public function _read($sid)  {
        return (string) $this->serializePhpSession($this->getVars());
    }

    public function _write($sid, $session_data)  {
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

    public function _destroy($sid) {  return $this->dao->deleteSessionById($sid); }

    public function _gc($max)
    {
//        return $this->dbo->;
    }
    
    
    /// Internal functions 
    
    private function save($name, $value)
    {
        $this->finalizeSaving($name, $value);
    }
    
    private function finalizeSaving($name, $value)
    {
        $data = serialize($value);
        $this->dao->deleteBySid($name);
        //$this->insert($finalName, $finalValue);

        $this->loadSesionVars();
    }
    
    
    private function getVar($varName)
    {
        return $this->vars[$varName];
    }

    private function getVars()
    {
        return $this->VARS;
    }

    private function delete($name)
    {
        //$this->del($nome);
        $this->loadSesionVars();
    }

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


     private function destroySession($sql)
    {

            $this->SQLStatement_DeleteSession->bindParam('sid', $this->sessionId, \PDO::PARAM_STR, $this->sid_len);
            if ($this->SQLStatement_DeleteSession->execute() === false) {
                $check = false;
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
     * Helper function that serialize an object to a string
     * in the Php session format
     *
     * @param mixed object $data Session data (or any object)
     * @return string serialied object
     * @access private
     */
    private function serializePhpSession($data):string
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
  
    private function startSession()    {
        if ($this->newSid()) {
            $vSessionId = $this->getSessionId();
            session_id($vSessionId);
            session_start();
            $_SESSION['SessionState'] = SecureSession::STATUS_UNAUTH;
            \setcookie($this->sid_name, $this->sessionId, \time()+$this->session_duration, "/", '', true, false);
            \session_set_cookie_params(SESS_MAX_DURATION);
            Logger::general(\sprintf('New session with Id:%s was started!', session_id()), Logger::DEBUG);
            return true;
        }
        return false;
    }

    public static function getSessionState()   {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return ($_SESSION['SessionState']);
        } else {
            return \BtcRelax\Session::STATUS_NOT_INIT;
        }
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
