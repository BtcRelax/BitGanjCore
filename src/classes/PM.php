<?php
namespace BtcRelax;

//require 'PMInterface.php';

// Points manager
class PM implements IPM
{
    private $vLastError = null;

    public function getLastError()
    {
        return $this->vLastError;
    }

    public function setLastError($_lastError = null)
    {
        $this->vLastError = $_lastError;
    }

    /// Products section
    ///
    ///
    public function renderProduct(\BtcRelax\Model\Product $vProduct)
    {
        $result = [];
        $result += ["ProductId" => $vProduct->getProductId()];
        $result += ["ProductName" => $vProduct->getProductName()];
        $result += ["ProductURL" => $vProduct->getDescriptionUrl()];
        $result += ["ProductHTML" => \sprintf("<a href=\"%s\">%s</a>", $vProduct->getDescriptionUrl(), $vProduct->getProductName())];
        return $result;
    }
    
    public function setProduct(\BtcRelax\Model\Product $vProduct)
    {
        try {
            $vProductDao = new \BtcRelax\Dao\ProductDao();
            $vResult = $vProductDao->save($vProduct);
            $this->setLastError();
        } catch (Exception $exc) {
            $this->setLastError(\sprintf("Продукт не был сохранён в системе! Error:%s", $exc->getMessage()));
            \BtcRelax\Log::general(\sprintf("Error while save to DB:%s", $exc->getTraceAsString()), \BtcRelax\Log::WARN);
            $vResult = false;
        }
        return $vResult;
    }
    
    public function getProduct(int $vProductId)
    {
        $vProductDao = new \BtcRelax\Dao\ProductDao();
        $result = $vProductDao->findById($vProductId);
        return $result;
    }
    
    
    public function actionGetFrontshopBookmarks()
    {
        $dao = new \BtcRelax\Dao\BookmarkDao();
        $status = \BtcRelax\Validation\BookmarkValidator::validateStatus('Published');
        $search = new \BtcRelax\Dao\BookmarkSearchCriteria();
        $search->setStatus($status);
        $search->setIsFrontshop(true);
        $bookmarksList = $dao->find($search);
        return $bookmarksList;
    }
        
    public function actionGetOrderBookmarks(\BtcRelax\Model\Order $pOrder)
    {
        $vBookmarks = $pOrder->getBookmarks();
        if (empty($vBookmarks) && is_numeric($pOrder->getIdOrder())) {
            $vSearch = new \BtcRelax\Dao\BookmarkSearchCriteria($pOrder->getIdOrder(), true);
            $vBookmarkDao = new \BtcRelax\Dao\BookmarkDao();
            $vBookmarks = $vBookmarkDao->find($vSearch);
            $pOrder->setBookmarks($vBookmarks);
        }
        return $vBookmarks;
    }
    
    public function renderGetOrderBookmarks(\BtcRelax\Model\Order $pOrder)
    {
        if (empty($pOrder->getState())) {
            return null;
        }
        $vBookmarks = $this->actionGetOrderBookmarks($pOrder);
        $result = [];
        foreach ($vBookmarks  as $cBookmark) {
            \array_push($result, $cBookmark->getBookmarkInfo());
        }
        return $result;
    }
    
    public function createNewPoint(\BtcRelax\Model\User $pUser, $params)
    {
        $result = false;
        $vAM = \BtcRelax\Core::createAM();
        if ($vAM->isUserHasRight('ADD_POINT', $pUser)) {
            $vBookmark = new \BtcRelax\Model\Bookmark($params);
            if ($vBookmark instanceof \BtcRelax\Model\Bookmark) {
                $vBookmark->setIdDroper($pUser->getIdCustomer());
                $vBookmarkDao = new \BtcRelax\Dao\BookmarkDao();
                $result = $vBookmarkDao->save($vBookmark);
                if (is_int($result) && $result === 1062) {
                    $this->setLastError("Bookmark exists at that location!");
                    $result = false;
                }
            }
        } else {
            $this->setLastError("User access denied!");
        }
        return $result;
    }

    public static function bookmarkById(int $vBookmarkId): \BtcRelax\Model\Bookmark
    {
        $dao = new \BtcRelax\Dao\BookmarkDao();
        $selectedBookmark = $dao->findById($vBookmarkId);
        if ($selectedBookmark instanceof \BtcRelax\Model\Bookmark) {
            return $selectedBookmark;
        } else {
            throw new \BtcRelax\Exception\NotFoundException(\sprintf("Bookmark id:%s not found !", $vBookmarkId));
        }
    }

    public function getBookmarkById(int $vBookmarkId)
    {
        $dao = new \BtcRelax\Dao\BookmarkDao();
        $selectedBookmark = $dao->findById($vBookmarkId);
        if ($selectedBookmark instanceof \BtcRelax\Model\Bookmark) {
            $this->setLastError();
        } else {
            $this->setLastError("Bookmark not found!");
        }
        return $selectedBookmark;
    }

