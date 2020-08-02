<?php
namespace BtcRelax\Model;

class Order {
    

    const STATUS_CREATED = "Created";

    /// Before create where we don't know wich payment provider will be selected.
    /// But when we know what payment providers supported
    const STATUS_PREPARING = "Preparig";     
    
    const STATUS_CONFIRMED = "Confirmed";
    const STATUS_PAID = "Paid";
    const STATUS_WAIT_FOR_PAY = "WaitForPayment"; 
    const STATUS_CANCELED = "Canceled"; 
    const STATUS_FINISHED = "Finished";
    
    const DELIVERY_HOTPOINT = "HotHiddenPoint";
    const DELIVERY_ORDEREDPOINT =  "OrderedHotPoint";
    const DELIVERY_POSTOFFICE = "PostOffice";
    
    private  $pIdOrder;
    private  $pCreateDate;
    private  $pEndDate;
    private  $pState ;
    private  $pCreator;
    private  $pDeliveryMethod = self::DELIVERY_HOTPOINT;
    private  $pLastError;    

    /// Variables with session scope
    public  $pBookmarks = [];
    public $pInvoices = [];


    public function __construct() {
    }

    public function createNew(\BtcRelax\Model\User $pCreator, $pDeliveryMethod = null) {
        $this->pCreator = $pCreator->getIdCustomer();
        $this->pDeliveryMethod = $pDeliveryMethod === null? self::DELIVERY_HOTPOINT : $pDeliveryMethod;
        $this->pCreateDate = new \DateTime();       
    }    
    
    public function isHasBookmarkId(int $pBookmarkId)    {
        if (\count($this->pBookmarks)>0 )
        {
            foreach ($this->pBookmarks as $vBookmark)
            {
                if ($pBookmarkId === $vBookmark->getIdBookmark())
                {
                    return true;
                }
            }
            }
        return false;
    }    

    public function getBookmarks(): array {
        return $this->pBookmarks;
    }
    
    public function getBookmark(): \BtcRelax\Model\Bookmark   {
        if (\count($this->pBookmarks) === 1) { $result = $this->pBookmarks[0]; }
        return $result;
    }
    
    public function setBookmarks(array $pBookmarks) {
        $this->pBookmarks = $pBookmarks;
    }

    public function addBookmark(\BtcRelax\Model\Bookmark $pBookmark)  {
        $vIsSet = false;
        foreach ($this->pBookmarks as $key => $value) {
            if ($value->getIdBookmark() === $pBookmark->getIdBookmark())
                {
                    unset($this->pBookmarks[$key]);
                    array_push($this->pBookmarks , $pBookmark);    
                    $vIsSet = true;
                    break;
                }
            }
        if (!$vIsSet) { array_push($this->pBookmarks , $pBookmark); };
        return $this->getBookmarks();        
    }
    
    public function getInvoices() {
        return $this->pInvoices;
    }

    public function addInvoice(\BtcRelax\Model\Invoice $pInvoice )    {
        array_push($this->pInvoices , $pInvoice);
        return $this->pInvoices;        
    }

    public function setInvoices($pInvoices) {
        $this->pInvoices = $pInvoices;
    }
        


    function getOrderHash() {
        return $this->pOrderHash;
    }

    function setOrderHash($pOrderHash) {
        $this->pOrderHash = $pOrderHash;
    }

        
        
    public function getCreator() {
            return $this->pCreator;
        }

    public function setCreator($pCreator) {
            $this->pCreator = $pCreator;
        }

    public function getDeliveryMethod() {
        return $this->pDeliveryMethod;
    }


    public function setDeliveryMethod($pDeliveryMethod) {
        $this->pDeliveryMethod = $pDeliveryMethod;
    }

    public static function allStatuses() {
        return [
            self::STATUS_CONFIRMED,
            self::STATUS_CREATED,
            self::STATUS_PREPARING,
            self::STATUS_CANCELED,
            self::STATUS_FINISHED,
            self::STATUS_PAID,
            self::STATUS_WAIT_FOR_PAY
        ];
    }
    
    public function getLastError() {
        return $this->pLastError;
    }

    public function setLastError($pLastError) {
        $this->pLastError = $pLastError;
    }

  
    public function getState() {
        return $this->pState;
    }

    public function setState($pState) {
        $this->pState = $pState;
    }

    function getIdOrder()  {
        return $this->pIdOrder;
    }
    
    public function setIdOrder($pIdOrder) {
        if ($this->pIdOrder !== null
                && $this->pIdOrder != $pIdOrder) {
            throw new Exception('Cannot change identifier to ' . $pIdOrder . ', already set to ' . $this->pIdOrder);
        }
        if ($pIdOrder === null) {
            $this->pIdOrder = null;
        } else {
            $this->pIdOrder = (int) $pIdOrder;
        }   
    }
           
    function getCreateDate ()    {
	return $this->pCreateDate;
    }
			 
    function setCreateDate(\DateTime $pCreateDate)
    {
	$this->pCreateDate = $pCreateDate;
    }
    
    function getEndDate ()
    {
	return $this->pEndDate;
    }
			 
    function setEndDate($pEndDate)
    {
	$this->pEndDate = $pEndDate;
    }
    
    public function __toString() {
        $result = null;
        if (!empty($this->pIdOrder)) {$result .= \sprintf("OrderId:%s|", $this->pIdOrder ); }
        if (!empty($this->pState)) {$result .= sprintf("State:%s|", $this->pState); }
        return $result;
    }
}
