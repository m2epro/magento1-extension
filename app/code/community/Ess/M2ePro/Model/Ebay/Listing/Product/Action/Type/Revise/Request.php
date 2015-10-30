<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Revise_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
{
    //########################################

    /**
     * @return array
     */
    public function getActionData()
    {
        $data = array_merge(
            array(
                'item_id' => $this->getEbayListingProduct()->getEbayItemIdReal()
            ),
            $this->getRequestVariations()->getData()
        );

        if ($this->getConfigurator()->isGeneralAllowed()) {

            $data['sku'] = $this->getEbayListingProduct()->getSku();

            $data = array_merge(

                $data,

                $this->getRequestCategories()->getData(),

                $this->getRequestPayment()->getData(),
                $this->getRequestReturn()->getData(),
                $this->getRequestShipping()->getData()
            );
        }

        return array_merge(
            $data,
            $this->getRequestSelling()->getData(),
            $this->getRequestDescription()->getData()
        );
    }

    /**
     * @param array $data
     * @return array
     */
    protected function prepareFinalData(array $data)
    {
        $data = $this->processingReplacedAction($data);

        $data = $this->insertHasSaleFlagToVariations($data);
        $data = $this->removeImagesIfThereAreNoChanges($data);
        $data = $this->removeNodesIfItemHasTheSaleOrBid($data);
        $data = $this->removeDurationByBestOfferMode($data);

        return parent::prepareFinalData($data);
    }

    //########################################

    private function processingReplacedAction($data)
    {
        $params = $this->getConfigurator()->getParams();

        if (!isset($params['replaced_action'])) {
            return $data;
        }

        $this->insertReplacedActionMessage($params['replaced_action']);
        $data = $this->modifyQtyByReplacedAction($params['replaced_action'], $data);

        return $data;
    }

    private function insertReplacedActionMessage($replacedAction)
    {
        switch ($replacedAction) {

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:

                $this->addWarningMessage(
                    'Revise was executed instead of Relist because \'Out Of Stock Control\' Option is enabled '.
                    'in the \'Price, Quantity and Format\' Policy'
                );

            break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:

                $this->addWarningMessage(
                    'Revise was executed instead of Stop because \'Out Of Stock Control\' Option is enabled '.
                    'in the \'Price, Quantity and Format\' Policy'
                );

            break;
        }

        return;
    }

    private function modifyQtyByReplacedAction($replacedAction, array $data)
    {
        if ($replacedAction != Ess_M2ePro_Model_Listing_Product::ACTION_STOP) {
            return $data;
        }

        $data['out_of_stock_control'] = $this->getEbayListingProduct()
                                             ->getEbaySellingFormatTemplate()->getOutOfStockControl();

        if (!$this->getIsVariationItem()) {
            $data['qty'] = 0;
            return $data;
        }

        if (!isset($data['variation']) || !is_array($data['variation'])) {
            return $data;
        }

        foreach ($data['variation'] as &$variation) {
            $variation['not_real_qty'] = true;
            $variation['qty'] = 0;
        }

        return $data;
    }

    // ---------------------------------------

    private function insertHasSaleFlagToVariations(array $data)
    {
        if (!isset($data['variation']) || !is_array($data['variation'])) {
            return $data;
        }

        foreach ($data['variation'] as &$variation) {
            if (!isset($variation['not_real_qty']) && isset($variation['qty']) && (int)$variation['qty'] <= 0) {

                /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
                $ebayVariation = $variation['_instance_']->getChildObject();

                if ($ebayVariation->getOnlineQtySold() || $ebayVariation->hasSales()) {
                    $variation['has_sales'] = true;
                }
            }
        }

        return $data;
    }

    private function removeImagesIfThereAreNoChanges(array $data)
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        $key = 'ebay_product_images_hash';
        if (!empty($additionalData[$key]) && isset($data['images']['images']) &&
            $additionalData[$key] == Mage::helper('M2ePro/Component_Ebay')->getImagesHash($data['images']['images'])) {
            unset($data['images']['images']);
        }

        $key = 'ebay_product_variation_images_hash';
        if (!empty($additionalData[$key]) && isset($data['variation_image']) &&
            $additionalData[$key] == Mage::helper('M2ePro/Component_Ebay')->getImagesHash($data['variation_image'])) {
            unset($data['variation_image']);
        }

        return $data;
    }

    private function removeNodesIfItemHasTheSaleOrBid(array $data)
    {
        if (!isset($data['title']) && !isset($data['subtitle']) &&
            !isset($data['duration']) && !isset($data['is_private'])) {
            return $data;
        }

        $deleteByAuctionFlag = $this->getEbayListingProduct()->isListingTypeAuction() &&
                               $this->getEbayListingProduct()->getOnlineBids() > 0;

        $deleteByFixedFlag = $this->getEbayListingProduct()->isListingTypeFixed() &&
                             $this->getEbayListingProduct()->getOnlineQtySold() > 0;

        if (isset($data['title']) && $deleteByAuctionFlag) {
            $warningMessageReasons[] = Mage::helper('M2ePro')->__('Title');
            unset($data['title']);
        }
        if (isset($data['subtitle']) && $deleteByAuctionFlag) {
            $warningMessageReasons[] = Mage::helper('M2ePro')->__('Subtitle');
            unset($data['subtitle']);
        }
        if (isset($data['duration']) && ($deleteByAuctionFlag || $deleteByFixedFlag)) {
            $warningMessageReasons[] = Mage::helper('M2ePro')->__('Duration');
            unset($data['duration']);
        }
        if (isset($data['is_private']) && ($deleteByAuctionFlag || $deleteByFixedFlag)) {
            $warningMessageReasons[] = Mage::helper('M2ePro')->__('Private Listing');
            unset($data['is_private']);
        }

        if (!empty($warningMessageReasons)) {

            // M2ePro_TRANSLATIONS
            // %field_title% field(s) were ignored because eBay doesn't allow Revise the Item if it has sales, bids for Auction Type or less than 12 hours remain before the Item end.
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    '%field_title% field(s) were ignored because eBay doesn\'t allow Revise the Item if it has sales, '.
                    'bids for Auction Type or less than 12 hours remain before the Item end.',
                    implode(', ', $warningMessageReasons)
                )
            );
        }

        return $data;
    }

    private function removeDurationByBestOfferMode(array $data)
    {
        if (isset($data['bestoffer_mode']) && $data['bestoffer_mode']) {

            // M2ePro_TRANSLATIONS
            // Duration field(s) was ignored because eBay doesn't allow Revise the Item if Best Offer is enabled.
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'Duration field(s) was ignored because '.
                    'eBay doesn\'t allow Revise the Item if Best Offer is enabled.'
                )
            );
            unset($data['duration']);
        }

        return $data;
    }

    //########################################
}