<?php
namespace BtcRelax;
require_once ('vendor/autoload.php');
/**
 * Description of APIClient
 *
 * @author god
 */
class APIClient {
    private $_baseServerUrl;
    private $_client;
    protected $_last_error = '';
    
    function __construct($baseUrl = null) {
        if (defined('SERVER_URL') && $baseUrl === null) 
            {
                $baseUrl = SERVER_URL;            
            }
        $this->_baseServerUrl = $baseUrl;
        $this->_client = new \GuzzleHttp\Client(['base_uri' => $this->_baseServerUrl]);
    }
    
    function getVer()
    {
        try {
            $response = $this->_client->request('GET','/api/Info?action=getver');        
            $json = $response->getBody();
            $data = \GuzzleHttp\json_decode($json, true);
            $result = $data['Core'];
        } catch (\GuzzleHttp\Exception $e) {
            $this->_last_error = $e->getMessage();
        }
        return $result;
    }
   
    
    function SetTelegramToNonce($pNonce, $pTelegramId)
    {
        $result = false;
        try {
            $payload = \sprintf('nonce=%s&telegramId=%s', $pNonce, $pTelegramId);
            $response = $this->_client->request('POST', '/callback.php', [ 'body' => $payload,
                 'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded',] ]);
            $code = $response->getStatusCode();
            if ($code === 200) {
                $result = true;
            }
        } catch (\GuzzleHttp\Exception $e) {
            $this->_last_error = $e->getMessage();
        }
        return $result;
    }
    
    public function get_last_error() {
        return $this->_last_error;
    }


}
