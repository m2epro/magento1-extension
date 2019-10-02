<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_StopQueue extends Ess_M2ePro_Model_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/StopQueue');
    }

    //########################################

    public function getComponentMode()
    {
        return $this->getData('component_mode');
    }

    public function isProcessed()
    {
        return (bool)$this->getData('is_processed');
    }

    public function getAdditionalData()
    {
        return $this->getSettings('additional_data');
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function add(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isStoppable()) {
            return false;
        }

        $requestData = $this->getRequestData($listingProduct);
        if (empty($requestData)) {
            return false;
        }

        $additionalData = array(
            'request_data' => $requestData,
        );

        $addedData = array(
            'component_mode'  => $listingProduct->getComponentMode(),
            'is_processed'    => 0,
            'additional_data' => Mage::helper('M2ePro')->jsonEncode($additionalData),
        );

        Mage::getModel('M2ePro/StopQueue')->setData($addedData)->save();

        return true;
    }

    // ---------------------------------------

    protected function getRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $data = array();

        if ($listingProduct->isComponentModeEbay()) {
            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
            $ebayListingProduct = $listingProduct->getChildObject();
            $ebayAccount        = $ebayListingProduct->getEbayAccount();

            $data = array(
                'account'     => $ebayAccount->getServerHash(),
                'marketplace' => $ebayListingProduct->getMarketplace()->getNativeId(),
                'item_id'     => $ebayListingProduct->getEbayItem()->getItemId(),
            );
        }

        if ($listingProduct->isComponentModeAmazon()) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();
            $amazonAccount        = $amazonListingProduct->getAmazonAccount();

            $data = array(
                'account' => $amazonAccount->getServerHash(),
                'sku'     => $amazonListingProduct->getSku(),
            );
        }

        return $data;
    }

    //########################################
}
