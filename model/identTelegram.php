<?php
namespace BtcRelax\Model;

class IdentTelegram extends Identicator {   
      
    protected $telegramBotName;
    protected $telegramId ;
    protected $telegramSessionCode;
    private $vIsSent;


    public function __construct() {
        parent::__construct();
        parent::setIdentTypeCode(Identicator::ID_TYPE_TELEGRAMM);
    }


    public function getIsSend() {
        return $this->vIsSent;
    }

    public function getTelegramId() {
        return $this->telegramId;
    }

    public  function saveTelegramUserData($auth_data) {
        $auth_data_json = json_encode($auth_data);
        setcookie('tg_user', $auth_data_json);
    }
    
    
    public function getTelegramUserData() {
        if (isset($_COOKIE['tg_user'])) {
            $auth_data_json = urldecode($_COOKIE['tg_user']);
            $auth_data = json_decode($auth_data_json, true);
            return $auth_data;
        }
        return false; 
    }
    

    
    protected  function logout()
    {
        //if (array_key_exists('logout',$_REQUEST) );
          setcookie('tg_user', '');
          header('Location: index.php');
    }


    public function getTelegramSessionCode() {
        return $this->telegramSessionCode;
    }

    public function getId()
    {
        return parent::getId();    
    }

    public function Authenticate($auth_data) {
        $check_hash = $auth_data['hash'];
        unset($auth_data['hash']);
        $data_check_arr = [];
        foreach ($auth_data as $key => $value) {
            $data_check_arr[] = $key . '=' . $value;
            if ($key === 'id')
            {
                $vIdentityKey = $value;
            }
            
        }
        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash('sha256', BOT_TOKEN, true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);
        if (strcmp($hash, $check_hash) !== 0) {
                throw new Exception('Data is NOT from Telegram');
        }
        if ((time() - $auth_data['auth_date']) > 86400) {
            throw new Exception('Data is outdated');
        }
        $this->setIdentityKey($vIdentityKey);
        return $auth_data;
    }
    
    public function getTelegramBotName() {
        return $this->telegramBotName ;
    }
    
    
    public function getTelegramBotLink() {
        $vResult = \sprintf('http://t.me/%s',$this->telegramBotName);
        return $vResult;
    }

    public function getAuthForm() {
            $imagePath = "/img/WaitCover.gif";
            $vNonce = "";
            $vNonceLink = ""; 
            $vRefreshInterval = "";
            if ($this->getAuthenticationState() === Identicator::STATE_INPROCESS)
                {
                    $vNonce = parent::getNonce();
                    $vNonceLink = \sprintf("%s?start=%s", $this->getTelegramBotLink(), $vNonce ); 
                    $imagePath = $this->qrCode($vNonceLink); 
                    $vRefreshInterval = AUTH_REFRESH_INTERVAL;
                }
            $templ = '<form id="frmLoginTelegramId" action="#" method="post"><input id="idNonce" name="nonce" value="%s" type="hidden" />
              <a id="idBotNonceLink" href="%s" target="_blank" ><img id="idImageBotNonce" class="loginQr" alt="Copoban via telegram bot" border="0" src="%s" /></a>
              <input id="idRefreshInterval" type="hidden"  value="%s" />
              <input type="hidden" name="checkId" value="%s" /></form>';
        $result = sprintf($templ, $vNonce, $vNonceLink, $imagePath, $vRefreshInterval,  $this->getIdentTypeCode());
        return $result;
    }

    public function getForm() {
        $vfrms = sprintf('<div id="id%sIdent" class="panel panel-default">
                <div class="panel-heading">
                  <center><h1>
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseTelegram">Telegram</a>
                  </h1></center>
                </div>
                <div id="collapseTelegram" class="panel-collapse collapse">
                  <div class="panel-body"><center>%s</center></div>
                </div>
              <script>
                   $(\'#collapseTelegram\').on(\'hide.bs.collapse\', function(event ) { JApp.Auth(event);} );
                   $(\'#collapseTelegram\').on(\'show.bs.collapse\', function( event ){ JApp.Auth(event, \'%s\');} ); 
              </script>
              </div>', $this->getIdentTypeCode(), $this->getAuthForm(),$this->getIdentTypeCode());
        return $vfrms;      
    }

    public function checkAuth($vParams) {
        $vNonce= parent::getNonce();
        $dao = new \BtcRelax\DAO();
        $result=$dao->checkNonceAddr($vNonce);
        return $result;
    }

    public function doAuthenticate($vParams) {
        $result = false;
        $vNonce= parent::getNonce();
        $dao = new \BtcRelax\DAO();                   
        $addr = $dao->address($vNonce, filter_input(INPUT_SERVER, 'REMOTE_ADDR'));
        if (isset($addr))
        {
                $this->setIdentityKey($addr);
                $result = true; 
        }
        parent::saveSession();
        return $result; 
    }

    public function init() {
        $this->telegramBotName = BOT_USERNAME;
    }

    public function processAuth($vParams) {
        $nonce = parent::getNonce();
        $remoteIp = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
        $dao = new \BtcRelax\DAO();
        $dao->insert($nonce,$remoteIp, parent::getSid() );
        $bot_uri = \sprintf("%s?start=%s", $this->getTelegramBotLink(), $nonce );
        $qr_uri = $this->qrCode($bot_uri);
        //$ajax_uri = SERVER_URL . 'api/CheckNonce';
        $ajax_uri = '/api/CheckNonce';
        $result = ['nonce' => $nonce, 'bot_uri' => $bot_uri, 'bot_name' => $this->getTelegramBotName() , 'qr_uri' => $qr_uri, 'ajax_uri' => $ajax_uri, 'refresh_interval' => AUTH_REFRESH_INTERVAL];
        $this->setAuthParams($result);
        $this->setAuthenticationState(Identicator::STATE_INPROCESS);
        parent::saveSession();
        return $result; 
    }

}
