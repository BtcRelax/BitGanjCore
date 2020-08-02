<?php
namespace BtcRelax\Validation;

use \BtcRelax\Exception\NotFoundException;
use \BtcRelax\Model\Bookmark;

final class BookmarkValidator {

	private function __construct() {

	}

	public static function validate(\BtcRelax\Model\Bookmark $bookmark) {
		
                $errors = [];
		return $errors;
	}
        
        public static function isCanChangeStatus (\BtcRelax\Model\User $pUser, \BtcRelax\Model\Bookmark $pBookmark, $pNewState) {
            $vNewStatus = self::isValidStatus($pNewState); $vAM = \BtcRelax\Core::createAM();
            $vCurrentState = $pBookmark->getState();
            if ((self::isBookmarkOwner($pUser, $pBookmark)) && ($vAM->isUserHasRight('EDIT_POINT', $pUser))) {
                if ($vCurrentState === $vNewState) {
                    throw new \BtcRelax\Exception\AssignBookmarkException(\sprintf("Bookmark id:%s already in state:%s",$pBookmark->getIdBookmark(), $vCurrentState));
                } else {
                    switch ($vCurrentState) {
                             case \BtcRelax\Model\Bookmark::STATUS_PREPARING:
                                 if (!($vNewState == \BtcRelax\Model\Bookmark::STATUS_PUBLISHED ) && !($vNewState == \BtcRelax\Model\Bookmark::STATUS_REJECTED) 
                                         && !($vNewState == \BtcRelax\Model\Bookmark::STATUS_LOST ))
                                 {
                                      throw new \BtcRelax\Exception\AssignBookmarkException(\sprintf("Changing state from:%s only allowed to Published or Rejected  or Lost! Or Saled, when sale from hands.",$vCurrentState));
                                 } else {
                                     if (empty($pBookmark->pLink) || empty($pBookmark->pRegionTitle) || empty($pBookmark->pAdvertiseTitle)) 
                                     {
                                        throw new \BtcRelax\Exception\AssignBookmarkException(\sprintf("Bookmark id:%s incompleate! Field Link or RegionTitle or Advertise, still empty. Operation forbiden!", $pBookmark->getIdBookmark())); 
                                     }
                                 }
                            break;
                             case \BtcRelax\Model\Bookmark::STATUS_SALED:
                                 if (($vNewState !== \BtcRelax\Model\Bookmark::STATUS_LOST) && ($vNewState !== \BtcRelax\Model\Bookmark::STATUS_CATCHED))
                                 {
                                     throw new \BtcRelax\Exception\AssignBookmarkException(\sprintf("Changing state from:%s only Lost or Catched!",$vCurrentState));
                                 }
                                 break;                     
                             case \BtcRelax\Model\Bookmark::STATUS_PUBLISHED:
                                 if (!($vNewState == \BtcRelax\Model\Bookmark::STATUS_PREPARING ) && !($vNewState == \BtcRelax\Model\Bookmark::STATUS_REJECTED) 
                                    && !($vNewState == \BtcRelax\Model\Bookmark::STATUS_LOST ) )
                                 {
                                     throw new \BtcRelax\Exception\AssignBookmarkException(\sprintf("Changing state from:%s only allowed to Preparing or Rejected or Lost!",$vCurrentState));
                                 }
                                 break;
                             case \BtcRelax\Model\Bookmark::STATUS_REJECTED:
                                 if (!($vNewState == \BtcRelax\Model\Bookmark::STATUS_PREPARING ) && !($vNewState == \BtcRelax\Model\Bookmark::STATUS_LOST ) )
                                 {
                                     throw new \BtcRelax\Exception\AssignBookmarkException(\sprintf("Changing state from:%s only allowed to Preparing or Lost!",$vCurrentState));
                                 }
                                 break;
                             default:
                                 throw new \BtcRelax\Exception\AssignBookmarkException(\sprintf("Changing state from %s to %s has no rule!",$vCurrentState, $vNewState));
                                 break;
                        }
                }
            } else { throw new \BtcRelax\Exception\AccessDeniedException("You dont have EDIT_POINT right, or not a owner!"); }
        }

        public static function isBookmarkOwner(\BtcRelax\Model\User $pUser, \BtcRelax\Model\Bookmark $pBookmark) {
            return $pUser->getIdCustomer() === $pBookmark->getIdDroper();
        }
        
	public static function validateStatus($status) {

		if (!self::isValidStatus($status)) {
			throw new NotFoundException('Unknown status: ' . $status);
		}
                return $status;
	}

	private static function isValidStatus($status) {
		return in_array($status, Bookmark::allStatuses());
	}





}

