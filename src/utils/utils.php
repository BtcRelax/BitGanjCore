<?php
namespace BtcRelax;

final class Utils
{
    public static function createLink($page, array $params = [])
    {
        unset($params['page']);
        $vUrl = \sprintf('/p/%s?%s', $page, \http_build_query($params));
        return  $vUrl;
    }
    
    public static function generateNonce(int $length = 16)
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
 
    public static function isJson($p_input):bool
    {
        $input=trim($p_input);
        if ((substr($input, 0, 1) == '{' && substr($input, -1) == '}') or (substr($input, 0, 1) == '[' && substr($input, -1) == ']')) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function getJson($p_input)
    {
        $result = false;
        if (\BtcRelax\Utils::isJson($p_input)) {
            $result = \json_decode($p_input, 1);
        }
        return $result;
    }
        
    public static function toJson(array $data)
    {
        return \json_encode($data);
    }

    public static function formatBoolean(bool $bool):int
    {
        return $bool ? 1 : 0;
    }
    
    public static function formatDate(\DateTime $date = null)
    {
        return $date->format('Y-m-d');
    }

    public static function formatDateTime(\DateTime $date)
    {
        return     $result = $date->format('Y-m-d H:i:s');
    }


    public static function redirect($page = null, array $params = [])
    {
        $vUri = \BtcRelax\Utils::getProtocol().'://'. filter_input(\INPUT_SERVER, 'SERVER_NAME') ;
        if (!is_null($page)) {
            $vUri = \BtcRelax\Utils::createLink($page, $params);
        }
        header('Location: ' . $vUri);
        die();
    }

    public static function is_mail(string $mail):bool
    {
        if (preg_match("/^[0-9a-zA-Z\.\-\_]+\@[0-9a-zA-Z\.\-\_]+\.[0-9a-zA-Z\.\-\_]+$/is", trim($mail))) {
            return true;
        }
        return false;
    }
    
    public static function is_url(string $pUrl): bool
    {
        if (\preg_match("/(http(s?):\/\/)([a-z0-9\-]+\.)+[a-z]{2,4}(\.[a-z]{2,4})*(\/[^ ]+)*/i", trim($pUrl))) {
            return true;
        }
        return false;
    }
    
   
    public static function getUserAgent()
    {
        return \filter_input(\INPUT_SERVER, 'HTTP_USER_AGENT');
    }
    
    public static function GetNewPair()
    {
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 384,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        // Create the private and public key
        $res = openssl_pkey_new($config);

        // Extract the private key from $res to $privKey
        \openssl_pkey_export($res, $privKey);

        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        $result = ['PrivateKey' => $privKey, 'PublicKey' => $pubKey];
        return $result;
    }
    


}
