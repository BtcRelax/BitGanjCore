<?php
namespace BtcRelax\Model;

use BtcRelax\BitID;
use BtcRelax\Dao\CustomerDao;
use BtcRelax\Dao\CustomerPropertyDao;
use BtcRelax\Dao\CustomerRightsDao;
use BtcRelax\Dao\IdentifierDao;
use BtcRelax\Dao\IdentifierSearchCriteria;
use BtcRelax\Mapping\CustomerMapper;
use BtcRelax\Utils;
use LogicException;

class User extends Customer
{
    private $xPub;
    private $InvoicesCount = -1;
    protected $_rights = array();
    protected $_properties = array();
    protected $_identifiers = array();

    public function __construct()
    {
        parent::__construct();
    }
    
    public function getIdentifiers()
    {
        return $this->_identifiers;
    }

    public static function createNew()
    {
        $instance = new self();
        $bitid = new BitID();
        $vNewUserId = $bitid->generateNonce(10);
        $instance->setIdCustomer($vNewUserId);
        return $instance;
    }
    
    public function getHiddenCustomerId()
    {
        return \sprintf("%s****%s", \substr($this->getIdCustomer(), 0, 4), \substr($this->getIdCustomer(), -4, 4));
    }
    
    public function setIdentifiers($_identifiers)
    {
        $this->_identifiers = $_identifiers;
    }
        
    private function isPropertyExists($pPropertyName)
    {
        $result = array_key_exists($pPropertyName, $this->_properties);
        return $result;
    }

    public function getPropertyValue($pPropertyName)
    {
        $result = $this->isPropertyExists($pPropertyName);
        if ($result) {
            $prop = $this->_properties[$pPropertyName];
            $result = $prop->getPropertyValue();
        }
        return $result;
    }

    public function getRights()
    {
        return $this->_rights;
    }
    
    public function setRights($rights)
    {
        $this->_rights = $rights;
    }

    public function getProperties()
    {
        return $this->_properties;
    }

    public function setProperties($_properties)
    {
        $vProperties = [];
        foreach ($_properties as $prop) {
            $vProperties +=  [$prop->getPropertyTypeCode() => $prop];
        }
        $this->_properties = $vProperties;
    }
  
    public function getIsSignedIn()
    {
        return !empty($this->m_idCustomer);
    }

    public function init($userId)
    {
        $custDao = new CustomerDao();
        $vCustomerRow = $custDao->findById($userId);
        if (false !==$vCustomerRow) {
            CustomerMapper::map($this, $vCustomerRow);
            $dao = new CustomerRightsDao();
            $vRights = $dao->findById($userId);
            $this->setRights($vRights);
            $dao2 = new CustomerPropertyDao();
            $vProperties = $dao2->findById($userId);
            $this->setProperties($vProperties);
            $vSearch = new IdentifierSearchCriteria(["IdCustomer" => $userId]);
            $dao3 = new IdentifierDao();
            $vIdentifiers = $dao3->find($vSearch);
            $this->setIdentifiers($vIdentifiers);
            return $this;
        } else {
            throw new \LogicException(\sprintf('Customer with Id:%s was not found.', $userId));
        }
    }
        
    public function getXPub()
    {
        return $this->xPub;
    }

    public function getInvoicesCount()
    {
        return $this->InvoicesCount;
    }

    public function setXPub($xPub)
    {
        $this->xPub = $xPub;
    }

    public function setInvoicesCount($InvoicesCount)
    {
        $this->InvoicesCount = $InvoicesCount;
    }

    public function getUserNameAlias()
    {
        if ($this->isPropertyExists("alias_nick")) {
            return $this->getPropertyValue("alias_nick");
        } else {
            return $this->getHiddenCustomerId();
        }
    }
    
    
    public function getUserHash()
    {
        $cId = $this->customer->getIdCustomer();
        $vBegin = substr($cId, 1, 9);
        return $vBegin;
    }

    public function RegisterNewUserId(Identicator $Identity)
    {
        $custDao = new CustomerDao();
        $bitid = new BitID();
        $nonce = $bitid->generateNonce(10);
        $result = $custDao->registerUserId($Identity->getIdentTypeCode(), $Identity->getIdentityKey(), $nonce);
        return $result;
    }
    
    private function renderUserProperties()
    {
        $vResult = [];
        foreach ($this->_properties as $prop) {
            $vResult += [$prop->getPropertyTypeCode() => [$prop->getPropertyValue(), $prop->getPropertyTypeTitle()]];
        }
        return $vResult;
    }
    
    private function renderUserRights()
    {
        $vResult = [];
        foreach ($this->_rights as $right) {
            $vResult += [$right->getRightCode() => $right->getRightDescription()];
        }
        return $vResult;
    }

    private function renderUserIdentifiers()
    {
        $vResult = [];
        foreach ($this->_identifiers as $ident) {
            $vResult += ["IdentType" => $ident->getIdentTypeCode(), "IdentKey" => $ident->getIdentityKey(), "IdentState" => $ident->getAuthenticationState() ];
        }
        return $vResult;
    }
    
    public function getArray()
    {
        $result = parent::getArray();
        $result += ["username" => $this->getUserNameAlias() ];
        $result += ["properties" => $this->getProperties() ];
        $result += ["rights" => $this->getRights() ];
        $result += ["identifiers" => $this->getIdentifiers() ];
        return $result;
    }
    
    public function __toString()
    {
        $result = parent::__toString();
        $result .= \sprintf(" Properties: [%s]", Utils::toJson($this->renderUserProperties()));
        $result .= \sprintf(" Rights: [%s]", Utils::toJson($this->renderUserRights()));
        $result .= \sprintf(" Identifiers: [%s]", Utils::toJson($this->renderUserIdentifiers()));
        return $result;
    }
}
