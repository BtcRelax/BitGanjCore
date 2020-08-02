<?php

namespace BtcRelax\Dao;

use BtcRelax\Dao\BookmarkSearchCriteria;
use BtcRelax\Log;
use BtcRelax\Mapping\BookmarkMapper;
use BtcRelax\Model\Bookmark;

final class BookmarkDao extends BaseDao {

    public function insert(Bookmark $newBookmark) {
        $result = false;
        $newBookmark->setCreateDate(new \DateTime());
        $sql = "INSERT INTO `Bookmarks` (`idBookmark`,`CreateDate`, `AdvertiseTitle`,`RegionTitle`,`CustomPrice`,`PriceCurrency`,"
                . "`IdDroper`,`Latitude`,`Longitude`,`Link`,`Description`) "
                . "VALUES (:idBookmark, :CreateDate,  :AdvertiseTitle, :RegionTitle, :CustomPrice, :PriceCurrency,"
                . ":IdDroper, :Latitude, :Longitude, :Link, :Description)";
        $rawParams = $this->getParams($newBookmark);
        $statement = $this->getDb()->prepare($sql);
        $statement->bindParam(':idBookmark',$rawParams[':idBookmark']);
	$statement->bindParam(':CreateDate',$rawParams[':CreateDate']);
        $statement->bindParam(':AdvertiseTitle',$rawParams[':AdvertiseTitle']);
        $statement->bindParam(':RegionTitle',$rawParams[':RegionTitle']);
        $statement->bindParam(':CustomPrice',$rawParams[':CustomPrice']);
        $statement->bindParam(':PriceCurrency',$rawParams[':PriceCurrency']);
        $statement->bindParam(':IdDroper',$rawParams[':IdDroper']);
        $statement->bindParam(':Latitude',$rawParams[':Latitude']);
        $statement->bindParam(':Longitude',$rawParams[':Longitude']);
        $statement->bindParam(':Link',$rawParams[':Link']);
        $statement->bindParam(':Description',$rawParams[':Description']);
        $statement->execute();
        if (!$statement->rowCount()) {
            $errMsg = $statement->errorInfo();
            if ($errMsg[1] === 1062) 
            {
                $result = $errMsg[1];
            } else { throw new \BtcRelax\Exception\AssignBookmarkException(\sprintf('Bookmark registering Error:%s"', $errMsg[2] )); }
        } else {
             $newPointId = $this->getDb()->lastInsertId();
             $result = $this->findById($newPointId);             
        }
        return $result;
    }

    public function save(Bookmark $pBookmark) {
        if (empty($pBookmark->getIdBookmark())) { return $this->insert($pBookmark);}
        return $this->update($pBookmark);
    }

    public function update(Bookmark $pBookmark) {
        $sql = 'UPDATE `Bookmarks` SET                     
                    `State` = :State,
                    `idOrder` = :idOrder,
                    `AdvertiseTitle` = :AdvertiseTitle,
                    `RegionTitle` = :RegionTitle,
                    `CustomPrice` = :CustomPrice,
                    `PriceCurrency` = :PriceCurrency,
                    `Latitude` = :Latitude,
                    `Longitude` = :Longitude,
                    `Link` = :Link,
                    `Description` = :Description,
                    `EndDate` = :EndDate
                    WHERE `idBookmark` = :idBookmark AND `IdDroper` = :IdDroper;';
        return $this->execute($sql, $pBookmark);
    }

    /**
     * @return \Model\Bookmark
     * @throws Exception when cannot update or find what to update
     */
    public function execute($sql, Bookmark $pBookmark) {
        $statement = $this->getDb()->prepare($sql);
        $vParams = $this->getParams($pBookmark);
        $this->executeStatement($statement, $vParams);
        if (!$pBookmark->getIdBookmark()) {
            $newBookmarkId = $this->getDb()->lastInsertId();
            return $this->findById($newBookmarkId);
        }
        if (!$statement->rowCount()) {
            throw new \BtcRelax\Exception\NotFoundException('Error updtating bookmark Id: "' . $pBookmark->getIdBookmark());
        }
        return $pBookmark;
    }

