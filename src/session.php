<?php
namespace BtcRelax;

class Session 
{
    const STATUS_NOT_INIT = "NOT_INITIALIZED";
    const STATUS_UNAUTH = "UNAUTHENTICATED";
    const STATUS_AUTH_PROCESS = "AUTH_PROCESS";
    const STATUS_GUEST = "GUEST";
    const STATUS_USER = "USER";
    const STATUS_ROOT = "ROOT";
    const STATUS_BANNED = "BANNED";
  
    private $sessionId = null;
    private $sessionDuration = 0;
    private $sessionMaxDuration = 0;
    private $dao;
    private $config = [];
    private $sid_name = null;
    private $vars = [];

    function __construct() {
        $this->config = \BtcRelax\Core::getInstance()->getConfig("session");
        $pdo = \BtcRelax\Dao\BaseDao::prepareConnection($this->config['DB_HOST'],$this->config['DB_NAME'],$this->config['DB_USER'],$this->config['DB_PASS']); 
        $this->dao = new  \BtcRelax\Dao\SessionsDao($pdo);
        $this->sid_name = $this->config['SIDNAME']??"JAHSID";
        $this->sessionDuration = $this->config['SESS_DURATION']??1800;
        $this->sessionMaxDuration = $this->config['SESS_MAX_DURATION']??3600; 
        session_set_save_handler(
		array($this, "open"),
		array($this, "close"),
		array($this, "read"),
		array($this, "write"),
		array($this, "destroy"),
		array($this, "gc")
		);
    }
   
    public function  getSessionId() {
        return $this->sessionId;
    }

    public function getSessionDuration(): int {
        return $this->sessionDuration;
    }

    public function getSessionMaxDuration(): int {
        return $this->sessionMaxDuration;
    }
    
    public function getExpireSession(){
        return time() + $this->getSessionDuration();
    }
    
    public function getForcedExpireSession(){
        return time() + $this->getSessionMaxDuration();
    }
    
    public function getUserAgent() {
        return \BtcRelax\Utils::getUserAgent();
    }
    
    public function getUserIP() {
        return \BtcRelax\Core::getRequest()->getClientIp();
    }
    
    public function getCurrentServer() {
        return \BtcRelax\Core::getRequest()->getHttpHost();
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
        return $this->vars;
    }

    private function expiredSession()
    {
        if (time()>$this->forcedExpire) {
            return true;
        } else {
            return false;
        }
    }

    private function loadSesionVars()
    {
        $this->updateSessionExpireTime();
        $data = $this->selectSessionVars();
        foreach ($data as $infos) {
            $this->vars[$infos["name"]]=unserialize($infos["value"]);
        }
    }

    private function newSid()
    {
        $this->sessionId = \BtcRelax\Utils::generateNonce($this->sid_len);
        return $this->dao->insertSession($this);
    }

    private function varName(&$var, $scope=0)
    {
        $old = $var;
        if (($key = array_search($var = 'unique'.rand().'value', !$scope ? $GLOBALS : $scope)) && $var = $old) {
            return $key;
        }
    }


     private function destroySession($sql)
    {
    }

    private function serializePhpSession($data):string
    {
        $serialized = '';
        foreach ($this->getVars() as $key => $value) {
            $serialized .= $key . "|" . serialize($value);
        }
        return (string) $serialized;
    }

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

    public function close(): bool {
		// Close the database connection
		// If successful
		if($this->db->close()){
		// Return True
		return true;
		}
		// Return False
		return false;
    }

    public function destroy(string $session_id): bool {
        return $this->dao->deleteSessionById($session_id); 
    }

    public function gc(int $maxlifetime): int {
        return $this->dao->deleteExpiredSession(); 
    }

    public function open(string $save_path, string $session_name): bool {
		// If successful
		if($this->dao->getDb()){
		// Return True
		return true;
		}
		// Return False
		return false;
    }

    public function read(string $session_id): string {
//        		// Set query
//		$this->db->query('SELECT data FROM sessions WHERE id = :id');
//		// Bind the Id
//		$this->db->bind(':id', $id);
//		// Attempt execution
//		// If successful
//		if($this->db->execute()){
//		// Save returned row
//		$row = $this->db->single();
//		// Return the data
//		return $row['data'];
//		}else{
//		// Return an empty string
//		return '';
//		}
        return (string) $this->serializePhpSession($this->getVars());
    }

    public function write(string $session_id, string $session_data): bool {
//        		// Create time stamp
//		$access = time();
//		// Set query  
//		$this->db->query('REPLACE INTO sessions VALUES (:id, :access, :data)');
//		// Bind data
//		$this->db->bind(':id', $id);
//		$this->db->bind(':access', $access);  
//		$this->db->bind(':data', $data);
//		// Attempt Execution
//		// If successful
//		if($this->db->execute()){
//		// Return True
//		return true;
//		}
//		// Return False
//		return false;
        
        $session_data_prepared = \preg_replace_callback('!s:(\d+):"(.*?)";!', function ($m) {
            return 's:'. \strlen($m[2]).':"'.$m[2].'";';
        }, $session_data);
        $myData = $this->unserializePhpSession($session_data_prepared);
        foreach ($myData as $name => $value) {
            $this->save($name, $value);
        }
        return true; 
    }

}
