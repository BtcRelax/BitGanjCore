<?php

namespace BtcRelax\Model;

class Bookmark {

    const STATUS_PREPARING = "Preparing";
    const STATUS_CHECKING = "Checking";
    const STATUS_REJECTED = "Rejected";
    const STATUS_READY = "Ready";
    const STATUS_PUBLISHED = "Published";
    const STATUS_SALED = "Saled";
    const STATUS_LOST = "Lost";
    const STATUS_PREORDERED = "PreOrdered";
    const STATUS_CATCHED = "Catched";
    
    private $pIdBookmark = null;
    private $pCreateDate;
    private $pIdOrder;
    private $pEndDate;
    private $pLatitude;
    private $pLongitude;
    private $pLink;
    private $pDescription = "";
    private $pRegionTitle;
    private $pCustomPrice;
    private $pPriceCurrency;
    private $pAdvertiseTitle;
    private $pUnlockDate;
    private $pState = self::STATUS_PREPARING;
    private $pIdDroper;
    private $pTargetAddress;
    private $pBookmarkHash;
    
    public function __construct($params = null) {
        $this->pState = self::STATUS_PREPARING;
        $this->pIdBookmark = null;
        $this->pDescription = "";
        if (!is_null($params)) {
            foreach ($params as $key => $value) {
                switch ($key) {
                    case 'title':
                        $this->pAdvertiseTitle = \rawurldecode($value);
                        break;
                    case 'price':
                        $this->pCustomPrice = $value;
                        break;
                    case 'location':
                        $this->pLatitude = $value['latitude'];
                        $this->pLongitude = $value['longitude'];
                        break;
                    case 'orderid':
                        $this->pIdOrder = $value['orderid'];
                        $this->pState = self::STATUS_SALED;
                        break;
                    default:
                        break;
                }
            }
        }
    }
    
    public function getBookmarkInfo()
    {
        $result = null;
        if ($this->isUnlocked()){
            $result = ['id' => $this->getIdBookmark(), 
               'Title' => $this->getAdvertiseTitleHTML(), 
               'RegionTitle' => $this->getRegionTitle(), 
               'Unlocked' => $this->isUnlocked(),
               'Latitude' => $this->getLatitude(),
               'Longitude' => $this->getLongitude(),
               'LocationLink' => $this->getLocationLink(),
               'Link' => $this->getLink(),
               'Description' => $this->getDescription(),
               ];
        } else {
            $result = ['id' => $this->getIdBookmark(), 
               'Title' => $this->getAdvertiseTitleHTML(), 
               'RegionTitle' => $this->getRegionTitle(), 
               'Unlocked' => $this->isUnlocked() ];        
        }
        if (!empty($this->pIdOrder)) {
             $vIdOrder = $this->getIdOrder();
             $result += ['OrderId' => intval($vIdOrder) ];
        }
        return $result;
    }
    
    public function getTargetAddress() {
        return $this->pTargetAddress;
    }
    
    public function isUnlocked():bool {
        return $this->getUnlockDate() == null? false : true ;
    }

    public function setTargetAddress($pTargetAddress) {
        $this->pTargetAddress = $pTargetAddress;
    }

    public function getBookmarkHash() {
        return $this->pBookmarkHash;
    }

    function setBookmarkHash($pBookmarkHash) {
        $this->pBookmarkHash = $pBookmarkHash;
    }

        
    public function getIdBookmark() {
        return $this->pIdBookmark !== null? (int) $this->pIdBookmark:null ;
    }

    function setIdBookmark($pValue) {
        $this->pIdBookmark = $pValue;
    }

    function getState() {
        return $this->pState;
    }

    function setState($pValue) {
        if (($pValue === self::STATUS_LOST) || ($pValue === self::STATUS_CATCHED)) { $this->setEndDate(new \DateTime()); }
        if ($pValue === self::STATUS_PUBLISHED) { $this->setIdOrder(null); }
        $this->pState = $pValue;
    }

    function getCreateDate() {
        return $this->pCreateDate;
    }

    function setCreateDate($pValue) {
        $this->pCreateDate = $pValue;
    }

    function getIdOrder() {
        return $this->pIdOrder;
    }
    
    function getOrder():\BtcRelax\Model\Order {
        return \BtcRelax\OM::orderById($this->pIdOrder);
    } 
    
    function setIdOrder($pValue) {
        $this->pIdOrder = $pValue;
    }

    function getLatitude() {
        return $this->pLatitude;
    }

    function setLatitude($pValue) {
        $this->pLatitude = $pValue;
    }

    function getLongitude() {
        return $this->pLongitude;
    }

    function setLongitude($pValue) {
        $this->pLongitude = $pValue;
    }

    function getLink() {
        return $this->pLink;
    }