    public function getParams(\BtcRelax\Model\Bookmark $pBookmark) {
        $params = [
            ':idBookmark' => $pBookmark->getIdBookmark(),
            ':State' => $pBookmark->getState(),
            ':idOrder' => $pBookmark->getIdOrder(),
            ':AdvertiseTitle' => $pBookmark->getAdvertiseTitle(),
            ':RegionTitle' => $pBookmark->getRegionTitle(),
            ':CustomPrice' => $pBookmark->getCustomPrice(),
            ':PriceCurrency' => $pBookmark->getPriceCurrency(),
            ':IdDroper' => $pBookmark->getIdDroper(),
            ':Latitude' => $pBookmark->getLatitude(),
            ':Longitude' => $pBookmark->getLongitude(),
            ':Link' => $pBookmark->getLink(),
            ':Description' => $pBookmark->getDescription(),
        ];
        /* @var $vEndDate type */
        $vCreateDate = $pBookmark->getCreateDate(); $vEndDate = $pBookmark->getEndDate();
        if (is_null($pBookmark->getIdBookmark()) && !empty($vCreateDate)) { 
            $params += [':CreateDate' => self::formatDateTime($vCreateDate)];
            };
        if (!is_null($vEndDate)) 
            { $params += [':EndDate' => self::formatDateTime($vEndDate)];} 
            else { $params += [':EndDate' => null ]; }; 
        return $params;
    }

    public function find(BookmarkSearchCriteria $search = null) {
        $result = [];
        $cnt = 0;
        foreach ($this->query($this->getFindSql($search)) as $row) {
            $bookmark = new Bookmark();
            \BtcRelax\Mapping\BookmarkMapper::map($bookmark, $row);
            $cnt = $cnt + 1;
            $result[$cnt] = $bookmark;
        }
        return $result;
    }

    private function getFindSql(BookmarkSearchCriteria $search = null) {
        $vTable = "Bookmarks";
        $sql = "SELECT `idBookmark`, `CreateDate`, `EndDate`, `State`, `idOrder`, `AdvertiseTitle`, `RegionTitle`, `CustomPrice`, `PriceCurrency`,"
                . " `IdDroper`, `UnlockDate`, `Latitude`, `Longitude`, `Link`, `Description` FROM  ";
        if ($search !== null)
        {
            if ($search->getIsFrontshop())
            {
                $vTable = "vwBookmarks";
            }
        };
        $sql = \sprintf("%s `%s`", $sql, $vTable); 
        $orderBy = ' CreateDate  ';
        $filter = '';
        if ($search !== null) {
            if (!empty($search->getOrderId())) {
                $filter = $this->addToFilter($filter, \sprintf(' idOrder = %d', $search->getOrderId()));
            }
            if ($search->getStatus() !== null) {
                //$sql .= 'AND State = ' . $this->getDb()->quote($search->getStatus());
                switch ($search->getStatus()) {

                    case Bookmark::STATUS_PREPARING:
                        $where = 'Preparing';
                        break;
                    case Bookmark::STATUS_CHECKING:
                        $where = 'Checking';
                        break;
                    case Bookmark::STATUS_PUBLISHED:
                        $where = 'Published';
                        break;
                    default:
                        throw new \BtcRelax\Exception\NotFoundException('No order for status: ' . $search->getStatus());
                }
                $filter = $this->addToFilter($filter, \sprintf('State = \'%s\'', $where));
            }
        }
        $sql .= \sprintf('%s ORDER BY %s', $filter, $orderBy);
        $msg = \sprintf('Final query generated by BookmarkDao is: %s', $sql);
        \BtcRelax\Log::general($msg, Log::INFO);
        return $sql;
    }

    public function findById($id) {
        $query = \sprintf("SELECT `idBookmark`, `CreateDate`, `EndDate`, `State`, `idOrder`, `AdvertiseTitle`, `RegionTitle`, `CustomPrice`,"
                . "`PriceCurrency`, `IdDroper`, `UnlockDate`, `Latitude`, `Longitude`, `Link`, `Description`"
                . " FROM `Bookmarks` WHERE idBookmark = '%s' LIMIT 1 ", $id);
        Log::general(\sprintf("Result query:%s", $query), Log::INFO);
        $row = parent::query($query)->fetch();
        if (!$row) {
            Log::general("Searched bookmark id, not found!", Log::ERROR);
            return null;
        }
        $bookmark = new Bookmark();
        BookmarkMapper::map($bookmark, $row);
        return $bookmark;
    }
    
