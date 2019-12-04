<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Qty
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract
{
    //########################################

    public function getData()
    {
        $data = array_merge(
            array(
                'qty' => $this->getEbayListingProduct()->getQty()
            ),
            $this->getLotSizeData()
        );

        $this->checkQtyWarnings();

        return $data;
    }

    //########################################

    /**
     * @return array
     */
    public function getLotSizeData()
    {
        $data = array();

        $categoryId = $this->getEbayListingProduct()->getCategoryTemplateSource()->getMainCategory();
        $marketplaceId = $this->getMarketplace()->getId();
        $categoryFeatures = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
            ->getFeatures($categoryId, $marketplaceId);

        /**
         * lsd - "Lot Size Disabled". If lsd = 1, then this feature does not work for this category.
         */
        if (isset($categoryFeatures['lsd']) && $categoryFeatures['lsd'] == 0) {
            $data['lot_size'] = $this->getEbayListingProduct()->getSellingFormatTemplateSource()->getLotSize();
        }

        return $data;
    }

    protected function checkQtyWarnings()
    {
        $qtyMode = $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->getQtyMode();
        if ($qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT) {
            $listingProductId = $this->getListingProduct()->getId();
            $productId = $this->getListingProduct()->getProductId();
            $storeId = $this->getListingProduct()->getListing()->getStoreId();

            if (!empty(Ess_M2ePro_Model_Magento_Product::$statistics[$listingProductId][$productId][$storeId]['qty'])) {
                $qtys = Ess_M2ePro_Model_Magento_Product::$statistics[$listingProductId][$productId][$storeId]['qty'];
                foreach ($qtys as $type => $override) {
                    $this->addQtyWarnings($type);
                }
            }
        }
    }

    /**
     * @param int $type
     */
    protected function addQtyWarnings($type)
    {
        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_MANAGE_STOCK_NO) {
            $this->addWarningMessage(
                'During the Quantity Calculation the Settings in the "Manage Stock No" '.
                'field were taken into consideration.'
            );
        }

        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_BACKORDERS) {
            $this->addWarningMessage(
                'During the Quantity Calculation the Settings in the "Backorders" '.
                'field were taken into consideration.'
            );
        }
    }

    //########################################
}
