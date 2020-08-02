<?php
namespace BtcRelax;

class LHCApi {
    protected $Core;
    protected $CurrentSession = null;
    protected $User = null;
    protected $LastError = null;
    protected $Department = null;
    protected $ThemeId = null;
    protected $UserNameAlias = "";
    protected $operatorId = "";
    public static $method = 'AES-256-CBC';
    private $host = null;
    private $username = null;
    private $apiKey = null;

    public function __construct($host, $username, $apiKey) {
        global  $core;
        if (empty($host)) {
            if (defined('LHC_URL')) {
                $host = LHC_URL;
            } else {
                throw new \BtcRelax\Exception("Try to create BtcRelax\LHCApi object with null host parameter and not defined LHC_URL parameter in config");
            }
        }
        $this->Core = $core;
        $this->host = $host;
	$this->username = $username;
	$this->apiKey = $apiKey;
    }
    
    public function getHost() {
        return $this->host;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getApiKey() {
        return $this->apiKey;
    }

    public function setHost($host) {
        $this->host = $host;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
        return $this;
    }

        
    private function executeRequest($function, $params, $uparams = array(), $method = 'GET', $manualAppend = '')
    {
		$ch = curl_init();
		$headers = array('Accept' => 'application/json');
		$uparamsArg = '';
		
		if (!empty($uparams) && is_array($uparams)) {
		    $parts = array();
		    foreach ($uparams as $param => $value) {
		        $parts[] = '/('.$param .')/'.$value;
		    }
		    $uparamsArg = implode('', $parts);
		    
		}
		
		$requestArgs = ($method == 'GET') ? '?' .http_build_query($params) : '';
		
		if ($method == 'POST') {
		    curl_setopt($ch,CURLOPT_POST,1);
		    curl_setopt($ch,CURLOPT_POSTFIELDS,$params);
		}
		
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->apiKey);		
		curl_setopt($ch, CURLOPT_URL, $this->host . '/restapi/' . $function . $manualAppend . $uparamsArg . $requestArgs);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Some hostings produces wargning...
		$content = curl_exec($ch);
		return $content;
	}
        
    public function execute($function, $params, $uparams = array(), $method = 'GET', $jsonObject = true, $manualAppend = '')
	{
	    $response = $this->executeRequest($function, $params, $uparams, $method, $manualAppend);
	   	    
	    if ($jsonObject == false) {
	        return $response;
	    }
	    
	    $jsonData = json_decode($response);
	    if ($jsonData !== null) {
	        return $jsonData;
	    } else {
	        throw new Exception('Could not parse response - '.$response);
	    }	    
	}
    
    
    public static function encrypt(string $data, string $key) : string  {
        $ivSize = openssl_cipher_iv_length(self::$method);
        $iv = openssl_random_pseudo_bytes($ivSize);
        $encrypted = openssl_encrypt($data, self::$method, $key, OPENSSL_RAW_DATA, $iv);
        // For storage/transmission, we simply concatenate the IV and cipher text
        $encrypted = base64_encode($iv . $encrypted);
        return $encrypted;
    }

    public static function getPageEmbededCode():string  {
        return "<div id=\"lhc_status_container_page\" >&nbsp;</div>";
    }
    
    public static function getChatBoxCode():string  {
        return "<div id=\"lhc_chatbox_embed_container\" ></div>";
    }
    
    public static function decrypt(string $data, string $key) : string  {
        $data = base64_decode($data);
        $ivSize = openssl_cipher_iv_length(self::$method);
        $iv = substr($data, 0, $ivSize);
        $data = openssl_decrypt(substr($data, $ivSize), self::$method, $key, OPENSSL_RAW_DATA, $iv);
        return $data;
    }
	    
    public function setUserNameAlias ($UserNameAlias) {
        $this->UserNameAlias = $UserNameAlias;
    }

    public function getOperatorId() {
        return $this->operatorId;
    }
    
    private function getOperatorString() {
        $result = "";
        if (is_int($this->getOperatorId())) {
            $result = \sprintf( "/(operator)/%s" ,$this->getOperatorId());        
        }
        return $result;
    }

