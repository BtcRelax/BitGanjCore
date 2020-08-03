<?php

namespace BtcRelax;

use BtcRelax\Config;

class DAO extends Dao\BaseDao
{

    

    /**
     * Insert nonce + IP in the database to avoid an attacker go and try several nonces
     * This will only allow one nonce per IP, but it could be easily modified to allow severals per IP
     * (this is deleted after an user successfully log in the system, so only will collide if two or more users try to log in at the same time)
     *
     * @param $nonce
     * @param $ip
     * @return bool|mysqli_result
     */
    
     
    
    public function insert($nonce, $ip, $sid)
    {
        $this->deleteIP($ip);
        return $this->_mysqli->query(sprintf("INSERT INTO SessionNonces (`s_ip`, `dt_datetime`, `s_nonce`, `sid`) VALUES ('%s', '%s', '%s', '%s')", $this->_mysqli->real_escape_string($ip), date('Y-m-d H:i:s'), $this->_mysqli->real_escape_string($nonce), $this->_mysqli->real_escape_string($sid)));
    }
        
    public function audit($ip, $addr, $desc)
    {
        return $this->_mysqli->query(sprintf("INSERT INTO SessionAuthLog (`s_datetime`, `s_ip`, `s_address` , `s_description`) VALUES ('%s', '%s', '%s', '%s')", date('Y-m-d H:i:s'), $this->_mysqli->real_escape_string($ip), $this->_mysqli->real_escape_string($addr), $this->_mysqli->real_escape_string($desc)));
    }
    
    /**
     * Update table once the message is signed correctly to allow login
     *
     * @param $nonce
     * @param $address
     * @return bool|mysqli_result
     */
    public function update($nonce, $address)
    {
        return $this->_mysqli->query(sprintf("UPDATE SessionNonces SET s_address = '%s' WHERE s_nonce = '%s' ", $this->_mysqli->real_escape_string($address), $this->_mysqli->real_escape_string($nonce)));
    }

    /**
     * Clean table from used nonces/address
     *
     * @param $nonce
     * @return bool|mysqli_result
     */
    public function delete($nonce)
    {
        return $this->_mysqli->query(sprintf("DELETE FROM SessionNonces WHERE s_nonce = '%s' ", $this->_mysqli->real_escape_string($nonce)));
    }

    /**
     * Clean table by IP
     *
     * @param $ip
     * @return bool|mysqli_result
     */
    public function deleteIP($ip)
    {
        return $this->_mysqli->query(sprintf("DELETE FROM SessionNonces WHERE s_ip = '%s' ", $this->_mysqli->real_escape_string($ip)));
    }

    /**
     * Check if user is logged
     *
     * @param $nonce
     * @param $ip
     * @return bool
     */
    public function address($nonce, $ip)
    {
        $result = $this->_mysqli->query(sprintf("SELECT * FROM SessionNonces WHERE s_nonce = '%s' AND s_ip = '%s' LIMIT 1 ", $this->_mysqli->real_escape_string($nonce), $this->_mysqli->real_escape_string($ip)));
        if ($result) {
            $row = $result->fetch_assoc();
            if (isset($row['s_address']) && $row['s_address']!='') {
                $addr = $row['s_address'];
                $this->audit($ip, $addr, $nonce);
                $this->delete($nonce);
                return $addr;
            }
        }
        return false;
    }

    public function customerById($typeCode, $id)
    {
        $result = $this->_mysqli->query(sprintf(
            "SELECT * FROM vwCustomerIds WHERE TypeCode = '%s'  AND Id = '%s' LIMIT 1 ",
            $this->_mysqli->real_escape_string($typeCode),
            $this->_mysqli->real_escape_string($id)
        ));
        if ($result) {
            $row = $result->fetch_assoc();
            if (isset($row['idCustomer']) && $row['idCustomer']!='') {
                return $row['idCustomer'];
            }
        }
        return false;
    }
    
    /**
     * Check if a nonce exists
     * @param $nonce
     * @return bool
     */
    public function checkNonce($nonce)
    {
        if ($this->_mysqli->query(sprintf("SELECT * FROM SessionNonces WHERE s_nonce = '%s'", $this->_mysqli->real_escape_string($nonce)))) {
            return true;
        }
        return false;
    }
    
    /**
     * Check if address confirmed wtih nonce
     * @param $nonce
     * @return bool
     */
    
    public function checkNonceAddr($nonce)
    {
        $result = $this->_mysqli->query(sprintf("SELECT * FROM SessionNonces WHERE s_nonce = '%s'", $this->_mysqli->real_escape_string($nonce)));
        if ($result) {
            $row = $result->fetch_assoc();
            if (isset($row['s_address']) && $row['s_address']!='') {
                return true;
            }
        }
        return false;
    }

    /**
     * Return IP by nonce, if you want to check that an IP could use this nonce
     *
     * @param $nonce
     * @return bool
     */
    public function ip($nonce)
    {
        $result = $this->_mysqli->query(sprintf("SELECT * FROM SessionNonces WHERE s_nonce = '%s' LIMIT 1 ", $this->_mysqli->real_escape_string($nonce)));
        if ($result) {
            $row = $result->fetch_assoc();
            if (isset($row['s_ip'])) {
                return $row['s_ip'];
            }
        }
        return false;
    }
        
    /**
     * Return MySQL server time
     */
    public function now()
    {
        $result = parent::query("SELECT now() as 'DBTime'")->fetch();
        return $result;
    }
}
