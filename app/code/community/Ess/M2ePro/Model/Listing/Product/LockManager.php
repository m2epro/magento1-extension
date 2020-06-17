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
    protected $_listingProduct = null;

    /** @var Ess_M2ePro_Model_Lock_Item_Manager */
    protected $_lockItemManager = null;

    /** @var Ess_M2ePro_Model_Listing_Log */
    protected $_listingLog = null;

    protected $_initiator = null;

    protected $_logsActionId = null;

    protected $_logsAction = null;

    //########################################

    public function __construct($args)
    {
        if (empty($args['listing_product'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Listing Product is not defined.');
        }

        $this->_listingProduct = $args['listing_product'];
    }

    //########################################

    public function setInitiator($initiator)
    {
        $this->_initiator = $initiator;
        return $this;
    }

    public function setLogsActionId($logsActionId)
    {
        $this->_logsActionId = $logsActionId;
        return $this;
    }

    public function setLogsAction($logsAction)
    {
        $this->_logsAction = $logsAction;
        return $this;
    }

    //########################################

    public function isLocked()
    {
        if ($this->_listingProduct->isSetProcessingLock(null)) {
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
            $this->_listingProduct->getListingId(),
            $this->_listingProduct->getProductId(),
            $this->_listingProduct->getId(),
            $this->_initiator,
            $this->_logsActionId,
            $this->_logsAction,
            Mage::helper('M2ePro')->__('Another Action is being processed. Try again when the Action is completed.'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
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

    protected function getLockItemManager()
    {
        if ($this->_lockItemManager !== null) {
            return $this->_lockItemManager;
        }

        return $this->_lockItemManager = Mage::getModel(
            'M2ePro/Lock_Item_Manager',
            array(
                'nick' => $this->_listingProduct->getComponentMode()
                          . '_listing_product_' . $this->_listingProduct->getId()
            )
        );
    }

    protected function getListingLog()
    {
        if ($this->_listingLog !== null) {
            return $this->_listingLog;
        }

        $this->_listingLog = Mage::getModel('M2ePro/' . $this->_listingProduct->getComponentMode() . '_Listing_Log');

        return $this->_listingLog;
    }

    //########################################
}
