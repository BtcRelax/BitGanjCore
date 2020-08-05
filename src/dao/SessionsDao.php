<?php
namespace BtcRelax\Dao;

use PDO;

final class SessionsDao extends BaseDao
{
    private $SQLStatement_InsertSession;
    private $SQLStatement_DeleteSession;
    private $SQLStatement_DeleteExpiredSession;
    private $SQLStatement_UpdateSessionExpires;
    private $SQLStatement_GetSessionInfos;
    private $SQLStatement_ActiveSessionsInfos;
    private $SQLStatement_GetSessionVars;
    private $SQLStatement_GetEncryptedSessionVars;
    private $SQLStatement_DeleteSessionVars;
    private $SQLStatement_DeleteEncryptedSessionVars;
    private $SQLStatement_InsertSessionVars;
    private $SQLStatement_InsertEncryptedSessionVars;

    private $SQLStatement_InsertSession;
    private $SQLStatement_DeleteSession;
    private $SQLStatement_DeleteExpiredSession;
    private $SQLStatement_UpdateSessionExpires;
    private $SQLStatement_GetSessionInfos;
    private $SQLStatement_ActiveSessionsInfos;
    private $SQLStatement_GetSessionVars;
    private $SQLStatement_GetEncryptedSessionVars;
    private $SQLStatement_DeleteSessionVars;
    private $SQLStatement_DeleteEncryptedSessionVars;
    private $SQLStatement_InsertSessionVars;
    private $SQLStatement_InsertEncryptedSessionVars;


    private function checkSessionId():bool
    {
        $this->SQLStatement_GetSessionInfos->bindParam(':sid', $this->sessionId, \PDO::PARAM_STR, $this->sid_len);
        $this->SQLStatement_GetSessionInfos->execute();
        $val = $this->SQLStatement_GetSessionInfos->fetchAll(\PDO::FETCH_ASSOC);
        if ($val[0]["ua"] ==$this->getUa()) {
            return true;
        } else {
            return false;
        }
    }

    public function getSessions($myId)
    {
        $result = [];
        $query = "SELECT `sid`, `user_agent` , `netinfo`, `forced_expire`, `is_signed`, `lapsed_min` FROM `vwSessionsInfo`;";
        foreach ($this->query($query) as $row) {
            if ($row['sid'] !== $myId) {
                array_push($result, ["id"=> $row['sid'], "data" => [ $row['sid'] , $row['user_agent'] ,  $row['netinfo'] , $row['is_signed'],  $row['lapsed_min'] ] ]);
            }
        }
        return $result;
    }
    //put your code here


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
}