    public function unlockBookmarkByOrder($pOrder, int $vBookmarkId)
    {
        $result = false;
        $vBookmarks = $this->actionGetOrderBookmarks($pOrder);
        foreach ($vBookmarks  as $cBookmark) {
            if (empty($cBookmark->getUnlockDate()) && ($cBookmark->getIdBookmark() === $vBookmarkId)) {
                $vBookmarkDao = new \BtcRelax\Dao\BookmarkDao();
                $vUnlockedBookmark = $vBookmarkDao->unlockBookmark($cBookmark);
                if ($vUnlockedBookmark instanceof \BtcRelax\Model\Bookmark) {
                    $pOrder->addBookmark($vUnlockedBookmark);
                    $result = true;
                }
            }
        }
        return $result;
    }

    public function setBookmarkCatchedByOrder($pOrder, $vBookmarkId)
    {
        $result = false;
        $vBookmarks = $this->actionGetOrderBookmarks($pOrder);
        foreach ($vBookmarks  as $cBookmark) {
            if ($cBookmark->getIdBookmark() === $vBookmarkId) {
                $vBookmarkDao = new \BtcRelax\Dao\BookmarkDao();
                $vCatchedBookmark = $vBookmarkDao->setBookmarkCatched($cBookmark);
                if ($vCatchedBookmark instanceof \BtcRelax\Model\Bookmark) {
                    $pOrder->addBookmark($vCatchedBookmark);
                    $result = true;
                }
            }
        }
        return $result;
    }

    /// Update  point info if that possible
    /// Return object Bookmark if success, and false if fail.
    /// When fail, set last error.
    public function updatePointById(\BtcRelax\Model\User $vUser, int $vPointId, $params)
    {
        $result = false;
        $vBookmark = $this->getBookmarkById($vPointId);
        if ($vBookmark instanceof \BtcRelax\Model\Bookmark) {
            if (($vBookmark->getState() === \BtcRelax\Model\Bookmark::STATUS_PREPARING) || ($vBookmark->getState() === \BtcRelax\Model\Bookmark::STATUS_SALED)) {
                $vAM = \BtcRelax\Core::createAM();
                if ($vBookmark->getIdDroper() === $vUser->getIdCustomer() && $vAM->isUserHasRight('EDIT_POINT', $vUser)) {
                    $vIsChanged = false;
                    foreach ($params as $key => $value) {
                        switch ($key) {
                            case 'link':
                                if ($vBookmark->getLink() !== $value) {
                                    $vBookmark->setLink($value);
                                    $vIsChanged = true;
                                }
                                break;
                            case 'description':
                                if ($vBookmark->getDescription() !== $value) {
                                    $vBookmark->setDescription($value);
                                    $vIsChanged = true;
                                }
                                break;
                            case 'region':
                                if ($vBookmark->getRegionTitle() !== $value) {
                                    $vBookmark->setRegionTitle($value);
                                    $vIsChanged = true;
                                }
                                break;
                            case 'price':
                                $vPrice = (float) $value;
                                if ($vBookmark->getCustomPrice() !== $vPrice) {
                                    $vBookmark->setCustomPrice($value);
                                    $vIsChanged = true;
                                }
                                break;
                            case 'title':
                                $vAT = \rawurldecode($value);
                                if ($vBookmark->getAdvertiseTitle() !== $vAT) {
                                    $vBookmark->setAdvertiseTitle($vAT);
                                    $vIsChanged = true;
                                }
                                break;
                            default:
                                break;
                        }
                    };
                    if ($vIsChanged) {
                        $vBookmarkDao = new \BtcRelax\Dao\BookmarkDao();
                        $result = $vBookmarkDao->save($vBookmark);
                    } else {
                        $result = $vBookmark;
                    }
                } else {
                    $this->setLastError("You are not a owner of bookmark, or has not rights");
                }
            } else {
                $this->setLastError("Bookmark can be edited only in Prepare state.");
            }
        }
        if ($result) {
            $this->setLastError();
        }
        return $result;
    }

    public function setNewState(\BtcRelax\Model\User $vUser, int $vPointId, string $vNewState)
    {
        $result = false;
        $vBookmark = $this->getBookmarkById($vPointId);
        if ($vBookmark instanceof \BtcRelax\Model\Bookmark) {
            $vAM = \BtcRelax\Core::createAM();
            if (($vBookmark->getIdDroper() === $vUser->getIdCustomer()) && $vAM->isUserHasRight('EDIT_POINT', $vUser)) {
                $vPosibility = $vBookmark->validateNewState($vNewState);
                $result = empty($vPosibility);
                if ($result) {
                    $vBookmark->setState($vNewState);
                    $vBookmarkDao = new \BtcRelax\Dao\BookmarkDao();
                    $result = $vBookmarkDao->save($vBookmark);
                } else {
                    $this->setLastError($vPosibility);
                }
            } else {
                $this->setLastError("You are not a owner of bookmark, or has not rights");
            }
        }
        if ($result) {
            $this->setLastError();
        }
        return $result;
    }
}
