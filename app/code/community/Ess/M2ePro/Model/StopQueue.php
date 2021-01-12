<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Listing_Product as Listing_Product;

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
     * @param int $actionType
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function add(Ess_M2ePro_Model_Listing_Product $listingProduct, $actionType = Listing_Product::ACTION_STOP)
    {
        if (!$listingProduct->isStoppable()) {
            return false;
        }

        try {
            $requestData = $this->getRequestData($listingProduct, $actionType);
        } catch (\Exception $exception) {
            Mage::helper('M2ePro/Module_Logger')->process(
                sprintf(
                    'Product [Listing Product ID: %s, SKU %s] was not added to stop queue because of the error: %s',
                    $listingProduct->getId(),
                    $listingProduct->getChildObject()->getSku(),
                    $exception->getMessage()
                ),
                'Product was not added to stop queue',
                false
            );

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            return false;
        }

        $addedData = array(
            'component_mode'  => $listingProduct->getComponentMode(),
            'is_processed'    => 0,
            'additional_data' => Mage::helper('M2ePro')->jsonEncode(array('request_data' => $requestData)),
        );

        Mage::getModel('M2ePro/StopQueue')->setData($addedData)->save();

        return true;
    }

    // ---------------------------------------

    protected function getRequestData(Listing_Product $listingProduct, $actionType = Listing_Product::ACTION_STOP)
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
                'action_type' => $actionType,
            );
        }

        if ($listingProduct->isComponentModeAmazon()) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();
            $amazonAccount        = $amazonListingProduct->getAmazonAccount();

            $data = array(
                'account'     => $amazonAccount->getServerHash(),
                'sku'         => $amazonListingProduct->getSku(),
                'action_type' => $actionType,
            );
        }

        if ($listingProduct->isComponentModeWalmart()) {
            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
            $walmartListingProduct = $listingProduct->getChildObject();
            $walmartAccount        = $walmartListingProduct->getWalmartAccount();

            $data = array(
                'account'     => $walmartAccount->getServerHash(),
                'sku'         => $walmartListingProduct->getSku(),
                'wpid'        => $walmartListingProduct->getWpid(),
                'action_type' => $actionType,
            );
        }

        return $data;
    }

    //########################################
}
