<?php
namespace BtcRelax;

use \PDO;

class DbSession
{
    const VER = 1;
    private static $instance;
    public $VARS = array();
    protected $sessionId;
    private $connessione;
    private $db_type = '';
    private $db_name = '';
    private $db_pass = '';
    private $db_server = '';
    private $db_username = '';
    private $table_name_session = '';
    private $table_name_variable = '';
    private $table_column_sid = '';
    private $table_column_name = '';
    private $table_column_value = '';
    private $table_column_exp = '';
    private $table_column_fexp = '';
    private $table_column_ua = '';
    protected $sid_name = '';
    private $sid_len = 10;
    protected $session_duration = 1800;
    private $session_max_duration = 3600;
    private $encrypt_data = false;
    private $encrypt_key = '';
    private $hijackBlock = true;
    private $hijackSalt = '';
    private $SQLStatement_CountSid;
    private $sessionIp = '';

    public function setSessionIp()
    {
        $this->sessionIp = \BtcRelax\Utils::getIpAddress();
        return $this;
    }

        
    public function getSessionIp()
    {
        return \BtcRelax\Utils::getIpAddress();
    }

    /**
     * The SQL Statement used for retriving the number of SID's
     *
     * @uses $SQLStatement_InsertSession->bindParam(':sid', $sid, PDO::PARAM_STR, $this->sid_len);
     *       $SQLStatement_InsertSession->bindParam(':expires', $expires, PDO::PARAM_INT);
     *       $SQLStatement_InsertSession->bindParam(':forcedExpires', $forcedExpires, PDO::PARAM_INT);
     *
     * @var PDO Statement
     */
    private $SQLStatement_InsertSession;


    /**
     * The SQL Statement used for deleting a session (all session vars will be deleted)
     *
     * @uses $SQLStatement_DeleteSessionVars->bindParam(':sid', $sid, PDO::PARAM_STR, $this->sid_len);
     *
     * @var PDO Statement
     */
    private $SQLStatement_DeleteSession;

    /**
     * The SQL Statement used for deleting expired session (used by the garbage collector)
     *
     * @uses $SQLStatement_DeleteSessionVars->bindParam(':time', $time, PDO::PARAM_INT);
     *
     * @var PDO Statement
     */
    private $SQLStatement_DeleteExpiredSession;

    /**
     * The SQL Statement used for retriving the number of SID's
     *
     * @uses $SQLStatement_UpdateSessionExpires->bindParam(':sid', $sid, PDO::PARAM_STR, $this->sid_len);
     *       $SQLStatement_UpdateSessionExpires->bindParam(':expires', $expires, PDO::PARAM_INT);
     *
     * @var PDO Statement
     */
    private $SQLStatement_UpdateSessionExpires;

    /**
     * The SQL Statement used for retriving session info's
     *
     * @uses $SQLStatement_GetSessionInfos->bindParam(':sid', $sid, PDO::PARAM_STR, $this->sid_len);
     *
     * @var PDO Statement
     */
    private $SQLStatement_GetSessionInfos;

    
    /**
     * Get array of not ended sessions
     *
     */
    private $SQLStatement_ActiveSessionsInfos;

    /**
     * The SQL Statement used for retriving session vars are not encrypted
     *
     * @uses $SQLStatement_GetSessionVars->bindParam(':sid', $sid, PDO::PARAM_STR, $this->sid_len);
     *
     * @var PDO Statement
     */
    private $SQLStatement_GetSessionVars;

    /**
     * The SQL Statement used for retriving session vars if the vars are encrypted
     *
     * @uses $SQLStatement_GetSessionVars->bindParam(':sid', $sid, PDO::PARAM_STR, $this->sid_len);
     *
     * @var PDO Statement
     */
    private $SQLStatement_GetEncryptedSessionVars;


    /**
     * The SQL Statement used for deleting a session vars if the vars are encrypted
     *
     * @uses $SQLStatement_DeleteSessionVars->bindParam(':sid', $sid, PDO::PARAM_STR, $this->sid_len);
     *       $SQLStatement_DeleteSessionVars->bindParam(':nome', $nome, PDO::PARAM_STR);
     *
     * @var PDO Statement
     */
    private $SQLStatement_DeleteSessionVars;

    /**
     * The SQL Statement used for deleting a session vars if the vars are encrypted
     *
     * @uses $SQLStatement_DeleteSessionVars->bindParam(':sid', $sid, PDO::PARAM_STR, $this->sid_len);
     *       $SQLStatement_DeleteSessionVars->bindParam(':nome', $nome, PDO::PARAM_STR);
     *
     * @var PDO Statement
     */
    private $SQLStatement_DeleteEncryptedSessionVars;