    public function setOperatorId($operatorId) {
        $this->operatorId = $operatorId;
    }


    
    private function getUserId () {
        return $this->getUser()->getIdCustomer();
    }

    private function getOrderId() {
		$vOM = \BtcRelax\Core::createOM();
		$result = $vOM->getActualOrder();
		if (FALSE !== $result ) {
                        if (is_int($result->getIdOrder())) {
				$result =  $result->getIdOrder();
			} else { $result = false; };
		};
        return $result;
    }
       
    public function getTheme() {
        return $this->ThemeId;
    }
    
    public function getThemeString() {
        $result = "";
        if (is_int($this->getTheme())) {
            $result = \sprintf( "/(theme)/%s" ,$this->getTheme());        
        };
        return $result;
    }
    
    public function setTheme($pThemeId) {
        $this->ThemeId = $pThemeId;
    }
        
    public function getDepartment() {
        return $this->Department;
    }
    
    public function getDepartmentString()  {
        $result = "";
        if (is_int($this->getDepartment())) {
            $result = \sprintf( "/(department)/%s" ,$this->getDepartment());        
        };
        return $result;
    }
    
    public function setDepartment($Department) {
        $this->Department = $Department;
    }
        
    public function getCurrentSession(): \BtcRelax\SecureSession {
        if (is_null($this->CurrentSession))
        { $this->CurrentSession = $this->Core->getCurrentSession(); }
        return $this->CurrentSession;
    }

    private function getUser() {
        $vAM = \BtcRelax\Core::createAM();
        return $vAM->getUser(); 
    }

    public function getTotalOrdersCount() {
        $vUser = $this->getUser();
        return $vUser->getTotalOrdersCount();
    }
    
    public function getLastError() { return $this->LastError; }

    public function setLastError($LastError) { $this->LastError = $LastError; }

    public static function getOfferScript() {
        $result = null;
        if ((defined('LHC_URL')) && (!empty(LHC_URL)) ) {
        $result = \sprintf("<script type=\"text/javascript\">var LHCBROWSEOFFEROptions = {domain:'%s'}; (function() {" 
            . "var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;"
            . "var referrer = (document.referrer) ? encodeURIComponent(document.referrer.substr(document.referrer.indexOf('://')+1)) : '';"
            . "var location  = (document.location) ? encodeURIComponent(window.location.href.substring(window.location.protocol.length)) : '';"
            . "po.src = '%s/index.php/rus/browseoffer/getstatus/(size)/450/(height)/450/(units)/pixels/(timeout)/1/(showoverlay)/true/(canreopen)/false?r='+referrer+'&l='+location;"
        . "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);})();</script>", LHC_URL , LHC_URL ); }
        return $result;
    }
 
    public function getWidgetScript() {
        //$vCurrentSession = $this->getCurrentSession(); $script = "";
        //$vIsHasLHCAccount = $vCurrentSession->getValue('isHasLHCAccount');
        //if (!$vIsHasLHCAccount) {
                    $script = $this->fillChatWidget();
        //    }
        return isset($script) === true ? $script: false ;
    }
    
