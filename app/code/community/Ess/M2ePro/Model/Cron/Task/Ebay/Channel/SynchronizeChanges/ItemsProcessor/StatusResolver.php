<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Channel_SynchronizeChanges_ItemsProcessor_StatusResolver
{
    const EBAY_STATUS_ACTIVE = 'Active';
    const EBAY_STATUS_ENDED = 'Ended';
    const EBAY_STATUS_COMPLETED = 'Completed';

    const SKIP_FLAG_KEY = 'skip_first_completed_status_on_sync';

    /** @var Ess_M2ePro_Model_Listing_Product */
    protected $_listingProduct;
    protected $_channelQty = 0;
    protected $_channelQtySold = 0;

    protected $_productStatus = null;
    protected $_onlineDuration = null;
    protected $_productAdditionalData = null;

    //########################################

    public function resolveStatus(
        $channelQty,
        $channelQtySold,
        $ebayStatus,
        Ess_M2ePro_Model_Listing_Product $listingProduct
    ) {
        $this->_channelQty = $channelQty;
        $this->_channelQtySold = $channelQtySold;
        $this->_listingProduct = $listingProduct;

        $isBehaviorOfGtc = $ebayStatus == self::EBAY_STATUS_ACTIVE &&
            $this->_channelQty - $this->_channelQtySold > 0 &&
            $this->_listingProduct->isInactive();

        // Listing product isn't listed and it child must have another item_id
        $isAllowedProductStatus = $this->_listingProduct->isListed() || $this->_listingProduct->isHidden();

        if (!$isBehaviorOfGtc && !$isAllowedProductStatus) {
            return false;
        }

        switch ($ebayStatus) {
            case self::EBAY_STATUS_ACTIVE:
                $this->handleActiveStatus();
                break;
            case self::EBAY_STATUS_COMPLETED:
                $this->handleCompletedStatus();
                break;
            case self::EBAY_STATUS_ENDED:
                $this->handleEndedStatus();
                break;
            default:
                throw new Ess_M2ePro_Model_Exception('Unknown eBay listing status');
        }

        return true;
    }

    //########################################

    protected function handleActiveStatus()
    {
        if ($this->_channelQty - $this->_channelQtySold <= 0) {
            // Listed Hidden Status can be only for GTC items
            if ($this->_listingProduct->getChildObject()->getOnlineDuration() === null) {
                $this->_onlineDuration = Ess_M2ePro_Helper_Component_Ebay::LISTING_DURATION_GTC;
            }

            $this->_productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN;
            return;
        }

        if ($this->_channelQty - $this->_channelQtySold > 0 && $this->statusCompletedIsAlreadySkipped()) {
            $this->unsetSkipFlag();
        }

        $this->_productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
    }

    protected function handleCompletedStatus()
    {
        if ($this->setProductStatusInactive()) {
            return;
        }

        if ($this->_channelQty - $this->_channelQtySold > 0) {
            if ($this->statusCompletedIsAlreadySkipped()) {
                $this->unsetSkipFlag();
                $this->_productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
            } else {
                $this->setSkipFlag();
                $this->_productStatus = $this->_listingProduct->getStatus();
            }

            return;
        }

        $this->_productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
    }

    protected function handleEndedStatus()
    {
        if (!$this->setProductStatusInactive()) {
            $this->_productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
        }
    }

    // ---------------------------------------

    protected function setProductStatusInactive()
    {
        if (!$this->_listingProduct->isHidden() && $this->_channelQty == $this->_channelQtySold) {
            $this->_productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
            return true;
        }

        return false;
    }

    //########################################

    public function statusCompletedIsAlreadySkipped()
    {
        $additionalData = $this->_listingProduct->getAdditionalData();
        return isset($additionalData[self::SKIP_FLAG_KEY]);
    }

    protected function setSkipFlag()
    {
        $additionalData = $this->_listingProduct->getAdditionalData();
        $additionalData[self::SKIP_FLAG_KEY] = true;
        $this->_productAdditionalData = Mage::helper('M2ePro')->jsonEncode($additionalData);
    }

    protected function unsetSkipFlag()
    {
        $additionalData = $this->_listingProduct->getAdditionalData();
        unset($additionalData[self::SKIP_FLAG_KEY]);
        $this->_productAdditionalData = Mage::helper('M2ePro')->jsonEncode($additionalData);
    }

    //########################################

    public function getProductStatus()
    {
        return $this->_productStatus;
    }

    public function getOnlineDuration()
    {
        return $this->_onlineDuration;
    }

    public function getProductAdditionalData()
    {
        return $this->_productAdditionalData;
    }

    //########################################
}
