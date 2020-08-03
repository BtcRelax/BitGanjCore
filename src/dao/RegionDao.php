<?php namespace BtcRelax\Dao;

final class RegionDao extends BaseDao
{
    //put your code here
    
    public function findById($id)
    {
        $query = \sprintf("SELECT `idProduct`,`ProductTitle`,`InfoURL` FROM `Products`" +
            "WHERE idProduct = '%s' LIMIT 1 ", $id);
        Log::general(\sprintf("Result query:%s", $query), Log::INFO);
        $row = parent::query($query)->fetch();
        if (!$row) {
            Log::general("Searched product id, not found!", Log::ERROR);
            return null;
        }
        $product = new \BtcRelax\Model\Product();
        ProductMapper::map($product, $row);
        return $product;
    }
}
