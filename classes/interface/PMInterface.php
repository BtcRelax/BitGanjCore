<?php
namespace BtcRelax;

/**
 * PointsManager interface
 * @author Chronos
 */
interface IPM{
    
    public function createNewPoint(\BtcRelax\Model\User $pUser, $params);
}