    /**
     * The SQL Statement used for insert a session vars if the vars are not encrypted
     *
     * @uses $SQLStatement_InsertSessionVars->bindParam(':sid', $sid, PDO::PARAM_STR, $this->sid_len);
     *       $SQLStatement_InsertSessionVars->bindParam(':nome', $nome, PDO::PARAM_STR);
     *       $SQLStatement_InsertSessionVars->bindParam(':valore', $value, PDO::PARAM_STR);
     *
     * @var PDO Statement
     */
    private $SQLStatement_InsertSessionVars;

    /**
     * The SQL Statement used for insert a session vars if the vars are not encrypted
     *
     * @uses $SQLStatement_InsertEncryptedSessionVars->bindParam(':sid', $sid, PDO::PARAM_STR, $this->sid_len);
     *       $SQLStatement_InsertEncryptedSessionVars->bindParam(':nome', $nome, PDO::PARAM_STR);
     *       $SQLStatement_InsertEncryptedSessionVars->bindParam(':valore', $value, PDO::PARAM_STR);
     *
     * @var PDO Statement
     */
    private $SQLStatement_InsertEncryptedSessionVars;

    /* PUBLIC METHOD */

    /**
     * Get Class version
     *
     * @return string Class Version
     */
    public static function getVersion()
    {
        return self::VER;
    }

