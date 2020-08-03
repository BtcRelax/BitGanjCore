<?php
namespace BtcRelax\Dao;

use BtcRelax\Log;
use BtcRelax\Mapping\ProductMapper;
use BtcRelax\Model\Product;
use BtcRelax\Exception\NotFoundException;
use \PDO;
use DateTime;

final class ProductDao extends BaseDao
{
    public function insert(Product $newProduct)
    {
        $sql = "INSERT INTO `Products` (`ProductTitle`,`InfoURL`) VALUES ( :ProductTitle, :InfoURL )";
        return $this->execute($sql, $newProduct);
    }

    public function update(\BtcRelax\Model\Product $pProduct)
    {
        $sql = 'UPDATE `Products` SET `ProductTitle` = :ProductTitle,
                   `InfoURL` = :InfoURL  WHERE `idProduct` = :idProduct;';
        return $this->execute($sql, $pInvoice);
    }
    
    public function save(Product $pProduct)
    {
        if ($pProduct->getProductId() === null) {
            return $this->insert($pProduct);
        }
        return $this->update($pProduct);
    }
    
    /**
     * @return \Model\Bookmark
     * @throws Exception when cannot update or find what to update
     */
    public function execute($sql, Product $pProduct)
    {
        $statement = $this->getDb()->prepare($sql);
        $vParams = $this->getParams($pProduct);
        $this->executeStatement($statement, $vParams);
        if (!$pProduct->getProductId()) {
            $newProductId = $this->getDb()->lastInsertId();
            return $this->findById($newProductId);
        }
        if (!$statement->rowCount()) {
            throw new \BtcRelax\Exception\NotFoundException('Error creating product Id: "' . $pProduct->getProductId());
        }
        return $pProduct;
    }

    public function getParams(\BtcRelax\Model\Product $pProduct)
    {
        $params = [
            ':ProductTitle' => $pProduct->getProductName(),
            ':InfoURL' => $pProduct->getDescriptionUrl(),
        ];
        if (is_numeric($pProduct->getProductId())) {
            $params += [ ':idProduct' => $pProduct->getProductId()];
        }
        return $params;
    }
    
    
    public function findById($id)
    {
        $query = \sprintf("SELECT `CreateDate`,`idProduct`,`ProductTitle`,`InfoURL` FROM `Products` WHERE `idProduct` = %s", $id);
        Log::general(\sprintf("Result query:%s", $query), Log::INFO);
        $row = $this->query($query)->fetch();
        if (!$row) {
            Log::general("Searched product id, not found!", Log::ERROR);
            return null;
        }
        $product = new \BtcRelax\Model\Product();
        ProductMapper::map($product, $row);
        return $product;
    }
}