    function setLink($pValue) {
        $this->pLink = $pValue;
    }

    function getDescription() {
        return $this->pDescription;
    }

    function setDescription($pValue) {
        $this->pDescription = $pValue;
    }

    function getRegionTitle() {
        return $this->pRegionTitle;
    }

    function setRegionTitle($pValue) {
        $this->pRegionTitle = $pValue;
    }

    function getIdDroper() {
        return $this->pIdDroper;
    }

    function setIdDroper($pValue) {
        $this->pIdDroper = $pValue;
    }

    function getCustomPrice():float {
        return $this->pCustomPrice;
    }

    function setCustomPrice($pValue) {
        $this->pCustomPrice = (float)$pValue;
    }
    
    function getPriceCurrency() {
        return $this->pPriceCurrency ?? \BtcRelax\RE::UAH ;
    }

    function setPriceCurrency($pPriceCurrency) {
        $this->pPriceCurrency = $pPriceCurrency;
    }

    function getAdvertiseTitle() {
        return $this->pAdvertiseTitle;
    }
    
    function getAdvertiseTitleHTML() {
        $result = $this->getAdvertiseTitle();
        if (\BtcRelax\Utils::isJson($result)) {
            $result = $this->prepareTitle($result);
        }
        return $result;
    }
    
    function setAdvertiseTitle($pValue) {
        $this->pAdvertiseTitle = $pValue;
    }

    function getUnlockDate() {
        return $this->pUnlockDate;
    }

    function setUnlockDate($pValue) {
        $this->pUnlockDate = $pValue;
    }

    function getEndDate() {
        return $this->pEndDate;
    }

    function setEndDate($pValue) {
        $this->pEndDate = $pValue;
    }

    public static function allStatuses() {
        return [
            self::STATUS_PREPARING,
            self::STATUS_CHECKING,
            self::STATUS_REJECTED,
            self::STATUS_READY,
            self::STATUS_PUBLISHED,
            self::STATUS_SALED,
            self::STATUS_LOST,
            self::STATUS_CATCHED,
            self::STATUS_PREORDERED
        ];
    }

    
    
    public function getStateInfo() {
        $result = ["bookmarkId" => $this->getIdBookmark(), "bookmarkState" => $this->getState()];
        //if (!\is_null($this->getEndDate())) { $result += [ "bookmarkEndDate" => $this->getEndDate()]; }
		if (!\is_null($this->getEndDate())) { $result += [ "bookmarkEndDate" => \BtcRelax\Utils::formatDateTime($this->getEndDate())]; }
        if (!\is_null($this->getIdOrder())) { 
            $vOrderId = \intval($this->getIdOrder());
            $result += [ "bookmarkOrderId" => $vOrderId ] ;
			$vOM = \BtcRelax\Core::createOM();
			$vOrderInfo = $vOM->getOrderInfoById($vOrderId);
            $result += [ "bookmarkOrder" => $vOrderInfo ];
        }
        return  $result;
    }

//    function getOrderById(int $pOrderId){
//        $vOM = \BtcRelax\Core::createOM();
//        return $vOM->getOrderInfoById($pOrderId);
//    }
    
    function prepareTitle($pTitle) {
        $titlesArray = \BtcRelax\Utils::getJson($pTitle); $result = null;
        foreach ($titlesArray as $value) {
            $vProductId = $value['ProductId'];
            $vPM = \BtcRelax\Core::createPM();
            $vProduct = $vPM->getProduct($vProductId);
            $vTitle = $value['Title'];
            $result = \sprintf('<a href="%s" target="_blank">%s</a> %s', $vProduct->getDescriptionUrl() , $vProduct->getProductName() , $vTitle  );
        }
        return $result;
    }
    
