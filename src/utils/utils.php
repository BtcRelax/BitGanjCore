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
 
    public static function getHeaders():array
    {
        if (!function_exists('getallheaders')) {
            foreach ($_SERVER as $name => $value) { /* RFC2616 (HTTP/1.1) defines header fields as case-insensitive entities. */
                if (strtolower(substr($name, 0, 5)) == 'http_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        } else {
            $headers = getallheaders();
        }
        return ($headers);
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
    
    public static function getRequestMethod():string
    {
        return \filter_input(\INPUT_SERVER, 'REQUEST_METHOD');
    }
    
    public static function getRequestUrl():string
    {
        $server_name = \filter_input(\INPUT_SERVER, 'SERVER_NAME');
        $server_port = \filter_input(\INPUT_SERVER, 'SERVER_PORT');
        $current_url  = (\filter_input(\INPUT_SERVER, 'HTTPS') != 'on') ? 'http://'.$server_name :  'https://'.$server_name;
        $current_url .= ($server_port != 80 || $server_port != 443) ? ":".$server_port : "";
        $current_url .= \filter_input(\INPUT_SERVER, 'REQUEST_URI');
        return $current_url;
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
    
    
    public static function getRequestParams()
    {
        $result = [];
        switch (self::getRequestMethod()) {
            case "POST":
                $result = self::cleanInputs($_POST);
                break;
            case "GET":
                $result = self::cleanInputs($_GET);
                break;
            case "DELETE":
                $result = self::cleanInputs($_GET);
                break;
            case "PUT":
                parse_str(file_get_contents("php://input"), $result);
                $result = self::cleanInputs($result);
                break;
            default:
                \BtcRelax\Log::general(\sprintf('Unknown type of request:%s', self::getRequestMethod()), \BtcRelax\Log::FATAL);
                break;
        }
        return $result;
    }
    
    
    private static function cleanInputs($data)
    {
        $clean_input = [];
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = self::cleanInputs($v);
            }
        } else {
            if (get_magic_quotes_gpc()) {
                $data = trim(stripslashes($data));
            }
            $data = strip_tags($data);
            $clean_input = trim($data);
        }
        return $clean_input;
    }
    
    public static function httpGet(string $url, $post_vars = false)
    {
        //header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
        //header('Pragma: no-cache'); // HTTP 1.0.
        //header('Expires: 0'); // Proxies.
        $post_contents = '';
        if (is_array($post_vars)) {
            foreach ($post_vars as $key => $val) {
                $post_contents .= ($post_contents ? '&' : '').urlencode($key).'='.urlencode($val);
            }
        } else {
            $post_contents = $post_vars;
        }
        $uinf = \parse_url($url);
        $host = $uinf['host'];
        $path = $uinf['path'];
        $path .= (isset($uinf['query']) && $uinf['query']) ? ('?'.$uinf['query']) : '';
        $headers = array(
        ($post_contents ? 'POST' : 'GET')." $path HTTP/1.1",
        "Host: $host",
    );
        if ($post_contents) {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = 'Content-Length: '.strlen($post_contents);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($post_contents) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_contents);
        }
        $data = curl_exec($ch);
        if (\curl_errno($ch)) {
            return false;
        }
        curl_close($ch);
        return $data;
    }
    
    public static function getIpAddress()
    {
        $ip = \filter_input(\INPUT_SERVER, 'REMOTE_ADDR');
        return isset($ip)? $ip: false;
    }

    public static function getIpAddressFromProxy()
    {
        $header = 'HTTP_X_FORWARDED_FOR';
        if (!isset($_SERVER[$header]) || empty($_SERVER[$header])) {
            return false;
        }
        // Extract IPs
        $ips = explode(',', $_SERVER[$header]);
        // trim, so we can compare against trusted proxies properly
        $ips = array_map('trim', $ips);
        // remove trusted proxy IPs
        // Any left?
        if (empty($ips)) {
            return false;
        }

        // Since we've removed any known, trusted proxy servers, the right-most
        // address represents the first IP we do not know about -- i.e., we do
        // not know if it is a proxy server, or a client. As such, we treat it
        // as the originating IP.
        // @see http://en.wikipedia.org/wiki/X-Forwarded-For
        $ip = array_pop($ips);
        return $ip;
    }
        
    public static function getProtocol():string
    {
        return \filter_input(\INPUT_SERVER, "SERVER_PROTOCOL");
    }
}
