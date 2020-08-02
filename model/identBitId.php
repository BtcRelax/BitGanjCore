<?php
namespace BtcRelax\Model;

class IdentBitId extends Identicator {

    public function __construct() {
        parent::__construct();
        parent::setIdentTypeCode(Identicator::ID_TYPE_BITID);
    }

    public function buildURI($callback, $nonce)
    {
        $vBitId = new \BtcRelax\BitID();
        return $vBitId->buildURI($callback, $nonce);
    }
       
    public function setId($vId) {
        parent::setId($vId);
    }
        
    public function getId()
    {
        return parent::getId();    
    }

    public function getAuthForm() {
            $imagePath = "/img/WaitCover.gif";
            $vNonce = "";
            $vNonceLink = ""; 
            $vRefreshInterval = "";
            if ($this->getAuthenticationState() === Identicator::STATE_INPROCESS)
                {
                    $vNonce = parent::getNonce();
                    $vNonceLink =  $this->buildURI(SERVER_URL . 'callback.php', $vNonce); 
                    $imagePath = $this->qrCode($vNonceLink); 
                    $vRefreshInterval = AUTH_REFRESH_INTERVAL;
                }
            $templ = '<form id="frmLoginBitId" action="#" method="post"><input id="idNonce" name="nonce" value="%s" type="hidden" />
              <a id="idNonceLink" href="%s"><img id="idImageNonce" class="loginQr" alt="Copoban" border="0" src="%s" /></a>
              <input id="idRefreshInterval" type="hidden"  value="%s" />
              <input type="hidden" name="checkId" value="%s" /></form>';
        $result = sprintf($templ, $vNonce, $vNonceLink, $imagePath, $vRefreshInterval,  $this->getIdentTypeCode());
        return $result;
    }

    public function getForm() {
        $vfrms = sprintf('<div id="id%sIdent"  class="panel panel-default">
                <div class="panel-heading">
                  <center><h1>
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseBitId">Copoban</a>
                  </h1></center>
                </div>
                <div id="collapseBitId" class="panel-collapse collapse" >
                  <div class="panel-body">
                      <div id="copobanContentId" class="w3-animate-zoom" >%s</div>
                  </div>
                </div>
              <script>
                   $(\'#collapseBitId\').on(\'hide.bs.collapse\', function( event ) { JApp.Auth( event );} );
                   $(\'#collapseBitId\').on(\'show.bs.collapse\', function( event ){ JApp.Auth( event , \'%s\');} ); 
              </script>
                   </div>',$this->getIdentTypeCode(), $this->getAuthForm(), $this->getIdentTypeCode() );
        return $vfrms;
    }

    public function checkAuth($vParams = null) {
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
    }

    public function processAuth($vParams) {
        $nonce = parent::getNonce();
        $remoteIp = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
        $dao = new \BtcRelax\DAO();
        $dao->insert($nonce,$remoteIp, parent::getSid() );
        $bitid_uri = $this->buildURI(SERVER_URL . 'callback.php', $nonce);
        $qr_uri = $this->qrCode($bitid_uri);
        //$ajax_uri = SERVER_URL . 'api/CheckNonce';
        $ajax_uri = '/api/CheckNonce';
        $result = ['nonce' => $nonce, 'bitid_uri' => $bitid_uri, 'qr_uri' => $qr_uri, 'ajax_uri' => $ajax_uri, 'refresh_interval' => AUTH_REFRESH_INTERVAL];
        $this->setAuthParams($result);
        $this->setAuthenticationState(Identicator::STATE_INPROCESS);
        parent::saveSession();
        return $result;        
    }

}