    public function GetPublicForm() {
        $lLink = 'Купить';
        $lTitle = $this->getRegionTitle();
        $lAdverTitle = $this->getAdvertiseTitleHTML();
        $lLocalPrice = $this->getCustomPrice();
        $vBookmarkId = $this->getIdBookmark();
        $result = sprintf("<div class=\"bookmark col-xs-12 col-sm-6 col-md-4 col-lg-3\">
							<form id=\"frmBookmarkId%s\" method=\"post\" action=\"#\" >
								<div class=\"bookmark-inner \">
									<div class=\"bookmark-head \">
                                                                            <p>%s</p>
                                                                            <p><span class=\"badge\">%s</span></p>
                                                                            <p class=\"bookmark-price\" >~%s UAH</p>
                                                                        </div>
									<div class=\"bookmark-body \"><center><p>
                                                                        <button type=\"button\" name=\"getBookmark\" value=\"%s\"  onclick=\"JApp.getBookmark(%s);\" class=\"button-buy button-buy-shadow button-buy-pulse \">%s</button>
									</p></center></div>
								</div>
							</form>
							</div>", $this->getBookmarkHash(), $lAdverTitle, $lTitle, $lLocalPrice, $vBookmarkId, $vBookmarkId , $lLink);
        return $result;
    }
			
    public function getLocationLink() {
        if (isset($this->pLatitude) && isset($this->pLongitude)) {
            if (is_numeric($this->getLatitude()) && is_numeric($this->getLongitude())) {
                return \sprintf("http://maps.google.com/maps?f=q&q=loc:%s,%s&t=k&spn=0.5,0.5",$this->getLatitude(),$this->getLongitude());
            }
        }
    }

    public function setDroperId($pDroperId) {
        $this->pIdDroper = $pDroperId;
    }

    public function setRegionNames($pRegionTitleUkr, $pRegionTitleRus) {
        $this->pRegionTitle = $pRegionTitleUkr;
        $this->pRegionTitle_ru = $pRegionTitleRus;
    }

    public function setLocation($pLatitude, $pLongitude) {
        $this->pLatitude = $pLatitude;
        $this->pLongitude = $pLongitude;
    }

    public function setRegion($selectedRegion) {
        $curReg = Region::getRegionList();
        $regionInfo = $curReg[$selectedRegion];
        $this->pRegionTitle = $regionInfo['TitleUkr'];
        $this->pRegionTitle_ru = $regionInfo['TitleRus'];
    }

    public function saveToDb() {
        $result = false;
        if ($this->pIdBookmark === null)
        {
            $dao = \BtcRelax\BookmarkDao();
            $daoRes = $dao->createNew($this);
            if ($daoRes instanceof \BtcRelax\Model\Bookmark)
            {
                $result = $daoRes;
            }
            // Need to insert, else update
        }
        return ($result);
    }

    public function validateNewState($vNewState) {
        $vCurrentState = $this->getState(); $result = '';
        if ($vCurrentState === $vNewState) {
            $result = \sprintf("Point already in state:%s", $vCurrentState);
        } else {
        switch ($vCurrentState) {
                     case \BtcRelax\Model\Bookmark::STATUS_PREPARING:
                         if (!($vNewState == \BtcRelax\Model\Bookmark::STATUS_PUBLISHED ) && !($vNewState == \BtcRelax\Model\Bookmark::STATUS_REJECTED) 
                                 && !($vNewState == \BtcRelax\Model\Bookmark::STATUS_LOST ) && !($vNewState == \BtcRelax\Model\Bookmark::STATUS_SALED ))
                         {
                             $result = \sprintf("Changing state from:%s only allowed to Published or Rejected  or Lost! Or Saled, when sale from hands.",$vCurrentState);
                         } else {
                             if (empty($this->pLink) || empty($this->pRegionTitle) || empty($this->pAdvertiseTitle)) 
                             {
                                $result = "Bookmark incompleate! Field Link or RegionTitle or Advertise, still empty. Operation forbiden!"; 
                             }
                         }
                    break;
                     case \BtcRelax\Model\Bookmark::STATUS_SALED:
                         if (($vNewState !== \BtcRelax\Model\Bookmark::STATUS_LOST) && ($vNewState !== \BtcRelax\Model\Bookmark::STATUS_CATCHED) && ($vNewState  !== \BtcRelax\Model\Bookmark::STATUS_PREPARING))
                         {
                             $result = \sprintf("Changing state from:%s only Lost or Catched or Preparing!",$vCurrentState);
                         }
                         break;                     
                     case \BtcRelax\Model\Bookmark::STATUS_PUBLISHED:
                         if (!($vNewState == \BtcRelax\Model\Bookmark::STATUS_PREPARING ) && !($vNewState == \BtcRelax\Model\Bookmark::STATUS_REJECTED) 
                            && !($vNewState == \BtcRelax\Model\Bookmark::STATUS_LOST ) &&  ($vNewState !== \BtcRelax\Model\Bookmark::STATUS_SALED))
                         {
                             $result = \sprintf("Changing state from:%s only allowed to Preparing or Rejected or Lost!",$vCurrentState);
                         }
                         break;
                     case \BtcRelax\Model\Bookmark::STATUS_REJECTED:
                         if (!($vNewState == \BtcRelax\Model\Bookmark::STATUS_PREPARING ) && !($vNewState == \BtcRelax\Model\Bookmark::STATUS_LOST ) )
                         {
                             $result = \sprintf("Changing state from:%s only allowed to Preparing or Lost!",$vCurrentState);
                         }
                         break;
                     default:
                         $result = \sprintf("Changing state from %s to %s has no rule!",$vCurrentState, $vNewState);
                         break;
                }
        }
        return $result;                
    }

}
