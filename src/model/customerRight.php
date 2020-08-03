<?php
namespace BtcRelax\Model;

class CustomerRight
{

        // private members
    protected $m_idCustomer = null;
    protected $m_RightCode;
    protected $m_RightDescription;

    public function __construct()
    {
    }

    /**
    * Constructor
    *
    * Example:
    * $myCustomers = Customers::WithParams( val1, val2,.. );
    */
    public static function WithParams($idCustomer, $RightCode)
    {
        $instance = new self();
        return $instance;
    }
                 
    //		public function __toString() {
    //			return "Id:" . $this->m_idCustomer;
    //		}
                
    public function getIdCustomer()
    {
        return $this->m_idCustomer;
    }

    public function getRightCode()
    {
        return $this->m_RightCode;
    }

    public function getRightDescription()
    {
        return $this->m_RightDescription;
    }

    public function setIdCustomer($m_idCustomer)
    {
        $this->m_idCustomer = $m_idCustomer;
    }

    public function setRightCode($m_RightCode)
    {
        $this->m_RightCode = $m_RightCode;
    }

    public function setRightDescription($m_RightDescription)
    {
        $this->m_RightDescription = $m_RightDescription;
    }

    public function __toString()
    {
        $vResult = \sprintf('Code:%s', $this->getRightCode());
        return $vResult;
    }
}
