<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Product_LockManager
{
    const LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME = 1800; // 30 min

    /** @var Ess_M2ePro_Model_Listing_Product */
    private $listingProduct = NULL;

    /** @var Ess_M2ePro_Model_Lock_Item_Manager */
    private $lockItemManager = NULL;

    /** @var Ess_M2ePro_Model_Listing_Log */
    private $listingLog = NULL;

    private $initiator = NULL;

    private $logsActionId = NULL;

    private $logsAction = NULL;

    //########################################

    public function __construct($args)
    {
        if (empty($args['listing_product'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Listing Product is not defined.');
        }

        $this->listingProduct = $args['listing_product'];
    }

    //########################################

    public function setInitiator($initiator)
    {
        $this->initiator = $initiator;
        return $this;
    }

    public function setLogsActionId($logsActionId)
    {
        $this->logsActionId = $logsActionId;
        return $this;
    }

    public function setLogsAction($logsAction)
    {
        $this->logsAction = $logsAction;
        return $this;
    }

    //########################################

    public function isLocked()
    {
        if ($this->listingProduct->isSetProcessingLock(NULL)) {
            return true;
        }

        if (!$this->getLockItemManager()->isExist()) {
            return false;
        }

        if ($this->getLockItemManager()->isInactiveMoreThanSeconds(self::LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME)) {
            $this->getLockItemManager()->remove();
            return false;
        }

        return true;
    }

    public function checkLocking()
    {
        if (!$this->isLocked()) {
            return false;
        }

        $this->getListingLog()->addProductMessage(
            $this->listingProduct->getListingId(),
            $this->listingProduct->getProductId(),
            $this->listingProduct->getId(),
            $this->initiator,
            $this->logsActionId,
            $this->logsAction,
            Mage::helper('M2ePro')->__('Another Action is being processed. Try again when the Action is completed.'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );

        return true;
    }

    // ----------------------------------------

    public function lock()
    {
        $this->getLockItemManager()->create();
    }

    public function unlock()
    {
        $this->getLockItemManager()->remove();
    }

    //########################################

    private function getLockItemManager()
    {
        if (!is_null($this->lockItemManager)) {
            return $this->lockItemManager;
        }

        return $this->lockItemManager = Mage::getModel(
            'M2ePro/Lock_Item_Manager',
            array(
                'nick' => $this->listingProduct->getComponentMode().'_listing_product_'.$this->listingProduct->getId()
            )
        );
    }

    private function getListingLog()
    {
        if (!is_null($this->listingLog)) {
            return $this->listingLog;
        }

        $this->listingLog = Mage::getModel('M2ePro/'.$this->listingProduct->getComponentMode().'_Listing_Log');

        return $this->listingLog;
    }

    //########################################
}