    public function unlockBookmark(\BtcRelax\Model\Bookmark $pBookmark) {
        $vId = $pBookmark->getIdBookmark();
        $vUnlockDate = new \DateTime();
        $pBookmark->setUnlockDate($vUnlockDate);
        $vUnlockDate = $this->formatDateTime($vUnlockDate);
        $pBookmark->setState(\BtcRelax\Model\Bookmark::STATUS_SALED);
            $sql = 'UPDATE `Bookmarks` SET `State` = :State, `UnlockDate` = :UnlockDate
                     WHERE `idBookmark` = :idBookmark AND `IdDroper` = :IdDroper 
                     AND `UnlockDate` IS NULL;';
                $statement = $this->getDb()->prepare($sql);
		$rawParams = $this->getParams($pBookmark);
		$statement->bindParam(':State',$rawParams[':State'], \PDO::PARAM_STR,10);
		$statement->bindParam(':UnlockDate',$vUnlockDate);
		$statement->bindParam(':idBookmark',$rawParams[':idBookmark'],\PDO::PARAM_INT);
		$statement->bindParam(':IdDroper',$rawParams[':IdDroper'],\PDO::PARAM_STR,34);
                $statement->execute();
        if (!$statement->rowCount()) {
            $errMsg = $statement->errorInfo();
            throw new \BtcRelax\Exception\AssignBookmarkException(\sprintf('Bookmark:%s cant be unlocked! Error:%s"', $pBookmark->getIdBookmark(), $errMsg[2] ));
        }
        return $this->findById($vId);
    }

    /**
     * @return \Model\Bookmark
     * Update fields OrderId in State of bookmark, to corresponding order and preordered, 
     * @throws Exception when cannot update or find what to update
     */
    public function assignBookmarkToOrder(\BtcRelax\Model\Bookmark $pBookmark, int $idOrder) {
        $vId = $pBookmark->getIdBookmark();
        $pBookmark->setState(\BtcRelax\Model\Bookmark::STATUS_PREORDERED);
	$pBookmark->setIdOrder($idOrder);
            $sql = 'UPDATE `Bookmarks` SET 
                    `State` = :State,
                    `idOrder` = :idOrder
                     WHERE `idBookmark` = :idBookmark AND `IdDroper` = :IdDroper 
                     AND `idOrder` IS NULL;';
                $statement = $this->getDb()->prepare($sql);
		$rawParams = $this->getParams($pBookmark);
		$statement->bindParam(':State',$rawParams[':State'], \PDO::PARAM_STR,10);
		$statement->bindParam(':idOrder',$rawParams[':idOrder'],\PDO::PARAM_INT);
		$statement->bindParam(':idBookmark',$rawParams[':idBookmark'],\PDO::PARAM_INT);
		$statement->bindParam(':IdDroper',$rawParams[':IdDroper'],\PDO::PARAM_STR,34);
                $statement->execute();
        if (!$statement->rowCount()) {
            $errMsg = $statement->errorInfo();
            throw new \BtcRelax\Exception\AssignBookmarkException(\sprintf('Bookmark:%s cant assign to order:%s !Error:%s"', $pBookmark->getIdBookmark(), $pBookmark->getIdOrder(),$errMsg[2] ));
        }
        return $this->findById($vId);
    }

    public function setBookmarkCatched($pBookmark) {
        $vId = $pBookmark->getIdBookmark();
        $pBookmark->setState(\BtcRelax\Model\Bookmark::STATUS_CATCHED);
            $sql = 'UPDATE `Bookmarks` SET `State` = :State, `EndDate` = :EndDate
                     WHERE `idBookmark` = :idBookmark AND `IdDroper` = :IdDroper ;';
                $statement = $this->getDb()->prepare($sql);
		$rawParams = $this->getParams($pBookmark);
		$statement->bindParam(':State',$rawParams[':State'], \PDO::PARAM_STR,10);
		$statement->bindParam(':EndDate',$rawParams[':EndDate']);
		$statement->bindParam(':idBookmark',$rawParams[':idBookmark'],\PDO::PARAM_INT);
		$statement->bindParam(':IdDroper',$rawParams[':IdDroper'],\PDO::PARAM_STR,34);
                $statement->execute();
//        if (!$statement->rowCount()) {
//            $errMsg = $statement->errorInfo();
//            throw new \BtcRelax\Exception\AssignBookmarkException(\sprintf('Bookmark:%s cant be finished! Error:%s"', $pBookmark->getIdBookmark(), $errMsg[2] ));
//        }
        return $this->findById($vId);        
    }

}