    /**
     * Get a stored var
     *
     * @param string $name Variable name
     * @return object Store variable
     */
    public function getVar($nome)
    {
        return $this->VARS[$nome];
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
     * Get the session id
     *
     * @access public
     * @return string SessionId
     */
    public function getSessionId()
    {
        return $this->sessionId;
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

        $this->del($finalName);
        $this->insert($finalName, $finalValue);

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
        $this->del($nome);
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

    

    /**
     * Check if the session id found is able ti be used
     *
     * @return boolean True if the session Id is ok, False if not
     */
    private function checkSessionId():bool
    {
        $this->SQLStatement_GetSessionInfos->bindParam(':sid', $this->sessionId, PDO::PARAM_STR, $this->sid_len);
        $this->SQLStatement_GetSessionInfos->execute();
        $val = $this->SQLStatement_GetSessionInfos->fetchAll(PDO::FETCH_ASSOC);
        if ($val[0]["ua"] ==$this->getUa()) {
            return true;
        } else {
            return false;
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
                    
        $this->SQLStatement_InsertSession->bindParam(':expires', $expireTime, PDO::PARAM_INT);
        $this->SQLStatement_InsertSession->bindParam(':forcedExpires', $this->forcedExpire, PDO::PARAM_INT);
        $this->SQLStatement_InsertSession->bindParam(':sid', $this->sessionId, PDO::PARAM_STR, $vSidLen);
        $this->SQLStatement_InsertSession->bindParam(':ua', $vUA, PDO::PARAM_STR, $vUaLen);
        $this->SQLStatement_InsertSession->bindParam(':netinfo', $this->getSessionIp(), PDO::PARAM_STR, 45);
        return $this->SQLStatement_InsertSession->execute();
    }

    /**
     * Generate user agent string based on
     * User Agent and Salt.
     * The return string is the SHA1 hash.
     *
     * @return string
     */
    private function getUa()
    {
        //return \sha1(\filter_input(\INPUT_SERVER, 'HTTP_USER_AGENT').$this->hijackSalt);
        return \filter_input(\INPUT_SERVER, 'HTTP_USER_AGENT');
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
     * Create a database connection
     *
     * @access private
     */
    private function dbConnection()
    {
        //if (!is_resource($this->connessione))
        //    $this->connessione = mysql_connect($this->db_server,$this->db_username,$this->db_pass) or die("Error connectin to the DBMS: " . mysql_error());
        if (!is_resource($this->connessione)) {
            try {
                $this->connessione = new PDO($this->db_type . ":dbname=" . $this->db_name . ";host=" . $this->db_server, $this->db_username, $this->db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                //echo "PDO connection object created";
                $this->setupSQLStatement();
            } catch (PDOException $e) {
                trigger_error(\sprintf("Cannot connect to sessions db:%s", $e->getMessage()));
            }
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
            $this->SQLStatement_DeleteSession->bindParam('sid', $this->sessionId, PDO::PARAM_STR, $this->sid_len);
            if ($this->SQLStatement_DeleteSession->execute() === false) {
                $check = false;
            }
        }

        if (setcookie(SIDNAME, $this->sessionId, time() - 3600, "/", '', false, true) === false) {
            $check = false;
        }
            
        unset($_REQUEST[SIDNAME]);
        unset($_POST[SIDNAME]);
        unset($_GET[SIDNAME]);

        return $check;
    }

    /**
     * Setting up the class, reading
     * an existing session, check if a session
     * is expired.
     *
     * @access private
     * @param Array $config Config Array
     */
    protected function setUp()
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
    }
    
    

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
        \BtcRelax\Log::general("Garbage collector working", \BtcRelax\Log::INFO);
        $time = time() - $this->session_max_duration;
        $this->SQLStatement_DeleteExpiredSession->bindParam('time', $time, PDO::PARAM_INT);
        if ($this->SQLStatement_DeleteExpiredSession->execute()===false) {
            trigger_error("Somenthing goes wrong with the garbace collector", E_USER_ERROR);
        } else {
            return true;
        }
    }

    //--------------------SQL FUNCTION

    /**
     * Create the SQL Statement for all the query
     * needed by the class
     *
     * @access private
     */
    private function setupSQLStatement()
    {
        $tabella_sessioni = $this->db_name.".".$this->table_name_session;
        $tabella_variabili = $this->db_name.".".$this->table_name_variable;
        /*** SQL statement: count SID ***/
        $this->SQLStatement_CountSid = $this->connessione->prepare("SELECT count(*) FROM ".$tabella_sessioni." WHERE ".$this->table_column_sid." = :sid");

        /*** SQL statement: Insert Session ***/
        $this->SQLStatement_InsertSession = $this->connessione->prepare("INSERT INTO ".$tabella_sessioni."(".$this->table_column_sid.",".$this->table_column_exp.",".$this->table_column_fexp.",".$this->table_column_ua.",netinfo) VALUES (:sid,:expires,:forcedExpires,:ua,:netinfo)");

        /*** SQL statement: Update Session Expires ***/
        $this->SQLStatement_UpdateSessionExpires = $this->connessione->prepare("UPDATE ".$tabella_sessioni." SET ".$this->table_column_exp." = :expires WHERE ".$this->table_column_sid." = :sid");

        /*** SQL statement: Get Session Infos ***/
        $this->SQLStatement_GetSessionInfos = $this->connessione->prepare("SELECT * FROM ".$tabella_sessioni." WHERE ".$this->table_column_sid." = :sid");

        
        $this->SQLStatement_ActiveSessionsInfos = $this->connessione->prepare("SELECT * FROM ".$tabella_sessioni);
        
        /*** SQL statement: Get Session Vars ***/
        $this->SQLStatement_GetSessionVars = $this->connessione->prepare("SELECT ".$this->table_column_value." as valore, ".$this->table_column_name." as nome FROM ".$tabella_variabili." WHERE ".$this->table_column_sid." = :sid");
        
        /*** SQL statement: Get Encrypted Session Vars ***/
        $this->SQLStatement_GetEncryptedSessionVars = $this->connessione->prepare("SELECT AES_DECRYPT(".$this->table_column_value.",'".$this->encrypt_key."') as valore,AES_DECRYPT(".$this->table_column_name.",'".$this->encrypt_key."') as nome FROM ".$tabella_variabili." WHERE ".$this->table_column_sid." = :sid");

        /*** SQL statement: Delete Session Vars ***/
        $this->SQLStatement_DeleteSessionVars = $this->connessione->prepare("DELETE FROM ".$tabella_variabili."  WHERE ".$this->table_column_sid." = :sid AND ".$this->table_column_name."= :nome ");

        /*** SQL statement: Delete Encrypted Session Vars ***/
        $this->SQLStatement_DeleteEncryptedSessionVars = $this->connessione->prepare("DELETE FROM ".$tabella_variabili."  WHERE ".$this->table_column_sid." = :sid AND ".$this->table_column_name."= AES_ENCRYPT(:nome,'".$this->encrypt_key."') ");

        /*** SQL statement: Insert Session Vars ***/
        $this->SQLStatement_InsertSessionVars = $this->connessione->prepare("INSERT INTO ".$tabella_variabili."(".$this->table_column_sid.",".$this->table_column_name.",".$this->table_column_value.") VALUE(:sid,:nome,:valore)");

        /*** SQL statement: Insert Encrypted Session Vars ***/
        $this->SQLStatement_InsertEncryptedSessionVars = $this->connessione->prepare("INSERT INTO ".$tabella_variabili."(".$this->table_column_sid.",".$this->table_column_name.",".$this->table_column_value.") VALUE(:sid,AES_ENCRYPT(:nome,'".$this->encrypt_key."'),AES_ENCRYPT(:valore,'".$this->encrypt_key."'))");

        /*** SQL statement: Delete Session ***/
        $this->SQLStatement_DeleteSession = $this->connessione->prepare("DELETE FROM ".$tabella_sessioni."  WHERE ".$this->table_column_sid." = :sid ");

        /*** SQL statement: Delete Expired Session ***/
        $this->SQLStatement_DeleteExpiredSession = $this->connessione->prepare("DELETE FROM ".$tabella_sessioni."  WHERE ".$this->table_column_fexp." < :time ");
    }

    /**
     * Count how many "$sid" are in the Session_Vars table
     *
     * @access private
     * @param string $sid: sid searched for
     * @return int: The number of records
     */
    private function getSidCount($sid)
    {
        $this->SQLStatement_CountSid->bindParam(':sid', $sid, PDO::PARAM_STR, $this->sid_len);
        $this->SQLStatement_CountSid->execute();

        $val=$this->SQLStatement_CountSid->fetchColumn();
        
        return $val;
    }

    /**
     *  Return array of session objects
     */
    public function getGlobalSessionsInfo()
    {
        $this->SQLStatement_ActiveSessionsInfos->execute();
        $val = $this->SQLStatement_ActiveSessionsInfos->fetchAll(PDO::FETCH_ASSOC);
        return $val;
    }
            
    /**
    * Select all session vars
    *
    * @access private
    * @return array Fetched Vars
    */
    private function selectSessionVars()
    {

       //prelevo le variabili e le metto nell'array VARS
        if ($this->encrypt_data==1) {
            $this->SQLStatement_GetEncryptedSessionVars->bindParam('sid', $this->sessionId, PDO::PARAM_STR, $this->sid_len);
            $this->SQLStatement_GetEncryptedSessionVars->execute();
            $val = $this->SQLStatement_GetEncryptedSessionVars->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $this->SQLStatement_GetSessionVars->bindParam('sid', $this->sessionId, PDO::PARAM_STR, $this->sid_len);
            $this->SQLStatement_GetSessionVars->execute();
            $val = $this->SQLStatement_GetSessionVars->fetchAll(PDO::FETCH_ASSOC);
        }
        //var_dump($val);
        return $val;
    }

    /**
     * Update current session expires
     *
     * @access private
     * @return boolean - True if the query succesfully done. False in any other case
     */
    private function updateSessionExpireTime()
    {
        $newExprireTime = time()+$this->session_duration;
        $this->SQLStatement_UpdateSessionExpires->bindParam(':expires', $newExprireTime, PDO::PARAM_INT);
        $this->SQLStatement_UpdateSessionExpires->bindParam(':sid', $this->sessionId, PDO::PARAM_STR, $this->sid_len);
        return $this->SQLStatement_UpdateSessionExpires->execute();
    }

    /**
     * Send a delete query to the DBMS
     *
     * Query will be created according to this prototype:
     * DELETE FROM $db.$tabelle WHERE $cond
     *
     * @access private
     * @param string $nome: name of the variable to delete from session
     * @return boolean - True if the query succesfully done. False in any other case
     */
    private function del($nome)
    {
        if ($this->encrypt_data==1) {
            $this->SQLStatement_DeleteEncryptedSessionVars->bindParam('sid', $this->sessionId, PDO::PARAM_STR, $this->sid_len);
            $this->SQLStatement_DeleteEncryptedSessionVars->bindParam('nome', $nome, PDO::PARAM_STR);
            $result = $this->SQLStatement_DeleteEncryptedSessionVars->execute();
        } else {
            $this->SQLStatement_DeleteSessionVars->bindParam('sid', $this->sessionId, PDO::PARAM_STR, $this->sid_len);
            $this->SQLStatement_DeleteSessionVars->bindParam('nome', $nome, PDO::PARAM_STR);
            $result = $this->SQLStatement_DeleteSessionVars->execute();
        }

        return $result;
    }

    /**
     * Send an insert query to the DBMS
     *
     * Query will be created according to this prototype:
     * INSERT INTO $db.$tabelle SET $nome = $valore
     *
     * @access private
     * @param string $nome: variable name
     * @param string $val: variable value
     * @return boolean - True if the query succesfully done. False in any other case
     */
    private function insert($nome, $val)
    {
        if ($this->encrypt_data==1) {
            $this->SQLStatement_InsertEncryptedSessionVars->bindParam('sid', $this->sessionId, PDO::PARAM_STR, $this->sid_len);
            $this->SQLStatement_InsertEncryptedSessionVars->bindParam('nome', $nome, PDO::PARAM_STR);
            $this->SQLStatement_InsertEncryptedSessionVars->bindParam('valore', $val, PDO::PARAM_STR);
            $result = $this->SQLStatement_InsertEncryptedSessionVars->execute();
        } else {
            $this->SQLStatement_InsertSessionVars->bindParam('sid', $this->sessionId, PDO::PARAM_STR, $this->sid_len);
            $this->SQLStatement_InsertSessionVars->bindParam('nome', $nome, PDO::PARAM_STR);
            $this->SQLStatement_InsertSessionVars->bindParam('valore', $val, PDO::PARAM_STR);
            $result = $this->SQLStatement_InsertSessionVars->execute();
        }

        return $result;
    }
}