    public function getAPIScript() {
        $vHtml = \sprintf("<script type=\"text/javascript\">(function() {
            var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
            var referrer = (document.referrer) ? encodeURIComponent(document.referrer.substr(document.referrer.indexOf('://')+1)) : '';
            var location  = (document.location) ? encodeURIComponent(window.location.href.substring(window.location.protocol.length)) : '';
            po.src = '%s/index.php/chat/getstatus/(position)/api/(ma)/br/(top)/350/(units)/pixels?r='+referrer+'&l='+location;
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
            })();</script>", $this->getHost());
        return $vHtml;
    }
    
    public function getChatboxScript() {        
        $vIdentifier = $this->getUserId();
        $script = \sprintf("<script type=\"text/javascript\">
            var LHCChatboxOptionsEmbed = {hashchatbox:'%s',identifier:'%s'};
            (function() { var po = document.createElement('script'); 
            po.type = 'text/javascript'; po.async = true; 
            po.src = '%s/index.php/rus/chatbox/embed/';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
            })();</script>", $this->getHashChatBox($vIdentifier)  ,$vIdentifier,$this->host );
        return $script;
    }
    
    private function getHashChatBox($identifier)  {
       return \sha1(LHC_CHATBOX_SHASH. \sha1(LHC_CHATBOX_SHASH.$identifier));
    }
    
    private function fillChatWidget() {
       $vPaidChat = $this->getPaidChat();
       $vUser = $this->getUser();
       $vUserAlias = $vUser->getUserNameAlias();
       $vIdUserName =  \BtcRelax\LHCApi::encrypt($vUserAlias,LHC_ENCRYPTION_KEY) ;      
       $vIdCustomer  = \BtcRelax\LHCApi::encrypt($vUser->getIdCustomer(),LHC_ENCRYPTION_KEY) ; 
       $vOrderCount = \BtcRelax\LHCApi::encrypt($vUser->getTotalOrdersCount(),LHC_ENCRYPTION_KEY) ;
       $vLostCount = \BtcRelax\LHCApi::encrypt($vUser->getLostCount(), LHC_ENCRYPTION_KEY);
       $vRegisterDate = \BtcRelax\LHCApi::encrypt($vUser->getCreateDateFormated(), LHC_ENCRYPTION_KEY);
       $script = \sprintf("<script type=\"text/javascript\">var LHCChatOptions = {};
                            LHCChatOptions.opt = {widget_height:340,widget_width:300,popup_height:520,popup_width:500,domain:'bitganj.website',subdomain:true};
                            LHCChatOptions.attr = new Array();
                            LHCChatOptions.attr.push({'name':'Username','value':'%s','type':'hidden','size':0,'encrypted':true });
                            LHCChatOptions.attr.push({'name':'CustomerId','value':'%s','type':'hidden','size':0,'encrypted':true});
                            LHCChatOptions.attr.push({'name':'OrderCount','value':'%s','type':'hidden','size':0,'encrypted':true});
                            LHCChatOptions.attr.push({'name':'LostCount','value':'%s','type':'hidden','size':0,'encrypted':true});
                            LHCChatOptions.attr.push({'name':'RegisterDate','value':'%s','type':'hidden','size':0,'encrypted':true});
                            
                            %s
                            
                            LHCChatOptions.attr_online = new Array();
                            LHCChatOptions.attr_online.push({'name':'Username','value':'%s'});
							
                            LHCChatOptions.attr_prefill_admin = new Array();
                            LHCChatOptions.attr_prefill_admin.push({'name':'Username','value':'%s','type':'hidden','size':0,'encrypted':true });
                            LHCChatOptions.attr_prefill_admin.push({'name':'CustomerId','value':'%s','type':'hidden','size':0,'encrypted':true});
                            LHCChatOptions.attr_prefill_admin.push({'name':'OrderCount','value':'%s','type':'hidden','size':0,'encrypted':true});
                            LHCChatOptions.attr_prefill_admin.push({'name':'LostCount','value':'%s','type':'hidden','size':0,'encrypted':true});
                            LHCChatOptions.attr_prefill_admin.push({'name':'RegisterDate','value':'%s','type':'hidden','size':0,'encrypted':true});


                            (function() { var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                            var referrer = (document.referrer) ? encodeURIComponent(document.referrer.substr(document.referrer.indexOf('://')+1)) : '';
                            var location  = (document.location) ? encodeURIComponent(window.location.href.substring(window.location.protocol.length)) : '';
                            po.src = '%s/index.php/rus/chat/getstatus/(click)/internal/(position)/bottom_right/(ma)/br/(hide_offline)/true/(top)/350/(units)/pixels%s%s%s?r='+referrer+'&l='+location;
                            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);})();</script>", 
                            $vIdUserName, $vIdCustomer , $vOrderCount,$vLostCount,$vRegisterDate,  $vPaidChat , $vUserAlias , $vIdUserName , $vIdCustomer,$vOrderCount, $vLostCount ,$vRegisterDate ,$this->host, $this->getDepartmentString(), $this->getThemeString(), $this->getOperatorString());    
        return $script ;
    }
    
    public static function prepareUserInfo(\BtcRelax\Model\User $vUser ) {
        if (empty($vUser)) { $vUser = $this->getUser();  }
        $vList  = $vUser->getArray(); $result = [];
        foreach ($vList as $key => $value) {
            if ((!\is_object($value)) && (!\is_array($value))) {
                $result += [ $key => \BtcRelax\LHCApi::encrypt($value,LHC_ENCRYPTION_KEY) ];
            }
        }
        return $result ;
    }
    
    public function prepareLHCChatOptionsPage(\BtcRelax\Model\User $vUser)
    {
        $vParams = $this->prepareUserInfo($vUser);
        foreach ($vParams as $key => $value) {
            $vParamsString .= \sprintf("LHCChatOptionsPage.attr.push({'name':'%s','value':'%s','type':'hidden','size':0,'encrypted':true});", $key, $value );           
        }
        $resultScript = \sprintf("var LHCChatOptionsPage = {};LHCChatOptionsPage.opt = {};
            LHCChatOptionsPage.attr = new Array();%s", $vParamsString);
        $vParamsPrefillString = "LHCChatOptionsPage.attr_prefill_admin = new Array();"; $vIndex = 0;
        foreach ($vParams as $key => $value) {
            $vParamsPrefillString .= \sprintf("LHCChatOptionsPage.attr_prefill_admin.push({'index':'%s', 'value':'%s', 'encrypted':true, 'hidden':true});", $vIndex, $value);
            $vIndex++;
        }
        $resultScript .= \sprintf("%sLHCChatOptionsPage.attr_online = new Array(); LHCChatOptionsPage.attr_online.push({'name':'Username','value':'%s'});", $vParamsPrefillString,  $vUser->getUserNameAlias() );
        return $resultScript ;
    }


    public function getPageWidgetScript() {
        //$vUser = $this->getUser();        $vUserAlias = $vUser->getUserNameAlias();
        //$vIdUserName =  \BtcRelax\LHCApi::encrypt($vUserAlias,LHC_ENCRYPTION_KEY) ;      
        //$vIdCustomer  = \BtcRelax\LHCApi::encrypt($vUser->getIdCustomer(),LHC_ENCRYPTION_KEY);
        // $vOrdersCount = \BtcRelax\LHCApi::encrypt($vUser->getTotalOrdersCount(),LHC_ENCRYPTION_KEY);

//        $script = \sprintf("<script type=\"text/javascript\">var LHCChatOptionsPage = {};
//            LHCChatOptionsPage.opt = {};
//            LHCChatOptionsPage.attr = new Array();
//            LHCChatOptionsPage.attr.push({'name':'CustomerId','value':'%s','type':'hidden','size':0,'encrypted':true});
//            LHCChatOptionsPage.attr.push({'name':'Username','value':'%s', 'type':'hidden', 'size':0,'encrypted':true });
//            LHCChatOptionsPage.attr.push({'name':'OrdersCount','value':'%s', 'type':'hidden', 'size':0,'encrypted':true });
//		            
//            LHCChatOptionsPage.attr_online = new Array();
//            LHCChatOptionsPage.attr_online.push({'name':'Username','value':'%s'});
//							
//            LHCChatOptionsPage.attr_prefill_admin = new Array();
//            LHCChatOptionsPage.attr_prefill_admin.push({'index':'0', 'value':'%s', 'encrypted':true, 'hidden':true});         
//            LHCChatOptionsPage.attr_prefill_admin.push({'index':'1', 'value':'%s', 'encrypted':true, 'hidden':true});             
//            LHCChatOptionsPage.attr_prefill_admin.push({'index':'2', 'value':'%s', 'encrypted':true, 'hidden':true});   
//            
//            (function() {
//            var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
//            po.src = '%sindex.php/rus/chat/getstatusembed/(leaveamessage)/true/%s%s%s';
//            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
//            })();</script>",$vIdCustomer,$vIdUserName, $vOrdersCount, $vUserAlias,$vIdCustomer,$vIdUserName, $vOrdersCount  ,$this->host, $this->getDepartmentString() , $this->getThemeString(),  $this->getOperatorString());
//            
//            return $script;
                return \sprintf("%s</div>
                            <script type=\"text/javascript\">
                            var LHCChatOptionsPage = {};
                                LHCChatOptionsPage.opt = {};
                        (function() {
                            var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                            po.src = '%s/rus/chat/getstatusembed/%s';
                            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                            })();
                           </script>", \BtcRelax\LHCApi::getPageEmbededCode() , $this->host ,$this->getDepartmentString() );
            }   
    
    
    
    public static function fillFAQWidget() {
        if (defined('LHC_URL')) {
            return \sprintf("<script type=\"text/javascript\">
                    var LHCFAQOptions = {status_text:'Вопросы?', url:'', identifier:''};
                    (function() { var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true; 
                    po.src = '%s/index.php/rus/faq/getstatus/(position)/bottom_right/(top)/450/(units)/pixels/';
                    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);                    
        })();</script>",LHC_URL);
        } else {
            return false;
        }
    }
	
    private function getPaidChat()  {
		$vResult = "";
		if (defined ('LHC_SECRET_VALIDATION_HASH'))
		{ 
                    $vSecretValidationHash = LHC_SECRET_VALIDATION_HASH;
                    $vOrderId = $this->getOrderId();
                    if (FALSE != $vOrderId)   {
    			
    			$vOrderIdHash = \sha1($vSecretValidationHash.sha1($vSecretValidationHash.$vOrderId));
    			$vResult = \sprintf("LHCChatOptions.attr.push({'name':'OrderId','value':'%s', 'type':'hidden', 'size':0,'encrypted':false });", $vOrderId );
    			$vResult =  \sprintf("%sLHCChatOptions.attr_paid = {phash:'%s',pvhash:'%s'};",$vResult, $vOrderId, $vOrderIdHash );
                    } else {
                        $vUserId = $this->getUserId();
                        $vUserIdHash = \sha1($vSecretValidationHash.sha1($vSecretValidationHash.$vUserId));
                        $vResult =  \sprintf("LHCChatOptions.attr_paid = {phash:'%s',pvhash:'%s'};", $vUserId, $vUserIdHash );
                    }               
		}
		return $vResult;
	}
	
  public static function getMenuItemForUser(\BtcRelax\Model\User $vUser) {
        $result = $vUser->getPropertyValue("lhc_uid");        
        if (FALSE !== $result) {
            $params = ['r' => 'chat/onlineusers', 'u' => $result , 't' =>  time() + 60 , 'secret_hash' => LHC_SHASH];
            $vGeneratedLink = \BtcRelax\LHCApi::generateAutoLoginLink($params);
            $vUrl = \sprintf("<a target=\"_blank\" href=\"%s/%s\">Помощь</a>",LHC_URL,$vGeneratedLink ); }
        return $result !== false? $vUrl: $result ;
    }
        

    
    public static function generateAutoLoginLink($params){
        $dataRequest = array();$dataRequestAppend = array();
        // Destination ID
        if (isset($params['r'])){
            $dataRequest['r'] = $params['r']; $dataRequestAppend[] = '/(r)/'.rawurlencode(base64_encode($params['r']));
        }
        // User ID
        if (isset($params['u']) && is_numeric($params['u'])){
            $dataRequest['u'] = $params['u']; $dataRequestAppend[] = '/(u)/'.rawurlencode($params['u']);
        }
        // Username
        if (isset($params['l'])){
            $dataRequest['l'] = $params['l']; $dataRequestAppend[] = '/(l)/'.rawurlencode($params['l']);
        }
        if (!isset($params['l']) && !isset($params['u'])) {
         throw new Exception('Username or User ID has to be provided');
        }
        // Expire time for link
        if (isset($params['t'])){
         $dataRequest['t'] = $params['t'];
         $dataRequestAppend[] = '/(t)/'.rawurlencode($params['t']);
        }
        $hashValidation = sha1($params['secret_hash'].sha1($params['secret_hash'].implode(',', $dataRequest)));
        return "index.php/user/autologin/{$hashValidation}".implode('', $dataRequestAppend);
    }
}
